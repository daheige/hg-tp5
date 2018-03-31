<?php
namespace library;

/**
 * 日志处理类
 * author: heige
 * time: 2018-01-09 23:29
 */
class Slog
{
    /* 日志级别 从上到下，由低到高 */
    const EMERG  = 'emerg';  // 严重错误: 导致系统崩溃无法使用
    const ALERT  = 'alter';  // 警戒性错误: 必须被立即修改的错误
    const CRIT   = 'crit';   // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR    = 'error';  // 一般错误: 一般性错误
    const WARN   = 'warn';   // 警告性错误: 需要发出警告的错误
    const NOTICE = 'notice'; // 通知: 程序可以运行但是还不够完美的错误
    const INFO   = 'info';   // 信息: 程序输出信息
    const DEBUG  = 'debug';  // 调试: 调试信息
    const SQL    = 'sql';    // SQL语句 注意只在调试模式开启时有效

    protected static $config = [
        'log_time_format' => 'Y-m-d H:i:s',
        'log_file_size'   => 2097152,
    ];

    public static function info($message, $destination = '')
    {
        self::write($message, self::INFO, $destination);
    }

    public static function notice($message, $destination = '')
    {
        self::write($message, self::NOTICE, $destination);
    }

    public static function warn($message, $destination = '')
    {
        self::write($message, self::WARN, $destination);
    }

    public static function error($message, $destination = '')
    {
        self::write($message, self::ERR, $destination);
    }

    public static function debug($message, $destination = '')
    {
        if (IS_PRO) {
            return true;
        }

        self::write($message, self::DEBUG, $destination);
    }

    public static function sql($message, $destination = '')
    {
        if (APP_DEBUG) {
            self::write($message, self::SQL, $destination);
        }

        return;
    }

    public static function record($message, $destination = '')
    {
        if (APP_DEBUG) {
            self::write($message, self::DEBUG, $destination);
        }

        return;
    }

    /**
     * 日志写入接口
     * @access public
     * @param  string $log         日志信息
     * @param  string $destination 写入目标
     * @return void
     */
    protected static function write($message, $level = self::ERR, $destination = '')
    {
        if (!empty($message) && is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $message = is_string($message) ? $message : json_encode($message, JSON_UNESCAPED_UNICODE);
        $log     = "{$level}: {$message}";
        $now     = date(self::$config['log_time_format']);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            $level = 'cli/' . $level;
        }

        //日志存放路径
        $destination = LOG_PATH . '/' . $level . '/' . date('y_m_d') . '/' . ($destination ? $destination : 'common') . '.log';

        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor(self::$config['log_file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination));
        }

        error_log("[{$now}] " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'localhost') . ' ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/') . "\r\n{$log}\r\n", 3, $destination);
    }

    //错误处理
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $arr[] = 'errno: ' . $errno;
        $arr[] = 'errfile: ' . $errfile;
        $arr[] = 'errline: ' . $errline;
        $arr[] = 'errMessage: ' . $errstr;
        self::info(implode(PHP_EOL, $arr), 'common_error');
    }

    //获取 fatal error register_shutdown_function("Log::fatalHandler");
    public static function fatalHandler()
    {
        $error   = error_get_last();
        $errType = E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
        if ($error && ($error["type"] === ($error["type"] & $errType))) {
            $arr[] = 'errno: ' . $error["type"];
            $arr[] = 'errfile: ' . $error["file"];
            $arr[] = 'errline: ' . $error["line"];
            $arr[] = 'errMessage: ' . $error["message"];
            self::info(implode(PHP_EOL, $arr), 'fatal_error');
        }
    }
}
