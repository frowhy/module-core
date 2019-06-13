<?php

namespace Modules\Core\Traits\Criteria;

use Illuminate\Support\Str;

trait ParseOrderByTrait
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

    protected function parseOrderBy()
    {
        $this->orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $this->sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $this->sortedBy = !empty($this->sortedBy) ? $this->sortedBy : 'asc';

        if (isset($this->orderBy) && !empty($this->orderBy)) {
            $split = explode('|', $this->orderBy);

            if (count($split) > 1) {
                $this->multiOrderBy($split);
            } else {
                $this->orderBy();
            }
        }
    }

    protected function orderBy()
    {
        $this->model = $this->model->orderBy($this->orderBy, $this->sortedBy);
    }

    protected function multiOrderBy($split)
    {
        $table = $this->model->getModel()->getTable();
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

        $this->model = $this->model->leftJoin($sortTable, $keyName, '=', $sortTable.'.id')
                                   ->orderBy($sortColumn, $this->sortedBy)
                                   ->addSelect($table.'.*');
    }
}
