<?php
namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;

class Machine extends Common {
    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 获取机器所有信息
     * @description 获取机器所有信息接口
     * @method get
     * @url http://domain/ems-api/v1/Machine/getMachineList
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @param formData 必选 json 表单信息 (let formData = JSON.stringify(this.form))
     * @return {"status":0,"msg":"[Machine][getMachineList] success","data":{"total":1,"rows":[{"fixed_no":"1201099","MODEL_NAME":"Katmai10FT ES-SKU3","category":null,"SERIAL_NO":"SN:ZB247462W","CPU":"cpuType","HDD":"123GB","MEMORY":"0GB","type":"NO","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14","mac_address":"","cd_rom":"DVD-BD","invoice_no":"20120109","three_c_flag":0,"three_c_code":null,"location":"库位35","department":"SWT部","section_manager":"SCD","remark":"SCD","reject_flag":"无","broken":"","model_status":"待借出审批","instore_operator":null,"instore_date":"2013-06-30 05:22:29","scrap_operator":null,"scrap_date":null,"user_id":"admin","start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"superAdmin","approver_name":null,"serial_number":"NO","supplier":"BYD","hasApply":true}]}}
     * @return_param status int 状态码
     * @return_param total int 总页数
     * @return_param hasApply boolean true代表有申请需要显示【取消申请】
     * @remark 需要将formData以json形式传递, {formData:{}}
     */
    public function getMachineList() {

        $pageSize = $this->request->param('limit');
        $offset = $this->request->param('offset');

        $formData = $this->request->param('formData');

        $map = getSearchCondition($formData);

        $jsonRes = array();

        try {

            $userId = $this->loginUser['ems'];

            $res = null;

            // 如果没填历史使用者
            if (empty($map['historyUser'])) {
                $res = Db::table('ems_main_engine')->where($map)->order('instore_date desc')
                        ->limit($offset, $pageSize)->select();

                foreach ($res as $key => $item) {
                    // 判断是否要有取消申请
                    if (BORROW_REVIEW == $item['model_status'] && $userId == $item['user_id'] ) {
                        $item['hasApply'] = true;
                    } else {
                        $item['hasApply'] = false;
                    }
                    $res[$key] = $item;
                }

                $res = itemChange($res);

                $total = Db::table('ems_main_engine')->where($map)->count();

                $jsonRes['total'] = $total;
                $jsonRes['rows'] = $res;

            } else {
                // 先查询ems_borrow_history
                $sqlA = Db::table('ems_borrow_history')->distinct(true)->field('fixed_no')
                            ->where('user_name', $map['historyUser'][0], $map['historyUser'][1])->buildSql();

                // 移除数组
                unset($map['historyUser']);
                $sqlB = Db::table('ems_main_engine')->where($map)->buildSql();

                // 查询
                $res = Db::table($sqlA . ' a')
                        ->join([$sqlB=> 'b'], 'a.fixed_no=b.fixed_no')
                        ->order('instore_date desc')
                        ->limit($offset, $pageSize)->select();

                foreach ($res as $key => $item) {
                    // 判断是否要有取消申请
                    if (BORROW_REVIEW == $item['model_status'] && $userId == $item['user_id'] ) {
                        $item['hasApply'] = true;
                    } else {
                        $item['hasApply'] = false;
                    }
                    $res[$key] = $item;
                }

                $res = itemChange($res);
                $total = Db::table($sqlA . ' a')->join([$sqlB=> 'b'], 'a.fixed_no=b.fixed_no')->count();

                $jsonRes['total'] = $total;
                $jsonRes['rows'] = $res;
            }

            return apiResponse(SUCCESS, '[Machine][getMachineList] success', $jsonRes);
        } catch (Exception $e) {
            Log::record('[Machine][getMachineList] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 获取我的借出申请的机器
     * @description 获取我的借出申请的机器接口
     * @method get
     * @url http://domain/ems-api/v1/Machine/getMyApplyMachineList
     * @param limit 必选 int 所需页的大小
     * @param offset 必选 int (当前页码-1)*pageSize
     * @return {"status":0,"msg":"[Machine][getMyApplyMachineList] success","data":{"total":1,"rows":[{"fixed_no":"1201099","MODEL_NAME":"Katmai10FT ES-SKU3","category":null,"SERIAL_NO":"SN:ZB247462W","CPU":"cpuType","HDD":"123GB","MEMORY":"0GB","type":"NO","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14","mac_address":"","cd_rom":"DVD-BD","invoice_no":"20120109","three_c_flag":0,"three_c_code":null,"location":"库位35","department":"SWT部","section_manager":"SCD","remark":"SCD","reject_flag":"无","broken":"","model_status":"待借出审批","instore_operator":null,"instore_date":"2013-06-30 05:22:29","scrap_operator":null,"scrap_date":null,"user_id":"admin","start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"superAdmin","approver_name":null,"serial_number":"NO","supplier":"BYD","hasApply":true}]}}
     * @return_param status int 状态码
     * @return_param total int 总页数
     * @return_param hasApply boolean true代表有申请需要显示【取消申请】
     * @remark 无
     */
    public function getMyApplyMachineList() {
        $pageSize = $this->request->param('limit');
        $offset = $this->request->param('offset');

        $jsonRes = array();

        try {

            $userId = $this->loginUser['ems'];

            $res = Db::table('ems_main_engine')->where('model_status',BORROW_REVIEW)
                ->where('user_id', $userId)->order('instore_date desc')->limit($offset, $pageSize)->select();

            foreach ($res as $key => $item) {
                $item['hasApply'] = true;
                $res[$key] = $item;
            }

            $res = itemChange($res);

            $total = Db::table('ems_main_engine')->where('model_status',BORROW_REVIEW)
                ->where('user_id', $userId)->count();

            $jsonRes['total'] = $total;
            $jsonRes['rows'] = $res;

            return apiResponse(SUCCESS, '[Machine][getMyApplyMachineList] success', $jsonRes);
        } catch (Exception $e) {
            Log::record('[Machine][getMyApplyMachineList] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }

    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 根据id获取单个机器的信息
     * @description 单个机器机器信息相关接口
     * @method post
     * @url http://domain/ems-api/v1/Machine/getMachineById
     * @param fixed_no 必选 int 样机编号
     * @return {"status":0,"msg":"[Machine][getMachineById] success","data":[{"fixed_no":"0602027","MODEL_NAME":"Cleveland10E CS2","category":null,"SERIAL_NO":"2147483647","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"75013482JU","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","three_c_flag":0,"three_c_code":null,"location":"","department":"SWT部","section_manager":"CSV","remark":"李甜","reject_flag":"无","broken":"","model_status":"使用中","instore_operator":null,"instore_date":"2013-06-21 07:59:33","scrap_operator":null,"scrap_date":null,"user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"","approver_name":null,"serial_number":"","supplier":null}]}
     */
    public function getMachineById() {
        $fixed_no = $this->request->param('fixed_no');
        try {
            $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_no)->select();

            return apiResponse(SUCCESS, '[Machine][getMachineById] success', itemChange($res));
        } catch (Exception $e) {
            Log::record('[Machine][getMachineById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 根据id获取单个机器的更新信息
     * @description 根据id获取单个机器的更新信息接口
     * @method post
     * @url http://domain/ems-api/v1/Machine/getUpdateMachineById
     * @param fixed_no 必选 int 样机编号
     * @return {"status":0,"msg":"[Machine][getUpdateMachineById] success","data":[{"fixed_no":"0602027","MODEL_NAME":"Cleveland10E CS2","category":null,"SERIAL_NO":"2147483647","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"75013482JU","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","three_c_flag":0,"three_c_code":null,"location":"","department":"37","section_manager":"491","remark":"李甜","reject_flag":0,"broken":0,"model_status":"3","instore_operator":null,"instore_date":"2013-06-21 07:59:33","scrap_operator":null,"scrap_date":null,"user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"","approver_name":null,"serial_number":"","supplier":null}]}
     */
    public function getUpdateMachineById() {
        $fixed_no = $this->request->param('fixed_no');
        try {
            $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_no)->select();

            return apiResponse(SUCCESS, '[Machine][getUpdateMachineById] success', $res);
        } catch (Exception $e) {
            Log::record('[Machine][getUpdateMachineById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 添加样品
     * @description 添加样品接口
     * @method post
     * @url http://domain/ems-api/v1/Machine/add
     * @param formData 必选 json 表单数据(字段名参考/数据字典)
     * @return {"status":0,"msg":"[Machine][add] success","data":[]}
     */
    public function add() {
        $formData = $this->request->param('formData');

        $data = getFormArray($formData);

        // #modelStatus#,#createUser#,sysdate(),#userName#
        // model_status,instore_operator,instore_date,user_name
        $data['model_status'] = IN_STORE;
        $data['instore_operator'] = $this->loginUser['ems']; // 老系统有些并没有存入这个字段
        $data['instore_date'] = Db::raw('now()');

        try {
            $res = Db::table('ems_main_engine')->insert($data);

            if (1 == $res) {
                return apiResponse(SUCCESS, '[Machine][add] success');
            } else {
                return apiResponse(ERROR, 'server error');
            }
        } catch (Exception $e) {
            Log::record('[Machine][add] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 编辑样品
     * @description 编辑样品接口
     * @method post
     * @url http://domain/ems-api/v1/Machine/edit
     * @param formData 必选 json 表单数据(字段名参考/数据字典)
     * @return {"status":0,"msg":"[Machine][edit] success","data":[]}
     */
    public function edit() {
        $formData = $this->request->param('formData');

        $data = getFormArray($formData);

        try {
            $res = Db::table('ems_main_engine')->update($data);

            if (1 == $res) {
                return apiResponse(SUCCESS, '[Machine][edit] success');
            } else {
                return apiResponse(ERROR, 'server error');
            }
        } catch (Exception $e) {
            Log::record('[Machine][edit] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
    /**
     * showdoc
     * @catalog 接口文档/机器信息相关
     * @title 获取最新的样品编号
     * @description 编辑界面需要请求最新的样品编号
     * @method get
     * @url http://domain/ems-api/v1/Machine/getLastId
     * @return {"status":0,"msg":"[Machine][getLastId] success","data":{"fixed":"1912003"}}
     * @return_param fixed int 样品编号
     */
    public function getLastId() {
        // 老系统就有个函数, 以年+月+000格式划分编号, 每月最多999, 超过则变为001, 一般每月不会录入1000台.
        $res = Db::query('select GETFIXEDNO() as fixed');
        return apiResponse(SUCCESS, '[Machine][getLastId] success', $res[0]);
    }

}
