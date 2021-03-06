<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Request\AbstractRequest;
use App\Services\Mine\MinePoolService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;
use Hyperf\Di\Annotation\Inject;

class UserChangeAssetsRequest extends AbstractRequest
{

    /**
     * @Inject
     * @var UserWarehouseRecordService
     */
    protected $uwrs;

    /**
     * @Inject
     * @var UserWarehouseService
     */
    protected $uws;

    /**
     * @Inject
     * @var SeparateWarehouseService
     */
    protected $sws;

    /**
     * @Inject
     * @var MinePoolService
     */
    protected $mps;

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
                'exists:users,id'
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
                    $value = (string)$value;
                    if (bccomp($value, '0') == 0) {
                        return $fail('持仓变动量不能为 0');
                    }
                    $user_id = (int)$this->input('user_id');
                    $coin_symbol = (string)$this->input('coin_symbol');
                    $separate_warehouse_sort = (int)$this->input('separate_warehouse_sort');
                    $user_warehouse = $this->uws->userWarehouse($user_id, $coin_symbol); //获取用户所有持仓
                    if ($user_warehouse->isEmpty() && $separate_warehouse_sort > 1) {
                        return $fail('只能从1号仓开始加仓!');
                    }
                    $symbol_separate_warehouse = $this->sws->separateWarehouse($coin_symbol);//获取当前操作仓位的详细
                    $currency_separate_warehouse = $symbol_separate_warehouse
                        ->firstWhere('sort', $separate_warehouse_sort);//当前操作仓位
                    if (!$currency_separate_warehouse) {
                        return $fail('当前操作仓位不存在!');
                    }
                    $new_assets = bcadd(
                        $user_warehouse->get($separate_warehouse_sort - 1)->assets ?? '0',
                        $value
                    );
                    if (bccomp($value, '0') == 1) {
                        if ($separate_warehouse_sort > $user_warehouse->count() + 1) {
                            return $fail(sprintf("不能对超过%d号的仓位加仓！", $user_warehouse->count() + 1));
                        }
                        for ($i = 0; $i < $user_warehouse->count() - 1; $i++) {
                            $last_separate_warehouse = $symbol_separate_warehouse
                                ->firstWhere('sort', $i + 1); //仓位
                            $warehouse_assets = $user_warehouse->get($i)
                                ? $user_warehouse->get($i)->assets : '0';
                            if ($last_separate_warehouse->sort == $separate_warehouse_sort) {
                                $warehouse_assets = bcadd($warehouse_assets, $value);
                            }
                            if ($this->mps->raiseCondition($coin_symbol) == 2) {//仓位必须加满、必须满足最高值
                                if ($warehouse_assets < $last_separate_warehouse->high) {
                                    return $fail(sprintf('必须加满%d号仓!', $last_separate_warehouse->sort));
                                }
                            } else {
                                if ($warehouse_assets < $last_separate_warehouse->low) {//仓位不需要加满、必须满足最低值
                                    return $fail(sprintf('%d号仓必须满足最低要求!', $last_separate_warehouse->sort));
                                }
                            }
                        }
                        if (bccomp($new_assets, (string)$currency_separate_warehouse->low, 0) <= 0) {
                            return $fail(sprintf("该仓位最低持仓量必须大于 %s", $currency_separate_warehouse->low));
                        }
                        if (bccomp($new_assets, (string)$currency_separate_warehouse->high) > 0) {
                            return $fail(sprintf("该仓位最大持仓量必须小于或等于 %s", $currency_separate_warehouse->high));
                        }
                    } else {
                        $today_revoke_record = $this->uwrs->todayRevoke($user_id, $coin_symbol);
                        if ($today_revoke_record) {
                            return $fail('撤仓超过限制');
                        }
                        if ($separate_warehouse_sort < $user_warehouse->count()) {
                            return $fail(sprintf('必须从%d号仓位开始撤仓', $user_warehouse->count()));
                        }
                        if (bccomp($new_assets, '0') == -1) {
                            return $fail(sprintf('仓位总数量不能小于 0'));
                        } elseif (bccomp($new_assets, '0') == 1) {//剩余持仓量
                            if (bccomp($new_assets, (string)$currency_separate_warehouse->low, 0) <= 0) {
                                return $fail(sprintf("该仓位最低持仓量必须大于 %s", $currency_separate_warehouse->low));
                            }
                        }
                    }
                }
            ]
        ];
    }
}
