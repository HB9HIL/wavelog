<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_archived_locations extends CI_Migration {

    public function up()
    {
		$fields = array(
			'archived' => array(
				'type' => 'tinyint',
				'default' => NULL,
				'null' => TRUE,
			),
		);

		// Add the settings field to the contest_session table
		if (!$this->db->field_exists('archived', 'station_profile')) {
			$this->dbforge->add_column('station_profile', $fields);
		}
	}

    public function down()
    {
		// Drop the settings field from the contest_session table
		if ($this->db->field_exists('archived', 'station_profile')) {
			$this->dbforge->drop_column('station_profile', 'archived');
		}

	}
}
