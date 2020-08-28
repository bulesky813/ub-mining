<?php

declare(strict_types=1);

namespace App\Request\Income;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;

class ExcludeUsersRequest extends AbstractRequest
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

        ];
    }
}
