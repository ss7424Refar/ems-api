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

class Test extends Controller {
    function test(){
        $res = Db::table('ems_user')->where('user_id', 'admin')->find();

        dump($res['USER_NAME']);

    }

}