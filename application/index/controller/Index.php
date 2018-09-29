<?php
namespace app\index\controller;

use app\index\model\Admin_user;
use think\Controller;
use think\Session;

class Index extends Controller
{
    public $beforeActionList = [
        'LandingDetection'=> ['except'=>'login,logins'],
        'SignIn'=>['only'=>'login'],
    ];
    //登录界面
    public function login()
    {
        return $this->fetch();
    }
    //如果登陆过,不再开放登录接口
    public function SignIn(){
//        实例化model
        $model = new Admin_user();
        $session = new Session();
        if($model->get([
            'user_id'=>$session->get('admin_user'),
            'pass'=>$session->get('admin_pass'),
            'distinguish'=>$session->get('state')
        ]))
        {
            return $this->redirect('/');
        }
    }
    //登录事件
    public function logins($user_id,$pass)
    {
        $model = new Admin_user();
        $pass = md5(md5($user_id).md5($pass).md5('!@#$%^&*()_+'));
        $rel = $model->get([
            'user_id'=>$user_id,
            'pass'=>$pass
        ]);
        if(!$rel){  //密码错误
            echo json_encode([
                'msg'=>'账号或密码码错误',
                'state'=>400
            ]);
            return;
        }
        $state = md5(time());
        if(!$model -> where([
            'user_id'=>$user_id,
            'pass'=>$pass
        ])->update(['distinguish'=>$state,'login_time'=>time()]))
        {
            echo json_encode([
                'msg'=>'账号或密码码错误',
                'state'=>400
            ]);
            return;
        }
        $model->where([
            'user_id'=>$user_id,
            'pass'=>$pass
        ])->setInc('count_num');
        $session = new Session();
        $session->set('admin_user',$user_id);
        $session->set('admin_pass',$pass);
        $session->set('state',$state);
        echo json_encode([
            'msg'=>'登陆成功',
            'date'=>date("Y-m-d H:i",$rel['login_time']) ,
            'state'=>200
        ]);
        return;
    }
    //注销登陆
    public function logOut()
    {
        $session = new Session();
        $session->delete('admin_user');
        return $this->redirect('/');
    }
    //登陆检测
    protected function LandingDetection()
    {
//        实例化model
        $model = new Admin_user();
        $session = new Session();
        if(!$model->get([
            'user_id'=>$session->get('admin_user'),
            'pass'=>$session->get('admin_pass'),
            'distinguish'=>$session->get('state')
        ]))
        {
            $session->delete('admin_user');
            $session->delete('admin_pass');
            $session->delete('state');
            return $this->redirect('/index/index/login');
        }
    }
    //主页信息
    public function index()
    {
        $model = new Admin_user();
        $session = new Session();
        $data = $model->where([
            'user_id'=>$session->get('admin_user'),
            'pass'=>$session->get('admin_pass'),
            'distinguish'=>$session->get('state')
        ])->field('user_name,user_id')->select()[0];
        $this->assign('data',$data);
        return $this->fetch('index');
    }
    //个人信息
    public function personal()
    {
        $model = new Admin_user();
        $session = new Session();
        $data = $model->get([
            'user_id'=>$session->get('admin_user'),
            'pass'=>$session->get('admin_pass'),
            'distinguish'=>$session->get('state')
        ]);
        unset($data['pass']);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function ChangePassword($pass,$NewPassword)
    {
        $model = new Admin_user();
        $session = new Session();
        if(md5(md5($session->  //客户端验证
            get('admin_user')).md5($pass).md5('!@#$%^&*()_+'))!=$session->get('admin_pass')
        )
        {
            echo json_encode([
                "msg"=>'密码错误.',
                "state"=>400
            ]);
            return;
        }
        //服务器验证
        //新密码签名
        $NewPass = md5(md5($session->get('admin_user')).md5($NewPassword).md5('!@#$%^&*()_+'));
        if(!$model->where([
                'user_id'=>$session->get('admin_user'),
                'pass'=>md5(md5($session->get('admin_user')).md5($pass).md5('!@#$%^&*()_+')),
                'distinguish'=>$session->get('state')
        ])->update(['pass'=>$NewPass]))
        {
            echo json_encode([
                "msg"=>'密码错误',
                "state"=>400
            ]);
            return;
        }
        echo json_encode([
            "msg"=>'密码修改成功请重新登录',
            "state"=>200
        ]);
        return;
    }
}
