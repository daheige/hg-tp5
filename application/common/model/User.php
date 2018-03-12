<?php
namespace app\common\model;

use \think\Model;

class User extends Model
{
    protected $table = 'test';
    /**
     * 自定义数据库连接信息
     */
    // 设置当前模型的数据库连接
    protected $connection = [
        // 数据库类型
        'type'     => 'mysql',
        // 服务器地址
        'hostname' => '127.0.0.1',
        // 数据库名
        'database' => 'test',
        // 数据库用户名
        'username' => 'root',
        // 数据库密码
        'password' => '1234',
        // 数据库编码默认采用utf8
        'charset'  => 'utf8',
        // 数据库表前缀
        'prefix'   => '',
        // 数据库调试模式
        'debug'    => false,
    ];

    public function getUserInfo()
    {
        return self::get(1);
    }

    public function getUser()
    {

        return $this->db()->where('name', 'haha31517723167')->find();
    }

}
