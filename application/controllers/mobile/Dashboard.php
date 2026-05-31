<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends Mobile_Controller {

    public function index() {
        $this->load->model('logbook_model');

        // logbooks_model is autoloaded (application/config/autoload.php)
        $locations = $this->logbooks_model->list_logbook_relationships(
            $this->session->userdata('active_station_logbook')
        );

        $data['page_title']  = __('Dashboard');
        $data['total_qsos']  = $this->logbook_model->total_qsos($locations);
        $data['recent_qsos'] = $this->logbook_model->get_last_qsos(5, $locations);

        $this->render('mobile/dashboard', $data);
    }
}
