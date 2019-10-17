<?php
/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/8/30
 * Time: 11:22
 */

namespace Kaylyu\Alipay\Tests\Order;


use Faker\Factory;
use Kaylyu\Alipay\F2fpay\Application;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeCancelContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeCloseContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradePayContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradePrecreateContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeQueryContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\ExtendParams;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\GoodsDetail;
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
     * 统一收单线下交易预创建（扫码支付）
     * @author kaylv <kaylv@dayuw.com>
     */
    public function QrPay()
    {
        $faker = Factory::create();

        // (必填) 商户网站订单系统中唯一订单号，64个字符以内，只能包含字母、数字、下划线，
        // 需保证商户系统端不能重复，建议通过数据库sequence生成，
        //$outTradeNo = "qrpay".date('Ymdhis').mt_rand(100,1000);
        $outTradeNo = $faker->bankAccountNumber;

        // (必填) 订单标题，粗略描述用户的支付目的。如“xxx品牌xxx门店当面付扫码消费”
        $subject = $faker->name;

        // (必填) 订单总金额，单位为元，不能超过1亿元
        // 如果同时传入了【打折金额】,【不可打折金额】,【订单总金额】三者,则必须满足如下条件:【订单总金额】=【打折金额】+【不可打折金额】
        $totalAmount = $faker->numberBetween(1,100);


        // (不推荐使用) 订单可打折金额，可以配合商家平台配置折扣活动，如果订单部分商品参与打折，可以将部分商品总价填写至此字段，默认全部商品可打折
        // 如果该值未传入,但传入了【订单总金额】,【不可打折金额】 则该值默认为【订单总金额】- 【不可打折金额】
        //String discountableAmount = "1.00"; //

        // (可选) 订单不可打折金额，可以配合商家平台配置折扣活动，如果酒水不参与打折，则将对应金额填写至此字段
        // 如果该值未传入,但传入了【订单总金额】,【打折金额】,则该值默认为【订单总金额】-【打折金额】
        $undiscountableAmount = "0.01";

        // 卖家支付宝账号ID，用于支持一个签约账号下支持打款到不同的收款账号，(打款到sellerId对应的支付宝账号)
        // 如果该字段为空，则默认为与支付宝签约的商户的PID，也就是appid对应的PID
        //$sellerId = "";

        // 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
        $body = "购买商品2件共15.00元";

        //商户操作员编号，添加此参数可以为商户操作员做销售统计
        $operatorId = "2088102179124175";

        // (可选) 商户门店编号，通过门店号和商家后台可以配置精准到门店的折扣信息，详询支付宝技术支持
        $storeId = "2088102179124175";

        // 支付宝的店铺编号
        $alipayStoreId= "2088102179124175";

        // 业务扩展参数，目前可添加由支付宝分配的系统商编号(通过setSysServiceProviderId方法)，系统商开发使用,详情请咨询支付宝技术支持
        $providerId = ""; //系统商pid,作为系统商返佣数据提取的依据
        $extendParams = new ExtendParams();
        $extendParams->setSysServiceProviderId($providerId);
        $extendParamsArr = $extendParams->getExtendParams();

        // 支付超时，线下扫码交易定义为5分钟
        $timeExpress = "5m";

        // 商品明细列表，需填写购买商品详细信息，
        $goodsDetailList = array();

        // 创建一个商品信息，参数含义分别为商品id（使用国标）、名称、单价（单位为分）、数量，如果需要添加商品类别，详见GoodsDetail
        $goods1 = new GoodsDetail();
        $goods1->setGoodsId("apple-01");
        $goods1->setGoodsName("iphone");
        $goods1->setPrice(3000);
        $goods1->setQuantity(1);
        //得到商品1明细数组
        $goods1Arr = $goods1->getGoodsDetail();

        // 继续创建并添加第一条商品信息，用户购买的产品为“xx牙刷”，单价为5.05元，购买了两件
        $goods2 = new GoodsDetail();
        $goods2->setGoodsId("apple-02");
        $goods2->setGoodsName("ipad");
        $goods2->setPrice(1000);
        $goods2->setQuantity(1);
        //得到商品1明细数组
        $goods2Arr = $goods2->getGoodsDetail();

        $goodsDetailList = array($goods1Arr,$goods2Arr);

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        // 创建请求builder，设置请求参数
        $qrPayRequestBuilder = new AlipayTradePrecreateContentBuilder();
        $qrPayRequestBuilder->setOutTradeNo($outTradeNo);
        $qrPayRequestBuilder->setTotalAmount($totalAmount);
        $qrPayRequestBuilder->setTimeExpress($timeExpress);
        $qrPayRequestBuilder->setSubject($subject);
        $qrPayRequestBuilder->setBody($body);
        $qrPayRequestBuilder->setUndiscountableAmount($undiscountableAmount);
//        $qrPayRequestBuilder->setExtendParams($extendParamsArr);
//        $qrPayRequestBuilder->setGoodsDetailList($goodsDetailList);
//        $qrPayRequestBuilder->setStoreId($storeId);
//        $qrPayRequestBuilder->setOperatorId($operatorId);
//        $qrPayRequestBuilder->setAlipayStoreId($alipayStoreId);

        $qrPayRequestBuilder->setAppAuthToken($appAuthToken);

        $response = $this->application->order->qrPay($qrPayRequestBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response);//响应内容
        var_dump($response->sign);
    }

    /**
     * 统一收单交易支付接口（条码支付）
     * @author kaylv <kaylv@dayuw.com>
     */
    public function BarPay(){

        $faker = Factory::create();
        // (必填) 商户网站订单系统中唯一订单号，64个字符以内，只能包含字母、数字、下划线，
        // 需保证商户系统端不能重复，建议通过数据库sequence生成，
        //$outTradeNo = "barpay" . date('Ymdhis') . mt_rand(100, 1000);
        $outTradeNo = $faker->bankAccountNumber;

        // (必填) 订单标题，粗略描述用户的支付目的。如“XX品牌XXX门店消费”
        $subject = $faker->name;

        // (必填) 订单总金额，单位为元，不能超过1亿元
        // 如果同时传入了【打折金额】,【不可打折金额】,【订单总金额】三者,则必须满足如下条件:【订单总金额】=【打折金额】+【不可打折金额】
        $totalAmount = $faker->numberBetween(1,100);

        // (必填) 付款条码，用户支付宝钱包手机app点击“付款”产生的付款条码
        $authCode = '280084845040308775'; //28开头18位数字

        // (可选,根据需要使用) 订单可打折金额，可以配合商家平台配置折扣活动，如果订单部分商品参与打折，可以将部分商品总价填写至此字段，默认全部商品可打折
        // 如果该值未传入,但传入了【订单总金额】,【不可打折金额】 则该值默认为【订单总金额】- 【不可打折金额】
        //String discountableAmount = "1.00"; //

        // (可选) 订单不可打折金额，可以配合商家平台配置折扣活动，如果酒水不参与打折，则将对应金额填写至此字段
        // 如果该值未传入,但传入了【订单总金额】,【打折金额】,则该值默认为【订单总金额】-【打折金额】
        $undiscountableAmount = "0.01";

        // 卖家支付宝账号ID，用于支持一个签约账号下支持打款到不同的收款账号，(打款到sellerId对应的支付宝账号)
        // 如果该字段为空，则默认为与支付宝签约的商户的PID，也就是appid对应的PID
        $sellerId = "";

        // 订单描述，可以对交易或商品进行一个详细地描述，比如填写"购买商品2件共15.00元"
        $body = "购买商品2件共15.00元";

        //商户操作员编号，添加此参数可以为商户操作员做销售统计
        $operatorId = "test_operator_id";

        // (可选) 商户门店编号，通过门店号和商家后台可以配置精准到门店的折扣信息，详询支付宝技术支持
        $storeId = "test_store_id";

        // 支付宝的店铺编号
        $alipayStoreId = "test_alipay_store_id";

        // 业务扩展参数，目前可添加由支付宝分配的系统商编号(通过setSysServiceProviderId方法)，详情请咨询支付宝技术支持
        $providerId = ""; //系统商pid,作为系统商返佣数据提取的依据
        $extendParams = new ExtendParams();
        $extendParams->setSysServiceProviderId($providerId);
        $extendParamsArr = $extendParams->getExtendParams();

        // 支付超时，线下扫码交易定义为5分钟
        $timeExpress = "5m";

        // 商品明细列表，需填写购买商品详细信息，
        $goodsDetailList = array();

        // 创建一个商品信息，参数含义分别为商品id（使用国标）、名称、单价（单位为分）、数量，如果需要添加商品类别，详见GoodsDetail
        $goods1 = new GoodsDetail();
        $goods1->setGoodsId("good_id001");
        $goods1->setGoodsName("XXX商品1");
        $goods1->setPrice(3000);
        $goods1->setQuantity(1);
        //得到商品1明细数组
        $goods1Arr = $goods1->getGoodsDetail();

        // 继续创建并添加第一条商品信息，用户购买的产品为“xx牙刷”，单价为5.05元，购买了两件
        $goods2 = new GoodsDetail();
        $goods2->setGoodsId("good_id002");
        $goods2->setGoodsName("XXX商品2");
        $goods2->setPrice(1000);
        $goods2->setQuantity(1);
        //得到商品1明细数组
        $goods2Arr = $goods2->getGoodsDetail();

        $goodsDetailList = array($goods1Arr, $goods2Arr);

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        // 创建请求builder，设置请求参数
        $barPayRequestBuilder = new AlipayTradePayContentBuilder();
        $barPayRequestBuilder->setOutTradeNo($outTradeNo);
        $barPayRequestBuilder->setTotalAmount($totalAmount);
        $barPayRequestBuilder->setAuthCode($authCode);
        $barPayRequestBuilder->setTimeExpress($timeExpress);
        $barPayRequestBuilder->setSubject($subject);
        $barPayRequestBuilder->setBody($body);
        $barPayRequestBuilder->setUndiscountableAmount($undiscountableAmount);
//        $barPayRequestBuilder->setExtendParams($extendParamsArr);
//        $barPayRequestBuilder->setGoodsDetailList($goodsDetailList);
//        $barPayRequestBuilder->setStoreId($storeId);
//        $barPayRequestBuilder->setOperatorId($operatorId);
//        $barPayRequestBuilder->setAlipayStoreId($alipayStoreId);

        $barPayRequestBuilder->setAppAuthToken($appAuthToken);

        //请求
        $response = $this->application->order->barPay($barPayRequestBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response);
        var_dump($response->sign);


    }

    /**
     * 统一收单线下交易查询
     * @author kaylv <kaylv@dayuw.com>
     */
    public function Query(){
        ////获取商户订单号
        $outTradeNo = '0478795592836';

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        //构造查询业务请求参数对象
        $queryContentBuilder = new AlipayTradeQueryContentBuilder();
        $queryContentBuilder->setOutTradeNo($outTradeNo);
        $queryContentBuilder->setAppAuthToken($appAuthToken);

        //请求
        $response = $this->application->order->query($queryContentBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response);
        var_dump($response->sign);
    }

    /**
     * 统一收单交易撤销接口
     * @author kaylv <kaylv@dayuw.com>
     */
    public function Cancel(){
        ////获取商户订单号
        $outTradeNo = '47060307421';

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        //构造查询业务请求参数对象
        $cancelContentBuilder = new AlipayTradeCancelContentBuilder();
        $cancelContentBuilder->setAppAuthToken($appAuthToken);
        $cancelContentBuilder->setOutTradeNo($outTradeNo);

        //请求
        $response = $this->application->order->cancel($cancelContentBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response);
        var_dump($response->sign);
    }

    /**
     * 统一收单交易关闭接口
     * @author kaylv <kaylv@dayuw.com>
     */
    public function Close(){
        ////获取商户订单号
        $outTradeNo = '47060307421';

        //第三方应用授权令牌,商户授权系统商开发模式下使用
        $appAuthToken = "";//根据真实值填写

        //构造查询业务请求参数对象
        $cancelContentBuilder = new AlipayTradeCloseContentBuilder();
        $cancelContentBuilder->setAppAuthToken($appAuthToken);
        $cancelContentBuilder->setOutTradeNo($outTradeNo);

        //请求
        $response = $this->application->order->close($cancelContentBuilder);

        var_dump($response);
        var_dump($response->tradeStatus);//此值可以与AlipayF2FPayResult定义的常量进行对比，判断响应数据是否正常
        var_dump($response->response->code);
        var_dump($response->sign);
    }
}