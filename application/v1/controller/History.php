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

class History extends Common {
    /**
     * showdoc
     * @catalog 接口文档/历史记录
     * @title 以样机编号查询历史记录接口
     * @description 以样机编号查询历史记录接口
     * @method get
     * @param fixed_no 必选 int 样机编号
     * @return {"status":0,"msg":"[History][getHistoryById] success","data":{"total":1,"rows":[{"user_name":"Deng, Xiaolong","start_date":"2013-06-30 04:09:41","end_date":"2013-06-30 04:13:33","predict_date":"2013-07-01 15:00:00","remark":""}]}}
     * @url http://domain/ems-api/v1/History/getHistoryById
     * @return_param user_name string 用户名
     * @return_param start_date date 开始使用时间
     * @return_param end_date date 结束使用时间
     * @return_param predict_date date 预估归还时间
     * @return_param remark string 备注
     * @remark 超过5条显示下拉框
     */
    public function getHistoryById() {
        // param
//        $offset = $this->request->param('offset'); // 前端传过来的值为(pageNo-1)*limit
//        $limit = $this->request->param('limit');
        $fixed_no = $this->request->param('fixed_no');

        try {
            $res = Db::table('ems_borrow_history')->where('FIXED_NO', $fixed_no)
                ->field('user_name, start_date, end_date, predict_date, remark')->order('start_date')
                ->select();

            $count = Db::table('ems_borrow_history')->where('FIXED_NO', $fixed_no)->count();

            $jsonResult['total'] = $count;
            $jsonResult['rows'] = $res;

            return apiResponse(SUCCESS, '[History][getHistoryById] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[History][getHistoryById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }
}
