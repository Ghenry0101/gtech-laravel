<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class OrderItemReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [File::image()->max(10240)],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.array' => __('Unggah maksimal 5 foto.'),
            'images.*.image' => __('Setiap file harus berupa gambar dengan format JPG, PNG, atau WEBP.'),
            'images.*.max' => __('Ukuran foto maksimal 10MB per file.'),
            'images.*.uploaded' => __('Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 10MB dan koneksi Anda stabil.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'images.*' => __('foto'),
        ];
    }
}
