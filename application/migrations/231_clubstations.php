<?php

class Migration_clubstations  extends CI_Migration
{

	public function up() {
		// Add the clubstation flag to the users table
		$col_check = $this->db->query("SHOW COLUMNS FROM users LIKE 'clubstation';")->num_rows() > 0;
		if (!$col_check) {
			$sql = "ALTER TABLE users ADD COLUMN clubstation TINYINT(1) DEFAULT 0 AFTER user_type;";
			try {
				$this->db->query($sql);
			} catch (Exception $e) {
				log_message('error', 'Mig 230 - Error adding column "clubstation": ' . $e->getMessage());
			}
		} else {
			log_message('info', 'Mig 230 - Column "clubstation" already exists, skipping ALTER TABLE users.');
		}

		// Create a new table for the club permissions
		if (!$this->db->table_exists('club_permissions')) {
			$this->dbforge->add_field(array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 6,
					'unsigned' => TRUE,
					'auto_increment' => TRUE,
					'null' => FALSE
				),
				'club_id' => array(
					'type' => 'INT',
					'constraint' => '6',
					'unsigned' => TRUE,
					'null' => FALSE,
				),
				'user_id' => array(
					'type' => 'INT',
					'constraint' => '6',
					'unsigned' => TRUE,
					'null' => FALSE,
				),
				'p_level' => array(
					'type' => 'INT',
					'constraint' => '6',
					'unsigned' => TRUE,
					'null' => FALSE,
				),
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('club_permissions');

			// Add a foreign key for the user_id
			$sql = "ALTER TABLE club_permissions ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE RESTRICT;";
			try {
				$this->db->query($sql);
			} catch (Exception $e) {
				log_message('error', 'Mig 230 - Error adding foreign key fk_user_id: ' . $e->getMessage());
			}

			// Add unique constraint for club_id and user_id
			$sql = "ALTER TABLE club_permissions ADD CONSTRAINT unique_member UNIQUE (club_id, user_id);";
			try {
				$this->db->query($sql);
			} catch (Exception $e) {
				log_message('error', 'Mig 230 - Error adding unique constraint unique_member: ' . $e->getMessage());
			}

			// Add 'created_by' field to API Keys
			$col_check = $this->db->query("SHOW COLUMNS FROM api LIKE 'created_by'")->num_rows() > 0;
			if (!$col_check) {
				$sql_add_column = "ALTER TABLE api ADD COLUMN created_by INT(10) NOT NULL DEFAULT 0;";
				try {
					$this->db->query($sql_add_column);

					$sql_update = "UPDATE api SET created_by = user_id;";
					$this->db->query($sql_update);

					$sql_drop_default = "ALTER TABLE api ALTER COLUMN created_by DROP DEFAULT;";
					$this->db->query($sql_drop_default);
				} catch (Exception $e) {
					log_message('error', 'Mig 230 - Error adding column "created_by": ' . $e->getMessage());
				}
			} else {
				log_message('info', 'Mig 230 - Column "created_by" already exists, skipping ALTER TABLE api.');
			}

			// Add 'operator' field to CAT Table
			$col_check = $this->db->query("SHOW COLUMNS FROM cat LIKE 'operator'")->num_rows() > 0;
			if (!$col_check) {
				$sql_add_column = "ALTER TABLE cat ADD COLUMN operator INT(10) NOT NULL DEFAULT 0;";
				try {
					$this->db->query($sql_add_column);

					$sql_update = "UPDATE cat SET operator = user_id;";
					$this->db->query($sql_update);

					$sql_drop_default = "ALTER TABLE cat ALTER COLUMN operator DROP DEFAULT;";
					$this->db->query($sql_drop_default);
				} catch (Exception $e) {
					log_message('error', 'Mig 230 - Error adding column "operator": ' . $e->getMessage());
				}
			} else {
				log_message('info', 'Mig 230 - Column "operator" already exists, skipping ALTER TABLE cat.');
			}
		}
	}

	public function down() {
		// we don't want to loose data in case of a down migration, so we don't drop the column here
	}
}