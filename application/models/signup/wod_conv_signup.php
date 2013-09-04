<?php

class wod_conv_signup extends CI_Model {
	
	public function __construct() {
		parent::__construct();
		//
		// used if generic_api methods ar used
		$this->database_name = 'workoutdb';
		$this->table_name = 'calendar';
	}
	
	public function get( $params = array() ) {
		$params = (array) $params;
		
		// Set the server's default timezone
		date_default_timezone_set('America/Los_Angeles');
		
		$date = mktime(0,0,0,11,3,2012);
		
		$sql  = "SELECT wod.id, wod.date ";
		$sql .= "FROM calendar_entry_template_wod wod ";
		$sql .= "ORDER BY wod.id, wod.date ";
		
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			
			$this->load->model('table/mysql_table_model');
			foreach ( $rows as $row ) {
				// echo "Row:"; print_r($row); "<br />";
				
				$new = date('Ymd',$row->date);
				
				// echo $row->id . " -- " . $new . "<br />";
				
				$data = new stdClass();
				$data->id = (int) $row->id;
				$data->yyyymmdd = $new;
				// update the calendar event.
				$this->mysql_table_model->put('workoutdb','calendar_entry_template_wod',$data);
				unset($data);
				
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
}
?>