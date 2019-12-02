<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function apiResponse($status = 0, $msg = null, $data = []) {
    // 跨域
    header('Access-Control-Allow-Origin:*');
    // 响应类型
    header('Access-Control-Allow-Methods:*');
    // 响应头设置
    header('Access-Control-Allow-Headers:x-requested-with,content-type,multipart/form-data');
    // data
    $data = [
        'status'=>$status,
        'msg'=>$msg,
        'data'=>$data,
    ];

    return json($data);

}

function getSearchCondition($formData) {
    $map = array(); // 查询条件

    if ($formData) {
        $formData = json_decode($formData);
        if (!empty($formData->fixed)) {
            $map['fixed_no'] = $formData->fixed;
        }
        if (!empty($formData->names)) {
            $map['MODEL_NAME'] = ['like', '%' . $formData->names . '%'];
        }
        if (!empty($formData->serial)) {
            $map['SERIAL_NO'] = $formData->serial;
        }
        if (!empty($formData->type)) {
            $map['type'] = ['like', '%' . $formData->type . '%'];
        }
        if (!empty($formData->user)) {
            $map['user_name'] = $formData->user;
        }
        if (!empty($formData->location)) {
            $map['location'] = $formData->location;
        }
        if (null != $formData->status) {
            $map['model_status'] = $formData->status;
        }
        if (!empty($formData->depart)) {
            $map['department'] = $formData->depart;
        }
        if (!empty($formData->section)) {
            $map['section_manager'] = $formData->section;
        }
        if (!empty($formData->cpu)) {
            $map['cpu'] = ['like', '%' . $formData->cpu . '%'];
        }
        if (!empty($formData->memory)) {
            $map['cpu'] = ['like', '%' . $formData->memory . '%'];
        }
        if (!empty($formData->hardware)) {
            $map['cpu'] = ['like', '%' . $formData->hardware . '%'];
        }
    }

    return $map;

}