<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/8
 * Time: 11:27
 */

namespace app\index\controller;


use app\index\model\Book_class;
use app\index\model\Books;
use app\index\model\Item_class;

class Commodity extends Index
{
    public function Purchase($key=null)
    {
        $book = new Books();
        if($key == null)
        {
            $data = $book->alias('A')
                ->join('item_class B','A.item_class_id = B.Id','LEFT')
                ->field('A.*,B.book_class_name,B.name')
                ->order('sales','desc')
                ->paginate(15,'false',['query' =>request()->param()]);
            $this->assign('book',$data);
            $this->assign('page',$data->render());
        }else{
            $data = $book->alias('A')
                ->join('item_class B','A.item_class_id = B.Id','LEFT')
                ->where('A.bookname|B.name|B.book_class_name|A.press',
                    'like','%'.htmlentities($key).'%')
                ->field('A.*,B.book_class_name,B.name')
                ->order('sales','desc')
                ->paginate(15,'false',['query' =>request()->param()]);
            $this->assign('book',$data);
            $this->assign('page',$data->render());
        }
        return $this->fetch();
    }

    public function getBookClass()//加载主分类
    {
        $model = new Book_class();
        $data = $model->field('Id,name')->select();
        echo json_encode($data);
        return;
    }

    public function getItemClass($id)//加载二级分类
    {
        $model = new Item_class();
        $data  = $model->where(['book_class_id'=>$id])->field('Id,name')->select();
        echo json_encode($data);
        return;
    }
    public function setCover($Id)
    {
        $book = new Books();
        $data = $book->alias('A')
            ->join('item_class B','A.item_class_id = B.Id','LEFT')
            ->where(['A.Id'=>$Id])
            ->field('A.*,B.book_class_name,B.name,B.book_class_id')
            ->order('sales','desc')->find();
        //不存在的商品
        if(!$data)
        {
            $this->redirect(url('index/index/ErrorPage',['msg'=>'书籍信息错误']));
            return;
        }
        $this->assign('book',$data);
        return $this->fetch();
    }

    public function editCommodity($Id)
    {
        $book = new Books();
        if(!$rel = $book->get(['Id'=>$Id]))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'非正常操作'
            ]);
            return;
        }
        $data = $this->request->post();
        if(isset($data['bookname']))
        {
            $data['bookname'] = htmlentities($data['bookname']);  //转义书籍名
        }
        if(isset($data['press']))
        {
            $data['press'] = htmlentities($data['press']);    //转义出版社名
        }
        if(isset($data['r_time']))
        {
            $data['r_time'] = strtotime($data['r_time']);     //转换为时间戳
        }
        if(isset($data['count']))
        {
            if($data['count']<$rel['sales'])  //总数小于销量
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'该书籍已售'.$rel['sales'].'本,故最低数量不得低于'.$rel['sales'].'本'
                ]);
                return;
            }
        }
        if(!$book->where(['Id'=>$Id])->update($data))
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'信息更新失败'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'信息更新完成'
        ]);
        return;
    }
}