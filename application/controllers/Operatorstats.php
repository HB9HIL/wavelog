<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Operatorstats extends CI_Controller
{

    function __construct() {
        parent::__construct();

        $this->load->model('user_model');
        if (!$this->user_model->authorize(2)) {
            $this->session->set_flashdata('error', __("You're not allowed to do that!"));
            redirect('dashboard');
        }
    }

    public function index() {
	    // Render Page
	    $data['page_title'] = __("Operator Statistics");

	    $this->load->model('operatorstats_model');
		$data['distinct_operators_count'] = $this->operatorstats_model->get_operator_data()['distinct_operators_count'];
		$data['operator_entries'] = $this->operatorstats_model->get_operator_data()['operator_entries'];
		

	    $this->load->view('interface_assets/header', $data);
	    $this->load->view('operatorstats/index');
	    $this->load->view('interface_assets/footer');
    }
}
