<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/28
 * Time: 10:20
 */

namespace app\index\model;


use think\Model;

class Book_class extends Model
{
    public function getId($key,$value){  //获取id
        return $this->where([$key=>$value])->field('Id')->select();
    }
}