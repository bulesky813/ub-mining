<?php

declare(strict_types=1);

namespace App\Request\Mine;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class MinePoolRequest extends FormRequest
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
            case '/api/v1/mine/create':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                ];
                break;
            case '/api/v1/mine/update':
                $rule = [
                    'pool_id' => [
                        'required',
                        'integer',
                    ],
                    'coin_symbol' => [
//                        'required',
                        'string',
                    ],
                    'status' => [
                        'integer',
                        Rule::in([0, 1]),
                    ]
                ];
                break;
        }
        return $rule;
    }
}
