<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\ProdiCode;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'nim' => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', 'unique:users,nim'],
            'name' => ['required', 'string', 'max:255'],
            'status_mahasiswa' => ['required', 'string', 'in:aktif,alumni'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.size' => 'NIM harus tepat 10 digit.',
            'nim.regex' => 'NIM hanya boleh berisi angka.',
            'nim.unique' => 'NIM sudah terdaftar. Silakan login atau gunakan akun yang sudah ada.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'status_mahasiswa.required' => 'Status mahasiswa wajib dipilih.',
            'status_mahasiswa.in' => 'Status mahasiswa harus aktif atau alumni.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ])->validate();

        $nim = (string) $input['nim'];
        $kodeProdi = substr($nim, 2, 4);
        $tahunMasuk = (int) substr($nim, 0, 2);
        $angkatan = 2000 + $tahunMasuk;

        $prodi = ProdiCode::where('kode', $kodeProdi)->first();

        try {
            return DB::transaction(function () use ($input, $nim, $kodeProdi, $angkatan, $prodi): User {
                $user = new User;
                $user->nim = $nim;
                $user->name = trim((string) $input['name']);
                $user->status_mahasiswa = (string) $input['status_mahasiswa'];
                $user->email = ! empty($input['email']) ? trim((string) $input['email']) : null;
                $user->password = Hash::make((string) $input['password']);
                $user->kode_prodi_nim = $kodeProdi;
                $user->angkatan = $angkatan;

                if ($prodi) {
                    $user->prodi_code_id = $prodi->id;
                    $user->kode_prodi_tidak_dikenal = false;
                } else {
                    $user->prodi_code_id = null;
                    $user->kode_prodi_tidak_dikenal = true;
                }

                // Kolom keamanan dan hak akses dipaksa secara ketat di backend
                $user->role = 'user';
                $user->status_verifikasi = 'menunggu';
                $user->is_suspended = false;
                $user->ktm_path = null;

                $user->save();

                return $user;
            });
        } catch (QueryException $e) {
            // Tangani race condition jika ada 2 request registrasi NIM yang sama secara bersamaan
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                throw ValidationException::withMessages([
                    'nim' => ['NIM sudah terdaftar. Silakan login atau gunakan akun yang sudah ada.'],
                ]);
            }

            if (str_contains($e->getMessage(), 'uq_users_nim') || str_contains($e->getMessage(), 'users.nim')) {
                throw ValidationException::withMessages([
                    'nim' => ['NIM sudah terdaftar. Silakan login atau gunakan akun yang sudah ada.'],
                ]);
            }

            throw $e;
        }
    }
}
