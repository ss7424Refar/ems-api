<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-29
 * Time: 上午11:57
 */

namespace app\v1\controller;

use think\Session;
use think\Db;
use think\Exception;
use think\Log;

class Permission extends Common {
    public function getPermission() {
        // 样机状态与导航栏的对应关系
        $statusList = array('ems_nav_return'=>3, 'ems_nav_assign'=>2, 'ems_nav_scrap'=>4, 'ems_nav_delete'=>7,
                            'ems_nav_borrow_review'=>1, 'ems_nav_scrap_review'=>4, 'ems_nav_delete_review'=>6);

        // 获取T系统账号
        $userInfo = $this->loginUser;

        try {

            $res = Db::table('role_rights')->where('role_id', $userInfo['T_role_id'])->alias('a')
                ->join('rights b', 'a.right_id=b.id', 'LEFT')->select();


//            return apiResponse(SUCCESS, 'get history success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Permission][getPermission] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function getNavBarList() {


    }

}
