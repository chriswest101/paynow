<?php

namespace Chriswest101\Paynow\Tests;

use Orchestra\Testbench\TestCase;
use Chriswest101\Paynow\PaynowServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PaynowServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
