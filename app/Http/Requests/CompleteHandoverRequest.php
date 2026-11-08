<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CompleteHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('claim')->status !== 'approved') {
            throw ValidationException::withMessages([
                'return' => 'Hanya klaim yang sudah disetujui dapat diserahterimakan.',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_identifier' => ['nullable', 'string', 'max:80'],
            'handover_location' => ['required', 'string', 'max:200'],
            'handover_notes' => ['nullable', 'string', 'max:2000'],
            'handover_evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'handover_confirmed' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'handover_location.required' => 'Lokasi serah terima wajib diisi.',
            'handover_evidence.required' => 'Unggah foto atau dokumen bukti serah terima.',
            'handover_evidence.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'handover_evidence.max' => 'Ukuran bukti maksimal 5 MB.',
            'handover_confirmed.accepted' =>
                'Konfirmasikan pemeriksaan identitas dan penyerahan fisik barang.',
        ];
    }
}
