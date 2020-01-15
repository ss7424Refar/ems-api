<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-18
 * Time: 下午2:48
 */

namespace app\v1\controller;

use think\Controller;
use think\Session;
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
        $this->checkSession();
    }

    public function checkMethod() {
        // vue会发送预请求, option处理
        if (Request::instance()->isOptions()) {
            $type = $this->getResponseType();
            $result = [
                'status' => SUCCESS,
                'msg'  => 'P.G.D', // permission get data
                'data' => []
            ];

            $response = Response::create($result, $type)->header(getHttpHeader());
            throw new HttpResponseException($response);
        }
    }

    public function checkSession(){
        if (null == Session::get('sampleLoginUser')){
            $type = $this->getResponseType(); // 获取当前的 response 输出类型
            $result = [
                'status' => TIMEOUT,
                'msg'  => 'user session timeout',
                'data' => []
            ];

            $response = Response::create($result, $type)->header(getHttpHeader());

            throw new HttpResponseException($response); // 构造方法可以用throw
        }

        $this->loginUser = Session::get('sampleLoginUser');

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
}