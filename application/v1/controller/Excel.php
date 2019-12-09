<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午3:57
 */

namespace app\v1\controller;

use think\Db;
use think\Exception;
use think\Log;

class Excel extends Common{
    /**
     * showdoc
     * @catalog 接口文档/EXCEL相关
     * @title EXCEL导出接口
     * @description EXCEL导出接口
     * @method get
     * @param formData 必选 json {formData: {}}
     * @return 无
     * @url http://domain/ems-api/v1/Excel/export
     * @remark 别问， 问就是尽力导成了csv 例子： let formData = JSON.stringify(this.form)； window.location.href = process.env.VUE_APP_BASE_API + '/services/MachineSever/outputExcel?' + 'formData=' + formData
     */
    public function export() {
        set_time_limit(0);
        ini_set('memory_limit', '500M');

        $formData = $this->request->param('formData');
        $map = getSearchCondition($formData);

        try {
            // field 暂时不用
            $columns = Db::table('information_schema.columns')
                ->field('column_name as field, column_comment as comment')
                ->where('table_name', 'ems_main_engine')
                ->where('table_schema', config('database.database'))->select();

            // 头部
            $column =[];
            foreach ($columns as $value) {
                $column[] = $value['comment'];
            }

            // 文件名
            $filename = 'machine_info_' . time() . '.csv';

            // header设置项
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header("Content-Disposition:attachment;filename=".$filename);
            header('Expires:0');
            header('Pragma:public');
            header('Cache-Control: must-revalidate');

            $fp = fopen('php://output', 'a');
            mb_convert_variables('GBK', 'UTF-8', $column);

            fputcsv($fp, $column);

            // 查询list
            if (empty($map['historyUser'])) {
                $list = Db::table('ems_main_engine')->where($map)->order('instore_date desc')->select();
            } else {
                // 先查询ems_borrow_history
                $sqlA = Db::table('ems_borrow_history')->distinct(true)->field('fixed_no')
                            ->where('user_name', $map['historyUser'])->buildSql();

                // 移除数组
                unset($map['historyUser']);
                $sqlB = Db::table('ems_main_engine')->where($map)->buildSql();

                // 查询
                $list = Db::table($sqlA . ' a')
                        ->join([$sqlB=> 'b'], 'a.fixed_no=b.fixed_no')
                        ->order('instore_date desc')
                        ->select();
            }

            // 获取常量
            $statusArray = json_decode(STATUS, true);
            $departArray = json_decode(DEPART, true);
            $sectionArray = json_decode(SECTION, true);

            // 生成csv
            foreach ($list as $row) {
                $item = [];
                foreach ($row as $key => $value) {
                    // 其中有个字段是为null的, 所以数组会存在越界
                    if ('model_status' == $key) {
                        if (null != $value) {
                            $value = $statusArray[$value];
                        }
                    }
                    if ('department' == $key) {
                        if (null != $value) {
                            $value = $departArray[$value];
                        }
                    }
                    if ('section_manager' == $key) {
                        if (null != $value) {
                            $value = $sectionArray[$value];
                        }
                    }
                    $item[] = mb_convert_encoding($value,'GBK','UTF-8');
                }

                fputcsv($fp, $item);
            }
            unset($list);
            ob_flush();
            flush();
            fclose($fp);
        } catch (Exception $e) {
            Log::record('[Excel][export] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
}