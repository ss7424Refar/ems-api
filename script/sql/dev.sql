ALTER TABLE `tpms`.`users`
ADD COLUMN `ems_uid` INT(11) NULL DEFAULT NULL AFTER `auth_method`;


-- 添加right button desc
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_add');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_delete');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_edit');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_update');

INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_scrap');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_import');


-- right nav
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_return');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_assign');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_scrap');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_delete');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_borrow_review');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_scrap_review');
INSERT INTO `tpms`.`rights` (`description`) VALUES ('ems_nav_delete_review');

