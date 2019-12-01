use tpms;
SET SQL_SAFE_UPDATES=0;

DROP TABLE `c3p0testtable`, `d_approve`, `d_asset_status_record`,
`d_attachment`, `d_cabinet`, `d_camera`, `d_comment_log`, `d_const_monitor`,
`d_display`, `d_error_record`, `d_ip_telephone`, `d_main_engine_bak`, `d_monitor_system`, 
`d_ovh`, `d_printer`, `d_relay_system`, `d_scanner`, `d_software_asset`, `d_ups`;

DROP TABLE `h_app_config`, `h_app_info`, `h_asset`, `h_change`, `h_db_config`, 
`h_db_info`, `h_network_config`, `h_network_info`, `h_problem`, `h_request`, 
`h_system_config`, `h_system_info`, `h_webpage_config`, `h_webpage_info`, `m_approve_setting`, 
`m_authority`, `m_group`, `m_maintenance`, `m_project`, `m_project_user`, `m_supplier`, 
`r_group_user`, `r_role_authority`, `sla_level_setting`, `t_solution`;

-- d_main_engine增加[供应商]注释
ALTER TABLE `d_main_engine` CHANGE COLUMN `supplier` `supplier` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '供应商' ;

-- d_main_engine 增加[区分]字段
ALTER TABLE `d_main_engine` 
CHANGE COLUMN `supplier` `supplier` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '供应商' ,
ADD COLUMN `category` VARCHAR(25) CHARACTER SET 'utf8' NULL COMMENT '区分' AFTER `MODEL_NAME`;

-- m_const 去掉没用的字段
ALTER TABLE `m_const` 
DROP COLUMN `IP_ADDRESS`,
DROP COLUMN `UPDATE_DATE`,
DROP COLUMN `UPDATE_USER`,
DROP COLUMN `CREATE_DATE`,
DROP COLUMN `CREATE_USER`,
DROP COLUMN `IS_DELETED`;

-- m_role 去掉没用字段
ALTER TABLE `m_role` 
DROP COLUMN `IP_ADDRESS`,
DROP COLUMN `UPDATE_DATE`,
DROP COLUMN `UPDATE_USER`,
DROP COLUMN `CREATE_DATE`,
DROP COLUMN `CREATE_USER`,
DROP COLUMN `IS_DELETED`;

-- m_user
ALTER TABLE `m_user` 
DROP COLUMN `IS_IMPORT`,
DROP COLUMN `EMPLID`,
DROP COLUMN `LOCATION`,
DROP COLUMN `CODE_CENTER`,
DROP COLUMN `IP_ADDRESS`,
DROP COLUMN `UPDATE_DATE`,
DROP COLUMN `UPDATE_USER`,
DROP COLUMN `CREATE_DATE`,
DROP COLUMN `CREATE_USER`,
DROP COLUMN `COMPANY`,
DROP COLUMN `PLACE`,
DROP COLUMN `JOB`,
DROP COLUMN `GENDER`,
DROP COLUMN `PHONE_NO`,
DROP COLUMN `MOBILE_NO`,
DROP COLUMN `SUP_USER_ID`;

-- 不知道m_user中is_delete中的字段是否对history有用

-- r_role_user 去掉没用的字段
ALTER TABLE `r_role_user` 
DROP COLUMN `IP_ADDRESS`,
DROP COLUMN `UPDATE_DATE`,
DROP COLUMN `UPDATE_USER`,
DROP COLUMN `CREATE_DATE`,
DROP COLUMN `CREATE_USER`;

-- 删除已经去除的用户
delete FROM r_role_user where IS_DELETED = 1;

-- 删除is_deleted列
ALTER TABLE `r_role_user` 
DROP COLUMN `IS_DELETED`;

-- 重命名
ALTER TABLE `d_main_engine` 
RENAME TO  `ems_main_engine` ;

ALTER TABLE `h_borrow_history` 
RENAME TO  `ems_borrow_history` ;

ALTER TABLE `m_const` 
RENAME TO  `ems_const` ;

ALTER TABLE `m_role` 
RENAME TO  `ems_role` ;

ALTER TABLE `m_user` 
RENAME TO  `ems_user` ;

ALTER TABLE `r_role_user` 
RENAME TO  `ems_role_user` ;


-- 去除回车

ALTER TABLE `tpms`.`ems_main_engine`
  CHANGE COLUMN `SERIAL_NO` `SERIAL_NO` VARCHAR(50) CHARACTER SET 'utf8' NOT NULL COMMENT '资产序列号' ,
  CHANGE COLUMN `CPU` `CPU` VARCHAR(200) CHARACTER SET 'utf8' NOT NULL COMMENT 'CPU' ,
  CHANGE COLUMN `HDD` `HDD` CHAR(20) CHARACTER SET 'utf8' NOT NULL COMMENT '硬盘' ,
  CHANGE COLUMN `MEMORY` `MEMORY` VARCHAR(20) CHARACTER SET 'utf8' NOT NULL COMMENT '内存' ,
  CHANGE COLUMN `type` `type` VARCHAR(50) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '型号' ,
  CHANGE COLUMN `purchase_date` `purchase_date` TIMESTAMP NULL DEFAULT NULL COMMENT '购买日期' ,
  CHANGE COLUMN `invoice_date` `invoice_date` TIMESTAMP NULL DEFAULT NULL COMMENT '发票日期' ,
  CHANGE COLUMN `warranty_date` `warranty_date` TIMESTAMP NULL DEFAULT NULL COMMENT '保修日期' ,
  CHANGE COLUMN `actual_price` `actual_price` DOUBLE NULL DEFAULT NULL COMMENT '实际价格' ,
  CHANGE COLUMN `tax_inclusive_price` `tax_inclusive_price` DOUBLE NULL DEFAULT NULL COMMENT '含税价格' ,
  CHANGE COLUMN `screen_size` `screen_size` VARCHAR(20) NULL DEFAULT NULL COMMENT '屏幕尺寸' ,
  CHANGE COLUMN `mac_address` `mac_address` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT 'Mac地址' ,
  CHANGE COLUMN `cd_rom` `cd_rom` CHAR(20) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '光驱' ,
  CHANGE COLUMN `location` `location` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '位置' ,
  CHANGE COLUMN `department` `department` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '部门' ,
  CHANGE COLUMN `section_manager` `section_manager` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '课长' ,
  CHANGE COLUMN `remark` `remark` VARCHAR(200) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '备注' ,
  CHANGE COLUMN `model_status` `model_status` CHAR(2) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '样机状态' ,
  CHANGE COLUMN `instore_operator` `instore_operator` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '入库操作者' ,
  CHANGE COLUMN `instore_date` `instore_date` TIMESTAMP NULL DEFAULT NULL COMMENT '入库时间' ,
  CHANGE COLUMN `scrap_date` `scrap_date` TIMESTAMP NULL DEFAULT NULL COMMENT '报废时间' ,
  CHANGE COLUMN `user_id` `user_id` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '使用者' ,
  CHANGE COLUMN `start_date` `start_date` TIMESTAMP NULL DEFAULT NULL COMMENT '开始使用时间' ,
  CHANGE COLUMN `predict_date` `predict_date` TIMESTAMP NULL DEFAULT NULL COMMENT '预估归还时间' ,
  CHANGE COLUMN `end_date` `end_date` TIMESTAMP NULL DEFAULT NULL COMMENT '结束使用时间' ,
  CHANGE COLUMN `approver_id` `approver_id` VARCHAR(45) CHARACTER SET 'utf8' NULL DEFAULT NULL COMMENT '审批操作者ID' ,
  CHANGE COLUMN `approve_date` `approve_date` TIMESTAMP NULL DEFAULT NULL COMMENT '审批时间' ;
