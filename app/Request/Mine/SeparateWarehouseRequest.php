<?php

declare(strict_types=1);

namespace App\Request\Mine;

use App\Request\AbstractRequest;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SeparateWarehouseRequest extends AbstractRequest
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
            case '/api/v1/mine/sw_create':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                    'sort' => [
                        'required',
                        'integer',
                    ],
                    'low' => [
                        'required',
                        'lt:high',
                    ],
                    'high' => [
                        'required',
                        'gt:low',
                    ],
                    'percent' => [
                        'required',
                    ],
                ];
                break;
            case '/api/v1/mine/sw_update':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                    'sort' => [
                        'required',
                        'integer',
                    ],
                    'low' => [
//                        'required',
                        'lt:high',
                    ],
                    'high' => [
//                        'required',
                        'gt:low',
                    ],
                ];
                break;
            case '/api/v1/mine/sw_del':
                $rule = [
                    'coin_symbol' => [
                        'required',
                        'string',
                    ],
                    'sort' => [
                        'required',
                        'integer',
                    ]
                ];
                break;
        }
        return $rule;
    }
}
