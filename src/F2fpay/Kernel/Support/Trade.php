<?php

namespace Kaylyu\Alipay\F2fpay\Kernel\Support;

/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/10/16
 * Time: 12:59
 */

// 交易成功
function tradeSuccess($response)
{
    return !empty($response)
        && ("10000" == $response->code);
}

// 查询返回“支付成功”
function querySuccess($queryResponse)
{
    return !empty($queryResponse) &&
        $queryResponse->code == "10000" &&
        ($queryResponse->trade_status == "TRADE_SUCCESS" ||
            $queryResponse->trade_status == "TRADE_FINISHED");
}

// 查询返回“交易关闭”
function queryClose($queryResponse)
{
    return !empty($queryResponse) &&
        $queryResponse->code == "10000" &&
        $queryResponse->trade_status == "TRADE_CLOSED";
}

// 交易异常，或发生系统错误
function tradeError($response)
{
    return empty($response) ||
        $response->code == "20000";
}