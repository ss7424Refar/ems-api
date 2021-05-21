<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 21-4-28
 * Time: 下午4:33
 */

namespace app\v1\controller;


use think\Db;
use think\Exception;
use think\Log;

class Word extends Common{

    /**
     * showdoc
     * @catalog 接口文档/EXCEL相关
     * @title Word导出
     * @description Word导出
     * @method get
     * @param fixed_nos 单选 string fixed_nos:0505073
     * @return 无
     * @url http://domain/ems-api/v1/word/download?fixed_no=xxx
     * @remark 无
     */
    public function download() {

        $fixed_no = $this->request->param('fixed_no');
        // 查询机器信息
        try {
            $res = Db::table('ems_main_engine')->where('fixed_no', $fixed_no)->find();
        } catch (Exception $e) {
            Log::record('[Word][download] error ' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }

        $res = exportChange($res);
        $column = getColumns('comment');

        $file = $res['MODEL_NAME']. '(' . $fixed_no . ')配置信息_'. date('Y-m-d_His', time()). '.docx';

        try {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Tahoma');
            $phpWord->setDefaultFontSize(9);

            $section = $phpWord->addSection();

            $styleTable = array('borderColor'=>'006699',
                'borderSize'=>6);
            $styleFirstRow = array();

            $phpWord->addTableStyle('myTable', $styleTable, $styleFirstRow);$i = 0;
            $table = $section->addTable('myTable');

            foreach ($res as $value) {
                $table->addRow();
                // 列名
                $table->addCell(3600)->addText($this->replaceValue($column[$i]));
                // 内容
                $table->addCell(4300)->addText(empty($value) ? '' : $this->replaceValue($value));
                $i++;
            }

            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $xmlWriter->save("php://output");

        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
            Log::record('[Word][download] create error ' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }


    }

    /**
     * PHPWord 导出模版Word文件，无法打开，提示xml pasring error
     * @param $replace
     * @return mixed
     */
    private function replaceValue($replace) {
        $replace=str_replace('&','&amp;',$replace);
        $replace=str_replace('<','&lt;',$replace);
        $replace=str_replace('>','&gt;',$replace);
        $replace=str_replace('\'','&quot;',$replace);
        $replace=str_replace('"','&apos;',$replace);

        return $replace;

    }
}