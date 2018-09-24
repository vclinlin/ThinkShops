<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/24
 * Time: 16:02
 */

namespace app\index\widget;


use think\Controller;

class WidgetCommon extends Controller
{
    public function meuNav(){
        return $this->fetch('common/meunav');
    }
}