<?php


namespace Modules\Core\Traits\Criteria;

trait ParseSearchableTrait
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

    use ParseFieldsSearchTrait;
    use ParseSearchWhereTrait;
    use ParseSearchDataTrait;
    use ParseSearchTrait;
    use ParseForceAndWhereTrait;
    use ParseValueTrait;

    /**
     * @throws \Exception
     */
    protected function parseSearchable()
    {
        $this->fieldsSearchable = $this->repository->getFieldsSearchable();
        $this->search = $this->request->get(config('repository.criteria.params.search', 'search'), null);

        if ($this->search && is_array($this->fieldsSearchable) && count($this->fieldsSearchable)) {

            $this->parseFieldsSearch();
            $this->parseSearchData();
            $this->parseSearch();
            $this->parseForceAndWhere();

            foreach ($this->fields as $field => $condition) {

                $this->getConditionField($field, $condition);
                $value = $this->parseValue($condition, $field);
                $relation = null;
                $this->getRelationField($field, $relation);
                $this->parseSearchWhere($value, $relation, $field, $condition);
            }
        }
    }

    protected function getConditionField(&$field, &$condition)
    {
        if (is_numeric($field)) {
            $field = $condition;
            $condition = "=";
        }
    }

    protected function getRelationField(&$field, &$relation)
    {
        if (stripos($field, '.')) {
            $explode = explode('.', $field);
            $field = array_pop($explode);
            $relation = implode('.', $explode);
        }
    }
}