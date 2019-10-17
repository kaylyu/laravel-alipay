<?php

namespace Kaylyu\Alipay\F2fpay\Order;

use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradeCancelRequest;
use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradeCloseRequest;
use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradeCreateRequest;
use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradePayRequest;
use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradePrecreateRequest;
use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradeQueryRequest;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeCancelContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeCloseContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradePayContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradePrecreateContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeQueryContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Result\AlipayF2FPayResult;
use Kaylyu\Alipay\F2fpay\Kernel\BaseClient;
use function Kaylyu\Alipay\F2fpay\Kernel\Support\querySuccess;
use function Kaylyu\Alipay\F2fpay\Kernel\Support\tradeError;
use function Kaylyu\Alipay\F2fpay\Kernel\Support\tradeSuccess;

/**
 * 订单
 * @author kaylv <kaylv@dayuw.com>
 * @package Kaylyu\Alipay\F2fpay\Order
 */
class Client extends BaseClient
{
    /**
     * 统一收单交易支付接口（条码支付）
     *
     * 收银员使用扫码设备读取用户手机支付宝“付款码”/声波获取设备（如麦克风）读取用户手机支付宝的声波信息后，将二维码或条码信息/声波信息通过本接口上送至支付宝发起支付
     *
     * @param AlipayTradePayContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function barPay(AlipayTradePayContentBuilder $builder)
    {
        //创建
        $request = new AlipayTradePayRequest();
        $request->setBizContent($builder->getBizContent());

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_pay_response;
        $sign = $response->sign;

        //组装返回数据
        $result = new AlipayF2FPayResult($data, $sign);

        //处理
        if (tradeSuccess($data)) {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_SUCCESS);
        } elseif (tradeError($data)) {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_UNKNOWN);
        } else {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_FAILED);
        }

        return $this->formatResponseToType($result);
    }

    /**
     * 统一收单线下交易预创建（扫码支付）
     *
     * 收银员通过收银台或商户后台调用支付宝接口，生成二维码后，展示给用户，由用户扫描二维码完成订单支付
     *
     * @param AlipayTradePrecreateContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function qrPay(AlipayTradePrecreateContentBuilder $builder)
    {
        //获取当面付配置
        $f2fpay = $this->app->getF2fpay();

        //创建
        $request = new AlipayTradePrecreateRequest();
        $request->setBizContent($builder->getBizContent());
        $request->setNotifyUrl($f2fpay['notify_url']);

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_precreate_response;
        $sign = $response->sign;

        //组装返回数据
        $result = new AlipayF2FPayResult($data, $sign);

        //处理
        if (tradeSuccess($data)) {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_SUCCESS);
        } elseif (tradeError($data)) {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_UNKNOWN);
        } else {
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_FAILED);
        }

        return $this->formatResponseToType($result);
    }

    /**
     * 统一收单线下交易查询
     *
     * 该接口提供所有支付宝支付订单的查询，商户可以通过该接口主动查询订单状态，完成下一步的业务逻辑。 需要调用查询接口的情况：
     * 当商户后台、网络、服务器等出现异常，商户系统最终未接收到支付通知；
     * 调用支付接口后，返回系统错误或未知交易状态情况；
     * 调用alipay.trade.pay，返回INPROCESS的状态；
     * 调用alipay.trade.cancel之前，需确认支付状态
     *
     * @param AlipayTradeQueryContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function query(AlipayTradeQueryContentBuilder $builder)
    {
        //查询
        $request = new AlipayTradeQueryRequest();
        $request->setBizContent($builder->getBizContent());

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_query_response;
        $sign = $response->sign;

        //组装返回数据
        $result = new AlipayF2FPayResult($data, $sign);

        //处理
        if (querySuccess($data)) {
            // 查询返回该订单交易支付成功
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_SUCCESS);
        } elseif (tradeError($data)) {
            //查询发生异常或无返回，交易状态未知
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_UNKNOWN);
        } else {
            //其他情况均表明该订单号交易失败
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_FAILED);
        }

        return $this->formatResponseToType($result);
    }

    /**
     * 统一收单交易撤销接口
     *
     * 支付交易返回失败或支付系统超时，调用该接口撤销交易。如果此订单用户支付失败，支付宝系统会将此订单关闭；如果用户支付成功，支付宝系统会将此订单资金退还给用户
     * 注意：只有发生支付系统超时或者支付结果未知时可调用撤销，其他正常支付的单如需实现相同功能请调用申请退款API
     * 提交支付交易后调用【查询订单API】，没有明确的支付结果再调用【撤销订单API】
     *
     * @param AlipayTradeCancelContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function cancel(AlipayTradeCancelContentBuilder $builder)
    {
        $request = new AlipayTradeCancelRequest();
        $request->setBizContent($builder->getBizContent());

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_cancel_response;
        $sign = $response->sign;

        //组装返回数据
        $result = new AlipayF2FPayResult($data, $sign);

        //处理
        if (tradeSuccess($data)) {
            // 查询返回该订单交易支付成功
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_SUCCESS);
        } elseif (tradeError($data)) {
            //查询发生异常或无返回，交易状态未知
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_UNKNOWN);
        } else {
            //其他情况均表明该订单号交易失败
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_FAILED);
        }

        return $this->formatResponseToType($result);
    }

    /**
     * 统一收单交易关闭接口
     *
     * 用于交易创建后，用户在一定时间内未进行支付，可调用该接口直接将未付款的交易进行关闭
     *
     * @param AlipayTradeCloseContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function close(AlipayTradeCloseContentBuilder $builder)
    {
        $request = new AlipayTradeCloseRequest();
        $request->setBizContent($builder->getBizContent());

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_close_response;
        $sign = $response->sign;

        //组装返回数据
        $result = new AlipayF2FPayResult($data, $sign);

        //处理
        if (tradeSuccess($data)) {
            // 查询返回该订单交易支付成功
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_SUCCESS);
        } elseif (tradeError($data)) {
            //查询发生异常或无返回，交易状态未知
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_UNKNOWN);
        } else {
            //其他情况均表明该订单号交易失败
            $result->setTradeStatus(AlipayF2FPayResult::ALIPAY_F2FPAY_RESULT_FAILED);
        }

        return $this->formatResponseToType($result);
    }
}
