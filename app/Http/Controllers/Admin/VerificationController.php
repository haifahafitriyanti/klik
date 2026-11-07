<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    /**
     * Tampilkan dashboard verifikasi identitas mahasiswa/alumni KLiK.
     */
    public function index(Request $request): Response
    {
        $statusFilter = (string) $request->query('status', 'menunggu');
        $ktmFilter = (string) $request->query('has_ktm', 'semua');
        $search = trim((string) $request->query('search', ''));

        // Statistik ringkasan pengajuan verifikasi
        $baseStatsQuery = User::where('role', 'user');

        $stats = [
            'total_menunggu' => (clone $baseStatsQuery)->where('status_verifikasi', 'menunggu')->count(),
            'siap_verifikasi' => (clone $baseStatsQuery)->where('status_verifikasi', 'menunggu')->whereNotNull('ktm_path')->count(),
            'menunggu_ktm' => (clone $baseStatsQuery)->where('status_verifikasi', 'menunggu')->whereNull('ktm_path')->count(),
            'total_disetujui' => (clone $baseStatsQuery)->where('status_verifikasi', 'disetujui')->count(),
            'total_ditolak' => (clone $baseStatsQuery)->where('status_verifikasi', 'ditolak')->count(),
        ];

        // Query pengajuan verifikasi dengan filter dan eager loading
        $query = User::query()
            ->where('role', 'user')
            ->with(['prodiCode.jurusan', 'verifier']);

        if (in_array($statusFilter, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $query->where('status_verifikasi', $statusFilter);
        }

        if ($ktmFilter === 'ada') {
            $query->whereNotNull('ktm_path');
        } elseif ($ktmFilter === 'belum') {
            $query->whereNull('ktm_path');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Urutan: Pengajuan yang siap diperiksa diutamakan, lalu terlama di antrean
        $submissions = $query
            ->orderByRaw("CASE WHEN status_verifikasi = 'menunggu' AND ktm_path IS NOT NULL THEN 1 WHEN status_verifikasi = 'menunggu' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(function (User $u) {
                $hasKtm = ! empty($u->ktm_path);

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'nim' => $u->nim,
                    'status_mahasiswa' => $u->status_mahasiswa,
                    'kode_prodi_nim' => $u->kode_prodi_nim,
                    'angkatan' => $u->angkatan,
                    'prodi_name' => $u->prodiCode?->nama_prodi,
                    'jenjang' => $u->prodiCode?->jenjang,
                    'jurusan_name' => $u->prodiCode?->jurusan?->nama,
                    'kode_prodi_tidak_dikenal' => (bool) $u->kode_prodi_tidak_dikenal,
                    'status_verifikasi' => $u->status_verifikasi,
                    'has_ktm' => $hasKtm,
                    'document_token' => $hasKtm
                        ? hash_hmac('sha256', (string) $u->ktm_path.'|'.$u->updated_at?->timestamp, (string) config('app.key'))
                        : null,
                    'created_at' => $u->created_at?->format('d M Y H:i'),
                    'updated_at' => $u->updated_at?->format('d M Y H:i'),
                    'verified_at' => $u->verified_at?->format('d M Y H:i'),
                    'verifier_name' => $u->verifier?->name,
                    'alasan_penolakan' => $u->alasan_penolakan,
                ];
            });

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'submissions' => $submissions,
            'filters' => [
                'status' => $statusFilter,
                'has_ktm' => $ktmFilter,
                'search' => $search,
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Tampilkan stream gambar foto KTM pengguna melalui akses privat khusus administrator.
     */
    public function ktm(Request $request, User $user): HttpResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();

        if (! $currentUser || ! $currentUser->isAdmin() || $currentUser->isSuspended()) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isAdmin()) {
            abort(403, 'Tidak ada dokumen KTM untuk akun administrator.');
        }

        if (empty($user->ktm_path)) {
            abort(404, 'Dokumen KTM tidak ditemukan.');
        }

        if (! Storage::disk('ktm')->exists($user->ktm_path)) {
            abort(404, 'File dokumen tidak ditemukan di penyimpanan privat server.');
        }

        $fileContents = Storage::disk('ktm')->get($user->ktm_path);
        $mimeType = Storage::disk('ktm')->mimeType($user->ktm_path) ?: 'image/jpeg';
        $extension = pathinfo($user->ktm_path, PATHINFO_EXTENSION) ?: 'jpg';

        return response($fileContents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="ktm_document.'.$extension.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Setujui pengajuan verifikasi identitas mahasiswa/alumni.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();

        if (! $currentUser || ! $currentUser->isAdmin() || $currentUser->isSuspended()) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isAdmin()) {
            abort(403, 'Aksi ini tidak diizinkan untuk akun administrator.');
        }

        try {
            DB::transaction(function () use ($user, $currentUser, $request) {
                /** @var User $lockedUser */
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

                if ($lockedUser->status_verifikasi !== 'menunggu') {
                    throw new DomainException('Pengajuan ini telah diproses sebelumnya oleh administrator lain (status saat ini: '.$lockedUser->status_verifikasi.').');
                }

                if (empty($lockedUser->ktm_path) || ! Storage::disk('ktm')->exists($lockedUser->ktm_path)) {
                    throw new DomainException('Pengguna belum mengunggah foto KTM yang sah sehingga tidak dapat disetujui.');
                }

                if ($lockedUser->isSuspended()) {
                    throw new DomainException('Akun pengguna sedang ditangguhkan.');
                }

                // Validasi versi dokumen KTM untuk mencegah persetujuan dokumen usang/lama
                if ($request->filled('document_token')) {
                    $expectedToken = hash_hmac('sha256', (string) $lockedUser->ktm_path.'|'.$lockedUser->updated_at?->timestamp, (string) config('app.key'));
                    if (! hash_equals($expectedToken, (string) $request->input('document_token'))) {
                        throw new DomainException('Dokumen KTM telah diganti oleh pengguna atau diperbarui setelah peninjauan. Harap muat ulang halaman untuk memeriksa dokumen terbaru.');
                    }
                }

                $lockedUser->status_verifikasi = 'disetujui';
                $lockedUser->verified_by = $currentUser->id;
                $lockedUser->verified_at = Carbon::now();
                $lockedUser->alasan_penolakan = null;
                $lockedUser->save();
            });
        } catch (DomainException $e) {
            return back()->withErrors(['verifikasi' => $e->getMessage()]);
        }

        return back()->with('status', 'Akun mahasiswa/alumni (NIM: '.$user->nim.') berhasil diverifikasi dan disetujui.');
    }

    /**
     * Tolak pengajuan verifikasi identitas mahasiswa/alumni dengan catatan alasan penolakan.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();

        if (! $currentUser || ! $currentUser->isAdmin() || $currentUser->isSuspended()) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isAdmin()) {
            abort(403, 'Aksi ini tidak diizinkan untuk akun administrator.');
        }

        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi agar pengguna mengetahui hal yang perlu diperbaiki.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 5 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        try {
            DB::transaction(function () use ($user, $currentUser, $request) {
                /** @var User $lockedUser */
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

                if ($lockedUser->status_verifikasi !== 'menunggu') {
                    throw new DomainException('Pengajuan ini telah diproses sebelumnya oleh administrator lain (status saat ini: '.$lockedUser->status_verifikasi.').');
                }

                if (empty($lockedUser->ktm_path) || ! Storage::disk('ktm')->exists($lockedUser->ktm_path)) {
                    throw new DomainException('Pengajuan ini tidak memiliki dokumen KTM aktif untuk ditinjau.');
                }

                if ($lockedUser->isSuspended()) {
                    throw new DomainException('Akun pengguna sedang ditangguhkan.');
                }

                // Validasi versi dokumen KTM untuk mencegah penolakan dokumen usang/lama
                if ($request->filled('document_token')) {
                    $expectedToken = hash_hmac('sha256', (string) $lockedUser->ktm_path.'|'.$lockedUser->updated_at?->timestamp, (string) config('app.key'));
                    if (! hash_equals($expectedToken, (string) $request->input('document_token'))) {
                        throw new DomainException('Dokumen KTM telah diganti oleh pengguna atau diperbarui setelah peninjauan. Harap muat ulang halaman untuk memeriksa dokumen terbaru.');
                    }
                }

                $lockedUser->status_verifikasi = 'ditolak';
                $lockedUser->alasan_penolakan = $request->input('alasan_penolakan');
                $lockedUser->verified_by = $currentUser->id;
                $lockedUser->verified_at = Carbon::now();
                $lockedUser->save();
            });
        } catch (DomainException $e) {
            return back()->withErrors(['verifikasi' => $e->getMessage()]);
        }

        return back()->with('status', 'Pengajuan verifikasi akun mahasiswa/alumni (NIM: '.$user->nim.') telah ditolak dengan catatan alasan penolakan.');
    }
}
