<?php

class action_competition extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get a single entry for an id
	// ==================================================================================================================
	
	public function getDetailForId( $p_competition_id ) {
		// --------------------------------------------------------------------------------------------------------------
		// Get the competition/phases/challenges
		// --------------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.*, ";
		$sql .= "p.id phase_id, p.name phase_name, ";
		$sql .= "chal.id challenge_id, chal.name challenge_name, chal.score_type_id challenge_score_type_id, chal.score_calculation_type_id challenge_score_calculation_type_id, ";
		$sql .= "chal.point_awarding_type_id challenge_point_awarding_type_id, chal.start challenge_start, chal.end challenge_end, chal.max_team_size challenge_team_size ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp, ";
		$sql .= "phase p, ";
		$sql .= "challenge chal ";
		$sql .= "WHERE comp.id = " . $p_competition_id . " ";
		$sql .= "AND p.competition_id = comp.id ";
		$sql .= "AND chal.phase_id = p.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$rows = $query->result();

		$entry = null;
		foreach ( $rows as $row ) {
			// print_r($row);
			if ( is_null($entry) ) {
				$entry = new stdClass();
				$entry->id = cast_int($row->id);
				$entry->client_id = cast_int($row->client_id);
				$entry->name = $row->name;
				$entry->description = $row->description;
				$entry->competition_type_id = cast_int($row->competition_type_id);
				$entry->group_size = format_team_size($row->team_size_min,$row->team_size_max);
				$entry->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
				$entry->status = format_status($row->group_count,$row->individual_count);
				$entry->phase = array();
				$phase = -1;
			}
			if ( !is_null($row->phase_id) && ($phase < 0 || $entry->phase[$phase]->id != $row->phase_id) ) {
				++$phase;
				$entry->phase[$phase] = new stdClass();
				$entry->phase[$phase]->id = cast_int($row->phase_id);
				$entry->phase[$phase]->name = $row->phase_name;
				$entry->phase[$phase]->chalenge = array();
				$challenge = -1;
			}
			if ( !is_null($row->challenge_id) && ($challenge < 0 || $entry->phase[$phase]->challenge[$challenge]->id != $row->challenge_id) ) {
				++$challenge;
				$entry->phase[$phase]->chalenge[$challenge] = new stdClass();
				$entry->phase[$phase]->chalenge[$challenge]->id = cast_int($row->challenge_id);
				$entry->phase[$phase]->chalenge[$challenge]->name = $row->name;
				$entry->phase[$phase]->chalenge[$challenge]->score_type_id = cast_int($row->challenge_score_type_id);
				$entry->phase[$phase]->chalenge[$challenge]->score_calculation_type_id = cast_int($row->challenge_score_calculation_type_id);
				$entry->phase[$phase]->chalenge[$challenge]->point_awarding_type_id = cast_int($row->challenge_point_awarding_type_id);
				$entry->phase[$phase]->chalenge[$challenge]->start = cast_int($row->challenge_start);
				$entry->phase[$phase]->chalenge[$challenge]->end = cast_int($row->challenge_end);
				$entry->phase[$phase]->chalenge[$challenge]->team_size = cast_int($row->challenge_team_size);
			}
		}
		
		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Get All entries for a client
	// ==================================================================================================================

	public function getForClient($p_client_id){
		// ------------------------------------------------------------------------------------------------------------
		//
		// Get the participants for the calendar event
		//
		// ------------------------------------------------------------------------------------------------------------
		// initialize the response data
		$response = new stdClass();
		$response->count = 0;
		$response->results = array();
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND concat(";
			$search_check .= "if(isnull(u.name),'',u.name)";
			$search_check .= ") LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		// ---------------------------------------------------------------------------------------------------------
		// Get the total record count without paging limits
		// ---------------------------------------------------------------------------------------------------------

		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "count(comp.id) cnt ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE comp.client_id = " . $p_client_id . " ";
		$sql .= $search_check . " ";

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		
		$count = 0;
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		
		if ( $count == 0 ) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.* ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE comp.client_id = " . $p_client_id . " ";
		$sql .= $search_check;
		$sql .= "ORDER BY comp.start, comp.end ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->client_id = cast_int($row->client_id);
			$entry->name = $row->name;
			$entry->description = $row->description;
			$entry->start = cast_int($row->start);
			$entry->end = cast_int($row->end);
			$entry->competition_type_id = cast_int($row->competition_type_id);
			$entry->group_size = format_team_size($row->team_size_min,$row->team_size_max);
			$entry->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
			$entry->status = format_status($row->group_count,$row->individual_count);
			$entry->deletable = false;
			array_push($entries, $entry);
			unset($entry);
		}

		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}
	
	// ==================================================================================================================
	// Create a list of competitions for a client with open registration on a given utc date/time
	// ==================================================================================================================
	
	public function getForClientStart( $p_client_id, $p_start ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.* ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE comp.client_id = " . $p_client_id . " ";
		$sql .= "AND " . $p_start . " BETWEEN comp.registration_start AND comp.registration_end ";
		$sql .= "ORDER BY comp.start, comp.end ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->start = cast_int($row->start);
			$entry->end = cast_int($row->end);
			$entry->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
			array_push($entries, $entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Get All groups for a competition
	// ==================================================================================================================

	public function getGroupsForCompetition($p_competition_id){
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair the optional name query
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "WHERE concat(";
			$search_check .= "if(isnull(g.name),'',g.name)";
			$search_check .= ") LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$limit = "LIMIT 0, " . $_GET['limit'] . " ";
		}
		
		$sql  = "SELECT ";
		$sql .= "comp.team_size_min, comp.team_size_max, ";
		$sql .= "g.*, ";
		$sql .= "COUNT(DISTINCT i.id) count ";
		$sql .= "FROM ";
		$sql .= "competition comp ";
		$sql .= "LEFT OUTER JOIN competition_group g ";
		$sql .= "LEFT OUTER JOIN competition_individual i ";
		$sql .= "ON i.competition_group_id = g.id ";
		$sql .= "ON g.competition_id = comp.id ";
		$sql .= $search_check;
		$sql .= "WHERE comp.id = " . $p_competition_id . " ";
		$sql .= "GROUP BY g.id ";
		$sql .= "ORDER BY g.name ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$i = -1;
		foreach ( $rows as $row ) {
			if ( $i < 0 ) {
				$entry = new stdClass();
				$entry->group_size = format_team_size($row->team_size_min,$row->team_size_max);
				$entry->group = array();
			}
			if ( !is_null($row->id) ) {
				++$i;
				$entry->group[$i] = new stdClass();
				$entry->group[$i]->id = cast_int($row->id);
				$entry->group[$i]->name = $row->name;
				$entry->group[$i]->count = cast_int($row->count);
			}
		}

		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Get All individuals for a competition group
	// ==================================================================================================================

	public function getIndividualsForGroup($p_competition_group_id){
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair the optional name query
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND concat(";
			$search_check .= "if(isnull(u.first_name),'',u.first_name),";
			$search_check .= "if(isnull(u.last_name),'',u.last_name)";
			$search_check .= ") LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$limit = "LIMIT 0, " . $_GET['limit'] . " ";
		}
		
		$sql  = "SELECT ";
		$sql .= "ci.id competition_individual_id, u.*, ";
		$sql .= "media.media_url media ";
		$sql .= "FROM ";
		$sql .= "competition_individual ci, ";
		$sql .= "user u ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = u.id ";
		$sql .= "WHERE ci.competition_group_id = " . $p_competition_group_id . " ";
		$sql .= "AND u.id = ci.user_id ";
		$sql .= $search_check;
		$sql .= "ORDER BY u.first_name, u.last_name ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = cast_int($row->competition_individual_id);
			$entry->first_name = $row->first_name;
			$entry->last_name = $row->last_name;
			$entry->email = $row->email;
			$entry->media = $row->media;
			
			array_push($entries,$entry);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Get the competition ids of all competitions who have challenges on the current date
	// ==================================================================================================================

	public function getIdForActiveChallenge(){
		$sql  = "SELECT ";
		$sql .= "comp.id id ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE comp.challenge_count > 0 ";
		$sql .= "AND " . time() . " BETWEEN comp.start AND comp.end ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entries[] = cast_int($row->id);
		}

		return $this->return_handler->results(200,"",$entry);
	}
	
	// ==================================================================================================================
	// Create a new competition (competion/phases/challenges)
	// ==================================================================================================================

	public function create( $p_fields ) {
		// echo "action_competition->create fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------
		// Create the competition
		// -------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_competition->insert',$p_fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$competition_id = $return['response']->id;
		// -------------------------------------------------------------------------------------------------
		// Create the phases and their challenges for the competition
		// -------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'phase') && is_array($competition->phase) ) {
			foreach( $competition->phase as &$phase ) {
				if ( is_object($phase) ) {
					// -------------------------------------------------------------------------------------------------
					// Create the phase
					// -------------------------------------------------------------------------------------------------
					$phase->competition_id = $competition_id;
					$return = $this->perform('table_workoutdb_phase->insert',$phase);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$phase_id = $return['response']->id;
					// -------------------------------------------------------------------------------------------------
					// Create the challenges for the phase
					// -------------------------------------------------------------------------------------------------
					if ( property_exists($phase,'challenge') && is_array($phase->challenge) ) {
						foreach( $phase->challenge as &$challenge ) {
							if ( is_object($challenge) ) {
								// -------------------------------------------------------------------------------------------------
								// Create the challenge
								// -------------------------------------------------------------------------------------------------
								$challenge->phase_id = $phase_id;
								$return = $this->perform('table_workoutdb_challenge->insert',$challenge);
								if ( $return['status'] >= 300 ) {
									return $return;
								}
								$challenge_id = $return['response']->id;
							}
						}
					}
				}
			}
		}

		$response = new stdClass();
		$response->id = $new_id;
		return $this->return_handler->results(201,"Entry created",$response);
	}

	// ==================================================================================================================
	// Update Competition (modify the competion, add and modify phases, and and modify challenges)
	// ==================================================================================================================

	public function update( $p_fields ) {
		// echo "action_competition->create fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------
		// Create the competition
		// -------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_competition->update',$p_fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------
		// Create the phases and their challenges for the competition
		// -------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'phase') && is_array($competition->phase) ) {
			foreach( $competition->phase as &$phase ) {
				if ( is_object($phase) ) {
					if ( !property_exists($phase,'id') || is_null($phase->id) ) {
						// -------------------------------------------------------------------------------------------------
						// Create the phase
						// -------------------------------------------------------------------------------------------------
						$phase->competition_id = $competition_id;
						$return = $this->perform('table_workoutdb_phase->insert',$phase);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
						$phase_id = $return['response']->id;
						// -------------------------------------------------------------------------------------------------
						// Create the challenges for the phase
						// -------------------------------------------------------------------------------------------------
						if ( property_exists($phase,'challenge') && is_array($phase->challenge) ) {
							foreach( $phase->challenge as &$challenge ) {
								if ( is_object($challenge) ) {
									// -------------------------------------------------------------------------------------------------
									// Create the challenge
									// -------------------------------------------------------------------------------------------------
									$challenge->phase_id = $phase_id;
									$return = $this->perform('table_workoutdb_challenge->insert',$challenge);
									if ( $return['status'] >= 300 ) {
										return $return;
									}
									$challenge_id = $return['response']->id;
								}
							}
						}
					} else {
						// -------------------------------------------------------------------------------------------------
						// Update the phase
						// -------------------------------------------------------------------------------------------------
						$phase->competition_id = $competition_id;
						$return = $this->perform('table_workoutdb_phase->insert',$phase);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
						$phase_id = $phase->id;
						// -------------------------------------------------------------------------------------------------
						// Create or Update the challenges for the phase
						// -------------------------------------------------------------------------------------------------
						if ( property_exists($phase,'challenge') && is_array($phase->challenge) ) {
							foreach( $phase->challenge as &$challenge ) {
								if ( is_object($challenge) ) {
									if ( !property_exists($challenge,'id') || is_null($challenge->id) ) {
										// -------------------------------------------------------------------------------------------------
										// Create the challenge
										// -------------------------------------------------------------------------------------------------
										$challenge->phase_id = $phase_id;
										$return = $this->perform('table_workoutdb_challenge->insert',$challenge);
										if ( $return['status'] >= 300 ) {
											return $return;
										}
										$challenge_id = $return['response']->id;
									} else {
										// -------------------------------------------------------------------------------------------------
										// Update the challenge
										// -------------------------------------------------------------------------------------------------
										$challenge->phase_id = $phase_id;
										$return = $this->perform('table_workoutdb_challenge->update',$challenge);
										if ( $return['status'] >= 300 ) {
											return $return;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$response = new stdClass();
		$response->id = $new_id;
		return $this->return_handler->results(201,"Entry created",$response);
	}

	// ==================================================================================================================
	// Add a Group to a Competition
	// ==================================================================================================================
	
	public function addGroupToCompetition( $p_data ) {
		// echo "addGroupToCompetition<br />"; var_dump($p_data);
		$p_data = (object) $p_data;
		// -------------------------------------------------------------------------------------------------------------
		// Validate manitory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_data,'competition_id') || is_null($p_data->competition_id) || empty($p_data->competition_id) || !is_numeric($p_data->competition_id) ) {
			return $this->return_handler->results(400,"Competition ID must be provided",new stdClass());
		}
		if ( !property_exists($p_data,'group_name') || is_null($p_data->group_name) || empty($p_data->group_name) || !is_string($p_data->group_name) ) {
			return $this->return_handler->results(400,"Group name must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition
		// ------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getForid',$p_data->competition_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid group",new stdClass());
		}
		$competition = $return['response'];
		// var_dump($competition);
		// -------------------------------------------------------------------------------------------------------------
		// Has the competition started?
		// -------------------------------------------------------------------------------------------------------------
		if ( time() >= $competition->start ) {
			return $this->return_handler->results(400,"Competition can not be changed, it has begun",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Create the competition_group
		// -------------------------------------------------------------------------------------------------------------
		$fields = new stdClass();
		$fields->competition_id = $p_data->competition_id;
		$fields->name = $p_data->group_name;
		$return = $this->perform('table_workoutdb_competition_group->insert',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		unset($fields);
		
		$competition_group_id = $return['response']->id;
		// -------------------------------------------------------------------------------------------------------------
		// Get the phases
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['competition_id'] = $p_data->competition_id;
		$return = $this->perform('table_workoutdb_phase->getForAndKeys',$key);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$phases = $return['response'];
		foreach( $phases as $phase ) {
			// -------------------------------------------------------------------------------------------------------------
			// Create the phase_group
			// -------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->phase_id = $phase->id;
			$fields->competition_group_id = $competition_group_id;
			$return = $this->perform('table_workoutdb_phase_group->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			unset($fields);
			
			$phase_group_id = $return['response']->id;
			// -------------------------------------------------------------------------------------------------------------
			// Get the challenges for the phase
			// -------------------------------------------------------------------------------------------------------------
			$key = array();
			$key['phase_id'] = $phase->id;
			$return = $this->perform('table_workoutdb_challenge->getForAndKeys',$key);
			if ( $return['status'] > 200 ) {
				return $return;
			}
			$challenges = $return['response'];
			foreach( $challenges as $challenge ) {
				// -------------------------------------------------------------------------------------------------------------
				// Create the phase_group
				// -------------------------------------------------------------------------------------------------------------
				$fields = new stdClass();
				$fields->challenge_id = $challenge->id;
				$fields->phase_group_id = $phase_group_id;
				$return = $this->perform('table_workoutdb_challenge_group->insert',$fields);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				unset($fields);
				
				$challenge_group_id = $return['response']->id;
			}
		}
			
		$response = new stdClass();
		$response->id = $competition_group_id;
		return $this->return_handler->results(201,"Created",$response);
	}

	// ==================================================================================================================
	// delete Groups from Competition
	// ==================================================================================================================

	public function deleteCompetitionGroups( $p_data ) {
		$p_data = (object) $p_data;
		// -------------------------------------------------------------------------------------------------------------
		// Validate Mandatory Fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_data,'competition_id') || is_null($p_data->competition_id) || empty($p_data->competition_id) || !is_numeric($p_data->competition_id) ) {
			return $this->return_handler->results(400,"You must supply competition_id",new stdClass());
		}
		if ( !property_exists($p_data,'group_id') || !is_array($p_data->group_id) ) {
			return $this->return_handler->results(400,"You must supply group_ids",new stdClass());
		}
		if ( count($p_data->group_id) == 0 || is_null($p_data->group_id[0]) || empty($p_data->group_id[0]) || !is_numeric($p_data->group_id[0]) ) {
			return $this->return_handler->results(204,"No groups to remove",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getForId',$p_data->competition_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid competition",new stdClass());
		}
		$competition = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Has the competition started?
		// -------------------------------------------------------------------------------------------------------------
		if ( time() >= $competition->start ) {
			return $this->return_handler->results(400,"Competition can not be changed, it has begun",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Delete a list of competition groups from a competition
		// -------------------------------------------------------------------------------------------------------------
		foreach ( $p_data->group_id as $competition_group_id ) {
			$return = $this->perform('this->deleteGroupFromCompetition',$competition_group_id,$p_data->competition_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}

		return $this->return_handler->results(202,"Individuals removed from group",new stdClass());
	}
	
	public function getForId( $p_competition_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.* ";
		$sql .= "FROM ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE comp.id = " . $p_competition_id . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$row = $query->row();

		$entry = new stdClass();
		$entry->id = cast_int($row->id);
		$entry->name = $row->name;
		$entry->start = cast_int($row->start);
		$entry->end = cast_int($row->end);
		$entry->competition_type_id = cast_int($row->competition_type_id);
		$entry->group_size = format_team_size($row->team_size_min,$row->team_size_max);
		$entry->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
		
		return $this->return_handler->results(200,"",$entry);
	}

	public function deleteGroupFromCompetition( $p_competition_group_id, $p_competition_id ) {
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition group and its phases
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getGroupForId',$p_competition_group_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$competition_group = $return['response'];
		// echo "competion_group : "; var_dump($competition_group);
		// -------------------------------------------------------------------------------------------------------------
		// Is the group a group in this competition?
		// -------------------------------------------------------------------------------------------------------------
		if ( $competition_group->competition_id != $p_competition_id ) {
			return $this->return_handler->results(400,"Group is not in Competition",new stdClass());	
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Delete the individuals in the group
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// echo "Delete individuals In Group<br />";
		foreach( $competition_group->phase_group as $phase_group ) {
			foreach ( $phase_group->challenge_group_id as $challenge_group_id ) {
				// Delete challenge_individuals for this challenge_group_id
				$key = array();
				$key['challenge_group_id'] = $challenge_group_id;
				$return = $this->perform('table_workoutdb_challenge_individual->deleteForAndKeys',$key);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
			// Delete phase_individuals for this phase_group_id
			$key = array();
			$key['phase_group_id'] = $phase_group->id;
			$return = $this->perform('table_workoutdb_phase_individual->deleteForAndKeys',$key);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// Delete the competion_individuals for the competition_phase
		$key = array();
		$key['competition_group_id'] = $competition_group->id;
		$return = $this->perform('table_workoutdb_competition_individual->deleteForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Delete all components of the competition group
		// -------------------------------------------------------------------------------------------------------------
		// echo "Delete Group From Competition<br />";
		// Delete the group from all challenges
		foreach( $competition_group->phase_group as $phase_group ) {
			// Delete the group from the challenges by phase
			$key = array();
			$key['phase_group_id'] = $phase_group->id;
			$return = $this->perform('table_workoutdb_challenge_group->deleteForAndKeys',$key);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// Delete the group from all phases
		$key = array();
		$key['competition_group_id'] = $competition_group->id;
		$return = $this->perform('table_workoutdb_phase_group->deleteForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// Delete the group from the competition
		$return = $this->perform('table_workoutdb_competition_group->delete',$competition_group->id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		return $this->return_handler->results(202,"Groups removed from competition",new stdClass());
	}

	public function getGroupForId( $p_competition_group_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "cg.competition_id competition_id, cg.id competition_group_id, ";
		$sql .= "pg.id phase_group_id, ";
		$sql .= "chig.id challenge_group_id ";
		$sql .= "FROM ";
		$sql .= "competition_group cg ";
		$sql .= "LEFT OUTER JOIN phase_group pg ";
		$sql .= "LEFT OUTER JOIN challenge_group chig ";
		$sql .= "ON chig.phase_group_id = pg.id ";
		$sql .= "ON pg.competition_group_id = cg.id ";
		$sql .= "WHERE cg.id = " . $p_competition_group_id . " ";

		// echo "$sql<br />";
		 
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entry = null;
		foreach( $rows as $row ) {
			// echo "row : "; var_dump($row);
			if ( is_null($entry) ) {
				$entry->competition_id = cast_int($row->competition_id);
				$entry->id = cast_int($row->competition_group_id);
				$entry->phase_group = array();
				$phase = -1;
			}
			if ( !is_null($row->phase_group_id) && ($phase < 0 || $row->phase_group_id != $entry->phase_group[$phase]->id) ) {
				++$phase;
				$entry->phase_group[$phase]->id = cast_int($row->phase_group_id);
				$entry->phase_group[$phase]->challenge_group_id = array();
				$challenge = -1;
			}
			if ( !is_null($row->challenge_group_id) && ($challenge < 0 || $row->challenge_group_id != $entry->phase_group[$phase]->challenge_group[$challenge]->id) ) {
				++$challenge;
				$entry->phase_group[$phase]->challenge_group_id[$challenge] = cast_int($row->challenge_group_id);
			}
		}
		
		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Add an Individual to a Group
	// ==================================================================================================================
	
	public function addClientUserToGroup( $p_data ) {
		$p_data = (object) $p_data;
		// -------------------------------------------------------------------------------------------------------------
		// Validate Mandatory Fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_data,'client_user_id') || is_null($p_data->client_user_id) || empty($p_data->client_user_id) || !is_numeric($p_data->client_user_id) ) {
			return $this->return_handler->results(400,"You must supply client_user_id",new stdClass());
		}
		if ( !property_exists($p_data,'group_id') || is_null($p_data->group_id) || empty($p_data->group_id) || !is_numeric($p_data->group_id) ) {
			return $this->return_handler->results(400,"You must supply competition_id",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the user
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_client_user->getForId',$p_data->client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid group",new stdClass());
		}
		$client_user = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Add the User to the Competition Group
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->addIndividualToGroup',$client_user->user_id,$p_data->group_id);
		
		return $return;
	}
	
	public function addUserToGroup( $p_data ) {
		$p_data = (object) $p_data;
		// -------------------------------------------------------------------------------------------------------------
		// Validate Mandatory Fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_data,'user_id') || is_null($p_data->user_id) || empty($p_data->user_id) || !is_numeric($p_data->user_id) ) {
			return $this->return_handler->results(400,"You must supply user_id",new stdClass());
		}
		if ( !property_exists($p_data,'group_id') || is_null($p_data->group_id) || empty($p_data->group_id) || !is_numeric($p_data->group_id) ) {
			return $this->return_handler->results(400,"You must supply competition_id",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Add the User to the Competition Group
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->addIndividualToGroup',$p_data->user_id,$p_data->group_id);
		
		return $return;
	}
	
	public function addIndividualToGroup( $p_user_id, $p_competition_group_id = null ) {
		// -------------------------------------------------------------------------------------------------------------
		// Get the user
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('action_user->getForId',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid user",new stdClass());
		}
		$user = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition
		// ------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getForGroup',$p_competition_group_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid group",new stdClass());
		}
		$competition = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Has the competition started?
		// -------------------------------------------------------------------------------------------------------------
		if ( time() >= $competition->start ) {
			return $this->return_handler->results(400,"Competition can not be changed, it has begun",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition
		// ------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getIndividualCountForGroup',$p_competition_group_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid group",new stdClass());
		}
		$individual_count = $return['response']->individual_count;
		// -------------------------------------------------------------------------------------------------------------
		// Has the competition started?
		// -------------------------------------------------------------------------------------------------------------
		// print_r($competition);
		if ( $individual_count >= $competition->group_size->max ) {
			return $this->return_handler->results(400,"Team is at Max Size",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Create the competition_individual entry
		// -------------------------------------------------------------------------------------------------------------
		$fields = new stdClass();
		$fields->competition_id = $competition->id;
		$fields->user_id = $p_user_id;
		$fields->competition_group_id = $p_competition_group_id;
		$fields->height = $user->height;
		$fields->weight = $user->weight;
		$fields->birthday = $user->birthday;
		$return = $this->perform('table_workoutdb_competition_individual->insert',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		unset($fields);
		
		$competition_individual_id = $return['response']->id;
		// -------------------------------------------------------------------------------------------------------------
		// Get the phases
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['competition_id'] = $competition->id;
		$return = $this->perform('table_workoutdb_phase->getForAndKeys',$key);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$phases = $return['response'];
		foreach( $phases as $phase ) {
			// -------------------------------------------------------------------------------------------------------------
			// Get the phase_group
			// -------------------------------------------------------------------------------------------------------------
			$key = array();
			$key['phase_id'] = $phase->id;
			$key['competition_group_id'] = $p_competition_group_id;
			$return = $this->perform('table_workoutdb_phase_group->getForAndKeys',$key);
			if ( $return['status'] > 200 ) {
				return $return;
			}
			$phase_group_id = $return['response'][0]->id;
			// -------------------------------------------------------------------------------------------------------------
			// Create the phase_individual entry
			// -------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->phase_id = $phase->id;
			$fields->competition_individual_id = $competition_individual_id;
			$fields->phase_group_id = $phase_group_id;
			$return = $this->perform('table_workoutdb_phase_individual->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			unset($fields);
			
			$phase_individual_id = $return['response']->id;
			// -------------------------------------------------------------------------------------------------------------
			// Get the challenges
			// -------------------------------------------------------------------------------------------------------------
			$key = array();
			$key['phase_id'] = $phase->id;
			$return = $this->perform('table_workoutdb_challenge->getForAndKeys',$key);
			if ( $return['status'] > 200 ) {
				return $return;
			}
			$challenges = $return['response'];
			foreach( $challenges as $challenge ) {
				// -------------------------------------------------------------------------------------------------------------
				// Get the challenge_group
				// -------------------------------------------------------------------------------------------------------------
				$key = array();
				$key['challenge_id'] = $challenge->id;
				$key['phase_group_id'] = $p_competition_group_id;
				$return = $this->perform('table_workoutdb_challenge_group->getForAndKeys',$key);
				if ( $return['status'] > 200 ) {
					return $return;
				}
				$challenge_group_id = $return['response'][0]->id;
				// -------------------------------------------------------------------------------------------------------------
				// Create the challenge_individual entry
				// -------------------------------------------------------------------------------------------------------------
				$fields = new stdClass();
				$fields->challenge_id = $challenge->id;
				$fields->phase_individual_id = $phase_individual_id;
				$fields->challenge_group_id = $challenge_group_id;
				$return = $this->perform('table_workoutdb_challenge_individual->insert',$fields);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				unset($fields);
				
				$challenge_individual_id = $return['response']->id;
			}
		}
		
		$response = new stdClass();
		$response->id = $competition_individual_id;
		return $this->return_handler->results(201,"Created",$response);
	}

	public function getIndividualCountForGroup( $p_competition_group_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "COUNT(i.id) individual_count ";
		$sql .= "FROM ";
		$sql .= "competition_individual i ";
		$sql .= "WHERE i.competition_group_id = " . $p_competition_group_id . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$row = $query->row();

		$entry = new stdClass();
		$entry->individual_count = cast_int($row->individual_count);
		
		return $this->return_handler->results(200,"",$entry);
	}
	
	// ==================================================================================================================
	// Delete Individual from a Group
	// ==================================================================================================================

	public function deleteGroupIndividuals( $p_data ) {
		$p_data = (object) $p_data;
		// -------------------------------------------------------------------------------------------------------------
		// Validate Mandatory Fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_data,'group_id') || is_null($p_data->group_id) || empty($p_data->group_id) || !is_numeric($p_data->group_id) ) {
			return $this->return_handler->results(400,"You must supply group_id",new stdClass());
		}
		if ( !property_exists($p_data,'individual_id') || !is_array($p_data->individual_id) ) {
			return $this->return_handler->results(400,"You must supply group_ids",new stdClass());
		}
		if ( count($p_data->individual_id) == 0 || is_null($p_data->individual_id[0]) || empty($p_data->individual_id[0]) || !is_numeric($p_data->individual_id[0]) ) {
			return $this->return_handler->results(204,"No individualss to remove",new stdClass());
		}
		// echo "Valid p_data<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getForGroup',$p_data->group_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid competition",new stdClass());
		}
		$competition = $return['response'];
		// echo "competition:"; print_r($competition); echo "<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Has the competition started?
		// -------------------------------------------------------------------------------------------------------------
		if ( time() >= $competition->start ) {
			return $this->return_handler->results(400,"Competition can not be changed, it has begun",new stdClass());
		}
		// echo "the competition has not started<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Delete a list of competition groups from a competition
		// -------------------------------------------------------------------------------------------------------------
		// print_r($competition); echo "<br />";
		foreach ( $p_data->individual_id as $competition_individual_id ) {
			// echo "delete $competition_individual_id from " . $p_data->group_id , "<br />";
			$return = $this->perform('this->deleteIndividualFromGroup',$competition_individual_id,$p_data->group_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}

		return $this->return_handler->results(202,"Individuals removed from group",new stdClass());
	}

	public function getForGroup( $p_competition_group_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "comp.* ";
		$sql .= "FROM ";
		$sql .= "competition_group g, ";
		$sql .= "competition_stats comp ";
		$sql .= "WHERE g.id = " . $p_competition_group_id . " ";
		$sql .= "AND comp.id = g.competition_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$row = $query->row();

		$entry = new stdClass();
		$entry->id = cast_int($row->id);
		$entry->name = $row->name;
		$entry->start = cast_int($row->start);
		$entry->end = cast_int($row->end);
		$entry->competition_type_id = cast_int($row->competition_type_id);
		$entry->group_size = format_team_size($row->team_size_min,$row->team_size_max);
		$entry->registration = format_registration($row->registration_type_id,$row->registration_start,$row->registration_end);
		
		return $this->return_handler->results(200,"",$entry);
	}
	
	public function deleteIndividualFromGroup( $p_competition_individual_id, $p_competition_group_id ) {
		// echo "deleteIndividualFromGroup $p_competition_individual_id $p_competition_group_id<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition group and its phases
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getIndividualForId',$p_competition_individual_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$competition_individual = $return['response'];
		// echo "individual:"; print_r($competition_individual); echo "<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Get the competition individual
		// -------------------------------------------------------------------------------------------------------------
		if ( $competition_individual->competition_group_id != $p_competition_group_id ) {
			return $this->return_handler->results(400,"Individual not in Group",new stdClass());	
		}
		// echo "individual in group<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Delete all components of the competition group
		// -------------------------------------------------------------------------------------------------------------
		foreach( $competition_individual->phase_individual_id as $phase_individual_id ) {
			// Delete the phase_individual from the challenges
			$key = array();
			$key['phase_individual_id'] = $phase_individual_id;
			$return = $this->perform('table_workoutdb_challenge_individual->deleteForAndKeys',$key);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// Delete the compititon_individual from the phases
		$key = array();
		$key['competition_individual_id'] = $competition_individual->competition_individual_id;
		$return = $this->perform('table_workoutdb_phase_individual->deleteForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// Delete the conpetition_individual
		$return = $this->perform('table_workoutdb_competition_individual->delete',$competition_individual->competition_individual_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		return $this->return_handler->results(202,"Individual removed from competition",new stdClass());
	}

	public function getIndividualForId( $p_competition_individual_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "ci.id competition_individual_id, ci.competition_id competition_id, ci.competition_group_id competition_group_id, ";
		$sql .= "pi.id phase_individual_id ";
		$sql .= "FROM ";
		$sql .= "competition_individual ci ";
		$sql .= "LEFT OUTER JOIN phase_individual pi ";
		$sql .= "ON pi.competition_individual_id = ci.id ";
		$sql .= "WHERE ci.id = " . $p_competition_individual_id . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entry = null;
		foreach( $rows as $row ) {
			if ( is_null($entry) ) {
				$entry->competition_id = $row->competition_id;
				$entry->competition_group_id = $row->competition_group_id;
				$entry->competition_individual_id = $row->competition_individual_id;
				$entry->phase_individual_id = array();
			}
			if ( !is_null($row->phase_individual_id) ) {
				$entry->phase_individual_id[] = cast_int($row->phase_individual_id);
			}
		}
		
		return $this->return_handler->results(200,"",$entry);
	}
	
}