<?php


namespace Modules\Core\Traits\Criteria;

trait ParseValueTrait
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

    protected function parseValue($condition, $field)
    {
        $value = null;
        $condition = trim(strtolower($condition));

        if (isset($this->searchData[$field])) {
            $value = $this->parseSearchDataValue($this->searchData[$field], $condition);
        } elseif (!is_null($this->search)) {
            $value = $this->parseSearchDataValue($this->search, $condition);
        }

        return $value;
    }

    protected function parseSearchDataValue($value, $condition)
    {
        switch ($condition) {
            case 'like':
                $value = "%{$value}%";
                break;
            case 'ilike':
                $value = "%{$value}%";
                break;
            case 'in':
                $value = explode(',', $value);
                break;
            case 'between':
                $value = explode(',', $value);
                break;
            case 'cross':
                $value = explode(',', $value);
                break;
        }

        return $value;
    }
}