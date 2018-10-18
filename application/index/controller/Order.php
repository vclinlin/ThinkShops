<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/18
 * Time: 17:44
 */

namespace app\index\controller;


use app\index\model\Order_book;

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
}