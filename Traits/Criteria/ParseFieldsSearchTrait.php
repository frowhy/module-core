<?php


namespace Modules\Core\Traits\Criteria;


use Exception;

trait ParseFieldsSearchTrait
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

    use ParseSearchFieldsTrait;

    /**
     * @throws \Exception
     */
    protected function parseFieldsSearch()
    {
        $this->parseSearchFields();

        $fields = $this->fieldsSearchable;

        if (!is_null($this->searchFields) && is_array($this->searchFields)) {

            $this->parseOriginalFields();

            $fields = [];

            foreach ($this->originalFields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }
                if (in_array($field, (array) $this->searchFields)) {
                    $fields[$field] = $condition;
                }
            }

            $this->assertFieldsNotAccepted($fields);
        }

        $this->fields = $fields;
    }

    protected function parseOriginalFields()
    {
        $this->acceptedConditions = config('repository.criteria.acceptedConditions', ['=', 'like']);

        $this->originalFields = $this->fieldsSearchable;

        foreach ($this->searchFields as $index => $field) {
            $field_parts = explode(':', $field);
            $temporaryIndex = array_search($field_parts[0], $this->originalFields);

            if (count($field_parts) == 2) {
                if (in_array($field_parts[1], $this->acceptedConditions)) {
                    unset($this->originalFields[$temporaryIndex]);
                    $field = $field_parts[0];
                    $condition = $field_parts[1];
                    $this->originalFields[$field] = $condition;
                    $this->searchFields[$index] = $field;
                }
            }
        }
    }

    /**
     * @param array $fields
     * @throws \Exception
     */
    protected function assertFieldsNotAccepted($fields)
    {
        if (count($fields) == 0) {
            throw new Exception((string) trans('repository::criteria.fields_not_accepted', ['field' => implode(',', (array) $this->searchFields)]));
        }
    }
}