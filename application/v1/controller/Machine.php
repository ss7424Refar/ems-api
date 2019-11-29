<?php
namespace app\v1\controller;


class Machine extends Common {

    public function getMachineList() {


    }

    public function getMachineById() {
        $fixed_no = $this->request->param('fixed_no');

        $names = array('资产编号', '资产名称', '资产序列号', '型号', '流水号', '发票号', '购买日期',
                        '发票日期', '保修日期', '实际价格', '含税价格', '位置', '状态', '使用者', '备注',
                        'CPU', '硬盘', '内存', 'Mac地址', '光驱', '屏幕尺寸', '部门', '课', '供应商');


    }
}
