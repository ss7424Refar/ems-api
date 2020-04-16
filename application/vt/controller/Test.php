<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午3:55
 */

namespace app\vt\controller;

use think\Controller;
use think\Db;
use think\Session;

class Test extends Controller {

    function mail(){
        $to = ['min.wang@dbh.dynabook.com'];
        $subject = config('mail_header_subject'). '[调试样式]';
        $mainBody = '<p>Dear Managers</p><p>SYD课的superAdmin提交了样品借出申请, 请登录样品管理系统确认及审批, 谢谢!</p>';
        $from = ['test@dbh.dynabook.com'];
        $value = array(
                    array('id'=>'2003181', 'name'=>'Altair LR40', 'desc'=>'hey'),
                    array('id'=>'2003182', 'name'=>'Altair LR30', 'desc'=>'WOW!'));

        // 插入数据
        $data = ['id'=>null, 'type'=>FLOW, 'main_body'=>$mainBody, 'subject'=>$subject,
            'from'=>json_encode($from), 'to'=>json_encode($to), 'table_data' => json_encode($value)];
        Db::table('ems_mail_queue')->insert($data);

        return 'done';

    }

}