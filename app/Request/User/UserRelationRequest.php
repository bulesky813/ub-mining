<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Exception\UserUniqueException;
use App\Request\AbstractRequest;
use App\Services\User\UserRelationService;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Di\Annotation\Inject;

class UserRelationRequest extends AbstractRequest
{
    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

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
                'gt:0',
                function ($attribute, $value, $fail) {
                    $ur = $this->urs->findUser($value);
                    if ($ur) {
                        throw new UserUniqueException('用户已绑定过上级!');
                    }
                }
            ],
            'parent_id' => [
                'required',
                'integer',
                'gt:0',
                function ($attribute, $value, $fail) {
                    if ($value == $this->input('user_id')) {
                        return $fail('相同的用户不能建立关系!');
                    }
                }
            ]
        ];
    }
}
