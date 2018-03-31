<?php
/**
 * 项目常量定义
 * author:daheige
 */
define('COMMON_PATH', APP_PATH . 'common/');
define('CONF_PATH', APP_PATH . 'config/'); //配置文件目录
define('RUNTIME_PATH', ROOT_PATH . 'runtime/');
define('EXTEND_PATH', APP_PATH . 'extend/'); //extend目录定义
define('THINK_PATH', ROOT_PATH . 'thinkphp/');

//文件日志存放目录
defined('LOG_PATH') || define('LOG_PATH', RUNTIME_PATH . '/log/');

//定义环境变量APP_ENV (nginx配置：testing测试环境,local本地环境,production生产环境)
//fastcgi_param APP_ENV "TESTING";#TESTING;PRODUCTION;STAGING
defined('APP_ENV') || define('APP_ENV', isset($_SERVER['APP_ENV']) ? strtolower($_SERVER['APP_ENV']) : 'production');

// 定义项目开始时间
defined('START_TIME') || define('START_TIME', microtime(true));

// 定义项目初始内存
defined('START_MEMORY') || define('START_MEMORY', memory_get_usage());

// 项目版本
define('SYS_VERSION', '1.0.0');

// 生产环境
defined('IS_PRO') or define('IS_PRO', APP_ENV == 'production' || is_file('/etc/php.env.production'));
defined('PRODUCTION', IS_PRO);

// 预发环境
defined('STAGING') || define('STAGING', is_file('/etc/php.env.staging'));

// 测试环境
defined('TESTING') || define('TESTING', is_file('/etc/php.env.testing'));

// 开发环境
defined('DEVELOPMENT') || define('DEVELOPMENT', !(IS_PRO || STAGING || TESTING));

//测试或本地环境打开调试模式，线上环境关闭
defined('APP_DEBUG') || define('APP_DEBUG', !IS_PRO || in_array(APP_ENV, ['testing', 'local']));

//js目录 相对于public目录
define('JS_SRC', '/' . (DEVELOPMENT || TESTING ? 'js_src' : 'js'));

//用于不同环境读取不同的config
define('SYS_CONFIG_PATH', in_array(APP_ENV, ['tesing', 'local']) ? CONF_PATH . APP_ENV . '/' : CONF_PATH . 'production/');

/**
 * 环境常量定义
 */
// 定义是否 CLI 模式
define('APP_IS_CLI', (PHP_SAPI === 'cli'));
if (APP_IS_CLI) {
    define('IS_AJAX', false);
    define('IS_CURL', false);
    define('API_MODE', false);
    define('HTTP_HOST', null);
    define('HTTP_PROTOCOL', null);
    define('HTTP_SSL', false);
    define('HTTP_BASE', null);
    define('HTTP_URL', null);
} else {
    // 定义是否 AJAX 请求
    define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']));

    // 定义是否 cURL 请求
    define('IS_CURL', isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false);

    // 定义当前是否为 API 模式
    define('API_MODE', isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

    // 定义主机地址
    if (isset($_SERVER['HTTP_HOST'])) {
        define('HTTP_HOST', strtolower($_SERVER['HTTP_HOST']));
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        define('HTTP_HOST', strtolower($_SERVER['HTTP_X_FORWARDED_HOST']));
    }

    // 定义 HTTP 协议
    define('HTTP_PROTOCOL', isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http');

    // 定义是否 SSL
    define('HTTP_SSL', isset($_SERVER['SERVER_PROTOCOL']) &&
        (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false));

    // 定义当前基础域名
    define('HTTP_BASE', HTTP_PROTOCOL . '://' . HTTP_HOST . '/');

    // 定义当前页面 URL 地址
    define('HTTP_URL', rtrim(HTTP_BASE, '/') . $_SERVER['REQUEST_URI']);
}
