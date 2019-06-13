<?php

namespace Modules\Core\Traits\Criteria;

trait ParseSearchTrait
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

    protected function parseSearch()
    {
        if (stripos($this->search, ';') || stripos($this->search, ':')) {
            $values = explode(';', $this->search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    $this->search = $s[0];
                }
            }

            $this->search = null;
        }
    }
}
