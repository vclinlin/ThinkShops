<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/28
 * Time: 16:23
 */

namespace app\index\controller;


use app\index\model\Book_class;
use app\index\model\Item_class;
use think\Session;

class Category extends Index
{
    public function FatherLevel($key=null)  //一级分类展示
    {
        $model = new Book_class();
        //默认操作
        if($key == null) {
            $data = $model->order('createtime','desc')->paginate(15);
            $page = $data->render();
            $this->assign('page', $page);
            $this->assign('data', $data);
            return $this->fetch();
        }
        //搜索
        $data = $model->where('name','like','%'.htmlentities($key).'%')->
        order('createtime','desc')->
        paginate(15,'false',['query' =>request()->param()]);
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('data', $data);
        return $this->fetch();
    }
    //显示所有一级分类
    public function FatherLevelAll()
    {
        $model = new Book_class();
        $data = $model->field(['Id','name'])->select();
        echo json_encode($data);
        return;
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

    public function addItemName($Id=0,$key = null,$get=0)
    {
//        echo $Id.'|' .  $key . '|' .  $get;
        $model = new Item_class();
        $book_model = new Book_class();
        //默认操作,或者参数不完整
        if(($key==null && $Id==0 && $get == 0) || ($key==null && $Id!=0))
        {
            $data = $model->order('book_class_name','desc')->paginate(15);
            $page = $data->render();
            $this->assign('page',$page);
            $this->assign('data',$data);
            $this->assign('is_search',['data'=>1]);
            return $this->fetch();
        }
        //查询操作
        if($Id == 0 && $key != null && $get == 0)
        {
            $data = $model->order('createtime','desc')->where('book_class_name|name|creator',
                'like','%'.htmlentities($key).'%')->paginate(15,'false',['query' =>request()->param()]);
            if(!$data)
            {
                $data = $model->where('createtime',
                    'like','%'.time($key).'%')->order('createtime','desc')->paginate(15,'false',['query' =>request()->param()]);
                $this->assign('page',$data->render());
                $this->assign('data',$data);
            }
            $this->assign('page',$data->render());
            $this->assign('data',$data);
            $this->assign('is_search',['data'=>1]);
            return $this->fetch();
        }
        //精确获取
        if($Id != 0 && $key != null  && $get == 1 && $book_model->get(['Id'=>$Id,'createtime'=>$key]))
        {
            $data = $model->where([
                'book_class_id'=>$Id
            ])->order('createtime','desc')->paginate(15,'false',['query' =>request()->param()]);
            $this->assign('page',$data->render());
            $this->assign('data',$data);
            $this->assign('addParameter',['Id'=>$Id]);
            $this->assign('is_search',['data'=>0]);
            return $this->fetch();
        }
        return $this->error('不能识别的操作');
    }

    public function delBookClass($Id)
    {
        $model = new Book_class();
        $item_model = new Item_class();
        if(!$model->where(['Id'=>$Id])->delete())
        {
            echo json_encode([
                'msg'=>'删除失败,稍后再试',
                'state'=>400
            ]);
            return;
        }
        $item_model->where(['book_class_id'=>$Id])->delete();
        echo json_encode([
            'msg'=>'已删除',
            'state'=>200
        ]);
        return;
    }
//添加子类
    public function addItem($book_class_id,$name)
    {
        $model = new Item_class();
        $book_model = new Book_class();
        $session = new Session();
        //父类信息
        $rel = $book_model->get(['Id'=>$book_class_id]);
        //创建者
        $user = $session->get('admin_user');
        if($model->get([
            'name'=>htmlentities($name),
            'book_class_id'=>$book_class_id
        ])){
            echo json_encode([
                'msg'=>'该一级分类下,存在一个同名子类',
                'state'=>400
            ]);
            return;
        }
        $saveData = [
            'book_class_id'=>$book_class_id,
            'name'=>htmlentities($name),
            'book_class_name'=>$rel['name'],
            'createtime'=>time(),
            'creator'=>$user
        ];
        if(!$model->save($saveData))
        {
            echo json_encode([
                'msg'=>'添加失败,稍后再试',
                'state'=>400
            ]);
            return;
        }
        echo json_encode([
            'msg'=>'已添加子类'.$name,
            'state'=>200
        ]);
        return;
    }
    //删除子类
    public function delItem($Id)
    {
        $model = new Item_class();
        if(!$model->where(['Id'=>$Id])->delete())
        {
            echo json_encode([
                'msg'=>'删除失败,稍后再试',
                'state'=>400
            ]);
            return;
        }
        echo json_encode([
            'msg'=>'已删除',
            'state'=>200
        ]);
        return;
    }
}