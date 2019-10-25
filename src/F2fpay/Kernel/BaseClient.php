<?php

namespace Kaylyu\Alipay\F2fpay\Kernel;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Kaylyu\Alipay\F2fpay\Base\Model\Result\AlipayF2FPayResult;
use Kaylyu\Alipay\Kernel\Exceptions\Exception;
use Kaylyu\Alipay\Kernel\ServiceContainer;
use Kaylyu\Alipay\Kernel\Support\Collection;
use Kaylyu\Alipay\Kernel\Traits\HasHttpRequests;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BaseClient.
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var \Kaylyu\Alipay\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var
     */
    protected $baseUri;

    /**
     * BaseClient constructor.
     *
     * @param \Kaylyu\Alipay\Kernel\ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @param $request
     * @param null $appAuthToken
     * @author kaylv <kaylv@dayuw.com>
     * @return array|Collection|string
     */
    public function httpPost($request, $appAuthToken = null)
    {
        return $this->request($request, 'POST', $appAuthToken);
    }

    /**
     * @param $request
     * @param string $method
     * @param null $appAuthToken
     * @author kaylv <kaylv@dayuw.com>
     * @return array|bool|Collection|mixed|object|ResponseInterface|\SimpleXMLElement|string
     * @throws Exception
     */
    public function request($request, string $method = 'GET', $appAuthToken = null)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        //初始化AopClient
        $aop = $this->newAopClient();

        //准备请求参数
        list($requestUrl, $apiParams) = $aop->requestPrepare($request, null, $appAuthToken);

        //发起HTTP请求
        $key = 'query';
        if ($method == 'POST') {
            $key = 'form_params';
        }
        $response = $this->performRequest($requestUrl, $method, [$key => $apiParams]);

        //读取内容
        $response = $response->getBody()->getContents();

        //解析
        $rs = json_decode($response);

        //校验
        if(isset($rs->null_response)){
            throw new Exception('系统繁忙！！！', $rs->null_response->code);
        }

        //验签解密
        $response = $aop->responseHandle($request, $response);

        return $response;
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log
        $this->pushMiddleware($this->logMiddleware(), 'log');
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }

    /**
     * 初始化AopClient
     * @author kaylv <kaylv@dayuw.com>
     * @throws Exception
     */
    private function newAopClient()
    {
        //获取当面付配置
        $f2fpay = $this->app->getF2fpay();

        //获取配置参数
        $gatewayUrl = $f2fpay['gateway_url'];
        $appId = $f2fpay['app_id'];
        $signType = $f2fpay['sign_type'];
        $rsaPrivateKey = $f2fpay['merchant_private_key'];
        $alipayPublicKey = $f2fpay['alipay_public_key'];
        $charset = $f2fpay['charset'];
        $notifyUrl = $f2fpay['notify_url'];

        if (empty($appId) || trim($appId) == "") {
            throw new Exception("appid should not be NULL!");
        }
        if (empty($rsaPrivateKey) || trim($rsaPrivateKey) == "") {
            throw new Exception("merchant_private_key should not be NULL!");
        }
        if (empty($alipayPublicKey) || trim($alipayPublicKey) == "") {
            throw new Exception("alipay_public_key should not be NULL!");
        }
        if (empty($charset) || trim($charset) == "") {
            throw new Exception("charset should not be NULL!");
        }
        if (empty($notifyUrl) || trim($notifyUrl) == "") {
            throw new Exception("sign_type should not be NULL");
        }
        if (empty($gatewayUrl) || trim($gatewayUrl) == "") {
            throw new Exception("gateway_url should not be NULL");
        }
        if (empty($signType) || trim($signType) == "") {
            throw new Exception("sign_type should not be NULL");
        }

        //组装请求数据
        $aop = new AopClient();
        $aop->gatewayUrl = $gatewayUrl;
        $aop->appId =$appId;
        $aop->signType = $signType;
        $aop->rsaPrivateKey = $rsaPrivateKey;
        $aop->alipayrsaPublicKey = $alipayPublicKey;
        $aop->apiVersion = "1.0";
        $aop->charset = $charset;
        $aop->format = 'json';

        return $aop;
    }

    /**
     * 格式化返回数据
     * @param $response
     * @author kaylv <kaylv@dayuw.com>
     * @return array|Collection|string
     */
    protected function formatResponseToType($response)
    {
        //获取数据
        $data = is_object($response) ? (array)$response : $response;
        //返回数据类型
        $type = $this->app->config->get('response_type');

        switch ($type ?? 'array') {
            default:
            case 'collection':
                return new Collection($data);
            case 'array':
                return $data;
            case 'object':
                return $response;
            case 'json':
                return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }
}
