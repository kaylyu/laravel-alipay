<h1 align="center"> Alipay </h1>

[![Build Status](https://travis-ci.org/kaylyu/laravel-alipay.svg?branch=master)](https://travis-ci.org/kaylyu/laravel-alipay)
[![Latest Stable Version](https://poser.pugx.org/kaylyu/alipay/v/stable)](https://packagist.org/packages/kaylyu/alipay)
[![Latest Unstable Version](https://poser.pugx.org/kaylyu/alipay/v/unstable)](https://packagist.org/packages/kaylyu/alipay)
[![Total Downloads](https://poser.pugx.org/kaylyu/alipay/downloads)](https://packagist.org/packages/kaylyu/alipay)
[![License](https://poser.pugx.org/kaylyu/alipay/license)](https://packagist.org/packages/kaylyu/alipay)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kaylyu/alipay/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kaylyu/alipay/?branch=master) 
[![Code Coverage](https://scrutinizer-ci.com/g/kaylyu/alipay/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kaylyu/alipay/?branch=master) 
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fkaylyu%2Falipay.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fkaylyu%2Falipay?ref=badge_shield)

基于Laravel框架的支付宝支付SDK，目前仅支持当面付

## Installation

```shell
$ composer require "kaylyu/alipay:~1.0" -vvv
```
## Config
- 配置 可以拷贝config/kaylu-alipay.php 到 laravel 目录config下面
```php
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
```

## Usage
- 注册对应Service Provider
```php
    // laravel >= 5.5
    Kaylyu\Alipay\F2fpay\ServiceProvider::class
    
    // lumen
    $app->register(Kaylyu\Alipay\F2fpay\ServiceProvider::class);
```
    
    
- 使用
```php
    //统一收单线下交易预创建（扫码支付）
    app('kaylu.alipay.f2fpay')->order->qrPay

```
