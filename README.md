<strong style="font-size:16px;">写在最开始的说明：该框架基于thinkphp5.0.15构建而成，新增了logic和service两个层，同时新增了curl并发库以及redis处理类，可用于构建大型web,api应用的开发。</strong>
<br/>ThinkPHP 5.0使用demo
===============

[![Total Downloads](https://poser.pugx.org/topthink/think/downloads)](https://packagist.org/packages/topthink/think)
[![Latest Stable Version](https://poser.pugx.org/topthink/think/v/stable)](https://packagist.org/packages/topthink/think)
[![Latest Unstable Version](https://poser.pugx.org/topthink/think/v/unstable)](https://packagist.org/packages/topthink/think)
[![License](https://poser.pugx.org/topthink/think/license)](https://packagist.org/packages/topthink/think)

ThinkPHP5在保持快速开发和大道至简的核心理念不变的同时，PHP版本要求提升到5.4，对已有的CBD模式做了更深的强化，优化核心，减少依赖，基于全新的架构思想和命名空间实现，是ThinkPHP突破原有框架思路的颠覆之作，其主要特性包括：

 + 基于命名空间和众多PHP新特性
 + 核心功能组件化
 + 强化路由功能
 + 更灵活的控制器
 + 重构的模型和数据库类
 + 配置文件可分离
 + 重写的自动验证和完成
 + 简化扩展机制
 + API支持完善
 + 改进的Log类
 + 命令行访问支持
 + REST支持
 + 引导文件支持
 + 方便的自动生成定义
 + 真正惰性加载
 + 分布式环境支持
 + 更多的社交类库

> ThinkPHP5的运行环境要求PHP5.4以上。

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

> router.php用于php自带webserver支持，可用于快速测试
> 切换到public目录后，启动命令：php -S localhost:8888  router.php
> 上面的目录结构和名称是可以改变的，这取决于你的入口文件和配置参数。

## 命名规范

`ThinkPHP5`遵循PSR-2命名规范和PSR-4自动加载规范，并且注意如下规范：

### 目录和文件

*   目录不强制规范，驼峰和小写+下划线模式均支持；
*   类库、函数文件统一以`.php`为后缀；
*   类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
*   类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；

### 函数和类、属性命名
*   类的命名采用驼峰法，并且首字母大写，例如 `User`、`UserType`，默认不需要添加后缀，例如`UserController`应该直接命名为`User`；
*   函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 `get_client_ip`；
*   方法的命名使用驼峰法，并且首字母小写，例如 `getUserName`；
*   属性的命名使用驼峰法，并且首字母小写，例如 `tableName`、`instance`；
*   以双下划线“__”打头的函数或方法作为魔法方法，例如 `__call` 和 `__autoload`；

### 常量和配置
*   常量以大写字母和下划线命名，例如 `APP_PATH`和 `THINK_PATH`；
*   配置参数以小写字母和下划线命名，例如 `url_route_on` 和`url_convert`；

### 数据表和字段
*   数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 `think_user` 表和 `user_name`字段，不建议使用驼峰和中文作为数据表字段命名。
### nginx配置
```
server {
        listen 80;
        index index.php index.html index.htm;
        root /web/tp5/public;

        # Add index.php to the list if you are using PHP
        index index.html index.htm default.html;
        server_name mytp.com *.mytp.com;

        location / {
            if (!-e $request_filename) {
                rewrite  ^(.*)$  /index.php?s=/$1  last;
                break;
            }
        }

        # pass  FastCGI server listening on 127.0.0.1:9000
        location ~ \.php$ {
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                include fastcgi_params;

                #fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_param SCRIPT_FILENAME    $document_root$fastcgi_script_name;
                #fastcgi_param PATH_INFO          $fastcgi_path_info;
                #fastcgi_param PATH_TRANSLATED    $document_root$fastcgi_path_info;
                fastcgi_param APP_ENV "TESTING";#TESTING;PRODUCTION;STAGING
        }

        location ~ /\.ht {
                deny all;
        }

        location ~ .*\.(xml|gif|jpg|jpeg|png|bmp|swf|woff|woff2|ttf|js|css)$ {
                expires 30d;
        }

        error_log /var/log/nginx/logs/mytp-error.log;
        access_log /var/log/nginx/logs/mytp-access.log;
}
```
### 项目模块化设计和目录调整如下：(请根据实际情况调整）
```
├── application
│   ├── common
│   ├── config  配置文件目录
│   ├── extend
│   └── index
├── build.php
├── composer.json
├── composer.lock
├── LICENSE.txt
├── public
│   ├── favicon.ico
│   ├── index.php
│   ├── robots.txt
│   ├── router.php
│   ├── static
│   └── uploads
├── README.md
├── runtime  权限755
│   ├── log
│   └── temp
├── think
├── thinkphp
│   ├── base.php
│   ├── codecov.yml
│   ├── composer.json
│   ├── console.php
│   ├── CONTRIBUTING.md
│   ├── convention.php
│   ├── helper.php
│   ├── lang
│   ├── library
│   ├── LICENSE.txt
│   ├── logo.png
│   ├── phpunit.xml
│   ├── README.md
│   ├── start.php
│   └── tpl
└── vendor
    ├── autoload.php
    ├── composer
    └── topthink
```
## 参与开发
请参阅 [ThinkPHP5 核心框架包](https://github.com/top-think/framework)。

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2017 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
