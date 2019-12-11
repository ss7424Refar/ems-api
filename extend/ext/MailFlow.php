<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-12-10
 * Time: 下午1:17
 */

namespace ext;
use think\Loader;
Loader::import('lib.swift_required');
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;


class MailFlow
{
    private $path;
    // 进程运行状态
    public static $working = 1;
    public static $resting = 0;

    public function __construct(){

        $this->path = RUNTIME_PATH. 'mail_worker_flag.txt';
        $this->createFile();
    }

    public function dog() {


    }

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

    private function createFile() {
        // 文件不存在则尝试创建之。可读又可以写
        if(!file_exists($this->path)) {
            $file = fopen($this->path, 'w+');

            fwrite($file, self::$resting);
            fclose($file);

            chmod($this->path, 0777);
        }
    }
}