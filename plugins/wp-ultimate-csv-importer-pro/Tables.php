<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\WCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class Tables {

	private static $instance = null;
	private static $smack_csv_instance = null;

	public static function getInstance() {
		if (Tables::$instance == null) {
			Tables::$instance = new Tables;
			Tables::$smack_csv_instance = SmackCSV::getInstance();
			Tables::$instance->create_tables();
			return Tables::$instance;
		}
		return Tables::$instance;
	}

	public function create_tables(){
		global $wpdb;

		//updated woo attribute 
		$get_pro_settings = get_option("sm_uci_pro_settings");
		$get_pro_settings['woocomattr'] = 'true';
		update_option('sm_uci_pro_settings', $get_pro_settings);
	
		$file_table_name = $wpdb->prefix ."smackcsv_file_events";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $file_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`mode` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`total_rows` INT(11) NOT NULL,
			`lock` BOOLEAN DEFAULT false,
			`progress` INT(6)) ENGINE=InnoDB" 
				);
			
		$image_media_table =  $wpdb->prefix ."ultimate_csv_importer_media_report";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $image_media_table (

			`hash_key` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) DEFAULT 'pending',
			`module` VARCHAR(255) DEFAULT NULL,
			`image_type` VARCHAR(255) DEFAULT NULL,
			`success_count` VARCHAR(255) DEFAULT 0,
			`fail_count` VARCHAR(255) DEFAULT 0
				) ENGINE=InnoDB"
				);

		$shortcode_table_name =  $wpdb->prefix ."ultimate_csv_importer_shortcode_manager";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $shortcode_table_name (
			`post_id` INT(6),
			`image_shortcode` VARCHAR(255) NOT NULL,
			`original_image` TEXT NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`image_meta` TEXT DEFAULT NULL,
			`status` VARCHAR(255) DEFAULT 'pending'
				) ENGINE=InnoDB"
				);

		$schedule_import_table = $wpdb->prefix ."ultimate_csv_importer_scheduled_import";
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS $schedule_import_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`templateid` int(10) NOT NULL,
			`importid` int(10) NOT NULL,
			`createdtime` datetime NOT NULL,
			`updatedtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`isrun` int(1) DEFAULT '0',
			`scheduledtimetorun` varchar(10) NOT NULL,
			`scheduleddate` date NOT NULL,
			`module` varchar(100) NOT NULL,
			`file_type` varchar(10) NOT NULL,
			`response` blob,
			`version` varchar(10) DEFAULT NULL,
			`event_key` varchar(100) DEFAULT NULL,
			`importbymethod` varchar(60) DEFAULT NULL,
			`import_limit` int(11) DEFAULT '1',
			`import_row_ids` blob default NULL,
			`frequency` int(5) DEFAULT '0',
			`start_limit` int(11) DEFAULT '0',
			`end_limit` int(11) DEFAULT '0',
			`lastrun` datetime DEFAULT '0000-00-00 00:00:00',
			`nexrun` datetime DEFAULT '0000-00-00 00:00:00',
			`scheduled_by_user` varchar(10) DEFAULT '1',
			`cron_status` varchar(30) DEFAULT 'pending',
			`import_mode` varchar(100) NOT NULL,
			`duplicate_headers` blob DEFAULT NULL,
			`time_zone` varchar(100) DEFAULT NULL,
			`hook_name` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)) ENGINE=InnoDB"
				);  

		$schedule_export_table = $wpdb->prefix ."ultimate_csv_importer_scheduled_export";
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS $schedule_export_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`module` varchar(100) NOT NULL,
			`export_mode` varchar(20) NOT NULL,
			`optionalType` varchar(100) DEFAULT NULL,
			`conditions` blob,
			`exclusions` blob,
			`file_name` varchar(100),
			`isrun` int(1) DEFAULT '0',
			`scheduleddate` date NOT NULL,
			`frequency` int(5) DEFAULT '0',
			`exportbymethod` varchar(60) DEFAULT NULL,
			`scheduledtimetorun` varchar(10) NOT NULL,
			`host_name` varchar(160),
			`host_port` int(5),
			`host_username` varchar(160),
			`host_password` varchar(160),
			`host_path` varchar(300),
			`file_type` varchar(10) NOT NULL,
			`start_limit` int(11) DEFAULT '0',
			`end_limit` int(11) DEFAULT '1000',
			`lastrun` datetime DEFAULT '0000-00-00 00:00:00',
			`nexrun` datetime DEFAULT '0000-00-00 00:00:00',
			`scheduled_by_user` varchar(10) DEFAULT '1',
			`cron_status` varchar(15) DEFAULT 'pending',
			`custom_options` blob DEFAULT NULL,
			`createdtime` datetime NOT NULL,
			`updatedtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`time_zone` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$ftp_schedule_table = $wpdb->prefix . "ultimate_csv_importer_ftp_schedules";
		$table = $wpdb->query( "
				CREATE TABLE IF NOT EXISTS $ftp_schedule_table (
					`id` int(10) NOT NULL AUTO_INCREMENT,
					`schedule_id` int(10) NOT NULL,
					`hostname` varchar(110) DEFAULT NULL,
					`username` varchar(110) DEFAULT NULL,
					`password` varchar(110) DEFAULT NULL,
					`initial_path` varchar(225) DEFAULT NULL,
					`filename` varchar(110) DEFAULT NULL,
					`port_no` int(5) DEFAULT NULL,
					`hosttype` varchar(110) DEFAULT NULL,
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB");

		$external_url_schedule = $wpdb->prefix . "ultimate_csv_importer_external_file_schedules";
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS $external_url_schedule (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`schedule_id` int(10) NOT NULL,
			`file_url` varchar(255) DEFAULT NULL,
			`filename` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB");
			
		$template_table_name = $wpdb->prefix ."ultimate_csv_importer_mappingtemplate";
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS $template_table_name (
			`id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`templatename` varchar(250) NOT NULL,
			`mapping` blob NOT NULL,
			`createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`deleted` int(1) DEFAULT '0',
			`templateused` int(10) DEFAULT '0',
			`mapping_type` varchar(30),
			`module` varchar(50) DEFAULT NULL,
			`csvname` varchar(250) DEFAULT NULL,
			`eventKey` varchar(60) DEFAULT NULL				
				) ENGINE = InnoDB "
				);  


		$export_template_table_name = $wpdb->prefix ."ultimate_csv_importer_export_template";
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS $export_template_table_name (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`filename` varchar(250) NOT NULL,
			`module` varchar(50) DEFAULT NULL,
			`optional_type` varchar(50) DEFAULT NULL,
			`export_type` varchar(50) DEFAULT NULL,
			`split` varchar(50) DEFAULT NULL,
			`split_limit` int(50) DEFAULT NULL,
			`category_name` varchar(50) DEFAULT NULL,
			`conditions` blob DEFAULT NULL,
			`event_exclusions` blob DEFAULT NULL,
			`export_mode` varchar(50) DEFAULT 'normal',
			`createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`offset` int(50) DEFAULT 0,	
			`actual_start_date` varchar(250) DEFAULT NULL,
			`actual_end_date` varchar(250) DEFAULT NULL,
			`actual_schedule_date` varchar(250) DEFAULT NULL,
			PRIMARY KEY (`id`)		
			) ENGINE=InnoDB"
		);  


		$log_table_name = $wpdb->prefix ."import_detail_log";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $log_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`total_records` INT(6),
			`processing_records` INT(6) default 0,
			`remaining_records` INT(6) default 0,
			`filesize` VARCHAR(255) NOT NULL,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0,
			`running` boolean not null default 1
				) ENGINE=InnoDB" 
				);

		$import_records_table = "smackuci_events";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $import_records_table (
			`id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`revision` bigint(20) NOT NULL default 0,
			`name` varchar(255),
			`original_file_name` varchar(255),
			`friendly_name` varchar(255),
			`import_type` varchar(32),
			`filetype` text,
			`filepath` text,
			`eventKey` varchar(32),
			`registered_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`parent_node` varchar(255),
			`processing` tinyint(1) NOT NULL default 0,
			`executing` tinyint(1) NOT NULL default 0,
			`triggered` tinyint(1) NOT NULL default 0,
			`event_started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`count` bigint(20) NOT NULL default 0,
			`processed` bigint(20) NOT NULL default 0,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0,
			`deleted` bigint(20) NOT NULL default 0,
			`is_terminated` tinyint(1) NOT NULL default 0,
			`terminated_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
			`siteid` int(11) NOT NULL DEFAULT 1,
			`month` varchar(60) DEFAULT NULL,
			`year` varchar(60) DEFAULT NULL,
			`deletelog` BOOLEAN DEFAULT false
				) ENGINE=InnoDB"
				);

		$custom_fields_table = "smack_field_types";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $custom_fields_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`choices` varchar(160) NOT NULL,
			`fieldType` varchar(100) NOT NULL,
			`groupType` varchar(100) NOT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$acf_fields_table = $wpdb->prefix ."ultimate_csv_importer_acf_fields";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $acf_fields_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`groupId` varchar(100) NOT NULL,
			`fieldId` varchar(100) NOT NULL,
			`fieldLabel` varchar(100) NOT NULL,
			`fieldName` varchar(100) NOT NULL,
			`fieldType` varchar(60) NOT NULL,
			`fdOption` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$post_entries_table = $wpdb->prefix ."post_entries_table";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $post_entries_table (
					`ID` INT(6),
					`file_name` varchar(255) DEFAULT NULL,
					`type` varchar(255) DEFAULT NULL,
					`revision` INT(6),
					`status` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB"
					);


		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}import_detail_log` LIKE 'running'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}import_detail_log` ADD COLUMN running boolean not null default 1");
		}

		$shortcode_result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'image_meta'");
		if($shortcode_result == 0){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` ADD COLUMN image_meta TEXT DEFAULT NULL;");
		}

		$schedule_result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_scheduled_import` LIKE 'hook_name'");
		if($schedule_result == 0){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_csv_importer_scheduled_import` ADD COLUMN hook_name varchar(100) DEFAULT NULL;");
		}

		$image_shortcode_result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` LIKE 'original_image'");
		if($image_shortcode_result){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_csv_importer_shortcode_manager` MODIFY original_image TEXT NOT NULL;");
		}

		/** Added deletelog column for log manager deletion available from ultimate csv pro version 6.4  */
		$logmanager_result = $wpdb->query("SHOW COLUMNS FROM smackuci_events LIKE 'deletelog'");
		if($logmanager_result == 0){
			$wpdb->query("ALTER table smackuci_events ADD COLUMN deletelog BOOLEAN DEFAULT false;");
		}
	}
}
