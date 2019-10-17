<?php
/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/8/30
 * Time: 11:22
 */

namespace Kaylyu\Alipay\Tests\Notify;


use Kaylyu\Alipay\F2fpay\Application;
use Kaylyu\Alipay\Tests\TestCase;

class ClientTest extends TestCase
{
    protected $application;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->application = new Application(
            [
                'response_type' => 'collection',//collection array json object
                'log' => [
                    'file' => __DIR__ . '/logs/kaylu-alipay.log',
                    'level' => 'debug',
                ],
                'http' => [
                    'timeout' => 30,
                ],
                //当面付
                'f2fpay' => [
                    //签名方式,默认为RSA2(RSA2048)
                    'sign_type' => "RSA2",

                    //支付宝公钥
                    'alipay_public_key' => "",

                    //商户私钥
                    'merchant_private_key' => "",

                    //编码格式
                    'charset' => "UTF-8",

                    //支付宝网关
                    'gateway_url' => "https://openapi.alipaydev.com/gateway.do",

                    //应用ID
                    'app_id' => "",

                    //异步通知地址,只有扫码支付预下单可用
                    'notify_url' => "http://www.baidu.com",
                ]
            ]
        );
    }

    /**
     * @author kaylv <kaylv@dayuw.com>
     */
    public function Product()
    {

    }
}