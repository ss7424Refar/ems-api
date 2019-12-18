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

use ext\MailTemplate;

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
//            $objReader = IOFactory::createReader('Xlsx');
            $objReader = IOFactory::createReader('Xls');
            $objPHPExcel = $objReader->load($excel['tmp_name']);

            $importArr = $objPHPExcel->getSheetByName('template')->toArray();
            $linkArr = $objPHPExcel->getSheetByName('links')->toArray();

            $jsonResult = [];

            if (count($importArr) <= 2) {
                return apiResponse(SUCCESS, '你需要填写至少一行的样品信息');
            }

            // 判断是否有重复
            $duplicateArray = array();
            foreach ($importArr as $key => $item) {
                if ($key >= 2) {
                    $res = null;
                    try {
                        $res = Db::table('ems_main_engine')->where('fixed_no', $item[0])->find();
                    } catch (Exception $e) {
                        Log::record('[Excel][import] duplicate error' . $e->getMessage());
                        return apiResponse(ERROR, 'server error');
                    }
                    if (null != $res) {
                        $duplicateArray[] = $item[0];
                    }
                }
            }

            if (!empty($duplicateArray)) {
                $jsonResult['duplicate'] = $duplicateArray;
                $jsonResult['error'] = null;
                return apiResponse(SUCCESS, '重复数据如下, 需要重写填写', $jsonResult);
            }

            // 插入数据 判断成功与否
            $successList = []; $errorList = [];
            // 发送给前端的数据
            $errorArray = [];
            // 定义键名
            $key = array('fixed_no', 'MODEL_NAME', 'SERIAL_NO', 'type', 'department', 'section_manager',
                        'model_status', 'invoice_no', 'serial_number', 'CPU', 'screen_size', 'MEMORY',
                        'HDD', 'cd_rom', 'location', 'remark');

            $statusArray = json_decode(STATUS, true);
            $departArray = json_decode(DEPART, true);
            $sectionArray = json_decode(SECTION, true);

            // 还是用index来循环比较好.
            for ($i = 2; $i < count($importArr); $i++) {
                $data = array();
                for ($j = 0; $j < count($importArr[$i]); $j++) {
                    $data[$key[$j]] = $importArr[$i][$j];
                }
                // 课转换
                $keyS = array_search($data['section_manager'], $sectionArray);
                if ($keyS) {
                    $data['section_manager'] = $keyS;
                }
                // 部门转换
                $keyD = array_search($data['department'], $departArray);
                if ($keyD) {
                    $data['department'] = $keyD;
                }
                // 状态转换
                $keySt = array_search($data['model_status'], $statusArray);
                $data['model_status'] = $keySt; // 不是很需要转换, 0为假; 1以后为真
                try{
                    $result = Db::table('ems_main_engine')->insert($data);

                    // 插入失败
                    if (1 != $result) {
                        $errorList[] = $data['fixed_no'];
                        $errorArray['detail'][] = array('id'=>$data['fixed_no'], 'msg'=>'mysql插入行数返回结果不为1');
                    } else {
                        $successList[] = $data['fixed_no'];
                    }

                }catch (Exception $e){
                    Log::record('[Excel][import] insert error' . $e->getMessage());

                    $errorList[] = $data['fixed_no'];
                    $errorArray['detail'][] = array('id'=>$data['fixed_no'], 'msg'=>$e->getMessage());
                }
            }

            // 如果存在失败的数据
            if (!empty($errorList)) {
                // 需要把之前插入成功的数据删除
                foreach ($successList as $item) {
                    try {
                        Db::table('ems_main_engine')->where('fixed_no', $item)->delete();
                    } catch (Exception $e) {
                        Log::record('[Excel][import] delete success line error' . $e->getMessage());
                        return apiResponse(ERROR, 'server error');
                    }
                }

//                $errorArray['listId'] = $errorList;

                $jsonResult['duplicate'] = null;
                $jsonResult['error'] = $errorArray;
                return apiResponse(SUCCESS, '错误数据如下, 需要重写填写', $jsonResult);
            }

            // 全部数据插入成功
            if ((count($importArr) - 2) == count($successList)) {

                // 插入到邮件表中
                $to = [];

                $mainBody = MailTemplate::getImportNotice();

                // 插入数据
                $data = ['id'=>null, 'type'=>IMPORT, 'main_body'=>$mainBody, 'subject'=>$subject,
                    'from'=>['MAIL'], 'to'=>json_encode($to), 'table_data' => json_encode()];

                return apiResponse(SUCCESS, '插入数据成功');
            }

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::record('[Excel][import] error' . $e->getMessage());
        }

    }

    public function download() {

        $fileName = "入库信息导入表.xls";
        $file = ROOT_PATH . 'public' . DS . 'download' . DS . $fileName;

        // 打开文件
        $f = fopen($file, "r");
        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".filesize($file));
        Header("Content-Disposition: attachment;filename=" . $fileName);
        ob_clean();
        flush();

        echo fread($f, filesize($file));
        fclose($f);
        exit();
    }
}