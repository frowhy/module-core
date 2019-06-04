<?php
/**
 * Created by PhpStorm.
 * User: frowhy
 * Date: 2017/12/6
 * Time: 上午10:30.
 */

namespace Modules\Core\Abstracts;

use League\Fractal\TransformerAbstract as BaseTransformerAbstract;

abstract class TransformerAbstract extends BaseTransformerAbstract
{
    protected $transform;
    protected $field;
    protected $filter;

    abstract public function fields($attribute);

    public function transform($transform)
    {
        $this->transform = $transform;
        $this->field = $this->fields($transform);

        return $this->field;
    }
}
