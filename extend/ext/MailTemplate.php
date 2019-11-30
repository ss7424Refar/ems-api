<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 18-12-12
 * Time: 上午9:13
 */

namespace ext;

class MailTemplate {

    public static $subjectBorrowApprove = 'Workflow:现有样机需借出审批(';
    public static $subjectReturn = 'Workflow:现有样机归还(';
    public static $subjectUsing = 'Workflow:现有样机使用确认(';
    public static $subjectDeleteApprove = 'Workflow:现有样机删除审批(';
    public static $subjectScrapApprove = 'Workflow:现有样机报废审批(';
    public static $subjectEnding = ')';

    public static function formatter($subject, $from) {
        return
            '<html charset="utf-8">'.
                '<div class="mail_box">'.
                    '<pre style="font-family: Time New Roman; font-size:15px;">' .
                        '<p></p><p></p>'.
                        '<p>'.$subject.'<p>'.
                        '<p>'. 'From:'. $from . '<p>'.
                         '<p></p><p></p>'.

                        '<p><span>Please check it and judge if you approve or not. Please check in 
                            <a href="http://172.30.52.43/ems/">EMS Management System</a> for details.</span><p>'.

                        '<p>Thanks&BestRegards!</p>'.

                    '</pre>'.
                '</div>'.
            '</html>';

    }
}