<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午5:00
 */

namespace app\v1\controller;

use think\Exception;
use think\Session;
use think\Db;
use think\Log;

class User extends Common {

    public function getPermission() {

        try {
            // 查询ems_role_user可能会存在2条记录
            $subSqlA = Db::table('ems_role_user')->field('ROLE_ID, TECH_ID')->buildSql();

            $res = Db::table('ems_user')->where(['user_id'=>$this->loginUserId])
                ->field('ID, USER_ID, USER_NAME')->alias('a')
                ->join([$subSqlA=>'b'], 'a.ID = b.TECH_ID')->select();
            dump($res);
        } catch (Exception $e) {
            Log::record('[User][getPermission] error '. $e->getMessage());
        }


    }

    public function getDetailById() {


    }
}