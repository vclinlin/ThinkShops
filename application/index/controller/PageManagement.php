<?php
/**
 * Created by PhpStorm.
 * User: 17424
 * Date: 2018/9/29
 * Time: 18:57
 */

namespace app\index\controller;


use app\index\model\Admin_url;
use app\index\model\Broadcast_msg;
use app\index\model\Gallery;
use app\index\model\Home_url;
use app\index\model\Logo_img;
use app\index\model\Logo_text;
use think\Exception;
use think\Session;

class PageManagement extends Index
{
    public function Broadcast(){  //轮播图管理
        $model = new Gallery();
        $data = $model->order('createtime','desc')->paginate(15);
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    public function UploadPicture($name)
    {
        $model = new Gallery();
        $session = new Session();
        $files = $this->request->file('file');
        $yesNum =0;
        $noNum = 0;
        try {
            foreach ($files as $file) {
                //支持常见图片的上传
                $info = $file
                    ->validate([
                        'ext' => 'bmp,jpg,png,tif,gif,pcx,tga,exif,fpx,svg,
                        psd,cdr,pcd,dxf,ufo,eps,ai,raw,WMF,webp'])
                    ->move('./gallery/', time() . rand(rand(1, 1000), rand(1000, 100000)));
                if (!$info) {
                    $noNum += 1;
                    continue;
                }
                $model->insert([
                    'file_path' => '/gallery/' . $info->getSaveName(),
                    'name' => htmlentities($name),
                    'createtime' => time(),
                    'creator' => $session->get('admin_user')
                ]);
                $yesNum += 1;
            }
            echo json_encode([
                'state'=>200,
                'count' => $yesNum + $noNum,
                'yes' => $yesNum,
                'no' => $noNum,
                'msg' => '上传完成'
            ]);
            return;
        }catch (Exception $e)
        {
            echo json_encode([
                'state'=>400,
                'msg' => '上传失败'
            ]);
            return;
        }
    }

    public function DelImage($id)
    {
        $model = new Gallery();
        $data = $model->get(['Id'=>$id]);
        if(!$data)
        {
            echo json_encode([
               'state'=>400,
               'msg'=>'删除失败'
            ]);
            return;
        }
        if(file_exists('.'.$data['file_path']))
        {
            if(!unlink('.'.$data['file_path']))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'删除失败'
                ]);
                return;
            }
        }
        if(!$model->where(["Id"=>$id])->delete())
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'记录删除失败'
            ]);
            return;
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'已删除'
        ]);
        return;
    }
    public function DelImages(){
        $model = new Gallery();
        $data = $model->all();
        foreach ($data as $list)
        {
            try {
                if (!file_exists('.' . $list['file_path'])) {
                    $model->where(['Id' => $list['Id']])->delete();
                }
            }catch (Exception $e){
                continue;
            }
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'清理完成'
        ]);
        return;
    }

    public function editName($name,$Id) //修改图库备注
    {
        $model = new Gallery();
        try {
            $model->where(['Id' => $Id])->update(['name' => htmlentities($name)]);
                }catch (Exception $e) {
            return $e->getMessage();
        }
        return;
    }

    public function setHomeUrl($url=null)
    {
        if($url==null) {
            return $this->fetch();
        }
        $model = new Home_url();
        if(!$model->where(['Id'=>1])->update(['url'=>htmlentities($url)]))
        {
            if(!$model->insert(['Id'=>1,'url'=>htmlentities($url)]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'设置失败'
                ]);
                return;
            }
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'设置完成'
        ]);
        return;
    }
    public function setAdminUrl($url=null)
    {
        if($url==null) {
            return $this->fetch();
        }
        $model = new Admin_url();
        if(!$model->where(['Id'=>1])->update(['url'=>htmlentities($url)]))
        {
            if(!$model->insert(['Id'=>1,'url'=>htmlentities($url)]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'设置失败'
                ]);
                return;
            }
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'设置完成'
        ]);
        return;
    }
    public function homePage()
    {
        $BroadcastModel = new Broadcast_msg();
        $logo_text = new Logo_text();
        $BroadcastData = $BroadcastModel->order('sort','asc')->select();
        //轮播图列表
        $this->assign('Broadcast',$BroadcastData);
        $this->assign('logo_text',$logo_text->get(['Id'=>1]));
        return $this->fetch();
    }
    public function SelectBroadcast($key=null)
    {
        $galleryModel = new Gallery();
        $data = $galleryModel->order('name','desc')
            ->where('name','like','%'.$key.'%')
            ->paginate(10,'false',['query' =>request()->param()]);
        $page = $data->render();
        $this->assign('page',$page);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function selectedList() //设置轮播图
    {
        $Broadcast = new Gallery();
        $session = new Session();
        $Admin_url = new Admin_url();
        $Broadcast_msg = new Broadcast_msg();
        $rel = $Admin_url->get(['Id'=>1]);
        if(!$rel)
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'请先设置后台地址'
            ]);
            return;
        }
        $url = $rel['url'];
        $list = $this->request->post();
        try {
            foreach ($list['data'] as $item) {
                $rel = $Broadcast->get(['Id' => $item]);
                if (!$rel) {
                    continue;
                }
                if (!file_exists('.' . $rel['file_path'])) {
                    continue;
                }
                $data = [
                    'admin_user' => $session->get('admin_user'),
                    'url' => $url . $rel['file_path'],
                    'name' => $rel['name'],
                    'create_time' => time()

                ];
                $Broadcast_msg->insert($data);
            }
            echo json_encode([
                'state'=>200,
                'msg'=>'设置成功'
            ]);
            return;
        }catch (Exception $e){
            echo json_encode([
                'state'=>400,
                'msg'=>'网络异常,稍后再试'
                ]);
            return;
        }

    }
    public function editListName($name,$Id) //修改轮播图备注
    {
        $model = new Broadcast_msg();
        try {
            $model->where(['Id' => $Id])->update(['name' => htmlentities($name)]);
        }catch (Exception $e) {
            return $e->getMessage();
        }
        return;
    }
    public function editSort($sort,$Id) //修改轮播图备注
    {
        $model = new Broadcast_msg();
        $rel = $model->where([   //获取原顺序为$sort的数据
            ['Id','<>',$Id],
            ['sort','=',$sort]
        ])->select();
        $rels = $model->get(['Id'=>$Id]);
        try {
            if(count($rel)>0)
            {
                //交换顺序
                $model->where(['Id' => $rel[0]['Id']])->update(['sort' => $rels['sort']]);
            }
            $model->where(['Id' => $Id])->update(['sort' => $sort]);
        }catch (Exception $e) {
            return $e->getMessage();
        }
        return;
    }

    public function AddLogo($logo_text)
    {
        $model = new Logo_text();
        if(!$model->where(['Id'=>1])->update(['logo'=>htmlentities($logo_text)]))
        {
            if(!$model->insert(['Id'=>1,'logo'=>htmlentities($logo_text)]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'设置失败'
                ]);
                return;
            }
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'设置完成'
        ]);
        return;
    }

    public function setLogo($key = null)
    {
        $galleryModel = new Gallery();
        $data = $galleryModel->order('name','desc')
            ->where('name','like','%'.$key.'%')
            ->paginate(10,'false',['query' =>request()->param()]);
        $page = $data->render();
        $this->assign('page',$page);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function setLogos($Id)
    {
        $Admin_url = new Admin_url();
        $rel = $Admin_url->get(['Id'=>1]);
        $Gallery = new Gallery();
        $rels = $Gallery->get(['Id'=>$Id]);
        if(!$rel)
        {
            echo json_encode([
                'state'=>400,
                'msg'=>'请先设置后台地址'
            ]);
            return;
        }
        $model = new Logo_img();
        if(!$model->where(['Id'=>1])->update(['url'=>$rel['url'].$rels['file_path']]))
        {
            if(!$model->insert(['Id'=>1,'url'=>$rel['url'].$rels['file_path']]))
            {
                echo json_encode([
                    'state'=>400,
                    'msg'=>'设置失败'
                ]);
                return;
            }
        }
        echo json_encode([
            'state'=>200,
            'msg'=>'设置完成'
        ]);
        return;
    }
}