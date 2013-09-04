<?php

class test_signup extends CI_Model {
	
	public function __construct() {
		parent::__construct();
		//
		// used if generic_api methods ar used
		$this->database_name = 'workoutdb';
		$this->table_name = 'calendar';
	}
	
	public function get( $params = array() ) {
		$params = (array) $params;
		
		$p_current = time();
		
		// Set the server's default timezone
		date_default_timezone_set('America/Los_Angeles');
		
		// Get the beginning of the month
		$month_start = mktime(0,0,0,date("m",$p_current),1,date("Y",$p_current));
		// Get the end of the month (the 1st of next month minus 1 second)
		$month_end = mktime(0,0,-1,date("m",$p_current) + 1,1,date("Y",$p_current));
		
		// echo "Month Start $month_start : $month_end -- " . date('Y/m/d H:i:s',$month_start) . " -- " . date('Y/m/d H:i:s',$month_end) . "<br />";
		
		// Get day-of-week (0:Sunday - 6:Saturday)
		$dow = date('w',$p_current);
		
		// Get the beginning of the week
		$week_start = mktime(0,0,0,date('m',$p_current),date('d',$p_current) - $dow,date("Y",$p_current));
		// Get the end of this week 
		$offset = 7 - $dow;
		$week_end = mktime(0,0,-1,date('m',$p_current),date('d',$p_current) + $offset,date("Y",$p_current));
		
		// echo "Week Start $week_start : $week_end -- " . date('Y/m/d H:i:s w',$week_start) . " -- " . date('Y/m/d H:i:s w',$week_end) . "<br />";
		
		// Get the beginning of the Day
		$day_start = mktime(0,0,0,date('m',$p_current),date('d',$p_current),date("Y",$p_current));
		// Get the end of the day (tomorrow minus 1 second)
		$day_end = mktime(0,0,-1,date('m',$p_current),date('d',$p_current) + 1,date("Y",$p_current));
		
		// echo "Day Start $day_start : $day_end -- " . date('Y/m/d H:i:s',$day_start) . " -- " . date('Y/m/d H:i:s',$day_end) . "<br />";
		
		// Get the start of 6 months ago
		$start_6month = mktime(0,0,0,date("m",$p_current) - 6,1,date("Y",$p_current));
		
		// echo "6 Month Start $start_6month -- " . date('Y/m/d H:i:s',$start_6month) . "<br />";
		
		return $this->return_handler->results(200,"",new stdClass());
	}
}
?>