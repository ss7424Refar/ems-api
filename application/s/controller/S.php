<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 21-2-19
 * Time: 下午3:59
 */

namespace app\s\controller;
use think\Controller;

class S extends Controller
{
    public function changeRole() {
        $roleId = $this->request->param('r');

        // 判断runtime下是否有roleId.txt文件
        $roleFile = RUNTIME_PATH. 'roleId.txt';

        $file = fopen($roleFile,"w");
        file_put_contents($roleFile, $roleId);

        fclose($file);

        echo '天天开心哦(*^__^*) 嘻嘻……'.$roleId;
    }

}