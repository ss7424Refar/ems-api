<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 18-12-12
 * Time: 上午9:13
 */

namespace ext;

class MailTemplate {

    public static $subjectBorrowApply = '[样机借出审批] Workflow from ';
    public static $subjectBorrowApplyApproveFromSection = '[样机借出审批通过] Workflow from ';
    public static $subjectBorrowApplyRejectFromSection = '[样机借出审批拒绝] Workflow from ';
    public static $subjectUsing = 'Workflow:现有样机使用确认(';
    public static $subjectDeleteApprove = 'Workflow:现有样机删除审批(';
    public static $subjectScrapApprove = 'Workflow:现有样机报废审批(';
    public static $subjectEnding = ')';

    public static function getBorrowApplyMainBody($section, $user) {
        return
            '<p>Dear Managers</p>'.
            '<p>'. $section .'课的'. $user .'提交了样品借出申请, 请登录样品管理系统确认及审批, 谢谢!</p>';

    }

    public static function getReplyApproveBorrowApplyFromSection() {
        return
            '<p>Dear Sample Manager</p>'.
            '<p>如下样机审批通过, 请登录样品管理系统确认及审批, 谢谢!</p>';

    }

    public static function getReplyRejectBorrowApplyFromSection($user) {
        return
            '<p>Dear '. $user. '</p>'.
            '<p>如下样机审批拒绝, 请登录样品管理系统确认及审批, 谢谢!</p>';

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


    private static function getTableData($json) {
        $tables = '';
        $tab = json_decode($json, true);

        $tableHeader = '<tr>'.
                        '  <th>样品编号</th>'.
                        '  <th>样品名称</th>'.
                        '  <th>备注</th>'.
                        '</tr>';
        for($i = 0; $i < count($tab); $i++) {
            if ($i % 2 == 0) {
                $tables = $tableHeader.
                            '<tr class="alt">'.
                            '	 <td>'. $tab[$i]['id'] .'</td>'.
                            '	 <td>'. $tab[$i]['name'] .'</td>'.
                            '	 <td>'. $tab[$i]['desc'] .'</td>'.
                            '</tr>';
            } else {
                $tables = $tableHeader.
                            '<tr class="alt">'.
                            '	 <td>'. $tab[$i]['id'] .'</td>'.
                            '	 <td>'. $tab[$i]['name'] .'</td>'.
                            '	 <td>'. $tab[$i]['desc'] .'</td>'.
                            '</tr>';
            }

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
            '	  p {'.
            '        font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;'.
            '        font-size:0.75em;'.
            '        margin: 8px 0px 2px 0px'.
            '     }'.
            '	  #customers{'.
            '        font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;'.
            '        border-collapse:collapse;'.
            '        margin-top:10px'.
            '     }'.
            '	  #customers td, #customers th{'.
            '        font-size:0.75em;'.
            '        border:1px solid #D4D4D4;'.
            '        padding:3px 7px 2px 7px;'.
            '	  }'.
            '	  #customers th {'.
            '        font-size:0.75em;'.
            '        text-align:left;'.
            '        padding-top:5px;'.
            '        padding-bottom:4px;'.
            '        background-color:#317EF3;'.
            '        color:#ffffff;'.
            '	   }'.
            '	   #customers tr.alt td {'.
            '         color:#000000;'.
            '         background-color:#F6F4F0;'.
            '	   }'.
            '</style>' ;
    }
}