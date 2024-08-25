<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*

	Plugins Controller

*/
class Plugins extends CI_Controller {

	public function index() {

		$data = array();

		$data['page_title'] = __("Plugins");
		$data['all_plugins'] = $this->plugins_model->get_plugins();

		$this->load->view('interface_assets/header', $data);
		$this->load->view('plugins/index');
		$this->load->view('interface_assets/footer');
	}
}
