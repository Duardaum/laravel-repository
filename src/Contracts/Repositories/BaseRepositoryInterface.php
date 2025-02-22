<?php

namespace Duardaum\LaravelRepository\Contracts\Repositories;


interface BaseRepositoryInterface
{

    public function getTableName(): string;

    public function create(array $data): \Illuminate\Database\Eloquent\Model;

    public function createMany(array $data): bool;

    /**
     * Import CSV file data to table
     *
     * @param string $path CSV file path
     * @param array<string, int>|array<empty, empty> $columns To-From key-value: To table column, From CSV column
     * @param ?callable $rowGenerate Callable to generate (transform) each row to insert on table
     * @param \stdClass|array|null $options Aditional info and settings params
     *
     * @return int Number of the batch inserted
     */
    public function importFile(string $path, array $columns, ?callable $rowGenerate = null, null|\stdClass|array $options = null): int;

    public function update(array $data, int|string $id): int;

    public function updateOrCreate(array $data, array $condition): \Illuminate\Database\Eloquent\Model;

    /**
     * @param int|string|array<int> $id
    */
    public function delete(int|string|array $id): int;

    public function deleteWhere(array $where): int;

    /**
     * @param int|string|array<int> $id
     */
    public function forceDelete(int|string|array $id): int;

    public function forceDeleteWhere(array $where): int;

    /**
     * @param int|string|array<int> $id
     */
    public function restore(int|string|array $id): int;

    public function restoreWhere(array $where): int;

    public function builder(): \Illuminate\Database\Eloquent\Builder;

    public function newQuery(): self;

    public function withTrashed(): self;

    public function onlyTrashed(): self;

    public function all(array $columns = array('*')): \Illuminate\Database\Eloquent\Collection;

    /**
     * @param int|string|array<int> $id
     * @param array $columns Selected columns from database
     */
    public function find(int|string|array $id, array $columns = ['*']): \Illuminate\Database\Eloquent\Model|null;

    public function findFirst(array $where, array $columns = ['*']); //: \Illuminate\Database\Eloquent\Model|null;

    public function findByField(string $field, mixed $value, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhere(array $where, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhereLimit(array $where, int $limit = 10, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhereIn(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhereNotIn(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhereBetween(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findWhereNotBetween(string $field, array $values, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findOrderBy(array $where, string $order_field, string $order_direction = 'asc', array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    public function findOrderByFirst(array $where, string $order_field, string $order_direction = 'asc', array $columns = ['*']): \Illuminate\Database\Eloquent\Model|null;

    public function findWherePaginate(array $where, int $perPage = 20, array $columns = ['*'], string $pageName = null, int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function paginate(int $perPage = 20, array $columns = ['*'], string $pageName = null, int $page = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

}
