<?php
// --------------------------------------------------------------------------------
// if you load the format helper, you must also load the cast helper!
// --------------------------------------------------------------------------------

function format_object_with_id( $p_object ) {
	if ( !is_null($p_object->id) ) {
		return $p_object;
	}
	return new stdClass();
}

function format_team_size($p_min_size,$p_max_size) {
	$team_size = new stdClass();
	if ( !is_null($p_min_size) || !is_null(p_max_size) ) {
		$team_size->min = cast_int($p_min_size);
		$team_size->max = cast_int($p_max_size);
	}
	return $team_size;
}

function format_registration($p_type_id,$p_start,$p_end) {
	$registration = new stdClass();
	if ( !is_null($p_type_id) || !is_null($p_start) || !is_null($p_end) ) {
		$registration->type_id = cast_int($p_type_id);
		$registration->start = cast_int($p_start);
		$registration->end = cast_int($p_end);
	}
	return $registration;
}

function format_status($p_group_count,$p_individual_count) {
	$status = new stdClass();
	if ( !is_null($p_group_count) || !is_null(p_individual_count) ) {
		$status->group_count = cast_int($p_group_count);
		$status->individual_count = cast_int($p_individual_count);
	}
	return $status;
}

function format_media( $p_id, $p_url ) {
	$media = new stdClass();
	if ( !is_null($p_id) ) {
		$media->id = cast_int($p_id);
		$media->url = $p_url;
	}
	
	return $media;
}

function format_height( $p_height, $p_height_uom_id ) {
	$height = new stdClass();
	if ( !is_null($p_height) ) {
		$height->value = cast_float($p_height);
		$height->id = cast_int($p_height_uom_id);
	}
	
	return $height;
}

function format_weight( $p_weight, $p_weight_uom_id ) {
	$weight = new stdClass();
	if ( !is_null($p_weight) ) {
		$weight->value = cast_float($p_weight);
		$weight->id = cast_int($p_weight_uom_id);
	}
	
	return $weight;
}

function format_timezone_offset($p_timezone) {
	if ( is_null($p_timezone) || empty($p_timezone) ) {
		return null;
	}
	$date_time_zone = new DateTimeZone($p_timezone);
    $date_time = new DateTime('now', $date_time_zone);
    $timezone_offset = $date_time_zone->getOffset($date_time);

	return $timezone_offset;
}

function format_result_unit( $p_input, $p_uom_id ) {
	$result_unit = new stdClass();
	if ( !is_null($p_input) ) {
		$result_unit->input = cast_float($p_input);
		$result_unit->id = cast_int($p_uom_id);
	}
	
	return $result_unit;
}

function format_time_limit( $p_value, $p_uom_id, $p_note ) {
	$time_limit = new stdClass();
	if ( !is_null($p_value) || !is_null($p_uom_id) || !is_null($p_note) ) {
		$time_limit->id = cast_int($p_uom_id);
		$time_limit->value = new stdClass();
		if ( !is_null($p_value) || !is_null($p_note) ) {
			$time_limit->value->input = $p_value;
			$time_limit->value->note = $p_note;
		}
	}
	
	return $time_limit;
}

function format_created($p_created,$p_created_by_app,$p_created_by_user_id, $p_created_by_user_first_name, $p_created_by_user_last_name) {
	$created = new stdClass();
	if ( !is_null($p_created) || !is_null($p_created_by_app) || !is_null($p_created_by_user_id) || !is_null($first_name) || !is_null($last_name) ) {
		$created->date_time = cast_int($p_created);
		$created->application = $p_created_by_app;
		$created->user = new stdClass();
		if ( !is_null($p_created_by_user_id) || !is_null($p_created_by_user_first_name) || !is_null($p_created_by_user_last_name) ) {
			$created->user->id = cast_int($p_created_by_user_id);
			$created->user->first_name = $p_created_by_user_first_name;
			$created->user->last_name = $p_created_by_user_last_name;
		}
	}
	return $created;
}