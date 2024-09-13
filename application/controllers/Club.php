<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* 

	This controller handles the club/special callsigns

*/

class Club extends CI_Controller {

    // Index page is not existent here. Redirect to dashboard
    public function index() {
        redirect('dashboard');
    }

}