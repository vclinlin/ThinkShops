<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/29
 * Time: 13:13
 */

namespace app\index\widget;


use app\index\controller\Index;
use app\index\model\Admin_user;

class WidgetData extends Index
{
    public function getAdminName($user_id)
    {
        $model = new Admin_user();
        $data  = $model->get(['user_id'=>$user_id]);
        return $data['user_name'];
    }
}