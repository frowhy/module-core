<?php
/**
 * Created by PhpStorm.
 * User: guoliang
 * Date: 2019/3/11
 * Time: 上午10:11.
 */

namespace Modules\Core\Criteria;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
    protected $searchClosures;

    use ParseSearchableTrait;
    use ParseOrderByTrait;
    use ParseFilterTrait;
    use ParseWithTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->isFirstField = true;

        $this->setCrossSearchClosure();
        $this->setBetweenSearchClosure();
        $this->setInSearchClosure();
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

        $this->parseSearchable();
        $this->parseOrderBy();
        $this->parseFilter();
        $this->parseWith();

        return $this->model;
    }

    public function setSearchClosure(string $condition, Closure $closure)
    {
        $this->searchClosures[$condition] = $closure;
    }

    protected function setCrossSearchClosure()
    {
        $crossMin = config('repository.criteria.cross.min', 'min');
        $crossMax = config('repository.criteria.cross.min', 'max');

        $this->setSearchClosure('cross', function (...$attributes) use ($crossMin, $crossMax) {
            $attributes[0]->where(function (Builder $query) use ($attributes, $crossMin, $crossMax) {
                $query->where("{$attributes[2]}_{$crossMin}", '<=', (int) $attributes[3][0])
                      ->where("{$attributes[2]}_{$crossMax}", '>=', (int) $attributes[3][1]);
            })->orWhere(function (Builder $query) use ($attributes, $crossMin, $crossMax) {
                $query->where("{$attributes[2]}_{$crossMin}", '<=', (int) $attributes[3][0])
                      ->where("{$attributes[2]}_{$crossMax}", '>=', (int) $attributes[3][0]);
            })->orWhere(function (Builder $query) use ($attributes, $crossMin, $crossMax) {
                $query->where("{$attributes[2]}_{$crossMin}", '>=', (int) $attributes[3][0])
                      ->where("{$attributes[2]}_{$crossMax}", '<=', (int) $attributes[3][1]);
            })->orWhere(function (Builder $query) use ($attributes, $crossMin, $crossMax) {
                $query->where("{$attributes[2]}_{$crossMin}", '>=', (int) $attributes[3][0])
                      ->where("{$attributes[2]}_{$crossMax}", '>=', (int) $attributes[3][1])
                      ->where("{$attributes[2]}_{$crossMin}", '<=', (int) $attributes[3][1]);
            });
        });
    }

    protected function setBetweenSearchClosure()
    {
        $this->setSearchClosure('between', function (...$attributes) {
            $attributes[0]->whereBetween($attributes[2], $attributes[3]);
        });
    }

    protected function setInSearchClosure()
    {
        $this->setSearchClosure('in', function (...$attributes) {
            $attributes[0]->whereIn($attributes[2], $attributes[3]);
        });
    }
}
