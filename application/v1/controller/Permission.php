<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-29
 * Time: 上午11:57
 */

namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;

class Permission extends Common {
    public function getPermission() {


    }

    public function getNavBarList() {
        // 样机状态与导航栏的对应关系
        $statusList = array('ems_nav_return'=>3, 'ems_nav_assign'=>2, 'ems_nav_scrap'=>4, 'ems_nav_delete'=>6,
            'ems_nav_borrow_review'=>1, 'ems_nav_scrap_review'=>4, 'ems_nav_delete_review'=>6);

        // 获取T系统账号
        $userInfo = $this->loginUser;

        try {
            $subSqlA = Db::table('role_rights')->where('role_id', $userInfo['roleId'])->buildSql();
            $subSqlB = Db::table('rights')->where('description', 'LIKE', 'ems_nav_%')->buildSql();

            // 获取该账号的权限名
            $res = Db::table($subSqlA . ' a')
                ->join([$subSqlB=> 'b'], 'a.right_id=b.id')->select();

            dump($res);

            $jsonResult = array();
            // 取得侧边栏上的numbers

            for ($i = 0; $i < count($res); $i++) {
                $status = $statusList[$res[$i]['description']];
                // 以前没有字段说明报废/删除是谁提出的, 所以待报废/待删除会统计全部数目
                $tmp = array();

                $tmp['label'] = $res[$i]['description'];

                // 领导脑子有坑, 写不下去了！
                $count = Db::table('');


            }
//            return apiResponse(SUCCESS, 'get history success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Permission][getNavBarList] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

}
