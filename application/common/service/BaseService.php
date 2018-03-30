<?php

namespace service;

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

    //原样返回响应结果不加处理
    public function getInterfaceData($epi_curl_manager)
    {
        $response = $epi_curl_manager->getResponse();
        //测试环境下记录请求的原始结果
        if (defined('APP_DEBUG') && APP_DEBUG) {
            Log::info('api result:' . var_export($response['data'], true), 'api_request_full_res');
        }

        if ($response === null || (isset($response['code']) && $response['code'] == 500)) {
            $this->setErrorInfo(10002, '网络异常，获取信息失败');
            return false;
        }

        if ($response['code'] == 200) {
            //记录慢查询api
            if ($response['time'] > 2) {
                Log::notice('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
            }

            return isset($response['data']) ? $response['data'] : '';
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

        //httpok 200
        if ($response['code'] == 200) {
            $response_data = json_decode($response['data'], true); //业务方返回的数据
            $code          = isset($response_data['code']) ? $response_data['code'] : 500;
            $msg           = isset($response_data['message']) ? $response_data['message'] : '获取信息失败';

            //业务方包含code,message,data
            //约定code = 0表示正常请求
            if (isset($response_data['code'])) {
                if ($response_data['code'] == 500) {
                    $this->setErrorInfo($code, $msg);
                    return false;
                } elseif ($response_data['code'] === 0 || $response_data['code'] === 200) {
                    // 记录慢查询接口
                    if ($response['time'] > 2) {
                        // 记录慢查询接口
                        Log::notice('CURL REQUEST ERROR : HTTP_CODE=' . $response['code'] . '; TOTAL_TIME=' . $response['time'] . '; EFFECTIVE_URL=' . $response['url'] . '; Data :' . $response['data'], 'service');
                    }

                    //如果包含data,就返回data，否则原样返回响应结果
                    return isset($response_data['data']) ? $response_data['data'] : $response_data;
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

    /**
     * [curlPost get curl请求]
     * 当is_format_json=true,返回结果code,message,data,否则原样返回
     * 第三个参数$options可以携带header头或cookie进行请求
     * @param  string  $url            [请求地址]
     * @param  array   $params         [请求参数]
     * @param  array   $options        [header头附加信息]
     * @param  boolean $is_format_json [是否解析json数据提交]
     * @return [array  or string]
     */
    public function curlPost($url = '', $params = [], $is_format_json = false, $options = [])
    {
        $curl_obj = $this->post($url, $params, $options);
        $result   = $this->getInterfaceData($curl_obj);
        $result   = $is_format_json ? json_decode($result, true) : $result;
        if ($is_format_json && empty($result)) {
            return ['code' => 500, 'message' => '获取信息失败', 'data' => null];
        }

        return $result ? $result : null;
    }

    /**
     * [curlPost post curl请求]
     * 当is_format_json=true,返回结果code,message,data,否则原样返回
     * 参数$options可以携带header头或cookie进行请求
     * @param  string  $url            [请求地址]
     * @param  array   $params         [请求参数]
     * @param  array   $options        [header头附加信息]
     * @param  boolean $is_format_json [是否解析json数据提交]
     * @return [array  or string]
     */
    public function curlGet($url = '', $params = [], $is_format_json = false, $options = [])
    {
        if (is_array($params) && $params) {
            $url = strpos($url, '?') !== false ? $url . '&' . http_build_query($params) : $url . '?' . http_build_query($params);
        }

        $curl_obj = $this->get($url, $options);
        $result   = $this->getInterfaceData($curl_obj);
        $result   = $is_format_json ? json_decode($result, true) : $result;
        if ($is_format_json && empty($result)) {
            return ['code' => 500, 'message' => '获取信息失败', 'data' => null];
        }

        return $result ? $result : null;
    }
}
