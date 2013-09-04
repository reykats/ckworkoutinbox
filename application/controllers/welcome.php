<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
    	parent::__construct();
		$this->load->helper(array('url'));
	}
	public function index()
	{
		redirect('http://www.workoutinbox.com/app/', 'refresh');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */