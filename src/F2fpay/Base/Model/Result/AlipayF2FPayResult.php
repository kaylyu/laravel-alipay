<?php

namespace Kaylyu\Alipay\F2fpay\Base\Model\Result;

class AlipayF2FPayResult
{
    /**
     * 成功
     */
    const ALIPAY_F2FPAY_RESULT_SUCCESS = 'SUCCESS';
    /**
     * 失败
     */
    const ALIPAY_F2FPAY_RESULT_FAILED = 'FAILED';
    /**
     * 未知错误
     */
    const ALIPAY_F2FPAY_RESULT_UNKNOWN = 'UNKNOWN';

    /**
     * 交易结果状态
     * @var
     * @author kaylv <kaylv@dayuw.com>
     */
    public $tradeStatus ;

    /**
     * 交易结果数据
     * @var
     * @author kaylv <kaylv@dayuw.com>
     */
    public $response;

    /**
     * 签名串
     * @var
     * @author kaylv <kaylv@dayuw.com>
     */
    public $sign;

    public function __construct($response, $sign)
    {
        $this->response = $response;
        $this->sign = $sign;
    }

    public function setTradeStatus($tradeStatus)
    {
       $this->tradeStatus = $tradeStatus;
    }

    public function getTradeStatus()
    {
        return $this->tradeStatus;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function toArray()
    {
        return $this->response;
    }
}