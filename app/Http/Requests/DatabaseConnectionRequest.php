<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DatabaseConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'string', 'in:mysql,pgsql,sqlite'],
            'host' => ['required_unless:driver,sqlite', 'nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer'],
            'database' => ['required', 'string', 'max:255'],
            'username' => ['required_unless:driver,sqlite', 'nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
