<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-22
 * Time: 上午8:55
 */

namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;

class LogRecord extends Common {
    /**
     * showdoc
     * @catalog 接口文档/操作记录
     * @title 以样机编号查询操作记录接口
     * @description 以样机编号查询操作记录接口
     * @method get
     * @param fixed_no 必选 int 样机编号
     * @return {"status":0,"msg":"[LogRecord][getRecordById] success","data":{"total":1,"rows":[{"fixed_no":"1002044","desc":"样品申请","role":"测试主管","operator":"何兰英","type":"申请","result":"同意","reason":null,"time":"2020-10-21 13:50:41"}]}}
     * @url http://domain/ems-api/v1/LogRecord/getRecordById
     * @return_param fixed_no string 编号
     * @return_param desc string 操作内容
     * @return_param role string 职位
     * @return_param operator string 操作者
     * @return_param type string 类型
     * @return_param result string 结果
     * @return_param reason string 原因说明
     * @return_param time date 时间
     * @remark 超过5条显示下拉框
     */
    public function getRecordById() {
        // param
//        $offset = $this->request->param('offset'); // 前端传过来的值为(pageNo-1)*limit
//        $limit = $this->request->param('limit');
        $fixed_no = $this->request->param('fixed_no');

        try {
            $res = Db::table('ems_log_record')->where('fixed_no', $fixed_no)
                ->field('id', true)->order('time desc')
                ->select();

            $count = Db::table('ems_log_record')->where('FIXED_NO', $fixed_no)->count();

            $jsonResult['total'] = $count;
            $jsonResult['rows'] = $res;

            return apiResponse(SUCCESS, '[LogRecord][getRecordById] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[LogRecord][getRecordById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }
}
