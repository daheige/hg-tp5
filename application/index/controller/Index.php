<?php
namespace app\index\controller;

use \think\Controller;
use \think\Log;

class Index extends Controller
{
    public function index()
    {
        $this->assign('name', 'heige');
        return $this->fetch('index');
    }

    public function test()
    {
        Log::write('测试日志信息，这是警告级别，并且实时写入', 'notice');
        $flag = 0;
        $str  = $flag ?: "heige";
        echo $str;
        echo 333;die;
    }

    //图片上传
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads', time());
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg
                echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                echo $info->getFilename();
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

    public function foo()
    {
        $Test = new \my\Test(); //自定义命名空间
        $Test->sayHello();
        die;
        hgtest();
    }
}
