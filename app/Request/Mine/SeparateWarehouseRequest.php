<?php

declare(strict_types=1);

namespace App\Request\Mine;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SeparateWarehouseRequest extends FormRequest
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
        }
        return $rule;
    }
}
