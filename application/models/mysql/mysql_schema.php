<?php

class mysql_schema extends CI_Model {
	
	private static $schema;
	private static $database;
	private static $database_schema;
	
	private static $db;
	private static $return_handler;
	
	public function __construct() {
		// echo "-mysql_schema start-";
		parent::__construct();
		//
		// get the return_handler model from the CI instance
		self::$return_handler = &get_instance()->return_handler;
		//
		// load the workoutdb schema
		// $this->loadSchema();
		
		self::$schema = json_decode(file_get_contents('../private/application/config/schema/workoutdb.json'));
		// echo "-mysql_schema end-";
	}
	
	public function loadSchema() {
		self::$database = 'workoutdb';
		self::$database_schema = $this->db->database;
		// Connect to mysql information_schema
		$this->load->database('information_schema',TRUE);
		
		// load the Tables and Columns
		self::loadTablesColumns();
		
		// load the the unique keys
		self::loadUniqueKeys();
		
		// load the table alias object for each database
		// load the column alieas object for each table
		self::loadTableAndColumnAlias();
		
		// Connect to mysql workoutdb
		$this->load->database('workoutdb',TRUE);
	}

	private function loadTablesColumns() {
		// echo "in loadTablesColumns<br />";
		
		// get the column definitions for the table's columns
		$sql  = "SELECT T.TABLE_SCHEMA, T.TABLE_NAME, ";
		$sql .= "C.COLUMN_NAME, C.DATA_TYPE, C.COLUMN_DEFAULT, C.IS_NULLABLE ";
		$sql .= "FROM TABLES T ";
		$sql .= "LEFT OUTER JOIN COLUMNS C ";
		$sql .= "ON C.TABLE_SCHEMA = T.TABLE_SCHEMA AND C.TABLE_NAME = T.TABLE_NAME ";
		$sql .= "WHERE T.TABLE_CATALOG = 'def' AND T.TABLE_SCHEMA = '" . self::$database_schema . "' ";
		$sql .= "ORDER BY T.TABLE_CATALOG, T.TABLE_SCHEMA, T.TABLE_NAME, C.ORDINAL_POSITION ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return;
		}
		$rows = $query->result();
		
		self::$schema = new stdClass();
		foreach ( $rows as $row ) {
			// echo "row:"; print_r($row); echo "<br />";
			
			// If the database node does not exist, create it
			if ( !property_exists(self::$schema,self::$database) ) {
				self::$schema->{self::$database} = new stdClass();
			}
			
			// if the table node does not exist, create it
			if ( !property_exists(self::$schema->{self::$database},$row->TABLE_NAME) ) {
				// echo "- " . self::$database . ":" . $row->TABLE_NAME . "- ";
				self::$schema->{self::$database}->{$row->TABLE_NAME} = new stdClass();
				// store the table alias
				self::$schema->{self::$database}->{$row->TABLE_NAME}->alias = mysql_alias::get_table(self::$database,$row->TABLE_NAME);
				// initialize the column and unique key objects
				self::$schema->{self::$database}->{$row->TABLE_NAME}->columns = new stdClass();
				self::$schema->{self::$database}->{$row->TABLE_NAME}->unique_keys = array();
			}
			
			if ( !is_null($row->COLUMN_NAME) ) {
				//
				// Create the column node
				self::$schema->{self::$database}->{$row->TABLE_NAME}->columns->{$row->COLUMN_NAME} = new stdClass();
				//
				// Create a pointer to the new column node
				$column = &self::$schema->{self::$database}->{$row->TABLE_NAME}->columns->{$row->COLUMN_NAME};
				//
				// store the column alias
				$column->alias = mysql_alias::get_column(self::$database,$row->TABLE_NAME,$row->COLUMN_NAME);
				// store the column data type
				$column->type = $row->DATA_TYPE;
				// store if the column value can be null
				if ( $row->IS_NULLABLE == "YES" ) {
					$column->nullable = TRUE;
				} else {
					$column->nullable = FALSE;
				}
				// store the column default value
				$return = self::cast_value($row->COLUMN_DEFAULT,$column->type);
				$column->default = $return['response'];
			}
		}
	}

	private function loadUniqueKeys() {
		
		// print_r(self::$schema);

		$sql  = "SELECT T1.TABLE_SCHEMA, T1.TABLE_NAME, T1.CONSTRAINT_NAME, T1.COLUMN_NAME, T1.ORDINAL_POSITION, T2.CONSTRAINT_TYPE ";
		$sql .= "FROM KEY_COLUMN_USAGE T1, ";
		$sql .= "TABLE_CONSTRAINTS T2 ";
		$sql .= "WHERE T1.TABLE_CATALOG = 'def' ";
		$sql .= "AND T1.TABLE_SCHEMA = '" . self::$database_schema . "' ";
		$sql .= "AND T2.CONSTRAINT_CATALOG = T1.CONSTRAINT_CATALOG ";
		$sql .= "AND T2.CONSTRAINT_SCHEMA = T1.CONSTRAINT_SCHEMA ";
		$sql .= "AND T2.CONSTRAINT_NAME = T1.CONSTRAINT_NAME ";
		$sql .= "AND T2.TABLE_SCHEMA = T1.TABLE_SCHEMA ";
		$sql .= "AND T2.TABLE_NAME = T2.TABLE_NAME ";
		$sql .= "AND T2.CONSTRAINT_TYPE IN ('UNIQUE','PRIMARY KEY') ";
		$sql .= "GROUP BY T1.TABLE_SCHEMA, T1.TABLE_NAME, T1.CONSTRAINT_NAME, T1.ORDINAL_POSITION ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return;
		}
		$rows = $query->result();
		
		$keys = array();
		$key = array();
		$last_database = null;
		$last_table = null;
		$last_constraint = null;
		foreach ( $rows as $row ) {
			// echo "row:"; print_r($row); echo "<br />";
			
			if ( $row->TABLE_SCHEMA != $last_database ||
			     $row->TABLE_NAME != $last_table ||
			     $row->CONSTRAINT_NAME != $last_constraint ) {
				if ( !is_null($last_constraint) ) {
					array_push($table->unique_keys,$key);
					unset($key);
				}
				
				$table = &self::$schema->{self::$database}->{$row->TABLE_NAME};
				
				$last_database = $row->TABLE_SCHEMA;
				$last_table = $row->TABLE_NAME;
				$last_constraint = $row->CONSTRAINT_NAME;
				$key = array();
			}
			
			$key[] = $row->COLUMN_NAME;
		}
		
		if ( count($key) > 0 ) {
			array_push($table->unique_keys,$key);
			unset($key);
		}
	}

	private function loadTableAndColumnAlias() {
		foreach( self::$schema as $database_name => $database ) {
			$table_alias = new stdClass();
			foreach( $database as $table_name => &$table ) {
				$table_alias->{$table->alias} = $table_name;
				
				$column_alias = new stdClass();
				foreach( $table->columns as $column_name => $column ) {
					$column_alias->{$column->alias} = $column_name;
				}
				
				$table->column_alias = $column_alias;
				unset($column_alias);
			}
			$database->table_alias = $table_alias;
			unset($table_alias);
		}
		
	}

	public static function cast_value($p_value,$p_type) {
		// ------------------------------------------------------
		// cast the value passed in to the type passed in
		// ------------------------------------------------------
		// set and cast the column value
		$value = null;
		if ( is_null($p_value) ) {
			$value = null;
		} else if ( $p_type == 'varchar' || $p_type == 'text' ) {
			$value = (string) $p_value;
		} else if ( $p_type == 'int' || $p_type == 'bigint' ) {
			$value = (integer) $p_value;
		} else if ( $p_type == 'tinyint' || $p_type == 'boolean' ) {
			$value = (boolean) $p_value;
		} else if ( $p_type == 'decimal' ) {
			$value = (float) $p_value;
		} else {
			$value = $p_value;
		}

		return self::$return_handler->results(200,"",$value);
	}
	
	public static function cast_row($p_database,&$row) {
		foreach( $row as $table_column => $value ) {
			$temp = explode('.',$table_column);
			if ( count($temp) == 2 ) {
				// cast the column based on the table/column type
				$return = self::cast_column($p_database,$temp[0],$temp[1],$value);
				if ( $return['status'] == 200 ) {
					$row->{$table_column} = $return['response']->value;
				}
			} else if ( count($temp) == 3 ) {
				if ( $temp[2] == 'json' ) {
					$row->{$table_column} = json_decode($value);
				} else {
					// cast the column based on the 3rd part of the column name {table}.{column}.{cast as}
					$return = self::cast_value($value,$temp[2]);
					if ( $return['status'] == 200 ) {
						$row->{$table_column} = $return['response'];
					}
				}
			}
		}
	}
	
	public static function cast_column($p_database_name,$p_table_name,$p_column_name,$p_value) {
		// echo "mysql_schema::cast_column $p_database_name $p_table_name $p_column_name $o_value<br /";
		
		// Was a valid database passed in
		if ( !is_null($p_database_name) && !empty($p_database_name) && !property_exists(self::$schema,$p_database_name) ) {
			return self::$return_handler->results(400,"'" . $p_database_name . "' is not a valid database",new stdClass());
		}
		// Was a valid database/table passed in
		if ( !is_null($p_table_name) && !empty($p_table_name) && !property_exists(self::$schema->{$p_database_name},$p_table_name) ) {
			return self::$return_handler->results(400,"'" . $p_database_name . "." . $p_table_name . "' is not a valid table",new stdClass());
		}
		// Was a valid database/table/column passed in
		if ( !is_null($p_column_name) && !empty($p_column_name) && !property_exists(self::$schema->{$p_database_name}->{$p_table_name}->columns,$p_column_name) ) {
			return self::$return_handler->results(400,"'" . $p_database_name . "." . $p_table_name . "." . $p_column_name . "' is not a valid column",new stdClass());
		}
		// cast the value to its column type
		$return = self::cast_value($p_value,self::$schema->{$p_database_name}->{$p_table_name}->columns->{$p_column_name}->type);
		
		$response = new stdClass();
		$response->value = $return['response'];
		return self::$return_handler->results(200,'',$response);
	}

	public static function objectify_row($row,$use_alias=false) {
		// Objectify at the table level and use column aliases if asked to
		$return = new stdClass();
		
		foreach( $row as $table_column => $value ) {
			$temp = explode('.',$table_column);
			if ( count($temp) >= 2 ) {
				$table_name = $temp[0];
				$column_name = $temp[1];
				// Use alias
				if ( $use_alias && property_exists(self::$schema->workoutdb,$temp[0]) && property_exists(self::$schema->workoutdb->{$temp[0]}->columns,$temp[1]) ) {
					$column_name = self::$schema->workoutdb->$temp[0]->columns->{$temp[1]}->alias;
				}
				$return->{$table_name}->{$column_name} = $value;
			} else {
				$return->{$table_column} = $value;
			}
		}
		
		foreach( $return as $key => &$value ) {
			if ( is_object($value) ) {
				if ( property_exists($value,'height') && property_exists($value,'height_uom_id') ) {
					$height = format_height($value->height,$value->height_uom_id);
					unset($value->height);
					unset($value->height_uom_id);
					$value->height = $height;
					unset($height);
				}
				if ( property_exists($value,'weight') && property_exists($value,'weight_uom_id') ) {
					$weight = format_weight($value->weight,$value->weight_uom_id);
					unset($value->weight);
					unset($value->weight_uom_id);
					$value->weight = $weight;
					unset($weight);
				}
				if ( property_exists($value,'result') && property_exists($value,'result_uom_id') ) {
					$result_unit = format_result_unit($value->result,$value->result_uom_id);
					unset($value->result);
					unset($value->result_uom_id);
					$value->result_unit = $result_unit;
					unset($result_unit);
				}
				if ( property_exists($value,'time_limit') && property_exists($value,'time_limit_uom_id') && property_exists($value,'time_limit_note') ) {
					$time_limit = format_time_limit($value->time_limit,$value->time_limit_uom_id,$value->time_limit_note);
					unset($value->time_limit);
					unset($value->time_limit_uom_id);
					unset($value->time_limit_note);
					$value->time_limit = $time_limit;
					unset($time_limit);
				}
				if ( property_exists($value,'timezone') ) {
					if ( is_null($value->timezone) || empty($value->timezone) ) {
						$value->timezone_offset = null;
					} else {
						$date_time_zone = new DateTimeZone($value->timezone);
					    $date_time = new DateTime('now', $date_time_zone);
					    $value->timezone_offset = $date_time_zone->getOffset($date_time);
						unset($date_time_zone);
						unset($date_time);
					}
				}
			}
		}
		
		return $return;
	}

	public static function getTableAlias($p_database,$p_table_name,$p_use_alias) {
		if ( $p_use_alias && property_exists(self::$schema,$p_database) && property_exists(self::$schema->{$p_database},$p_table_name) ) {
			return self::$schema->{$p_database}->{$p_table_name}->alias;
		}
		return $p_table_name;
	}

	public static function getColumnAlias($p_database,$p_table_name,$p_column_name,$p_use_alias) {
		if ( $p_use_alias && property_exists(self::$schema,$p_database) && property_exists(self::$schema->{$p_database},$p_table_name) && property_exists(self::$schema->{$p_database}->{$p_table_name}->columns,$p_column_name) ) {
			return self::$schema->{$p_database}->{$p_table_name}->columns->{$p_column_name}->alias;
		}
		return $p_column_name;
	}

	public static function get($p_database_name = null,$p_table_name = null) {
		// echo "mysql_schema::get $p_database_name $p_table_name<br /";
		
		// Was a valid database passed in
		if ( !is_null($p_database_name) && !property_exists(self::$schema,$p_database_name) ) {
			return self::$return_handler->results(400,"'" . $p_database_name . "' is not a valid database",new stdClass());
		}
		// Was a valid database/table passed in
		if ( !is_null($p_database_name) && !is_null($p_table_name) && !property_exists(self::$schema->{$p_database_name},$p_table_name) ) {
			return self::$return_handler->results(400,"'" . $p_table_name . "' is not a valid table",new stdClass());
		}
		
		if ( !is_null($p_database_name) ) {
			if ( !is_null($p_table_name) ) {
				// return the table's schema
				return self::$return_handler->results(200,"",self::$schema->{$p_database_name}->{$p_table_name});
			}
			// return the database's schema
			return self::$return_handler->results(200,"",self::$schema->{$p_database_name});
		}
		// return the complete schema
		return self::$return_handler->results(200,"",self::$schema);
	}
	
	public static function refreshSchemaFile($p_database_name = null) {
		// echo "mysql_schema::refreshSchemaFile<br />";
		$return = self::get();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$data = json_encode($return['response']);
		
		$filename = '../private/application/config/schema/workoutdb.json';
		if ( !file_exists($filename) ) {
			return self::$return_handler->results(400,"schema file does not exist",new stdClass());
		}

		$fh = fopen($filename,'w');
		if ( !$fh ) {
			return self::$return_handler->results(400,"schema file could not be Opened for write",new stdClass());
		}

		$bytes = fwrite($fh,$data);
		if ( !$bytes ) {
			return self::$return_handler->results(400,"schema file could not be written to",new stdClass());
		}

		fclose($fh);

		return self::$return_handler->results(200,"",new stdClass());
	}

	public static function validTable($p_database_name = null,$p_table_name = null) {
		// echo "mysql_schema::get $p_database_name $p_table_name<br /";
		
		// Was a valid database passed in
		if ( !is_null($p_database_name) && !property_exists(self::$schema,$p_database_name) ) {
			return self::$return_handler->results(400,"'" . $p_database_name . "' is not a valid database",new stdClass());
		}
		// Was a valid database/table passed in
		if ( !is_null($p_database_name) && !is_null($p_table_name) && !property_exists(self::$schema->{$p_database_name},$p_table_name) ) {
			return self::$return_handler->results(400,"'" . $p_table_name . "' is not a valid table",new stdClass());
		}
		// return the complete schema
		return self::$return_handler->results(200,"",new stdClass());
	}
}