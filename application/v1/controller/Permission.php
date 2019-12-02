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
    /**
     * showdoc
     * @catalog 接口文档/权限相关
     * @title 主界面的显示隐藏
     * @description 主界面的显示隐藏接口
     * @method post
     * @url http://domain/ems-api/v1/Permission/getItems
     * @return {"status":0,"msg":"[Permission][getItems] success","data":{"ems_btn_add":true,"ems_btn_delete":true,"ems_btn_edit":true,"ems_btn_import":true,"ems_btn_return":false,"ems_btn_scrap":false,"ems_btn_update":false,"ems_chart":true}}
     * @return_param status int 状态码1代表失败
     * @return_param msg string 状态码说明
     * @remark 返回0， 代表获取数据
     */
    public function getItems() {
        $roleId = $this->loginUser['roleId'];

        try {
            // 获得所有的button内容
            $allRights = Db::table('rights')->where('description', 'LIKE', 'ems_btn_%')->select();

            $currentRight = Db::table('role_rights')->alias('a')
                ->join('rights b', 'a.right_id=b.id')
                ->where('role_id', $roleId)->where('description', 'LIKE', 'ems_btn_%')->select();

            $in_right = [];
            foreach ($currentRight as $right) {
                $in_right[] = $right['description'];
            }

            $jsonResult = array();
            foreach ($allRights as $right) {
                if (in_array($right['description'], $in_right)) {
                    $jsonResult[$right['description']] = true;
                } else {
                    $jsonResult[$right['description']] = false;
                }

            }
            // 获取chart的权限
            if (ADMIN == $roleId || MANAGER == $roleId || EMS_ADMIN == $roleId) {
                $jsonResult['ems_chart'] = true;
            } else {
                $jsonResult['ems_chart'] = false;
            }
            return apiResponse(SUCCESS, '[Permission][getItems] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Permission][getItems] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
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
