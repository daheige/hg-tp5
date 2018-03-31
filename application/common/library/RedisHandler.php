<?php
namespace library;

/**
 * redis操作类
 * author:heige
 * git:daheige
 * time:2017-12-22
 */
class RedisHandler
{
    /**
     * @var Redis $redis
     */
    private $redis;
    private static $_instance = [];
    private function __construct($config = [])
    {

        if ($config == 'REDIS_DEFAULT') {
            $conf = [
                'host' => '127.0.0.1',
                'port' => 6379,
            ];
        } else {
            $conf = is_array($config) && !empty($config) ? $config : [
                'host' => '127.0.0.1',
                'port' => 6379,
            ];
        }

        $this->redis = new \Redis();
        try {
            $this->redis->connect($conf['host'], $conf['port']);
            if (isset($conf['password']) && $conf['password']) {
                $this->redis->auth($conf['password']);
            }
            $this->redis->ping();
            
            //设置redis key前缀
            if (isset($conf['prefix']) && $conf['prefix']) {
                $this->redis->setOption(\Redis::OPT_PREFIX, $prefix); // use custom prefix on all keys
            }

            //设置redis database哪个库,默认最大不超过16个库,默认采用第0个库
            if (isset($conf['database']) && is_numeric($conf['database'])) {
                if ($conf['database'] > 15) {
                    $conf['database'] = 0;
                }

                $this->redis->select($conf['database']);
            }

            //设置redis key前缀
            if (isset($conf['prefix']) && $conf['prefix']) {
                $this->redis->setOption(\Redis::OPT_PREFIX, $prefix); // use custom prefix on all keys
            }

            //设置redis database哪个库,默认最大不超过16个库,默认采用第0个库
            if (isset($conf['database']) && is_numeric($conf['database'])) {
                if ($conf['database'] > 15) {
                    $conf['database'] = 0;
                }

                $this->redis->select($conf['database']);
            }

        } catch (\Exception $e) {
            // throw new \Exception('RedisHandle_redis_connect 3 ' . $e->getMessage(), 1);
            write_log('RedisHandle_redis_connect 3 ' . $e->getMessage(), 'redis_error', 'info');
            return false;
        }

        return $this->redis;
    }

    /**
     * 取得handle对象
     * $config = array(
     *  'server' => '127.0.0.1' 服务器
     *  'port'   => '6379' 端口号
     * )
     * @param  string        $config
     * @return RedisHandle
     */
    public static function getInstance($config = 'REDIS_DEFAULT')
    {
        $conn_id = md5(var_export($config, true)); //redis连接唯一标识
        if (!isset(self::$_instance[$conn_id]) || !(self::$_instance[$conn_id] instanceof self)) {
            self::$_instance[$conn_id] = new self($config);
        }
        return self::$_instance[$conn_id];
    }
    /**
     * 设置值(string)会将$value自动转为json格式
     * @param  string       $key     KEY名称
     * @param  string|array $value   获取得到的数据
     * @param  int          $timeOut 时间
     * @return bool
     */
    public function setJson($key, $value, $timeOut = 0)
    {
        $value  = json_encode($value, JSON_UNESCAPED_UNICODE);
        $retRes = $this->redis->set($key, $value);
        if ($timeOut > 0) {
            $this->redis->EXPIRE($key, $timeOut);
        }
        return $retRes;
    }
    /**
     * 通过KEY获取数据(string),返回array
     * @param  string  $key KEY名称
     * @return mixed
     */
    public function getJson($key)
    {
        $result = $this->redis->get($key);
        return json_decode($result, true);
    }
    /**
     * 双重缓存，防止击穿
     * 如果key没有被初始化，仍会导致击穿，需要用其他方式去考虑大规模key构造攻击
     * @param  int   $key Redis key
     * @return Mix
     */
    public function getByLock($key)
    {
        $sth = $this->redis->get($key);
        if ($sth === false) {
            return $sth;
        } else {
            $sth = json_decode($sth, true);
            if (!isset($sth['expire']) || intval($sth['expire']) <= time()) {
                $lock = $this->redis->incr($key . '.lock');
                if ($lock === 1) {
                    return false;
                }
                return isset($sth['data']) ? $sth['data'] : $sth;
            } else {
                return $sth['data'];
            }
        }
    }
    /**
     * 设置Redis，防止缓存击穿
     * @param  int    $key    Redis key
     * @param  Mix    $value  缓存值
     * @param  int    $expire 过期时间
     * @return bool
     */
    public function setByLock($key, $value, $expire = 0)
    {
        $expire = intval($expire) > 0 ? intval($expire) : 300;
        $r_exp  = $expire + 100;
        $c_exp  = time() + $expire;
        $arg    = ['data' => $value, 'expire' => $c_exp];
        $rs     = $this->redis->setex($key, $r_exp, json_encode($arg, JSON_UNESCAPED_UNICODE));
        $this->redis->del($key . '.lock');
        return $rs;
    }
    /**
     * 清空数据
     */
    public function flushAll()
    {
        return true;
        //return $this->redis->flushAll();
    }

    /**
     * 数据入队列(对应redis的list数据结构)
     * @param  string       $key   KEY名称
     * @param  string|array $value 需压入的数据
     * @param  bool         $right 是否从右边开始入
     * @return int
     */
    public function push($key, $value)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        return $this->redis->lPush($key, $value);
    }
    /**
     * 数据出队列（对应redis的list数据结构）
     * @param  string  $key  KEY名称
     * @param  bool    $left 是否从左边开始出数据
     * @return mixed
     */
    public function pop($key)
    {
        $val = $this->redis->rPop($key);
        if ($val) {
            $val = json_decode($val, true);
        }
        return $val;
    }
    /**
     * 透明地调用redis其它操作方法
     * 用来支持redis命令调用
     * 命令参考：http://redisdoc.com/
     * @param  string  $name
     * @param  array   $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        return call_user_func_array([$this->redis, $name], $params);
    }
}
