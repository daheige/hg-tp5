<?php
namespace app\index\controller;

use \think\captcha\Captcha; //导入验证码类
use \think\Controller;

class Hgpublic extends Controller
{
    public function verify()
    {
        $config = [
            // 验证码字体大小
            'fontSize' => 30,
            // 验证码位数
            'length'   => 6,
            // 关闭验证码杂点
            'useNoise' => false,
            'codeSet'  => '0123456789',
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}
