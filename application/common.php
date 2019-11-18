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