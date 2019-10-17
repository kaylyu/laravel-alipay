<?php

namespace  Kaylyu\Alipay\F2fpay\Refund;

use Kaylyu\Alipay\F2fpay\Base\Aop\Request\AlipayTradeRefundRequest;
use Kaylyu\Alipay\F2fpay\Base\Model\Builder\AlipayTradeRefundContentBuilder;
use Kaylyu\Alipay\F2fpay\Base\Model\Result\AlipayF2FPayResult;
use Kaylyu\Alipay\F2fpay\Kernel\BaseClient;
use function Kaylyu\Alipay\F2fpay\Kernel\Support\tradeError;
use function Kaylyu\Alipay\F2fpay\Kernel\Support\tradeSuccess;


/**
 * Class Product.
 */
class Client extends BaseClient
{
    /**
     * 统一收单交易退款接口
     *
     * 当交易发生之后一段时间内，由于买家或者卖家的原因需要退款时，卖家可以通过退款接口将支付款退还给买家，支付宝将在收到退款请求并且验证成功之后，按照退款规则将支付款按原路退到买家帐号上
     * 交易超过约定时间（签约时设置的可退款时间）的订单无法进行退款 支付宝退款支持单笔交易分多次退款，多次退款需要提交原支付订单的商户订单号和设置不同的退款单号
     * 一笔退款失败后重新提交，要采用原来的退款单号
     * 总退款金额不能超过用户实际支付金额
     *
     * @param AlipayTradeRefundContentBuilder $builder
     * @author kaylv <kaylv@dayuw.com>
     * @return array|\Kaylyu\Alipay\Kernel\Support\Collection|string
     */
    public function refund(AlipayTradeRefundContentBuilder $builder)
    {
        $request = new AlipayTradeRefundRequest();
        $request->setBizContent($builder->getBizContent());

        //请求
        $response = $this->httpPost($request, $builder->getAppAuthToken());

        //获取
        $data = $response->alipay_trade_refund_response;
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
