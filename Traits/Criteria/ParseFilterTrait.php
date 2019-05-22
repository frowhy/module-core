<?php


namespace Modules\Core\Traits\Criteria;

trait ParseFilterTrait
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

    protected function parseFilter()
    {
        $this->filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);

        if (isset($this->filter) && !empty($this->filter)) {
            if (is_string($this->filter)) {
                $this->filter = explode(';', $this->filter);
            }

            $this->model = $this->model->select($this->filter);
        }
    }
}