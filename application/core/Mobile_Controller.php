<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mobile_Controller extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        if (!$this->user_model->authorize(2)) {
            redirect('user/login');
        }
    }

    /**
     * Render a mobile view inside the master layout.
     *
     * @param string $view  Path relative to application/views/ (e.g. 'mobile/dashboard')
     * @param array  $data  Variables to pass to the view
     */
    protected function render($view, $data = []) {
        $data['language']     = current_language();
        $data['callsign']     = $this->session->userdata('user_callsign');
        $data['content_view'] = $view;
        $this->load->view('mobile/_layout', $data);
    }
}
