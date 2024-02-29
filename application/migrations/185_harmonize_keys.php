<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_harmonize_keys extends CI_Migration {

	public function up() {
		$this->db->query("update ".$this->config->item('table_name')." set station_id=0 where station_id is null;");
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." ENGINE=InnoDB;");
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." CHANGE COLUMN `station_id` `station_id` INT(10) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." CHANGE COLUMN `COL_PRIMARY_KEY` `COL_PRIMARY_KEY` BIGINT(20) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `station_profile` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `users` CHANGE COLUMN `user_id` `user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;");
		$this->db->query("ALTER TABLE `station_logbooks_relationship` CHANGE COLUMN `logbook_relation_id` `logbook_relation_id` INT UNSIGNED NOT NULL, CHANGE COLUMN `station_logbook_id` `station_logbook_id` INT UNSIGNED NOT NULL, CHANGE COLUMN `station_location_id` `station_location_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `station_logbooks` CHANGE COLUMN `logbook_id` `logbook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `user_options` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `oqrs` CHANGE COLUMN `station_id` `station_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `notes` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `lotw_certs` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `label_types` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `paper_types` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `eQSL_images` CHANGE COLUMN `qso_id` `qso_id` BIGINT(20) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `contest_session` CHANGE COLUMN `station_id` `station_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `qsl_images` CHANGE COLUMN `qsoid` `qsoid` BIGINT(20) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `contest_session` CHANGE COLUMN `station_id` `station_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `cat` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `api` CHANGE COLUMN `user_id` `user_id` INT UNSIGNED NOT NULL;");


	}

	public function down(){
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." ENGINE=MyISAM;");
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." CHANGE COLUMN `station_id` `station_id` INT(11) NOT NULL;");
		$this->db->query("ALTER TABLE ".$this->config->item('table_name')." CHANGE COLUMN `COL_PRIMARY_KEY` `COL_PRIMARY_KEY` INT UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `station_profile` CHANGE COLUMN `user_id` `user_id` BIGINT(20) DEFAULT NULL;");
		$this->db->query("ALTER TABLE `station_logbooks_relationship` CHANGE COLUMN `logbook_relation_id` `logbook_relation_id` BIGINT(20) NOT NULL, CHANGE COLUMN `station_logbook_id` `station_logbook_id` BIGINT(20) NOT NULL, CHANGE COLUMN `station_location_id` `station_location_id` BIGINT(20) NOT NULL;");
		$this->db->query("ALTER TABLE `station_logbooks` CHANGE COLUMN `logbook_id` `logbook_id` BIGINT(20) NOT NULL AUTO_INCREMENT, CHANGE COLUMN `user_id` `user_id` BIGINT(20) NOT NULL;");
		$this->db->query("ALTER TABLE `user_options` CHANGE COLUMN `user_id` `user_id` INT NOT NULL;");
		$this->db->query("ALTER TABLE `oqrs` CHANGE COLUMN `station_id` `station_id` INT NOT NULL;");
		$this->db->query("ALTER TABLE `notes` CHANGE COLUMN `user_id` `user_id` BIGINT(20) NULL DEFAULT NULL;");
		$this->db->query("ALTER TABLE `lotw_certs` CHANGE COLUMN `user_id` `user_id` INT NULL DEFAULT NULL;");
		$this->db->query("ALTER TABLE `label_types` CHANGE COLUMN `user_id` `user_id` INT(5) NULL DEFAULT NULL;");
		$this->db->query("ALTER TABLE `paper_types` CHANGE COLUMN `user_id` `user_id` INT(5) NULL DEFAULT NULL;");
		$this->db->query("ALTER TABLE `eQSL_images` CHANGE COLUMN `qso_id` `qso_id` VARCHAR(250) NOT NULL;");
		$this->db->query("ALTER TABLE `contest_session` CHANGE COLUMN `station_id` `station_id` BIGINT(20) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `qsl_images` CHANGE COLUMN `qsoid` `qsoid` INT NULL;");
		$this->db->query("ALTER TABLE `contest_session` CHANGE COLUMN `station_id` `station_id` BIGINT(20) UNSIGNED NOT NULL;");
		$this->db->query("ALTER TABLE `cat` CHANGE COLUMN `user_id` `user_id` BIGINT(20) NULL;");
		$this->db->query("ALTER TABLE `api` CHANGE COLUMN `user_id` `user_id` BIGINT(20) NULL;");
		$this->db->query("ALTER TABLE `users` CHANGE COLUMN `user_id` `user_id` INT(11) NOT NULL AUTO_INCREMENT;");
	}
}
