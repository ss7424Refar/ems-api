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
     * @title EXCEL导出
     * @description EXCEL导出
     * @method get
     * @param formData 单选 json formData:{}(checkbox没有勾选)
     * @param fixed_nos 单选 string fixed_nos:[]
     * @param myApply 单选 boolean myApply:true /false
     * @return 无
     * @url http://domain/ems-api/v1/Excel/export
     * @remark `别问, 问就是尽力导成了csv; 例子: let formData = JSON.stringify(this.form); window.location.href = process.env.VUE_APP_BASE_API + '/services/MachineSever/outputExcel?formData={}`
     */
    public function export() {
        set_time_limit(0);
        ini_set('memory_limit', '500M'); // 增加字段后需要修改内存大小, 否则跳转下载出错

        $formData = $this->request->param('formData');
        $map = getSearchCondition($formData);
        $fixed_nos = $this->request->param('fixed_nos'); // checkedList
        $myApply = $this->request->param('myApply'); // 我的借出申请

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
//            mb_convert_variables('GBK', 'UTF-8', $column);

            fputcsv($fp, $column);

            // 查询list
            if ($fixed_nos) {
                $list = Db::table('ems_main_engine')->whereIn('fixed_no', json_decode($fixed_nos))->select();
            } else {
                // 一般给个check
                if ($myApply == 'check') {
                    $list = Db::table('ems_main_engine')->where('model_status',BORROW_REVIEW)
                        ->where('user_id', $this->loginUser['ems'])->order('instore_date desc')->select();
                } else {
                    if (empty($map['historyUser'])) {
                        $list = Db::table('ems_main_engine')->where($map)->order('instore_date desc')->select();
                    } else {
                        // 先查询ems_borrow_history
                        $sqlA = Db::table('ems_borrow_history')->distinct(true)->field('fixed_no')
                            ->where('user_name', $map['historyUser'][0], $map['historyUser'][1])->buildSql();
                        // 移除数组
                        unset($map['historyUser']);
                        $sqlB = Db::table('ems_main_engine')->where($map)->buildSql();

                        // 查询
                        $list = Db::table($sqlA . ' a')
                            ->join([$sqlB=> 'b'], 'a.fixed_no=b.fixed_no')
                            ->order('instore_date desc')
                            ->select();
                    }
                }

            }
            // 需要替换入库操作者
            $allUsers = Db::table('ems_user')->distinct(true)
                ->field('USER_ID, USER_NAME, IS_DELETED')->select();
            $allKeyUsers = array();
            foreach ($allUsers as $item) {
                // 可能出现覆盖的情况，但是入库操作者来说概率挺小的
                $allKeyUsers[$item['USER_ID']] = $item['USER_NAME'];
            }

            // 生成csv
            foreach ($list as $row) {
                $row = exportChange($row);
                // 转换入库操作者ID
                if (array_key_exists($row['instore_operator'], $allKeyUsers)) {
                    $row['instore_operator'] = $allKeyUsers[$row['instore_operator']];
                }

                $item = [];
                // 替换备注中的回车
                $row['remark'] = str_replace(PHP_EOL, '\r\n', $row['remark']);
                foreach ($row as $value) {
                    // 其实是不用转换的， 正式上有bug.
                    $item[] = mb_convert_encoding($value,'GBK','UTF-8');
//                    $item[] = $value;
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

    /**
     * showdoc
     * @catalog 接口文档/EXCEL相关
     * @title EXCEL导入
     * @description EXCEL导入
     * @method post
     * @param excel 必选 file 文件(xlsx)
     * @param subject 必选 string 邮件标题
     * @return 参考导入返回内容说明
     * @url http://domain/ems-api/v1/Excel/import
     * @remark 1. 需要post请求; 2. Content-Type=multipart/form-data
     */
    public function import() {

        $excel = request()->file('excel')->getInfo();
        $subject = $this->request->param('subject');

        // 导入数据
        $importArr = [];
        // 新导入数据
        $newImportArr = [];
        // link sheet
        $linkArr = [];
        // 转换后的links
        $_linkArr = [];
        // 返回结果
        $jsonResult = [];
        // 插入数据 判断成功与否
        $successList = []; $errorList = [];
        // 发送给前端的数据
        $errorArray = [];

        $statusArray = json_decode(STATUS, true);
        $departArray = json_decode(DEPART, true);
        $sectionArray = json_decode(SECTION, true);

        // 读取excel
        try {
            $objReader = IOFactory::createReader('Xlsx');
            $objReader->setReadDataOnly(TRUE);
            $objPHPExcel = $objReader->load($excel['tmp_name']);

            $importArr = $objPHPExcel->getSheetByName('template')->toArray();
            $linkArr = $objPHPExcel->getSheetByName('links')->toArray();

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::record('[Excel][import] read error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
        // 上传excel到服务器 (以当前日期为子目录，以微秒时间的md5编码为文件名的文件)
        $file = request()->file('excel');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                Log::record('[Excel][import] upload file ' . $excel['name']. ' | '. $info->getSaveName());
            }else{
                // 上传失败获取错误信息
                Log::record('[Excel][import] upload error ' . $file->getError());
                return apiResponse(ERROR, 'server error');
            }
        }
        if (count($importArr) <= 2) {
            return apiResponse(SUCCESS, '你需要填写至少一行的样品信息');
        }
        // 验证并转换数据
        try {

            // 定义键名
            $key = array('fixed_no', 'MODEL_NAME', 'category', 'SERIAL_NO', 'type', 'department', 'section_manager',
                 'model_status', 'broken', 'actual_price', 'tax_inclusive_price', 'three_c_flag', 'three_c_code',
                'invoice_no', 'serial_number', 'CPU', 'screen_size', 'MEMORY', 'HDD', 'cd_rom', 'location', 'remark');

            // 还是用index来循环比较好.
            for ($i = 2; $i < count($importArr); $i++) {
                $data = array();
                for ($j = 0; $j < count($key); $j++) {
                    $data[$key[$j]] = trim($importArr[$i][$j]); // trim 转换null为''
                }
                // 空编号check
                if (empty($data['fixed_no'] )) {
                    return apiResponse(SUCCESS, '资产编号不能为空 (第'. ($i + 1) .'行)');
                }

                // 课转换
                $keyS = array_search($data['section_manager'], $sectionArray);
                if ($keyS) {
                    $data['section_manager'] = $keyS;
                } else {
                    return apiResponse(SUCCESS, '课填写不正确 (第'. ($i + 1) .'行)');
                }
                // 部门转换
                $keyD = array_search($data['department'], $departArray);
                if ($keyD) {
                    $data['department'] = $keyD;
                } else {
                    return apiResponse(SUCCESS, '部门填写不正确 (第'. ($i + 1) .'行)');
                }
                // 状态转换
                $keySt = array_search($data['model_status'], $statusArray);
                // 判断false
                if ('boolean' == gettype($keySt) && empty($keySt)) {
                    return apiResponse(SUCCESS, '状态填写不正确 (第'. ($i + 1) .'行)');
                }
                // 损坏check
                if (empty($data['broken'] )) {
                    return apiResponse(SUCCESS, '损坏不能为空 (第'. ($i + 1) .'行)');
                }
                if (empty($data['three_c_flag'])) {
                    return apiResponse(SUCCESS, '是否免3c不能为空 (第'. ($i + 1) .'行)');
                }

                $data['model_status'] = $keySt;
                $data['instore_operator'] = $this->loginUser['ems'];
                $data['instore_date'] = Db::raw('now()'); // 入库时间
                $data['broken'] = $data['broken'] == 'Y' ? 1 : 0;
                $data['three_c_flag'] = $data['three_c_flag'] == 'Y' ? 1 : 0;

                $newImportArr[] = $data;
            }
        } catch (Exception $e) {
            Log::record('[Excel][import] check error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

        Log::record('[Excel][import] importArr | newImportArr = ' .
            count($importArr). ' | '. count($newImportArr));

        if (0 == count($newImportArr)) {
            return apiResponse(SUCCESS, '转换后的样品数据行数为0');
        }
        // 不大会走这个分支
        if ((count($importArr) -2) != count($newImportArr)) {
            return apiResponse(SUCCESS, '转换后的样品数据行数不正确 ('.count($importArr).'/'.
                count($newImportArr));
        }

        // 插入数据
        foreach ($newImportArr as $data) {
            try {
                $result = Db::table('ems_main_engine')->insert($data);
                // 插入失败
                if (1 != $result) {
                    $errorList[] = $data['fixed_no'];
                    $errorArray['detail'][] = array('id'=>$data['fixed_no'], 'msg'=>'mysql插入行数返回结果不为1');
                } else {
                    $successList[] = $data['fixed_no'];
                }
            } catch (Exception $e){
                Log::record('[Excel][import] insert error ' . Db::table('ems_main_engine')->getLastSql());
                Log::record('[Excel][import] insert error ' . $e->getMessage());

                $errorList[] = $data['fixed_no'];
                $errorArray['detail'][] = array('id'=>$data['fixed_no'], 'msg'=>$e->getMessage());
            }
        }

        Log::record('[Excel][import] newImportArr | successList | errorList = ' .
            count($newImportArr). ' | '. count($successList). ' | '. count($errorList));

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

            $jsonResult['error'] = $errorArray;
            return apiResponse(SUCCESS, '错误或重复数据如下, 需要重写填写', $jsonResult);
        }

        // 发送邮件
        try {
            foreach ($linkArr as $item) {
                $_linkArr[$item[0]] = $item[1];
            }

            // 全部数据插入成功
            $json = array();
            // 存储邮件表格内容, 需要拿到各个课的负责人, 所以这里再循环一次
            foreach ($newImportArr as $item) {

                $section = $sectionArray[trim($item['section_manager'])];// 这里可能很容易出错
                $tmp = null;
                $tmp['id'] = trim($item['fixed_no']);
                $tmp['name'] = trim($item['MODEL_NAME']);
                $tmp['sn'] = trim($item['SERIAL_NO']);
                $tmp['pn'] = trim($item['type']);
                $tmp['section'] = $section;
                $tmp['remark'] = trim($item['remark']);
                // 0 , false, null, '' 都为N/A
                $tmp['charge'] = trim($_linkArr[$section]);
                $json[] = $tmp;
            }
            // 插入到邮件表中
            $mainBody = MailTemplate::getImportNotice();

//            $usr = $this->getUserInfoById($this->loginUser['ems']);
            // 插入数据
            $data = ['id'=>null, 'type'=>IMPORT, 'main_body'=>$mainBody, 'subject'=>$subject,
                'from'=>config('mail_import_from'), 'to'=>config('mail_import_to'), 'table_data' => json_encode($json)];

            Db::table('ems_mail_queue')->insert($data);

            return apiResponse(SUCCESS, '插入数据成功');
        } catch (Exception $e) {
            Log::record('[Excel][import] mail error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

    }

    /**
     * showdoc
     * @catalog 接口文档/EXCEL相关
     * @title EXCEL导入模板下载
     * @description EXCEL导入模板下载
     * @method get
     * @return 无
     * @url http://domain/ems-api/v1/Excel/download
     * @remark 1. window.location.href=
     */
    public function download() {

        $fileName = "入库信息导入表.xlsx";
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
    }
}