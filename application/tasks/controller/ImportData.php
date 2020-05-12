<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 20-5-11
 * Time: 上午9:02
 */

namespace app\tasks\controller;

use think\Db;
use think\Exception;
use think\Log;

class ImportData
{
    public function dog() {
        try {
            Log::record('[ImportData] start');

            $res = Db::table('ems_main_engine')->distinct(true)->field('category')
                ->whereNotNull('category')->select();

            $saveArr = [];
            foreach ($res as $index => $item) {
                foreach ($item as $value) {
                    $saveArr[] = $value;
                }
            }

            // 获取ems_const表数量
            $total = Db::table('ems_const')->count();
            if ($total == count($saveArr)) {
                Log::record('ems_const has no need to update ... total = '. $total);
            } else {
                // 先删除ems_const表
                try {
                    Db::table('ems_const')->delete(true);
                } catch (Exception $e) {
                    Log::record('delete ems_const fail ... '. $e->getMessage());
                }
                Log::record('start update ems_const ...');

                // 重置序列
                Db::execute('ALTER TABLE ems_const AUTO_INCREMENT = 1;');
                // 插入数据
                foreach ($saveArr as $value) {
                    if (!empty($value)) {
                        Db::table('ems_const')
                            ->data(['id'=>null,'name'=>$value])
                            ->insert();
                    }
                }
            }
        } catch (Exception $e) {
            Log::record('[ImportData][dog] error' . $e->getMessage());
        }
        Log::record('[ImportData] end');
    }
}