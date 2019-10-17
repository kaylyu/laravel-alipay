<?php
/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/10/16
 * Time: 11:05
 */

namespace Kaylyu\Alipay\F2fpay\Kernel;

use Kaylyu\Alipay\F2fpay\Base\Aop\AopClient as BaseAopClient;
use Kaylyu\Alipay\Kernel\Exceptions\Exception;

class AopClient extends BaseAopClient
{
    /**
     * 准备请求参数
     * @param $request
     * @param null $authToken
     * @param null $appInfoAuthtoken
     * @author kaylv <kaylv@dayuw.com>
     * @return array
     * @throws Exception
     */
    public function requestPrepare($request, $authToken = null, $appInfoAuthtoken = null)
    {

        $this->setupCharsets($request);

        //		//  如果两者编码不一致，会出现签名验签或者乱码
        if (strcasecmp($this->fileCharset, $this->postCharset)) {

            // writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
            throw new Exception("文件编码：[" . $this->fileCharset . "] 与表单提交编码：[" . $this->postCharset . "]两者不一致!");
        }

        $iv = null;

        if (!$this->checkEmpty($request->getApiVersion())) {
            $iv = $request->getApiVersion();
        } else {
            $iv = $this->apiVersion;
        }

        //组装系统参数
        $sysParams["app_id"] = $this->appId;
        $sysParams["version"] = $iv;
        $sysParams["format"] = $this->format;
        $sysParams["sign_type"] = $this->signType;
        $sysParams["method"] = $request->getApiMethodName();
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["auth_token"] = $authToken;
        $sysParams["alipay_sdk"] = $this->alipaySdkVersion;
        $sysParams["terminal_type"] = $request->getTerminalType();
        $sysParams["terminal_info"] = $request->getTerminalInfo();
        $sysParams["prod_code"] = $request->getProdCode();
        $sysParams["notify_url"] = $request->getNotifyUrl();
        $sysParams["charset"] = $this->postCharset;
        $sysParams["app_auth_token"] = $appInfoAuthtoken;

        //获取业务参数
        $apiParams = $request->getApiParas();

        if (method_exists($request, "getNeedEncrypt") && $request->getNeedEncrypt()) {

            $sysParams["encrypt_type"] = $this->encryptType;

            if ($this->checkEmpty($apiParams['biz_content'])) {

                throw new Exception(" api request Fail! The reason : encrypt request is not supperted!");
            }

            if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {

                throw new Exception(" encryptType and encryptKey must not null! ");
            }

            if ("AES" != $this->encryptType) {

                throw new Exception("加密类型只支持AES");
            }

            // 执行加密
            $enCryptContent = \Kaylyu\Alipay\F2fpay\Base\Aop\encrypt($apiParams['biz_content'], $this->encryptKey);
            $apiParams['biz_content'] = $enCryptContent;
        }
        //签名
        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams), $this->signType);


        //系统参数放入GET请求串
        $requestUrl = $this->gatewayUrl . "?";
        foreach ($sysParams as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->postCharset)) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        return [$requestUrl, $apiParams];
    }

    /**
     * 处理响应
     * @param $request
     * @param $response
     * @author kaylv <kaylv@dayuw.com>
     * @return bool|mixed|\SimpleXMLElement
     */
    public function responseHandle($request, $response)
    {
        //解析AOP返回结果
        $respWellFormed = false;

        // 将返回结果转换本地文件编码
        $r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $response);

        //存放数据
        $signData = null;
        $respObject = null;

        if ("json" == $this->format) {

            $respObject = json_decode($r);
            if (null !== $respObject) {
                $respWellFormed = true;
                $signData = $this->parserJSONSignData($request, $response, $respObject);
            }
        } else {
            if ("xml" == $this->format) {

                $respObject = @ simplexml_load_string($response);
                if (false !== $respObject) {
                    $respWellFormed = true;

                    $signData = $this->parserXMLSignData($request, $response);
                }
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            return false;
        }

        // 验签
        $this->checkResponseSign($request, $signData, $response, $respObject);

        // 解密
        if (method_exists($request, "getNeedEncrypt") && $request->getNeedEncrypt()) {
            if ("json" == $this->format) {
                $resp = $this->encryptJSONSignSource($request, $response);

                // 将返回结果转换本地文件编码
                $r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
                $respObject = json_decode($r);
            } else {
                $resp = $this->encryptXMLSignSource($request, $response);

                $r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
                $respObject = @ simplexml_load_string($r);
            }
        }

        return $respObject;
    }
}