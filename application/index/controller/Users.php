<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/28 0028
 * Time: 9:28
 */

namespace app\index\controller;

use app\index\model\Favorite;
use app\common\model\Users AS userModel;
use think\Db;
use think\Request;

class Users extends Base
{
    public function _initialize()
    {
        parent::_initialize();

    }
    //用户中心
    public function index($id)
    {
        $userModel = new userModel();
        $user = $userModel::get($id);
        //处理积分等级
        $postsLevel = Db::name('level')->field('point,icon,number')->order('point DESC')->select();
        $level = processLevel($postsLevel,$user->points);
        $user->user_level = $level[0];
        $user->level_icon = $level[1];
        $this->assign([
            'id'   => $id,
            'user' => $user,
        ]);
        return $this->fetch();
    }

    public function setting()
    {
        return $this->fetch();
    }

    //用户收藏
    public function favorite($type='')
    {
        if (!($this->uid))$this->error('需要您先登陆噢=￣ω￣=',url('/login'));
        $query = Db::name('favorite');
        //根据条件筛选
        if (empty($type)){
            $list = $query->where('user_id',$this->uid)->order('create_time DESC')->paginate(8);
        }else{
            $list = $query->where('user_id',$this->uid)->where('article_type',$type)->order('create_time DESC')->paginate(8);
        }
        $favorites = [];
        foreach ($list as $key => $value){
            $favorites[] = $value;
        }

        foreach ($favorites as $key => $value){
            $favorites[$key]['create_time'] = timeToString($value['create_time']);
        }
        $page = $list->render();
        $this->assign([
            'page'      => $page,
            'type'      => $type,
            'favorites' => $favorites,
        ]);
        return $this->fetch();
    }

    public function favoriteDel(Request $request)
    {
        if (!($this->uid))$this->error('需要您先登陆噢=￣ω￣=',url('/login'));
        if ($request->isDelete()){
            $query = Db::name('favorite');
            $data = input('param.');
            //id集合
            $ids = explode('-',$data['id']);
            $ids = array_filter($ids);
            //找出用户所有的收藏
            $resId = $query->where('user_id',$this->uid)->column('id');
            foreach ($ids as $value){
                if (!in_array($value,$resId)){
                    $this->error('数据删除错误,存在不属于自己的收藏信息');
                }
            }
            $res = Favorite::destroy($ids);
            (false !== $res)?$this->success('删除成功'):$this->error('删除失败');
        }
    }

    /*//重新发送邮件
    public function resendMail(Request $request)
    {
        if ($request->isPost()){
            $data = input('param.');
            if (($this->uid) != $data['uid'])$this->error('非法用户=￣ω￣=',url('/login'));
            $message = "爱编程论坛,请点击下面网址进行激活账号^_^~,有效时间为24小时.";
            $hash = getHash($data['uid']);
            $href = config('DOMAIN').'/check/uid/'.$data['uid'].'/hash/'.$hash;
            $message = $message.$href;

            //保存hash验证值
            $res = saveHash($data['uid'],$hash);
            if (!$res){
                $data['status']  = 2;
                $data['message'] = '保存hash验证值失败';
                return $data;
            }
            $res = Db::name('Users')->where('id',$data['uid'])->field('email')->find();
            $res = Common::sendEmail($data['uid'],$res['email'],$message);
            if ($res['status'] == 2){
                $this->error($res['message']);
            }
            $this->success('邮件发送成功,请前去邮箱验证');
        }
    }


    //验证hash
    public function check()
    {
        if (!($this->uid))$this->error('需要您先登陆噢=￣ω￣=',url('/login'));
        $data = input('param.');
        $res = findHash($data['uid'],$data['hash']);
        if ($res){
            $res = Db::name('Users')->where('id',$data['uid'])->update(['is_validate'=>1]);
        }
        if($res !== false){
            $this->success('验证成功!',url('/user/'.$data['uid']));
        }else{
            $this->assign([
                'signal' => 'fail'
            ]);
        }
        return $this->fetch();
    }*/


}