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

define('SECTION', json_encode(array('1884' => 'SCD', '2271' => 'SWV', '2272' => 'PSD', '2273' => 'CUD',
    '2274' => 'FWD', '442' => 'SYD', '462' => 'HWD', '485' => 'MED',
    '491' => 'CSV', '499' => 'HWV', '520' => 'PAV', '540' => 'SSD', '2020'=>'FATN', '2021'=>'PSO', '2022'=>'市场保守课', '2023'=>'Design')));

define('STATUS', json_encode(array('在库', '待借出审批', '待分配', '使用中', '待报废审批', '已报废',
    '待删除审批')));
define('DEPART', json_encode(array('29' => 'DT部', '33' => 'VT部', '37' => 'SWT部', '41' => 'NPI部', '45' => '总经办', 
	'49' => '品证部', '53' => 'DBT')));


define('LINKS', json_encode(array(
        '29' => array('442' => 'SYD', '462' => 'HWD', '485' => 'MED', '499' => 'HWV', '2273' => 'CUD'),
        '33' => array('520' => 'PAV'),
        '37' => array('1884' => 'SCD', '2271' => 'SWV', '2272' => 'PSD', '2274' => 'FWD', '491' => 'CSV', '540' => 'SSD'),
        '41' => array('2020'=>'FATN'),
        '45' => array('2021'=>'PSO'),
        '49' => array('2022'=>'市场保守课'),
        '53' => array('2023'=>'Design'))
));

define('BROKEN', json_encode(array('', '损坏')));
define('THREE_C', json_encode(array('否', '是')));
define('REJECT_FLAG', json_encode(array('无', '有')));

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
define('T_MANAGER', 6);
define('S_MANAGER', 20);
define('ST_MANAGER', 21); // 别问我这是啥角色, 我也不知道.
define('EMS_ADMIN', 14);
define('EMS_AUDITOR', 15);
define('COMMON_USER', 16); // 可能没什么用, 先放着

// ems url
define('EMS_URL', 'http://pcs.dbh.dynabook.com/');

// email type
define('FLOW', 'flow');
define('IMPORT', 'import');
define('APPLY', 'apply');
define('REJECT', 'reject');

// log record
define('LOG_DESC_BORROW', '样品申请');
define('LOG_DESC_DELETE', '样品删除');
define('LOG_DESC_SCRAPE', '样品报废');
define('LOG_DESC_ASSIGN', '样品分配');
define('LOG_DESC_RETURN', '样品归还');

define('LOG_TYPE_CHECK', '审批');
define('LOG_TYPE_APPLY', '申请');

define('LOG_RESULT_CANCEL', '取消');
define('LOG_RESULT_REJECT', '拒绝');
define('LOG_RESULT_APPROVE', '同意');
define('LOG_RESULT_ADD', '提出');








