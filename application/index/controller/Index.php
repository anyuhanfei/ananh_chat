<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Request;
use think\Db;


class Index extends Controller
{
    public function index()
    {
        if(Session::get('user_id')){
            $user_id = Session::get('user_id');
            $user_name = Session::get('user_name');
            $this->assign('user_id', $user_id);
            $this->assign('user_name', $user_name);
            return $this->fetch('index');
        }else{
            $this->redirect('Index/login');
        }
    }

    public function login(){
        return $this->fetch('login');
    }

    public function save_login()
    {
        $user_name = Request::instance()->post('user_name');
        $user_password = Request::instance()->post('user_password');
        $user = Db::table('user')->where('user_name', $user_name)->find();
        if($user){
            //有这个用户
            if($user['user_password'] == $user_password){
                Session::set('user_id', $user['user_id']);
                Session::set('user_name', $user['user_name']);
                return array('error_code'=>1, 'data'=>array(), 'msg'=>'登录成功');
            }else{
                return array('error_code'=>-1, 'data'=>array(), 'msg'=>'密码错误,登录失败');
            }
        }else{
            //没有这个用户，新建一个用户
            $res = Db::table('user')->insert([
                'user_name'=>$user_name,
                'user_password'=>$user_password
            ]);
            $user_id = Db::name('user')->getLastInsID();
            Session::set('user_id', $user_id);
            Session::set('user_name', $user_name);
            return array('error_code'=>1, 'data'=>array(), 'msg'=>'创建用户成功，已自动登录');
        }
    }

    public function out_login(){
        Session::delete('user_id');
        Session::delete('user_name');
        $this->redirect('Index/login');
    }
}
