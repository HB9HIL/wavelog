<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->_mobile_redirect();
    }

    private function _mobile_redirect() {
        // Only redirect authenticated users — keeps login page reachable on any device
        if (!$this->session->userdata('user_id')) {
            return;
        }

        $uri = $this->uri->uri_string();

        // Skip: already in mobile area, API endpoints, or user has forced desktop mode
        if (
            strpos($uri, 'mobile') === 0 ||
            strpos($uri, 'api')    === 0 ||
            (isset($_COOKIE['wl_force_desktop']) && $_COOKIE['wl_force_desktop'] === '1')
        ) {
            return;
        }

        // Load Mobile_Detect only when all cheap checks have passed
        $this->load->library('Mobile_detect');
        if ($this->mobile_detect->isMobile() && !$this->mobile_detect->isTablet()) {
            redirect('mobile/' . ltrim($uri, '/'));
        }
    }
}

// Mobile_Controller is not auto-loaded by CI3 from application/core/,
// so we load it here after MY_Controller is fully defined.
require_once APPPATH . 'core/Mobile_Controller.php';
