<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/18
 * Time: 17:44
 */

namespace app\index\controller;


use app\index\model\Admin_user;
use app\index\model\Order_book;
use think\Session;

class Order extends Index
{
    //未支付
    public function notPay()
    {
        $order = new Order_book();
        //查询未过期,未支付的订单
        $order_data = $order->where([
            'order_state'=>0,
            'pay_state'=>0
        ])->paginate(15);
        $this->assign('data',$order_data);
        $this->assign('page',$order_data->render());
        return $this->fetch();
    }
    //未发货
    public function notDeliver()
    {
        $order = new Order_book();
        //查询已支付还未发货的订单
        $order_data = $order->where([
            'order_state'=>0,
            'pay_state'=>1
        ])->paginate(15);
        $this->assign('data',$order_data);
        $this->assign('page',$order_data->render());
        return $this->fetch();
    }
    //已完成
    public function Completed()
    {
        $order = new Order_book();
        //查询已发货未签收的订单
        $order_data = $order->where([
            'order_state'=>4,
            'pay_state'=>1
        ])->paginate(15);
        $this->assign('data',$order_data);
        $this->assign('page',$order_data->render());
        return $this->fetch();
    }

    public function delOrder($id,$pass)
    {
        /**
         * 验证密码
         */
        $session = new Session();
        //离线验证
        $pass = md5(md5($session->get('admin_user')).md5($pass).md5('!@#$%^&*()_+'));
        if($pass != $session->get('admin_pass'))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'密码错误'
            ]);
            return;
        }
        //绝对验证
        $user = new Admin_user();
        if(!$user->get([
            'user_id'=>$session->get('admin_user'),
            'pass'=>$pass,
            'grade'=>9
        ]))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'权限不足'
            ]);
            return;
        }
        //删除订单
        $order = new Order_book();
        if(!$order->where([
            'Id'=>$id,
            'pay_state'=>0,
            'order_state'=>0
        ])->delete())
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'用户已取消该订单,或已支付'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已删除'
        ]);
        return;
    }

    public function DeliverNum($id,$express,$order_number)
    {
        $order = new Order_book();
        if(!$order->where([
            'order_number'=>$order_number,
            'Id'=>$id,
            'pay_state'=>1,
            'order_state'=>0
        ])->update(['express'=>$express,'delivery_time'=>time(),'order_state'=>4]))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'订单信息出错,刷新后再试'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已发货'
        ]);
        return;
    }
}