<?php
/**
 * 常量定义
 * author:daheige
 */
define('COMMON_PATH', APP_PATH . 'common/');
define('CONF_PATH', APP_PATH . 'config/'); //配置文件目录
define('RUNTIME_PATH', ROOT_PATH . 'runtime/');
define('EXTEND_PATH', APP_PATH . 'extend/'); //extend目录定义
define('THINK_PATH', ROOT_PATH . 'thinkphp/');

//定义环境变量APP_ENV (testing测试环境,local本地环境,production生产环境)
define('APP_ENV', isset($_SERVER['APP_ENV']) ? strtolower($_SERVER['APP_ENV']) : 'production');
//测试或本地环境打开调试模式，线上环境关闭
define('APP_DEBUG', in_array(APP_ENV, ['testing', 'local']));
//用于不同环境读取不同的config
define('SYS_CONFIG_PATH', in_array(APP_ENV, ['tesing', 'local']) ? CONF_PATH . APP_ENV . '/' : CONF_PATH . 'production/');
