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

class Common extends Controller {

    //登录用户
    public $loginUserId = '';

    public function _initialize(){
        parent::_initialize();
        $this->checkSession();
    }

    /* @throws
     * check session
     */
    protected function checkSession(){

        if (null == Session::get('ems_user')){
            return apiResponse(TIMEOUT, 'user session timeout');
        }

        $this->loginUserId = Session::get('ems_user');

    }

}