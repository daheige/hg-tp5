<?php

namespace Service;

use \library\ConcurrenceCurl;
use \library\Slog as Log;

/**
 * Service 基类
 */
class BaseService
{
    protected $errorCode     = 0;
    protected $errorMessage  = '';
    protected $errorMessages = ['0' => ''];

    /**
     * 接口返回的错误信息
     * @var null
     */
    protected $serviceErrorInfo  = null;
    protected static $_instances = [];

    protected static $concurrenceCurl = null;

    //通过单例模式调用服务
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    /**
     * 接口调用
     * @param  string                   $request_url 接口请求地址
     * @param  array                    $options     CURL请求附加参数
     * @return ConcurrenceCurlManager
     */
    public static function call($request_url, $options = [])
    {
        if (empty(self::$concurrenceCurl)) {
            self::$concurrenceCurl = ConcurrenceCurl::getInstance();
        }
        return self::$concurrenceCurl->addUrl($request_url, $options);
    }

    public static function post($request_url, $params = [], $options = [])
    {
        $options[CURLOPT_POST]       = true;
        $options[CURLOPT_POSTFIELDS] = $params;
        return self::call($request_url, $options);
    }

    //通过fsockopen异步方式请求接口和调用
    public static function asyncGet($request_url, $params = [])
    {
        \library\AsyncCurl::get($request_url, $params);
    }

    public static function asyncPost($request_url, $params)
    {
        \library\AsyncCurl::post($request_url, $params);
    }

    public static function delete($request_url, $params = [], $options = [])
    {
        $options[CURLOPT_POSTFIELDS] = $params;
        return self::call($request_url, $options);
    }

    public static function get($request_url, $options = [])
    {
        return self::call($request_url, $options);
    }

    public function setErrorInfo($error_code, $error_message)
    {
        $this->errorCode    = $error_code;
        $this->errorMessage = $error_message;
    }

    public function getErrorMessage()
    {
        return empty($this->errorMessage) ? (isset($this->errorMessages[$this->errorCode]) ? $this->errorMessages[$this->errorCode] : '') : $this->errorMessage;
    }

    public function getErrorInfo()
    {
        return $this->serviceErrorInfo;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getInterfaceData($epi_curl_manager)
    {
        $response = $epi_curl_manager->getResponse();
        if ($response === null || (isset($response['code']) && $response['code'] == 500)) {
            $this->setErrorInfo(10002, '网络异常，获取信息失败');
            return false;
        }

        if ($response['code'] == 200) {
            $response_data = json_decode($response['data'], true);
            $code          = isset($response_data['code']) ? $response_data['code'] : 500;
            $msg           = isset($response_data['message']) ? $response_data['message'] : '获取信息失败';
            if (isset($response_data['code'])) {
                if ($response_data['code'] == 500) {
                    $this->setErrorInfo($code, $msg);
                    return false;
                } elseif ($response_data['code'] === 0 || $response_data['code'] === 200) {
                    // 记录慢查询接口
                    if ($response['time'] > 0.2) {
                        // 记录慢查询接口
                        Log::notice('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
                    }
                    return isset($response_data['data']) ? $response_data['data'] : '';
                }
            } else {
                // 记录接口返回错误数据
                $this->setErrorInfo($code, $msg);
                Log::warn('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
                return false;
            }
        }

        // 记录接口请求错误
        Log::error('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
        $this->setErrorInfo($response['code'], '获取信息失败');
        return false;
    }

    //获取post或get请求之后的结果转换为array
    public function getData($epi_curl_manager)
    {
        $response = $epi_curl_manager->getResponse();
        if ($response === null || (isset($response['code']) && $response['code'] == 500)) {
            $this->setErrorInfo(10002, '网络异常，获取信息失败');
            return false;
        }

        if ($response['code'] == 200) { //响应状态
            $response_data = json_decode($response['data'], true);
            if (!empty($response_data) && is_array($response_data)) {
                return $response_data;
            } else {
                // 记录接口返回错误数据
                $this->setErrorInfo(10002, '请求失败');
                Log::warn('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
                return false;
            }
        }

        // 记录接口请求错误
        Log::error('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
        $this->setErrorInfo($response['code'], '获取信息失败');
        return false;
    }
}
