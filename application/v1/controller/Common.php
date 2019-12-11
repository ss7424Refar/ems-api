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
use think\exception\HttpResponseException;

class Common extends Controller {

    //登录用户
    public $loginUser = array();

    public function _initialize(){
        parent::_initialize();
        $this->checkSession();
    }

    public function checkSession(){
        if (null == Session::get('loginUser')){
            $type = $this->getResponseType(); // 获取当前的 response 输出类型
            $result = [
                'status' => TIMEOUT,
                'msg'  => 'user session timeout',
                'data' => []
            ];

            $response = Response::create($result, $type)->header(getHttpHeader());

            throw new HttpResponseException($response); // 构造方法可以用throw
        }

        $this->loginUser = Session::get('loginUser');

    }

}