<?php

namespace Chriswest101\Paynow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Chriswest101\Paynow\Skeleton\SkeletonClass
 */
class PaynowFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Chriswest101\Paynow\PayNow::class;
    }
}
