<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/10/8
 * Time: 11:27
 */

namespace app\index\controller;


use app\index\model\Books;

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

    public function setCover($Id)
    {
        $book = new Books();
        $data = $book->alias('A')
            ->join('item_class B','A.item_class_id = B.Id','LEFT')
            ->where(['A.Id'=>$Id])
            ->field('A.*,B.book_class_name,B.name')
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
}