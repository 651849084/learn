<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27
 * Time: 15:58
 */
return [
    'salt'=>'shop',
    // auth配置
    'auth'    => [
        // 权限开关
        'auth_on'           => 1,
        // 认证方式，1为实时认证；2为登录认证。
        'auth_type'         => 1,
        // 用户组数据不带前缀表名
        'auth_group'        => 'auth_group',
        // 用户-用户组关系不带前缀表
        'auth_group_access' => 'auth_group_access',
        // 权限规则不带前缀表
        'auth_rule'         => 'auth_rule',
        // 用户信息不带前缀表
        'auth_user'         => 'shop_admin',
    ],
    'paginate'=>[
        'type'     => 'bootstrap',
        'var_page' => 'list'
    ]
];