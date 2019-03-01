<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28
 * Time: 14:44
 */
namespace app\admin\controller;
use app\admin\validate\AdminUserValidate;
use think\Config;
use think\Db;


class AdminUser extends Base{
    protected $request;
    public function getAdmin(){
        /*有权限走这里的可以读取所有用户*/
        $map = [
            'a.status'=>['<>',2]
        ];
        /*获取分页数据*/
        $members = Db::name('admin')
            ->alias('a')
            ->join('__AUTH_GROUP_ACCESS__ aga','a.id=aga.uid')
            ->join('__AUTH_GROUP__ ag','aga.group_id=ag.id')
            ->field('a.*,ag.title')
            ->where($map)
            ->paginate(8);
        $list = $members->render();
        $count = count($members);
        $this->assign('count',$count);
        $this->assign('members',$members);
        /*获取分页样式*/
        $this->assign('list',$list);
        return $this->fetch();
    }
    public function addAdmin(){
        if($this->request->isPost()){
            $params = $this->request->param();

            $validate = new AdminUserValidate();
            /*我操，params接收的数据存在data里面了！*/
            $checkData = $params['data'];
            $check = $validate->check($checkData);
            if(!$check){
                return json(['status'=>0,'msg'=>$validate->getError()]);
            }
            /*同时添加两张表数据，使用手动开启事务操作Db::startTrans()（官网的自动开启事务，导致返回值不能按照自定义return的值返回，而是返回页面）*/
            Db::startTrans();
            try{
                $username = $checkData['username'];
                $password = $checkData['password'];
                $role = $checkData['role'];
                $dataAdmin = [
                    'username'=>$username,
                    'password'=>$password,
                    'create_time'=>time(),
                    'status'=>1
                ];
                $resultAdminId = Db::name('admin')->insertGetId($dataAdmin);
                $dataGroupAccess = [
                    'uid'=>$resultAdminId,
                    'group_id'=>$role
                ];
                $result = Db::name('auth_group_access')->insert($dataGroupAccess);
                // 提交事务
                Db::commit();
                if($result){
                    return json(['status'=>1,'msg'=>'操作成功！']);
                }else{
                    return json(['status'=>0,'msg'=>'操作失败']);
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }

            /* 改方式返回值有问题
             * Db::transaction(function() use($checkData){
                $username = $checkData['username'];
                $password = $checkData['password'];
                $role = $checkData['role'];
                $dataAdmin = [
                    'username'=>$username,
                    'password'=>$password,
                    'create_time'=>time(),
                    'status'=>1
                ];
                $resultAdminId = Db::name('admin')->insertGetId($dataAdmin);
                $dataGroupAccess = [
                    'uid'=>$resultAdminId,
                    'group_id'=>$role
                ];
                $result = Db::name('auth_group_access')->insert($dataGroupAccess);

                if($result){
                    return json(['status'=>1,'msg'=>'操作成功！']);
                }else{
                    return json(['status'=>0,'msg'=>'操作失败']);
                }
            });*/
        }
        $map=[
            'status'=>1
        ];
        $role = Db::name('auth_group')->where($map)->select();
        $this->assign('role',$role);
        return $this->fetch();
    }
    public function changeStatus(){
        $params = $this->request->param();
        $adminId = $params['id'];
        $status = $params['status'];
        $map = [
            'id'=>$adminId
        ];
        $user = Db::name('admin')->where($map)->field('status')->find();

        if($user['status'] == 1 && $status == 0){
            $result = Db::name('admin')->where($map)->update(['status'=>0]);
        }elseif ($user['status'] == 0 && $status == 1){
            $result = Db::name('admin')->where($map)->update(['status'=>1]);
        }else{
            return json(['status'=>3,'msg'=>'刷新页面重试！']);
        }

        if($result){
            return json(['status'=>1,'msg'=>'操作成功！']);
        }else{
            return json(['status'=>0,'msg'=>'操作失败！刷新页面重试！']);
        }

    }
    public function delAll(){
        $params = $this->request->param();
        $ids = $params['ids'];
        $map = [
            'id'=>['in',$ids]
        ];
        $result = Db::name('admin')->where($map)->update(['status'=>2]);
        if($result){
            return json(['status'=>1,'msg'=>'操作成功！']);
        }else{
            return json(['status'=>0,'msg'=>'操作失败！']);
        }

    }
    public function delOne(){
        $params = $this->request->param();
        $adminId = $params['id'];
        $map = [
            'id'=>$adminId
        ];
        $result = Db::name('admin')->where($map)->update(['status'=>2]);
        if ($result){
            return json(['status'=>1,'msg'=>'操作成功！']);
        }else{
            return json(['status'=>2,'msg'=>'操作失败！']);
        }

    }
    public function search(){
        if($this->request->isPost()){
            $params = $this->request->param();
            $startTime = strtotime($params['start']);
            $endTime = strtotime($params['end']);
            $username = $params['username'];
            $map = [];

            if(!$startTime && $endTime){
                $startTime = $endTime - date('m');
                $endTime = mktime(23,59,59,date('m',$endTime),date('d',$endTime),date('Y',$endTime));
                $startTime = mktime(0,0,0,date('m',$startTime),date('d',$startTime),date('Y',$startTime));
                $map['a.create_time'] = ['between',[$startTime,$endTime]];
            }elseif($startTime && !$endTime){
                $endTime = $startTime + date('m');
                $endTime = mktime(23,59,59,date('m',$endTime),date('d',$endTime),date('Y',$endTime));
                $startTime = mktime(0,0,0,date('m',$startTime),date('d',$startTime),date('Y',$startTime));
                $map['a.create_time'] = ['between',[$startTime,$endTime]];
            }elseif($startTime && $endTime){
                $endTime = mktime(23,59,59,date('m',$endTime),date('d',$endTime),date('Y',$endTime));
                $startTime = mktime(0,0,0,date('m',$startTime),date('d',$startTime),date('Y',$startTime));
                $map['a.create_time'] = ['between',[$startTime,$endTime]];
            }else{
                $map = [];
            }

            if($username){
                $map['a.username'] = ['=',$username];
            }
        }
        $map['a.status'] =['<>',2];
        $map = isset($map) ? $map : [];
        /*获取分页数据*/
        $members = Db::name('admin')
            ->alias('a')
            ->join('__AUTH_GROUP_ACCESS__ aga','a.id=aga.uid')
            ->join('__AUTH_GROUP__ ag','aga.group_id=ag.id')
            ->field('a.*,ag.title')
            ->where($map)
            ->paginate(8);
        /*获取分页样式*/
        $list = $members->render();
        $count = count($members);
        $this->assign('count',$count);
        $this->assign('members',$members);
        $this->assign('list',$list);
        return $this->fetch('get_admin');

    }
    public function editAdmin(){
        if($this->request->isPost()){

            $params = $this->request->param();

            /*我操，params接收的数据存在data里面了！*/
            $checkData = $params['data'];
            if($checkData['password'] != $checkData['repassword']) return json(['status'=>0,'msg'=>'两次密码不一致！']);
            if(!$checkData['password']){
                $checkData['password'] = 'youGuess';
            }
            $validate = new AdminUserValidate();
            $check = $validate->check($checkData);
            if(!$check){
                return json(['status'=>0,'msg'=>$validate->getError()]);
            }

            /*同时添加两张表数据，使用手动开启事务操作Db::startTrans()（官网的自动开启事务，导致返回值不能按照自定义return的值返回，而是返回页面）*/
            Db::startTrans();
            try{
                if($params['data']['password']){
                    $salt = Config::get('salt');
                    $password = md5($params['data']['password'].$salt);
                    $dataAdmin = [
                        'password'=>$password,
                    ];
                    Db::name('admin')->where(['id'=>$checkData['id']])->update($dataAdmin);
                }
                $checkRole = Db::name('auth_group_access')->field('group_id')->where(['uid'=>$checkData['id']])->find();
                $role = $checkData['role'];
                if($checkRole['group_id'] != $role){
                    $dataGroupAccess = [
                        'group_id'=>$role
                    ];
                    $result = Db::name('auth_group_access')->where([ 'uid'=>$checkData['id']])->update($dataGroupAccess);
                }else{
                    $result = 1;
                }
                // 提交事务
                Db::commit();
                if($result){
                    return json(['status'=>1,'msg'=>'操作成功！']);
                }else{
                    return json(['status'=>0,'msg'=>'操作失败']);
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }

            /* 改方式返回值有问题
             * Db::transaction(function() use($checkData){
                $username = $checkData['username'];
                $password = $checkData['password'];
                $role = $checkData['role'];
                $dataAdmin = [
                    'username'=>$username,
                    'password'=>$password,
                    'create_time'=>time(),
                    'status'=>1
                ];
                $resultAdminId = Db::name('admin')->insertGetId($dataAdmin);
                $dataGroupAccess = [
                    'uid'=>$resultAdminId,
                    'group_id'=>$role
                ];
                $result = Db::name('auth_group_access')->insert($dataGroupAccess);

                if($result){
                    return json(['status'=>1,'msg'=>'操作成功！']);
                }else{
                    return json(['status'=>0,'msg'=>'操作失败']);
                }
            });*/
        }

        $adminId  = $this->request->param('id');

        $admin = Db::name('admin')->field('username')->where(['id'=>$adminId])->find();
        $userName = $admin['username'];
        $roles = Db::name('auth_group')
            ->where(['status'=>1])
            ->field('title,id')
            ->select();
        $role = Db::name('auth_group_access')
            ->where(['uid'=>$adminId])
            ->field('group_id')
            ->find();
        $this->assign('userName',$userName);
        $this->assign('adminId',$adminId);
        $this->assign('role',$role);
        $this->assign('roles',$roles);
        return $this->fetch();
    }
    public function getRole(){
        return $this->fetch();
    }
}