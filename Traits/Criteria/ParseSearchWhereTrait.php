<?php

namespace Modules\Core\Traits\Criteria;

trait ParseSearchWhereTrait
{
    /** @var \Illuminate\Http\Request $request */
    protected $request;
    /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder $model */
    protected $model;
    /** @var \Prettus\Repository\Contracts\RepositoryInterface $repository */
    protected $repository;
    protected $search;
    protected $searchData;
    protected $searchFields;
    protected $isFirstField;
    protected $modelForceAndWhere;
    protected $fieldsSearchable;
    protected $fields;
    protected $filter;
    protected $orderBy;
    protected $sortedBy;
    protected $with;
    protected $searchJoin;
    protected $acceptedConditions;
    protected $originalFields;
    protected $crossMin;
    protected $crossMax;

    use ParseSearchAndClosureTrait;
    use ParseSearchAndRelationClosureTrait;
    use ParseSearchOrClosureTrait;
    use ParseSearchOrRelationClosureTrait;

    protected function parseSearchWhere($value, $relation, $field, $condition)
    {
        if ($this->isFirstField || $this->modelForceAndWhere) {
            $this->parseSearchAndWhere($value, $relation, $field, $condition);
        } else {
            $this->parseSearchOrWhere($value, $relation, $field, $condition);
        }
    }

    protected function parseSearchOrWhere($value, $relation, $field, $condition)
    {
        if (!is_null($value)) {
            if (!is_null($relation)) {
                $this->parseSearchOrRelationClosure($value, $relation, $field, $condition);
            } else {
                $this->parseSearchOrClosure($value, $field, $condition);
            }
        }
    }

    protected function parseSearchAndWhere($value, $relation, $field, $condition)
    {
        if (!is_null($value)) {
            if (!is_null($relation)) {
                $this->parseSearchAndRelationClosure($value, $relation, $field, $condition);
            } else {
                $this->parseSearchAndClosure($value, $field, $condition);
            }
            $this->isFirstField = false;
        }
    }
}
