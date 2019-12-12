<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 18-12-10
 * Time: 下午2:00
 */
namespace app\push\controller;

use think\worker\Server;

use Workerman\Lib\Timer;
use think\Log;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2344';

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {

        // 只在id编号为0的进程上设置定时器，其它1、2、3号进程不设置定时器
        // 执行watchExpired
        if($worker->id === 0) {
            Timer::add(2, function()use($worker){
                Log::record('[Worker][id][1][SendMail]');
                $watcher = controller('tasks/MailMan');
                $watcher->dog();
            });
        }
    }
}