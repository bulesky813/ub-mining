<?php

namespace App\Services\Base;

use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Collection;

trait BaseModelService
{
    public function factory(string $modelClass): BaseModelService
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function queryFormat($model, string $column_name, $value): Builder
    {
        return $model->when($value, function ($query) use ($value, $column_name) {
            if (is_array($value)) {
                $condition = $value['condition'] ?? '=';
                $data = $value['data'] ?? '';
                if ($condition == 'in') {
                    if (is_array($data)) {
                        $query->whereIn($column_name, $data);
                    }
                } elseif ($condition == 'or' || $condition == 'function') {
                    if (is_callable($data)) {
                        $query->where(function ($query) use ($data) {
                            $data($query);
                        });
                    }
                } else {
                    if ($data) {
                        $query->where($column_name, $condition, $data);
                    }
                }
            } else {
                $query->where($column_name, $value);
            }
        });
    }

    public function create(array $attr): ?Model
    {
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        foreach ($attr as $column_name => $value) {
            $model->$column_name = $value;
        }
        $model->save();
        return $model;
    }

    public function update(array $condition, array $attr): int
    {
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        foreach ($condition as $column_name => $value) {
            $model = $this->queryFormat($model, $column_name, $value);
        }
        return $model->update($attr);
    }

    public function get(
        array $condition = []
    ): ?Model {
        $query = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        foreach ($condition as $column_name => $data) {
            $query = $this->queryFormat($query, $column_name, $data);
        }
        return $query->first();
    }

    public function findByAttr(array $attr): Collection
    {
        $attr = new Collection($attr);
        $start_at = $attr->get('start_at');
        $end_at = $attr->get('end_at');
        $pn = $attr->get('pn', 1);
        $order = $attr->get('order', '');
        $paginate = $attr->get('paginate', false);
        $ps = $attr->get('ps', 20);
        $chunk = $attr->get('chunk', null);
        $attr->forget(['order', 'sort', 'pn', 'ps', 'paginate', 'start_at', 'end_at', 'chunk']);
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        $model = $model->when($start_at && $end_at, function ($query) use ($start_at, $end_at) {
            $query->whereBetween(
                'created_at',
                [Carbon::parse($start_at)->toDateTimeString(), Carbon::parse($end_at)->toDateTimeString()]
            );
        });
        $attr->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $model = $model->when($order, function ($query) use ($order) {
            foreach (explode(',', $order) as $order_str) {
                list($order_column_name, $order_by) = explode(" ", $order_str);
                $query->orderBy($order_column_name, $order_by);
            }
        });
        if ($paginate) {
            $model = $model->offset(($pn - 1) * $ps);
        }
        if ($paginate == false && is_callable($chunk)) {
            $export_num = 0;
            $model->chunk(1000, function ($orders) use ($chunk, &$export_num) {
                call_user_func($chunk, $orders);
                $export_num += 1000;
                if ($export_num > config('app.export_num')) {
                    return false;
                }
            });
            return collect([]);
        }
        return $model->limit($ps)->get();
    }

    public function sum(array $sum_column_names, array $attr): Model
    {
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        collect($attr)->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $data = $model->first(collect($sum_column_names)->map(function ($sum_column_name, $aliases) {
            return DB::raw(sprintf("SUM(%s) as %s", $sum_column_name, $aliases));
        })->toArray());
        return $data;
    }

    public function count(array $sum_column_names, array $attr): Model
    {
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        collect($attr)->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $data = $model->first(collect($sum_column_names)->map(function ($sum_column_name, $aliases) {
            return DB::raw(sprintf("count(%s) as %s", $sum_column_name, $aliases));
        })->toArray());
        return $data;
    }
}
