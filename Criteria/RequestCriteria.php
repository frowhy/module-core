<?php
/**
 * Created by PhpStorm.
 * User: guoliang
 * Date: 2019/3/11
 * Time: 上午10:11
 */

namespace Modules\Core\Criteria;


use Illuminate\Http\Request;
use Modules\Core\Traits\Criteria\{
    ParseFilterTrait,
    ParseOrderByTrait,
    ParseSearchableTrait,
    ParseWithTrait
};
use Prettus\Repository\Contracts\{
    CriteriaInterface,
    RepositoryInterface
};

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
    protected $isFirstField = true;
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

    use ParseSearchableTrait;
    use ParseOrderByTrait;
    use ParseFilterTrait;
    use ParseWithTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder $model
     * @param \Prettus\Repository\Contracts\RepositoryInterface $repository
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;
        $this->repository = $repository;

        $this->parseSearchable();
        $this->parseOrderBy();
        $this->parseFilter();
        $this->parseWith();

        return $this->model;
    }
}
