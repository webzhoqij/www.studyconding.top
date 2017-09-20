<?php

namespace app\index\controller;

use think\Db;

class Index extends Base
{
    public function index()
    {
        //找出所有的资讯和板块推荐
        $information = Db::name('information')->where('status = 1 AND is_del = 1')->field('id,author,title,pic,brief,look,comment,create_time')->order('look DESC')->limit(5)->select();
        //找出所有二级模块
        $module = Db::name('module')->where('status = 1 AND is_del = 1 AND pid != 0')->field('id,name,link')->order('sort DESC')->select();
        $this->assign([
           'information' => $information,
            'module'     => $module
        ]);
        return $this->fetch();
    }
}
