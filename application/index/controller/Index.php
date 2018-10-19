<?php
namespace app\index\controller;

use app\index\model\Admin_url;
use app\index\model\Admin_user;
use think\Controller;
use think\Session;

class Index extends Controller
{
    public $beforeActionList = [
        'LandingDetection'=> ['except'=>'login,logins,login_state,setadmin'],
        'SignIn'=>['only'=>'login'],
    ];
    //登录界面
    public function login()
    {
        $model = new Admin_user();
        if(count($model->all()) == 0)
        {
            return $this->fetch('SetAdmin');
        }
        return $this->fetch();
    }

    public function SetAdmin($user_id,$pass,$name)
    {
        $model = new Admin_user();
        if(count($model->all()) > 0)
        {
            return $this->redirect('/');
        }
        //没有超级用户
        $pass = md5(md5($user_id).md5($pass).md5("!@#$%^&*()_+"));
        if(!$model->insert(['user_id'=>$user_id,'pass'=>$pass,'creation_time'=>time(),
            'grade'=>9,'user_name'=>htmlentities($name)]))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'设置失败'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'设置完成'
        ]);
        return;
    }

    public function login_state($user_id)
    {
        $model = new Admin_user();
        if(!$model->get([
            'user_id'=>$user_id,
            'login_state'=>1
        ])){
            echo json_encode([
                'state'=>200
            ]);
            return;
        }
        echo json_encode([
            'state'=>400
        ]);
        return;
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
        ])->update(['distinguish'=>$state,'login_time'=>time(),'login_state'=>1]))
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
        $model = new Admin_user();
        $model->where(['user_id'=>$session->get('admin_user')])->update(
            ['login_state'=>0]
        );
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
    public function ErrorPage($msg=null)
    {
        $this->assign('msg',['msg'=>$msg]);
        return $this->fetch();
    }
}
