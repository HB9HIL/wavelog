<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Operator extends CI_Controller {
    
	public function displayOperatorDialog() {
		$this->load->view('operator/index');
	}
}
?>