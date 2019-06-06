<?php
/**
 * Created by PhpStorm.
 * User: guoliang
 * Date: 2019/3/11
 * Time: 上午10:11.
 */

namespace Modules\Core\Criteria;

use Illuminate\Http\Request;
use Modules\Core\Traits\Criteria\ParseCrossTrait;
use Modules\Core\Traits\Criteria\ParseFilterTrait;
use Modules\Core\Traits\Criteria\ParseOrderByTrait;
use Modules\Core\Traits\Criteria\ParseSearchableTrait;
use Modules\Core\Traits\Criteria\ParseWithTrait;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class RequestCriteria implements CriteriaInterface
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

    use ParseSearchableTrait;
    use ParseOrderByTrait;
    use ParseFilterTrait;
    use ParseWithTrait;
    use ParseCrossTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->isFirstField = true;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder $model
     * @param \Prettus\Repository\Contracts\RepositoryInterface                                                            $repository
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;
        $this->repository = $repository;

        $this->parseCross();
        $this->parseSearchable();
        $this->parseOrderBy();
        $this->parseFilter();
        $this->parseWith();

        return $this->model;
    }
}
