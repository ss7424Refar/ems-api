<?php
/**
 * Created by PhpStorm.
 * User: refar
 * Date: 19-11-19
 * Time: 下午2:04
 */

/*
 * api response code
 */
define('SUCCESS', 0);
define('ERROR', 1);
define('TIMEOUT', 2);

/*
 * 部门/状态/课
 */

define('SECTION', json_encode(array('1884'=>'SCD', '2271'=>'SWV', '2272'=>'PSD', '2273'=>'CUD',
                                   '2274'=>'FWD', '442'=>'SYD', '462'=>'HWD', '485'=>'MED',
                                   '491'=>'CSV', '499'=>'HWV', '520'=>'PAV','540'=>'SSD')));

define('STATUS', json_encode(array('在库', '待借出审批', '待分配', '使用中', '待报废审批', '已报废',
                                        '待删除审批')));
define('DEPART', json_encode(array('29'=>'DT部', '33'=>'VT部', '37'=>'SWT部')));

// 更新字段时为了看懂用到状态
define('IN_STORE', 0);
define('BORROW_REVIEW', 1);
define('ASSIGNING', 2);
define('USING', 3);
define('SCRAP_REVIEW', 4);
define('SCRAPED', 5);
define('DELETE_REVIEW', 6);

// 定义权限的roleId
define('ADMIN', 8);
define('MANAGER', 6);
define('EMS_ADMIN', 14);
define('EMS_APPROVE', 15);
define('COMMON_USER', 16);

