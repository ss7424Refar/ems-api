<?php
namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;
use ext\MailerUtil;

class Machine extends Common {
    /**
     * @return \think\response\Json
     */
    public function getMachineList() {
        $pageSize = $this->request->param('limit');
        $offset = $this->request->param('offset');

        $formData = $this->request->param('formData');

        $map = getSearchCondition($formData);

        $jsonRes = array();

        try {
            $res = Db::table('ems_main_engine')->where($map)->order('instore_date desc')
                        ->limit($offset, $pageSize)->select();

            $total = Db::table('ems_main_engine')->where($map)->count();

            $jsonRes['total'] = $total;
            $jsonRes['rows'] = $res;

            return apiResponse(SUCCESS, '[Machine][getMachineList] success', $jsonRes);
        } catch (Exception $e) {
            Log::record('[Machine][getMachineList] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    /**
     * 根据fixed_no返回样机具体信息, 如果查询没数据则为null
     * @return \think\response\Json
     */
    public function getMachineById() {
        $fixed_no = $this->request->param('fixed_no');
        try {
            $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_no)->find();

            return apiResponse(SUCCESS, '[Machine][getMachineById] success', $res);

        } catch (Exception $e) {
            Log::record('[Machine][getMachineById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }

}
