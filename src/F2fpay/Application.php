<?php

namespace Kaylyu\Alipay\F2fpay;

use Closure;
use Kaylyu\Alipay\Kernel\ServiceContainer;

/**
 * @property Order\Client  $order
 * @property Refund\Client  $refund
 *
 * Class Application.
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Order\ServiceProvider::class,
        Refund\ServiceProvider::class,
    ];

    /**
     * 订单支付成功通知
     * @param Closure $closure
     * @author kaylv <kaylv@dayuw.com>
     * @return string
     */
    public function handlePaid(Closure $closure)
    {
        return (new Notify\Paid($this))->handle($closure);
    }
}
