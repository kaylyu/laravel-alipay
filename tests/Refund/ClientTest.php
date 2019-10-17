<?php
/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/8/30
 * Time: 11:22
 */

namespace Kaylyu\Alipay\Tests\Refund;


use Faker\Factory;
use Kaylyu\Alipay\F2fpay\Application;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeRefundContentBuilder;
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
     * 统一收单交易退款接口
     * @author kaylv <kaylv@dayuw.com>
     */
    public function Refund(){
        $faker = Factory::create();
        ////获取商户订单号
        $outTradeNo = '0478795592836';

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        //创建退款请求builder,设置参数
        $refundRequestBuilder = new AlipayTradeRefundContentBuilder();
        $refundRequestBuilder->setOutTradeNo($outTradeNo);
        $refundRequestBuilder->setRefundAmount(0.5);
        $refundRequestBuilder->setOutRequestNo($faker->bankAccountNumber);

        $refundRequestBuilder->setAppAuthToken($appAuthToken);

        //请求
        $response = $this->application->refund->refund($refundRequestBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response->code);
        var_dump($response->sign);
    }
}