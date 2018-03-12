<?php
// 定义应用目录
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'application/');

// 加载系统常量和框架引导文件
require_once APP_PATH . 'config/constants.php';
require_once THINK_PATH . 'start.php';
