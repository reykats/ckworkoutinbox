<?php

class client_signup extends generic_api {
	
	public function __construct() {
		parent::__construct();
		//
		// used if generic_api methods ar used
		$this->database_name = 'workoutdb';
		$this->table_name = 'client';
	}
	
	public function get( $params = array() ) {
		if ( count($params) == 1 ) {
			if ( !is_null($params[0]) && !empty($params[0]) ){
				if ( is_numeric($params[0]) ) {
					return $this->findOne($params[0]);
				} else {
					return $this->return_handler->results(400,"ID must be numeric",new stdClass());
				}
			} else {
				return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
		}
	}
	
	public function findOne( $p_client_id ) {
		// get the calendar entry name and the workouts for that calendar entry for a given date/time
		$sql  = "SELECT c.name, ";
		$sql .= "media.id media_id, media_url media_url ";
		$sql .= "FROM client c ";
		$sql .= "LEFT OUTER JOIN client_media media ";
		$sql .= "ON media.client_id = c.id ";
		$sql .= "WHERE c.id = " . $p_client_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$row = $query->row(); 
			
			$entry = new stdClass();
			$entry->name = $row->name;
			$entry->terms_and_conditions_id = null;
			$entry->media = new stdClass();
			if ( !is_null($row->media_id) ) {
				$entry->media->id = $row->media_id;
				$entry->media->url = $row->media_url;
			}
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
	}

	public function post( $params = array(), $data ) {
		return $this->return_handler->results(400,"You can not POST to this API",new stdClass());
	}

	public function put( $params = array(), $data ) {
		return $this->return_handler->results(400,"You can not PUT to this API",new stdClass());
	}

	public function delete( $params = array() ) {
		return $this->return_handler->results(400,"You can not DELETE to this API",new stdClass());
	}
}
?>