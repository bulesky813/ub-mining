<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Request\AbstractRequest;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\User\UserWarehouseService;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class UserChangeAssetsRequest extends AbstractRequest
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
                'gt:0',
                'exists:user_relation,user_id'
            ],
            'coin_symbol' => [
                'required',
                'alpha_num',
                Rule::exists('mine_pool', 'coin_symbol')->where(function ($query) {
                    $query->where('status', 1);
                })
            ],
            'separate_warehouse_sort' => [
                'required',
                'integer',
                'exists:separate_warehouse,sort'
            ],
            'value' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (bccomp($value, '0') == 0) {
                        return $fail('持仓变动量不能为 0');
                    }
                    $user_id = (int)$this->input('user_id');
                    $coin_symbol = (string)$this->input('coin_symbol');
                    $separate_warehouse_sort = (int)$this->input('separate_warehouse_sort');
                    $uw = new UserWarehouseService();
                    $user_warehouse = $uw->userWarehouse($user_id, $coin_symbol); //获取用户所有持仓
                    if ($user_warehouse->isEmpty() && $separate_warehouse_sort > 1) {
                        return $fail('只能从1号仓开始加仓!');
                    }
                    $sws = new SeparateWarehouseService();
                    $symbol_separate_warehouse = $sws->separateWarehouse(
                        $coin_symbol,
                        $separate_warehouse_sort
                    )->offsetGet(0);//获取当前操作仓位的详细
                    $new_assets = bcadd(
                        $user_warehouse->offsetGet($separate_warehouse_sort - 1)->assets ?? '0',
                        $value
                    );
                    if (bccomp($value, '0') == 1) {
                        if ($separate_warehouse_sort > $user_warehouse->count() + 1) {
                            return $fail(sprintf("不能对超过%d号的仓位加仓！", $user_warehouse->count() + 1));
                        }
                        if (bccomp($new_assets, (string)$symbol_separate_warehouse->low) <= 0) {
                            return $fail(sprintf("该仓位最低持仓量必须大于 %s", $symbol_separate_warehouse->low));
                        }
                        if (bccomp($new_assets, (string)$symbol_separate_warehouse->high) > 0) {
                            return $fail(sprintf("该仓位最大持仓量必须小于或等于 %s", $symbol_separate_warehouse->high));
                        }
                    } else {
                        if ($separate_warehouse_sort < $user_warehouse->count()) {
                            return $fail(sprintf('必须从%d号仓位开始撤仓', $user_warehouse->count()));
                        }
                        if (bccomp($new_assets, '0') == -1) {
                            return $fail(sprintf('持仓总数量不能小于 0'));
                        }
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'coin_symbol.exists' => '币种矿池未开启'
        ]);
    }
}
