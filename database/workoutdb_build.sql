SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `workoutdb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `workoutdb` ;

-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_level`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_level` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_level` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_body_region`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_body_region` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_body_region` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_level_id` BIGINT NOT NULL ,
  `library_body_region_id` BIGINT NOT NULL ,
  `bodyweight` TINYINT NOT NULL DEFAULT 0 ,
  `distance` TINYINT NOT NULL DEFAULT 0 ,
  `calorie` TINYINT NOT NULL DEFAULT 0 ,
  `json_instructions` VARCHAR(100) NOT NULL DEFAULT '[]' ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  INDEX `fk_library_exercise_library_exercise_level1` (`library_exercise_level_id` ASC) ,
  INDEX `fk_library_exercise_library_body_region1` (`library_body_region_id` ASC) ,
  CONSTRAINT `fk_library_exercise_library_exercise_level1`
    FOREIGN KEY (`library_exercise_level_id` )
    REFERENCES `workoutdb`.`library_exercise_level` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_library_body_region1`
    FOREIGN KEY (`library_body_region_id` )
    REFERENCES `workoutdb`.`library_body_region` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_body_part`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_body_part` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_body_part` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_body_part`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_body_part` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_body_part` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_body_part_id` BIGINT NOT NULL ,
  INDEX `fk_library_exercise_body_region_library_body_region1` (`library_body_part_id` ASC) ,
  INDEX `fk_library_exercise_body_region_library_exercise1` (`library_exercise_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_library_exercise_body_region_library_body_region10`
    FOREIGN KEY (`library_body_part_id` )
    REFERENCES `workoutdb`.`library_body_part` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_body_region_library_exercise10`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_body_region_body_part`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_body_region_body_part` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_body_region_body_part` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_body_region_id` BIGINT NOT NULL ,
  `library_body_part_id` BIGINT NOT NULL ,
  INDEX `fk_library_body_region_body_part_library_body_region1` (`library_body_region_id` ASC) ,
  INDEX `fk_library_body_region_body_part_library_body_part1` (`library_body_part_id` ASC) ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uq_library_body_region_id` (`library_body_region_id` ASC, `library_body_part_id` ASC) ,
  CONSTRAINT `fk_library_body_region_body_part_library_body_region1`
    FOREIGN KEY (`library_body_region_id` )
    REFERENCES `workoutdb`.`library_body_region` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_body_region_body_part_library_body_part1`
    FOREIGN KEY (`library_body_part_id` )
    REFERENCES `workoutdb`.`library_body_part` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_sport_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_sport_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_sport_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_sport_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_sport_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_sport_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_sport_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_exercise_exercise_level_library_exercise1` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exercise_exercise_level_library_exercise_level1` (`library_sport_type_id` ASC) ,
  UNIQUE INDEX `uq_library_exercise_sport_type` (`library_exercise_id` ASC, `library_sport_type_id` ASC) ,
  CONSTRAINT `fk_library_exercise_exercise_level_library_exercise10`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_exercise_level_library_exercise_level10`
    FOREIGN KEY (`library_sport_type_id` )
    REFERENCES `workoutdb`.`library_sport_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_exercise_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_exercise_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_exercise_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_exercise_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_exercise_exercise_level_library_exercise1` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exercise_exercise_level_library_exercise_level1` (`library_exercise_type_id` ASC) ,
  UNIQUE INDEX `uq_library_exercise_exercise_type` (`library_exercise_id` ASC, `library_exercise_type_id` ASC) ,
  CONSTRAINT `fk_library_exercise_exercise_level_library_exercise11`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_exercise_level_library_exercise_level11`
    FOREIGN KEY (`library_exercise_type_id` )
    REFERENCES `workoutdb`.`library_exercise_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  `note` TEXT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_instruction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_instruction` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_instruction` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_exercise_media_id` BIGINT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_exer_inst_lib_exercise1` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exer_inst_lib_exercise_media1` (`library_exercise_media_id` ASC) ,
  CONSTRAINT `fk_library_exer_inst_lib_exercise1`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exer_inst_lib_exercise_media1`
    FOREIGN KEY (`library_exercise_media_id` )
    REFERENCES `workoutdb`.`library_exercise_media` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_exercise_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_exercise_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_exercise_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_exercise_media_id` BIGINT NOT NULL ,
  INDEX `fk_library_exercise_exercise_media_library_exercise1` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exercise_exercise_media_exercise_media1` (`library_exercise_media_id` ASC) ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uq_library_exercise_exercise_media` (`library_exercise_id` ASC, `library_exercise_media_id` ASC) ,
  CONSTRAINT `fk_library_exercise_exercise_media_library_exercise1`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_exercise_media_exercise_media1`
    FOREIGN KEY (`library_exercise_media_id` )
    REFERENCES `workoutdb`.`library_exercise_media` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_equipment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_equipment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_equipment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `abbreviation` VARCHAR(10) NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_exercise_equipment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_equipment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_exercise_equipment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_exercise_id` BIGINT NOT NULL ,
  `library_equipment_id` BIGINT NOT NULL ,
  `manditory` TINYINT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_exercise_equipment_library_exercise1` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exercise_equipment_library_equipment1` (`library_equipment_id` ASC) ,
  UNIQUE INDEX `uq_library_exercise_equipment` (`library_exercise_id` ASC, `library_equipment_id` ASC) ,
  CONSTRAINT `fk_library_exercise_equipment_library_exercise10`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_exercise_equipment_library_equipment1`
    FOREIGN KEY (`library_equipment_id` )
    REFERENCES `workoutdb`.`library_equipment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'the equipment used for a exercise';


-- -----------------------------------------------------
-- Table `workoutdb`.`library_measurement_system`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_measurement_system` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_measurement_system` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_measurement`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_measurement` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_measurement` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_measurement_system_unit`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_measurement_system_unit` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_measurement_system_unit` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_measurement_id` BIGINT NOT NULL ,
  `library_measurement_system_id` BIGINT NOT NULL ,
  `abbr` VARCHAR(10) NULL ,
  `name` VARCHAR(100) NOT NULL DEFAULT '' ,
  `description` TEXT NULL ,
  `english_conversion` DECIMAL(20,10) NOT NULL DEFAULT 1 ,
  `metic_conversion` DECIMAL(20,10) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_measurement_system_units_measurment_system1` (`library_measurement_system_id` ASC) ,
  INDEX `fk_library_measurement_system_units_library_measurement1` (`library_measurement_id` ASC) ,
  CONSTRAINT `fk_measurement_system_units_measurment_system10`
    FOREIGN KEY (`library_measurement_system_id` )
    REFERENCES `workoutdb`.`library_measurement_system` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_measurement_system_units_library_measurement10`
    FOREIGN KEY (`library_measurement_id` )
    REFERENCES `workoutdb`.`library_measurement` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `height` DECIMAL(6,2) NULL ,
  `height_uom_id` BIGINT NULL ,
  `weight` DECIMAL(6,2) NULL ,
  `weight_uom_id` BIGINT NULL ,
  `birthday` INT(13) NULL ,
  `gender` VARCHAR(2) NULL COMMENT 'M or F' ,
  `phone` VARCHAR(10) NULL ,
  `password` VARCHAR(32) NULL ,
  `timezone` VARCHAR(45) NULL ,
  `username` VARCHAR(100) NULL ,
  `first_name` VARCHAR(100) NOT NULL ,
  `last_name` VARCHAR(100) NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `address` VARCHAR(255) NULL ,
  `token` VARCHAR(10) NULL ,
  `token_expire` INT(13) NULL ,
  `send_log_notification` TINYINT NOT NULL DEFAULT 1 ,
  `about_me` TEXT NULL ,
  `last_login` INT(13) NULL ,
  `anonymous_on_leaderboard` TINYINT NULL DEFAULT 0 COMMENT 'Use \"anonymous\" on the leader board for the user\'s name.' ,
  `fb_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  INDEX `fk_user_library_measurement_system_unit1` (`height_uom_id` ASC) ,
  INDEX `fk_user_library_measurement_system_unit2` (`weight_uom_id` ASC) ,
  UNIQUE INDEX `fb_id_UNIQUE` (`fb_id` ASC) ,
  CONSTRAINT `fk_user_library_measurement_system_unit1`
    FOREIGN KEY (`height_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_library_measurement_system_unit2`
    FOREIGN KEY (`weight_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`client_user_role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_role` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client_user_role` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`client_user_emergency_contact`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_emergency_contact` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client_user_emergency_contact` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `created` INT(13) NOT NULL ,
  `reviewed` INT(13) NULL ,
  `phone` VARCHAR(10) NOT NULL ,
  `relation` VARCHAR(45) NULL ,
  `email` VARCHAR(255) NULL ,
  `name` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`getcube_user_master`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`getcube_user_master` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_user_master` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(255) NOT NULL ,
  `password` VARCHAR(45) NOT NULL ,
  `token` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`client`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `widget_token` VARCHAR(100) NOT NULL COMMENT 'This token is used by a web widget to gain access to the system without logging in.' ,
  `getcube_user_master_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  UNIQUE INDEX `widget_token_UNIQUE` (`widget_token` ASC) ,
  INDEX `fk_client_getcube_user_master1` (`getcube_user_master_id` ASC) ,
  CONSTRAINT `fk_client_getcube_user_master1`
    FOREIGN KEY (`getcube_user_master_id` )
    REFERENCES `workoutdb`.`getcube_user_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`location` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`location` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `client_user_id` BIGINT NULL COMMENT 'The gym\'s contact person' ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `phone` VARCHAR(45) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `timezone` VARCHAR(100) NULL ,
  `email` VARCHAR(255) NULL ,
  `address` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_location_client1` (`client_id` ASC) ,
  INDEX `fk_location_member1` (`client_user_id` ASC) ,
  CONSTRAINT `fk_location_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_location_member1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`affiliation_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`affiliation_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`affiliation_type` (
  `id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `affiliation_typecol_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'Staff, Member, Guest, Archive';


-- -----------------------------------------------------
-- Table `workoutdb`.`client_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_user_role_id` BIGINT NOT NULL ,
  `location_id` BIGINT NULL ,
  `client_user_emergency_contact_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `note` TEXT NULL ,
  `affiliation_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_membership_membership_type1` (`client_user_role_id` ASC) ,
  INDEX `fk_member_emergency_contact1` (`client_user_emergency_contact_id` ASC) ,
  INDEX `fk_client_user_location1` (`location_id` ASC) ,
  INDEX `fk_client_user_affiliation_type1` (`affiliation_type_id` ASC) ,
  CONSTRAINT `fk_membership_membership_type1`
    FOREIGN KEY (`client_user_role_id` )
    REFERENCES `workoutdb`.`client_user_role` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_member_emergency_contact1`
    FOREIGN KEY (`client_user_emergency_contact_id` )
    REFERENCES `workoutdb`.`client_user_emergency_contact` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_client_user_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_client_user_affiliation_type1`
    FOREIGN KEY (`affiliation_type_id` )
    REFERENCES `workoutdb`.`affiliation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`classroom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`classroom` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`classroom` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `location_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_classroom_location1` (`location_id` ASC) ,
  CONSTRAINT `fk_classroom_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user_goal_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user_goal_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user_goal_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user_progress_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user_progress_log` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user_progress_log` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `user_goal_type_id` BIGINT NULL ,
  `height` DECIMAL(6,2) NULL ,
  `height_uom_id` BIGINT NULL ,
  `weight` DECIMAL(6,2) NULL ,
  `weight_uom_id` BIGINT NULL ,
  `percent_fat` DECIMAL(6,2) NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_progress_log_user1` (`user_id` ASC) ,
  INDEX `fk_user_progress_log_user_goal_type1` (`user_goal_type_id` ASC) ,
  INDEX `fk_user_progress_log_library_measurement_system_unit1` (`height_uom_id` ASC) ,
  INDEX `fk_user_progress_log_library_measurement_system_unit2` (`weight_uom_id` ASC) ,
  CONSTRAINT `fk_user_progress_log_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_progress_log_user_goal_type1`
    FOREIGN KEY (`user_goal_type_id` )
    REFERENCES `workoutdb`.`user_goal_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_progress_log_library_measurement_system_unit1`
    FOREIGN KEY (`height_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_progress_log_library_measurement_system_unit2`
    FOREIGN KEY (`weight_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_progress_log_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_media_user_progress_log1` (`user_progress_log_id` ASC) ,
  CONSTRAINT `fk_user_media_user_progress_log1`
    FOREIGN KEY (`user_progress_log_id` )
    REFERENCES `workoutdb`.`user_progress_log` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_workout_recording_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_workout_recording_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_workout_recording_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_workout`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_workout` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_workout` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `library_workout_recording_type_id` BIGINT NULL ,
  `benchmark` TINYINT NOT NULL DEFAULT 0 ,
  `name` VARCHAR(100) NOT NULL ,
  `json_workout` TEXT NULL ,
  `json_workout_summary` TEXT NULL ,
  `json_workout_summary_male` TEXT NULL ,
  `json_workout_summary_female` TEXT NULL ,
  `original_json_workout` TEXT NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_workout_library_workout_recording_type1` (`library_workout_recording_type_id` ASC) ,
  UNIQUE INDEX `uq_library_workout_name_unique` (`name` ASC) ,
  INDEX `fk_library_workout_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_library_workout_library_workout_recording_type1`
    FOREIGN KEY (`library_workout_recording_type_id` )
    REFERENCES `workoutdb`.`library_workout_recording_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_workout_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_workout_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_workout_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_workout_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_workout_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `stored_locally` TINYINT NOT NULL DEFAULT 0 ,
  `media_url` TEXT NOT NULL ,
  `note` TEXT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_workout_media_library_workout1` (`library_workout_id` ASC) ,
  CONSTRAINT `fk_library_workout_media_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_equipment_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_equipment_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_equipment_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_equipment_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  `description` TEXT NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_equipment_media_library_equipment1` (`library_equipment_id` ASC) ,
  CONSTRAINT `fk_library_equipment_media_library_equipment100`
    FOREIGN KEY (`library_equipment_id` )
    REFERENCES `workoutdb`.`library_equipment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_equipment_measurement`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_equipment_measurement` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_equipment_measurement` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_equipment_id` BIGINT NOT NULL ,
  `library_measurement_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_equipment_measurement_library_measurement1` (`library_measurement_id` ASC) ,
  INDEX `fk_library_equipment_measurement_library_equipment1` (`library_equipment_id` ASC) ,
  UNIQUE INDEX `uq_library_equipment_measurement` (`library_equipment_id` ASC, `library_measurement_id` ASC) ,
  CONSTRAINT `fk_library_equipment_measurement_library_measurement10`
    FOREIGN KEY (`library_measurement_id` )
    REFERENCES `workoutdb`.`library_measurement` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_equipment_measurement_library_equipment1`
    FOREIGN KEY (`library_equipment_id` )
    REFERENCES `workoutdb`.`library_equipment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NULL ,
  `location_id` BIGINT NULL ,
  `classroom_id` BIGINT NULL ,
  `user_id` BIGINT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `timezone` VARCHAR(100) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `calendar_location_fk1` (`location_id` ASC) ,
  INDEX `fk_calendar_client1` (`client_id` ASC) ,
  INDEX `fk_calendar_classroom1` (`classroom_id` ASC) ,
  INDEX `fk_calendar_user1` (`user_id` ASC) ,
  CONSTRAINT `calendar_location_fk100`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_client10`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_classroom10`
    FOREIGN KEY (`classroom_id` )
    REFERENCES `workoutdb`.`classroom` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry_repeat_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry_repeat_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry_repeat_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user_profile_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user_profile_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user_profile_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_image_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_image_user10`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'class, workshop, apointment, one-on-one';


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry_template` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry_template` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_entry_type_id` BIGINT NOT NULL ,
  `client_id` BIGINT NOT NULL ,
  `log_participant` TINYINT NOT NULL ,
  `wod` TINYINT NOT NULL ,
  `log_result` TINYINT NOT NULL ,
  `rsvp` TINYINT NOT NULL ,
  `waiver` TINYINT NOT NULL ,
  `payment` TINYINT NOT NULL ,
  `all_day` TINYINT NULL ,
  `duration` INT(13) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_entry_detail_calendar_entry_detail_type1` (`calendar_entry_type_id` ASC) ,
  INDEX `fk_calendar_entry_template_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_calendar_entry_detail_calendar_entry_detail_type10`
    FOREIGN KEY (`calendar_entry_type_id` )
    REFERENCES `workoutdb`.`calendar_entry_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_template_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_id` BIGINT NOT NULL ,
  `calendar_entry_type_id` BIGINT NOT NULL ,
  `calendar_entry_repeat_type_id` BIGINT NOT NULL ,
  `calendar_entry_template_id` BIGINT NULL ,
  `log_participant` TINYINT NOT NULL ,
  `wod` TINYINT NOT NULL ,
  `log_result` TINYINT NOT NULL ,
  `rsvp` TINYINT NOT NULL ,
  `waiver` TINYINT NOT NULL ,
  `payment` TINYINT NOT NULL ,
  `all_day` TINYINT NULL ,
  `duration` INT(13) NULL ,
  `start` INT(13) NOT NULL ,
  `end` INT(13) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  `location` TEXT NULL ,
  `removed_dates` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_entry_calendar_entry_type1` (`calendar_entry_type_id` ASC) ,
  INDEX `fk_calendar_entry_calendar1` (`calendar_id` ASC) ,
  INDEX `fk_calendar_entry_calendar_entry_repeat_type1` (`calendar_entry_repeat_type_id` ASC) ,
  INDEX `fk_calendar_entry_calendar_entry_template1` (`calendar_entry_template_id` ASC) ,
  CONSTRAINT `fk_calendar_entry_detail_calendar_entry_detail_type100`
    FOREIGN KEY (`calendar_entry_type_id` )
    REFERENCES `workoutdb`.`calendar_entry_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_copy1_calendar1`
    FOREIGN KEY (`calendar_id` )
    REFERENCES `workoutdb`.`calendar` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_copy1_calendar_entry_repeat_type1`
    FOREIGN KEY (`calendar_entry_repeat_type_id` )
    REFERENCES `workoutdb`.`calendar_entry_repeat_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_copy1_calendar_entry_detail1`
    FOREIGN KEY (`calendar_entry_template_id` )
    REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_event` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_event` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_id` BIGINT NOT NULL ,
  `calendar_entry_template_id` BIGINT NULL ,
  `calendar_entry_id` BIGINT NOT NULL ,
  `start` INT(13) NOT NULL ,
  `log_participant` TINYINT NOT NULL ,
  `wod` TINYINT NOT NULL ,
  `log_result` TINYINT NOT NULL ,
  `rsvp` TINYINT NOT NULL ,
  `waiver` TINYINT NOT NULL ,
  `payment` TINYINT NOT NULL ,
  `all_day` TINYINT NULL ,
  `duration` INT(13) NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  `location` TEXT NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_entry_calendar1` (`calendar_id` ASC) ,
  INDEX `fk_calendar_event_calendar_entry1` (`calendar_entry_id` ASC) ,
  UNIQUE INDEX `un_clendar_event_start1` (`start` ASC, `calendar_entry_id` ASC) ,
  INDEX `fk_calendar_event_calendar_entry_template1` (`calendar_entry_template_id` ASC) ,
  INDEX `ix_calendar_event_start` (`start` ASC) ,
  CONSTRAINT `fk_calendar_entry_copy1_calendar10`
    FOREIGN KEY (`calendar_id` )
    REFERENCES `workoutdb`.`calendar` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_calendar_entry1`
    FOREIGN KEY (`calendar_entry_id` )
    REFERENCES `workoutdb`.`calendar_entry` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_calendar_entry_template1`
    FOREIGN KEY (`calendar_entry_template_id` )
    REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`emotional_level`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`emotional_level` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`emotional_level` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_event_participation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_event_participation` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_event_participation` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_user_id` BIGINT NOT NULL ,
  `calendar_event_id` BIGINT NOT NULL ,
  `email_reminder_sent` VARCHAR(13) NULL ,
  `email_reminder_opened` VARCHAR(13) NULL ,
  `start_emotional_level_id` BIGINT NULL ,
  `end_emotional_level_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `created_by_app` VARCHAR(20) NULL ,
  `created_by_user_id` BIGINT NULL ,
  `note` TEXT NULL ,
  INDEX `fk_calendar_event_participation_client_user1` (`client_user_id` ASC) ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_event_participation_calendar_event1` (`calendar_event_id` ASC) ,
  INDEX `fk_calendar_event_participation_emotional_level1` (`start_emotional_level_id` ASC) ,
  INDEX `fk_calendar_event_participation_emotional_level2` (`end_emotional_level_id` ASC) ,
  INDEX `uq_calendar_event_participation` (`client_user_id` ASC, `calendar_event_id` ASC) ,
  INDEX `fk_calendar_event_participation_user1` (`created_by_user_id` ASC) ,
  CONSTRAINT `fk_calendar_event_participation_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_participation_calendar_event1`
    FOREIGN KEY (`calendar_event_id` )
    REFERENCES `workoutdb`.`calendar_event` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_participation_emotional_level1`
    FOREIGN KEY (`start_emotional_level_id` )
    REFERENCES `workoutdb`.`emotional_level` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_participation_emotional_level2`
    FOREIGN KEY (`end_emotional_level_id` )
    REFERENCES `workoutdb`.`emotional_level` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_participation_user1`
    FOREIGN KEY (`created_by_user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'who participated in a calendar event';


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `library_workout_id` BIGINT NULL ,
  `calendar_event_participation_id` BIGINT NULL ,
  `library_workout_recording_type_id` BIGINT NULL ,
  `workout_modified` TINYINT NOT NULL COMMENT 'was the workout performed as perscribed.' ,
  `workout_log_completed` TINYINT NOT NULL COMMENT 'has the workout logging process beem complete?' ,
  `result` DECIMAL(10,2) NULL ,
  `result_uom_id` BIGINT NULL ,
  `time_limit` INT(13) NULL ,
  `time_limit_uom_id` BIGINT NULL ,
  `start` INT(13) NOT NULL COMMENT 'The start date time of the workout that the log is for' ,
  `height` DECIMAL(6,2) NULL COMMENT 'The height of the user at the time  the workout_log is create.' ,
  `height_uom_id` BIGINT NULL ,
  `weight` DECIMAL(6,2) NULL COMMENT 'The weight of the user at the time  the workout_log is create.' ,
  `weight_uom_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `time_limit_note` TEXT NULL ,
  `json_log` TEXT NULL COMMENT 'The log of a user\'s participation workout results' ,
  `json_log_summary` TEXT NULL ,
  `note` TEXT NULL ,
  `original_json_log` TEXT NULL ,
  `auto_calculated_result` TINYINT NULL DEFAULT 0 COMMENT 'Were the results auto-calculated yes (1) or no (0).' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_participation_log_library_workout1` (`library_workout_id` ASC) ,
  INDEX `fk_workout_log_user1` (`user_id` ASC) ,
  INDEX `fk_workout_log_library_workout_recording_type1` (`library_workout_recording_type_id` ASC) ,
  INDEX `fk_workout_log_calendar_event_participation1` (`calendar_event_participation_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit1` (`result_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit2` (`time_limit_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit3` (`weight_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit4` (`height_uom_id` ASC) ,
  CONSTRAINT `fk_participation_log_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_workout_recording_type1`
    FOREIGN KEY (`library_workout_recording_type_id` )
    REFERENCES `workoutdb`.`library_workout_recording_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_calendar_event_participation1`
    FOREIGN KEY (`calendar_event_participation_id` )
    REFERENCES `workoutdb`.`calendar_event_participation` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_measurement_system_unit1`
    FOREIGN KEY (`result_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_measurement_system_unit2`
    FOREIGN KEY (`time_limit_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_measurement_system_unit3`
    FOREIGN KEY (`weight_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_measurement_system_unit4`
    FOREIGN KEY (`height_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`classroom_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`classroom_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`classroom_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `classroom_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_classroom_media_classroom1` (`classroom_id` ASC) ,
  CONSTRAINT `fk_classroom_media_classroom1`
    FOREIGN KEY (`classroom_id` )
    REFERENCES `workoutdb`.`classroom` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`ci_sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`ci_sessions` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ci_sessions` (
  `session_id` VARCHAR(40) NOT NULL DEFAULT 0 ,
  `ip_address` VARCHAR(16) NOT NULL DEFAULT 0 ,
  `user_agent` VARCHAR(50) NOT NULL ,
  `last_activity` INT(10) NOT NULL DEFAULT 0 ,
  `user_data` TEXT NOT NULL ,
  PRIMARY KEY (`session_id`) )
ENGINE = InnoDB
COMMENT = 'used by codeigniter to log session data';


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log_calculation_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log_calculation_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log_calculation_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log_calculation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log_calculation` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log_calculation` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `workout_log_id` BIGINT NOT NULL ,
  `workout_log_calculation_type_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `json_step1` TEXT NULL ,
  `json_step2` TEXT NULL ,
  `json_results` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_workout_log_calculation_calculation_type1` (`workout_log_calculation_type_id` ASC) ,
  INDEX `fk_workout_log_calculation_workout_log1` (`workout_log_id` ASC) ,
  CONSTRAINT `fk_workout_log_calculation_calculation_type1`
    FOREIGN KEY (`workout_log_calculation_type_id` )
    REFERENCES `workoutdb`.`workout_log_calculation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_calculation_workout_log1`
    FOREIGN KEY (`workout_log_id` )
    REFERENCES `workoutdb`.`workout_log` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log_library_exercise`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log_library_exercise` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log_library_exercise` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `workout_log_id` BIGINT NOT NULL ,
  `library_exercise_id` BIGINT NOT NULL ,
  INDEX `fk_table1_workout_log1` (`workout_log_id` ASC) ,
  INDEX `fk_table1_library_exercise1` (`library_exercise_id` ASC) ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uq_workout_log_library_exercise` (`workout_log_id` ASC, `library_exercise_id` ASC) ,
  CONSTRAINT `fk_table1_workout_log1`
    FOREIGN KEY (`workout_log_id` )
    REFERENCES `workoutdb`.`workout_log` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_library_exercise1`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'crossreference of exercises in the workout log';


-- -----------------------------------------------------
-- Table `workoutdb`.`email_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_template` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_template` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `media_url` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'The templates used to created email batches';


-- -----------------------------------------------------
-- Table `workoutdb`.`email_batch`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_batch` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_batch` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `email_template_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `subject` VARCHAR(255) NOT NULL ,
  `content` TEXT NOT NULL COMMENT 'the file name of the template definition file.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_email_batch_user1` (`user_id` ASC) ,
  INDEX `fk_email_batch_email_template1` (`email_template_id` ASC) ,
  CONSTRAINT `fk_email_batch_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_batch_email_template1`
    FOREIGN KEY (`email_template_id` )
    REFERENCES `workoutdb`.`email_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`email_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_log` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_log` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `status` VARCHAR(10) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `json_email` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`email`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `sending_user_id` BIGINT NOT NULL ,
  `receiving_user_id` BIGINT NOT NULL ,
  `email_batch_id` BIGINT NOT NULL ,
  `email_log_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `reviewed` INT(13) NULL ,
  `sent` VARCHAR(45) NULL ,
  `sending_name` VARCHAR(200) NOT NULL ,
  `receiving_name` VARCHAR(200) NOT NULL ,
  `sending_email` VARCHAR(255) NOT NULL ,
  `receiving_email` VARCHAR(255) NOT NULL ,
  `subject` VARCHAR(255) NOT NULL ,
  `media_url` TEXT NOT NULL COMMENT 'The filename that is used to store the email.' ,
  PRIMARY KEY (`id`) ,
  INDEX `email_subject_idx` (`subject` ASC) ,
  INDEX `email_sent_idx` (`created` ASC) ,
  INDEX `fk_email_user1` (`sending_user_id` ASC) ,
  INDEX `fk_email_user2` (`receiving_user_id` ASC) ,
  INDEX `email_sending_name_idx` (`sending_name` ASC) ,
  INDEX `email_sending_email_idx` (`sending_email` ASC) ,
  INDEX `email_receiving_name_idx` (`receiving_name` ASC) ,
  INDEX `email_receiving_email_idx` (`receiving_email` ASC) ,
  INDEX `fk_email_email_batch1` (`email_batch_id` ASC) ,
  INDEX `fk_email_email_log1` (`email_log_id` ASC) ,
  CONSTRAINT `fk_email_user1`
    FOREIGN KEY (`sending_user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_user2`
    FOREIGN KEY (`receiving_user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_email_batch1`
    FOREIGN KEY (`email_batch_id` )
    REFERENCES `workoutdb`.`email_batch` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_email_log1`
    FOREIGN KEY (`email_log_id` )
    REFERENCES `workoutdb`.`email_log` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Email History log.  The actual email is stored in a file.';


-- -----------------------------------------------------
-- Table `workoutdb`.`email_batch_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_batch_user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_batch_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `email_batch_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_email_batch_user_user1` (`user_id` ASC) ,
  INDEX `fk_email_batch_user_email_batch1` (`email_batch_id` ASC) ,
  UNIQUE INDEX `uq_email_batch_user` (`user_id` ASC, `email_batch_id` ASC) ,
  CONSTRAINT `fk_email_batch_user_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_batch_user_email_batch1`
    FOREIGN KEY (`email_batch_id` )
    REFERENCES `workoutdb`.`email_batch` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'The list of users to be sent the email in this batch';


-- -----------------------------------------------------
-- Table `workoutdb`.`email_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_tag` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_tag` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`email_tag_email`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_tag_email` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_tag_email` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email_id` BIGINT NOT NULL ,
  `email_tag_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_email_tag_email_email1` (`email_id` ASC) ,
  INDEX `fk_email_tag_email_email_tag1` (`email_tag_id` ASC) ,
  UNIQUE INDEX `uq_email_tag_email` (`email_id` ASC, `email_tag_id` ASC) ,
  CONSTRAINT `fk_email_tag_email_email1`
    FOREIGN KEY (`email_id` )
    REFERENCES `workoutdb`.`email` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_tag_email_email_tag1`
    FOREIGN KEY (`email_tag_id` )
    REFERENCES `workoutdb`.`email_tag` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`email_tag_email_batch`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_tag_email_batch` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_tag_email_batch` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email_batch_id` BIGINT NOT NULL ,
  `email_tag_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_email_tag_email_batch_email_batch1` (`email_batch_id` ASC) ,
  INDEX `fk_email_tag_email_batch_email_tag1` (`email_tag_id` ASC) ,
  UNIQUE INDEX `uq_email_tag_email_batch` (`email_batch_id` ASC, `email_tag_id` ASC) ,
  CONSTRAINT `fk_email_tag_email_batch_email_batch1`
    FOREIGN KEY (`email_batch_id` )
    REFERENCES `workoutdb`.`email_batch` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_email_tag_email_batch_email_tag1`
    FOREIGN KEY (`email_tag_id` )
    REFERENCES `workoutdb`.`email_tag` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`email_event_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`email_event_history` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`email_event_history` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_email_event_history_email1` (`email_id` ASC) ,
  CONSTRAINT `fk_email_event_history_email1`
    FOREIGN KEY (`email_id` )
    REFERENCES `workoutdb`.`email` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'sent back from CritSend';


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_event_library_workout`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_event_library_workout` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_event_library_workout` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_workout_id` BIGINT NOT NULL ,
  `calendar_event_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_event_library_workout_library_workout1` (`library_workout_id` ASC) ,
  INDEX `fk_calendar_event_library_workout_calendar_event1` (`calendar_event_id` ASC) ,
  UNIQUE INDEX `uq_calendar_event_library_workout` (`library_workout_id` ASC, `calendar_event_id` ASC) ,
  CONSTRAINT `fk_calendar_event_library_workout_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_event_library_workout_calendar_event1`
    FOREIGN KEY (`calendar_event_id` )
    REFERENCES `workoutdb`.`calendar_event` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'cross reference table between calendar_event and library_workout';


-- -----------------------------------------------------
-- Table `workoutdb`.`location_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`location_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`location_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `location_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_location_media_location1` (`location_id` ASC) ,
  CONSTRAINT `fk_location_media_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`client_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_client_media_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_client_media_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_media` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_media` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_id` BIGINT NOT NULL ,
  `date` INT(13) NULL COMMENT 'the date that the media is for.  if null, it is for the calendar.' ,
  `created` INT(13) NOT NULL ,
  `media_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_media_calendar1` (`calendar_id` ASC) ,
  CONSTRAINT `fk_calendar_media_calendar1`
    FOREIGN KEY (`calendar_id` )
    REFERENCES `workoutdb`.`calendar` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry_template_wod`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry_template_wod` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry_template_wod` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_entry_template_id` BIGINT NOT NULL ,
  `yyyymmdd` VARCHAR(10) NOT NULL ,
  `client_id` BIGINT NOT NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_entry_template_wod_calendar_entry_template1` (`calendar_entry_template_id` ASC) ,
  UNIQUE INDEX `ix_calendar_entry_template_wod1` (`calendar_entry_template_id` ASC, `yyyymmdd` ASC) ,
  INDEX `fk_calendar_entry_template_wod_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_calendar_entry_template_wod_calendar_entry_template1`
    FOREIGN KEY (`calendar_entry_template_id` )
    REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_template_wod_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`calendar_entry_template_wod_library_workout`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`calendar_entry_template_wod_library_workout` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`calendar_entry_template_wod_library_workout` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_entry_template_wod_id` BIGINT NOT NULL ,
  `library_workout_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_entry_template_wod_library_workout_calendar_entry1` (`calendar_entry_template_wod_id` ASC) ,
  INDEX `fk_calendar_entry_template_wod_library_workout_library_workout1` (`library_workout_id` ASC) ,
  UNIQUE INDEX `uq_calendar_entry_template_wod_library_workout` (`calendar_entry_template_wod_id` ASC, `library_workout_id` ASC) ,
  CONSTRAINT `fk_calendar_entry_template_wod_library_workout_calendar_entry1`
    FOREIGN KEY (`calendar_entry_template_wod_id` )
    REFERENCES `workoutdb`.`calendar_entry_template_wod` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_calendar_entry_template_wod_library_workout_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_workout_library_exercise`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_workout_library_exercise` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_workout_library_exercise` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_workout_id` BIGINT NOT NULL ,
  `library_exercise_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_workout_library_exercise_library_workout1` (`library_workout_id` ASC) ,
  INDEX `fk_library_workout_library_exercise_library_exercise1` (`library_exercise_id` ASC) ,
  UNIQUE INDEX `uq_library_workout_library_exercise` (`library_workout_id` ASC, `library_exercise_id` ASC) ,
  CONSTRAINT `fk_library_workout_library_exercise_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_workout_library_exercise_library_exercise1`
    FOREIGN KEY (`library_exercise_id` )
    REFERENCES `workoutdb`.`library_exercise` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`library_workout_library_equipment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`library_workout_library_equipment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`library_workout_library_equipment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `library_workout_id` BIGINT NOT NULL ,
  `library_equipment_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_library_workout_library_equipment_library_workout1` (`library_workout_id` ASC) ,
  INDEX `fk_library_workout_library_equipment_library_equipment1` (`library_equipment_id` ASC) ,
  UNIQUE INDEX `uq_library_workout_library_equipment` (`library_workout_id` ASC, `library_equipment_id` ASC) ,
  CONSTRAINT `fk_library_workout_library_equipment_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_library_workout_library_equipment_library_equipment1`
    FOREIGN KEY (`library_equipment_id` )
    REFERENCES `workoutdb`.`library_equipment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`provisional_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`provisional_user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`provisional_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `client_user_role_id` BIGINT NOT NULL ,
  `location_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `token_expire` INT(13) NOT NULL ,
  `height` DECIMAL(6,2) NULL ,
  `height_uom_id` BIGINT NULL ,
  `weight` DECIMAL(6,2) NULL ,
  `weight_uom_id` BIGINT NULL ,
  `birthday` INT(13) NOT NULL ,
  `gender` VARCHAR(2) NOT NULL COMMENT 'M or F' ,
  `phone` VARCHAR(10) NULL ,
  `password` VARCHAR(32) NULL ,
  `timezone` VARCHAR(45) NOT NULL ,
  `token` VARCHAR(45) NOT NULL COMMENT 'if not null, the user goes to the activation screen instead of login screen.' ,
  `first_name` VARCHAR(100) NOT NULL ,
  `last_name` VARCHAR(100) NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `address` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `phone_UNIQUE` (`phone` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  INDEX `fk_user_library_measurement_system_unit1` (`height_uom_id` ASC) ,
  INDEX `fk_user_library_measurement_system_unit2` (`weight_uom_id` ASC) ,
  INDEX `fk_provisional_user_client1` (`client_id` ASC) ,
  INDEX `fk_provisional_user_location1` (`location_id` ASC) ,
  INDEX `fk_provisional_user_client_user_role1` (`client_user_role_id` ASC) ,
  CONSTRAINT `fk_user_library_measurement_system_unit10`
    FOREIGN KEY (`height_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_library_measurement_system_unit20`
    FOREIGN KEY (`weight_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_provisional_user_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_provisional_user_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_provisional_user_client_user_role1`
    FOREIGN KEY (`client_user_role_id` )
    REFERENCES `workoutdb`.`client_user_role` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log_pending`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log_pending` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log_pending` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `calendar_event_participation_id` BIGINT NOT NULL ,
  `library_workout_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_pending_workout_log_calendar_event_participation1` (`calendar_event_participation_id` ASC) ,
  INDEX `fk_pending_workout_log_library_workout1` (`library_workout_id` ASC) ,
  CONSTRAINT `fk_pending_workout_log_calendar_event_participation1`
    FOREIGN KEY (`calendar_event_participation_id` )
    REFERENCES `workoutdb`.`calendar_event_participation` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pending_workout_log_library_workout1`
    FOREIGN KEY (`library_workout_id` )
    REFERENCES `workoutdb`.`library_workout` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`workout_log_library_equipment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_log_library_equipment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`workout_log_library_equipment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `workout_log_id` BIGINT NOT NULL ,
  `library_equipment_id` BIGINT NOT NULL ,
  INDEX `fk_workout_log_library_equipment_workout_log1` (`workout_log_id` ASC) ,
  INDEX `fk_workout_log_library_equipment_library_equipment1` (`library_equipment_id` ASC) ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `uq_workout_log_library_equipment` (`workout_log_id` ASC, `library_equipment_id` ASC) ,
  CONSTRAINT `fk_workout_log_library_equipment_workout_log1`
    FOREIGN KEY (`workout_log_id` )
    REFERENCES `workoutdb`.`workout_log` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_log_library_equipment_library_equipment1`
    FOREIGN KEY (`library_equipment_id` )
    REFERENCES `workoutdb`.`library_equipment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`user_notification_queue`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`user_notification_queue` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`user_notification_queue` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `timestamp` INT(13) NOT NULL COMMENT 'UTC timestamp of latest activity to notify for.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_notification_queue_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_notification_queue_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This is the queue used to notify users that they need to complete some system process. (Workout logging, Profile data . . .)';


-- -----------------------------------------------------
-- Table `workoutdb`.`competition_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`competition_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'individual or group';


-- -----------------------------------------------------
-- Table `workoutdb`.`registration_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`registration_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`registration_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Online, coach, ...';


-- -----------------------------------------------------
-- Table `workoutdb`.`competition`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`competition` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `client_id` BIGINT NOT NULL COMMENT 'What Client is sponsoring the competition.' ,
  `user_id` BIGINT NULL COMMENT 'Who is the contact for the competition.' ,
  `competition_type_id` INT NOT NULL ,
  `registration_type_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `description` TEXT NULL ,
  `closed_competition` TINYINT NULL ,
  `team_size_min` INT NULL ,
  `team_size_max` INT NULL ,
  `registration_start` INT(13) NULL ,
  `registration_end` INT(13) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_competition_client1` (`client_id` ASC) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  UNIQUE INDEX `un_client_user_name` (`client_id` ASC, `name` ASC) ,
  INDEX `fk_competition_user1` (`user_id` ASC) ,
  INDEX `fk_competition_competition_type1` (`competition_type_id` ASC) ,
  INDEX `fk_competition_registration_type1` (`registration_type_id` ASC) ,
  CONSTRAINT `fk_competition_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_competition_type1`
    FOREIGN KEY (`competition_type_id` )
    REFERENCES `workoutdb`.`competition_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_regestration_type1`
    FOREIGN KEY (`registration_type_id` )
    REFERENCES `workoutdb`.`registration_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`competition_individual`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`competition_individual` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_individual` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT NOT NULL ,
  `user_id` BIGINT NOT NULL ,
  `competition_group_id` BIGINT NULL ,
  `height` DECIMAL(8,2) NULL ,
  `height_uom_id` BIGINT NULL ,
  `weight` DECIMAL(8,2) NULL ,
  `weight_uom_id` BIGINT NULL ,
  `birthday` INT(13) NULL ,
  `alias_name` VARCHAR(100) NULL COMMENT 'if this field has a value, it will be used for the inividual\'s name.' ,
  `affiliated_gym` VARCHAR(100) NULL COMMENT 'used if the competitor is not affiliated with a gym.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_competition_individual1` (`user_id` ASC) ,
  UNIQUE INDEX `unique_competition_individual_compitition_user` (`competition_id` ASC, `user_id` ASC) ,
  INDEX `fk_competition_individual_competition1` (`competition_id` ASC) ,
  INDEX `fk_competition_individual_competition_group1` (`competition_group_id` ASC) ,
  INDEX `fk_competition_individual_library_measurement_system_unit1` (`height_uom_id` ASC) ,
  INDEX `fk_competition_individual_library_measurement_system_unit2` (`weight_uom_id` ASC) ,
  CONSTRAINT `fk_competitor_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_user_competition1`
    FOREIGN KEY (`competition_id` )
    REFERENCES `workoutdb`.`competition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_user_competition_group1`
    FOREIGN KEY (`competition_group_id` )
    REFERENCES `workoutdb`.`competition_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_individual_library_measurement_system_unit1`
    FOREIGN KEY (`height_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_individual_library_measurement_system_unit2`
    FOREIGN KEY (`weight_uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`competition_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`competition_group` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_group` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `captain_id` BIGINT NULL COMMENT 'if this competion is set for online registration, the group captain is the person in the group allowed to delete people from the group during the registration period.' ,
  `affiliated_gym` VARCHAR(100) NULL COMMENT 'Used to overwrite the list of gyms the competitors belong too.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_competition_group_competition1` (`competition_id` ASC) ,
  INDEX `fk_competition_group_competition_individual1` (`captain_id` ASC) ,
  UNIQUE INDEX `unique_competition_group_competition_name` (`competition_id` ASC, `name` ASC) ,
  CONSTRAINT `fk_competition_group_competition1`
    FOREIGN KEY (`competition_id` )
    REFERENCES `workoutdb`.`competition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_group_competition_individual1`
    FOREIGN KEY (`captain_id` )
    REFERENCES `workoutdb`.`competition_individual` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`score_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`score_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`score_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Checkins, Days checked in, Workouts logged, Log results, Power, Load ...';


-- -----------------------------------------------------
-- Table `workoutdb`.`score_calculation_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`score_calculation_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`score_calculation_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'total, average, best of ##';


-- -----------------------------------------------------
-- Table `workoutdb`.`phase`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`phase` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_phase_competition1` (`competition_id` ASC) ,
  UNIQUE INDEX `unique_phase_competion_phase_name` (`competition_id` ASC, `name` ASC) ,
  CONSTRAINT `fk_phase_competition1`
    FOREIGN KEY (`competition_id` )
    REFERENCES `workoutdb`.`competition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`point_awarding_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`point_awarding_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`point_awarding_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`challenge`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`challenge` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `score_type_id` BIGINT NOT NULL ,
  `score_calculation_type_id` BIGINT NOT NULL ,
  `point_awarding_type_id` BIGINT NOT NULL ,
  `start` INT(13) NULL ,
  `end` INT(13) NULL ,
  `max_team_size` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_challenge_score_recording_type1` (`score_type_id` ASC) ,
  INDEX `fk_challenge_challenge_score_calculation_type1` (`score_calculation_type_id` ASC) ,
  INDEX `fk_challenge_phase1` (`phase_id` ASC) ,
  INDEX `fk_challenge_point_awarding_type1` (`point_awarding_type_id` ASC) ,
  UNIQUE INDEX `unique_challenge_phase_name` (`phase_id` ASC, `name` ASC) ,
  CONSTRAINT `fk_challenge_challenge_score_recording_type1`
    FOREIGN KEY (`score_type_id` )
    REFERENCES `workoutdb`.`score_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_challenge_score_calculation_type1`
    FOREIGN KEY (`score_calculation_type_id` )
    REFERENCES `workoutdb`.`score_calculation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_phase1`
    FOREIGN KEY (`phase_id` )
    REFERENCES `workoutdb`.`phase` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_point_awarding_type1`
    FOREIGN KEY (`point_awarding_type_id` )
    REFERENCES `workoutdb`.`point_awarding_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`invoice_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`invoice_item` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`invoice_item` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `invoiice_id` BIGINT NOT NULL ,
  `invoice_item_description` VARCHAR(100) NOT NULL ,
  `count` INT NOT NULL ,
  `unit_price` DECIMAL(8,2) NOT NULL ,
  `total_price` DECIMAL(8,2) NOT NULL ,
  `tax_item` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`gateway_account_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`gateway_account_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`gateway_account_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'creditCard, checking, savings, or businessChecking';


-- -----------------------------------------------------
-- Table `workoutdb`.`phase_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`phase_group` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_group` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT NOT NULL ,
  `competition_group_id` BIGINT NOT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_phase_group_phase1` (`phase_id` ASC) ,
  INDEX `fk_phase_group_competition_group1` (`competition_group_id` ASC) ,
  UNIQUE INDEX `un_phase_group_phase_competition_group` (`phase_id` ASC, `competition_group_id` ASC) ,
  INDEX `fk_phase_group_library_measurement_system_unit1` (`uom_id` ASC) ,
  CONSTRAINT `fk_phase_group_phase1`
    FOREIGN KEY (`phase_id` )
    REFERENCES `workoutdb`.`phase` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_phase_group_competition_group1`
    FOREIGN KEY (`competition_group_id` )
    REFERENCES `workoutdb`.`competition_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_phase_group_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`phase_individual`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`phase_individual` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_individual` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT NOT NULL ,
  `competition_individual_id` BIGINT NOT NULL ,
  `phase_group_id` BIGINT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_phase_user_phase1` (`phase_id` ASC) ,
  INDEX `fk_phase_user_competition_user1` (`competition_individual_id` ASC) ,
  INDEX `fk_phase_user_phase_group1` (`phase_group_id` ASC) ,
  UNIQUE INDEX `un_phase_individual_phase_competition_individual` (`phase_id` ASC, `competition_individual_id` ASC) ,
  INDEX `fk_phase_individual_library_measurement_system_unit1` (`uom_id` ASC) ,
  CONSTRAINT `fk_phase_user_phase1`
    FOREIGN KEY (`phase_id` )
    REFERENCES `workoutdb`.`phase` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_phase_user_competition_user1`
    FOREIGN KEY (`competition_individual_id` )
    REFERENCES `workoutdb`.`competition_individual` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_phase_user_phase_group1`
    FOREIGN KEY (`phase_group_id` )
    REFERENCES `workoutdb`.`phase_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_phase_individual_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`ranking_attribute_by_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`ranking_attribute_by_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute_by_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Rank attribute by attribute \"value\", \"value range\", ...';


-- -----------------------------------------------------
-- Table `workoutdb`.`ranking_attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`ranking_attribute` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL ,
  `rank_attribute_by_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_ranking_attribute_rank_attribute_by_type1` (`rank_attribute_by_type_id` ASC) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  CONSTRAINT `fk_ranking_attribute_rank_attribute_by_type1`
    FOREIGN KEY (`rank_attribute_by_type_id` )
    REFERENCES `workoutdb`.`ranking_attribute_by_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'age, weight, height, . . .\ngender and competition type (group/individual) are automatic categories that do not need ranking_type entries';


-- -----------------------------------------------------
-- Table `workoutdb`.`ranking_attribute_range`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`ranking_attribute_range` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute_range` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `ranking_attribute_id` BIGINT NOT NULL ,
  `from` DECIMAL(8,2) NULL ,
  `to` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_ranking_attribute_division_library_measurement_system_unit1` (`uom_id` ASC) ,
  INDEX `fk_ranking_attribute_division_ranking_attribute1` (`ranking_attribute_id` ASC) ,
  CONSTRAINT `fk_ranking_attribute_division_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ranking_attribute_division_ranking_attribute1`
    FOREIGN KEY (`ranking_attribute_id` )
    REFERENCES `workoutdb`.`ranking_attribute` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`competition_ranking_attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`competition_ranking_attribute` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_ranking_attribute` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT NOT NULL ,
  `ranking_attribute_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_competition_ranking_competition1` (`competition_id` ASC) ,
  INDEX `fk_competition_ranking_ranking_attribute1` (`ranking_attribute_id` ASC) ,
  UNIQUE INDEX `unique_competition_ranking_attribute` (`competition_id` ASC, `ranking_attribute_id` ASC) ,
  CONSTRAINT `fk_competition_ranking_competition1`
    FOREIGN KEY (`competition_id` )
    REFERENCES `workoutdb`.`competition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_competition_ranking_ranking_attribute1`
    FOREIGN KEY (`ranking_attribute_id` )
    REFERENCES `workoutdb`.`ranking_attribute` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`challenge_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`challenge_group` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_group` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `challenge_id` BIGINT NOT NULL ,
  `phase_group_id` BIGINT NOT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_group_phase_group1` (`phase_group_id` ASC) ,
  INDEX `fk_challenge_group_challenge1` (`challenge_id` ASC) ,
  INDEX `fk_challenge_group_library_measurement_system_unit1` (`uom_id` ASC) ,
  UNIQUE INDEX `unique_challenge_group_challenge_phase_group` (`challenge_id` ASC, `phase_group_id` ASC) ,
  CONSTRAINT `fk_challenge_group_phase_group1`
    FOREIGN KEY (`phase_group_id` )
    REFERENCES `workoutdb`.`phase_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_group_challenge1`
    FOREIGN KEY (`challenge_id` )
    REFERENCES `workoutdb`.`challenge` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_group_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`challenge_group_result`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`challenge_group_result` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_group_result` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `challenge_group_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_group_result_library_measurement_system_unit1` (`uom_id` ASC) ,
  INDEX `fk_challenge_group_result_challenge_group4` (`challenge_group_id` ASC) ,
  CONSTRAINT `fk_challenge_group_result_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_group_result_challenge_group4`
    FOREIGN KEY (`challenge_group_id` )
    REFERENCES `workoutdb`.`challenge_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`challenge_individual`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`challenge_individual` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_individual` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `challenge_id` BIGINT NOT NULL ,
  `phase_individual_id` BIGINT NOT NULL ,
  `challenge_group_id` BIGINT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_individual_challenge1` (`challenge_id` ASC) ,
  INDEX `fk_challenge_individual_phase_individual1` (`phase_individual_id` ASC) ,
  UNIQUE INDEX `un_challenge_individual_challenge_phase_individual` (`challenge_id` ASC, `phase_individual_id` ASC) ,
  INDEX `fk_challenge_individual_challenge_group1` (`challenge_group_id` ASC) ,
  INDEX `fk_challenge_individual_library_measurement_system_unit1` (`uom_id` ASC) ,
  CONSTRAINT `fk_challenge_individual_challenge1`
    FOREIGN KEY (`challenge_id` )
    REFERENCES `workoutdb`.`challenge` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_individual_phase_individual1`
    FOREIGN KEY (`phase_individual_id` )
    REFERENCES `workoutdb`.`phase_individual` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_individual_challenge_group1`
    FOREIGN KEY (`challenge_group_id` )
    REFERENCES `workoutdb`.`challenge_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_individual_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`challenge_individual_result`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`challenge_individual_result` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_individual_result` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `challenge_individual_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_individual_result_library_measurement_system_unit1` (`uom_id` ASC) ,
  INDEX `fk_challenge_individual_result_challenge_individual1` (`challenge_individual_id` ASC) ,
  CONSTRAINT `fk_challenge_individual_result_library_measurement_system_unit1`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_individual_result_challenge_individual1`
    FOREIGN KEY (`challenge_individual_id` )
    REFERENCES `workoutdb`.`challenge_individual` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`phase_individual_result`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`phase_individual_result` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_individual_result` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `phase_individual_id` BIGINT NOT NULL ,
  `created` INT(13) NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_individual_result_library_measurement_system_unit1` (`uom_id` ASC) ,
  INDEX `fk_challenge_individual_result_phase_individual1` (`phase_individual_id` ASC) ,
  CONSTRAINT `fk_challenge_individual_result_library_measurement_system_uni`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_individual_result_phase_individual10`
    FOREIGN KEY (`phase_individual_id` )
    REFERENCES `workoutdb`.`phase_individual` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`phase_group_result`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`phase_group_result` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_group_result` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `phase_group_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL ,
  `uom_id` BIGINT NULL ,
  `points` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_challenge_group_result_library_measurement_system_unit1` (`uom_id` ASC) ,
  INDEX `fk_challenge_group_result_phase_group1` (`phase_group_id` ASC) ,
  CONSTRAINT `fk_challenge_group_result_library_measurement_system_unit10`
    FOREIGN KEY (`uom_id` )
    REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_challenge_group_result_phase_group10`
    FOREIGN KEY (`phase_group_id` )
    REFERENCES `workoutdb`.`phase_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`server`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`server` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`server` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `timezone` VARCHAR(100) NOT NULL COMMENT 'The timezone the system server is set to.  Found in /etc/timezone' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`action`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`action` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`action` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'Take Payment, Make Refund, Manage Clerks, Manage Finance Permission, Manage Members';


-- -----------------------------------------------------
-- Table `workoutdb`.`action_permission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`action_permission` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`action_permission` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `action_id` BIGINT NOT NULL ,
  `user_id` BIGINT NOT NULL ,
  `client_id` BIGINT NULL ,
  `location_id` BIGINT NULL COMMENT 'if null, the permission is for all locations for the client.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_client_user_location_location1` (`location_id` ASC) ,
  INDEX `fk_permissions_client1` (`client_id` ASC) ,
  INDEX `fk_permissions_user1` (`user_id` ASC) ,
  INDEX `fk_staff_permissions_permission1` (`action_id` ASC) ,
  CONSTRAINT `fk_client_user_location_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_permissions_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_permissions_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_staff_permissions_permission1`
    FOREIGN KEY (`action_id` )
    REFERENCES `workoutdb`.`action` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Assign permission to actions at system, client, or location levels';


-- -----------------------------------------------------
-- Table `workoutdb`.`payment_method`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`payment_method` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`payment_method` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL COMMENT 'CreditCard, Cash, Check, GiftCertificate, DealRedemption, or Other' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'CreditCard, Cash, Check, GiftCertificate, DealRedemption, or Other';


-- -----------------------------------------------------
-- Table `workoutdb`.`cc_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`cc_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`cc_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`getcube_stored_payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`getcube_stored_payment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_stored_payment` (
  `id` BIGINT NOT NULL COMMENT 'getcube\'s stored_payment id' ,
  `guid` VARCHAR(100) NOT NULL ,
  `client_user_id` BIGINT NOT NULL ,
  `payment_method_id` BIGINT NOT NULL ,
  `cc_type_id` BIGINT NULL ,
  `cc_redacted_number` VARCHAR(45) NULL ,
  `cc_expire_mm` VARCHAR(2) NULL ,
  `cc_expire_ccyy` VARCHAR(4) NULL ,
  `cc_billing_zipcode` VARCHAR(10) NULL ,
  `cc_name_on_card` VARCHAR(255) NULL ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  `json` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_getcube_stored_payment_client_user1` (`client_user_id` ASC) ,
  INDEX `fk_getcube_stored_payment_payment_method1` (`payment_method_id` ASC) ,
  INDEX `fk_getcube_stored_payment_cc_type1` (`cc_type_id` ASC) ,
  UNIQUE INDEX `guid_UNIQUE` (`guid` ASC) ,
  CONSTRAINT `fk_getcube_stored_payment_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_getcube_stored_payment_payment_method1`
    FOREIGN KEY (`payment_method_id` )
    REFERENCES `workoutdb`.`payment_method` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_getcube_stored_payment_cc_type1`
    FOREIGN KEY (`cc_type_id` )
    REFERENCES `workoutdb`.`cc_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This is getcube\'s stored credit card';


-- -----------------------------------------------------
-- Table `workoutdb`.`billing_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`billing_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`billing_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `descrtiption` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'Bill for a specific number of participations, Bill for participation during a period, Bill for a period.';


-- -----------------------------------------------------
-- Table `workoutdb`.`priod_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`priod_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`priod_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `abbreviation` VARCHAR(10) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `abbreviation_UNIQUE` (`abbreviation` ASC) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'D - Day, W - Week, M - Month, Y - Year, P - Participation';


-- -----------------------------------------------------
-- Table `workoutdb`.`participation_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`participation_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`participation_type` (
  `id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL COMMENT 'Checkins, Days of Participation' ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_status` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_status` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
COMMENT = 'Paid, Grace, Hold, Inactive';


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `prepay` TINYINT NOT NULL COMMENT 'The member pays for services to be rendered (pre-paid) or for services already rendered (post-paid).' ,
  `billing_type_id` BIGINT NOT NULL COMMENT 'Bill for set nunber of participations, Bill for participatiuons within period, Bill for period.' ,
  `billing_participation_type_id` BIGINT NULL COMMENT 'Checkins, Days of Participation' ,
  `billing_period` INT NULL COMMENT 'The number of period types that the bill is for.' ,
  `billing_priod_type_id` BIGINT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `billing_offset` INT NULL COMMENT 'For pay_for_time_period and post paid pay_for_participation, bill on paid_thu date plus this offset.  For pre-paid pay for participationm, bill member when they have offset paid participations of less.' ,
  `base_rate` DECIMAL(8,2) NULL COMMENT 'The base rate for the contract. No tax, no discounts.' ,
  `individual_rate` TINYINT NULL COMMENT 'the rate is for each individual on this contract or for the group as a whole.' ,
  `grace_period_offset` INT NULL COMMENT 'Inactivate the Member on the paid_thru date plus this offset.' ,
  `contract_period` INT NULL COMMENT 'The number of contract period types the contract is for.' ,
  `contract_priod_type_id` BIGINT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `max_participation` INT NULL COMMENT 'How many participations  in the maximum participation period does the contract cover.' ,
  `max_participation_type_id` BIGINT NULL COMMENT 'Checkins, Days of Participation' ,
  `max_participation_period` INT NULL COMMENT 'The number of max participation period types max participation is within.' ,
  `max_participation_priod_type_id` BIGINT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `max_client_users` INT NULL COMMENT 'The maximum number of client_users that can be covered on this type of contract' ,
  `max_coaches_limit` INT NULL COMMENT 'The maximum number of coaches this contract type can be limited too.  If NULL, it is not limited.' ,
  `max_location_limit` INT NULL COMMENT 'The maximum number of locations this contract type can be limited too.  If NULL, it is not limited.' ,
  `max_calendar_event_template_limit` INT NULL COMMENT 'The maximum number of class types this contract type can be limited too.  If NULL, it is not limited.' ,
  `recurring_required` TINYINT NULL COMMENT 'This contract requires the member be on recurring credit card payments.' ,
  `sell_online` TINYINT NOT NULL COMMENT 'This contract can be sold online.' ,
  `created` INT(13) NOT NULL COMMENT 'The date the contract type was created in the system' ,
  `effective` INT(13) NULL COMMENT 'The 1st day the contract can be sold.' ,
  `stop_assigning` INT(13) NULL COMMENT 'Do not allow the contract to be sold after this date.' ,
  `name` VARCHAR(100) NULL ,
  `description` TEXT NULL ,
  `signup_fee` DECIMAL(8,2) NULL COMMENT 'a one time fee to signup for this contract type.' ,
  `cancelation_fee` DECIMAL(8,2) NULL COMMENT 'A fee for when the customer wants to stop their membership before the end of the contract.' ,
  `hold_fee` DECIMAL(8,2) NULL COMMENT 'The fee for putting the contract on hold (temporarily stopped).' ,
  `hold_allowed` TINYINT NULL COMMENT 'Can a member put this contract type on temporary hold?' ,
  `terms_and_conditions` TEXT NULL ,
  `next_contract_type_id` BIGINT NULL COMMENT 'null = One-Time-Only; Same = Recurring; Different = Chained.' ,
  `contract_status_id` BIGINT NOT NULL ,
  INDEX `fk_contract_template_client1` (`client_id` ASC) ,
  INDEX `fk_contract_type_bill_type1` (`billing_type_id` ASC) ,
  INDEX `fk_contract_type_priod_type1` (`billing_priod_type_id` ASC) ,
  INDEX `fk_contract_type_priod_type2` (`contract_priod_type_id` ASC) ,
  INDEX `fk_contract_type_priod_type3` (`max_participation_priod_type_id` ASC) ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_type_contract_type1` (`next_contract_type_id` ASC) ,
  INDEX `fk_contract_type_billing_participation_type1` (`billing_participation_type_id` ASC) ,
  INDEX `fk_contract_type_participation_type1` (`max_participation_type_id` ASC) ,
  INDEX `fk_contract_type_contract_status1` (`contract_status_id` ASC) ,
  CONSTRAINT `fk_contract_template_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_bill_type1`
    FOREIGN KEY (`billing_type_id` )
    REFERENCES `workoutdb`.`billing_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_priod_type1`
    FOREIGN KEY (`billing_priod_type_id` )
    REFERENCES `workoutdb`.`priod_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_priod_type2`
    FOREIGN KEY (`contract_priod_type_id` )
    REFERENCES `workoutdb`.`priod_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_priod_type3`
    FOREIGN KEY (`max_participation_priod_type_id` )
    REFERENCES `workoutdb`.`priod_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_contract_type1`
    FOREIGN KEY (`next_contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_billing_participation_type1`
    FOREIGN KEY (`billing_participation_type_id` )
    REFERENCES `workoutdb`.`participation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_participation_type1`
    FOREIGN KEY (`max_participation_type_id` )
    REFERENCES `workoutdb`.`participation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_contract_status1`
    FOREIGN KEY (`contract_status_id` )
    REFERENCES `workoutdb`.`contract_status` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `max_participation` INT NULL COMMENT 'The number of participations that have been paid for.' ,
  `paid_thru` INT(13) NULL COMMENT 'The date the member has paid through.' ,
  `max_weekly_participation` INT NULL ,
  `contract_start` INT(13) NULL ,
  `contract_end` INT(13) NULL COMMENT 'The date the contract is good through.' ,
  `note` TEXT NULL ,
  `next_contract_type_id` BIGINT NULL ,
  `created` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_contract_template1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_contract_type1` (`next_contract_type_id` ASC) ,
  CONSTRAINT `fk_contract_contract_template1`
    FOREIGN KEY ()
    REFERENCES `workoutdb`.`contract_type` ()
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_contract_type1`
    FOREIGN KEY ()
    REFERENCES `workoutdb`.`contract_type` ()
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_calendar_entry_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_calendar_entry_template` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_calendar_entry_template` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `calendar_entry_template_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_template_calendar_entry_template_contract_template1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_template_calendar_entry_template_calendar_entry_t1` (`calendar_entry_template_id` ASC) ,
  UNIQUE INDEX `uq_contract_template_calendar_entry_template_1` (`calendar_entry_template_id` ASC, `contract_type_id` ASC) ,
  CONSTRAINT `fk_contract_template_calendar_entry_template_contract_template1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_template_calendar_entry_template_calendar_entry_t1`
    FOREIGN KEY (`calendar_entry_template_id` )
    REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_location` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_location` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `location_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_template_location_contract_template1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_template_location_location1` (`location_id` ASC) ,
  UNIQUE INDEX `uq_contract_template_location` (`contract_type_id` ASC, `location_id` ASC) ,
  CONSTRAINT `fk_contract_template_location_contract_template1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_template_location_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_coach`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_coach` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_coach` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `client_user_id` BIGINT NOT NULL ,
  INDEX `fk_contract_coach_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_coach_client_user1` (`client_user_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_contract_coach_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_coach_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`discount_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`discount_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`discount_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `discription` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_discount_type_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_discount_type_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_discount_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_discount_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_discount_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `discount_type_id` BIGINT NOT NULL ,
  `percent_discount` DECIMAL(5,2) NULL ,
  `base_rate_discount` DECIMAL(8,2) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_type_discount_contract_type1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_type_discount_discount_type1` (`discount_type_id` ASC) ,
  UNIQUE INDEX `uq_contract_type_discoun_type1` (`contract_type_id` ASC, `discount_type_id` ASC) ,
  CONSTRAINT `fk_contract_type_discount_contract_type1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_discount_discount_type1`
    FOREIGN KEY (`discount_type_id` )
    REFERENCES `workoutdb`.`discount_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'The discounts that are allowed to be used with this contract.';


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_client_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_client_user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_client_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `client_user_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_client_user_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_client_user_client_user1` (`client_user_id` ASC) ,
  CONSTRAINT `fk_contract_client_user_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_client_user_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Which members are covered by the contract.';


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_calendar_event_participation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_calendar_event_participation` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_calendar_event_participation` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `calendar_event_participation_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_calendar_event_participation_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_calendar_event_participation_calendar_event_parti1` (`calendar_event_participation_id` ASC) ,
  CONSTRAINT `fk_contract_calendar_event_participation_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_calendar_event_participation_calendar_event_parti1`
    FOREIGN KEY (`calendar_event_participation_id` )
    REFERENCES `workoutdb`.`calendar_event_participation` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'What contract was this participation covered under.';


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_category` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_category` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_category_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_contract_category_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_contract_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_contract_category` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_contract_category` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `contract_category_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_type_contract_category_contract_type1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_type_contract_category_contract_category1` (`contract_category_id` ASC) ,
  CONSTRAINT `fk_contract_type_contract_category_contract_type1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_contract_category_contract_category1`
    FOREIGN KEY (`contract_category_id` )
    REFERENCES `workoutdb`.`contract_category` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_contract_type_discount_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_contract_type_discount_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_contract_type_discount_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `contract_type_discount_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_contract_type_discount_type_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_contract_type_discount_type_contract_type_discoun1` (`contract_type_discount_type_id` ASC) ,
  CONSTRAINT `fk_contract_contract_type_discount_type_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_contract_type_discount_type_contract_type_discoun1`
    FOREIGN KEY (`contract_type_discount_type_id` )
    REFERENCES `workoutdb`.`contract_type_discount_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_location` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_location` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `location_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_location_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_location_location1` (`location_id` ASC) ,
  CONSTRAINT `fk_contract_location_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_location_location1`
    FOREIGN KEY (`location_id` )
    REFERENCES `workoutdb`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_calendar_entry_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_calendar_entry_template` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_calendar_entry_template` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT NOT NULL ,
  `calendar_entry_template_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_calendar_entry_template_contract1` (`contract_id` ASC) ,
  INDEX `fk_contract_calendar_entry_template_calendar_entry_template1` (`calendar_entry_template_id` ASC) ,
  CONSTRAINT `fk_contract_calendar_entry_template_contract1`
    FOREIGN KEY (`contract_id` )
    REFERENCES `workoutdb`.`contract` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_calendar_entry_template_calendar_entry_template1`
    FOREIGN KEY (`calendar_entry_template_id` )
    REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_coach`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_coach` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_coach` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `client_user_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_type_coach_contract_type2` (`contract_type_id` ASC) ,
  INDEX `fk_contract_type_coach_client_user1` (`client_user_id` ASC) ,
  UNIQUE INDEX `uq_contract_type_coach1` (`contract_type_id` ASC, `client_user_id` ASC) ,
  CONSTRAINT `fk_contract_type_coach_contract_type1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_coach_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`contract_type_affiliation_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`contract_type_affiliation_type` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_affiliation_type` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT NOT NULL ,
  `affiliation_type_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_type_affiliation_type_contract_type1` (`contract_type_id` ASC) ,
  INDEX `fk_contract_type_affiliation_type_affiliation_type1` (`affiliation_type_id` ASC) ,
  CONSTRAINT `fk_contract_type_affiliation_type_contract_type1`
    FOREIGN KEY (`contract_type_id` )
    REFERENCES `workoutdb`.`contract_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contract_type_affiliation_type_affiliation_type1`
    FOREIGN KEY (`affiliation_type_id` )
    REFERENCES `workoutdb`.`affiliation_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'contract can only be sold to this type of existing client_user';


-- -----------------------------------------------------
-- Table `workoutdb`.`order_master`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`order_master` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_master` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `getcube_order_master_id` BIGINT NULL ,
  `client_user_id` BIGINT NOT NULL COMMENT 'The client user who is buying' ,
  `created` INT(13) NOT NULL ,
  `created_by_user_id` BIGINT NOT NULL COMMENT 'The client user who is selling' ,
  `created_by_app` VARCHAR(20) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_master_client_user1` (`client_user_id` ASC) ,
  INDEX `fk_order_master_user1` (`created_by_user_id` ASC) ,
  CONSTRAINT `fk_order_master_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_master_user1`
    FOREIGN KEY (`created_by_user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`order_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`order_item` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_item` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `order_master_id` BIGINT NOT NULL ,
  `description` TEXT NOT NULL ,
  `amount` DECIMAL(8,2) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_item_order_master1` (`order_master_id` ASC) ,
  CONSTRAINT `fk_order_item_order_master1`
    FOREIGN KEY (`order_master_id` )
    REFERENCES `workoutdb`.`order_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`getcube_settlement`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`getcube_settlement` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_settlement` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `getcube_settlement_id` BIGINT NOT NULL ,
  `guid` VARCHAR(100) NOT NULL ,
  `status` VARCHAR(45) NOT NULL ,
  `collected_amount` DECIMAL(8,2) NOT NULL COMMENT 'The amount collected for the transaction' ,
  `fee_amount` DECIMAL(8,2) NOT NULL COMMENT 'The fees on the transaction' ,
  `net_amount` DECIMAL(8,2) NOT NULL COMMENT 'The collected_amount minus the fee_amount. The amount deposited in to the bank.' ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  `json` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `guid_UNIQUE` (`guid` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`order_transaction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`order_transaction` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_transaction` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `getcube_payment_id` INT(13) NULL ,
  `order_master_id` BIGINT NOT NULL ,
  `collected_amount` DECIMAL(8,2) NOT NULL DEFAULT 0 COMMENT 'the amount charged to the customer' ,
  `fee_amount` DECIMAL(8,2) NOT NULL DEFAULT 0 ,
  `is_refund` TINYINT NOT NULL DEFAULT 0 ,
  `refunded_order_transaction_id` BIGINT NULL COMMENT 'The link to the transaction being refunded by this transaction.' ,
  `payment_method_id` BIGINT NOT NULL ,
  `check_number` VARCHAR(45) NULL ,
  `getcube_stored_payment_id` BIGINT NULL ,
  `cc_type_id` BIGINT NULL ,
  `cc_redacted_number` VARCHAR(45) NULL ,
  `cc_expire_mm` VARCHAR(2) NULL ,
  `cc_expire_ccyy` VARCHAR(4) NULL ,
  `cc_billing_zipcode` VARCHAR(10) NULL ,
  `cc_name_on_card` VARCHAR(255) NULL ,
  `getcube_settlement_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  `json` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_transactions_order_master1` (`order_master_id` ASC) ,
  INDEX `fk_order_transactions_getcube_stored_payment1` (`getcube_stored_payment_id` ASC) ,
  INDEX `fk_order_transactions_payment_method1` (`payment_method_id` ASC) ,
  INDEX `fk_order_transactions_cc_type1` (`cc_type_id` ASC) ,
  INDEX `fk_order_transaction_order_transaction_settlement1` (`getcube_settlement_id` ASC) ,
  INDEX `fk_order_transaction_order_transaction1` (`refunded_order_transaction_id` ASC) ,
  CONSTRAINT `fk_order_transactions_order_master1`
    FOREIGN KEY (`order_master_id` )
    REFERENCES `workoutdb`.`order_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transactions_getcube_stored_payment1`
    FOREIGN KEY (`getcube_stored_payment_id` )
    REFERENCES `workoutdb`.`getcube_stored_payment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transactions_payment_method1`
    FOREIGN KEY (`payment_method_id` )
    REFERENCES `workoutdb`.`payment_method` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transactions_cc_type1`
    FOREIGN KEY (`cc_type_id` )
    REFERENCES `workoutdb`.`cc_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_order_transaction_settlement1`
    FOREIGN KEY (`getcube_settlement_id` )
    REFERENCES `workoutdb`.`getcube_settlement` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_order_transaction1`
    FOREIGN KEY (`refunded_order_transaction_id` )
    REFERENCES `workoutdb`.`order_transaction` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_role_staff`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_role_staff` (`id` INT, `name` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`user_profile_media_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`user_profile_media_last_entered` (`id` INT, `user_id` INT, `created` INT, `media_url` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_equipment_media_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_equipment_media_last_entered` (`id` INT, `library_equipment_id` INT, `created` INT, `media_url` INT, `description` INT, `note` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_exercise_media_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_exercise_media_last_entered` (`library_exercise_id` INT, `id` INT, `created` INT, `media_url` INT, `note` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_workout_media_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_media_last_entered` (`id` INT, `library_workout_id` INT, `created` INT, `stored_locally` INT, `media_url` INT, `note` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`user_progress_log_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`user_progress_log_last_entered` (`id` INT, `user_id` INT, `user_goal_type_id` INT, `height` INT, `height_uom_id` INT, `weight` INT, `weight_uom_id` INT, `percent_fat` INT, `created` INT, `deleted` INT, `note` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_role_guest`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_role_guest` (`id` INT, `name` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_role_member`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_role_member` (`id` INT, `name` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_role_review`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_role_review` (`id` INT, `name` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_role_trial`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_role_trial` (`id` INT, `name` INT, `description` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_workout_assigned`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_assigned` (`library_workout_id` INT, `assigned` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`num_users_logged_workout`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`num_users_logged_workout` (`library_workout_id` INT, `number_of_users` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`workout_logged_by_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`workout_logged_by_user` (`user_id` INT, `library_workout_id` INT, `count` INT, `start` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_workout_number_of_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_number_of_users` (`library_workout_id` INT, `number_of_users` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_workout_last_participation_by_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_last_participation_by_user` (`user_id` INT, `library_workout_id` INT, `last_participation` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`library_workout_last_participation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_last_participation` (`client_id` INT, `library_workout_id` INT, `last_participation` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`competition_stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`competition_stats` (`id` INT, `name` INT, `client_id` INT, `user_id` INT, `competition_type_id` INT, `registration_type_id` INT, `created` INT, `deleted` INT, `description` INT, `closed_competition` INT, `team_size_min` INT, `team_size_max` INT, `registration_start` INT, `registration_end` INT, `phase_count` INT, `challenge_count` INT, `group_count` INT, `individual_count` INT, `start` INT, `end` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`challenge_group_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`challenge_group_detail` (`competitition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `client_id` INT, `competition_id` INT, `phase_id` INT, `challenge_id` INT, `group_captain_id` INT, `group_name` INT, `group_affiliated_gym` INT, `result` INT, `uom_id` INT, `points` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`challenge_individual_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`challenge_individual_detail` (`client_user_id` INT, `client_id` INT, `user_id` INT, `competition_individual_id` INT, `phase_individual_id` INT, `challenge_individual_id` INT, `competition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `competition_id` INT, `phase_id` INT, `challenge_id` INT, `first_name` INT, `last_name` INT, `email` INT, `height` INT, `height_uom_id` INT, `weight` INT, `weight_uom_id` INT, `birthday` INT, `alias_name` INT, `affiliated_gym` INT, `group_name` INT, `group_affiliated_gym` INT, `challenge_name` INT, `score_type_id` INT, `score_calculation_type_id` INT, `point_awarding_type_id` INT, `challenge_start` INT, `challenge_end` INT, `max_team_size` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`competition_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`competition_detail` (`competition_id` INT, `phase_id` INT, `challenge_id` INT, `competition_individual_id` INT, `phase_individual_id` INT, `challenge_individual_id` INT, `competition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `client_id` INT, `user_id` INT, `competition_name` INT, `competition_type_id` INT, `registration_type_id` INT, `closed_competition` INT, `competition_min_team_size` INT, `competition_max_team_size` INT, `registration_start` INT, `registration_end` INT, `group_name` INT, `group_affiliated_gym` INT, `phase_name` INT, `challenge_name` INT, `score_type_id` INT, `score_calculation_type_id` INT, `point_awarding_type_id` INT, `challenge_start` INT, `challenge_end` INT, `challenge_max_team_size` INT, `first_name` INT, `last_name` INT, `email` INT, `height` INT, `height_uom_id` INT, `weight` INT, `weight_uom_id` INT, `birthday` INT, `alias_name` INT, `affiliated_gym` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_calendar_event_participation_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered` (`id` INT, `client_user_id` INT, `calendar_event_id` INT, `email_reminder_sent` INT, `email_reminder_opened` INT, `start_emotional_level_id` INT, `end_emotional_level_id` INT, `created` INT, `created_by_app` INT, `created_by_user_id` INT, `note` INT);

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_staff`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_role_staff` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_staff`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_staff` AS
SELECT *
FROM client_user_role
WHERE name = "Staff";

-- -----------------------------------------------------
-- View `workoutdb`.`user_profile_media_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`user_profile_media_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`user_profile_media_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`user_profile_media_last_entered` AS
SELECT *
FROM user_profile_media
WHERE id IN (SELECT max(id) FROM user_profile_media GROUP BY user_id);

-- -----------------------------------------------------
-- View `workoutdb`.`library_equipment_media_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_equipment_media_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`library_equipment_media_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_equipment_media_last_entered` AS
SELECT *
FROM library_equipment_media 
WHERE id IN (SELECT max(id) FROM library_equipment_media GROUP BY library_equipment_id);

-- -----------------------------------------------------
-- View `workoutdb`.`library_exercise_media_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_exercise_media_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`library_exercise_media_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_exercise_media_last_entered` AS
SELECT ex.library_exercise_id, media.*
FROM library_exercise_exercise_media ex,
library_exercise_media media
WHERE media.id = ex.library_exercise_media_id
AND media.id IN (SELECT max(library_exercise_media_id) FROM library_exercise_exercise_media GROUP BY library_exercise_id);

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_media_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_workout_media_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`library_workout_media_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_workout_media_last_entered` AS
SELECT *
FROM library_workout_media
WHERE id IN (SELECT max(id) FROM library_workout_media GROUP BY library_workout_id);

-- -----------------------------------------------------
-- View `workoutdb`.`user_progress_log_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`user_progress_log_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`user_progress_log_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`user_progress_log_last_entered` AS
SELECT *
FROM user_progress_log
WHERE id IN (SELECT max(id) FROM user_progress_log GROUP BY user_id);

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_guest`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_role_guest` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_guest`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_guest` AS
SELECT *
FROM client_user_role
WHERE name = "Guest";

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_member`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_role_member` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_member`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_member` AS
SELECT *
FROM client_user_role
WHERE name = "Member";

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_review`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_role_review` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_review`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_review` AS
SELECT *
FROM client_user_role
WHERE name = "Review";

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_trial`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_role_trial` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_trial`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_trial` AS
SELECT *
FROM client_user_role
WHERE name = "Trial";

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_assigned`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_workout_assigned` ;
DROP TABLE IF EXISTS `workoutdb`.`library_workout_assigned`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_workout_assigned` AS
SELECT w.id library_workout_id, 
IF((COUNT(distinct ev.id) + COUNT(distinct wod.id)) = 0,false,true) assigned
FROM library_workout w
LEFT OUTER JOIN calendar_event_library_workout ev
ON ev.library_workout_id = w.id
LEFT OUTER JOIN calendar_entry_template_wod_library_workout wod
ON wod.library_workout_id = w.id
GROUP BY w.id;

-- -----------------------------------------------------
-- View `workoutdb`.`num_users_logged_workout`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`num_users_logged_workout` ;
DROP TABLE IF EXISTS `workoutdb`.`num_users_logged_workout`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`num_users_logged_workout` AS
SELECT l.library_workout_id, COUNT(DISTINCT l.user_id) number_of_users
FROM workout_log l
WHERE l.workout_log_completed
GROUP BY l.library_workout_id;

-- -----------------------------------------------------
-- View `workoutdb`.`workout_logged_by_user`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`workout_logged_by_user` ;
DROP TABLE IF EXISTS `workoutdb`.`workout_logged_by_user`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`workout_logged_by_user` AS
SELECT l.user_id, l.library_workout_id, count(l.id) count, max(start) start
FROM workout_log l
WHERE l.workout_log_completed
GROUP BY l.user_id, l.library_workout_id;

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_number_of_users`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_workout_number_of_users` ;
DROP TABLE IF EXISTS `workoutdb`.`library_workout_number_of_users`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_workout_number_of_users` AS
SELECT w.library_workout_id, count(distinct c.user_id) number_of_users
FROM calendar_event_library_workout w,
calendar_event_participation p,
client_user c
WHERE p.calendar_event_id = w.calendar_event_id
AND c.id = p.client_user_id
GROUP BY w.library_workout_id;

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_last_participation_by_user`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_workout_last_participation_by_user` ;
DROP TABLE IF EXISTS `workoutdb`.`library_workout_last_participation_by_user`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_workout_last_participation_by_user` AS
SELECT c.user_id, w.library_workout_id, max(e.start) last_participation
FROM calendar_event_library_workout w,
calendar_event e,
calendar_event_participation p,
client_user c
WHERE e.id = w.calendar_event_id
AND p.calendar_event_id = e.id
AND c.id = p.client_user_id
GROUP BY c.user_id, w.library_workout_id;

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_last_participation`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`library_workout_last_participation` ;
DROP TABLE IF EXISTS `workoutdb`.`library_workout_last_participation`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`library_workout_last_participation` AS
SELECT c.client_id, w.library_workout_id, max(e.start) last_participation
FROM calendar c,
calendar_event e,
calendar_event_library_workout w,
calendar_event_participation p
WHERE e.calendar_id = c.id
AND w.calendar_event_id = e.id
AND p.calendar_event_id = e.id
GROUP BY c.client_id, w.library_workout_id;

-- -----------------------------------------------------
-- View `workoutdb`.`competition_stats`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`competition_stats` ;
DROP TABLE IF EXISTS `workoutdb`.`competition_stats`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`competition_stats` AS
SELECT
comp.*,
COUNT( DISTINCT phase.id ) phase_count,
COUNT( DISTINCT chal.id ) challenge_count,
COUNT( DISTINCT cg.id ) group_count,
COUNT( DISTINCT ci.id ) individual_count,
MIN(chal.start) start, MAX(chal.end) end
FROM
competition comp
LEFT OUTER JOIN phase phase
LEFT OUTER JOIN challenge chal
ON chal.phase_id = phase.id
ON phase.competition_id = comp.id
LEFT OUTER JOIN competition_group cg
ON cg.competition_id = comp.id
LEFT OUTER JOIN competition_individual ci
ON ci.competition_id = comp.id
GROUP BY comp.id;

-- -----------------------------------------------------
-- View `workoutdb`.`challenge_group_detail`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`challenge_group_detail` ;
DROP TABLE IF EXISTS `workoutdb`.`challenge_group_detail`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`challenge_group_detail` AS
SELECT 
cg.id competitition_group_id, pg.id phase_group_id, chg.id challenge_group_id,
c.client_id, cg.competition_id, pg.phase_id, chg.challenge_id,
cg.captain_id group_captain_id, cg.name group_name, cg.affiliated_gym group_affiliated_gym,
chg.result, chg.uom_id, chg.points
FROM 
challenge_group chg,
phase_group pg,
competition_group cg,
competition c
WHERE pg.id = chg.phase_group_id
AND cg.id = pg.competition_group_id
AND c.id = cg.competition_id;

-- -----------------------------------------------------
-- View `workoutdb`.`challenge_individual_detail`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`challenge_individual_detail` ;
DROP TABLE IF EXISTS `workoutdb`.`challenge_individual_detail`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`challenge_individual_detail` AS
SELECT
cu.id client_user_id, comp.client_id, ci.user_id,
ci.id competition_individual_id, pi.id phase_individual_id, chi.id challenge_individual_id,
ci.competition_group_id, pi.phase_group_id, chi.challenge_group_id,
ci.competition_id, pi.phase_id, chi.challenge_id,
u.first_name, u.last_name, u.email,
ci.height, ci.height_uom_id, ci.weight, ci.weight_uom_id, 
ci.birthday, ci.alias_name, ci.affiliated_gym,
cg.name group_name, cg.affiliated_gym group_affiliated_gym,
ch.name challenge_name, ch.score_type_id, ch.score_calculation_type_id, ch.point_awarding_type_id, ch.start challenge_start, ch.end challenge_end, ch.max_team_size
FROM
challenge ch,
challenge_individual chi,
phase_individual pi,
competition_individual ci
LEFT OUTER JOIN competition_group cg
ON cg.id = ci.competition_group_id
LEFT OUTER JOIN user u
ON u.id = ci.user_id,
competition comp,
client_user cu
WHERE ch.score_type_id = 1 and ch.score_calculation_type_id = 1
AND chi.challenge_id = ch.id
AND pi.id = chi.phase_individual_id
AND ci.id = pi.competition_individual_id
AND comp.id = ci.competition_id
AND cu.user_id = ci.user_id
AND cu.client_id = comp.client_id;

-- -----------------------------------------------------
-- View `workoutdb`.`competition_detail`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`competition_detail` ;
DROP TABLE IF EXISTS `workoutdb`.`competition_detail`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`competition_detail` AS
SELECT 
ci.competition_id, pi.phase_id, chi.challenge_id,
ci.id competition_individual_id, pi.id phase_individual_id, chi.id challenge_individual_id,
ci.competition_group_id, pi.phase_group_id, chi.challenge_group_id,
c.client_id, ci.user_id,
c.name competition_name, c.competition_type_id, c.registration_type_id, c.closed_competition,
c.team_size_min competition_min_team_size, c.team_size_max competition_max_team_size, c.registration_start, c.registration_end,
cg.name group_name, cg.affiliated_gym group_affiliated_gym,
p.name phase_name,
ch.name challenge_name, ch.score_type_id, ch.score_calculation_type_id, ch.point_awarding_type_id, ch.start challenge_start, ch.end challenge_end,
ch.max_team_size challenge_max_team_size,
u.first_name, u.last_name, u.email,
ci.height, ci.height_uom_id, ci.weight, ci.weight_uom_id, 
ci.birthday, ci.alias_name, ci.affiliated_gym
FROM
competition c
LEFT OUTER JOIN competition_individual ci
LEFT OUTER JOIN user u
ON u.id = ci.user_id
LEFT OUTER JOIN competition_group cg
ON cg.id = ci.competition_group_id
LEFT OUTER JOIN phase_individual pi
LEFT OUTER JOIN phase p
ON p.id = pi.phase_id
LEFT OUTER JOIN challenge_individual chi
LEFT OUTER JOIN challenge ch
ON ch.id = chi.challenge_id
ON chi.phase_individual_id = pi.id
ON pi.competition_individual_id = ci.id
ON ci.competition_id = c.id;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_calendar_event_participation_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_calendar_event_participation_last_entered` AS
SELECT *
FROM calendar_event_participation
WHERE id IN (SELECT max(id) FROM calendar_event_participation GROUP BY client_user_id);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
