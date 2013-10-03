<?php
define("APP_PATH",dirname(__FILE__));
define("SP_PATH",dirname(__FILE__).'/SpeedPHP');
$spConfig = array(
    'mode' => 'release',
    'db' => array( // 数据库设置
        'host' => $_SERVER['jz_host'],  // 数据库地址
        'login' => $_SERVER['jz_user'], // 数据库用户名
        'password' => $_SERVER['jz_pass'], // 数据库密码
        'database' => $_SERVER['jz_db'], // 数据库的库名称
        'prefix' => '' // 表前缀
    ),
    'view' => array( // 视图配置
        'enabled' => TRUE, // 开启视图
        'config' =>array(
            'template_dir' => APP_PATH.'/tpl', //模板目录
        ),
        'engine_name' => 'speedy', //模板引擎的类名称
        'engine_path' => SP_PATH.'/Drivers/speedy.php', //模板引擎主类路径
    ),
    'launch' => array(
        'router_prefilter' => array( 
            array('spUrlRewrite', 'setReWrite'), 
        ),
        'function_url' => array(
            array("spUrlRewrite", "getReWrite"),  // 对spUrl进行挂靠，让spUrl可以进行Url_ReWrite地址的生成
        )
    ),
    'ext' => array(
        'spUrlRewrite' => array(
            'suffix' => '', 
            'sep' => '/', 
            'map' => array( 
                'dl' => 'main@dl',
                'token' => 'main@token',
                'bind' => 'main@bind',
                'itemList' => 'main@itemList',
                'itemAdd' => 'main@itemAdd'
            ),
            'args' => array(
                
            )
        )
    )
);
require(SP_PATH."/SpeedPHP.php");
import(APP_PATH.'/include/functions.php');//载入自定义函数库
spRun();