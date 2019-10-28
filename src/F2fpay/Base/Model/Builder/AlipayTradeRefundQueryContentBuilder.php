<?php

namespace Kaylyu\Alipay\F2fpay\Base\Model\Builder;

class AlipayTradeRefundQueryContentBuilder extends ContentBuilder
{
    // 支付宝交易号,和商户订单号不能同时为空, 如果同时存在则通过tradeNo查询支付宝交易
    private $tradeNo;

    // 商户订单号，通过此商户订单号查询当面付的交易状态
    private $outTradeNo;

    //请求退款接口时，传入的退款请求号，如果在退款请求时未传入，则该值为创建交易时的外部交易号
    private $outRequestNo;

    //银行间联模式下有用，其它场景请不要使用；双联通过该参数指定需要查询的交易所属收单机构的pid;
    private $orgId;

    private $bizContentarr = array();

    private $bizContent = null;

    public function getBizContent()
    {
        if (!empty($this->bizContentarr)) {
            $this->bizContent = json_encode($this->bizContentarr, JSON_UNESCAPED_UNICODE);
        }
        return $this->bizContent;
    }

    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizContentarr['out_trade_no'] = $outTradeNo;
    }

    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
        $this->bizContentarr['trade_no'] = $tradeNo;
    }

    public function getOutRequestNo()
    {
        return $this->outRequestNo;
    }

    public function setOutRequestNo($outRequestNo)
    {
        $this->outRequestNo = $outRequestNo;
        $this->bizContentarr['out_request_no'] = $outRequestNo;
    }

    public function getOrgId()
    {
        return $this->orgId;
    }

    public function setOrgId($orgId)
    {
        $this->orgId = $orgId;
        $this->bizContentarr['org_pid'] = $orgId;
    }


}