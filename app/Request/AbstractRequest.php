<?php

namespace App\Request;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Request\FormRequest;

class AbstractRequest extends FormRequest
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
        return [];
    }
}
