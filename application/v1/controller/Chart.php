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
    /**
     * showdoc
     * @catalog 接口文档/图表相关
     * @title 样品区分图表
     * @description 样品区分图表接口
     * @method post
     * @url http://domain/ems-api/v1/Chart/getCategory
     * @return {"status":0,"msg":"[Chart][getCategory] success","data":{"seriesData":[{"value":1,"name":"Altair LE70"},{"value":1,"name":"Altair LE71"},{"value":1,"name":"Altair LE72"},{"value":1,"name":"Altair LE73"},{"value":1,"name":"Altair LE74"},{"value":1,"name":"Altair LE75"},{"value":1,"name":"Altair LR30"},{"value":75,"name":"Altair LZ15"},{"value":34,"name":"Altair LZ25"},{"value":5,"name":"Altair MZ"},{"value":13,"name":"Altair MZ20"},{"value":40,"name":"USB-C Dock"},{"value":3,"name":"Yosemite10FG"},{"value":33,"name":"主板"},{"value":5,"name":"光盘"},{"value":40,"name":"内存条"},{"value":7,"name":"散热片"},{"value":2,"name":"显示屏"},{"value":5,"name":"硬盘"}],"legendData":["Altair LE70","Altair LE71","Altair LE72","Altair LE73","Altair LE74","Altair LE75","Altair LR30","Altair LZ15","Altair LZ25","Altair MZ","Altair MZ20","USB-C Dock","Yosemite10FG","主板","光盘","内存条","散热片","显示屏","硬盘"]}}
     * @return_param seriesData array 饼图的值
     * @return_param legendData array 图例的值
     * @remark 返回0， 代表获取数据
     */
    public function getCategory() {
        // 查询不为null的数据
        $jsonResult = array();

        try {
            $res = Db::table('ems_main_engine')->field('count(*) as total, category')
                    ->whereNotNull('category')->group('category')
                    ->order('total desc')->limit(10)->select();

            foreach ($res as $index => $item) {
                $data['value'] = $item['total'];
                $data['name'] = $item['category'];
                $jsonResult['seriesData'][] = $data;

                $jsonResult['legendData'][] = $item['category'];
            }

            return apiResponse(SUCCESS, '[Chart][getCategory] success', $jsonResult);
        } catch (Exception $e) {
            Log::record('[Chart][getCategory] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
}