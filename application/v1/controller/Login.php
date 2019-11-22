<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午3:13
 */

namespace app\v1\controller;

use think\Controller;
use think\Exception;
use think\Session;
use think\Db;
use think\Log;

class Login extends Controller {

    public function check(){
        if (config('session_debug')) {
            Session::set('ems_user','admin');
            return apiResponse(SUCCESS, 'valid user');
        } else {
            if (null == $this->request->server('HTTP_REFERER')) {
                return apiResponse(ERROR, 'no http referer');
            } else {
                // 判断user_id是否存在于ems中
                $userId = $this->request->param('userId');

                try {
                    $res = Db::table('ems_user')->where('user_id', $userId)->find();

                    if (null == $res) {
                        return apiResponse(ERROR, 'invalid user');
                    } else {
                        Log::record('hello! '. $res['USER_NAME']); // 表字段是大写的.
                        // 存入session
                        Session::get('ems_user', $userId);
                        return apiResponse(SUCCESS, 'valid user');
                    }
                } catch (Exception $e) {
                    Log::record('[Login][check] get user_id error '. $e->getMessage());
                    return apiResponse(ERROR, 'server error');
                }
            }

        }

    }

}