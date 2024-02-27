<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Operator extends CI_Controller {
    
	public function displayOperatorDialog() {
		$this->load->view('operator/index');
	}

	public function saveOperator() {

		
		$operator = ['operator_callsign' => $this->security->xss_clean($this->input->post('operator_callsign'))];

		$this->session->set_userdata($operator);
		
	}
}
?>