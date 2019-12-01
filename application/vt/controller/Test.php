<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午3:55
 */

namespace app\vt\controller;

use think\Controller;
use think\Db;
use think\Session;

class Test extends Controller {
    function test(){
//        $res = Db::table('ems_user')->where('user_id', 'admin')->find();
//
//        dump($res['USER_NAME']);

//        dump(json_decode(DEPART, true));

//        Session::set('login_user', array('T'=>'A', 'ems'=>'hello'));
//
//        dump(Session::get('login_user')['T']);


//        $arr = array('http://www.cnblogs.com/','博客园','PHP教程');
//        foreach($arr as $k=>$v){
//            echo $k."=>".$v."<br />";
//        }

        // 先查询ems中的section
        $subSqlA = Db::table('ems_user')->where('SECTION', '2271')
            ->where('IS_DELETED', 0)->buildSql();
        // 查询T系统roleId 为课长
        $subSqlB = Db::table('users')->where('role_id', MANAGER)->buildSql();

        $res = Db::table($subSqlA . ' a')
            ->join([$subSqlB=> 'b'], 'a.id=b.ems_uid')->select();

        $tos = array();
        for ($j = 0; $j < count($res); $j++) {
            array_push($tos, $res[$j]['email']);
        }
        dump($tos);
    }

    public function test2() {


        ini_set('memory_limit','500M');
        $list = Db::table('ems_main_engine')->order('fixed_no desc')->select();
        dump($list);

//        ini_set('memory_limit','500M');
//        // php 当数据大起来的时候，Db::table的select()会报错..其实是内存不足的原因. 所以暂时导出几个关键的字段.
//        $list = Db::table('ems_main_engine')->select();
//        dump($list);
    }
}