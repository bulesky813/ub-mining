<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class UserCoinSymbolInfoRequest extends AbstractRequest
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
            'user_id' => [
                'required',
                'integer',
                'gt:0'
            ],
            'coin_symbol' => [
                'required',
                Rule::exists('mine_pool', 'coin_symbol')->where(function ($query) {
                    $query->where('status', 1);
                })
            ]
        ];
    }
}
