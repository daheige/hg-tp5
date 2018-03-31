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

    /**
     * [json json数据输出]
     * @return [string] [json]
     */
    public function json()
    {
        return json(['code' => 200, 'message' => '获取成功', 'data' => ['username' => 'heige313', 'git' => 'daheige']]);
    }

    public function xml()
    {
        $data = ['name' => 'thinkphp', 'url' => 'thinkphp.cn'];
        // 指定xml数据输出
        return xml(['data' => $data, 'code' => 1, 'message' => '操作完成']);
    }

    public function getUser()
    {
        //数据返回格式：对象方式返回
        $res = model('User')->getUserInfo();
        var_dump($res->name);
        $res = model('User')->getUser();
        var_dump($res->name);

        $res = model('User')::get(1);
        var_dump($res->name);

        //调用model内部会使用单例模式和做new User操作，先在独立模块下查找User模型
        //然后公共common/model查找模型
        $user = model('User');
        $res  = $user->where('name', 'heige')
            ->find();
        var_dump($res->name);
    }

    public function hginfo()
    {
        \library\Slog::info('fefe', __FUNCTION__);
        //助手函数调用
        write_log('heige313', __FUNCTION__);

        echo logic("Test")->getUser();
    }

    public function hgservice()
    {
        //服务demo
        service('Test')->getUser('http://www.baidu.com/api/info?id=1');
        echo 3;die;
    }
}
