<?php
//可以自定义函数
function hgtest()
{
    echo 1;die;
}
if (!function_exists('parse_name')) {

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param  string   $name 字符串
 * @param  integer  $type 转换类型
 * @return string
 */
    function parse_name($name, $type = 0)
    {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {return strtoupper($match[1]);}, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}

if (!function_exists('logic')) {
    /**
     * 创建logic
     * @author heige
     *
     * @param  string    $name logic名称
     * @return B\Logic
     */
    function logic($name, $group = 'common')
    {
        $name  = parse_name($name, 1);
        $class = '\\logic\\' . parse_name($group, 1) . '\\' . $name . 'Logic';

        if (class_exists($class)) {
            $logic = $class::getInstance();
        } else {
            $class = '\\logic\\' . $name . 'Logic';
            $logic = class_exists($class) ? $class::getInstance() : \logic\BaseLogic::getInstance();
        }
        return $logic;
    }
}

if (!function_exists('service')) {
    /**
     * 创建logic
     * @author heige
     *
     * @param  string    $name logic名称
     * @return B\Logic
     */
    function service($name, $group = 'common')
    {
        $name  = parse_name($name, 1);
        $class = '\\service\\' . parse_name($group, 1) . '\\' . $name . 'Service';

        if (class_exists($class)) {
            $service = $class::getInstance();
        } else {
            $class   = '\\service\\' . $name . 'Service';
            $service = class_exists($class) ? $class::getInstance() : \service\BaseService::getInstance();
        }
        return $service;
    }
}

if (!function_exists('write_log')) {

    function write_log($message = '', $file_name = "common", $method = "info")
    {
        if (empty($message)) {
            return false;
        }

        $class = '\library\Slog';
        if (method_exists($class, $method)) {
            call_user_func_array([$class, $method], [$message, $file_name]);
        } else {
            $class::info($message, $file_name);
        }
    }
}

if (!function_exists('post')) {
/**
 * CURL POST 请求
 * @param  string   $url
 * @param  array    $postdata
 * @param  array    $curl_opts
 * @return string
 */
    function post($url, array $postdata = null, array $curl_opts = null)
    {
        $ch = curl_init();

        if (null !== $postdata) {
            $postdata = http_build_query($postdata);
        }

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_URL            => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $postdata,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        if (null !== $curl_opts) {
            curl_setopt_array($ch, $curl_opts);
        }
        $result = curl_exec($ch);
        // 获取http状态码
        $intReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (200 != $intReturnCode) {
            return [];
        }

        return $result;
    }
}

if (!function_exists('get')) {
/**
 * CURL GET 请求
 *
 * @param  string   $url
 * @param  array    $curl_opts
 * @return string
 */
    function get($url, array $curl_opts = null)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_URL            => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        if (null !== $curl_opts) {
            curl_setopt_array($ch, $curl_opts);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

if (!function_exists('curl')) {
/**
 * [curl curl请求api支持GET,POST,PUT三种方式]
 * @param  [type]     $url             [请求地址]
 * @param  string     $method          [请求方式支持GET,POST,PUT]
 * @param  array      $data            [请求参数]
 * @param  array      $opts            [附加curl参数或header头]
 * @return [response] [请求结果]
 */
    function curl($url, $method = 'GET', $data = [], $opts = [])
    {
        $method = strtoupper($method);
        $ch     = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        foreach ($opts as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
        switch ($method) {
            case 'GET':
                //拼接get参数
                $url = $data == [] ? $url : $url . '?' . urldecode(http_build_query($data));
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
                break;
            case 'PUT':
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            //记录错误信息
            write_log('curl_error: ' . curl_error($ch), __FUNCTION__, 'error');
        }

        return $response;
    }
}
