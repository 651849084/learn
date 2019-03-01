<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27
 * Time: 15:48
 */
namespace app\admin\logic;
use think\Db;
use think\Session;

/**
 * Class LoginLogic 登陆逻辑
 * @package app\admin\logic
 */
class LoginLogic{
    public function login($user,$pwd,$salt){
        /*查询条件，过滤html标签和两侧空格*/
        $map = [
            'username' =>strip_tags(trim($user)),
            'status'=> 1
        ];
        $user = Db::name('admin')->field('id,username,password')->where($map)->find();
        if ($user){
            if(md5($pwd.$salt) == $user['password']){
                /*用户信息保存*/
                Session::set('adminId',$user['id']);
                Session::set('userName',$user['username']);
                return ['status'=>1,'msg'=>'登陆成功'];
            }else{
                return ['status'=>2,'msg'=>'密码错误'];
            }
        }else{
            return ['status'=>3,'msg'=>'账号不存在'];
        }
    }
}