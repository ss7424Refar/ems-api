<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 20-1-14
 * Time: 下午2:18
 */

namespace app\home\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

use think\Db;
use think\Exception;

use \PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * 使用方法
 * 1. chmod 757 target_excel
 * 2. php think ImportData '/opt/test.xlsx'
 * Class ImportData
 * @package app\home\command
 */
class ImportData extends Command
{
    protected function configure()
    {
        $this->setName('ImportData')->setDescription('导入样品[区分]字段专用');

        //参数1
        $this->addArgument('path', Argument::REQUIRED, "The path of the import excel");
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('start input data ...' );

        try {
            ini_set('memory_limit', '500M');

            $objReader = IOFactory::createReader('Xlsx');
            $objReader->setReadDataOnly(TRUE);
            $objPHPExcel = $objReader->load($input->getArgument('path'));

            $importArr = $objPHPExcel->getSheet(0)->toArray();

            $saveArr = []; // 保存区分
            foreach ($importArr as $index => $item) {
                if ($index >= 1) {
                    $id = $item[0]; $category = $item[2];
                    if (null != $category) {
                        try {
                            $res = $this->getStart($output, $id);
//                            $output->writeln('id=' .$id . ' ,res='. $res);
                            if ('3' != $res) {
                                if ('2' == $res) {
                                    $id = '0'. $id;
                                }
                                try {
                                    Db::table('ems_main_engine')->where('fixed_no', $id)
                                        ->update(['category' => $category]);
                                    if (!in_array($category, $saveArr)) {
                                        $saveArr[] = $category;
                                    }
                                } catch (Exception $e) {
                                    $output->writeln('update data fail ... ['. $id .'] '. $e->getMessage());
                                }
                            } else {
                                $output->writeln('id not exist ... ['. $id .']');
                            }
                        } catch (Exception $e) {
                            $output->writeln('select data byId fail ... ['. $id .'] '. $e->getMessage());

                        }
                    }
                }
            }
            // 保存到ems_const
            if (!empty($saveArr)) {
                $output->writeln('start delete ems_const ...' );
                // 先删除ems_const表
                try {
                    Db::table('ems_const')->delete(true);
                } catch (Exception $e) {
                    $output->writeln('delete ems_const fail ... '. $e->getMessage());
                }
                $output->writeln('start update ems_const ...' );

                // 重置序列
                Db::execute('ALTER TABLE ems_const AUTO_INCREMENT = 1;');
                // 插入数据
                foreach ($saveArr as $value) {
                    Db::table('ems_const')
                        ->data(['id'=>null,'name'=>$value])
                        ->insert();

                }
                $output->writeln('end update ems_const ...' );
            }
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $output->writeln('read data fail ... '. $e->getMessage());
            $output->writeln('end input data ...' );
        }

        $output->writeln('end input data ...' );
    }

    protected function getStart(Output $output, $id) {
        try {
            $res = Db::table('ems_main_engine')->where('fixed_no', $id)->find();
            // 存在
            if ($res) {
                return '1';
            } else {
                $res = Db::table('ems_main_engine')->where('fixed_no', '0'. $id)->find();
                if ($res) {
                    return '2';
                }
                return '3';
            }
        } catch (Exception $e) {
            $output->writeln('getStart select data byId fail ... ['. $id .'] '. $e->getMessage());
        }
        return '3';
    }
}