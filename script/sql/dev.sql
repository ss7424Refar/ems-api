ALTER TABLE `tpms`.`users`
ADD COLUMN `ems_uid` INT(11) NULL DEFAULT NULL AFTER `auth_method`;


-- 添加right button desc
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_add');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_delete');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_edit');
-- INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_update');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_scrap');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_import');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_assign');


-- right nav
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_assign');
-- INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_scrap');
-- INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_delete');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_borrow_review');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_scrap_review');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_delete_review');


-- 插入admin对应的权限 (8)
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '64');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '70');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '71');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '72');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '73');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '74');
INSERT INTO `tpms`.`role_rights` (`role_id`, `right_id`) VALUES ('8', '75');


-- 增加ems_mail_queue
DROP TABLE IF EXISTS `ems_mail_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ems_mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) DEFAULT NULL,
  `main_body` text,
  `subject` text,
  `from` text,
  `to` text,
  `table_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- 增加样品区分表
CREATE TABLE `tpms`.`ems_const` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  PRIMARY KEY (`id`));

UPDATE `tpms`.`ems_user` SET `MAIL`='yuanjin.chen@dbh.dynabook.com' WHERE `ID`='1';


-- +---------------------------------------------------------------------------------
-- + beautiful line
-- +---------------------------------------------------------------------------------

-- ems_log_record.sql
CREATE TABLE `tpms`.`ems_log_record` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `fixed_no` VARCHAR(20) NULL,
  `desc` VARCHAR(45) NULL,
  `role` VARCHAR(50) NULL,
  `operator` VARCHAR(45) NULL,
  `type` VARCHAR(15) NULL,
  `result` VARCHAR(15) NULL,
  `time` DATETIME NULL,
  PRIMARY KEY (`id`));


-- ems_log_record_add_reason.sql
ALTER TABLE `tpms`.`ems_log_record`
  ADD COLUMN `reason` VARCHAR(80) NULL DEFAULT NULL AFTER `result`;


-- Add broken to main machine
ALTER TABLE `tpms`.`ems_main_engine`
  ADD COLUMN `broken` INT(1) NULL DEFAULT 0 COMMENT '损坏' AFTER `remark`;

-- update broken to 1
update `tpms`.`ems_main_engine`
set broken = 1
where remark like '%坏%';

-- 修改实际价格、含税价格可以编辑成美元/日元.
ALTER TABLE `tpms`.`ems_main_engine`
CHANGE COLUMN `actual_price` `actual_price` VARCHAR(20) NULL DEFAULT NULL COMMENT '实际价格' ,
CHANGE COLUMN `tax_inclusive_price` `tax_inclusive_price` VARCHAR(20) NULL DEFAULT NULL COMMENT '含税价格';

-- 免3c证明号
ALTER TABLE `tpms`.`ems_main_engine`
ADD COLUMN `three_c_code` VARCHAR(50) DEFAULT NULL COMMENT '免3c证明号' AFTER `invoice_no`;

-- 拒绝记录
ALTER TABLE `tpms`.`ems_main_engine`
ADD COLUMN `reject_flag` INT(1) NULL DEFAULT '0' COMMENT '拒绝记录' AFTER `remark`;

-- 是否免3c证明号
ALTER TABLE `tpms`.`ems_main_engine`
ADD COLUMN `three_c_flag` INT(1) NULL DEFAULT '0' COMMENT '是否免3c' AFTER `invoice_no`;

-- 修改utf8
-- ALTER TABLE `tpms`.`ems_main_engine`
-- CHANGE COLUMN `MODEL_NAME` `MODEL_NAME` VARCHAR(100) NOT NULL COMMENT '资产名称' ,
-- CHANGE COLUMN `invoice_no` `invoice_no` VARCHAR(50) NULL DEFAULT NULL COMMENT '发票号' ,
-- CHANGE COLUMN `user_name` `user_name` VARCHAR(45) NULL DEFAULT NULL COMMENT '使用者姓名' ,
-- CHANGE COLUMN `approver_name` `approver_name` VARCHAR(45) NULL DEFAULT NULL COMMENT '审批者姓名' ;

-- 确认查询
show create table ems_main_engine;

-- 修改至DATE类型

ALTER TABLE `tpms`.`ems_main_engine`
CHANGE COLUMN `purchase_date` `purchase_date` DATE NULL DEFAULT NULL COMMENT '购买日期' ,
CHANGE COLUMN `invoice_date` `invoice_date` DATE NULL COMMENT '发票日期' ,
CHANGE COLUMN `warranty_date` `warranty_date` DATE NULL DEFAULT NULL COMMENT '保修日期' ;

