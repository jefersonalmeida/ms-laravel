<?php


namespace Tests\Traits;


trait TestProd
{
    protected function skipTestIfNotProd($message = '')
    {
        if (!$this->isTestingProd()) {
            $this->markTestSkipped($message);
        }
    }

    protected function isTestingProd(): bool
    {
        return env('TESTING_PROD', true) !== false;
    }
}
