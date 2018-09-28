<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/28
 * Time: 16:23
 */

namespace app\index\controller;


use app\index\model\Book_class;
use think\Session;

class Category extends Index
{
    public function FatherLevel()  //一级分类展示
    {
        $model = new Book_class();
        $data = $model->paginate(15);
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function addFatherLevel($name)
    {
        $model = new Book_class();
        $session = new Session();
        if($name == "")
        {
            echo json_encode([
                'msg'=>'请输入有效分类值',
                'state'=>400
            ]);
            return;
        }
        $data= [
            'name'=>htmlentities($name),
            'createtime'=>time(),
            'creator'=>$session->get('admin_user')
        ];
        if($model->get(['name'=>htmlentities($name)]))
        {
            echo json_encode([
                'msg'=>'该分类已存在',
                'state'=>400
            ]);
            return;
        }
        if(!$model->insert($data))
        {
            echo json_encode([
                'msg'=>'添加失败,稍后再试',
                'state'=>400
            ]);
            return;
        }
        echo json_encode([
            'msg'=>'添加成功',
            'state'=>200
        ]);
        return;
    }
}