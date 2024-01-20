<?php

namespace Duardaum\LaravelRepository\Repositories;

use Duardaum\LaravelRepository\Contracts\Repositories\BaseRepositoryInterface;
use JetBrains\PhpStorm\Pure;

abstract class BaseRepository implements BaseRepositoryInterface
{
    private const DEFAULT_PAGINATOR_LIMIT = 20;

    protected string|\Illuminate\Database\Eloquent\Model $_model;

    private \Illuminate\Database\Eloquent\Builder $_query;

    public function __construct()
    {
        $this->_model = $this->resolveModel();
    }

    protected function resolveModel(): \Illuminate\Database\Eloquent\Model
    {
        /**
         * @var \Illuminate\Database\Eloquent\Model $model
        */
        $model = app($this->_model);
        $this->_query = $model->newQuery();
        return $model;
    }

    public function getTableName(): string
    {
        return $this->_model->getTable();
    }

    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->_model->create($data);
    }

    public function createMany(array $data): bool
    {
        return $this->_model->insert($data);
    }

    public function update(array $data, int|string $id): int
    {
        return $this->_model->find($id)->update($data);
    }

    public function updateOrCreate(array $data, array $condition): \Illuminate\Database\Eloquent\Model
    {
        return $this->_model->updateOrCreate($condition, $data);
    }

    public function delete(int|string|array $id): int
    {
        return $this->_model->destroy($id);
    }

    public function deleteWhere(array $where): int
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return $this->_model->where($find)->delete();
    }

    public function forceDelete(int|string|array $id): int
    {
        $d = 0;
        $find = (is_array($id) ? $id : [$id]);
        $this->_model->withTrashed()->find($find)->each(function(\Illuminate\Database\Eloquent\Model $model) use (&$d){
            if($model->forceDelete())
                $d++;
        });
        return $d;
    }

    public function forceDeleteWhere(array $where): int
    {
        $d = 0;
        $find = (is_array($where[0]) ? $where : [$where]);
        $this->_model->withTrashed()->where($find)->each(function(\Illuminate\Database\Eloquent\Model $model) use (&$d){
            if($model->forceDelete())
                $d++;
        });
        return $d;
    }

    public function restore(int|string|array $id): int
    {
        $d = 0;
        $find = (is_array($id) ? $id : [$id]);
        $this->_model->onlyTrashed()->find($find)->each(function($model) use (&$d){
            if($model->restore())
                $d++;
        });
        return $d;
    }

    public function restoreWhere(array $where): int
    {
        $d = 0;
        $find = (is_array($where[0]) ? $where : [$where]);
        $this->_model->onlyTrashed()->where($find)->each(function($model) use (&$d){
            if($model->restore())
                $d++;
        });
        return $d;
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->_model->newQuery();
    }

    public function newQuery(): self
    {
        $this->_query = $this->_model->newQuery();
        return $this;
    }

    public function withTrashed(): self
    {
        $this->_query = $this->_model->withTrashed();
        return $this;
    }

    public function onlyTrashed(): self
    {
        $this->_query = $this->_model->onlyTrashed();
        return $this;
    }

    #[Pure]
    private function getCurrentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->_query->clone();
    }

    public function all(array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->get();
    }

    public function find(int|string|array $id, array $columns = ['*']): \Illuminate\Database\Eloquent\Model|null
    {
        return self::getCurrentQuery()->select($columns)->find($id);
    }

    public function findFirst(array $where, array $columns = ['*'])//: \Illuminate\Database\Eloquent\Model|null
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->select($columns)->where($find)->limit(1)->get()->first();
    }

    public function findByField(string $field, mixed $value, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->where($field, $value)->get();
    }

    public function findWhere(array $where, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->select($columns)->where($find)->get();
    }

    public function findWhereLimit(array $where, int $limit = 10, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->select($columns)->where($find)->limit($limit)->get();
    }

    public function findWhereIn(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->whereIn($field, $values)->get();
    }

    public function findWhereNotIn(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->whereNotIn($field, $values)->get();
    }

    public function findWhereBetween(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->whereBetween($field, $values)->get();
    }

    public function findWhereNotBetween(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->select($columns)->whereNotBetween($field, $values)->get();
    }

    public function findOrderBy(array $where, string $order_field, string $order_direction = 'asc', array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->select($columns)->where($find)->orderBy($order_field, $order_direction)->get();
    }

    public function findOrderByFirst(array $where, string $order_field, string $order_direction = 'asc', array $columns = ['*']): \Illuminate\Database\Eloquent\Model|null
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->select($columns)->where($find)->orderBy($order_field, $order_direction)->limit(1)->get()->first();
    }

    public function findWherePaginate(array $where, int $perPage = self::DEFAULT_PAGINATOR_LIMIT, array $columns = ['*'], string $pageName = null, int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $find = (is_array($where[0]) ? $where : [$where]);
        return self::getCurrentQuery()->where($find)->paginate($perPage, $columns, $pageName, $page);
    }

    public function paginate(int $perPage = self::DEFAULT_PAGINATOR_LIMIT, array $columns = ['*'], string $pageName = null, int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return self::getCurrentQuery()->paginate($perPage, $columns, $pageName, $page);
    }

}
