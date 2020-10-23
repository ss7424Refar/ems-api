<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午2:48
 */

namespace app\v1\controller;

use think\Controller;
use think\Cookie;
use think\Response;
use think\Request;
use think\exception\HttpResponseException;

use think\Log;
use think\Exception;
use think\Db;

class Common extends Controller {

    //登录用户
    public $loginUser = array();

    public function _initialize(){
        parent::_initialize();
        $this->checkMethod();
        $this->checkCookie();
    }

    public function checkMethod() {
        // vue会发送预请求, option处理
        if (Request::instance()->isOptions()) {
            $result = [
                'status' => SUCCESS,
                'msg'  => 'P.G.D', // permission get data
                'data' => []
            ];

            $this->throwJsonException($result);
        }
    }

    public function checkCookie(){
        if (config('session_debug')) {
            $this->loginUser = array('T'=>'admin', 'ems'=>'admin', 'roleId'=>ADMIN, 'section'=>'2271',
                'desc'=>'拯救世界的直男');
        } else {
            if (null == $this->request->server('HTTP_REFERER')) {
                $result = [
                    'status' => ERROR,
                    'msg'  => 'no http referer',
                    'data' => []
                ];
                $this->throwJsonException($result);
            } else {
                try {
                    $userCookie = Cookie::get('TESTLINK_USER_AUTH_COOKIE');
                    if (null == $userCookie) {
                        $result = [
                            'status' => TIMEOUT,
                            'msg'  => 'user session timeout',
                            'data' => []
                        ];
                        $this->throwJsonException($result);
                    }
                    // 查看T系统用户的权限 (查询right_id)
                    $t_user = Db::table('users')->where('cookie_string', $userCookie)->alias('a')
                                ->join('role_rights b', 'a.role_id=b.role_id', 'LEFT')
                                ->find();
                    if (null == $t_user['right_id']) {
                        // 像预留角色之类, 会被拒绝登录
                        $result = [
                            'status' => ERROR,
                            'msg'  => 'T reject',
                            'data' => []
                        ];
                        $this->throwJsonException($result);
                    } else {
                        // 查询ems系统用户
                        $ems_user = Db::table('ems_user')->where('ID', $t_user['ems_uid'])
                            ->where('IS_DELETED', 0)->find();

                        if (empty($ems_user)) {
                            $result = [
                                'status' => ERROR,
                                'msg'  => 'ems invalid',
                                'data' => []
                            ];
                            $this->throwJsonException($result);
                        } elseif (empty($ems_user['SECTION'])) {
                            $result = [
                                'status' => ERROR,
                                'msg'  => 'ems invalid(no section)',
                                'data' => []
                            ];
                            $this->throwJsonException($result);
                        } else {
                            // 查询role-desc给log_record用
                            $role = Db::table('roles')->field('notes')
                                        ->where('id', $t_user['role_id'])->find();
                            $desc = str_replace('</p>', '',
                                        str_replace('<p>', '', (empty($role['notes']) ? '': $role['notes'])));

                            $this->loginUser = array(
                                'T'=>$t_user['login'],
                                'ems'=>$ems_user['USER_ID'],
                                'roleId'=>$t_user['role_id'],
                                'section'=>$ems_user['SECTION'],
                                'desc'=>$desc
                            );

                            Log::record('hello! ['. $ems_user['USER_ID']. '] ' . $ems_user['USER_NAME']);
                        }
                    }

                } catch (Exception $e) {
                    Log::record('[Common][checkCookie] error '. $e->getMessage());
                    $result = [
                        'status' => ERROR,
                        'msg'  => 'server error',
                        'data' => []
                    ];
                    $this->throwJsonException($result);
                }
            }

        }
    }

    // 可能每个继承类都需要查询userInfo
    protected function getUserInfoById($userId) {

        try {
            $user = Db::table('ems_user')->where('user_id', $userId)->where('IS_DELETED', 0)
                ->find();
            return $user;
        } catch (Exception $e) {
            Log::record('[Common][getUserInfoById] error' . $e->getMessage());
            return apiResponse(ERROR, 'server error');
        }
    }

    private function throwJsonException($result) {
        $response = Response::create($result, $this->getResponseType())->header(getHttpHeader());
        throw new HttpResponseException($response);
    }
}