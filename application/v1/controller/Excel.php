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
     * 别问， 问就是尽力导成了csv
     * @return \think\response\Json
     */
    public function export() {
        set_time_limit(0);
        ini_set('memory_limit', '500M');

        try {
            $columns = Db::table('information_schema.columns')
                ->field('column_name as field, column_comment as comment')
                ->where('table_name', 'ems_main_engine')->select();

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

            // 获取表数目
            $count = Db::table('ems_main_engine')->order('fixed_no desc')->count();

            // 判断pages数目
            $pages = ceil($count / MAX_LINE);

            // 获取常量
            $statusArray = json_decode(STATUS, true);
            $departArray = json_decode(DEPART, true);
            $sectionArray = json_decode(SECTION, true);

            for($i = 1; $i <= $pages; $i++) {
                $list = Db::table('ems_main_engine')->order('fixed_no desc')
                    ->limit(($i - 1) * MAX_LINE, MAX_LINE)->select();

                foreach ($list as $row) {
                    $item = [];
                    foreach ($row as $key => $value) {
                        if ('model_status' == $key) {
                            if (null != $value) {
                                $r = $statusArray[$value];
                            }
                        }
                        if ('department' == $key) {
                            if (null != $value) {
                                $r = $departArray[$value];
                            }
                        }
                        if ('section_manager' == $key) {
                            if (null != $value) {
                                $r = $sectionArray[$value];
                            }
                        }

                        $item[] = mb_convert_encoding($value,'GBK','UTF-8');
                    }

                    fputcsv($fp, $item);
                }
                unset($list);
                ob_flush();
                flush();
            }
            fclose($fp);
        } catch (Exception $e) {
            Log::record('[Excel][export] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }
}