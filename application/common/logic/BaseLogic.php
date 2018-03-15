<?php
//logic基类

namespace logic;

/**
 * Logic 基类
 * @author heige
 */
class BaseLogic
{
    protected $errorCode         = 0;
    protected $errorMessages     = ['0' => ''];
    protected $errorMessage      = '';
    protected static $_instances = [];

    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    public function getErrorMessage()
    {
        return empty($this->errorMessage) ? (isset($this->errorMessages[$this->errorCode]) ? $this->errorMessages[$this->errorCode] : '') : $this->errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function setErrorInfo($code = 200, $msg = 'ok')
    {
        $this->errorCode    = $code;
        $this->errorMessage = $msg;
    }
}
