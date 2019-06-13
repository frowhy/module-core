<?php

namespace Modules\Core\Traits\Criteria;

trait ParseSearchFieldsTrait
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
    protected $searchClosures;

    protected function parseSearchFields()
    {
        $this->searchFields =
            $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);

        if (!is_array($this->searchFields) && !is_null($this->searchFields)) {
            $this->searchFields = explode(';', $this->searchFields);
        }
    }
}
