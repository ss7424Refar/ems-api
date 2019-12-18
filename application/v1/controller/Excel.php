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

use \PhpOffice\PhpSpreadsheet\IOFactory;

class Excel extends Common{
    /**
     * showdoc
     * @catalog 接口文档/EXCEL相关
     * @title EXCEL导出接口
     * @description EXCEL导出接口
     * @method get
     * @param formData 必选 json formData:{}
     * @return 无
     * @url http://domain/ems-api/v1/Excel/export
     * @remark `别问, 问就是尽力导成了csv; 例子: let formData = JSON.stringify(this.form); window.location.href = process.env.VUE_APP_BASE_API + '/services/MachineSever/outputExcel?formData={}`
     */
    public function export() {
        set_time_limit(0);
        ini_set('memory_limit', '500M');

        $formData = $this->request->param('formData');
        $map = getSearchCondition($formData);

        try {
            // 列名
            $column = getColumns('comment');
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

            $list = itemChange($list);
            // 生成csv
            foreach ($list as $row) {
                $item = [];
                foreach ($row as $value) {
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

    public function import() {

        $excel = request()->file('excel')->getInfo();
        $subject = $this->request->param('subject');

        try {
            $objReader = IOFactory::createReader('Xlsx');
            $objPHPExcel = $objReader->load($excel['tmp_name']);

            $importArr = $objPHPExcel->getSheetByName('template')->toArray();
            $linkArr = $objPHPExcel->getSheetByName('links')->toArray();

            if (count($importArr) <= 2) {
                return apiResponse(SUCCESS, '你需要填写至少一行的样品信息');
            }

            // 判断是否有重复
            $duplicateArray = array();
            foreach ($importArr as $key => $item) {
                if ($key >= 2) {
                    $res = Db::table('ems_main_engine')->where('fixed_no', $item[0])->find();

                    //

                }

            }

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::record('[Excel][import] error' . $e->getMessage());
        }

    }

}