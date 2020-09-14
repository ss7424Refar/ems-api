<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-11
 * Time: 上午10:11
 */

namespace app\tasks\controller;

use ext\MailTemplate;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;
Loader::import('lib.swift_required');
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Swift_RfcComplianceException;

class MailMan {

    public function dog() {

        try {
            Log::record('[MailMan] start');

//            $resE = Db::table('ems_mail_queue')->whereIn('to', ['[]', '[null]'])
//                ->whereOr('from', null)->select();
//
//            // 如果存在垃圾数据, 发送邮件给自己
//            if (!empty($resE)) {
//                Log::record('ems_queue has error');
//                self::send(config('mail_from'), ["lin.zhu@dbh.dynabook.com"], '[]',
//                    'ems_queue has error', 'ems_queue has error! please check!');
//            }

            $res = Db::table('ems_mail_queue')->where('to', '<>', '[]')
                ->where('to', '<>', '[null]')->whereNotNull('from')
                ->order('id')->select();

            foreach ($res as $key => $item) {
                if (FLOW == $item['type']) {
                    $content = MailTemplate::getContent($item['main_body'], $item['table_data']);
                    $cc = config('mail_cc');
                } else {
                    $content = MailTemplate::getImportContent($item['main_body'], $item['table_data']);
                    $cc = config('mail_import_cc');
                }

                Log::record($content);
                try {
                    $r = self::send($item['from'], json_decode($item['to'], true), $cc,
                        $item['subject'], $content);

                    if ($r > 0) {
                        Log::record('[MailMan][dog] success ' .$item['id']);

                        // 删除该条记录
                        Db::table('ems_mail_queue')->where('id', $item['id'])->delete();
                    }
                } catch (Swift_RfcComplianceException $e) {
                    Log::record('[MailMan][dog] fail ' .$item['id'] .' '.$e->getMessage());
//                    self::send(config('mail_from'), ["lin.zhu@dbh.dynabook.com"], '[]',
//                        'ems_queue has error', 'ems_queue has error! please check!');
                }
            }
        } catch (Exception $e) {
            Log::record('[MailMan][dog] error' . $e->getMessage());
        }

        Log::record('[MailMan] end');
    }

    // 发送邮件function
    private static function send($from, $to, $cc, $mailTitle, $content) {

        $transport = Swift_SmtpTransport::newInstance(config('smtp_host'), config('smtp_port'));

        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($mailTitle)
            ->setFrom(array($from))
            ->setTo($to) // 这里也是需要数组的
            ->setCc(json_decode($cc, true))
            ->setBody($content, 'text/html', 'utf-8');

        // Send the message
        $result = $mailer->send($message);
        return $result;
    }
}