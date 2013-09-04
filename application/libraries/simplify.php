<?php

class simplify {
	
	protected $ci = null;
	
	protected $exercises = array();
	protected $equipment = array();
	
	protected $nodes = array();
	
	public function __construct() {
		// Get a link to the CodeIgniter instance
		$this->ci =& get_instance();
		/*
		// load recording_type lookup table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_workout_recording_type',
		                'result'=>'name');
		$this->ci->load->library('lookup_table',$params,'recording_type');
		*/
		// load exercise lookup table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_exercise',
		                'result'=>'name');
		$this->ci->load->library('lookup_table',$params,'exercise');
		
		// load equipment lookup table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_equipment',
		                'result'=>'name');
		$this->ci->load->library('lookup_table',$params,'equipment');
		
		// load unit lookup table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_measurement_system_unit',
		                'result'=>'abbr');
		$this->ci->load->library('lookup_table',$params,'unit');
		/*
		// load measurement lookup table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_measurement',
		                'result'=>'name');
		$this->ci->load->library('lookup_table',$params,'measurement');
		*/
		// load exercise / equipment pass through table
		$params = array('database_name'=>'workoutdb',
		                'table_name'=>'library_exercise_equipment',
		                'keys'=>array('library_exercise_id','library_equipment_id'),
		                'result'=>'mandatory'
		               );
		$this->ci->load->library('lookup_table',$params,'ex_eq_xref');
		
		// load the return_handler (auto loaded)
		// $this->ci->load->library('return_handler');
		
		$this->expression_variables = array(
			'{#maxe}' => 'Max',
			'{#brn}' => 'Number of Round',
			'{#bbw}' => 'BodyWeight',
			'{#mom}' => 'Minute on Minute',
			'{#1RM}' => '1RM'
		);
		
		// $nodes describes how to validate and simplify our workout structure
		$this->nodes = array();
		$this->nodes['workout_log'] = array();
		$this->nodes['workout_log']['complete_round'] = new stdClass();
		$this->nodes['workout_log']['incomplete_round'] = new stdClass();
		$this->nodes['workout_log']['complete_round']->type = 'object';
		$this->nodes['workout_log']['incomplete_round']->type = 'object';
		$this->nodes['workout_log']['complete_round']->use = TRUE;
		$this->nodes['workout_log']['incomplete_round']->use = TRUE;
		
		$this->nodes['complete_round'] = array();
		$this->nodes['complete_round']['time_limit'] = new stdClass();
		$this->nodes['complete_round']['repeats'] = new stdClass();
		$this->nodes['complete_round']['set'] = new stdClass();
		$this->nodes['complete_round']['time_limit']->type = 'object';
		$this->nodes['complete_round']['repeats']->type = 'object';
		$this->nodes['complete_round']['set']->type = 'array';
		$this->nodes['complete_round']['time_limit']->use = TRUE;
		$this->nodes['complete_round']['repeats']->use = TRUE;
		$this->nodes['complete_round']['set']->use = TRUE;
		
		$this->nodes['incomplete_round'] = array();
		$this->nodes['incomplete_round']['time_limit'] = new stdClass();
		$this->nodes['incomplete_round']['repeats'] = new stdClass();
		$this->nodes['incomplete_round']['set'] = new stdClass();
		$this->nodes['incomplete_round']['time_limit']->type = 'object';
		$this->nodes['incomplete_round']['repeats']->type = 'object';
		$this->nodes['incomplete_round']['set']->type = 'array';
		$this->nodes['incomplete_round']['time_limit']->use = TRUE;
		$this->nodes['incomplete_round']['repeats']->use = TRUE;
		$this->nodes['incomplete_round']['set']->use = TRUE;
		
		$this->nodes['workout'] = array();
		$this->nodes['workout']['time_limit'] = new stdClass();
		$this->nodes['workout']['repeats'] = new stdClass();
		$this->nodes['workout']['set'] = new stdClass();
		$this->nodes['workout']['time_limit']->type = 'object';
		$this->nodes['workout']['repeats']->type = 'object';
		$this->nodes['workout']['set']->type = 'array';
		$this->nodes['workout']['time_limit']->use = TRUE;
		$this->nodes['workout']['repeats']->use = TRUE;
		$this->nodes['workout']['set']->use = TRUE;
		
		$this->nodes['set'] = array();
		$this->nodes['set']['time_limit'] = new stdClass();
		$this->nodes['set']['repeats'] = new stdClass();
		$this->nodes['set']['break_time'] = new stdClass();
		$this->nodes['set']['exercise_group'] = new stdClass();
		$this->nodes['set']['time_limit']->type = 'object';
		$this->nodes['set']['repeats']->type = 'object';
		$this->nodes['set']['break_time']->type = 'object';
		$this->nodes['set']['exercise_group']->type = 'array';
		$this->nodes['set']['time_limit']->use = TRUE;
		$this->nodes['set']['repeats']->use = TRUE;
		$this->nodes['set']['break_time']->use = TRUE;
		$this->nodes['set']['exercise_group']->use = TRUE;
		
		$this->nodes['exercise_group'] = array();
		$this->nodes['exercise_group']['time_limit'] = new stdClass();
		$this->nodes['exercise_group']['repeats'] = new stdClass();
		$this->nodes['exercise_group']['break_time'] = new stdClass();
		$this->nodes['exercise_group']['exercise'] = new stdClass();
		$this->nodes['exercise_group']['time_limit']->type = 'object';
		$this->nodes['exercise_group']['repeats']->type = 'object';
		$this->nodes['exercise_group']['break_time']->type = 'object';
		$this->nodes['exercise_group']['exercise']->type = 'object';
		$this->nodes['exercise_group']['time_limit']->use = TRUE;
		$this->nodes['exercise_group']['repeats']->use = TRUE;
		$this->nodes['exercise_group']['break_time']->use = TRUE;
		$this->nodes['exercise_group']['exercise']->use = TRUE;
		
		$this->nodes['exercise'] = array();
		$this->nodes['exercise']['id'] = new stdClass();
		$this->nodes['exercise']['name'] = new stdClass();
		$this->nodes['exercise']['media'] = new stdClass();
		$this->nodes['exercise']['distance_measurement'] = new stdClass();
		$this->nodes['exercise']['equipment'] = new stdClass();
		$this->nodes['exercise']['id']->type = 'integer';
		$this->nodes['exercise']['name']->type = 'string';
		$this->nodes['exercise']['media']->type = 'object';
		$this->nodes['exercise']['distance_measurement']->type = 'object';
		$this->nodes['exercise']['equipment']->type = 'array';
		$this->nodes['exercise']['id']->use = TRUE;
		$this->nodes['exercise']['name']->use = FALSE;
		$this->nodes['exercise']['media']->use = FALSE;
		$this->nodes['exercise']['distance_measurement']->use = TRUE;
		$this->nodes['exercise']['equipment']->use = TRUE;
		
		$this->nodes['equipment'] = array();
		$this->nodes['equipment']['id'] = new stdClass();
		$this->nodes['equipment']['name'] = new stdClass();
		$this->nodes['equipment']['deletable'] = new stdClass();
		$this->nodes['equipment']['media'] = new stdClass();
		$this->nodes['equipment']['unit'] = new stdClass();
		$this->nodes['equipment']['id']->type = 'integer';
		$this->nodes['equipment']['name']->type = 'string';
		$this->nodes['equipment']['deletable']->type = 'bool';
		$this->nodes['equipment']['media']->type = 'object';
		$this->nodes['equipment']['unit']->type = 'object';
		$this->nodes['equipment']['id']->use = TRUE;
		$this->nodes['equipment']['name']->use = FALSE;
		$this->nodes['equipment']['deletable']->use = FALSE;
		$this->nodes['equipment']['media']->use = FALSE;
		$this->nodes['equipment']['unit']->use = TRUE;
		
		$this->nodes['unit'] = array();
		$this->nodes['unit']['id'] = new stdClass();
		$this->nodes['unit']['man'] = new stdClass();
		$this->nodes['unit']['woman'] = new stdClass();
		$this->nodes['unit']['id']->type = 'integer';
		$this->nodes['unit']['man']->type = 'object';
		$this->nodes['unit']['woman']->type = 'object';
		$this->nodes['unit']['id']->use = TRUE;
		$this->nodes['unit']['man']->use = TRUE;
		$this->nodes['unit']['woman']->use = TRUE;
		
		$this->nodes['man'] = array();
		$this->nodes['man']['input'] = new stdClass();
		$this->nodes['man']['note'] = new stdClass();
		$this->nodes['man']['input']->type = 'string';
		$this->nodes['man']['note']->type = 'string';
		$this->nodes['man']['input']->use = TRUE;
		$this->nodes['man']['note']->use = TRUE;
		
		$this->nodes['woman'] = array();
		$this->nodes['woman']['input'] = new stdClass();
		$this->nodes['woman']['note'] = new stdClass();
		$this->nodes['woman']['input']->type = 'string';
		$this->nodes['woman']['note']->type = 'string';
		$this->nodes['woman']['input']->use = TRUE;
		$this->nodes['woman']['note']->use = TRUE;
		
		$this->nodes['distance_measurement'] = array();
		$this->nodes['distance_measurement']['id'] = new stdClass();
		$this->nodes['distance_measurement']['value'] = new stdClass();
		$this->nodes['distance_measurement']['id']->type = 'integer';
		$this->nodes['distance_measurement']['value']->type = 'object';
		$this->nodes['distance_measurement']['id']->use = TRUE;
		$this->nodes['distance_measurement']['value']->use = TRUE;
		
		$this->nodes['time_limit'] = array();
		$this->nodes['time_limit']['id'] = new stdClass();
		$this->nodes['time_limit']['value'] = new stdClass();
		$this->nodes['time_limit']['id']->type = 'integer';
		$this->nodes['time_limit']['value']->type = 'object';
		$this->nodes['time_limit']['id']->use = TRUE;
		$this->nodes['time_limit']['value']->use = TRUE;
		
		$this->nodes['break_time'] = array();
		$this->nodes['break_time']['id'] = new stdClass();
		$this->nodes['break_time']['value'] = new stdClass();
		$this->nodes['break_time']['id']->type = 'integer';
		$this->nodes['break_time']['value']->type = 'object';
		$this->nodes['break_time']['id']->use = TRUE;
		$this->nodes['break_time']['value']->use = TRUE;
		
		$this->nodes['value'] = array();
		$this->nodes['value']['input'] = new stdClass();
		$this->nodes['value']['note'] = new stdClass();
		$this->nodes['value']['input']->type = 'string';
		$this->nodes['value']['note']->type = 'string';
		$this->nodes['value']['input']->use = TRUE;
		$this->nodes['value']['note']->use = TRUE;
		
		$this->nodes['repeats'] = array();
		$this->nodes['repeats']['input'] = new stdClass();
		$this->nodes['repeats']['note'] = new stdClass();
		$this->nodes['repeats']['input']->type = 'string';
		$this->nodes['repeats']['note']->type = 'string';
		$this->nodes['repeats']['input']->use = TRUE;
		$this->nodes['repeats']['note']->use = TRUE;
	}
	
	public function printLookupTables() {
		/*
		echo "<br />recording_type<br />";
		$this->ci->recording_type->printAll();
		*/
		echo "<br />exercise<br />";
		$this->ci->exercise->printAll();
		
		echo "<br />equipment<br />";
		$this->ci->equipment->printAll();
		
		echo "<br />unit<br />";
		$this->ci->unit->printAll();
		/*
		echo "<br />measurement<br />";
		$this->ci->measurement->printAll();
		*/
		echo "<br />ex_eq_xref<br />";
		$this->ci->ex_eq_xref->printAll();
		
		return $this->ci->return_handler->results(200,"",new stdClass());
	}
	
	public function workout_log($p_workout_log) {
		// echo "simplify->workout<br />";
		
		// clear the list of exercises for the workout
		$this->exercises = array();
		// clear the list of equipment for the workout
		$this->equipment = array();
		
		// The workout must be an object
		if ( !is_object($p_workout_log) ) {
			return $this->ci->return_handler->results(400,"workout is not an object",new stdClass());
		}
		// create the workout
		$return = $this->simplify_object($node='workout_log',$p_workout_log,$breadcrumb='workout_log');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$response = new stdClass();
		$response->workout_log = $return['response'];
		$response->exercises = $this->exercises;
		$response->equipment = $this->equipment;
		
		return $this->ci->return_handler->results(200,'',$response);
	}
	
	public function workout($p_workout) {
		// echo "simplify->workout<br />\n";
		
		// clear the list of exercises for the workout
		$this->exercises = array();
		// clear the list of equipment for the workout
		$this->equipment = array();
		
		// The workout must be an object
		if ( !is_object($p_workout) ) {
			return $this->ci->return_handler->results(400,"workout is not an object",new stdClass());
		}
		
		// print_r($p_workout);
		
		// create the workout
		$return = $this->simplify_object($node='workout',$p_workout,$breadcrumb='workout');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$response = new stdClass();
		$response->workout = $return['response'];
		$response->exercises = $this->exercises;
		$response->equipment = $this->equipment;
		
		return $this->ci->return_handler->results(200,'',$response);
	}
	
	// ==============================================================================================================================================
	// Simplify the Workout
	// ==============================================================================================================================================
	
	protected function simplify_object($p_node_name,&$p_object, $p_breadcrumb) {
		// echo "simplify->simplify_object node:$p_node_name breadcrumb:$p_breadcrumb<br />\n";
		// ---------------------------------------------------------------------------------------------------------------
		// Initialize the simplified object
		// ---------------------------------------------------------------------------------------------------------------
		$simple_object = new stdClass();
		// ---------------------------------------------------------------------------------------------------------------
		// if the node is empty, return
		// ---------------------------------------------------------------------------------------------------------------
		if ( count((array) $p_object) == 0 ) {
			return $this->ci->return_handler->results(200,'',$simple_object);
		}
		// ---------------------------------------------------------------------------------------------------------------
		// Validate missing, unknown, and invalid type nodes
		// ---------------------------------------------------------------------------------------------------------------
		if ( !array_key_exists($p_node_name,$this->nodes) ) {
			return $this->ci->return_handler->results(400,$p_breadcrumb . " do not know how to validate a " . $p_node_name . " node.",new stdClass());
		}

		$return = $this->validate_nodes($p_node_name,$p_object,$p_breadcrumb);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ---------------------------------------------------------------------------------------------------------------
		// Simplify the validated object and array nodes
		// ---------------------------------------------------------------------------------------------------------------
		// echo "$p_node_name<br />\n";
		foreach( $p_object as $key => $value ) {
			if ( $this->nodes[$p_node_name][$key]->use ) {
				if ( is_object($value) ) {
					$return = $this->simplify_object($key,$value,$p_breadcrumb . '.' . $key);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$simple_object->{$key} = $return['response'];
				} else if ( is_array($value) ) {
					$return = $this->simplify_array($key,$value,$p_breadcrumb . '.' . $key);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$simple_object->{$key} = $return['response'];
				} else {
					// cast the value and put it in simple_object
					$cmd = '$simple_object->' . $key . ' = (' . $this->nodes[$p_node_name][$key]->type . ') $value;';
					eval($cmd);
				}
			}
		}
		// ---------------------------------------------------------------------------------------------------------------
		// if the current node is logically empty, simplify it to an empty node
		// ---------------------------------------------------------------------------------------------------------------
		$simple_object = $this->empty_object($p_node_name,$simple_object);
		// ---------------------------------------------------------------------------------------------------------------
		// if man not empty and woman is empty, clone man into woman. And vise versa.
		// ---------------------------------------------------------------------------------------------------------------
		if ( property_exists($simple_object,'man') && property_exists($simple_object,'woman') ) {
			if ( count((array) $simple_object->man) > 0 && count((array) $simple_object->woman) == 0 ) {
				$simple_object->woman = clone $simple_object->man;
			} else if ( count((array) $simple_object->man) == 0 && count((array) $simple_object->woman) > 0 ) {
				$simple_object->man = clone $simple_object->woman;
			}
		}
		// ---------------------------------------------------------------------------------------------------------------
		// if this is an exercise node, does it contain all mandatory equipment?
		// ---------------------------------------------------------------------------------------------------------------
		if ( $p_node_name == 'exercise' ) {
			$return = $this->validate_mandatory_equipment($simple_object,$p_breadcrumb);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// ---------------------------------------------------------------------------------------------------------------
		// if this is an exercise node and it is not empty, add its id to the workout's exercise list
		// ---------------------------------------------------------------------------------------------------------------
		if ( $p_node_name == 'exercise' ) {
			// echo "exercise:"; var_dump($simple_object); echo "<br />";
			if ( property_exists($simple_object,'id') && !in_array($simple_object->id,$this->exercises) ) {
				$this->exercises[] = $simple_object->id;
			}
		}
		// ---------------------------------------------------------------------------------------------------------------
		// if this is an equipment node and it is not empty, add its id to the workout's equipment list
		// ---------------------------------------------------------------------------------------------------------------
		if ( $p_node_name == 'equipment' ) {
			// echo "equipment:"; var_dump($simple_object); echo "<br />";
			if ( property_exists($simple_object,'id') && !in_array($simple_object->id,$this->equipment) ) {
				$this->equipment[] = $simple_object->id;
			}
		} 
		// ---------------------------------------------------------------------------------------------------------------
		// return the simplified object
		// ---------------------------------------------------------------------------------------------------------------
		return $this->ci->return_handler->results(200,'',$simple_object);
	}
	
	protected function simplify_array($p_node_name, &$p_array, $p_breadcrumb) {
		// echo "simplify->simplify_array node:$p_node_name breadcrumb:$p_breadcrumb<br />\n";
		// ---------------------------------------------------------------------------------------------------------------
		// Initialize the simplified array
		// ---------------------------------------------------------------------------------------------------------------
		$simple_array = array();
		// ---------------------------------------------------------------------------------------------------------------
		// validate and simplify each entry
		// ---------------------------------------------------------------------------------------------------------------
		foreach( $p_array as $key => $value ) {
			// ---------------------------------------------------------------------------------------------------------------
			// validate each entry of the array
			// ---------------------------------------------------------------------------------------------------------------
			if ( !is_object($value) ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "[" . $key . "] is not an object",new stdClass());
			}
			// ---------------------------------------------------------------------------------------------------------------
			// simplify each entry of the array
			// ---------------------------------------------------------------------------------------------------------------
			$return = $this->simplify_object($p_node_name,$value,$p_breadcrumb . '[' . $key . ']');
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// ---------------------------------------------------------------------------------------------------------------
			// add the validated and simplified entry to the simplified array
			// ---------------------------------------------------------------------------------------------------------------
			$simple_array[$key] = $return['response'];
		}
		// ---------------------------------------------------------------------------------------------------------------
		// return the simplified array
		// ---------------------------------------------------------------------------------------------------------------
		return $this->ci->return_handler->results(200,'',$simple_array);
	}

	protected function validate_nodes($p_node_name,&$p_object,$p_breadcrumb) {
		// echo "simplify->validate_nodes breadcrumb:$p_breadcrumb<br />\n";
		// ---------------------------------------------------------------------------------------------------------------
        // create a node array with both type and found elements
		// ---------------------------------------------------------------------------------------------------------------
		$nodes = array();
		foreach( $this->nodes[$p_node_name] as $key => $value ) {
			$nodes[$key] = clone $value;
			$nodes[$key]->found = FALSE;
		}
		// ---------------------------------------------------------------------------------------------------------------
        // Validate the object
		// ---------------------------------------------------------------------------------------------------------------
		// var_dump($nodes);
		foreach( $p_object as $key => $value ) {
			// echo "$key<br />\n";
			/*
			echo "$key<br />";
			if ( !is_object($value) && !is_array($value) ) {
				var_dump($value); echo "<br />";
			}
			*/
			if ( !array_key_exists($key,$nodes) ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $key . " is an unknown node.",new stdClass());
			}
			if ( !property_exists($nodes[$key],'type') ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $key . " does not have a type.",new stdClass());
			}
			// if the node type is supposed to be integer or real validate that it is numeric
			$type = $nodes[$key]->type;
			if ( $type == 'integer' || $type == 'real' ) {
				$type = 'numeric';
			}
			// put the eval results into $condition
			$cmd = '$condition = !is_' . $type . '($value);';
			// echo "$cmd<br />";
			eval($cmd);
			// var_dump($condition); echo "<br />";
			if ( $condition ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $key . " is not " . $type . ".",new stdClass());
			}
			$nodes[$key]->found = TRUE;
		}
		// var_dump($nodes);
		// ---------------------------------------------------------------------------------------------------------------
        // are there missing nodes
		// ---------------------------------------------------------------------------------------------------------------
		$missing = array();
		foreach( $nodes as $key => $value ) {
			if ( !$nodes[$key]->found ) {
				$missing[] = $key;
			}
		}
		if ( count($missing) > 0 ) {
			return $this->ci->return_handler->results(400,$p_breadcrumb . " has the following nodes missing : " . implode(', ', $missing) . ".",new stdClass());
		}
		// ---------------------------------------------------------------------------------------------------------------
        // if this object contains an id, is it valid?
		// ---------------------------------------------------------------------------------------------------------------
		if ( property_exists($p_object,'id') ) {
			// echo "validate id:$p_node_name:$p_object->id<br />\n";
			if ( !$this->valid_id($p_node_name,$p_object->id) ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . ".id is an invalid id value:" . $p_object->id . ".",new stdClass());
			}
			// echo "VALID<br />\n";
			
		}
		// ---------------------------------------------------------------------------------------------------------------
        // if this object contains an inut node, is it valid?
		// ---------------------------------------------------------------------------------------------------------------
		if ( property_exists($p_object,'input') ) {
			$return = $this->validate_input_node($p_object->input,$p_breadcrumb . ".input");
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// ---------------------------------------------------------------------------------------------------------------
        // return no error
		// ---------------------------------------------------------------------------------------------------------------
		return $this->ci->return_handler->results(200,'',new stdClass());
	}
	
	protected function valid_id( $p_node_name, $p_id ) {
		// echo "simplify->valid_id node:$p_node_name id:$p_id<br />";
		if ( $p_node_name == 'exercise' ) {
			if ( $this->ci->exercise->validID($p_id) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else if ( $p_node_name == 'equipment' ) {
			if ( $this->ci->equipment->validID($p_id) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else if ( $p_node_name == 'unit' || $p_node_name = 'distance_measurement' || $p_node_name = 'time_limit' || $p_node_name = 'break_time' ) {
			if ( $this->ci->unit->validID($p_id) ) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}
	
	protected function empty_object($p_node_name,$p_object) {
		// ---------------------------------------------------------------------------------------------------------------
        // Is this object logically empty
		// ---------------------------------------------------------------------------------------------------------------
		if ( $p_node_name == 'exercise' || $p_node_name == 'equipment' ) {
			$empty_object = FALSE;
		} else {
			$empty_object = TRUE;
			foreach( $p_object as $key => $value ) {
				if ( is_object($value) ) {
					if ( count((array) $value) > 0 ) {
						$empty_object = FALSE;
						break;
					}
				} else if ( is_array($value) ) {
					$empty_object = FALSE;
					break;
				} else {
					if ( is_null($value) ) {
						continue;
					}
					if ( is_string($value) && !$value == '' ) {
						$empty_object = FALSE;
						continue;
					}
				}
			}
		}
		
		if ( $empty_object ) {
			$p_object = new stdClass();
		}
		
		return $p_object;
	}
	
	public function validate_input_node($p_input,$p_breadcrumb) {
		// echo "action_workout->format_input " . $p_input . " -- ";
		// -----------------------------------------------------------------
		// if the input is numeric, it is valid
		// -----------------------------------------------------------------
		if ( is_numeric($p_input) ) {
			return $this->ci->return_handler->results(200,$p_breadcrumb . " is OK.",$p_input);
		}
		// -----------------------------------------------------------------
		// split the input at the : if it exists
		// -----------------------------------------------------------------
		$part = explode(':',$p_input);
		// the 1st part is the formula
		$formula = $part[0];
		// -----------------------------------------------------------------		
		// Validate that the input value is numeric
		// -----------------------------------------------------------------
		if ( count($part) > 1 ) {
			if ( !is_numeric($part[1]) ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $part[1] . " is not numeric.",new stdClass());
			}
		}
		// -----------------------------------------------------------------
		// Validate the formula
		// -----------------------------------------------------------------
		// Split the formula at the *
		// -----------------------------------------------------------------
		$part = explode('*',$formula);
		// the 1st part is the variable
		$variable = $part[0];
		// -----------------------------------------------------------------
		// if it exists, the 2nd part is the number
		// -----------------------------------------------------------------
		if ( count($part) > 1 ) {
			if ( !is_numeric($part[1]) ) {
				return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $part[1] . " is not numeric.",new stdClass());
			}
		}
		// -----------------------------------------------------------------
		// Validate the variable
		// -----------------------------------------------------------------
		if ( !array_key_exists($variable,$this->expression_variables) ) {
			return $this->ci->return_handler->results(400,$p_breadcrumb . "." . $variable . " is not a valid variable.",new stdClass());
		}
		
		return $this->ci->return_handler->results(200,$p_breadcrumb . " is OK.",$p_input);
	}

	public function validate_mandatory_equipment($p_simple_exercise,$p_breadcrumb) {
		if ( property_exists($p_simple_exercise,'id') && property_exists($p_simple_exercise,'equipment') && is_array($p_simple_exercise->equipment) ) {  // do all exercise properties exist?
			$ex_lookup = $this->ci->ex_eq_xref->get($p_simple_exercise->id);  // get the exercise / equipment cross-reference table for the exercise
			if ( !is_null($ex_lookup) ) {
				// print_r($ex_lookup); echo "<br />\n";
				foreach( $ex_lookup as $equipment_id => $mandatory  ) {
					if ( (boolean) $mandatory ) {
						$found = false;
						foreach( $p_simple_exercise->equipment as $equipment ) {
							// print_r($equipment); echo "<br />\n";
							if ( $equipment_id == $equipment->id ) {
								$found = true;
							}
						}
						
						if ( !$found ) {
							// echo "$equipment_id not found!<br />\n";
							return $this->ci->return_handler->results(400,$p_breadcrumb . " is missing mandatory equipment " . $this->ci->equipment->getValue($equipment_id) . ".",new stdClass());
						}
					}
				}
			}
		}
	}
}