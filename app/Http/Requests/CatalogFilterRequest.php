<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CatalogFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi dan bersihkan query parameter sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('q')) {
            $rawQ = $this->query('q');
            if (is_string($rawQ)) {
                $trimmed = trim($rawQ);
                $normalized['q'] = $trimmed !== '' ? mb_substr($trimmed, 0, 100) : null;
            } else {
                $normalized['q'] = null;
            }
        }

        if ($this->has('category')) {
            $rawCat = $this->query('category');
            if (is_string($rawCat)) {
                $trimmedCat = trim($rawCat);
                $normalized['category'] = $trimmedCat !== '' ? mb_substr($trimmedCat, 0, 50) : null;
            } else {
                $normalized['category'] = null;
            }
        }

        if ($this->has('brand')) {
            $rawBrand = $this->query('brand');
            if (is_string($rawBrand)) {
                $trimmedBrand = trim($rawBrand);
                $normalized['brand'] = $trimmedBrand !== '' ? mb_substr($trimmedBrand, 0, 50) : null;
            } else {
                $normalized['brand'] = null;
            }
        }

        if ($this->has('min_price')) {
            $rawMin = $this->query('min_price');
            if (is_string($rawMin) && is_numeric($rawMin) && (int) $rawMin >= 0) {
                $normalized['min_price'] = (int) $rawMin;
            } else {
                $normalized['min_price'] = null;
            }
        }

        if ($this->has('max_price')) {
            $rawMax = $this->query('max_price');
            if (is_string($rawMax) && is_numeric($rawMax) && (int) $rawMax >= 0) {
                $normalized['max_price'] = (int) $rawMax;
            } else {
                $normalized['max_price'] = null;
            }
        }

        if ($this->has('sort')) {
            $rawSort = $this->query('sort');
            if (is_string($rawSort) && in_array($rawSort, ['newest', 'price_asc', 'price_desc'], true)) {
                $normalized['sort'] = $rawSort;
            } else {
                $normalized['sort'] = 'newest';
            }
        }

        if ($this->has('page')) {
            $rawPage = $this->query('page');
            if (is_numeric($rawPage) && (int) $rawPage >= 1) {
                $normalized['page'] = (int) $rawPage;
            } else {
                $normalized['page'] = 1;
            }
        }

        if (! empty($normalized)) {
            $this->merge($normalized);
        }
    }

    /**
     * Aturan validasi query string katalog laptop KLiK.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'sort' => ['nullable', 'string', 'in:newest,price_asc,price_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
