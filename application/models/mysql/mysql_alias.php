<?php

class mysql_alias extends CI_Model {
	
	private static $database;
	
	public function __construct() {
		// echo "-mysql_alias start-";
		
		parent::__construct();
		
		$this->loadDatabaseAlias();
		
		// echo "-mysql_alias end-";
	}
	
	private function loadDatabaseAlias() {
		self::$database = new stdClass();
		self::$database->workoutdb = new stdClass();
		
		// database table alias
		self::$database->workoutdb->table = new stdClass();
		self::$database->workoutdb->table->library_body_part = "body_part";
		self::$database->workoutdb->table->library_body_region = "body_region";
		self::$database->workoutdb->table->library_equipment = "equipment";
		self::$database->workoutdb->table->library_exercise = "exercise";
		self::$database->workoutdb->table->library_exercise_level = "exercise_level";
		self::$database->workoutdb->table->library_exercise_type = "exercise_type";
		self::$database->workoutdb->table->library_measurement = "measurement";
		self::$database->workoutdb->table->library_measurement_system = "system";
		self::$database->workoutdb->table->library_measurement_system_unit = "unit";
		self::$database->workoutdb->table->library_sport_type = "sport_type";
		self::$database->workoutdb->table->library_workout = "workout";
		self::$database->workoutdb->table->library_workout_recording_type = "workout_recording_type";
		self::$database->workoutdb->table->library_workout_log = "workout_log";
		self::$database->workoutdb->table->calendar_entry = "entry";
		self::$database->workoutdb->table->calendar_entry_type = "entry_type";
		self::$database->workoutdb->table->calendar_entry_repeat_type = "repeat_type";
		self::$database->workoutdb->table->calendar_entry_template = "template";
		self::$database->workoutdb->table->calendar_event_participation = "participation";
		self::$database->workoutdb->table->client_user_role = "role";
		self::$database->workoutdb->table->emotional_level = "emotional_scale";
		
		// database wide column alias
		self::$database->workoutdb->column = new stdClass();
		self::$database->workoutdb->column->created = "create_date";
		self::$database->workoutdb->column->deleted = "delete_date";
		self::$database->workoutdb->column->description = "desc";
		self::$database->workoutdb->column->media_url = "url";
		self::$database->workoutdb->column->json_workout = "workout";
		self::$database->workoutdb->column->json_workout_display = "workout_summary";
		self::$database->workoutdb->column->json_log = "custom_workout";
		self::$database->workoutdb->column->json_log_flat = "workoutlog_flattenData";
		self::$database->workoutdb->column->json_log_display = "workoutlog_summary";
		self::$database->workoutdb->column->start_emotional_level_id = "start_emotional_scale_id";
		self::$database->workoutdb->column->end_emotional_level_id = "end_emotional_scale_id";
		
		// Create column aliases based on the table aliases for the linking ids
		foreach ( self::$database->workoutdb->table as $key => $value ) {
			self::$database->workoutdb->column->{$key . '_id'} = $value . '_id';
		}
	}
	
	public static function get_table($p_database,$p_table) {
		if ( property_exists(self::$database,$p_database) ) {
			if ( property_exists(self::$database->{$p_database},'table') && 
			     property_exists(self::$database->{$p_database}->table,$p_table) ) {
				return self::$database->{$p_database}->table->{$p_table};
			}
		}
		return $p_table;
	}

	public static function get_column($p_database,$p_table,$p_column) {
		if ( property_exists(self::$database,$p_database) ) {
			/*
			if ( property_exists(self::$database->{$p_database},'table') && 
			     property_exists(self::$database->{$p_database}->table,$p_table) && 
			     property_exists(self::$database->{$p_database}->table->{$p_table},'column') && 
			     property_exists(self::$database->{$p_database}->table->{$p_table}->column,$p_column) ) {
				return self::$database->{$p_database}->table->{$p_table}->column->{$p_column};
			}
			*/
			if ( property_exists(self::$database->{$p_database},'column') &&
			     property_exists(self::$database->{$p_database}->column,$p_column) ) {
				return self::$database->{$p_database}->column->{$p_column};
			}
		}
		return $p_column;
	}
}
