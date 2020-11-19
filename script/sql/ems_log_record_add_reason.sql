ALTER TABLE `tpms`.`ems_log_record` 
ADD COLUMN `reason` VARCHAR(80) NULL DEFAULT NULL AFTER `result`;