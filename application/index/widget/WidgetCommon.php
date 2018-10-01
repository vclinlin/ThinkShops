<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/24
 * Time: 16:02
 */

namespace app\index\widget;


use app\index\controller\Index;
use app\index\model\Admin_url;
use app\index\model\Home_url;

class WidgetCommon extends Index
{
    public function setUrl()
    {
        $home = new Home_url();
        if(!$home->get(['Id'=>1]))
        {
            echo url('index/PageManagement/setHomeUrl');
            return;
        }
        $admin = new Admin_url();
        if(!$admin->get(['Id'=>1]))
        {
            echo url('index/PageManagement/setAdminUrl');
            return;
        }
        echo $home->get(['Id'=>1])['url'];
        return;
    }
}