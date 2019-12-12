ALTER TABLE `tpms`.`users`
ADD COLUMN `ems_uid` INT(11) NULL DEFAULT NULL AFTER `auth_method`;


-- 添加right button desc
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_add');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_delete');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_edit');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_update');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_scrap');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_import');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_btn_assign');


-- right nav
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_assign');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_scrap');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_delete');
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
CREATE TABLE `tpms`.`ems_mail_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `main_body` TEXT NULL,
  `subject` TEXT NULL,
  `from` TEXT NULL,
  `to` TEXT NULL,
  `table_data` TEXT NULL);
