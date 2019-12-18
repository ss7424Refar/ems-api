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


    static $working = 1;
    const work = 1;

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


//        ini_set('memory_limit','500M');
//        $list = Db::table('ems_main_engine')->order('fixed_no desc')->select();
//
//        $statusArray = json_decode(STATUS, true);
//        $departArray = json_decode(DEPART, true);
//        $sectionArray = json_decode(SECTION, true);
//
//        foreach ($list as $row) {
//            $item = [];
//            foreach ($row as $key => $value) {
//                // 其中有个字段是为null的, 所以数组会存在越界
//                if ('model_status' == $key) {
//                    if (null != $value) {
//                        $value = $statusArray[$value];
//                    }
//                }
//                if ('department' == $key) {
//                    if (null != $value) {
//                        $value = $departArray[$value];
//                    }
//                }
//                if ('section_manager' == $key) {
//                    if (null != $value) {
//                        $value = $sectionArray[$value];
//                    }
//                }
//                $item[] = $value;
////                $item[] = mb_convert_encoding($value,'GBK','UTF-8');
//            }
//
//            dump($item);
//            break;
//        }


//        $data=array ("hello"=>1,"hell2o"=>122,"hello3"=>133);
//
//        unset($data['hello']);
//
//        dump($data);

//        dump(config('database.database'));

//        dump(self::work);
//        dump(self::$working);

//        $usr = Db::table('ems_user')->where('USER_ID', '500323')
//            ->where('IS_DELETED', 0)->find();
//        dump($usr);
//        $address = [];
//        $subSqlA = Db::table('ems_user')->where('SECTION', '2271')
//            ->where('IS_DELETED', 0)->buildSql();
//        // 查询T系统roleId 为课长
//        $subSqlB = Db::table('users')->whereIn('role_id', [T_MANAGER, S_MANAGER])->buildSql();
//
//        $res = Db::table($subSqlA . ' a')
//            ->join([$subSqlB=> 'b'], 'a.id=b.ems_uid')->field('MAIL')->select();
//
//        foreach ($res as $key => $value) {
//            $address[] = $value['MAIL'];
//        }
//        dump($address);
//        $allData = Db::table('ems_main_engine')->where('model_status', USING)
//            ->order('fixed_no desc')->select();
//
//
//        $allData = itemChange($allData);
//        $column = getColumns('field');
//
//        $search = $this->request->param('search');
//
//        if (null != $search) {
//            foreach ($allData as $key => $row) {
//                $rowExist = false;
//                foreach ($column as $value) {
//                    // 包含
//                    if (stristr($row[$value], $search) !== false) {
//                        $rowExist = true;
//                        break;
//                    }
//
//                }
//                // 不存在的话删除
//                if (!$rowExist) {
//                    unset($allData[$key]);
//                }
//            }
//        }
//        dump(array_slice($allData, 0, 6));
//        $data['model_status'] = '使用中';
//        $status = json_decode(STATUS);
//        $data['model_status'] = array_search($data['model_status'], $status);
//        dump($data['model_status']);

        $formData = '{"status":null}';
        $formData = json_decode($formData);

        if (null != $formData->status) {
            dump('hello');
        }
    }
}