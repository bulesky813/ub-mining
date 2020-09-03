<?php

declare(strict_types=1);

namespace App\Request\Mine;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class MinePoolRequest extends AbstractRequest
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
            case '/api/v1/mine/base_config_save':
                $rule = [
                    'enable_time' => [
                        'required',
                        'gt:0',
                    ],
                    'raise_condition' => [
                        'required',
                        Rule::in([1, 2]),
                    ]
                ];
                break;
        }
        return $rule;
    }
}
