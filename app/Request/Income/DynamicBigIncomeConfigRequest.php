<?php

declare(strict_types=1);

namespace App\Request\Income;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;

class DynamicBigIncomeConfigRequest extends AbstractRequest
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
            case '/api/v1/mine/dynamic/big_income_config_create':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                    'num' => [
                        'required',
                        'string',
                    ],
                    'person_num' => [
                        'required',
                        'string',
                    ],
                    'percent' => [
                        'required',
                        'string',
                    ],
                ];
                break;
            case '/api/v1/mine/dynamic/big_income_config_update':
                $rule = [
                    'config_id' => [
                        'required',
                        'integer',
                    ],
                    'coin_symbol' => [
                        'required',
                        'string',
                    ]
                ];
                break;
            case '/api/v1/mine/dynamic/big_income_config_del':
                $rule = [
                    'config_id' => [
                        'required',
                        'integer',
                    ],
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
