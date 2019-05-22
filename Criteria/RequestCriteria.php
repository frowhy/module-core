<?php
/**
 * Created by PhpStorm.
 * User: Govern Fu
 * Date: 2019/3/11
 * Time: 上午10:11
 */

namespace Modules\Core\Criteria;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class RequestCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param Builder|Model $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $model = $this->setSearch($model, $repository);
        $model = $this->setOrderBy($model);
        $model = $this->setFilter($model);
        $model = $this->setWith($model);

        return $model;
    }

    /**
     * @param $search
     *
     * @return array
     */
    protected function parserSearchData($search)
    {
        $searchData = [];

        if (stripos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     * @return string|null
     */
    protected function parserSearchValue($search)
    {

        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    /**
     * @param array $fields
     * @param array|null $searchFields
     * @return array
     * @throws \Exception
     */
    protected function parserFieldsSearch(array $fields = [], array $searchFields = null)
    {
        if (!is_null($searchFields) && count($searchFields)) {
            $acceptedConditions = config('repository.criteria.acceptedConditions', [
                '=',
                'like',
            ]);

            $fields = $this->parserSearchFields($fields, $searchFields, $acceptedConditions);

            $this->fieldsAccept($fields);
        }

        return $fields;
    }

    /**
     * @param $fields
     * @throws \Exception
     */
    protected function fieldsAccept($fields)
    {
        if (count($fields) == 0) {
            throw new Exception((string) trans('repository::criteria.fields_not_accepted', ['field' => implode(',', $searchFields)]));
        }
    }

    protected function getSubqueryClosure($modelTableName, $field, $condition, $value)
    {
        switch ($condition) {
            case 'in':
                return function (Builder $query) use ($modelTableName, $field, $value) {
                    $query->whereIn($modelTableName.'.'.$field, $value);
                };
            case 'between':
                return function (Builder $query) use ($modelTableName, $field, $value) {
                    $query->whereBetween($modelTableName.'.'.$field, $value);
                };
            case 'cross':
                return function (Builder $query) use ($modelTableName, $field, $value) {
                    $query->where(function (Builder $query) use ($modelTableName, $field, $value) {
                        $query->where(function (Builder $query) use ($modelTableName, $field, $value) {
                            $query->where("{$modelTableName}.{$field}_min", '<=', $value[0])
                                  ->where("{$modelTableName}.{$field}_max", '>=', $value[1]);
                        })->orWhere(function (Builder $query) use ($modelTableName, $field, $value) {
                            $query->where("{$modelTableName}.{$field}_min", '<=', $value[0])
                                  ->where("{$modelTableName}.{$field}_max", '>=', $value[0]);
                        })->orWhere(function (Builder $query) use ($modelTableName, $field, $value) {
                            $query->where("{$modelTableName}.{$field}_min", '>=', $value[0])
                                  ->where("{$modelTableName}.{$field}_max", '<=', $value[1]);
                        })->orWhere(function (Builder $query) use ($modelTableName, $field, $value) {
                            $query->where("{$modelTableName}.{$field}_min", '>=', $value[0])
                                  ->where("{$modelTableName}.{$field}_max", '>=', $value[1])
                                  ->where("{$modelTableName}.{$field}_min", '<=', $value[1]);
                        });
                    });
                };
            default:
                return function (Builder $query) use ($modelTableName, $field, $condition, $value) {
                    $query->orWhere($modelTableName.'.'.$field, $condition, $value);
                };
        }
    }

    protected function getQueryClosure($field, $condition, $value)
    {
        switch ($condition) {
            case 'in':
                return function (Builder $query) use ($field, $value) {
                    $query->whereIn($field, $value);
                };
            case 'between':
                return function (Builder $query) use ($field, $value) {
                    $query->whereBetween($field, $value);
                };
            case 'cross':
                return function (Builder $query) use ($field, $value) {
                    $query->where(function (Builder $query) use ($field, $value) {
                        $query->where(function (Builder $query) use ($field, $value) {
                            $query->where("{$field}_min", '<=', $value[0])
                                  ->where("{$field}_max", '>=', $value[1]);
                        })->orWhere(function (Builder $query) use ($field, $value) {
                            $query->where("{$field}_min", '<=', $value[0])
                                  ->where("{$field}_max", '>=', $value[0]);
                        })->orWhere(function (Builder $query) use ($field, $value) {
                            $query->where("{$field}_min", '>=', $value[0])
                                  ->where("{$field}_max", '<=', $value[1]);
                        })->orWhere(function (Builder $query) use ($field, $value) {
                            $query->where("{$field}_min", '>=', $value[0])
                                  ->where("{$field}_max", '>=', $value[1])
                                  ->where("{$field}_min", '<=', $value[1]);
                        });
                    });
                };
            default:
                return function (Builder $query) use ($field, $condition, $value) {
                    $query->where($field, $condition, $value);
                };
        }
    }

    protected function getRelationQueryClosure($field, $condition, $value)
    {
        return function (Builder $query) use (
            $field,
            $condition,
            $value
        ) {
            $query->where($this->getQueryClosure($field, $condition, $value));
        };
    }

    protected function setValue($condition, $field, $search, $searchData)
    {
        if (isset($searchData[$field])) {
            $value = $this->setSearchDataValue($condition, $field, $searchData);
        } else {
            $value = $this->setSearchValue($condition, $search);
        }

        return $value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @return mixed
     */
    protected function setOrderBy($model)
    {
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';

        if (isset($orderBy) && !empty($orderBy)) {
            $split = explode('|', $orderBy);
            if (count($split) > 1) {
                $model = $this->sort($model, $split, $sortedBy);
            } else {
                $model = $model->orderBy($orderBy, $sortedBy);
            }
        }

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @return mixed
     */
    protected function setFilter($model)
    {
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @return mixed
     */
    protected function setWith($model)
    {
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);

        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param \Prettus\Repository\Contracts\RepositoryInterface $repository
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    protected function setSearch($model, RepositoryInterface $repository)
    {
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);

        $fieldsSearchable = $repository->getFieldsSearchable();

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {

            $searchFields =
                is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';', $searchFields);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            $model =
                $model->where(function (Builder $query) use (
                    $fields,
                    $search,
                    $searchData,
                    $isFirstField,
                    $modelForceAndWhere
                ) {
                    foreach ($fields as $field => $condition) {
                        if (is_numeric($field)) {
                            $field = $condition;
                            $condition = "=";
                        }
                        $condition = trim(strtolower($condition));
                        $value = $this->setValue($condition, $field, $search, $searchData);
                        $relation = null;
                        if (stripos($field, '.')) {
                            $explode = explode('.', $field);
                            $field = array_pop($explode);
                            $relation = implode('.', $explode);
                        }

                        $query->where($this->searchQueryClosure($isFirstField, $modelForceAndWhere, $relation, $field, $condition, $value));
                    }
                });
        }

        return $model;
    }

    protected function setSearchDataValue($condition, $field, $searchData)
    {
        $value = $searchData[$field];
        if ($condition == "like" || $condition == "ilike") {
            $value = "%{$value}%";
        }
        if ($condition == "in" || $condition == "between" || $condition == "cross") {
            $value = explode(',', $value);
        }

        return $value;
    }

    protected function setSearchValue($condition, $search)
    {
        $value = null;

        if (!is_null($search)) {
            $value = $search;
            if ($condition == "like" || $condition == "ilike") {
                $value = "%{$value}%";
            }
            if ($condition == "in" || $condition == "between" || $condition == "cross") {
                $value = explode(',', $value);
            }
        }

        return $value;
    }

    protected function parserSearchFields($fields, $searchFields, $acceptedConditions)
    {
        $originalFields = $fields;
        $fields = [];

        foreach ($searchFields as $index => $field) {
            $field_parts = explode(':', $field);
            $temporaryIndex = array_search($field_parts[0], $originalFields);

            if (count($field_parts) == 2 && in_array($field_parts[1], $acceptedConditions)) {
                unset($originalFields[$temporaryIndex]);
                $field = $field_parts[0];
                $condition = $field_parts[1];
                $originalFields[$field] = $condition;
                $searchFields[$index] = $field;
            }
        }

        foreach ($originalFields as $field => $condition) {
            if (is_numeric($field)) {
                $field = $condition;
                $condition = "=";
            }
            if (in_array($field, $searchFields)) {
                $fields[$field] = $condition;
            }
        }

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param $split
     * @param $sortedBy
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function sort($model, $split, $sortedBy)
    {
        $table = $model->getModel()->getTable();
        $sortTable = $split[0];
        $sortColumn = $split[1];

        $split = explode(':', $sortTable);
        if (count($split) > 1) {
            $sortTable = $split[0];
            $keyName = $table.'.'.$split[1];
        } else {
            $prefix = Str::singular($sortTable);
            $keyName = $table.'.'.$prefix.'_id';
        }

        return $model
            ->leftJoin($sortTable, $keyName, '=', $sortTable.'.id')
            ->orderBy($sortColumn, $sortedBy)
            ->addSelect($table.'.*');
    }

    protected function searchQueryClosure(&$isFirstField, $modelForceAndWhere, $relation, $field, $condition, $value)
    {
        return function (Builder $query) use (
            $isFirstField,
            $modelForceAndWhere,
            $relation,
            $field,
            $condition,
            $value
        ) {
            $modelTableName = $query->getModel()->getTable();
            if ($isFirstField || $modelForceAndWhere) {
                if (!is_null($value)) {
                    if (!is_null($relation)) {
                        $query->whereHas($relation, $this->getRelationQueryClosure($field, $condition, $value));
                    } else {
                        $query->where($this->getQueryClosure($field, $condition, $value));
                    }
                    $isFirstField = false;
                }
            } else {
                if (!is_null($value)) {
                    if (!is_null($relation)) {
                        $query->orWhereHas($relation, $this->getRelationQueryClosure($field, $condition, $value));
                    } else {
                        $query->orWhere($this->getSubqueryClosure($modelTableName, $field, $condition, $value));
                    }
                }
            }
        };
    }
}
