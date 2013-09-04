<?php

class cli_schema extends action_generic {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function refresh() {
		// create the schema from the database (not the schema file)
		mysql_schema::loadSchema('workoutdb');
		
		// Update the schma file with the schema created from the database
		return mysql_schema::refreshSchemaFile('workoutdb');
	}
}