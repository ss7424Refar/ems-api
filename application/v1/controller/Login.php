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
     * showdoc
     * @catalog 接口文档/登录入口
     * @title 登录入口
     * @description 登录ems系统的入口check接口
     * @method post
     * @url http://domain/ems-api/v1/login/check
     * @return {"status":0,"msg":"access","data":[]}
     * @return_param status int 状态码
     * @return_param msg string 状态码说明
     * @remark 需要点击链接的时候发送请求，或返回1/2， 则不能进入系统
     */
    public function check(){
        if (config('session_debug')) {
            if (ADMIN == config('current_role_id')) {
                Session::set('loginUser', array('T'=>'admin', 'ems'=>'admin', 'roleId'=>ADMIN, 'section'=>'2271'));
            } elseif (COMMON_USER == config('current_role_id')) {
                Session::set('loginUser',
                    array('T'=>'Huang Chunna', 'ems'=>'500357', 'roleId'=>COMMON_USER, 'section'=>'2271'));
            } elseif (EMS_ADMIN == config('current_role_id')) {
                Session::set('loginUser',
                    array('T'=>'Lin Chong', 'ems'=>'q20099th', 'roleId'=>EMS_ADMIN, 'section'=>'2271'));
            } elseif (EMS_AUDITOR == config('current_role_id')) {
                Session::set('loginUser',
                    array('T'=>'Han Guangri', 'ems'=>'p51213th', 'roleId'=>EMS_AUDITOR, 'section'=>'2271'));
            } elseif (T_MANAGER == config('current_role_id')) {
                Session::set('loginUser',
                    array('T'=>'Wang Yan', 'ems'=>'p51215th', 'roleId'=>T_MANAGER, 'section'=>'2271'));
            } elseif (S_MANAGER == config('current_role_id')) {
                Session::set('loginUser',
                    array('T'=>'Lu Yan', 'ems'=>'p90614th', 'roleId'=>S_MANAGER, 'section'=>'2271'));
            }

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
                        // 像预留角色之类, 会被拒绝登录
                        return apiResponse(ERROR, 'T reject');
                    } else {
                        // 查询ems系统用户
                        $ems_user = Db::table('ems_user')->where('ID', $t_user['ems_uid'])
                                        ->where('IS_DELETED', 0)->find();

                        if (empty($ems_user)) {
                            return apiResponse(ERROR, 'ems invalid');
                        } elseif (empty($ems_user['SECTION'])) {
                            return apiResponse(ERROR, 'ems invalid(no section)');
                        } else {
                            Session::set('loginUser',
                                array('T'=>$t_user['login'],
                                      'ems'=>$ems_user['USER_ID'],
                                      'roleId'=>$t_user['role_id'],
                                      'section'=>$ems_user['SECTION']));

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