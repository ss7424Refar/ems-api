<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 18-12-12
 * Time: 上午9:13
 */

namespace ext;
use think\Loader;
Loader::import('lib.swift_required'); // 不要问我为什么，本尊也不造。
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

/**
 * Class MailerUtil
 * @package ext
 */
class MailerUtil {

    /**
     * @param $from
     * @param $to
     * @param $cc
     * @param $mailTitle
     * @param $content
     * @return int
     */
    public static function send($from, $to, $cc, $mailTitle, $content) {

        $transport = Swift_SmtpTransport::newInstance(config('smtp_host'), config('smtp_port'));

        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($mailTitle)
            ->setFrom(array($from))
            ->setTo($to)
            ->setCc(json_decode($cc, true))
            ->setBody($content, 'text/html', 'utf-8');

        // Send the message
        $result = $mailer->send($message);
        return $result;

    }
}