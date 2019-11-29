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
use think\Cookie;
use think\Db;
use think\Log;

class Login extends Controller {
    /**
     * 判断点击链接后能够进入ems入口
     * @return \think\response\Json
     */
    public function check(){
        if (config('session_debug')) {
            Session::set('login_user',array('T'=>'admin', 'ems'=>'admin', 'T_role_id'=>8));
            return apiResponse(SUCCESS, 'access');
        } else {
            if (null == $this->request->server('HTTP_REFERER')) {
                return apiResponse(TIMEOUT, 'no http referer');
            } else {

                try {

                    $userCookie = Cookie::get('TESTLINK_USER_AUTH_COOKIE');
                    if (null == $userCookie) {
                        return apiResponse(TIMEOUT, 'no cookie found');
                    }
                    // 查看T系统用户的权限 (查询right_id)
                    $t_user = Db::table('users')->where('cookie_string', $userCookie)->alias('a')
                                ->join('role_rights b', 'a.role_id=b.role_id', 'LEFT')
                                ->find();
                    if (null == $t_user['right_id']) {
                        return apiResponse(ERROR, 'T reject');
                    } else {
                        // 查询ems系统用户
                        $ems_user = Db::table('ems_user')->where('ID', $t_user['ems_uid'])
                                        ->where('IS_DELETED', 0)->find();

                        if (empty($ems_user)) {
                            return apiResponse(ERROR, 'ems invalid');
                        } else {
                            Session::set('login_user',
                                array('T'=>$t_user['login'],
                                      'ems'=>$ems_user['USER_ID'],
                                      'T_role_id'=>$t_user['role_id']));

                            Log::record('hello! ['. $ems_user['USER_ID']. ' ] ' . $ems_user['USER_NAME']);

                            return apiResponse(SUCCESS, 'access');
                        }

                    }

                } catch (Exception $e) {
                    Log::record('[Login][check] error '. $e->getMessage());
                    return apiResponse(ERROR, 'server error');
                }
            }

        }

    }

}