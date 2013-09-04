<?php

class api_permission extends CI_Model {

	private static $permission = array();

	private static $return_handler;

	public function __construct() {
		 // echo "-api_permission start-";
		 
		parent::__construct();
		// does the session have access to the system
		self::$permission['system'] = 1;
		// does the session have access to the application within the system
		self::$permission['app|test'] = 11;
		self::$permission['app|p_staff'] = 21;
		self::$permission['app|t_staff'] = 22;
		self::$permission['app|w_staff'] = 23;
		self::$permission['app|w_app'] = 30;
		self::$permission['app|w_admin'] = 40;
		// does the session have access to the action within the system
		self::$permission['table|getSchema|GET'] = 1001;
		self::$permission['table|getAll|GET'] = 1002;

		self::$return_handler = &get_instance()->return_handler;
		
		// echo "-api_permission end-";
	}

	public static function getAll() {
		return self::$return_handler->results(200,"",self::$permission);
	}

	public static function testSystemPermission() {
		// echo "testSystemPermission<br />";

		return self::$return_handler->results(200,"",new stdClass());
	}

	public static function testApplictationPermission($p_app = null) {
		// echo "testApplicationPermission<br />";

		$index = 'app|' . $p_app;

		// echo "index:$index<br />";
		return self::$return_handler->results(200,"",new stdClass());
	}

	public static function testActionPermission($p_action,$p_method) {
		// echo "testActionPermission<br />";

		$index = $p_action . '|' . $p_method;

		// echo "index:$index<br />";
		return self::$return_handler->results(200,"",new stdClass());
	}

}