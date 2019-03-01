<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28
 * Time: 23:05
 */
namespace app\admin\validate;
use think\Validate;

class AdminUserValidate extends Validate{
    protected $rule = [
        'username' =>'require|max:25|min:5',
        'password'=>'require|max:16|min:6',
        'role'=>'require'
    ];
    protected $message = [
        'username.require'=>'账号不能为空！',
        'username.max'=>'账号最多25位！',
        'username.min'=>'账号最少5位！',
        'password.require'=>'密码不能为空！',
        'password.max'=>'密码最多16位！',
        'password.min'=>'密码最少6位！',
        'role.require'=>'角色不能为空！'
    ];

}