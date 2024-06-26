<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   This migration adds the url for the maptileserver to the options table in wavelog
*/

class Migration_add_tileserver_to_options extends CI_Migration {

    public function up()
    {
        $data = array(
            array('option_name' => "map_tile_server", 'option_value' => "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", 'autoload' => "yes"),
            array('option_name' => "map_tile_server_copyright", 'option_value' => "Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a>", 'autoload' => "yes")
         );

         $this->db->insert_batch('options', $data);
    }

    public function down()
    {
        // No option to down
    }
}