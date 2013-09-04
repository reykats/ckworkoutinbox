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
  INDEX `fk_library_exercise_library_exercise_level1_idx` (`library_exercise_level_id` ASC) ,
  INDEX `fk_library_exercise_library_body_region1_idx` (`library_body_region_id` ASC) ,
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
  UNIQUE INDEX `uq_library_exercise_body_part` (`library_exercise_id` ASC, `library_body_part_id` ASC) ,
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
  INDEX `fk_library_body_region_body_part_library_body_region1_idx` (`library_body_region_id` ASC) ,
  INDEX `fk_library_body_region_body_part_library_body_part1_idx` (`library_body_part_id` ASC) ,
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
  INDEX `fk_library_exer_inst_lib_exercise1_idx` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exer_inst_lib_exercise_media1_idx` (`library_exercise_media_id` ASC) ,
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
  INDEX `fk_library_exercise_exercise_media_library_exercise1_idx` (`library_exercise_id` ASC) ,
  INDEX `fk_library_exercise_exercise_media_exercise_media1_idx` (`library_exercise_media_id` ASC) ,
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
  INDEX `fk_library_exercise_equipment_library_equipment1_idx` (`library_equipment_id` ASC) ,
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
  `metric_conversion` DECIMAL(20,10) NOT NULL DEFAULT 1 ,
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
  `fb_id` VARCHAR(225) NULL ,
  `google_id` VARCHAR(225) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  INDEX `fk_user_library_measurement_system_unit1_idx` (`height_uom_id` ASC) ,
  INDEX `fk_user_library_measurement_system_unit2_idx` (`weight_uom_id` ASC) ,
  UNIQUE INDEX `fb_id_UNIQUE` (`fb_id` ASC) ,
  UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC) ,
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
-- Table `workoutdb`.`getcube_user_master`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`getcube_user_master` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_user_master` (
  `id` BIGINT NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `password` VARCHAR(40) NOT NULL ,
  `token` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'All getcube user_masters must be company managers in the getcube system so they can see the stored_payments (stored credit cards) at all locations for a member.';


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
  `logo_media_url` TEXT NULL ,
  `fb_page_id` BIGINT NULL ,
  `fb_page_token` VARCHAR(225) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  UNIQUE INDEX `widget_token_UNIQUE` (`widget_token` ASC) ,
  INDEX `fk_client_getcube_user_master1_idx` (`getcube_user_master_id` ASC) ,
  UNIQUE INDEX `fb_page_id_UNIQUE` (`fb_page_id` ASC) ,
  UNIQUE INDEX `fb_page_token_UNIQUE` (`fb_page_token` ASC) ,
  CONSTRAINT `fk_client_getcube_user_master1`
    FOREIGN KEY (`getcube_user_master_id` )
    REFERENCES `workoutdb`.`getcube_user_master` (`id` )
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
  INDEX `fk_location_client1_idx` (`client_id` ASC) ,
  INDEX `fk_location_member1_idx` (`client_user_id` ASC) ,
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
-- Table `workoutdb`.`client_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`client_user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NOT NULL ,
  `client_id` BIGINT NOT NULL ,
  `client_user_role_id` BIGINT NOT NULL ,
  `location_id` BIGINT NULL ,
  `client_user_emergency_contact_id` BIGINT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL ,
  `note` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_membership_user1_idx` (`user_id` ASC) ,
  INDEX `fk_membership_client1_idx` (`client_id` ASC) ,
  INDEX `fk_membership_membership_type1_idx` (`client_user_role_id` ASC) ,
  INDEX `fk_member_emergency_contact1_idx` (`client_user_emergency_contact_id` ASC) ,
  INDEX `fk_client_user_location1_idx` (`location_id` ASC) ,
  UNIQUE INDEX `uq_client_user1` (`user_id` ASC, `client_id` ASC) ,
  CONSTRAINT `fk_membership_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_membership_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
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
  INDEX `fk_classroom_location1_idx` (`location_id` ASC) ,
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
  INDEX `fk_user_progress_log_user1_idx` (`user_id` ASC) ,
  INDEX `fk_user_progress_log_user_goal_type1_idx` (`user_goal_type_id` ASC) ,
  INDEX `fk_user_progress_log_library_measurement_system_unit1_idx` (`height_uom_id` ASC) ,
  INDEX `fk_user_progress_log_library_measurement_system_unit2_idx` (`weight_uom_id` ASC) ,
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
  INDEX `fk_user_media_user_progress_log1_idx` (`user_progress_log_id` ASC) ,
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
  INDEX `fk_library_workout_library_workout_recording_type1_idx` (`library_workout_recording_type_id` ASC) ,
  UNIQUE INDEX `uq_library_workout_name_unique` (`name` ASC) ,
  INDEX `fk_library_workout_client1_idx` (`client_id` ASC) ,
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
  INDEX `fk_library_workout_media_library_workout1_idx` (`library_workout_id` ASC) ,
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
  INDEX `fk_library_equipment_measurement_library_equipment1_idx` (`library_equipment_id` ASC) ,
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
  INDEX `fk_calendar_user1_idx` (`user_id` ASC) ,
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
  INDEX `fk_calendar_entry_template_client1_idx` (`client_id` ASC) ,
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
  INDEX `fk_calendar_event_calendar_entry1_idx` (`calendar_entry_id` ASC) ,
  UNIQUE INDEX `un_clendar_event_start1` (`start` ASC, `calendar_entry_id` ASC) ,
  INDEX `fk_calendar_event_calendar_entry_template1_idx` (`calendar_entry_template_id` ASC) ,
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
  INDEX `fk_calendar_event_participation_client_user1_idx` (`client_user_id` ASC) ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_event_participation_calendar_event1_idx` (`calendar_event_id` ASC) ,
  INDEX `fk_calendar_event_participation_emotional_level1_idx` (`start_emotional_level_id` ASC) ,
  INDEX `fk_calendar_event_participation_emotional_level2_idx` (`end_emotional_level_id` ASC) ,
  INDEX `fk_calendar_event_participation_user1_idx` (`created_by_user_id` ASC) ,
  UNIQUE INDEX `uq_calendar_event_participation` (`client_user_id` ASC, `calendar_event_id` ASC) ,
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
  `json_log` TEXT NULL COMMENT 'The log of a user\'s participation workout results' ,
  `json_log_summary` TEXT NULL ,
  `note` TEXT NULL ,
  `original_json_log` TEXT NULL ,
  `time_limit_note` TEXT NULL ,
  `auto_calculate_result` TINYINT NULL DEFAULT 0 COMMENT 'Were the results auto-calculated yes (1) or no (0).' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_participation_log_library_workout1_idx` (`library_workout_id` ASC) ,
  INDEX `fk_workout_log_user1_idx` (`user_id` ASC) ,
  INDEX `fk_workout_log_library_workout_recording_type1_idx` (`library_workout_recording_type_id` ASC) ,
  INDEX `fk_workout_log_calendar_event_participation1_idx` (`calendar_event_participation_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit1_idx` (`result_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit2_idx` (`time_limit_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit3_idx` (`weight_uom_id` ASC) ,
  INDEX `fk_workout_log_library_measurement_system_unit4_idx` (`height_uom_id` ASC) ,
  UNIQUE INDEX `uq_workout_log_participation_workout` (`calendar_event_participation_id` ASC, `library_workout_id` ASC) ,
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
  INDEX `fk_classroom_media_classroom1_idx` (`classroom_id` ASC) ,
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
  INDEX `fk_workout_log_calculation_calculation_type1_idx` (`workout_log_calculation_type_id` ASC) ,
  INDEX `fk_workout_log_calculation_workout_log1_idx` (`workout_log_id` ASC) ,
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
  INDEX `fk_table1_workout_log1_idx` (`workout_log_id` ASC) ,
  INDEX `fk_table1_library_exercise1_idx` (`library_exercise_id` ASC) ,
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
  INDEX `fk_email_batch_user1_idx` (`user_id` ASC) ,
  INDEX `fk_email_batch_email_template1_idx` (`email_template_id` ASC) ,
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
  INDEX `fk_email_user1_idx` (`sending_user_id` ASC) ,
  INDEX `fk_email_user2_idx` (`receiving_user_id` ASC) ,
  INDEX `email_sending_name_idx` (`sending_name` ASC) ,
  INDEX `email_sending_email_idx` (`sending_email` ASC) ,
  INDEX `email_receiving_name_idx` (`receiving_name` ASC) ,
  INDEX `email_receiving_email_idx` (`receiving_email` ASC) ,
  INDEX `fk_email_email_batch1_idx` (`email_batch_id` ASC) ,
  INDEX `fk_email_email_log1_idx` (`email_log_id` ASC) ,
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
  INDEX `fk_email_batch_user_user1_idx` (`user_id` ASC) ,
  INDEX `fk_email_batch_user_email_batch1_idx` (`email_batch_id` ASC) ,
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
  INDEX `fk_email_tag_email_email1_idx` (`email_id` ASC) ,
  INDEX `fk_email_tag_email_email_tag1_idx` (`email_tag_id` ASC) ,
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
  INDEX `fk_email_tag_email_batch_email_batch1_idx` (`email_batch_id` ASC) ,
  INDEX `fk_email_tag_email_batch_email_tag1_idx` (`email_tag_id` ASC) ,
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
  INDEX `fk_email_event_history_email1_idx` (`email_id` ASC) ,
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
  INDEX `fk_calendar_event_library_workout_library_workout1_idx` (`library_workout_id` ASC) ,
  INDEX `fk_calendar_event_library_workout_calendar_event1_idx` (`calendar_event_id` ASC) ,
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
  INDEX `fk_location_media_location1_idx` (`location_id` ASC) ,
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
  INDEX `fk_client_media_client1_idx` (`client_id` ASC) ,
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
  INDEX `fk_calendar_media_calendar1_idx` (`calendar_id` ASC) ,
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
  INDEX `fk_calendar_entry_template_wod_calendar_entry_template1_idx` (`calendar_entry_template_id` ASC) ,
  UNIQUE INDEX `uq_calendar_entry_template_wod1` (`calendar_entry_template_id` ASC, `yyyymmdd` ASC) ,
  INDEX `fk_calendar_entry_template_wod_client1_idx` (`client_id` ASC) ,
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
  INDEX `fk_calendar_entry_template_wod_library_workout_calendar_ent_idx` (`calendar_entry_template_wod_id` ASC) ,
  INDEX `fk_calendar_entry_template_wod_library_workout_library_work_idx` (`library_workout_id` ASC) ,
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
  INDEX `fk_library_workout_library_exercise_library_workout1_idx` (`library_workout_id` ASC) ,
  INDEX `fk_library_workout_library_exercise_library_exercise1_idx` (`library_exercise_id` ASC) ,
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
  INDEX `fk_library_workout_library_equipment_library_workout1_idx` (`library_workout_id` ASC) ,
  INDEX `fk_library_workout_library_equipment_library_equipment1_idx` (`library_equipment_id` ASC) ,
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
  INDEX `fk_provisional_user_client1_idx` (`client_id` ASC) ,
  INDEX `fk_provisional_user_location1_idx` (`location_id` ASC) ,
  INDEX `fk_provisional_user_client_user_role1_idx` (`client_user_role_id` ASC) ,
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
  INDEX `fk_pending_workout_log_calendar_event_participation1_idx` (`calendar_event_participation_id` ASC) ,
  INDEX `fk_pending_workout_log_library_workout1_idx` (`library_workout_id` ASC) ,
  UNIQUE INDEX `uq_workout_log_pending` (`calendar_event_participation_id` ASC, `library_workout_id` ASC) ,
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
  INDEX `fk_workout_log_library_equipment_workout_log1_idx` (`workout_log_id` ASC) ,
  INDEX `fk_workout_log_library_equipment_library_equipment1_idx` (`library_equipment_id` ASC) ,
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
  INDEX `fk_user_notification_queue_user1_idx` (`user_id` ASC) ,
  UNIQUE INDEX `uq_user_notification_queue` (`user_id` ASC) ,
  CONSTRAINT `fk_user_notification_queue_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `workoutdb`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This is the queue used to notify users that they need to complete some system process. (Workout logging, Profile data . . .)';


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
-- Table `workoutdb`.`order_master`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`order_master` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_master` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `getcube_order_master_id` BIGINT NULL ,
  `client_user_id` BIGINT NOT NULL ,
  `created` INT(13) NOT NULL ,
  `created_by_user_id` BIGINT NOT NULL ,
  `created_by_app` VARCHAR(20) NOT NULL ,
  `updated` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_master_client_user1_idx` (`client_user_id` ASC) ,
  INDEX `fk_order_master_user1_idx` (`created_by_user_id` ASC) ,
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
  INDEX `fk_order_item_order_master1_idx` (`order_master_id` ASC) ,
  CONSTRAINT `fk_order_item_order_master1`
    FOREIGN KEY (`order_master_id` )
    REFERENCES `workoutdb`.`order_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`payment_method`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`payment_method` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`payment_method` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL COMMENT 'CreditCard, Cash, Check, GiftCertificate, DealRedemption, or Other' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `payment_method_name_UNIQUE` (`name` ASC) )
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
  UNIQUE INDEX `cc_type_name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`getcube_stored_payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`getcube_stored_payment` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_stored_payment` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
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
  INDEX `fk_getcube_stored_payment_payment_method1_idx` (`payment_method_id` ASC) ,
  INDEX `fk_getcube_stored_payment_cc_type1_idx` (`cc_type_id` ASC) ,
  INDEX `fk_getcube_stored_payment_client_user1_idx` (`client_user_id` ASC) ,
  UNIQUE INDEX `guid_UNIQUE` (`guid` ASC) ,
  CONSTRAINT `fk_getcube_stored_payment_payment_method1`
    FOREIGN KEY (`payment_method_id` )
    REFERENCES `workoutdb`.`payment_method` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_getcube_stored_payment_cc_type1`
    FOREIGN KEY (`cc_type_id` )
    REFERENCES `workoutdb`.`cc_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_getcube_stored_payment_client_user1`
    FOREIGN KEY (`client_user_id` )
    REFERENCES `workoutdb`.`client_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This is getcube\'s stored credit card';


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
  `getcube_payment_id` BIGINT NULL ,
  `order_master_id` BIGINT NOT NULL ,
  `collected_amount` DECIMAL(8,2) NOT NULL DEFAULT 0 COMMENT 'the amount charged to the customer' ,
  `fee_amount` DECIMAL(8,2) NOT NULL DEFAULT 0 ,
  `is_refund` TINYINT NOT NULL DEFAULT 0 ,
  `refunded_order_transaction_id` BIGINT NULL ,
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
  `updated(13)` INT(13) NULL ,
  `deleted` INT(13) NULL ,
  `json` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_transaction_order_master1_idx` (`order_master_id` ASC) ,
  INDEX `fk_order_transaction_order_transaction1_idx` (`refunded_order_transaction_id` ASC) ,
  INDEX `fk_order_transaction_payment_method1_idx` (`payment_method_id` ASC) ,
  INDEX `fk_order_transaction_getcube_stored_payment1_idx` (`getcube_stored_payment_id` ASC) ,
  INDEX `fk_order_transaction_cc_type1_idx` (`cc_type_id` ASC) ,
  INDEX `fk_order_transaction_getcube_settlement1_idx` (`getcube_settlement_id` ASC) ,
  CONSTRAINT `fk_order_transaction_order_master1`
    FOREIGN KEY (`order_master_id` )
    REFERENCES `workoutdb`.`order_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_order_transaction1`
    FOREIGN KEY (`refunded_order_transaction_id` )
    REFERENCES `workoutdb`.`order_transaction` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_payment_method1`
    FOREIGN KEY (`payment_method_id` )
    REFERENCES `workoutdb`.`payment_method` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_getcube_stored_payment1`
    FOREIGN KEY (`getcube_stored_payment_id` )
    REFERENCES `workoutdb`.`getcube_stored_payment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_cc_type1`
    FOREIGN KEY (`cc_type_id` )
    REFERENCES `workoutdb`.`cc_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_getcube_settlement1`
    FOREIGN KEY (`getcube_settlement_id` )
    REFERENCES `workoutdb`.`getcube_settlement` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `workoutdb`.`image_size`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`image_size` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`image_size` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
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
CREATE TABLE IF NOT EXISTS `workoutdb`.`library_workout_last_participation` (`client_id` INT, `library_workout_id` INT, `last_participation` INT, `timezone` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_calendar_event_participation_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered` (`id` INT, `client_user_id` INT, `calendar_event_id` INT, `email_reminder_sent` INT, `email_reminder_opened` INT, `start_emotional_level_id` INT, `end_emotional_level_id` INT, `created` INT, `created_by_app` INT, `created_by_user_id` INT, `note` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`location_media_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`location_media_last_entered` (`id` INT, `location_id` INT, `created` INT, `media_url` INT);

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
SELECT c.client_id, w.library_workout_id, max(e.start) last_participation, c.timezone
FROM calendar c,
calendar_event e,
calendar_event_library_workout w,
calendar_event_participation p
WHERE e.calendar_id = c.id
AND w.calendar_event_id = e.id
AND p.calendar_event_id = e.id
GROUP BY c.client_id, w.library_workout_id;

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

-- -----------------------------------------------------
-- View `workoutdb`.`location_media_last_entered`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `workoutdb`.`location_media_last_entered` ;
DROP TABLE IF EXISTS `workoutdb`.`location_media_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`location_media_last_entered` AS
SELECT *
FROM location_media
WHERE id IN (SELECT max(id) FROM location_media GROUP BY location_id);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
