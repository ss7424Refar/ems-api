<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午2:36
 */

namespace app\v1\controller;

class Options extends Common {
    /**
     * showdoc
     * @catalog 接口文档/下拉选项
     * @title 下拉选项-课
     * @description 下拉菜单获取课信息接口
     * @method post
     * @url http://domain/ems-api/v1/Options/getDepartment
     * @return {"status":0,"msg":"[Options][getDepartment] success","data":[{"value":null,"text":"请选择"},{"value":29,"text":"DT部"},{"value":33,"text":"VT部"},{"value":37,"text":"SWT部"}]}
     * @return_param status int 状态码
     * @return_param msg string 状态码说明
     * @remark 返回0, 代表获取数据
     */
    public function getDepartment() {
        $depart = json_decode(DEPART, true);

        return apiResponse(SUCCESS, '[Options][getDepartment] success', $this->getKeyValue($depart));
    }
    /**
     * showdoc
     * @catalog 接口文档/下拉选项
     * @title 下拉选项-部门
     * @description 下拉菜单获取部门信息接口
     * @method post
     * @url http://domain/ems-api/v1/Options/getSection
     * @return {"status":0,"msg":"[Options][getSection] success","data":[{"value":null,"text":"请选择"},{"value":1884,"text":"SCD"},{"value":2271,"text":"SWV"},{"value":2272,"text":"PSD"},{"value":2273,"text":"CUD"},{"value":2274,"text":"FWD"},{"value":442,"text":"SYD"},{"value":462,"text":"HWD"},{"value":485,"text":"MED"},{"value":491,"text":"CSV"},{"value":499,"text":"HWV"},{"value":520,"text":"PAV"},{"value":540,"text":"SSD"}]}
     * @return_param status int 状态码
     * @return_param msg string 状态码说明
     * @remark 返回0, 代表获取数据
     */
    public function getSection() {
        $section = json_decode(SECTION, true);

        return apiResponse(SUCCESS, '[Options][getSection] success', $this->getKeyValue($section));
    }
    /**
     * showdoc
     * @catalog 接口文档/下拉选项
     * @title 下拉选项-样机状态
     * @description 下拉菜单获取样机状态信息接口
     * @method post
     * @url http://domain/ems-api/v1/Options/getStatus
     * @return {"status":0,"msg":"[Options][getStatus] success","data":[{"value":null,"text":"请选择"},{"value":0,"text":"在库"},{"value":1,"text":"待借出审批"},{"value":2,"text":"待分配"},{"value":3,"text":"使用中"},{"value":4,"text":"待报废审批"},{"value":5,"text":"已报废"},{"value":6,"text":"待删除审批"},{"value":7,"text":"待删除"},{"value":8,"text":"待报废"}]}
     * @return_param status int 状态码
     * @return_param msg string 状态码说明
     * @remark 返回0， 代表获取数据
     */
    public function getStatus() {
        $status = json_decode(STATUS, true);

        return apiResponse(SUCCESS, '[Options][getStatus] success', $this->getKeyValue($status));
    }
    /**
     * showdoc
     * @catalog 接口文档/下拉选项
     * @title 下拉选项-部门联动课
     * @description 根据部门下拉框内容联动课
     * @method post
     * @url http://domain/ems-api/v1/Options/getLinks
     * @param depart 必选 int 课的value
     * @return {"status":0,"msg":"[Options][getLinks] success","data":{"1884":"SCD","2271":"SWV","2272":"PSD","2274":"FWD","491":"CSV","540":"SSD"}}
     * @return_param status int 状态码
     * @return_param msg string 状态码说明
     * @remark 返回0， 代表获取数据, 建议初始化数据. 别传入null.
     */
    public function getLinks() {
        $depart = $this->request->param('depart');

        $links = json_decode(LINKS, true);

        if (array_key_exists($depart, $links)) {
            return apiResponse(SUCCESS, '[Options][getLinks] success', $links[$depart]);
        }
    }
    private function getKeyValue($arr) {
        $jsonResult = array();
        foreach($arr as $key => $value){
            $tmp = array();
            $tmp['value'] = $key;
            $tmp['text'] = $value;
            array_push($jsonResult, $tmp);
        }
        return $jsonResult;
    }
}