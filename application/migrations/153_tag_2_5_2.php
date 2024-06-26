<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   Tag Wavelog as 2.5.1
*/

class Migration_tag_2_5_2 extends CI_Migration {

    public function up()
    {
    
        // Tag Wavelog 2.5.1
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.5.2'));
    }

    public function down()
    {
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.5.1'));
    }
}