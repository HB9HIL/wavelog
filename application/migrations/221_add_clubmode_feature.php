<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_clubmode_feature extends CI_Migration {

	public function up() {
		// Add the club_account field to the users table
		if (!$this->db->field_exists('club_account', 'users')) {
			$fields = array(
				'club_account' => array(
					'type' => 'TINYINT',
					'constraint' => 1,
					'null' => TRUE,
					'default' => 0,
				),
			);
			$this->dbforge->add_column('users', $fields);
		}

		// Create new auth table for club accounts
		if (!$this->db->table_exists('club_auth')) {
			$this->dbforge->add_field(array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 6,
					'unsigned' => TRUE,
					'auto_increment' => TRUE,
					'null' => FALSE
				),
				'club_uid' => array(
					'type' => 'INT',
					'constraint' => 6,
					'unsigned' => TRUE,
					'null' => FALSE
				),
				'user_id' => array(
					'type' => 'INT',
					'constraint' => 6,
					'unsigned' => TRUE,
					'null' => FALSE,
				),
				'permission' => array(
					'type' => 'INT',
					'constraint' => 6,
					'unsigned' => TRUE,
					'null' => FALSE,
				)
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('club_auth');

			// Add foreign keys
			$this->dbtry('ALTER TABLE club_auth ADD CONSTRAINT fk_club_uid FOREIGN KEY (club_uid) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE RESTRICT;');
			$this->dbtry('ALTER TABLE club_auth ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE RESTRICT;');
		}
	}

	public function down() {
		// Drop the club_account column
		if ($this->db->field_exists('club_account', 'users')) {
			$this->dbforge->drop_column('users', 'club_account');
		}

		// Drop the club_auth table
		if ($this->db->table_exists('club_auth')) {
			$this->dbforge->drop_table('club_auth');
		}
	}

	function dbtry($what) {
		try {
			$this->db->query($what);
		} catch (Exception $e) {
			log_message("error", "Something went wrong while altering FKs: " . $e . " // Executing: " . $this->db->last_query());
		}
	}
}
