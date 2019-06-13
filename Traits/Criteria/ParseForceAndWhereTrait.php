<?php

namespace Modules\Core\Traits\Criteria;

trait ParseForceAndWhereTrait
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
    protected $searchClosures;

    protected function parseForceAndWhere()
    {
        $this->searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), 'and');

        $this->modelForceAndWhere = strtolower($this->searchJoin) === 'and';
    }
}
