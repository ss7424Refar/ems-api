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
    /**
     * showdoc
     * @catalog 接口文档/侧边栏(TableData)
     * @title 待归还
     * @description 待归还接口
     * @method post
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param search 必选 string 搜索文本的内容;初始化加载为null
     * @url http://domain/ems-api/v1/Status/getPendingReturn
     * @return {"status":0,"msg":"[Status][getPendingReturn] success","data":{"total":2,"rows":[{"fixed_no":"1011086","MODEL_NAME":"Geneve 10 CS SKU3","category":null,"SERIAL_NO":"YA146621W","CPU":"CpuType","HDD":"640GB","MEMORY":"4G","type":"1TE7UCU00I1","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14","mac_address":"","cd_rom":"DVD-BD","invoice_no":"2110540399","location":"库位153","department":"SWT部","section_manager":"SWV","remark":"IS归还工作机","model_status":"使用中","instore_operator":null,"instore_date":"2013-06-30 05:31:06","scrap_operator":null,"scrap_date":null,"user_id":"admin","start_date":"2019-12-12 16:22:22","predict_date":"2019-12-13 13:08:27","end_date":null,"approver_id":"admin","approve_date":"2019-12-12 16:22:22","user_name":"superAdmin","approver_name":"superAdmin","serial_number":"NO","supplier":""},{"fixed_no":"1010089","MODEL_NAME":"Altair 10","category":null,"SERIAL_NO":"XA125355H","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"XA125355H","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","location":"库位21-1","department":"DT部","section_manager":"SYD","remark":"IS归还。","model_status":"使用中","instore_operator":null,"instore_date":"2013-06-21 08:01:43","scrap_operator":null,"scrap_date":null,"user_id":"admin","start_date":"2019-12-12 16:22:22","predict_date":"2019-12-13 13:08:27","end_date":null,"approver_id":"admin","approve_date":"2019-12-12 16:22:22","user_name":"superAdmin","approver_name":"superAdmin","serial_number":"","supplier":""}]}}
     * @remark 根据搜索框内容进行全字段查询
     */
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
    /**
     * showdoc
     * @catalog 接口文档/侧边栏(TableData)
     * @title 待分配
     * @description 待分配接口
     * @method post
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param search 必选 string 搜索文本的内容;初始化加载为null
     * @url http://domain/ems-api/v1/Status/getPendingAssign
     * @return {"status":0,"msg":"[Status][getPendingAssign] success","data":{"total":1,"rows":[{"fixed_no":"0807004","MODEL_NAME":"Malmo10AC","category":null,"SERIAL_NO":"68012845J","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"75013491JU","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","location":"","department":"SWT部","section_manager":"CSV","remark":"张仁仁转工作机","model_status":"待分配","instore_operator":null,"instore_date":"2013-06-21 07:59:33","scrap_operator":null,"scrap_date":null,"user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"","approver_name":null,"serial_number":"","supplier":null}]}}
     * @remark 无
     */
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
    /**
     * showdoc
     * @catalog 接口文档/侧边栏(TableData)
     * @title 待报废审批
     * @description 待报废审批接口
     * @method post
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param search 必选 string 搜索文本的内容;初始化加载为null
     * @url http://domain/ems-api/v1/Status/getPendingScrap
     * @return {"status":0,"msg":"[Status][getPendingScrap] success","data":{"total":1,"rows":[{"fixed_no":"1806192","MODEL_NAME":"Altair DX10 CS1 1J-1 System","category":null,"SERIAL_NO":"6J170137H","CPU":"i5-8250U","HDD":"SATA 128GB","MEMORY":"8GB","type":"XR83JTG43BBAD11","purchase_date":"2018-06-24 00:00:00","invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"","mac_address":"","cd_rom":"","invoice_no":"iEXPn201806-0018","location":"","department":"DT部","section_manager":"HWD","remark":"手提至新竹Realtek，已由Allion銷毀，不再返回DBH.","model_status":"待报废审批","instore_operator":"q10357th","instore_date":"2018-06-24 14:45:24","scrap_operator":"李欣耘","scrap_date":"2019-11-28 08:59:54","user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":null,"approver_name":null,"serial_number":"iEXPn201806-0018","supplier":""}]}}
     * @remark 1. Admin/审批员权限显示所有; 2. 样品管理员显示自己申请的机器
     */
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
    /**
     * showdoc
     * @catalog 接口文档/侧边栏(TableData)
     * @title 待删除审批
     * @description 待删除审批接口
     * @method post
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param search 必选 string 搜索文本的内容;初始化加载为null
     * @url http://domain/ems-api/v1/Status/getPendingDelete
     * @return {"status":0,"msg":"[Status][getPendingDelete] success","data":{"total":1,"rows":[{"fixed_no":"1806192","MODEL_NAME":"Altair DX10 CS1 1J-1 System","category":null,"SERIAL_NO":"6J170137H","CPU":"i5-8250U","HDD":"SATA 128GB","MEMORY":"8GB","type":"XR83JTG43BBAD11","purchase_date":"2018-06-24 00:00:00","invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"","mac_address":"","cd_rom":"","invoice_no":"iEXPn201806-0018","location":"","department":"DT部","section_manager":"HWD","remark":"手提至新竹Realtek，已由Allion銷毀，不再返回DBH.","model_status":"待报废审批","instore_operator":"q10357th","instore_date":"2018-06-24 14:45:24","scrap_operator":"李欣耘","scrap_date":"2019-11-28 08:59:54","user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":null,"approver_name":null,"serial_number":"iEXPn201806-0018","supplier":""}]}}
     * @remark 数据为假数据; 1. Admin权限显示所有; 2. 样品管理员显示自己申请的机器, 3. 课长显示自己课的机器
     */
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
            } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId'] || ST_MANAGER == $userInfo['roleId']) {
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
    /**
     * showdoc
     * @catalog 接口文档/侧边栏(TableData)
     * @title 待借出审批
     * @description 待借出审批接口
     * @method post
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param search 必选 string 搜索文本的内容;初始化加载为null
     * @url http://domain/ems-api/v1/Status/getPendingBorrow
     * @return {"status":0,"msg":"[Status][getPendingBorrow] success","data":{"total":1,"rows":[{"fixed_no":"1806192","MODEL_NAME":"Altair DX10 CS1 1J-1 System","category":null,"SERIAL_NO":"6J170137H","CPU":"i5-8250U","HDD":"SATA 128GB","MEMORY":"8GB","type":"XR83JTG43BBAD11","purchase_date":"2018-06-24 00:00:00","invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"","mac_address":"","cd_rom":"","invoice_no":"iEXPn201806-0018","location":"","department":"DT部","section_manager":"HWD","remark":"手提至新竹Realtek，已由Allion銷毀，不再返回DBH.","model_status":"待报废审批","instore_operator":"q10357th","instore_date":"2018-06-24 14:45:24","scrap_operator":"李欣耘","scrap_date":"2019-11-28 08:59:54","user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":null,"approver_name":null,"serial_number":"iEXPn201806-0018","supplier":""}]}}
     * @remark 数据为假数据; 1. Admin权限显示所有; 2. 课长显示自己课的机器
     */
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

            } elseif (T_MANAGER == $userInfo['roleId'] || S_MANAGER == $userInfo['roleId'] || ST_MANAGER == $userInfo['roleId']) {
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