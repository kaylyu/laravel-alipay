<?php

namespace Kaylyu\Alipay\F2fpay\Notify;

use Closure;
use Exception;
use Kaylyu\Alipay\F2fpay\Kernel\AopClient;

class Paid extends Handler
{
    /**
     * 入口
     * @param Closure $closure
     * @author kaylv <kaylv@dayuw.com>
     * @return string
     */
    public function handle(Closure $closure)
    {
        $this->strict(
            \call_user_func($closure, $this->getMessage(), [$this, 'fail'])
        );

        return $this->toResponse();
    }

    /**
     * 验签过程
     * @param $message
     * @author kaylv <kaylv@dayuw.com>
     * @return bool
     * @throws Exception
     */
    public function validate($message)
    {
        //获取当面付配置
        $f2fpay = $this->app->getF2fpay();

        //获取配置参数
        $gatewayUrl = $f2fpay['gateway_url'];
        $appId = $f2fpay['app_id'];
        $signType = $f2fpay['sign_type'];
        $rsaPrivateKey = $f2fpay['merchant_private_key'];
        $alipayPublicKey = $f2fpay['alipay_public_key'];
        $charset = $f2fpay['charset'];
        $notifyUrl = $f2fpay['notify_url'];

        if (empty($appId) || trim($appId) == "") {
            throw new Exception("appid should not be NULL!");
        }
        if (empty($rsaPrivateKey) || trim($rsaPrivateKey) == "") {
            throw new Exception("merchant_private_key should not be NULL!");
        }
        if (empty($alipayPublicKey) || trim($alipayPublicKey) == "") {
            throw new Exception("alipay_public_key should not be NULL!");
        }
        if (empty($charset) || trim($charset) == "") {
            throw new Exception("charset should not be NULL!");
        }
        if (empty($notifyUrl) || trim($notifyUrl) == "") {
            throw new Exception("sign_type should not be NULL");
        }
        if (empty($gatewayUrl) || trim($gatewayUrl) == "") {
            throw new Exception("gateway_url should not be NULL");
        }
        if (empty($signType) || trim($signType) == "") {
            throw new Exception("sign_type should not be NULL");
        }

        //组装请求数据
        $aop = new AopClient();
        $aop->gatewayUrl = $gatewayUrl;
        $aop->appId =$appId;
        $aop->signType = $signType;
        $aop->rsaPrivateKey = $rsaPrivateKey;
        $aop->alipayrsaPublicKey = $alipayPublicKey;
        $aop->apiVersion = "1.0";
        $aop->charset = $charset;
        $aop->format = 'json';

        //验签
        return $aop->rsaCheckV2($message, null);
    }
}