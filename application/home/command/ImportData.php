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
 * 使用方法 php think ImportData '/opt/test.xlsx'
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

            foreach ($importArr as $index => $item) {
                if ($index >= 1) {
                    $id = $item[0]; $category = $item[2];
                    if (null != $category) {
                        try {
                            $res = Db::table('ems_main_engine')->where('fixed_no', $id)->find();
                            // 存在
                            if ($res) {
                                try {
                                    Db::table('ems_main_engine')->where('fixed_no', $id)
                                        ->update(['category' => $category]);
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
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            $output->writeln('read data fail ... '. $e->getMessage());
            $output->writeln('end input data ...' );
        }

        $output->writeln('end input data ...' );
    }

}