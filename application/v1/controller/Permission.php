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
        /**
         * - 普通用户 {申请} {导出}
         * - 样机管理员 {申请} {导出} {添加} {分配} {归还} {报废} {删除} {导入} {编辑}
         * - 样机审核员 {报废} {删除}
         * - S-Manager {申请} {导出}
         * - T-Manager {申请} {导出}
         */
        try {
            $roleId = $this->loginUser['roleId'];
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
            if (ADMIN == $roleId || S_MANAGER == $roleId || T_MANAGER == $roleId || EMS_ADMIN == $roleId) {
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
        $statusList = array('ems_nav_return'=>USING, 'ems_nav_assign'=>ASSIGNING,
                            'ems_nav_borrow_review'=>BORROW_REVIEW, 'ems_nav_scrap_review'=>SCRAP_REVIEW,
                            'ems_nav_delete_review'=>DELETE_REVIEW);

        $urlList = array('ems_nav_return'=>'/returned', 'ems_nav_assign'=>'/allocated',
            'ems_nav_borrow_review'=>'/approval', 'ems_nav_scrap_review'=>'/scrapp',
            'ems_nav_delete_review'=>'/delapp');

        try {
            // 获取T系统账号
            $userInfo = $this->loginUser;

            $subSqlA = Db::table('role_rights')->where('role_id', $userInfo['roleId'])->buildSql();
            $subSqlB = Db::table('rights')->where('description', 'LIKE', 'ems_nav_%')->buildSql();

            // 获取该账号的权限名
            $res = Db::table($subSqlA . ' a')
                ->join([$subSqlB=> 'b'], 'a.right_id=b.id')->select();

            $jsonResult = array();

            for ($i = 0; $i < count($res); $i++) {
                $desc = $res[$i]['description'];
                $status = $statusList[$desc];

                $tmp = array();
                $tmp['text'] = $desc;
                $tmp['url'] = $urlList[$desc];

                /**
                 * - 普通用户 {待归还}
                 * - 样机管理员 {待分配} {待报废审批} {待删除审批}
                 * - 样机审核员 {待报废审批}
                 * - S-Manager {待借出审批} {待删除审批}
                 * - T-Manager {待借出审批} {待删除审批}
                 */
                // 要获取数目的话, 只能一个个判断
                if ('ems_nav_assign' == $desc) {
                    // 待分配获取所有的机子数目
                    $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)->count();
                } elseif ('ems_nav_return' == $desc) {
                    // 待归还要保证是当前用户
                    $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)
                                        ->where('user_id', $userInfo['ems'])->count();
                } elseif ('ems_nav_borrow_review' == $desc) {
                    // 如果是Admin 显示所有数据
                    if (ADMIN == $userInfo['roleId']) {
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)->count();
                    } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId']) {
                        // 只统计自己课下的机子
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)
                            ->where('section_manager', $userInfo['section'])->count();
                    }
                } elseif ('ems_nav_delete_review' == $desc) {
                    // 如果是Admin 显示所有数据
                    if (ADMIN == $userInfo['roleId'] || EMS_ADMIN == $userInfo['roleId']) {
                        // 提交申请删除的机子 (没有delete_user_id字段所以显示全部)
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)->count();
                    } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId']) {
                        // 只统计自己课下的机子
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)
                            ->where('section_manager', $userInfo['section'])->count();
                    }
                }  elseif ('ems_nav_scrap_review' == $desc) {
                    if (ADMIN == $userInfo['roleId'] || EMS_AUDITOR == $userInfo['roleId']) {
                        // 提交申请删除的机子 (没有delete_user_id字段所以显示全部)
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)->count();
                    } elseif (EMS_ADMIN == $userInfo['roleId']) {
                        // 先查询user_name
                        $usr = Db::table('ems_user')->where('USER_ID', $userInfo['ems'])
                                    ->where('IS_DELETED', 0)->find();
                        // 只统计自己申请的机子
                        $tmp['num'] = Db::table('ems_main_engine')->where('model_status', $status)
                            ->where('scrap_operator', $usr['USER_NAME'])->count();
                    }
                }

                // 一般不会走到这个分支
                if (!array_key_exists('num', $tmp)) {
                    $tmp['num'] = 0;
                }
                $jsonResult[] = $tmp;
            }
            return apiResponse(SUCCESS, '[Permission][getNavBarList] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Permission][getNavBarList] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

}
