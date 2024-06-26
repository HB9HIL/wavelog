<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   This migration set's english as default language in the options table
*/

class Migration_add_lang_to_options extends CI_Migration {

    public function up()
    {
        $data = array(
            array('option_name' => "language", 'option_value' => "english", 'autoload' => "yes"),
         );

         $this->db->insert_batch('options', $data);
    }

    public function down()
    {
        // No option to down
    }
}