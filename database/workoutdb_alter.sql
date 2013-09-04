SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER TABLE `workoutdb`.`email_batch_user` DROP FOREIGN KEY `fk_email_batch_user_user1` ;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`action_permission` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `action_id` BIGINT(20) NOT NULL ,
  `user_id` BIGINT(20) NOT NULL ,
  `client_id` BIGINT(20) NULL DEFAULT NULL ,
  `location_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'if null, the permission is for all locations for the client.' ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Assign permission to actions at system, client, or location levels';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`action` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Take Payment, Make Refund, Manage Clerks, Manage Finance Permission, Manage Members';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`affiliation_type` (
  `id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `affiliation_typecol_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Staff, Member, Guest, Archive';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`billing_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `descrtiption` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Bill for a specific number of participations, Bill for participation during a period, Bill for a period.';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`cc_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_group_result` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `challenge_group_id` BIGINT(20) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_group` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `challenge_id` BIGINT(20) NOT NULL ,
  `phase_group_id` BIGINT(20) NOT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_individual_result` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `challenge_individual_id` BIGINT(20) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge_individual` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `challenge_id` BIGINT(20) NOT NULL ,
  `phase_individual_id` BIGINT(20) NOT NULL ,
  `challenge_group_id` BIGINT(20) NULL DEFAULT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`challenge` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `score_type_id` BIGINT(20) NOT NULL ,
  `score_calculation_type_id` BIGINT(20) NOT NULL ,
  `point_awarding_type_id` BIGINT(20) NOT NULL ,
  `start` INT(13) NULL DEFAULT NULL ,
  `end` INT(13) NULL DEFAULT NULL ,
  `max_team_size` INT(11) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_group` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `captain_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'if this competion is set for online registration, the group captain is the person in the group allowed to delete people from the group during the registration period.' ,
  `affiliated_gym` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Used to overwrite the list of gyms the competitors belong too.' ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_individual` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT(20) NOT NULL ,
  `user_id` BIGINT(20) NOT NULL ,
  `competition_group_id` BIGINT(20) NULL DEFAULT NULL ,
  `height` DECIMAL(8,2) NULL DEFAULT NULL ,
  `height_uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `weight` DECIMAL(8,2) NULL DEFAULT NULL ,
  `weight_uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `birthday` INT(13) NULL DEFAULT NULL ,
  `alias_name` VARCHAR(100) NULL DEFAULT NULL COMMENT 'if this field has a value, it will be used for the inividual\'s name.' ,
  `affiliated_gym` VARCHAR(100) NULL DEFAULT NULL COMMENT 'used if the competitor is not affiliated with a gym.' ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_ranking_attribute` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT(20) NOT NULL ,
  `ranking_attribute_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'individual or group';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`competition` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `client_id` BIGINT(20) NOT NULL COMMENT 'What Client is sponsoring the competition.' ,
  `user_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Who is the contact for the competition.' ,
  `competition_type_id` INT(11) NOT NULL ,
  `registration_type_id` BIGINT(20) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `closed_competition` TINYINT(4) NULL DEFAULT NULL ,
  `team_size_min` INT(11) NULL DEFAULT NULL ,
  `team_size_max` INT(11) NULL DEFAULT NULL ,
  `registration_start` INT(13) NULL DEFAULT NULL ,
  `registration_end` INT(13) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_calendar_entry_template` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `calendar_entry_template_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_calendar_event_participation` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `calendar_event_participation_id` BIGINT(20) NOT NULL ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'What contract was this participation covered under.';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_category` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_contract_category_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_contract_category_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_client_user` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `client_user_id` BIGINT(20) NOT NULL ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Which members are covered by the contract.';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_coach` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `client_user_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_contract_type_discount_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `contract_type_discount_type_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_location` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_id` BIGINT(20) NOT NULL ,
  `location_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_status` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Paid, Grace, Hold, Inactive';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_affiliation_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `affiliation_type_id` BIGINT(20) NOT NULL ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'contract can only be sold to this type of existing client_user';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_calendar_entry_template` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `calendar_entry_template_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_coach` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `client_user_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_contract_category` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `contract_category_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_discount_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `discount_type_id` BIGINT(20) NOT NULL ,
  `percent_discount` DECIMAL(5,2) NULL DEFAULT NULL ,
  `base_rate_discount` DECIMAL(8,2) NULL DEFAULT NULL ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'The discounts that are allowed to be used with this contract.';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type_location` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `location_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT(20) NOT NULL ,
  `prepay` TINYINT(4) NOT NULL COMMENT 'The member pays for services to be rendered (pre-paid) or for services already rendered (post-paid).' ,
  `billing_type_id` BIGINT(20) NOT NULL COMMENT 'Bill for set nunber of participations, Bill for participatiuons within period, Bill for period.' ,
  `billing_participation_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Checkins, Days of Participation' ,
  `billing_period` INT(11) NULL DEFAULT NULL COMMENT 'The number of period types that the bill is for.' ,
  `billing_priod_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `billing_offset` INT(11) NULL DEFAULT NULL COMMENT 'For pay_for_time_period and post paid pay_for_participation, bill on paid_thu date plus this offset.  For pre-paid pay for participationm, bill member when they have offset paid participations of less.' ,
  `base_rate` DECIMAL(8,2) NULL DEFAULT NULL COMMENT 'The base rate for the contract. No tax, no discounts.' ,
  `individual_rate` TINYINT(4) NULL DEFAULT NULL COMMENT 'the rate is for each individual on this contract or for the group as a whole.' ,
  `grace_period_offset` INT(11) NULL DEFAULT NULL COMMENT 'Inactivate the Member on the paid_thru date plus this offset.' ,
  `contract_period` INT(11) NULL DEFAULT NULL COMMENT 'The number of contract period types the contract is for.' ,
  `contract_priod_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `max_participation` INT(11) NULL DEFAULT NULL COMMENT 'How many participations  in the maximum participation period does the contract cover.' ,
  `max_participation_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Checkins, Days of Participation' ,
  `max_participation_period` INT(11) NULL DEFAULT NULL COMMENT 'The number of max participation period types max participation is within.' ,
  `max_participation_priod_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'D - Day, W - Week, M - Month, Y - Year, P - Participation' ,
  `max_client_users` INT(11) NULL DEFAULT NULL COMMENT 'The maximum number of client_users that can be covered on this type of contract' ,
  `max_coaches_limit` INT(11) NULL DEFAULT NULL COMMENT 'The maximum number of coaches this contract type can be limited too.  If NULL, it is not limited.' ,
  `max_location_limit` INT(11) NULL DEFAULT NULL COMMENT 'The maximum number of locations this contract type can be limited too.  If NULL, it is not limited.' ,
  `max_calendar_event_template_limit` INT(11) NULL DEFAULT NULL COMMENT 'The maximum number of class types this contract type can be limited too.  If NULL, it is not limited.' ,
  `recurring_required` TINYINT(4) NULL DEFAULT NULL COMMENT 'This contract requires the member be on recurring credit card payments.' ,
  `sell_online` TINYINT(4) NOT NULL COMMENT 'This contract can be sold online.' ,
  `created` INT(13) NOT NULL COMMENT 'The date the contract type was created in the system' ,
  `effective` INT(13) NULL DEFAULT NULL COMMENT 'The 1st day the contract can be sold.' ,
  `stop_assigning` INT(13) NULL DEFAULT NULL COMMENT 'Do not allow the contract to be sold after this date.' ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `signup_fee` DECIMAL(8,2) NULL DEFAULT NULL COMMENT 'a one time fee to signup for this contract type.' ,
  `cancelation_fee` DECIMAL(8,2) NULL DEFAULT NULL COMMENT 'A fee for when the customer wants to stop their membership before the end of the contract.' ,
  `hold_fee` DECIMAL(8,2) NULL DEFAULT NULL COMMENT 'The fee for putting the contract on hold (temporarily stopped).' ,
  `hold_allowed` TINYINT(4) NULL DEFAULT NULL COMMENT 'Can a member put this contract type on temporary hold?' ,
  `terms_and_conditions` TEXT NULL DEFAULT NULL ,
  `next_contract_type_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'null = One-Time-Only; Same = Recurring; Different = Chained.' ,
  `contract_status_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`contract` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `contract_type_id` BIGINT(20) NOT NULL ,
  `max_participation` INT(11) NULL DEFAULT NULL COMMENT 'The number of participations that have been paid for.' ,
  `paid_thru` INT(13) NULL DEFAULT NULL COMMENT 'The date the member has paid through.' ,
  `max_weekly_participation` INT(11) NULL DEFAULT NULL ,
  `contract_start` INT(13) NULL DEFAULT NULL ,
  `contract_end` INT(13) NULL DEFAULT NULL COMMENT 'The date the contract is good through.' ,
  `note` TEXT NULL DEFAULT NULL ,
  `next_contract_type_id` BIGINT(20) NULL DEFAULT NULL ,
  `created` INT(13) NULL DEFAULT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`discount_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `client_id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `discription` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_discount_type_client1` (`client_id` ASC) ,
  CONSTRAINT `fk_discount_type_client1`
    FOREIGN KEY (`client_id` )
    REFERENCES `workoutdb`.`client` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`gateway_account_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'creditCard, checking, savings, or businessChecking';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_settlement` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `getcube_settlement_id` BIGINT(20) NOT NULL ,
  `status` VARCHAR(45) NOT NULL ,
  `collected_amount` DECIMAL(8,2) NOT NULL COMMENT 'The amount collected for the transaction' ,
  `fee_amount` DECIMAL(8,2) NOT NULL COMMENT 'The fees on the transaction' ,
  `net_amount` DECIMAL(8,2) NOT NULL COMMENT 'The collected_amount minus the fee_amount. The amount deposited in to the bank.' ,
  `json` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`getcube_stored_payment` (
  `id` BIGINT(20) NOT NULL COMMENT 'getcube\'s stored_payment id' ,
  `guid` BIGINT(20) NOT NULL ,
  `client_user_id` BIGINT(20) NOT NULL ,
  `payment_method_id` BIGINT(20) NOT NULL ,
  `cc_type_id` BIGINT(20) NULL DEFAULT NULL ,
  `cc_redacted_number` VARCHAR(45) NULL DEFAULT NULL ,
  `cc_expire_mm` VARCHAR(2) NULL DEFAULT NULL ,
  `cc_expire_ccyy` VARCHAR(4) NULL DEFAULT NULL ,
  `cc_billing_zipcode` VARCHAR(10) NULL DEFAULT NULL ,
  `cc_name_on_card` VARCHAR(255) NULL DEFAULT NULL ,
  `created` INT(13) NOT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
  `updated` INT(13) NULL DEFAULT NULL ,
  `json` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_getcube_stored_payment_client_user1` (`client_user_id` ASC) ,
  INDEX `fk_getcube_stored_payment_payment_method1` (`payment_method_id` ASC) ,
  INDEX `fk_getcube_stored_payment_cc_type1` (`cc_type_id` ASC) ,
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
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'This is getcube\'s stored credit card';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`invoice_item` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `invoiice_id` BIGINT(20) NOT NULL ,
  `invoice_item_description` VARCHAR(100) NOT NULL ,
  `count` INT(11) NOT NULL ,
  `unit_price` DECIMAL(8,2) NOT NULL ,
  `total_price` DECIMAL(8,2) NOT NULL ,
  `tax_item` TINYINT(4) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_item` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `order_master_id` BIGINT(20) NOT NULL ,
  `description` TEXT NOT NULL ,
  `amount` DECIMAL(8,2) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL DEFAULT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_item_order_master1` (`order_master_id` ASC) ,
  CONSTRAINT `fk_order_item_order_master1`
    FOREIGN KEY (`order_master_id` )
    REFERENCES `workoutdb`.`order_master` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_master` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `getcube_order_master_id` BIGINT(20) NULL DEFAULT NULL ,
  `client_user_id` BIGINT(20) NOT NULL COMMENT 'The client user who is buying' ,
  `created` INT(13) NOT NULL ,
  `created_by_user_id` BIGINT(20) NOT NULL COMMENT 'The client user who is selling' ,
  `created_by_app` VARCHAR(20) NOT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
  `updated` INT(13) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`order_transaction` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `getcube_payment_id` INT(13) NULL DEFAULT NULL ,
  `order_master_id` BIGINT(20) NOT NULL ,
  `collected_amount` DECIMAL(8,2) NOT NULL COMMENT 'the amount charged to the customer' ,
  `fee_amount` DECIMAL(8,2) NOT NULL ,
  `is_refund` TINYINT(4) NOT NULL DEFAULT 0 ,
  `refunded_order_transaction_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'The link to the transaction being refunded by this transaction.' ,
  `payment_method_id` BIGINT(20) NOT NULL ,
  `check_number` VARCHAR(45) NULL DEFAULT NULL ,
  `getcube_stored_payment_id` BIGINT(20) NULL DEFAULT NULL ,
  `cc_type_id` BIGINT(20) NULL DEFAULT NULL ,
  `cc_redacted_number` VARCHAR(45) NULL DEFAULT NULL ,
  `cc_expire_mm` VARCHAR(2) NULL DEFAULT NULL ,
  `cc_expire_ccyy` VARCHAR(4) NULL DEFAULT NULL ,
  `cc_billing_zipcode` VARCHAR(10) NULL DEFAULT NULL ,
  `cc_name_on_card` VARCHAR(255) NULL DEFAULT NULL ,
  `order_transaction_settlement_id` BIGINT(20) NULL DEFAULT NULL ,
  `created` INT(13) NOT NULL ,
  `updated` INT(13) NULL DEFAULT NULL ,
  `deleted` INT(13) NULL DEFAULT NULL ,
  `json` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_order_transactions_order_master1` (`order_master_id` ASC) ,
  INDEX `fk_order_transactions_getcube_stored_payment1` (`getcube_stored_payment_id` ASC) ,
  INDEX `fk_order_transactions_payment_method1` (`payment_method_id` ASC) ,
  INDEX `fk_order_transactions_cc_type1` (`cc_type_id` ASC) ,
  INDEX `fk_order_transaction_order_transaction_settlement1` (`order_transaction_settlement_id` ASC) ,
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
    FOREIGN KEY (`order_transaction_settlement_id` )
    REFERENCES `workoutdb`.`getcube_settlement` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_transaction_order_transaction1`
    FOREIGN KEY (`refunded_order_transaction_id` )
    REFERENCES `workoutdb`.`order_transaction` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`participation_type` (
  `id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL COMMENT 'Checkins, Days of Participation' ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`payment_method` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL COMMENT '\'CreditCard\', \'Cash\', \'Check\', \'GiftCertificate\', \'DealRedemption\', or \'Other\'' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = '\'CreditCard\', \'Cash\', \'Check\', \'GiftCertificate\', \'DealRedemption\', or \'Other\'';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_group_result` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `phase_group_id` BIGINT(20) NOT NULL ,
  `created` INT(13) NOT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_group` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT(20) NOT NULL ,
  `competition_group_id` BIGINT(20) NOT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_individual_result` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `phase_individual_id` BIGINT(20) NOT NULL ,
  `created` INT(13) NULL DEFAULT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase_individual` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `phase_id` BIGINT(20) NOT NULL ,
  `competition_individual_id` BIGINT(20) NOT NULL ,
  `phase_group_id` BIGINT(20) NULL DEFAULT NULL ,
  `result` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NULL DEFAULT NULL ,
  `points` DECIMAL(8,2) NULL DEFAULT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`phase` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `competition_id` BIGINT(20) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_phase_competition1` (`competition_id` ASC) ,
  UNIQUE INDEX `unique_phase_competion_phase_name` (`competition_id` ASC, `name` ASC) ,
  CONSTRAINT `fk_phase_competition1`
    FOREIGN KEY (`competition_id` )
    REFERENCES `workoutdb`.`competition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`point_awarding_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`priod_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `abbreviation` VARCHAR(10) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `abbreviation_UNIQUE` (`abbreviation` ASC) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'D - Day, W - Week, M - Month, Y - Year, P - Participation';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute_by_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Rank attribute by attribute \"value\", \"value range\", ...';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute_range` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `ranking_attribute_id` BIGINT(20) NOT NULL ,
  `from` DECIMAL(8,2) NULL DEFAULT NULL ,
  `to` DECIMAL(8,2) NULL DEFAULT NULL ,
  `uom_id` BIGINT(20) NOT NULL ,
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE  TABLE IF NOT EXISTS `workoutdb`.`ranking_attribute` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  `rank_attribute_by_type_id` BIGINT(20) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_ranking_attribute_rank_attribute_by_type1` (`rank_attribute_by_type_id` ASC) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  CONSTRAINT `fk_ranking_attribute_rank_attribute_by_type1`
    FOREIGN KEY (`rank_attribute_by_type_id` )
    REFERENCES `workoutdb`.`ranking_attribute_by_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'age, weight, height, . . .\ngender and competition type (group/individual) are automatic categories that do not need ranking_type entries';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`registration_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Online, coach, ...';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`score_calculation_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'total, average, best of ##';

CREATE  TABLE IF NOT EXISTS `workoutdb`.`score_type` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Checkins, Days checked in, Workouts logged, Log results, Power, Load ...';

ALTER TABLE `workoutdb`.`calendar_entry_template_wod_library_workout` 
  ADD CONSTRAINT `fk_calendar_entry_template_wod_library_workout_calendar_entry1`
  FOREIGN KEY (`calendar_entry_template_wod_id` )
  REFERENCES `workoutdb`.`calendar_entry_template_wod` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`calendar_entry_template_wod` 
  ADD CONSTRAINT `fk_calendar_entry_template_wod_calendar_entry_template1`
  FOREIGN KEY (`calendar_entry_template_id` )
  REFERENCES `workoutdb`.`calendar_entry_template` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`calendar_event_library_workout` 
  ADD CONSTRAINT `fk_calendar_event_library_workout_library_workout1`
  FOREIGN KEY (`library_workout_id` )
  REFERENCES `workoutdb`.`library_workout` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`calendar_event` CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL  ;

ALTER TABLE `workoutdb`.`client_user` DROP COLUMN `client_id` , DROP COLUMN `user_id` , ADD COLUMN `affiliation_type_id` BIGINT(20) NOT NULL  AFTER `note` , 
  ADD CONSTRAINT `fk_client_user_affiliation_type1`
  FOREIGN KEY (`affiliation_type_id` )
  REFERENCES `workoutdb`.`affiliation_type` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, ADD INDEX `fk_client_user_affiliation_type1` (`affiliation_type_id` ASC) 
, DROP INDEX `fk_membership_client1` 
, DROP INDEX `fk_membership_user1` 
, DROP INDEX `uq_client_user1` ;

ALTER TABLE `workoutdb`.`client` ADD COLUMN `getcube_user_master_id` BIGINT(20) NULL DEFAULT NULL  AFTER `widget_token` , CHANGE COLUMN `widget_token` `widget_token` VARCHAR(100) NOT NULL COMMENT 'This token is used by a web widget to gain access to the system without logging in.'  , 
  ADD CONSTRAINT `fk_client_getcube_user_master1`
  FOREIGN KEY (`getcube_user_master_id` )
  REFERENCES `workoutdb`.`getcube_user_master` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, ADD INDEX `fk_client_getcube_user_master1` (`getcube_user_master_id` ASC) 
, DROP INDEX `widget_)token_UNIQUE` 
, ADD UNIQUE INDEX `widget_token_UNIQUE` (`widget_token` ASC) ;

ALTER TABLE `workoutdb`.`getcube_user_master` DROP COLUMN `client_user_id` , CHANGE COLUMN `email` `email` VARCHAR(255) NOT NULL  , CHANGE COLUMN `password` `password` VARCHAR(45) NOT NULL  
, DROP INDEX `fk_getcube_user_client_user1` ;

ALTER TABLE `workoutdb`.`library_body_region_body_part` 
  ADD CONSTRAINT `fk_library_body_region_body_part_library_body_region1`
  FOREIGN KEY (`library_body_region_id` )
  REFERENCES `workoutdb`.`library_body_region` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_equipment_measurement` DROP FOREIGN KEY `fk_library_equipment_measurement_library_equipment1` ;

ALTER TABLE `workoutdb`.`library_equipment_measurement` 
  ADD CONSTRAINT `fk_library_equipment_measurement_library_equipment1`
  FOREIGN KEY (`library_equipment_id` )
  REFERENCES `workoutdb`.`library_equipment` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_exercise_equipment` 
  ADD CONSTRAINT `fk_library_exercise_equipment_library_exercise10`
  FOREIGN KEY (`library_exercise_id` )
  REFERENCES `workoutdb`.`library_exercise` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_exercise_exercise_media` 
  ADD CONSTRAINT `fk_library_exercise_exercise_media_library_exercise1`
  FOREIGN KEY (`library_exercise_id` )
  REFERENCES `workoutdb`.`library_exercise` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_exercise_exercise_type` 
  ADD CONSTRAINT `fk_library_exercise_exercise_level_library_exercise11`
  FOREIGN KEY (`library_exercise_id` )
  REFERENCES `workoutdb`.`library_exercise` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_exercise_sport_type` 
  ADD CONSTRAINT `fk_library_exercise_exercise_level_library_exercise10`
  FOREIGN KEY (`library_exercise_id` )
  REFERENCES `workoutdb`.`library_exercise` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_workout_library_equipment` 
  ADD CONSTRAINT `fk_library_workout_library_equipment_library_workout1`
  FOREIGN KEY (`library_workout_id` )
  REFERENCES `workoutdb`.`library_workout` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_workout_library_exercise` 
  ADD CONSTRAINT `fk_library_workout_library_exercise_library_workout1`
  FOREIGN KEY (`library_workout_id` )
  REFERENCES `workoutdb`.`library_workout` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`user` DROP COLUMN `anonymous_on_leaderboard` , ADD COLUMN `anonymous_on_leaderboard` TINYINT(4) NULL DEFAULT 0 COMMENT 'Use \"anonymous\" on the leader board for the user\'s name.'  AFTER `last_login` , CHANGE COLUMN `gender` `gender` VARCHAR(2) NULL DEFAULT NULL COMMENT 'M or F'  , CHANGE COLUMN `phone` `phone` VARCHAR(10) NULL DEFAULT NULL  , CHANGE COLUMN `password` `password` VARCHAR(32) NULL DEFAULT NULL  , CHANGE COLUMN `timezone` `timezone` VARCHAR(45) NULL DEFAULT NULL  , CHANGE COLUMN `username` `username` VARCHAR(100) NULL DEFAULT NULL  , CHANGE COLUMN `first_name` `first_name` VARCHAR(100) NOT NULL  , CHANGE COLUMN `last_name` `last_name` VARCHAR(100) NOT NULL  , CHANGE COLUMN `email` `email` VARCHAR(255) NOT NULL  , CHANGE COLUMN `address` `address` VARCHAR(255) NULL DEFAULT NULL  , CHANGE COLUMN `token` `token` VARCHAR(10) NULL DEFAULT NULL  , CHANGE COLUMN `about_me` `about_me` TEXT NULL DEFAULT NULL  ;

ALTER TABLE `workoutdb`.`workout_log_library_equipment` 
  ADD CONSTRAINT `fk_workout_log_library_equipment_workout_log1`
  FOREIGN KEY (`workout_log_id` )
  REFERENCES `workoutdb`.`workout_log` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`workout_log_library_exercise` 
  ADD CONSTRAINT `fk_table1_workout_log1`
  FOREIGN KEY (`workout_log_id` )
  REFERENCES `workoutdb`.`workout_log` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`workout_log` DROP COLUMN `auto_calculate_result` , ADD COLUMN `auto_calculated_result` TINYINT(4) NULL DEFAULT 0 COMMENT 'Were the results auto-calculated yes (1) or no (0).'  AFTER `time_limit_note` , 
  ADD CONSTRAINT `fk_participation_log_library_workout1`
  FOREIGN KEY (`library_workout_id` )
  REFERENCES `workoutdb`.`library_workout` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_workout_log_calendar_event_participation1`
  FOREIGN KEY (`calendar_event_participation_id` )
  REFERENCES `workoutdb`.`calendar_event_participation` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_workout_log_library_measurement_system_unit1`
  FOREIGN KEY (`result_uom_id` )
  REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_workout_log_library_measurement_system_unit2`
  FOREIGN KEY (`time_limit_uom_id` )
  REFERENCES `workoutdb`.`library_measurement_system_unit` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_workout_log_library_workout_recording_type1`
  FOREIGN KEY (`library_workout_recording_type_id` )
  REFERENCES `workoutdb`.`library_workout_recording_type` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_workout_log_user1`
  FOREIGN KEY (`user_id` )
  REFERENCES `workoutdb`.`user` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`location` 
  ADD CONSTRAINT `fk_location_client1`
  FOREIGN KEY (`client_id` )
  REFERENCES `workoutdb`.`client` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION, 
  ADD CONSTRAINT `fk_location_member1`
  FOREIGN KEY (`client_user_id` )
  REFERENCES `workoutdb`.`client_user` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`library_measurement_system_unit` DROP COLUMN `english_conversion` , DROP COLUMN `metric_conversion` , ADD COLUMN `metic_conversion` DECIMAL(20,10) NOT NULL DEFAULT 1  AFTER `description` , ADD COLUMN `english_conversion` DECIMAL(20,10) NOT NULL DEFAULT 1  AFTER `description` ;

ALTER TABLE `workoutdb`.`calendar_event_participation` DROP COLUMN `created_by_user_id` , ADD COLUMN `created_by_user_id` BIGINT(20) NULL DEFAULT NULL  AFTER `created_by_app` , CHANGE COLUMN `created` `created` INT(13) NOT NULL  
, DROP INDEX `fk_calendar_event_participation_user1` 
, ADD INDEX `fk_calendar_event_participation_user1` (`created_by_user_id` ASC) ;

ALTER TABLE `workoutdb`.`email_batch_user` 
  ADD CONSTRAINT `fk_email_batch_user_user1`
  FOREIGN KEY (`user_id` )
  REFERENCES `workoutdb`.`user` (`id` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `workoutdb`.`email_tag_email` 
ADD UNIQUE INDEX `uq_email_tag_email` (`email_id` ASC, `email_tag_id` ASC) ;

ALTER TABLE `workoutdb`.`email_tag_email_batch` 
ADD UNIQUE INDEX `uq_email_tag_email_batch` (`email_batch_id` ASC, `email_tag_id` ASC) ;


-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`challenge_group_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`challenge_group_detail` (`competitition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `client_id` INT, `competition_id` INT, `phase_id` INT, `challenge_id` INT, `group_captain_id` INT, `group_name` INT, `group_affiliated_gym` INT, `result` INT, `uom_id` INT, `points` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`challenge_individual_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`challenge_individual_detail` (`client_user_id` INT, `client_id` INT, `user_id` INT, `competition_individual_id` INT, `phase_individual_id` INT, `challenge_individual_id` INT, `competition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `competition_id` INT, `phase_id` INT, `challenge_id` INT, `first_name` INT, `last_name` INT, `email` INT, `height` INT, `height_uom_id` INT, `weight` INT, `weight_uom_id` INT, `birthday` INT, `alias_name` INT, `affiliated_gym` INT, `group_name` INT, `group_affiliated_gym` INT, `challenge_name` INT, `score_type_id` INT, `score_calculation_type_id` INT, `point_awarding_type_id` INT, `challenge_start` INT, `challenge_end` INT, `max_team_size` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`client_user_calendar_event_participation_last_entered`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered` (`id` INT, `client_user_id` INT, `calendar_event_id` INT, `email_reminder_sent` INT, `email_reminder_opened` INT, `start_emotional_level_id` INT, `end_emotional_level_id` INT, `created` INT, `created_by_app` INT, `created_by_user_id` INT, `note` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`competition_detail`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`competition_detail` (`competition_id` INT, `phase_id` INT, `challenge_id` INT, `competition_individual_id` INT, `phase_individual_id` INT, `challenge_individual_id` INT, `competition_group_id` INT, `phase_group_id` INT, `challenge_group_id` INT, `client_id` INT, `user_id` INT, `competition_name` INT, `competition_type_id` INT, `registration_type_id` INT, `closed_competition` INT, `competition_min_team_size` INT, `competition_max_team_size` INT, `registration_start` INT, `registration_end` INT, `group_name` INT, `group_affiliated_gym` INT, `phase_name` INT, `challenge_name` INT, `score_type_id` INT, `score_calculation_type_id` INT, `point_awarding_type_id` INT, `challenge_start` INT, `challenge_end` INT, `challenge_max_team_size` INT, `first_name` INT, `last_name` INT, `email` INT, `height` INT, `height_uom_id` INT, `weight` INT, `weight_uom_id` INT, `birthday` INT, `alias_name` INT, `affiliated_gym` INT);

-- -----------------------------------------------------
-- Placeholder table for view `workoutdb`.`competition_stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `workoutdb`.`competition_stats` (`id` INT, `name` INT, `client_id` INT, `user_id` INT, `competition_type_id` INT, `registration_type_id` INT, `created` INT, `deleted` INT, `description` INT, `closed_competition` INT, `team_size_min` INT, `team_size_max` INT, `registration_start` INT, `registration_end` INT, `phase_count` INT, `challenge_count` INT, `group_count` INT, `individual_count` INT, `start` INT, `end` INT);

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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`challenge_group_detail`
-- -----------------------------------------------------
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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`challenge_individual_detail`
-- -----------------------------------------------------
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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_calendar_event_participation_last_entered`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_calendar_event_participation_last_entered`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_calendar_event_participation_last_entered` AS
SELECT *
FROM calendar_event_participation
WHERE id IN (SELECT max(id) FROM calendar_event_participation GROUP BY client_user_id);


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`competition_detail`
-- -----------------------------------------------------
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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`competition_stats`
-- -----------------------------------------------------
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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_guest`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_guest`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_guest` AS
SELECT *
FROM client_user_role
WHERE name = "Guest";


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_member`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_member`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_member` AS
SELECT *
FROM client_user_role
WHERE name = "Member";


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_review`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_review`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_review` AS
SELECT *
FROM client_user_role
WHERE name = "Review";


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`client_user_role_trial`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`client_user_role_trial`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`client_user_role_trial` AS
SELECT *
FROM client_user_role
WHERE name = "Trial";


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`library_workout_assigned`
-- -----------------------------------------------------
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


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`num_users_logged_workout`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`num_users_logged_workout`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`num_users_logged_workout` AS
SELECT l.library_workout_id, COUNT(DISTINCT l.user_id) number_of_users
FROM workout_log l
WHERE l.workout_log_completed
GROUP BY l.library_workout_id;


USE `workoutdb`;

-- -----------------------------------------------------
-- View `workoutdb`.`workout_logged_by_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `workoutdb`.`workout_logged_by_user`;
USE `workoutdb`;
CREATE  OR REPLACE VIEW `workoutdb`.`workout_logged_by_user` AS
SELECT l.user_id, l.library_workout_id, count(l.id) count, max(start) start
FROM workout_log l
WHERE l.workout_log_completed
GROUP BY l.user_id, l.library_workout_id;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
