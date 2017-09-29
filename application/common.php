<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * @param $ip  IP地址
 * @return mixed|SimpleXMLElement   高德地图返回的所在地数据
 */
function getLocation($ip)
{
    ($ip == '127.0.0.1')&&$ip = '115.196.225.254';
    $locationUrl = "http://restapi.amap.com/v3/ip?key=".config('GAODEKEY')."&ip=".$ip;
    $location = \curl\Curl::cUrl($locationUrl);
    return $location;
}
/**
 * @param string $param 参数
 * @return string       自定义加密数据
 */
function getHash($param = '')
{
    $salt = config('SALT');
    if (empty($param)){
        $param = $salt;
    }else{
        $param = $salt.$param;
    }
    $hash = md5($param.time());
    return $hash;
}

/**
 * @param $uid          用户id
 * @param $hash         hash值
 * @param int $time     过期时间戳
 * @return int|string   是否插入成功
 */
function saveHash($uid,$hash,$time=86400)
{
    $query = \think\Db::name('UsersHash');
    $data['user_id']  = $uid;
    $data['hash'] = $hash;
    $data['end_time'] = time()+$time;
    $res = $query->where('user_id',$uid)->field('id')->find();
    if ($res){
        $res = $query->where('id',$res['id'])->update($data);
    }else{
        $res = $query->insert($data);
    }
    return $res;
}

/**
 * @param $uid   用户id
 * @param $hash  HASH值
 * @return array|false|int|PDOStatement|string|\think\Model  记录存在并删除返回true   记录不存在返回false
 */
function findHash($uid,$hash)
{
    $Query = \think\Db::name('UsersHash');
    $res = $Query->where('user_id',$uid)->where('hash',$hash)->where('end_time','>',time())->find();
    if ($res){
        return $res;
    }
    return false;
}


/**
 * @param $data   传入的数组
 * @param int $length  新数组的长度
 * @return array      筛选过后的数组
 */
function randArray($data,$length = 5){
    $array = [];
    for ($i = 0; $i < $length;$i++){
        $key = array_rand($data);
        $array[] = $data[$key];
        unset($data[$key]);
    }
    return $array;
}

/**
 * 删除数组中的指定元素
 * @param $data     初始数组
 * @param $value    要删除的元素值
 * @return mixed    最终数组
 */
function removeValue($value,$data=array()){
    if (in_array($value,$data)){
        $data = array_values($data);
        $key = array_search($value,$data);
        array_splice($data,$key,1);
    }
    return $data;
}