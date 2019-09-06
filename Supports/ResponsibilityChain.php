<?php
/**
 * Created by PhpStorm.
 * User: frowhy
 * Date: 2019-03-08
 * Time: 11:23.
 */

namespace Modules\Core\Supports;

use Closure;
use Exception;

class ResponsibilityChain
{
    private $isException = false;
    private $isContinue = false;
    private $result = null;
    private $lastResult = null;

    public function append(Closure $result, bool $isLastResult = false, bool $isContinue = false): self
    {
        if (!$this->isException || $this->isContinue) {
            $this->isContinue = $isContinue;

            try {
                $this->result = $result($this->result);
            } catch (Exception $exception) {
                $this->result = Handler::renderException($exception);
            }

            if ($isLastResult) {
                $this->lastResult = $this->result;
            }

            if (!$this->result->isContinue()) {
                $this->isException = true;
            }
        }

        return $this;
    }

    public function handle(): Response
    {
        if (!is_null($this->lastResult)) {
            return $this->lastResult;
        }

        return $this->result;
    }
}
