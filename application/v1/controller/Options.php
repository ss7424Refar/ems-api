<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-30
 * Time: 下午2:36
 */

namespace app\v1\controller;

class Options  extends Common {
    public function getDepartment() {
        $depart = json_decode(DEPART, true);

        return apiResponse(SUCCESS, '[Options][getDepartment] success', $this->getKeyValue($depart));
    }

    public function getSection() {
        $section = json_decode(SECTION, true);

        return apiResponse(SUCCESS, '[Options][getSection] success', $this->getKeyValue($section));
    }

    public function getStatus() {
        $status = json_decode(STATUS, true);

        return apiResponse(SUCCESS, '[Options][getStatus] success', $this->getKeyValue($status));
    }

    private function getKeyValue($arr) {
        $jsonResult = array();
        foreach($arr as $key => $value){
            $tmp = array();
            $tmp['value'] = $key;
            $tmp['text'] = $value;
            array_push($jsonResult, $tmp);
        }
        array_unshift($jsonResult, array('value'=>null, 'text'=>'请选择'));
        return $jsonResult;
    }
}