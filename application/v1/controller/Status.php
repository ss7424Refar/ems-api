<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-12-15
 * Time: 上午9:29
 */

namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;

class Status extends Common{

    public function getPendingReturn() {

        try {
            $pageSize = $this->request->param('limit');
            $offset = $this->request->param('offset');
            $search = $this->request->param('search');

            // 获取T系统账号
            $userInfo = $this->loginUser;

            $allData = Db::table('ems_main_engine')->where('model_status', USING)
                ->where('user_id', $userInfo['ems'])->order('fixed_no desc')->select();

            return apiResponse(SUCCESS, '[Status][getPendingReturn] success',
                        $this->getKeywordData($search, $allData, $offset, $pageSize));
        } catch (Exception $e) {
            Log::record('[Status][getPendingReturn] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function getPendingAssign() {
        try {
            $pageSize = $this->request->param('limit');
            $offset = $this->request->param('offset');
            $search = $this->request->param('search');

            $allData = Db::table('ems_main_engine')->where('model_status', ASSIGNING)
                        ->order('fixed_no desc')->select();

            return apiResponse(SUCCESS, '[Status][getPendingAssign] success',
                $this->getKeywordData($search, $allData, $offset, $pageSize));
        } catch (Exception $e) {
            Log::record('[Status][getPendingAssign] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function getPendingScrap() {
        try {
            $pageSize = $this->request->param('limit');
            $offset = $this->request->param('offset');
            $search = $this->request->param('search');

            // 获取T系统账号
            $userInfo = $this->loginUser;

            $usr = Db::table('ems_user')->where('USER_ID', $userInfo['ems'])
                ->where('IS_DELETED', 0)->find();

            $allData = array();

            if (ADMIN == $userInfo['roleId'] || EMS_AUDITOR == $userInfo['roleId']) {
                $allData = Db::table('ems_main_engine')->where('model_status', SCRAP_REVIEW)
                    ->order('fixed_no desc')->select();
            } elseif (EMS_ADMIN == $userInfo['roleId']) {
                $allData = Db::table('ems_main_engine')->where('scrap_operator', $usr['USER_NAME'])
                    ->where('model_status', SCRAP_REVIEW)->order('fixed_no desc')->select();
            }

            return apiResponse(SUCCESS, '[Status][getPendingScrap] success',
                $this->getKeywordData($search, $allData, $offset, $pageSize));
        } catch (Exception $e) {
            Log::record('[Status][getPendingScrap] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function getPendingDelete() {
        try {
            $pageSize = $this->request->param('limit');
            $offset = $this->request->param('offset');
            $search = $this->request->param('search');

            // 获取T系统账号
            $userInfo = $this->loginUser;

            $usr = Db::table('ems_user')->where('USER_ID', $userInfo['ems'])
                ->where('IS_DELETED', 0)->find();

            $allData = array();

            if (ADMIN == $userInfo['roleId']) {
                $allData = Db::table('ems_main_engine')->where('model_status', DELETE_REVIEW)
                    ->order('fixed_no desc')->select();
            } elseif (EMS_ADMIN == $userInfo['roleId']) {
                // 只统计自己申请的机子
                $allData = Db::table('ems_main_engine')->where('model_status', DELETE_REVIEW)
                    ->where('scrap_operator', $usr['USER_NAME'])->order('fixed_no desc')->select();
            } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId']) {
                // 只统计自己课下的机子
                $allData = Db::table('ems_main_engine')->where('model_status', DELETE_REVIEW)
                    ->where('section_manager', $userInfo['section'])->order('fixed_no desc')->select();
            }
            return apiResponse(SUCCESS, '[Status][getPendingDelete] success',
                $this->getKeywordData($search, $allData, $offset, $pageSize));
        } catch (Exception $e) {
            Log::record('[Status][getPendingDelete] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    public function getPendingBorrow() {
        try {
            $pageSize = $this->request->param('limit');
            $offset = $this->request->param('offset');
            $search = $this->request->param('search');

            // 获取T系统账号
            $userInfo = $this->loginUser;
            $allData = array();

            // 如果是Admin 显示所有数据
            if (ADMIN == $userInfo['roleId']) {
                $allData = Db::table('ems_main_engine')->where('model_status', BORROW_REVIEW)
                    ->order('fixed_no desc')->select();

            } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId']) {
                // 只统计自己课下的机子
                $allData = Db::table('ems_main_engine')->where('model_status', BORROW_REVIEW)
                    ->where('section_manager', $userInfo['section'])->order('fixed_no desc')->select();
            }
            return apiResponse(SUCCESS, '[Status][getPendingBorrow] success',
                $this->getKeywordData($search, $allData, $offset, $pageSize));
        } catch (Exception $e) {
            Log::record('[Status][getPendingBorrow] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }
    private function getKeywordData($search, $allData, $offset, $pageSize) {

        $allData = itemChange($allData);
        $column = getColumns('field');

        $jsonResult = array();

        if (null != $search) {
            foreach ($allData as $key => $row) {
                $rowExist = false;
                foreach ($column as $value) {
                    // 包含
                    $r = empty($row[$value]) ? '' : $row[$value];
                    if (stristr($r, $search) !== false) {
                        $rowExist = true;
                        break;
                    }
                }
                // 不存在的话删除
                if (!$rowExist) {
                    unset($allData[$key]);
                }
            }
        }

        $jsonResult['total'] = count($allData);
        $jsonResult['rows'] = array_slice($allData, $offset, $pageSize);

        return $jsonResult;
    }
}