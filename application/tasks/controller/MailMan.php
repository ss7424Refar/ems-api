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

            // 自动重连
            config('database.break_reconnect', true);
            Db::Connect(config('database.'));

            Log::record('[MailMan][DbConfig] '. json_encode(config('database')));

            $res = Db::table('ems_mail_queue')->where('to', '<>', '[]')
                ->where('to', '<>', '[null]')->whereNotNull('from')
                ->order('id')->select();

            $cc = config('mail_cc');

            $content = '';
            foreach ($res as $key => $item) {
                if (FLOW == $item['type']) {
                    $content = MailTemplate::getContent($item['main_body'], $item['table_data']);
                } elseif (IMPORT == $item['type']) {
                    $content = MailTemplate::getImportContent($item['main_body'], $item['table_data']);
                } elseif (APPLY == $item['type']) {
                    $content = MailTemplate::getApplyContent($item['main_body'], $item['table_data']);
                } elseif (REJECT == $item['type']) {
                    $content = MailTemplate::getRejectContent($item['main_body'], $item['table_data']);
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
                }
            }
        } catch (Exception $e) {
            Log::record('[MailMan][dog] error ' . $e->getMessage());
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