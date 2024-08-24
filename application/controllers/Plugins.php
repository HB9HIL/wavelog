<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*

	Plugins Controller

*/
class Plugins extends CI_Controller {

	public function index() {

		$data['page_title'] = __("Plugins");

		$this->load->model('plugins_model');
		
		$data['all_plugins'] = $this->plugins_model->get_all();

		$this->load->view('interface_assets/header', $data);
		$this->load->view('plugins/index');
		$this->load->view('interface_assets/footer');
	}
}
