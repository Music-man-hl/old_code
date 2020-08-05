<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname' => env('DATABASE_HOSTNAME'),
    // 数据库名
    'database' => 'mp',
    // 用户名
    'username' => env('DATABASE_USERNAME'),
    // 密码
    'password' => env('DATABASE_PASSWORD'),
    // 端口
    'hostport' => '3306',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '',
    // 数据库调试模式
    'debug' => env('APP_DEBUG'),
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0 ,  //  APP_EVN == 1 ? 0 : 1
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false, //APP_EVN == 1 ? false : true
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain' => config('app.app_debug'),
    // Query类
    'query'           => '\\think\\db\\Query',
    // 开启断线重连
    'break_reconnect' => true,

    //dms_main 数据库
    'dms_main' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => env('DATABASE_HOSTNAME'),
        // 数据库名
        'database' => 'dms_main',
        // 用户名
        'username' => env('DATABASE_USERNAME'),
        // 密码
        'password' => env('DATABASE_PASSWORD'),
        // 端口
        'hostport' => '3306',
    ],

    //dms_product 数据库
    'dms_product' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => env('DATABASE_HOSTNAME'),
        // 数据库名
        'database' => 'dms_product',
        // 用户名
        'username' => env('DATABASE_USERNAME'),
        // 密码
        'password' => env('DATABASE_PASSWORD'),
        // 端口
        'hostport' => '3306',
    ],

    //mp_order数据库
    'mp_order' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => env('DATABASE_HOSTNAME'),
        // 数据库名
        'database' => 'mp_order',
        // 用户名
        'username' => env('DATABASE_USERNAME'),
        // 密码
        'password' => env('DATABASE_PASSWORD'),
        // 端口
        'hostport' => '3306',
    ],
];
