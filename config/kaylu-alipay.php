<?php

return [
    'response_type' => env('KAYLYU_ALIPAY_RESPONSE_TYPE', 'collection'), //collection array json object
    'log' => [
        'level' => env('KAYLYU_ALIPAY_LOG_LEVEL', 'debug'),
        'file' => env('KAYLYU_ALIPAY_LOG_FILE', storage_path('logs/kaylu-alipay.log')),
    ],
    'http' => [
        'verify' => env('KAYLYU_ALIPAY_HTTP_VERIFY', false),
        'timeout' => env('KAYLYU_ALIPAY__HTTP_TIMEOUT', 60),
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
];