<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午3:55
 */

namespace app\vt\controller;

use think\Controller;
use think\Log;
use think\Loader;
Loader::import('lib.swift_required');
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class Test extends Controller {

    function mail(){
        $content = $this->request->param('content');

        $to = ['min.wang@dbh.dynabook.com'];
        $subject = config('mail_header_subject'). '[调试样式]';
        $from = ['test@dbh.dynabook.com'];

        Log::record($content);
        $r = self::send($from, $to, config('mail_cc'), $subject, $content);

        if ($r > 0) {
            return 'done';
        }
        return 'fail';
    }

    // 发送邮件function
    private static function send($from, $to, $cc, $mailTitle, $content) {

        $transport = Swift_SmtpTransport::newInstance(config('smtp_host'), config('smtp_port'));

        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($mailTitle)
            ->setFrom($from)
            ->setTo($to) // 这里也是需要数组的
            ->setCc(json_decode($cc, true))
            ->setBody($content, 'text/html', 'utf-8');

        // Send the message
        $result = $mailer->send($message);
        return $result;
    }

}