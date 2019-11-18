use itd;
SET SQL_SAFE_UPDATES=0;

DROP TABLE `c3p0TestTable`, `c3p0testtable`, `d_approve`, `d_asset_status_record`,
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

-- 需要增加角色对应的权限json
ALTER TABLE `m_role` 
CHANGE COLUMN `AUTHORITY` `AUTHORITY` TEXT;

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
delete FROM itd.r_role_user where IS_DELETED = 1;

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

-- 添加role_json
UPDATE `ems_role` SET `AUTHORITY`='{\"add\":true, \"edit\":true, \"delete\":true, \"srap\":true, \"apply\":true, \"assign\":true, \"return\":true, \"import\":true, \"export\":true, \"applayCheck\":true, \"scrapCheck\":true, \"deleteCheck\":true}' WHERE `ROLE_ID`='1';
UPDATE `ems_role` SET `AUTHORITY`='{\"add\":true,\"edit\":true,\"delete\":true,\"srap\":true,\"apply\":true,\"assign\":true,\"return\":true,\"import\":true,\"export\":true,\"applayCheck\":false,\"scrapCheck\":false,\"deleteCheck\":false}' WHERE `ROLE_ID`='2';
UPDATE `ems_role` SET `AUTHORITY`='{\"add\":false,\"edit\":false,\"delete\":false,\"srap\":false,\"apply\":true,\"assign\":false,\"return\":false,\"import\":false,\"export\":false,\"applayCheck\":false,\"scrapCheck\":false,\"deleteCheck\":false}' WHERE `ROLE_ID`='3';
UPDATE `ems_role` SET `AUTHORITY`='{\"add\":false,\"edit\":false,\"delete\":false,\"srap\":false,\"apply\":false,\"assign\":false,\"return\":false,\"import\":false,\"export\":false,\"applayCheck\":false,\"scrapCheck\":true,\"deleteCheck\":false}' WHERE `ROLE_ID`='4';
UPDATE `ems_role` SET `AUTHORITY`='{\"add\":false,\"edit\":false,\"delete\":false,\"srap\":false,\"apply\":true,\"assign\":false,\"return\":false,\"import\":false,\"export\":false,\"applayCheck\":true,\"scrapCheck\":false,\"deleteCheck\":true}' WHERE `ROLE_ID`='5';
