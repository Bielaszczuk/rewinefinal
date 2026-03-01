<?php

namespace App\Http\Requests\Comparison;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompareWinesRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'wineAId'  => ['required', 'uuid', 'exists:wines,id'],
            'wineBId'  => ['required', 'uuid', 'different:wineAId', 'exists:wines,id'],
            'language' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'wineAId.required' => 'El primer vino es requerido',
            'wineAId.uuid' => 'El ID del primer vino debe ser un UUID válido',
            'wineAId.exists' => 'El primer vino no existe',
            'wineBId.required' => 'El segundo vino es requerido',
            'wineBId.uuid' => 'El ID del segundo vino debe ser un UUID válido',
            'wineBId.different' => 'Los vinos deben ser diferentes',
            'wineBId.exists' => 'El segundo vino no existe',
            'language.in' => 'El idioma debe ser es-AR, en-US o pt-BR',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'wineAId' => 'primer vino',
            'wineBId' => 'segundo vino',
            'language' => 'idioma',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'timestamp' => now()->toISOString(),
                'path'      => $this->path(),
                'requestId' => app()->bound('request-id') ? app('request-id') : null,
                'status'    => 422,
                'code'      => 'VALIDATION_ERROR',
                'message'   => 'Los datos proporcionados no son válidos',
                'errors'    => $this->formatErrors($validator),
            ], 422)
        );
    }

    /**
     * Format validation errors for response.
     */
    private function formatErrors(Validator $validator): array
    {
        return collect($validator->errors()->toArray())
            ->flatMap(fn($msgs, $field) => array_map(
                fn($m) => ['field' => $field, 'message' => $m],
                $msgs
            ))
            ->values()
            ->all();
    }

    /**
     * Get the wine A ID.
     */
    public function getWineAId(): string
    {
        return $this->input('wineAId');
    }

    /**
     * Get the wine B ID.
     */
    public function getWineBId(): string
    {
        return $this->input('wineBId');
    }

    /**
     * Get the language, with default fallback.
     */
    public function getLanguage(): string
    {
        return $this->input('language', 'es-AR');
    }
}
