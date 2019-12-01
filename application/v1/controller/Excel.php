<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午3:57
 */

namespace app\v1\controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Db;
use think\Exception;
use think\Log;

class Excel extends Common
{
    public function export()
    {
//        $formData = $this->request->param('formData');
//        $map = getSearchCondition($formData);

        // 生成列坐标A~AJ
        $letter = range('A', 'Z');
        $letter2 = range('A', 'J');
        foreach ($letter2 as $value) {
            $letter[] = 'A' . $value;
        }

        // excel
        try {
            try {
                // 获取表注释
                $headers = Db::table('information_schema.columns')
                    ->field('column_name as field, column_comment as comment')
                    ->where('table_name', 'ems_main_engine')->select();

                // 获取表中数据
                try {
                    set_time_limit(0);
                    ini_set('memory_limit', '1024M');
                    // 获取表中数据
                    $count = Db::table('ems_main_engine')->order('fixed_no desc')->count();

                    // 判断sheet数目
                    $sheets = ceil($count / MAX_LINE);
                    // 获取常量
                    $statusArray = json_decode(STATUS, true);
                    $departArray = json_decode(DEPART, true);
                    $sectionArray = json_decode(SECTION, true);

                    try {
                        $objPHPExcel = new Spreadsheet();

                        for ($s = 1; $s <= $sheets; $s++) {
                            dump($s);
                            // $list = Db::table('ems_main_engine')->where($map)->order('fixed_no desc')->select();
                            $list = Db::table('ems_main_engine')->limit(($s - 1) * MAX_LINE, MAX_LINE)
                                ->order('fixed_no desc')->select();
                            if ($s > 0) {
                                $objPHPExcel->createSheet();
                            } else {
                                //激活当前的sheet表
                                $objPHPExcel->setActiveSheetIndex($s);
                            }
                            // 设置当前激活的sheet表格名称；
                            $objPHPExcel->getActiveSheet()->setTitle('output_' . $s);

                            //设置A列水平居中
                            $objPHPExcel->setActiveSheetIndex($s)->getStyle('A')->getAlignment();
//                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                            // 自动调节列宽
                            for ($k = 0; $k < count($letter); $k++) {
                                $objPHPExcel->setActiveSheetIndex($s)->getColumnDimension($letter[$k])
                                    ->setAutoSize(true);
                            }
                            //生成表头
                            for ($h = 0; $h < count($letter); $h++) {
                                //设置表头值
                                $objPHPExcel->getActiveSheet()->setCellValue("$letter[$h]1", $headers[$h]['comment']);
                                //设置表头字体样式
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFont()->setName('宋体');
                                //设置表头字体大小
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFont()->setSize(11);
                                //设置表头字体是否加粗
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFont()->setBold(true);
                                //设置表头文字水平居中
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                //设置文字上下居中
                                $objPHPExcel->getActiveSheet()->getStyle($letter[$h])
                                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                                //设置单元格背景色
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFill()->setFillType(Fill::FILL_SOLID);
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFill()->getStartColor()->setARGB('FF6DBA43');
                                //设置字体颜色
                                $objPHPExcel->getActiveSheet()->getStyle("$letter[$h]1")
                                    ->getFont()->getColor()->setARGB('FFFFFFFF');
                            }

                            // 循环刚取出来的数组，将数据逐一添加到excel表格。
                            for ($i = 0; $i < count($list); $i++) {
                                // 常量转换

                                if (!empty($list[$i]['model_status'])) {
                                    $list[$i]['model_status'] = $statusArray[$list[$i]['model_status']];
                                }
                                $list[$i]['department'] = $departArray[$list[$i]['department']];
                                $list[$i]['section_manager'] = $sectionArray[$list[$i]['section_manager']];

                                for ($j = 0; $j < count($letter); $j++) {
                                    if (0 == $j) {
                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($letter[$j] . ($i + 2), $i + 1); // 记数用的
                                    } else {
                                        //其他数据
                                        $objPHPExcel->getActiveSheet()
                                            ->setCellValue($letter[$j] . ($i + 2), $list[$i][$headers[$j - 1]['field']]);
                                    }
                                }
                            }
                            // 设置保存的Excel表格名称
                            $filename = 'machine_info_' . time() . '.Xlsx';
                            // 设置浏览器窗口下载表格
                            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                            header('Content-Disposition:attachment;filename="' . $filename . '"');
                            $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
                            $objWriter->save('php://output');

                            unset($list);
                            ob_flush();
                            flush();


                        }

                    } catch (Exception $e) {

                        Log::record('[Excel][getTableData] error' . $e->getMessage());
                    }
                } catch (Exception $e) {
                    Log::record('[Excel][getTableData] error' . $e->getMessage());
                }
            } catch (Exception $e) {
                Log::record('[Excel][getTableNames] error' . $e->getMessage());
            }
        } catch (Exception $e) {
            Log::record('[Excel][export] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');

        }

    }

    public function export2(){

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $list = Db::table('ems_main_engine')->order('fixed_no desc')->select();
        $headers = Db::table('information_schema.columns')
            ->field('column_name as field, column_comment as comment')
            ->where('table_name', 'ems_main_engine')->distinct('true')->select();

        $string = '';
        $headerArray =[];
        foreach ($headers as $value) {
            $headerArray[] = $value['comment'];
        }

        $string = implode(",",$headerArray)."\n";

        foreach ($list as $value1) {
            $tmp = [];
            foreach ($value1 as $v) {
                $tmp[] = $v;
            }
            $string .= implode(",", $tmp)."\n";
        }

        $filename = date('Ymd').'.csv'; //设置文件名
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        exit(mb_convert_encoding($string, "GBK", "UTF-8"));

    }
}