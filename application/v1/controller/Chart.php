<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午3:58
 */

namespace app\v1\controller;


use think\Db;
use think\Exception;
use think\Log;

class Chart extends Common{
    /**
     * showdoc
     * @catalog 接口文档/图表相关
     * @title 样机状态分布
     * @description 样机状态分布表接口
     * @method post
     * @url http://domain/ems-api/v1/Chart/getMachineStatus
     * @return {"status":0,"msg":"[Chart][getMachineStatus] success","data":{"seriesData":[{"value":12834,"name":"在库"},{"value":62,"name":"待借出审批"},{"value":8,"name":"待分配"},{"value":5226,"name":"使用中"},{"value":589,"name":"待报废审批"},{"value":6154,"name":"已报废"},{"value":3,"name":"待删除审批"}],"legendData":["在库","待借出审批","待分配","使用中","待报废审批","已报废","待删除审批"]}}
     * @return_param seriesData array 饼图的值
     * @return_param legendData array 图例的值
     * @remark 返回0， 代表获取数据
     */
    public function getMachineStatus() {
        // 取得状态
        $status = json_decode(STATUS, true);

        $jsonResult = array();

        try {
            foreach ($status as $index => $item) {
                $res = Db::table('ems_main_engine')->where('model_status', $index)->count();

                $data['value'] = $res;
                $data['name'] = $item;
                $jsonResult['seriesData'][] = $data;
            }
            $jsonResult['legendData'] = $status;
            return apiResponse(SUCCESS, '[Chart][getMachineStatus] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Chart][getMachineStatus] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }
    /**
     * showdoc
     * @catalog 接口文档/图表相关
     * @title 年度入库图表
     * @description 年度入库图表接口
     * @method post
     * @url http://domain/ems-api/v1/Chart/getYearStore
     * @return {"status":0,"msg":"[Chart][getYearStore] success","data":{"xAxisData":[2013,2014,2015,2016,2017,2018,2019],"seriesData":[6438,2186,2442,1895,2875,4213,4844]}}
     * @return_param xAxisData array 代表横坐标数据
     * @return_param seriesData array 柱形图的值
     * @remark 返回0， 代表获取数据
     */
    public function getYearStore() {
        $jsonResult = array();

        try {
            $res = Db::table('ems_main_engine')->field('YEAR(instore_date) as year, count(*) as total')
                ->group('year')->select();

            foreach ($res as $index => $item) {
                $jsonResult['xAxisData'][] = $item['year'];
                $jsonResult['seriesData'][] = $item['total'];
            }
            return apiResponse(SUCCESS, '[Chart][getYearStore] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Chart][getYearStore] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
}