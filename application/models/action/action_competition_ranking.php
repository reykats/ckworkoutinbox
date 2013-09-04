<?php

class action_competition_ranking extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// The results for a competition
	// ==================================================================================================================
	
	public function getResultsForCompetition( $p_competition_id ) {
		// echo "getResultsForCompetition $p_competition_id<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.*, ";
		$sql .= "COUNT(p.calendar_event_participation_id) checkins ";
		$sql .= "FROM ";
		$sql .= "competition_detail comp ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation_detail p ";
		$sql .= "ON comp.user_id = p.user_id AND comp.client_id = p.client_id AND p.event_start BETWEEN comp.challenge_start AND comp.challenge_end ";
		$sql .= "WHERE comp.competition_id = " . $p_competition_id . " ";
		$sql .= "GROUP BY phase_id, challenge_id, competition_group_id, user_id ";
		$sql .= "ORDER BY phase_name, phase_id, group_name, competition_group_id, last_name, first_name, user_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		$competition = null;
		foreach ( $rows as $row ) {
			// print_r($row);
			if ( is_null($competition) ) {
				$competition = new stdClass();
				$competition->id = cast_int($row->competition_id);
				$competition->name = $row->competition_name;
				$competition->competition_type_id = cast_int($row->competition_type_id);
				$competition->closed_competition = cast_boolean($row->closed_competition);
				$competition->competition_type_id = cast_int($row->competition_type_id);
				$competition->group_size = format_team_size($row->competition_min_team_size,$row->competition_max_team_size);
				$competition->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
				// inititialize the phases in the competition
				$entry->phase = array();
				$phase = &$competition->phase;
				$p = -1;
			}
			if ( !is_null($row->phase_id) && ($p < 0 || $phase[$p]->id != $row->phase_id) ) {
				++$p;
				$phase[$p] = new stdClass();
				$phase[$p]->id = cast_int($row->phase_id);
				$phase[$p]->name = $row->phase_name;
				// inititalize the challenges in the phase
				$phase[$p]->challenge = array();
				$challenge = &$phase[$p]->challenge;
				$c = -1;
			}
			if ( !is_null($row->challenge_id) && ($c < 0 || $challenge[$c]->id != $row->challenge_id) ) {
				++$c;
				$challenge[$c] = new stdClass();
				$challenge[$c]->id = cast_int($row->challenge_id);
				$challenge[$c]->name = $row->challenge_name;
				$challenge[$c]->score_type_id = cast_int($row->score_type_id);
				$challenge[$c]->score_calculation_type_id = cast_int($row->score_calculation_type_id);
				$challenge[$c]->point_awarding_type_id = cast_int($row->point_awarding_type_id);
				$challenge[$c]->start = cast_int($row->challenge_start);
				$challenge[$c]->end = cast_int($row->challenge_end);
				$challenge[$c]->team_size = cast_int($row->challenge_max_team_size);
				if ( $competition->competition_type_id == 1 ) { // Individual competition (no Grouops)
					$group = null;
					$g = null;
					// inititalize the individuals in the challenge
					$challenge[$c]->individual = array();
					$individual = &$challenge[$c]->individual;
					$i = -1;
				} else if ( $competition->competition_type_id == 2 ) { // Group compitition
					// inititaliZe the groups in the challenge
					$challenge[$c]->group = array();
					$group = &$challenge[$c]->group;
					$g = -1;
				}
			}
			if ( !is_null($row->competition_group_id) && ($g < 0 || $group[$g]->competition_group_id != $row->competition_group_id) ) {
				++$g;
				$group[$g]->competition_group_id = cast_int($row->competition_group_id);
				$group[$g]->phase_group_id = cast_int($row->phase_group_id);
				$group[$g]->challenge_group_id = cast_int($row->challenge_group_id);
				$group[$g]->name = $row->group_name;
				$group[$g]->affiliated_gym = $row->group_affiliated_gym;
				// inititalize the individuals in the group
				$group[$g]->individual = array();
				$individual = &$group[$g]->individual;
				$i = -1;
			}
			if ( !is_null($row->competition_individual_id) && ($i < 0 || $individual[$i]->competition_individual_id != $row->competition_group_id) ) {
				++$i;
				$individual[$i]->competition_individual_id = cast_int($row->competition_individual_id);
				$individual[$i]->phase_individual_id = cast_int($row->phase_individual_id);
				$individual[$i]->challenge_individual_id = cast_int($row->challenge_individual_id);
				$individual[$i]->first_name = $row->first_name;
				$individual[$i]->last_name = $row->last_name;
				$individual[$i]->email = $row->email;
				$individual[$i]->height = format_height($row->height,$row->height_uom_id);
				$individual[$i]->weight = format_weight($row->weight,$row->weight_uom_id);
				$individual[$i]->birthday = cast_int($row->birthday);
				$individual[$i]->alias_name = $row->alias_name;
				$individual[$i]->affiliated_gym = $row->group_affiliated_gym;
				$individual[$i]->checkin_count = cast_int($row->checkins);
			}
		}
		return $this->return_handler->results(200,"",$competition);
	}

	// ==================================================================================================================
	// List the Competition Ranking for the groups
	// ==================================================================================================================
	
	public function getGroupRankingForCompetition( $p_competition_id ) {
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.*, ";
		$sql .= "media.media_url media_url, ";
		$sql .= "COUNT(p.calendar_event_participation_id) checkins ";
		$sql .= "FROM ";
		$sql .= "competition_detail comp ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = comp.user_id ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation_detail p ";
		$sql .= "ON comp.user_id = p.user_id AND comp.client_id = p.client_id AND p.event_start BETWEEN comp.challenge_start AND comp.challenge_end ";
		$sql .= "WHERE comp.competition_id = " . $p_competition_id . " ";
		$sql .= "GROUP BY user_id ";
		$sql .= "ORDER BY group_name, competition_group_id, last_name, first_name, user_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		$group = array();
		$g = -1;
		foreach ( $rows as $row ) {
			// print_r($row);
			if ( $g < 0 || $group[$g]->id != $row->competition_group_id ) {
				// Create a new group level entry
				++$g;
				$group[$g]->id = cast_int($row->competition_group_id);
				$group[$g]->name = $row->group_name;
				$group[$g]->affiliated_gym = $row->group_affiliated_gym;
				$group[$g]->result = new stdClass();
				$group[$g]->result->value = 0;
				$group[$g]->result->uom_id = null;
				// inititalize the individuals in the group
				$group[$g]->individual = array();
				$individual = &$group[$g]->individual;
				$i = -1;
			}
			if ( !is_null($row->competition_individual_id) && ($i < 0 || $individual[$i]->competition_individual_id != $row->competition_group_id) ) {
				// Create a new individual level entry
				++$i;
				$individual[$i]->id = cast_int($row->competition_individual_id);
				$individual[$i]->first_name = $row->first_name;
				$individual[$i]->last_name = $row->last_name;
				$individual[$i]->email = $row->email;
				$individual[$i]->media = $row->media_url;
				$individual[$i]->result = new stdClass();
				$individual[$i]->result->value = 0;
				$individual[$i]->result->uom_id = null;
			}
			if ( $i >= 0 && !is_null($row->checkins) && $individual[$i]->competition_individual_id != $row->competition_group_id ) {
				// sum the checkins at the group and individual levels
				$group[$g]->result->value += (int) $row->checkins;
				$individual[$i]->result->value += (int) $row->checkins;
			}
		}

		// sort the event list by result->value/name/id
		$this->load->helper('compare_helper');
		usort($group,'group_ranking_compare');
		
		// set the ranking value
		$ranking = 1;
		$g_max = count($group) - 1;
		for( $g = 0; $g <= $g_max; ++$g ) {
			if ( $g == 0 || $group[$g]->result->value == $group[$g - 1]->result->value ) {
				$group[$g]->ranking = $ranking;
			} else {
				$group[$g]->ranking = ++$ranking;
			}
		}

		return $this->return_handler->results(200,"",$group);
	}
}