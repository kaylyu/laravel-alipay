<?php

namespace Kaylyu\Alipay\F2fpay\Refund;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['refund'] = function ($app) {
            return new Client($app);
        };
    }
}
