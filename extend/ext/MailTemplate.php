<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 18-12-12
 * Time: 上午9:13
 */

namespace ext;

class MailTemplate {

    public static $subjectBorrowApply = '[样品借出审批] Workflow from ';
    public static $subjectBorrowApplyApproveFromSection = '[样品借出审批通过] Workflow from ';
    public static $subjectBorrowApplyRejectFromSection = '[样品借出审批拒绝] Workflow from ';

    public static $subjectBorrowApplyApproveFromSample = '[样品分配通过] Workflow from ';
    public static $subjectBorrowApplyRejectFromSample = '[样品分配拒绝] Workflow from ';

    public static $subjectReturnSample = '[样品归还] Workflow from ';

    public static $subjectDeleteApply = '[样品删除审批] Workflow from ';
    public static $subjectDeleteApplyApproveFromSection = '[样品删除审批通过] Workflow from ';
    public static $subjectDeleteApplyRejectFromSection = '[样品删除审批拒绝] Workflow from ';

    public static $subjectScrapApply = '[样品报废审批] Workflow from ';
    public static $subjectScrapApplyApproveFromSample = '[样品报废审批通过] Workflow from ';
    public static $subjectScrapApplyRejectFromSample = '[样品报废审批拒绝] Workflow from ';

    public static function getBorrowApply($section, $user) {
        return
            '<p>Dear Managers</p>'.
            '<p>'. $section .'课的'. $user .'提交了样品借出申请, 请登录样品管理系统确认及审批, 谢谢!</p>';

    }

    public static function getReplyApproveBorrowApplyFromSection() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批通过, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getReplyRejectBorrowApplyFromSection($user) {
        return
            '<p>Dear '. $user. '</p>'.
            '<p>如下样机审批拒绝, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getReplyApproveBorrowApplyFromSample($user) {
        return
            '<p>Dear '. $user. '</p>'.
            '<p>如下样机分配通过, 请及时到样品管理库取得样品, 谢谢!</p>';

    }

    public static function getReplyRejectBorrowApplyFromSample($user) {
        return
            '<p>Dear '. $user. '</p>'.
            '<p>如下样机分配拒绝, 请登录样品管理系统确认, 谢谢</p>';

    }

    public static function getReturnSample($user) {
        return
            '<p>Dear '. $user. '</p>'.
            '<p>如下样机已归还, 请登录样品管理系统确认, 谢谢</p>';

    }

    public static function getDeleteApply($section, $user) {
        return
            '<p>Dear Managers</p>'.
            '<p>'. $section .'课的'. $user .'提交了样品删除申请, 请登录样品管理系统确认及审批, 谢谢!</p>';

    }

    public static function getDeleteApproveFromSection() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批通过, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getDeleteRejectFromSection() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批拒绝, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getScrapApply() {
        return
            '<p>Dear Sample Managers</p>'.
            '<p>您收到了样品报废申请, 请登录样品管理系统确认及审批, 谢谢!</p>';

    }

    public static function getScrapApproveFromSample() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批通过, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getScrapRejectFromSample() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批拒绝, 请登录样品管理系统确认, 谢谢!</p>';

    }

    public static function getImportNotice() {
        return
            '<p>Dear All</p>'.
            '<p>大家好, 以下样品已入库, 如果有需要可通过申请再来领取</p>'.
            '<p>点击<a style="font-size:12px;" href="'.EMS_URL .'">此链接</a>确认详情</p>';
    }

    public static function getContent($mainBody, $tables) {

        return '<html charset="utf-8">'.
               '    <head>'.
                        self::getCSSStyle().
               '    </head>'.
               '    <body>'.
                        $mainBody.
                        self::getTableData($tables).
                        self::getFooter().
               '    </body>'.
                '</html>';
    }

    public static function getImportContent($mainBody, $tables) {

        return '<html charset="utf-8">'.
                '    <head>'.
                        self::getCSSStyle().
                '    </head>'.
                '    <body>'.
                        $mainBody.
                        self::getImportTableData($tables).
//                        self::getFooter().
                '    </body>'.
                '</html>';
    }

    public static function getApplyContent($mainBody, $tables) {

        return '<html charset="utf-8">'.
            '    <head>'.
            self::getCSSStyle().
            '    </head>'.
            '    <body>'.
            $mainBody.
            self::getApplyTableData($tables).
            self::getFooter().
            '    </body>'.
            '</html>';
    }

    private static function getTableData($json) {
        $tab = json_decode($json, true);

        $tables = '<tr>'.
                    '  <th>样品编号</th>'.
                    '  <th>样品名称</th>'.
                    '  <th>备注</th>'.
                  '</tr>';
        for($i = 0; $i < count($tab); $i++) {
            if ($i % 2 == 0) {
                $tables = $tables.'<tr class="alt">';
            } else {
                $tables = $tables.'<tr class="">';
            }
            $tables = $tables.
                '	 <td>'. $tab[$i]['id'] .'</td>'.
                '	 <td>'. $tab[$i]['name'] .'</td>'.
                '	 <td>'. $tab[$i]['desc'] .'</td>'.
            '</tr>';
        }

        return  '<table id="customers">' .
                    $tables.
                '</table>';
    }

    private static function getImportTableData($json) {
        $tables = '';
        $tab = json_decode($json, true);

        $tables = '<tr>'.
            '  <th>样品编号</th>'.
            '  <th>样品名称</th>'.
            '  <th>序列号</th>'.
            '  <th>型号</th>'.
            '  <th>备注</th>'.
            '  <th>课</th>'.
            '  <th>负责人</th>'.
            '</tr>';
        for($i = 0; $i < count($tab); $i++) {
            if ($i % 2 == 0) {
                $tables = $tables.'<tr class="alt">';
            } else {
                $tables = $tables.'<tr class="">';
            }
            $tables = $tables.
                '	 <td>'. $tab[$i]['id'] .'</td>'.
                '	 <td>'. $tab[$i]['name'] .'</td>'.
                '	 <td>'. $tab[$i]['sn'] .'</td>'.
                '	 <td>'. $tab[$i]['pn'] .'</td>'.
                '	 <td>'. $tab[$i]['remark'] .'</td>'.
                '	 <td>'. $tab[$i]['section'] .'</td>'.
                '	 <td>'. $tab[$i]['charge'] .'</td>'.
                '</tr>';
        }

        return  '<table id="customers">' .
            $tables.
            '</table>';

    }

    private static function getApplyTableData($json) {
        $tab = json_decode($json, true);

        $tables = '<tr>'.
            '  <th>样品编号</th>'.
            '  <th>样品名称</th>'.
            '  <th>预计归还时间</th>'.
            '  <th>备注</th>'.
            '</tr>';
        for($i = 0; $i < count($tab); $i++) {
            if ($i % 2 == 0) {
                $tables = $tables.'<tr class="alt">';
            } else {
                $tables = $tables.'<tr class="">';
            }
            $tables = $tables.
                '	 <td>'. $tab[$i]['id'] .'</td>'.
                '	 <td>'. $tab[$i]['name'] .'</td>'.
                '	 <td>'. $tab[$i]['predictDate'] .'</td>'.
                '	 <td>'. $tab[$i]['desc'] .'</td>'.
                '</tr>';
        }

        return  '<table id="customers">' .
            $tables.
            '</table>';
    }

    private static function getFooter() {
        return
            '<p style="margin-top: 10px">点击<a style="font-size:12px;" href="'.EMS_URL .'">此链接</a>确认详情</p>';
    }
    private static function getCSSStyle() {
        return
            '<style type="text/css">' .
            '     p {'.
            '        font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;'.
            '        font-size:13px;'.
            '        margin: 8px 0px 4px 0px;'.
            '        color: Purple'.
            '     }'.
            '     #customers{'.
            '        font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;'.
            '        border-collapse:collapse;'.
            '     }'.
            '     #customers td , #customers th {'.
            '        font-size:12px;'.
            '        font-weight:normal;'.
            '        border:1px solid #D4D4D4;'.
            '        text-align: left;'.
            '        padding: 5px 5px 5px 5px;'.
            '     }'.
            '     #customers th {'.
//            '        background-color:#317EF3;'.
            '        background-color:'.self::getRandomColor().
            '        color:#ffffff;'.
            '      }'.
            '      #customers tr.alt td {'.
            '         color:#000000;'.
            '         background-color:#F6F4F0;'.
            '      }'.
            '</style>' ;
    }

    // 别问, 问就是生成所有的颜色满足领导一切需求
    private static function getRandomColor() {
        $colors = array('Crimson', 'Wheat', 'Peru', 'LightSeaGreen', 'BlueViolet', 'RoyalBlue', 'Black');

        return $colors[rand(0, 6)].';';
    }
}