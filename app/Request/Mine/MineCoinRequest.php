<?php

declare(strict_types=1);

namespace App\Request\Mine;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;

class MineCoinRequest extends AbstractRequest
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
        $rule = [];
        switch ($this->getUri()->getPath()) {
            case '/api/v1/mine/coin_create':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                ];
                break;
            case '/api/v1/mine/coin_update':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ]
                ];
                break;
        }
        return $rule;
    }
}
