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
    public function getHistoryById() {
        // param
        $offset = $this->request->param('offset'); // 前端传过来的值为(pageNo-1)*limit
        $limit = $this->request->param('limit');
        $fixed_no = $this->request->param('fixed_no');

        try {
            $jsonRes = array();
            $res = Db::table('ems_borrow_history')->where('FIXED_NO', $fixed_no)
                ->field('user_name, start_date, end_date, predict_date')->order('start_date')
                ->limit($offset, $limit)->select();

            $count = Db::table('ems_borrow_history')->where('FIXED_NO', $fixed_no)->count();

            $jsonResult['total'] = $count;
            $jsonResult['rows'] = $res;

            return apiResponse(SUCCESS, 'get history success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[History][getHistoryById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }
}
