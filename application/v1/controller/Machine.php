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
     * @return {"status":0,"msg":"[Machine][getMachineList] success","data":{"total":24891,"rows":[{"fixed_no":"1911631","MODEL_NAME":"Altair LE71 CS2主板 Gr06 Power","category":null,"SERIAL_NO":"无","CPU":"i3-8145U","HDD":"0","MEMORY":"0","type":"A5A004597060","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"","mac_address":null,"cd_rom":"","invoice_no":"iEXPn(Des)201911-0033","location":"库位327-11","department":"DT部","section_manager":"HWD","remark":"","model_status":"使用中","instore_operator":null,"instore_date":"2019-11-28 14:33:58","scrap_operator":null,"scrap_date":null,"user_id":"501609","start_date":"2019-11-28 15:57:32","predict_date":"2019-12-28 00:00:00","end_date":null,"approver_id":"p51201th","approve_date":"2019-11-28 17:02:56","user_name":"郑宁","approver_name":"吴勇","serial_number":"iEXPn(Des)201911-0033","supplier":null,"hasApply":false},{"fixed_no":"1911630","MODEL_NAME":"Altair LE71 CS2主板 Gr06 Power","category":null,"SERIAL_NO":"无","CPU":"i3-8145U","HDD":"0","MEMORY":"0","type":"A5A004597060","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"","mac_address":null,"cd_rom":"","invoice_no":"iEXPn(Des)201911-0033","location":"库位327-11","department":"DT部","section_manager":"HWD","remark":"","model_status":"使用中","instore_operator":null,"instore_date":"2019-11-28 14:33:58","scrap_operator":null,"scrap_date":null,"user_id":"501609","start_date":"2019-11-28 14:49:55","predict_date":"2019-12-28 00:00:00","end_date":null,"approver_id":"p51201th","approve_date":"2019-11-28 17:02:56","user_name":"郑宁","approver_name":"吴勇","serial_number":"iEXPn(Des)201911-0033","supplier":null,"hasApply":false}]}}
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
     * @title 根据id获取单个机器的信息
     * @description 单个机器机器信息相关接口
     * @method post
     * @url http://domain/ems-api/v1/Machine/getMachineById
     * @param fixed_no 必选 int 样机编号
     * @return {"status":0,"msg":"[Machine][getMachineById] success","data":[{"fixed_no":"0602027","MODEL_NAME":"Cleveland10E CS2","category":null,"SERIAL_NO":"2147483647","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"75013482JU","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","location":"","department":"SWT部","section_manager":"CSV","remark":"李甜","model_status":"使用中","instore_operator":null,"instore_date":"2013-06-21 07:59:33","scrap_operator":null,"scrap_date":null,"user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"","approver_name":null,"serial_number":"","supplier":null}]}
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
     * @return {"status":0,"msg":"[Machine][getUpdateMachineById] success","data":[{"fixed_no":"0602027","MODEL_NAME":"Cleveland10E CS2","category":null,"SERIAL_NO":"2147483647","CPU":"Intel","HDD":"250G","MEMORY":"2GB","type":"75013482JU","purchase_date":null,"invoice_date":null,"warranty_date":null,"actual_price":null,"tax_inclusive_price":null,"screen_size":"14.1","mac_address":"","cd_rom":"DVD-BD","invoice_no":"","location":"","department":"37","section_manager":"491","remark":"李甜","model_status":"3","instore_operator":null,"instore_date":"2013-06-21 07:59:33","scrap_operator":null,"scrap_date":null,"user_id":null,"start_date":null,"predict_date":null,"end_date":null,"approver_id":null,"approve_date":null,"user_name":"","approver_name":null,"serial_number":"","supplier":null}]}
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
