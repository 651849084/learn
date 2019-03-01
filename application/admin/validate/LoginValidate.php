<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27
 * Time: 15:46
 */
namespace app\admin\validate;
use think\captcha\Captcha;
use think\Validate;

/**
 * Class LoginValidate 登陆验证器
 * @package app\admin\validate
 */
class LoginValidate extends Validate {
    /*验证规则*/
    protected $rule = [
        'username'=>'require',
        'password'=>'require',
        /*使用自定义验证规则，假如比如满足某个值可以在规则方法后面：值，自动传递给规则方法的$rule变量*/
        'verify'=>'require|checkVerify'
    ];
    /*提示信息*/
    protected $message = [
        'username.require' =>'账号不能为空！',
        'password.require'=>'密码不能为空！',
        'verify.require'=>'验证码不能为空！'
    ] ;
    // 自定义验证规则
    protected function checkVerify($value)
    {
        $captcha = new Captcha();
        $result = $captcha->check($value);
        return $result ? true : '验证码错误';
    }

}