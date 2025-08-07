<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetugasKegiatanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bertugas_sebagai'      => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
            'wilayah_tugas'         => 'required',
            'beban_kerja'           => 'required|integer',
            'satuan_beban_kerja'    => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bertugas_sebagai.regex' => 'Kolom ini hanya boleh berisi huruf.',
            'satuan.regex' => 'Kolom ini hanya boleh berisi huruf.',
        ];
    }
}
