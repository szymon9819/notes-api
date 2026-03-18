<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IssueApiTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|ValidationRuleContract|list<ValidationRule|ValidationRuleContract|string>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ];
    }

    public function email(): string
    {
        return $this->string('email')->toString();
    }

    public function password(): string
    {
        return $this->string('password')->toString();
    }

    public function deviceName(): string
    {
        return $this->string('device_name')->toString();
    }
}
