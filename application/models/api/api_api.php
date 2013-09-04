<?php

class api_api extends common_perform {

	protected $application = '';
	protected $action = '';
	protected $request_method = '';

	public function __construct() {
		//echo "-a_api_api-";
		parent::__construct();
		// echo "-b_api_api-";
	}

	public function process($params) {
		// echo "process";
		$params = (array) $params;

		// echo "params:"; print_r($params); echo "<br />";
		// echo "request method:" . $_SERVER['REQUEST_METHOD'] . "<br />";
		// echo "_GET:"; print_r($_GET); echo "<br />";
		// echo "data:"; print_r( json_decode(file_get_contents("php://input")) ); echo "<br />";

		$this->application = array_shift($params);
		$this->action = array_shift($params);
		if ( isset($_SERVER['REQUEST_METHOD']) ) {
			$this->request_method = $_SERVER['REQUEST_METHOD'];
		} else {
			$this->request_method = "GET";
		}

		// echo "application:" . $this->application . "<br />";
		// echo "action:" . $this->action . "<br />";
		// echo "request_method:" . $this->request_method . "<br />";

		// Does the Session have permission to use this system
		$return = api_permission::testSystemPermission();
		if ( $return['status'] > 200 ) {
			return $return;
		}

		// Does the Session have permission to use the application
		$return = api_permission::testApplictationPermission($this->application);
		if ( $return['status'] > 200 ) {
			return $return;
		}

		// Is the API call calling a valid action
		if ( !method_exists($this,$this->action) ) {
			return $this->return_handler->results(400,$this->action . " is not a valid Action.",new stdClass() );
		}
		
		// get the POST/PUT data
		$return = $this->get_data();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$data = $return['response'];
		
		// Process the Action
		return $this->{$this->action}($params,$data);
	}

	public function schema($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				return $this->perform('action_schema->get');
			} else if ( count($params) == 1 ) {
				if ( $params[0] == 'lookup' ) {
					return $this->perform('action_schema->lookup');
				} else if ( $params[0] == 'workout' ) {
					return $this->perform('action_schema->workout');
				} else if ( $params[0] == 'workout_log' ) {
					return $this->perform('action_schema->workout_log');
				} else if ( $params[0] == 'refresh' ) {
					return $this->perform('cli_schema->refresh');
				} else if ( $params[0] == 'file' ) {
					return $this->perform('action_schema->file');
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function login($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------------------
				// get the client session data
				// ------------------------------------------------------------------------------
				return $this->perform('action_login->getSessionUserData');
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				if ( count((array) $data) == 1 ) {
					if ( property_exists($data,'token') && !is_null($data->token) && !empty($data->token) ) {
						// ------------------------------------------------------------------------------
						// Validate email/token
						// ------------------------------------------------------------------------------
						return $this->perform('action_user->validateToken',$data->token);
					} else if (property_exists($data, 'fb_id') && !is_null($data->fb_id) && !empty($data->fb_id)) {
						// ------------------------------------------------------------------------------
						// Login using facebook id
						// ------------------------------------------------------------------------------
						return $this->perform('action_login->loginUser', $data);
					}  else if (property_exists($data, 'google_id') && !is_null($data->google_id) && !empty($data->google_id)) {	
						// ------------------------------------------------------------------------------
						// Login using google id
						// ------------------------------------------------------------------------------
						return $this->perform('action_login->loginUser', $data);
					}
				} if ( count((array) $data) == 2 ) {
					if ( property_exists($data,'email') && !is_null($data->email) && !empty($data->email) &&
					     property_exists($data,'password') && !is_null($data->password) && !empty($data->password) ) {
						// ------------------------------------------------------------------------------
						// login the user
						// ------------------------------------------------------------------------------
						return $this->perform('action_login->loginUser',$data);
					} else if ( property_exists($data,'token') && !is_null($data->token) && !empty($data->token) &&
					     property_exists($data,'fb_id') && !is_null($data->fb_id) && !empty($data->fb_id) ) {
					    // ------------------------------------------------------------------------------
						// Set the user's facebook id and login the user
						// ------------------------------------------------------------------------------
					     return $this->perform('action_user->updateFacebookLoginForToken', $data);	
					}  else if ( property_exists($data,'token') && !is_null($data->token) && !empty($data->token) &&
					     property_exists($data,'google_id') && !is_null($data->google_id) && !empty($data->google_id) ) {
					    // ------------------------------------------------------------------------------
						// Set the user's google id and login the user
						// ------------------------------------------------------------------------------
					     return $this->perform('action_user->updateGoogleLoginForToken', $data);	
					} else if ( property_exists($data,'token') && !is_null($data->token) && !empty($data->token) &&
					     property_exists($data,'password') && !is_null($data->password) && !empty($data->password) ) {
						// ------------------------------------------------------------------------------
						// Set the user's password and login the user
						// ------------------------------------------------------------------------------
						return $this->perform('action_user->updatePasswordLoginForToken',$data);
					}
				}
			} if ( count($params) == 1 ) {
				if ( $params[0] == "reset" ) {
					if ( count((array) $data) == 1 ) {
						if ( property_exists($data,'email') && !is_null($data->email) && !empty($data->email) ) {
							// ------------------------------------------------------------------------------
							// Set the user's token and token expire date and send an email to the user
							// ------------------------------------------------------------------------------
							return $this->perform('action_user->setToken',$data->email);
						}
					}
				}
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------------------
				// logout the current session
				// ------------------------------------------------------------------------------
				return $this->perform('action_login->logout');
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function metadata($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				return $this->perform('action_metadata->getAll');
			} else if ( count($params) == 1 && $params[0] == 'timezone' ) {
				return $this->perform('action_metadata->getTimezone');
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function import_users($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 5 ) {
				if ( $params[0] == 'preview' ) {
					return $this->perform('csv_client_user->preview',$params[1],$params[2],$params[3],$params[4]);
				}
			} else if ( count($params) == 2 ) {
				if ( $params[0] == 'import' ) {
					return $this->perform('csv_client_user->post',$params[1]);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function email($params,$data) {

		if ($this->request_method = 'POST' ) {
			if ( count($params) == 1 ) {
				if ( $params[0] == 'error' ) {
					if ( isset($data) && is_object($data) && property_exists($data,'method') && property_exists($data,'url') && property_exists($data,'input_data') && property_exists($data,'message') ) {
						// ------------------------------------------------------------------
						// Send a System Error Email to Support
						// ------------------------------------------------------------------
						// echo "call email_error->sendErrorEmail<br />";
						// echo "method:" . $data->method . "<br />";
						// echo "url:" . $data->url . "<br />";
						// echo "data:"; print_r($data->input_data);
						// echo "message:" . $data->message . "<br />";
						return $this->perform('email_error->sendEmail',$data->method,$data->url,$data->input_data,$data->message);
					}
				} else if ( $params[0] == 'OTO' ) {
					// ------------------------------------------------------------------
					// Send a Special One Time Only Email
					// ------------------------------------------------------------------
					return $this->perform('email_one_time->sendOneTimeEmail');
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function file($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 4 ) {
				if ( isset($params[0]) && !is_null($params[0]) && !empty($params[0]) &&
				     isset($params[1]) && !is_null($params[1]) && !empty($params[1]) &&
				     isset($params[2]) && !is_null($params[2]) && !empty($params[2]) &&
				     isset($params[3]) && !is_null($params[3]) && !empty($params[3]) ) {
					if ( $params[0] == "view_image" ) {
						// ------------------------------------------------------------------
						// View an Image
						// ------------------------------------------------------------------
						$image = array();
						$image[] = $params[1]; // image type
						$image[] = $params[2]; // image filename
						$image[] = $params[3]; // image size
						// echo "perform file_image->view<br />";
						// print_r($image); echo "<br />";
						return $this->perform('file_image->view',$image);
					}
				}
			}
		}

		if ($this->request_method = 'POST' ) {
			if ( count($params) == 1 ) {
				if ( $params[0] == 'upload' ) {
					// ------------------------------------------------------------------
					// Upload an image to Temp Media
					// ------------------------------------------------------------------
					return $this->perform('file_upload->upload_temp_image');
				}
			}
		}

		if ($this->request_method = 'PUT' ) {
			if ( count($params) == 4 ) {
				if ( isset($params[0]) && !is_null($params[0]) && !empty($params[0]) &&
				     isset($params[1]) && !is_null($params[1]) && !empty($params[1]) &&
				     isset($params[2]) && !is_null($params[2]) && !empty($params[2]) &&
				     isset($params[3]) && !is_null($params[3]) && !empty($params[3]) ) {
					if ( $params[0] == "rotate" ) {
						// ------------------------------------------------------------------
						// Rotate an Image
						// ------------------------------------------------------------------
						$rotate = array();
						$rotate[] = $params[1]; // image type
						$rotate[] = $params[2]; // image filename
						$rotate[] = $params[3]; // rotate degrees
						return $this->perform('file_upload->rotate',$rotate);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function calendar_media($params,$data) {

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 3 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) &&
				     !is_null($params[2]) && !empty($params[2]) ) {
					if ( $params[0] == 'upload' && is_numeric($params[1]) && is_numeric($params[2]) ) {
						// ------------------------------------------------------------------------------
						// Upload an Image directly to Calendar Media (skipping Temp Media)
						// -----------------------------------------------------------------------------
						return $this->perform('table_workoutdb_calendar_media->uploadCreate',$calendar_id=$params[1],$UTC_date_time=$params[2]);
					}
				}
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					// ------------------------------------------------------------------------------
					// Delete an image from Calendar Media
					// ------------------------------------------------------------------------------
					return $this->perform('table_workoutdb_calendar_media->delete',$calendar_media_id=$params[0]);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function location_media($params,$data) {

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 3 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'upload' && is_numeric($params[1]) ) {
						// ------------------------------------------------------------------------------
						// Upload an Image directly to Location Media (skipping Temp Media)
						// -----------------------------------------------------------------------------
						return $this->perform('table_workoutdb_location_media->uploadCreate',$location_id=$params[1]);
					}
				}
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					// ------------------------------------------------------------------------------
					// Delete an image from Location Media
					// ------------------------------------------------------------------------------
					return $this->perform('table_workoutdb_location_media->delete',$location_media_id=$params[0]);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function calendar($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				if ( (isset($_GET['client_id']) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id'])) ) {
					// ------------------------------------------------------------------
					// getForClientLocationOrClassroom - get a list of all Calendars for a Client
					// ------------------------------------------------------------------
					return $this->perform('action_calendar->getForClientLocationOrClassroom',$_GET['client_id'],$location_id=null,$classroom_id=null);
				}
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						// use the calendar_entry_api model to do this
						// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						return $this->perform('action_calendar_entry->getForCalendar',$calendar_id=$params[0]);
					} else if ( $params[0] == 'has_activity' ) {
						if ( array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     array_key_exists('date',$_GET) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// -----------------------------------------------------------------------------
							// getDaysWithActivityForClientDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_has_activity->getDaysWithActivityForClientDate',$_GET['client_id'],$yymmdd=$_GET['date']);
						}
					} else if ( $params[0] == "entry_template" ) {
						if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) ){
							// -----------------------------------------------------------------------------
							// getCalendarEntryTemplates - get the calendar_entry_templates for a client
							// -----------------------------------------------------------------------------
							return $this->perform('action_calendar_entry_template->getForClient',$_GET['client_id']);
						}
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == "class" && $params[1] == "list" ) {
						if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     isset($_GET['date']) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ){
							// -----------------------------------------------------------------------------
							// getClassesForClientDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_calendar->getClassesForClientDate',$_GET['client_id'],$yymmdd=$_GET['date']);
						} else if ( isset($_GET['user_id']) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
						     isset($_GET['date']) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ){
							// -----------------------------------------------------------------------------
							// getLogResultClassesForUserDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_calendar->getLogResultClassesForUserDate',$_GET['user_id'],$yymmdd=$_GET['date']);
						}
					} else if ( $params[0] == "entry" && is_numeric($params[1]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_calendar_entry->getForId',$calendar_entry_id=$params[1]);
					} else if ( $params[0] == "class" && $params[1] == "detail" ) {
						if ( isset($_GET['key']) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( is_numeric($key[0]) && is_numeric($key[1]) ) {
									// -----------------------------------------------------------------------------
									// getClassesForClientDate (date is UTC date/time)
									// -----------------------------------------------------------------------------
									return $this->perform('action_workout->getForEntryStart',$entry_id=$key[0],$UTC=$key[1]);
								} else if ( $key[0][0] == "W" && is_numeric($key[1]) ) {
									$key[0] = str_replace("W","",$key[0]);
									if ( is_numeric($key[0]) ) {
									// -----------------------------------------------------------------------------
									// getForTemplateDate (date is yyyymmdd)
									// -----------------------------------------------------------------------------
										return $this->perform('action_workout->getForTemplateDate',$calendar_entry_template_id=$key[0],$yyyymmdd=$key[1]);
									}
								}
							}
						}
					} else if ( $params[0] == "entry" && $params[1] == "occurance" ) {
						if ( isset($_GET['key']) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 &&
							     !empty($key[0]) && is_numeric($key[0]) && 
								 !empty($key[1]) && is_numeric($key[1]) ) {
								// ------------------------------------------------------------------
								// getCalendarEntryEvent
								// ------------------------------------------------------------------
								return $this->perform('action_calendar_event->getCalendarEntryEventForEntryStart',$calendar_entry_id=$key[0],$UTC_date_time=$key[1]);
							}
						}
					} else if ( $params[0] == "event" && $params[1] == "participant" ) {
						if ( isset($_GET['key']) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( is_numeric($key[0]) && is_numeric($key[1]) ) {
									// ------------------------------------------------------------------
									// getCalendarEventParticipants
									// ------------------------------------------------------------------
									return $this->perform('action_checkin->getForEntryStartFormatWeb',$calendar_entry_id=$key[0],$UTC_date_time=$key[1]);
								}
							}
						}
					}
				}
			}
		}
		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'entry' ) {
						// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						// Create or Update a calendar entry
						// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						return $this->perform('action_calendar_entry->create',$data);
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'class' && $params[1] == "add_note" ) {
						if ( isset($data->key) && !is_null($data->key) && !empty($data->key) &&
						     isset($data->note) ) {
							$key = explode('_',$data->key);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( is_numeric($key[0]) && is_numeric($key[1]) ) {
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// Create or update a calendar_event
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									return $this->perform('action_calendar_event>create',$data);
								} else if ( $key[0][0] == "W" && is_numeric($key[1]) ) {
									$key[0] = str_replace("W","",$key[0]);
									if ( is_numeric($key[0]) ) {
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										// Change the note field on the workout-of-the-day (create the WOD if needed)
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										return $this->perform('action_wod->changeWODNote',$data);
									}
								}
							}
						}
					} else if ( $params[0] == 'class' && $params[1] == "schedule_workout" ) {
						if ( isset($data->key) && !is_null($data->key) && !empty($data->key) &&
						     isset($data->workout) && !is_null($data->workout) && !empty($data->workout) && is_numeric($data->workout) ) {
							$key = explode('_',$data->key);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( is_numeric($key[0]) && is_numeric($key[1]) ) {
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// Schedule a single workout to a non-wod event
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									return $this->perform('action_calendar_event->scheduleEventWorkout',$data);
								} else if ( $key[0][0] == "W" && is_numeric($key[1]) ) {
									$key[0] = str_replace("W","",$key[0]);
									if ( is_numeric($key[0]) ) {
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										// Schedule a single workout to a wod
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										return $this->perform('action_wod->scheduleWODWorkout',$data);
									}
								}
							}
						}
					} else if ( $params[0] == 'class' && $params[1] == "remove_workout" ) {
						if ( isset($data->key) && !is_null($data->key) && !empty($data->key) &&
						     isset($data->workout) && !is_null($data->workout) && !empty($data->workout) && is_numeric($data->workout) ) {
							$key = explode('_',$data->key);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( is_numeric($key[0]) && is_numeric($key[1]) ) {
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									// Remove a single workout from a non-wod event
									// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
									return $this->perform('action_calendar_event->removeEventWorkout',$data);
								} else if ( $key[0][0] == "W" && is_numeric($key[1]) ) {
									$key[0] = str_replace("W","",$key[0]);
									if ( is_numeric($key[0]) ) {
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										// Remove a single workout from a wod
										// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
										return $this->perform('action_wod->removeWODWorkout',$data);
									}
								}
							}
						}								
					}
				}
			}
		}
		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'entry' ) {
						if ( isset($data->id) && !is_null($data->id) && !empty($data->id) && is_numeric($data->id) ) {
							// -----------------------------------------------------------------------------------------------
							// update a calendar_entry entry
							// -----------------------------------------------------------------------------------------------
							return $this->perform('action_calendar_entry->update',$data);
						}
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'entry' && $params[1] == 'occurance' ) {
						if ( property_exists($data,'key') ) {
							$key = explode('_',$data->key);
							if ( count($key) == 2 &&
							     !empty($key[0]) && is_numeric($key[0]) && 
								 !empty($key[1]) && is_numeric($key[1]) ) {
								// -----------------------------------------------------------------------------------------------
								// create/update a calendar_event entry
								// -----------------------------------------------------------------------------------------------
								return $this->perform('action_calendar_event->create',$data);
							}
						}
					}
				}
			}
		}
		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == "entry" && is_numeric($params[1]) ) {
						if ( isset($_GET['date']) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// -----------------------------------------------------------------------------------------------
							// remove a date from a calendar entry
							// -----------------------------------------------------------------------------------------------
							return $this->perform('action_calendar_entry->AddRemovedDate',$params[1],$_GET['date']);
						} else {
							// -----------------------------------------------------------------------------------------------
							// DELETE a calendar_entry entry
							// -----------------------------------------------------------------------------------------------
							return $this->perform('action_calendar_entry->delete',$params[1]);
						}
					}
				}
			}
		}			

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function checkin_streak($params,$data) {
		
		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				if ( isset($_GET['user_id']) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
				     isset($_GET['goal']) && !is_null($_GET['goal']) && !empty($_GET['goal']) && is_numeric($_GET['goal']) &&
				     isset($_GET['weeks']) && !is_null($_GET['weeks']) && !empty($_GET['weeks']) && is_numeric($_GET['weeks']) ) {
					// ------------------------------------------------------------------
					// getForUserGoal
					// ------------------------------------------------------------------
					return $this->perform('action_streak->getForUserGoal',$_GET['user_id'],$_GET['goal'],$_GET['weeks']);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function client($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					// ------------------------------------------------------------------
					// getForId
					// ------------------------------------------------------------------
					return $this->perform('table_workoutdb_client->getForId',$params[0]);
				} else if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ($params[0] == 'search' ) {
						return $this->perform('action_client->getSearchList',$cast_output=TRUE);
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ){
					if ( $params[1] == "extendToken" ) {
						// ------------------------------------------------------------------
						// get the facebook extended token for the client
						// ------------------------------------------------------------------
						return $this->perform('action_facebook->extendUserToken',$params[0]);
					} else if ( is_numeric($params[0]) && $params[1] == "location" ) {
						// ------------------------------------------------------------------
						// Get the locations for a client
						// ------------------------------------------------------------------
						return $this->perform('action_location->getForClient',$params[0]);
					} 
				}
			}
		}

		if ( $this->request_method == 'PUT') {
			return $this->perform('table_workoutdb_client->update',$data);
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function client_user($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) ) {
					if ( substr($this->application,0,2) == 't_' || substr($this->application,0,2) == 'p_' ) {
						// ------------------------------------------------------------------
						// getForClientFormatMobile
						// ------------------------------------------------------------------
						return $this->perform('action_member->getForClientFormatMobile',$_GET['client_id']);
					} else {
						// ------------------------------------------------------------------
						// getForClientFormatWeb
						// ------------------------------------------------------------------
						return $this->perform('action_member->getForClientFormatWeb',$_GET['client_id']);
					}
				}
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					// ------------------------------------------------------------------
					// getForId
					// ------------------------------------------------------------------
					return $this->perform('action_member->getForId',$params[0]);
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && 
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( is_numeric($params[0]) && $params[1] == "participation" ) {
						// ------------------------------------------------------------------
						// findOneParticipation
						// ------------------------------------------------------------------
						return $this->perform('action_checkin->getForClientUser',$client_user_id=$params[0]);
					}
				}
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				return $this->perform('action_member->create',$data);
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				return $this->perform('action_member->update',$data);
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					return $this->perform('action_member->deactivate',$client_user_id=$params[0]);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function event($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				if ( isset($_GET['calendar_id']) && !is_null($_GET['calendar_id']) && !empty($_GET['calendar_id']) && is_numeric($_GET['calendar_id']) &&
				     isset($_GET['start']) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) ) {
					// ------------------------------------------------------------------
					// getCalendarEventsForCalendarStartByDay
					// ------------------------------------------------------------------
					if ( $this->application == 'p_staff' ) {
						$count_deleted = true;
					} else {
						$count_deleted = false;
					}
					return $this->perform('action_calendar_event->getForCalendarStartByDay',$_GET['calendar_id'], $UTC_date_time=$_GET['start'],$count_deleted);
				}
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == "participant" ) {
						if ( isset($_GET['key']) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 &&
							     !empty($key[0]) && is_numeric($key[0]) &&
								 !empty($key[1]) && is_numeric($key[1]) ) {
								// ------------------------------------------------------------------
								// getParticipantsForEntryStart
								// ------------------------------------------------------------------
								if ( $this->application == 'p_staff' ) {
									$show_deleted = true;
								} else {
									$show_deleted = false;
								}
								return $this->perform('action_checkin->getForEntryStartFormatMobile',$calendar_entry_id=$key[0],$UTC_date_time=$key[1],$show_deleted);
							}
						}
					} else if ( $params[0] == 'has_activity' ) {
						if ( array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     array_key_exists('date',$_GET) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// -----------------------------------------------------------------------------
							// getDaysWithActivityForClientDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_has_activity->getDaysWithActivityForClientDate',$_GET['client_id'],$ccyymmdd=$_GET['date']);
						}
					} else if ( $params[0] == 'workout' ) {
						if ( array_key_exists('client_user_id',$_GET) && !is_null($_GET['client_user_id']) && !empty($_GET['client_user_id']) && is_numeric($_GET['client_user_id']) &&
							 array_key_exists('key',$_GET) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 &&
							     !empty($key[0]) && is_numeric($key[0]) &&
								 !empty($key[1]) && is_numeric($key[1]) ) {
								// -----------------------------------------------------------------------------
								// getForEntryStart ( where start is utc date/time )
								// -----------------------------------------------------------------------------
								return $this->perform('action_workout->getForEntryStartClientUser',$calendar_entry_id=$key[0],$UTC_date_time=$key[1],$_GET['client_user_id']);
							}
						}
					} else if ( $params[0] == 'workoutlog' ) {
						if ( array_key_exists('client_user_id',$_GET) && !is_null($_GET['client_user_id']) && !empty($_GET['client_user_id']) && is_numeric($_GET['client_user_id']) &&
							        array_key_exists('workout_id',$_GET) && !is_null($_GET['workout_id']) && !empty($_GET['workout_id']) && is_numeric($_GET['workout_id']) ) {
							// -----------------------------------------------------------------------------
							// getForClientUserWorkout
							// -----------------------------------------------------------------------------
							return $this->perform('action_workout_log->getForClientUserWorkout',$_GET['client_user_id'],$yymmdd=$_GET['workout_id']);
						}
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
				   if ( $params[0] == "participant" && $params[1] == "count" ) {
						if ( isset($_GET['client_id']) &&!is_null($_GET['client_id']) &&  !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     isset($_GET['date']) &&!is_null($_GET['date']) &&  !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// ------------------------------------------------------------------
							// GetParticipantCountByLocationTime
							// ------------------------------------------------------------------
							return $this->perform('action_checkin_charts->getChartNumbersForClientDate',$_GET['client_id'],$ccyymmdd=$_GET['date']);
						}
					} else if ( $params[0] == "participant" && $params[1] == "list" ) {
						if ( isset($_GET['key']) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
							$key = explode('_',$_GET['key']);
							if ( count($key) == 2 && !empty($key[0]) && !empty($key[1]) ) {
								if ( $key[0][0] == "L" && is_numeric($key[1]) ) {
									$key[0] = str_replace("L","",$key[0]);
									if ( is_numeric($key[0]) ) {
										// ------------------------------------------------------------------
										// getForLocationTime
										// ------------------------------------------------------------------
										return $this->perform('action_checkin->getForLocationStart',$location_id=$key[0],$UTC_date_time=$key[1]);
									}
								}
							}
						}
					}
				}
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 2 ) {
				if ( $params[0] == 'participant' && $params[1] == 'delete' ) {
					if ( isset($data->key) && !is_null($data->key) && !empty($data->key) &&
					     isset($data->participant) && is_array($data->participant) ) {
						$key = explode('_',$data->key);
						if ( count($key) == 2 &&
					         !empty($key[0]) && is_numeric($key[0]) &&
						     !empty($key[1]) && is_numeric($key[1]) ) {
							// -----------------------------------------------------------------------------------------------------------------------------
							// Delete a list of checked in members from an event
							//------------------------------------------------------------------------------------------------------------------------------
							return $this->perform('action_checkin->deleteCheckins',$calendar_entry_id=$key[0],$UTC_date_time=$key[1],$client_user_list=$data->participant);
						}
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function equipment($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// getAll
				// ------------------------------------------------------------------
				return $this->perform('action_equipment->getAll');
			} if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_equipment->getForId',$params[0]);
					} else if ( $params[0] == 'search' ) {
						// ------------------------------------------------------------------
						// getSearchList
						// ------------------------------------------------------------------
						return $this->perform('action_equipment->getSearchList',$cast_output=TRUE);
					}
				}
			}
		}
		
		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// create
				// ------------------------------------------------------------------
				return $this->perform('action_equipment->create',$data);
			}
		}
		
		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// create
				// ------------------------------------------------------------------
				return $this->perform('action_equipment->update',$data);
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_equipment->delete',$params[0]);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function exercise($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// getAll
				// ------------------------------------------------------------------
				return $this->perform('action_exercise->getAll');
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_exercise->getForId',$params[0]);
					} else if ( $params[0] == 'search' ) {
						// ------------------------------------------------------------------
						// getSearchList
						// ------------------------------------------------------------------
						return $this->perform('action_exercise->getSearchList',$cast_output=TRUE);
					}
				}
			}
		}
		
		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// create
				// ------------------------------------------------------------------
				return $this->perform('action_exercise->create',$data);
			}
		}
		
		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// create
				// ------------------------------------------------------------------
				return $this->perform('action_exercise->update',$data);
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_exercise->delete',$params[0]);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function participant($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == "retention" ) {
						if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) &&  !empty($_GET['client_id']) && is_numeric($_GET['client_id']) ) {
							// ------------------------------------------------------------------
							// getForClientByParticipation
							// ------------------------------------------------------------------
							return $this->perform('action_retention->getForClient',$_GET['client_id']);
						}
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( is_numeric($params[0]) && $params[1] == "emotional" ) {
						// ------------------------------------------------------------------
						// getEmotionalForId
						// ------------------------------------------------------------------
						return $this->perform('action_checkin->getEmotionalForId',$params[0]);
					}
				}
			} else if ( count($params) == 3 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) &&
				     !is_null($params[2]) && !empty($params[2]) ) {
					if ( $params[0] == "retention" &&
					     ($params[1] == 'not_seen_in' || $params[1] == 'just_came_back' || $params[1] == 'came_consistently_for') &&
					     ($params[2] == '1month' || $params[2] == '2weeks' || $params[2] == '1week') ) {
						if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) &&  !empty($_GET['client_id']) && is_numeric($_GET['client_id']) ) {
							// ------------------------------------------------------------------
							// getForParticipationPeriodClient
							// ------------------------------------------------------------------
							return $this->perform('action_retention->getForClient',$_GET['client_id'],$format='long',$params[1],$params[2]);
						}
					}
				}
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				if ( property_exists($data,'key') && !is_null($data->key) && !empty($data->key) && is_string($data->key) &&
				     property_exists($data,'client_user_id') && !is_null($data->client_user_id) && !empty($data->client_user_id) && is_numeric($data->client_user_id) ) {
					$key = explode('_',$data->key);
					if ( count($key) == 2 &&
					     !empty($key[0]) && is_numeric($key[0]) &&
						 !empty($key[1]) && is_numeric($key[1]) ) {
						// -----------------------------------------------------------------------------------------------------------------------------
						// Create the calendar_event entry (if needed)
						// Create the calendar_event_participation entry (if needed)
						//------------------------------------------------------------------------------------------------------------------------------
						return $this->perform('action_checkin->checkinExistingMember',$calendar_entry_id=$key[0],$UTC_date_time=$key[1],$data->client_user_id);
					}
				}
			} else if ( count($params) == 1 && $params[0] == "client_user" ) {
				if ( property_exists($data,'key') && !is_null($data->key) && !empty($data->key) && is_string($data->key) &&
				     property_exists($data,'role_id') && !is_null($data->role_id) && !empty($data->role_id) && is_numeric($data->role_id) &&
				     property_exists($data,'first_name') && !is_null($data->first_name) && !empty($data->first_name) && is_string($data->first_name) &&
				     property_exists($data,'last_name') && !is_null($data->last_name) && !empty($data->last_name) && is_string($data->last_name) &&
				     property_exists($data,'email') && !is_null($data->email) && !empty($data->email) && is_string($data->email) ) {
					$key = explode('_',$data->key);
					if ( count($key) == 2 &&
					     !empty($key[0]) && is_numeric($key[0]) &&
						 !empty($key[1]) && is_numeric($key[1]) ) {
						// -----------------------------------------------------------------------------------------------------------------------------
						// Create the calendar_event entry (if needed)
						// Create the user entry (if needed), client_user entry (if needed), and calendar_event_participation entry (if needed)
						//------------------------------------------------------------------------------------------------------------------------------
						return $this->perform('action_checkin->checkinNewMember',$data);
					}
				}
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == "emotional" ) {
						return $this->perform('action_checkin->updateCheckinEmotionalLevel',$data);
					} else if ( $params[0] == "note" ) {
						return $this->perform('action_checkin->updateCheckinNote',$data);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function leaderboard($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'client' ) {
						if ( array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
						     array_key_exists('start',$_GET) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) ) {
							// -----------------------------------------------------------------------------
							// getClientWorkoutsWithLogsForUserDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_leaderboard->getClientWorkoutsWithLogsForUserDate',$_GET['user_id'],$ccyymmdd=$_GET['start']);
						}
					} else if ( $params[0] == 'workout' ) {
						if ( array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     array_key_exists('start',$_GET) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) ) {
							// -----------------------------------------------------------------------------
							// getWorkoutsForClientDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_leaderboard->getWorkoutsForClientDate',$_GET['client_id'],$ccyymmdd=$_GET['start']);
						}
					} else if ( $params[0] == 'location' ) {
						if ( array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     array_key_exists('start',$_GET) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) &&
							 array_key_exists('workout_id',$_GET) && !is_null($_GET['workout_id']) && !empty($_GET['workout_id']) && is_numeric($_GET['workout_id']) ) {
							// -----------------------------------------------------------------------------
							// getLocationScheduleForClientDateWorkout (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_leaderboard->getLocationScheduleForClientDateWorkout',$_GET['client_id'],$ccyymmdd=$_GET['start'],$_GET['workout_id']);
						}
					} else if ( $params[0] == 'ranking' ) {
						if ( array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
						     array_key_exists('start',$_GET) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) &&
						     array_key_exists('workout_id',$_GET) && !is_null($_GET['workout_id']) && !empty($_GET['workout_id']) && is_numeric($_GET['workout_id']) ) {
							// -----------------------------------------------------------------------------
							// getWorkoutsForClientDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_leaderboard->getRankingForClientDateWorkout',$_GET['client_id'],$ccyymmdd=$_GET['start'],$_GET['workout_id']);
						}
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function user($params,$data) {
		// echo "api_api->user method:" . $this->request_method . " params:"; print_r($params); echo " data:"; print_r($data);

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_user->getForId',$params[0]);
					} else if ( $params[0] == 'has_activity' ) {
						if ( array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
						     array_key_exists('date',$_GET) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// -----------------------------------------------------------------------------
							// getDaysWithActivitForUserDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_has_activity->getDaysWithActivityForUserDate',$_GET['user_id'],$yymmdd=$_GET['date']);
						}
					} else if ( $params[0] == 'has_leaderboard_activity' ) {
						if ( array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
						     array_key_exists('date',$_GET) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
							// -----------------------------------------------------------------------------
							// getDaysWithLeaderboardActivityForUserDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_has_activity->getDaysWithLeaderboardActivityForUserDate',$_GET['user_id'],$yymmdd=$_GET['date']);
						}
					} else if ( $params[0] == 'checkin' ) {
						if ( array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) &&
						     array_key_exists('start',$_GET) && !is_null($_GET['start']) && !empty($_GET['start']) && is_numeric($_GET['start']) ) {
							// -----------------------------------------------------------------------------
							// getDaysWithActivitForUserDate (date is yyyymmdd)
							// -----------------------------------------------------------------------------
							return $this->perform('action_checkin->getForUserDate',$_GET['user_id'],$yymmdd=$_GET['start']);
						}
					}
				}
			}
			else if (count($params) == 2) {
				if ( is_numeric($params[0])) {
					// ------------------------------------------------------------------
					// getForSocial
					// ------------------------------------------------------------------
					return $this->perform('action_user->getForSocial',$params[0],$params[1]);
				}
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'workout_log_pending' && is_numeric($params[1]) ) {
						// -----------------------------------------------------------------------------
						// delete a workoutdb workout_log_pending entry
						// -----------------------------------------------------------------------------
						return $this->perform('table_workoutdb_workout_log_pending->delete',$params[1]);
					}
				}
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				// -----------------------------------------------------------------------------
				// Update the user
				// -----------------------------------------------------------------------------
				return $this->perform('action_user->update',$data);
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'password' ) {
						// -----------------------------------------------------------------------------
						// Change the user password
						// -----------------------------------------------------------------------------
						return $this->perform('action_user->updatePassword',$data);
						
					}
				}
			}
		}


		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function workout($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				if ( isset($_GET) && array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) ) {
					// ------------------------------------------------------------------
					// getForClient get a list of workouts (client_id is used to get participation numbers)
					// ------------------------------------------------------------------
					return $this->perform('action_workout->getForClient',$_GET['client_id']);
				} if ( isset($_GET) && array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
					// ------------------------------------------------------------------
					// getForClient get a list of workouts (client_id is used to get participation numbers)
					// ------------------------------------------------------------------
					return $this->perform('action_workout->getForUser',$_GET['user_id']);
/*
				} if ( isset($_GET) && array_key_exists('key',$_GET) && !is_null($_GET['key']) && !empty($_GET['key']) ) {
					$key = explode('_',$_GET['key']);
					if ( count($key) == 2 &&
					     !empty($key[0]) && is_numeric($key[0]) &&
						 !empty($key[1]) && is_numeric($key[1]) ) {
						// ------------------------------------------------------------------
						// getScheduledForEntryStart Get a list of scheduled workouts for a class
						// ------------------------------------------------------------------
						return $this->perform('action_workout->getScheduledForEntryStart',$key[0],$key[1]);
					}
*/
				}
			} if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_workout->getForId',$library_workout_id=$params[0]);
					} else if ( $params[0] == 'search' ) {
						// ------------------------------------------------------------------
						// getSearchList
						// ------------------------------------------------------------------
						return $this->perform('table_workoutdb_workout->getSearchList',$cast_output=TRUE);
					}
				}
			} else if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'detail' && is_numeric($params[1]) ) {
						if ( isset($_GET['client_id']) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) ) {
							// ------------------------------------------------------------------
							// getDetailForIdClient
							// ------------------------------------------------------------------
							return $this->perform('action_workout->getDetailForIdClient',$library_workout_id=$params[1],$_GET['client_id']);
						} else if ( isset($_GET['user_id']) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) ) {
							// ------------------------------------------------------------------
							// findOneDetailForUser
							// ------------------------------------------------------------------
							return $this->perform('action_workout->getDetailForIdUser',$library_workout_id=$params[1],$_GET['user_id']);
						}
					}
				}
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				// -----------------------------------------------------------------------------------------------
				// Create a workout
				// -----------------------------------------------------------------------------------------------
				return $this->perform('action_workout->create',$data);
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'preview' ) {
						// -----------------------------------------------------------------------------------------------
						// Return a workout summary based on the POSTed workout
						// -----------------------------------------------------------------------------------------------
						return $this->perform('action_workout->getPreviewWorkoutSummary',$data);
					}
				}
			}
		}
		
		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				// -----------------------------------------------------------------------------------------------
				// UPDATE a workout
				// -----------------------------------------------------------------------------------------------
				return $this->perform('action_workout->update',$data);
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
					// ------------------------------------------------------------------------------
					// Delete a workout
					// ------------------------------------------------------------------------------
					return $this->perform('action_workout->delete',$library_workout_id=$params[0]);
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function workoutlog($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
/*
				if ( isset($_GET) && array_key_exists('key',$_GET) && !is_null($_GET['key']) && !empty($_GET['key']) &&
				     isset($_GET) && array_key_exists('workout_id',$_GET) && !is_null($_GET['workout_id']) && !empty($_GET['workout_id']) && is_numeric($_GET['workout_id']) ) {
					$key = explode('_',$_GET['key']);
					if ( count($key) == 2 &&
					     !empty($key[0]) && is_numeric($key[0]) &&
						 !empty($key[1]) && is_numeric($key[1]) ) {
						// ------------------------------------------------------------------
						// getForEntryStartWorkout Get a list workoutlogs for a class and workout
						// ------------------------------------------------------------------
						return $this->perform('action_workout_log->getForEntryStartWorkout',$key[0],$key[1],$_GET['workout_id']);
					}
				} else 
*/
				if ( isset($_GET) && array_key_exists('client_id',$_GET) && !is_null($_GET['client_id']) && !empty($_GET['client_id']) && is_numeric($_GET['client_id']) &&
				     isset($_GET) && array_key_exists('date',$_GET) && !is_null($_GET['date']) && !empty($_GET['date']) && is_numeric($_GET['date']) ) {
					// ------------------------------------------------------------------
					// getForClientDate Get a list of workoutlogs for a client on a date
					// ------------------------------------------------------------------
					return $this->perform('action_workout_log->getForClientDate',$_GET['client_id'],$_GET['date']);
				} else if ( isset($_GET) && array_key_exists('user_id',$_GET) && !is_null($_GET['user_id']) && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
					// ------------------------------------------------------------------
					// getForClientDate Get a list of workoutlogs for a client on a date
					// ------------------------------------------------------------------
					return $this->perform('action_workout_log->getForUser',$_GET['user_id']);
				}
			} else if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// getForId
						// ------------------------------------------------------------------
						return $this->perform('action_workout_log->getForId',$params[0]);
					}
				}
			} if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[1]) ) {
					if ( $params[0] == 'detail' && is_numeric($params[1]) ) {
						// ------------------------------------------------------------------
						// getDetailForId
						// ------------------------------------------------------------------
						return $this->perform('action_workout_log->getDetailForId',$params[1]);
					} else if ( $params[0] == 'notification' && $params[1] == "queue" ) {
						// -----------------------------------------------------------------------------
						// Run the user notification daemon
						// -----------------------------------------------------------------------------
						return $this->perform('cli_user_notification->daemon');
					} else if ( $params[0] == 'notification' && is_numeric($params[1]) ) {
						// -----------------------------------------------------------------------------
						// Send a user notification email to a single user
						// -----------------------------------------------------------------------------
						return $this->perform('email_user_notification->sendEmail',$params[1]);
					} else if ( $params[0] == "delete" && $params[1] == 'pending' ) {
						// ---------------------------------------------------------------
						// Get a list of events with pendinding workout logs
						// ---------------------------------------------------------------
						return $this->perform('cli_del_pending_logs->delete',1);
					}
				}
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// create
				// ------------------------------------------------------------------
				return $this->perform('action_workout_log->create',$data);
				//return $this->perform('action_workout_log->create',$data);
			} if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'preview' ) {
						// ------------------------------------------------------------------
						// getPreview
						// ------------------------------------------------------------------
						return $this->perform('action_workout_log->getPreview',$data);
					} else if ( $params[0] == 'autocalculate' ) {
						// ------------------------------------------------------------------
						// autoCalculate
						// ------------------------------------------------------------------
						// get the user's session data
						$user = $this->session->userdata('user');
						// 
						return $this->perform('action_workout_log->autoCalculate',$data,$user->user_id);
					}
				}
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// update
				// ------------------------------------------------------------------
				return $this->perform('action_workout_log->update',$data);
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( is_numeric($params[0]) ) {
						// ------------------------------------------------------------------
						// delete
						// ------------------------------------------------------------------
						return $this->perform('action_workout_log->delete',$params[0]);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function gateway($params,$data) {
		// echo "gateway "; print_r($params); print_r($data);
		
		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( isset($_GET['client_user_id']) && !is_null($_GET['client_user_id']) && !empty($_GET['client_user_id']) && is_numeric($_GET['client_user_id']) ) {
						if ( $params[0] == "orderReport" ) {
							if ( isset($_GET['order_id']) && !is_null($_GET['order_id']) && !empty($_GET['order_id']) && is_numeric($_GET['order_id']) ) {
								// ------------------------------------------------------------------
								// getOrderReport
								// ------------------------------------------------------------------
								return $this->perform('getcube_request->getOrderReport',$_GET['client_user_id'],$_GET['order_id']);
							}
						} else if ( $params[0] == "paymentReport" ) {
							// ------------------------------------------------------------------
							// getPaymentReport
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getPaymentReport',$_GET['client_user_id']);
						} else if ( $params[0] == "user" ) {
							// ------------------------------------------------------------------
							// getUserMasters
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getUserMaster',$_GET['client_user_id']);
						} else if ( $params[0] == "validate" ) {
							// ------------------------------------------------------------------
							// validate the client_user with getcube
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->validateToken',$_GET['client_user_id']);
						} else if ( $params[0] == "tokenDetail" ) {
							// ------------------------------------------------------------------
							// getPayments
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getTokenDetail',$_GET['client_user_id']);
						} else if ( $params[0] == "location" ) {
							// ------------------------------------------------------------------
							// getLocations
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getLocations',$_GET['client_user_id']);
						} else if ( $params[0] == "customer" ) {
							// ------------------------------------------------------------------
							// getCustomers
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getCustomers',$_GET['client_user_id']);
						} else if ( $params[0] == "loginHistory" ) {
							// ------------------------------------------------------------------
							// getUserLoginHistory
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getUserLoginHistory',$_GET['client_user_id']);
						} else if ( $params[0] == "loginToday" ) {
							// ------------------------------------------------------------------
							// getUserLoginsToday
							// ------------------------------------------------------------------
							return $this->perform('getcube_request->getUserLoginsToday',$_GET['client_user_id']);
						}
					}
				}
			}
		}

		if ( $this->request_method == 'POST' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'location' ) {
						// ------------------------------------------------------------------
						// createLocation
						// ------------------------------------------------------------------
						return $this->perform('getcube_request->createLocation',$_GET['client_user_id'],$data);
					}
				}
			}
		}

		if ( $this->request_method == 'PUT' ) {
			if ( count($params) == 1 ) {
				if ( !is_null($params[0]) && !empty($params[0]) ) {
					if ( $params[0] == 'location' ) {
						// ------------------------------------------------------------------
						// createLocation
						// ------------------------------------------------------------------
						return $this->perform('getcube_request->updateLocation',$_GET['client_user_id'],$data);
					}
				}
			}
		}

		if ( $this->request_method == 'DELETE' ) {
			if ( count($params) == 2 ) {
				if ( !is_null($params[0]) && !empty($params[0]) &&
				     !is_null($params[1]) && !empty($params[0]) ) {
					if ( $params[0] == 'location' && is_numeric($params[1]) ) {
						// ------------------------------------------------------------------
						// createLocation
						// ------------------------------------------------------------------
						return $this->perform('getcube_request->deleteLocation',$_GET['client_user_id'],$params[1]);
					}
				}
			}
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

	public function dummy($params,$data) {
		if ( $this->config->item('workoutinbox_test_server') ) {
			if ( $this->request_method == 'GET' ) {
				if ( count($params) == 1 && $params[0] == 'user' ) {
					// -----------------------------------------------------------------------------------------------
					// UPDATE all users
					// -----------------------------------------------------------------------------------------------
					return $this->perform('dummy_user->update');
				}
			}
		} else {
			return $this->return_handler->results(500,"Not valid on Production",new stdClass());
		}

		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}
	
	public function facebook($params,$data) {
		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 3 && $params[0] == "client" && $params[1] == "post" && $params[2] == "workout" ) {
				return $this->perform('action_facebook->postWorkoutToFacebook');
			}
		}
		
		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}
	
	public function ziran($params,$data) {
		if ( $this->request_method == 'GET' ) {
				return $this->perform('action_facebook->postToFacebookAsClient',4);
		}
		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

}