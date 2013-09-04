<?php

class action_metadata extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get the metadata for the web app
	// ==================================================================================================================
	
	public function getAll( $p_use_alias=true ) {
		$library_measurement = $this->perform('action_uom->get',$p_use_alias);
		if ( $library_measurement['status'] >= 300 ) {
			return $library_measurement;
		}
		// echo "library_measurement:"; print_r($library_measurement);

		$library_workout_recording_type = $this->perform('table_workoutdb_library_workout_recording_type->getSearchList');
		if ( $library_workout_recording_type['status'] >= 300 ) {
			return $library_workout_recording_type;
		}
		// echo "library_workout_recording_type:"; print_r($library_workout_recording_type);
		
		$library_exercise_level = $this->perform('table_workoutdb_library_exercise_level->getSearchList');
		if ( $library_exercise_level['status'] >= 300 ) {
			return $library_exercise_level;
		}
		// echo "library_exercise_level:"; print_r($library_exercise_level);
		
		$library_exercise_type = $this->perform('table_workoutdb_library_exercise_type->getSearchList');
		if ( $library_exercise_type['status'] >= 300 ) {
			return $library_exercise_type;
		}
		// echo "library_exercise_type:"; print_r($library_exercise_type);
		
		$library_sport_type = $this->perform('table_workoutdb_library_sport_type->getSearchList');
		if ( $library_sport_type['status'] >= 300 ) {
			return $library_sport_type;
		}
		//  echo "library_sport_type:"; print_r($library_sport_type);
		
		$library_body_region = $this->perform('action_body_region_part->getAll',$p_use_alias);
		if ( $library_body_region['status'] >= 300 ) {
			return $library_body_region;
		}
		// echo "library_body_region:"; print_r($library_body_region);
		
		$client_user_role = $this->perform('table_workoutdb_client_user_role->getSearchList');
		if ( $client_user_role['status'] >= 300 ) {
			return $client_user_role;
		}
		//echo "client_user_role:"; print_r($client_user_role);
		
		$calendar_entry_type = $this->perform('table_workoutdb_calendar_entry_type->getSearchList');
		if ( $calendar_entry_type['status'] >= 300 ) {
			return $calendar_entry_type;
		}
		
		$calendar_entry_repeat_type = $this->perform('table_workoutdb_calendar_entry_repeat_type->getSearchList');
		if ( $calendar_entry_repeat_type['status'] >= 300 ) {
			return $calendar_entry_repeat_type;
		}
		//echo "calendar_entry_repeat_type:"; print_r($calendar_entry_repeat_type);
		
		$emotional_level = $this->perform('table_workoutdb_emotional_level->getSearchList',$order="id");
		if ( $emotional_level['status'] >= 300 ) {
			return $emotional_level;
		}
		//echo "emotional_level:"; print_r($emotional_level);
		
		$timezones = $this->perform('this->getTimezone');
		if ( $timezones['status'] >= 300 ) {
			return $timezones;
		}
		//echo "timezones:"; print_r($timezones);
		
		$image_size = $this->perform('table_workoutdb_image_size->getSearchList',$order="id");
		if ( $image_size['status'] >= 300 ) {
			return $image_size;
		}
		
		$attributes = array(mysql_schema::getTableAlias('workoutdb','library_measurement',$p_use_alias) => $library_measurement['response'],
		                    mysql_schema::getTableAlias('workoutdb','library_workout_recording_type',$p_use_alias) => $library_workout_recording_type['response'],
		                    mysql_schema::getTableAlias('workoutdb','library_exercise_level',$p_use_alias) => $library_exercise_level['response'],
		                    mysql_schema::getTableAlias('workoutdb','library_exercise_type',$p_use_alias) => $library_exercise_type['response'],
		                    mysql_schema::getTableAlias('workoutdb','library_sport_type',$p_use_alias) => $library_sport_type['response'],
		                    mysql_schema::getTableAlias('workoutdb','library_body_region',$p_use_alias) => $library_body_region['response'],
		                    mysql_schema::getTableAlias('workoutdb','client_user_role',$p_use_alias) => $client_user_role['response'],
		                    mysql_schema::getTableAlias('workoutdb','calendar_entry_type',$p_use_alias) => $calendar_entry_type['response'],
		                    mysql_schema::getTableAlias('workoutdb','calendar_entry_repeat_type',$p_use_alias) => $calendar_entry_repeat_type['response'],
		                    mysql_schema::getTableAlias('workoutdb','emotional_level',$p_use_alias) => $emotional_level['response'],
		                    "timezone"=>$timezones['response'],
		                    mysql_schema::getTableAlias('workoutdb','image_size',$p_use_alias) => $image_size['response']
		                    );
				
		return $this->return_handler->results(200,"",$attributes);
	}

	// ==================================================================================================================
	// Get the metadata for timezones
	// ==================================================================================================================
	
	public function getTimezone() {
		
		$locations = array();
		
		$zones = timezone_identifiers_list();
		foreach ( $zones as $zone ) {
			$zone = explode('/',$zone);
			// Only use "friendly" continent names
			if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' ||
				$zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific') {
				if (isset($zone[1]) != '') {
					$locations[$zone[0]][$zone[0]. '/' . $zone[1]] = str_replace('_', ' ', $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
				}
			}
		}
		// print_r($locations);
		
		return $this->return_handler->results(200,"",$locations);
		
	}
}