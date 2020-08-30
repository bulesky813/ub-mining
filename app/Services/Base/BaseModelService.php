<?php

namespace App\Services\Base;

use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

trait BaseModelService
{
    protected function factory(string $modelClass): BaseModelService
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    protected function queryFormat($model, string $column_name, $value): Builder
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
                        $data($query);
                    }
                } else {
                    if ($data) {
                        $query->where($column_name, $condition, $data);
                    }
                }
            } elseif (is_callable($value)) {
                $value($query);
            } else {
                $query->where($column_name, $value);
            }
        });
    }

    protected function create(array $attr): ?Model
    {
        $model = (new \ReflectionClass($this->modelClass))->newInstance();
        foreach ($attr as $column_name => $value) {
            $model->$column_name = $value;
        }
        $model->save();
        return $model;
    }

    protected function update(array $condition, array $attr): int
    {
        $model = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        foreach ($condition as $column_name => $value) {
            $model = $this->queryFormat($model, $column_name, $value);
        }
        return $model->update($attr);
    }

    protected function get(
        array $condition = []
    ): ?Model {
        $query = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        $order = Arr::get($condition, 'order', '');
        Arr::forget($condition, ['order']);
        foreach ($condition as $column_name => $data) {
            $query = $this->queryFormat($query, $column_name, $data);
        }
        $query = $query->when($order, function ($query) use ($order) {
            foreach (explode(',', $order) as $order_str) {
                list($order_column_name, $order_by) = explode(" ", $order_str);
                $query->orderBy($order_column_name, $order_by);
            }
        });
        return $query->first();
    }

    protected function findByAttr(array $attr): Collection
    {
        $attr = new Collection($attr);
        $order = $attr->get('order', '');
        $paginate = $attr->get('paginate', false);
        $pn = $attr->get('pn', 1);
        $ps = $attr->get('ps', 20);
        $chunk = $attr->get('chunk', null);
        $select = $attr->get('select', []);
        $with = $attr->get('with', []);
        $attr->forget(['order', 'pn', 'ps', 'paginate', 'chunk', 'select', 'with']);
        $model = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        $model = $model->when($select, function ($query) use ($select) {
            $query->select($select);
        });
        $model = $model->when($with, function ($query) use ($with) {
            $query->with($with);
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
            $model = $model->offset(($pn - 1) * $ps)->limit($ps);
        }
        if ($paginate == false && $chunk) {
            $model->chunk(1000, function ($orders) use ($chunk) {
                call_user_func($chunk, $orders);
            });
            return collect([]);
        }
        return $model->get();
    }

    protected function sum(array $sum_column_names, array $attr): Model
    {
        $model = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        collect($attr)->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $data = $model->first(collect($sum_column_names)->map(function ($sum_column_name, $aliases) {
            return DB::raw(sprintf("SUM(%s) as %s", $sum_column_name, $aliases));
        })->toArray());
        return $data;
    }

    protected function count(array $sum_column_names, array $attr): Model
    {
        $model = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        collect($attr)->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $data = $model->first(collect($sum_column_names)->map(function ($sum_column_name, $aliases) {
            return Db::raw(sprintf("count(%s) as %s", $sum_column_name, $aliases));
        })->toArray());
        return $data;
    }

    protected function max(array $max_column_names, array $attr): Model
    {
        $model = (new \ReflectionMethod($this->modelClass, 'query'))->invoke(null);
        collect($attr)->each(function ($value, $column_name) use (&$model) {
            $model = $this->queryFormat($model, $column_name, $value);
        });
        $data = $model->first(collect($max_column_names)->map(function ($column_name, $aliases) {
            return Db::raw(sprintf("max(%s) as %s", $column_name, $aliases));
        })->toArray());
        return $data;
    }
}
