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
