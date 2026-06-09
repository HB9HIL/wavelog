<?php
class Contesting_model extends CI_Model {

	/**
	 * Retrieves the contests associated with the current user.
	 *
	 * @return array List of contests with their details.
	 */
	function get_user_contests() {
		$user_id = $this->session->userdata('user_id');

		$binding = [];
		$sql = "SELECT 
					cs.id AS contest_session_id,
					cs.time_start,
					cs.time_end,
					cs.comment,
					sp.station_callsign AS station,
					c.name AS contestname,
					COUNT(cq.id) AS qso_count
				FROM contest_session cs
				JOIN contest c ON c.id = cs.contest_adif_id
				JOIN station_profile sp ON sp.station_id = cs.station_id
				LEFT JOIN contest_qsos cq ON cq.contest_session_id = cs.id
				WHERE cs.user_id = ?
				GROUP BY cs.id
				ORDER BY cs.time_start DESC";
		$binding[] = $user_id;

		$query = $this->db->query($sql, $binding);
		return $query->result_array();
	}

	/**
	 * Check if contest associated with current user
	 *
	 * @param int $contest_session_id The ID of the contest session.
	 * @return bool If user is associated with contest
	 */
	function check_user_contest($contest_session_id) {
		$user_id = $this->session->userdata('user_id');

		$sql = "SELECT 
					COUNT(*) AS cnt
				FROM contest_session
				WHERE id = ?
				AND user_id = ?";

		$query = $this->db->query($sql, [$contest_session_id, $user_id]);
		$row = $query->row();

		return ((int) $row->cnt === 1);
	}

	/**
	 * Retrieves information about a specific contest session.
	 *
	 * @param int $contest_session_id The ID of the contest session.
	 * @return array|null The contest session information or null if not found.
	 */
	function get_session_info($contest_session_id) {
		$user_id = $this->session->userdata('user_id');

		$binding = [];
		$sql = "SELECT cs.id AS contest_session_id,
				cs.time_start AS time_start,
				cs.time_end AS time_end,
				cs.comment AS comment,
				cs.settings AS settings,
				c.name AS contest_name,
				c.id AS contest_id,
				c.adifname AS contest_adifname,
				sp.station_id AS station_id,
				sp.station_callsign AS station_callsign,
				sp.station_gridsquare AS station_gridsquare
			FROM contest_session cs
			JOIN contest c ON c.id = cs.contest_adif_id
			JOIN station_profile sp ON sp.station_id = cs.station_id
			WHERE cs.id = ? AND cs.user_id = ?
			LIMIT 1";
		$binding[] = $contest_session_id;
		$binding[] = $user_id;

		$query = $this->db->query($sql, $binding);
		$row = $query->row_array();
		if ($row && !empty($row['settings'])) {
			$settings = json_decode($row['settings'], true) ?? [];
			$row['copyexchangeto']  = $settings['copyexchangeto']  ?? '';
			$row['exchangefields']  = $settings['exchangefields']  ?? ['exchange'];
			$row['exchangetype']    = $settings['exchangetype']    ?? 'Exchange';
			$row['callbook_lookup'] = $settings['callbook_lookup'] ?? true;
		} else {
			$row['copyexchangeto']  = '';
			$row['exchangefields']  = ['exchange'];
			$row['exchangetype']    = 'Exchange';
			$row['callbook_lookup'] = true;
		}
		unset($row['settings']);
		return $row;
	}

	/**
	 * Creates a new contest session for the current user.
	 *
	 * @param int $contest_adif_id The id of the contest (contest table)
	 * @param string $session_start The start time of the session.
	 * @param string $session_end The end time of the session.
	 * @param int $station_location The station location (station_id).
	 * @param string $session_notes Notes for the session.
	 * @return bool True on success, false on failure. If $return_id is true, returns the inserted session ID instead.
	 */
	function create_contest_session($contest_adif_id, $session_start, $session_end, $station_location, $session_notes, $return_id = false, $exchangetype = 'Serial', $copyexchangeto = '', $exchangefields = ["serial"], $callbook_lookup = true) {
		$user_id = $this->session->userdata('user_id');

		$settings = json_encode(['exchangetype' => $exchangetype, 'copyexchangeto' => $copyexchangeto, 'exchangefields' => $exchangefields, 'callbook_lookup' => $callbook_lookup]);

		$sql = "INSERT INTO contest_session (user_id, contest_adif_id, time_start, time_end, station_id, comment, settings)
				VALUES (?, ?, ?, ?, ?, ?, ?)";

		$bindings = [
			$user_id,
			$contest_adif_id, // TODO: Modify database to use contest_id instead of contest_adif_id
			$session_start,
			$session_end,
			$station_location,
			$session_notes,
			$settings
		];

		if ($return_id) {
			$this->db->query($sql, $bindings);
			return $this->db->insert_id();
		} else {
			return $this->db->query($sql, $bindings) ? true : false;
		}
	}

	/**
	 * Updates an existing contest session for the current user.
	 * 
	 * @param int $contest_session_id The ID of the contest session to update.
	 * @param int $contest_id The id of the contest (contest table)
	 * @param string $time_start The start time of the session.
	 * @param string $time_end The end time of the session.
	 * @param int $station_id The station location (station_id).
	 * @param string $notes Notes for the session.
	 * @return bool True on success, false on failure.
	 */
	function update_contest_session($contest_session_id, $contest_id, $time_start, $time_end, $station_id, $notes, $exchangetype = 'Serial', $copyexchangeto = '', $exchangefields = ["serial"], $callbook_lookup = true) {
		if (!clubaccess_check(9)) {
			$this->session->set_flashdata('error', __("Officers must edit contests."));
			redirect('contesting');
		}
		$user_id = $this->session->userdata('user_id');

		$settings = json_encode(['exchangetype' => $exchangetype, 'copyexchangeto' => $copyexchangeto, 'exchangefields' => $exchangefields, 'callbook_lookup' => $callbook_lookup]);

		$sql = "UPDATE contest_session
				SET contest_adif_id = ?, time_start = ?, time_end = ?, station_id = ?, comment = ?, settings = ?
				WHERE id = ? AND user_id = ?";

		$bindings = [
			$contest_id,
			$time_start,
			$time_end,
			$station_id,
			$notes,
			$settings,
			$contest_session_id,
			$user_id
		];

		$this->db->query($sql, $bindings);
		return true;
	}

	/**
	 * Deletes a contest session and its associated QSOs for the current user.
	 *
	 * @param int $contest_session_id The ID of the contest session to delete.
	 * @return bool True on success, false on failure.
	 */
	function delete_contest_session($contest_session_id, $delete_qsos = false) {
		if (!clubaccess_check(9)) {
			$this->session->set_flashdata('error', __("Only clubstation officers can delete."));
			redirect('contesting');
		}
		$user_id = $this->session->userdata('user_id');

		if ($delete_qsos) {
			$this->load->is_loaded('logbook_model') ?: $this->load->model('logbook_model');
			$query = $this->db->query("SELECT qso_id FROM contest_qsos WHERE contest_session_id = ?", [$contest_session_id]);
			foreach ($query->result() as $row) {
				$this->logbook_model->delete($row->qso_id);
			}
			// contest_qsos rows are cascade-deleted via FK when logbook rows are removed
		} else {
			$this->db->query("DELETE FROM contest_qsos WHERE contest_session_id = ?", [$contest_session_id]);
		}

		$this->db->query("DELETE FROM contest_session WHERE id = ? AND user_id = ?", [$contest_session_id, $user_id]);
		return true;
	}

	/**
	 * Delete a QSO from a contest. Does not delete QSO from main logbook.
	 *
	 * @param int $qso_id The ID of the QSO.
	 * @param int $contest_session_id The ID of the contest session to delete.
	 * @return bool True on success, false on failure.
	 */
	function unlink_qso($qso_id, $contest_session_id) {

		// Delete associated QSOs (this does not delete the QSOs themselves from the main logbook)
		// Could just use qso_id, but keep contest_session_id to ensure unlink_qso caller knows which contest is being modified
		$sql_delete_qsos = "DELETE FROM contest_qsos WHERE contest_session_id = ? AND qso_id = ?";

		$bindings_qsos = [$contest_session_id, $qso_id];
		$this->db->query($sql_delete_qsos, $bindings_qsos);
		return true;
	}

	/**
	 * Get the contest that a QSO is linked to
	 *
	 * @param int $qso_id The ID of the QSO.
	 * @return int The ID of the contest, otherwise zero
	 */
	function get_linked_contest($qso_id) {

		$sql_get_qsos = "SELECT contest_session_id FROM contest_qsos WHERE qso_id = ?";

		$bindings_qsos = [$qso_id];
		$query = $this->db->query($sql_get_qsos, $bindings_qsos);

        if ($query->num_rows() > 0) {
            return $query->row()->contest_session_id;
        } else {
            return 0;
        }
	}

	/**
	 * Retrieves all QSOs associated with a specific contest session.
	 *
	 * @param int $contest_session_id The ID of the contest session.
	 * @param string $band A valid band
	 * @return array List of QSOs in the session.
	 */
	function get_session_qsos($contest_session_id, $band = "all") {

		$band_constraint = '';
		$bindings = [$contest_session_id];

		if ($band !== 'all') {
			$band_constraint = " AND lb.COL_BAND = ?";
			$bindings[] = $band;
		}
		
		$sql = "SELECT
					lb.COL_PRIMARY_KEY AS qso_id,
					lb.COL_CALL AS callsign,
					lb.COL_TIME_ON AS time_on,
					lb.COL_BAND AS band,
					lb.COL_FREQ AS frequency,
					lb.COL_MODE AS mode,
					lb.COL_SUBMODE AS submode,
					lb.COL_RST_SENT AS rst_sent,
					lb.COL_RST_RCVD AS rst_recv,
					lb.COL_STX AS serial_sent,
					lb.COL_SRX AS serial_recv,
					lb.COL_STX_STRING AS exch_sent,
					lb.COL_SRX_STRING AS exch_recv,
					lb.COL_GRIDSQUARE AS locator,
					lb.COL_OPERATOR AS operator,
					lb.COL_GRIDSQUARE as gridsquare_recv,
					lb.COL_ANT_PATH as antenna_path,
					lb.COL_DXCC as dxcc
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN " . $this->config->item('table_name') . " lb ON lb.COL_PRIMARY_KEY = cq.qso_id
				WHERE cq.contest_session_id = ? {$band_constraint}
				ORDER BY cq.id ASC";

		$query = $this->db->query($sql, $bindings);
		return $query->result_array();
	}

	/**
	 * Retrieves QSOs of a session that changed after the client's watermark (delta sync).
	 * Relies on the main table's last_modified column (ON UPDATE CURRENT_TIMESTAMP),
	 * which is bumped path-independently by any UPDATE that changes a value — so this
	 * catches contest edits as well as edits made through the regular logbook.
	 *
	 * The query is session-bounded: it walks contest_qsos via its session index and
	 * joins the logbook on the primary key, so cost scales with session size, not
	 * total logbook size. There is no index on last_modified, but the filter only
	 * runs over the rows already fetched by primary key, so that is fine.
	 *
	 * The watermark is a (second, qso_id) pair, not just a timestamp, because
	 * last_modified is a TIMESTAMP (1s resolution): a bulk import lands many QSOs in the
	 * same second. A plain >= would re-send all of them on every heartbeat. The pair
	 * compares as: strictly later second, OR same second with a higher qso_id. This still
	 * cannot miss an edit (an edit bumps last_modified into a later second), but within
	 * the boundary second it only returns QSOs the client has not seen yet.
	 *
	 * @param int $contest_session_id
	 * @param int $since_ts Unix timestamp in ms; 0 returns all QSOs (initial load)
	 * @param int $since_id Highest qso_id already seen within the since_ts second
	 * @return array List of QSOs including last_modified_ms
	 */
	function get_session_qsos_since($contest_session_id, $since_ts, $since_id = 0) {
		// Compare on the numeric side (UNIX_TIMESTAMP) rather than FROM_UNIXTIME(?):
		// FROM_UNIXTIME(0) returns NULL under non-UTC server time zones, which would make
		// the initial load (since_ts = 0) match no rows. Floor to whole seconds because
		// last_modified is a TIMESTAMP with 1s resolution.
		$since_sec = (int)($since_ts / 1000);
		$bindings = [$contest_session_id, $since_sec, $since_sec, (int)$since_id];
		$sql = "SELECT
					lb.COL_PRIMARY_KEY AS qso_id,
					lb.COL_CALL AS callsign,
					lb.COL_TIME_ON AS time_on,
					lb.COL_BAND AS band,
					lb.COL_FREQ AS frequency,
					lb.COL_MODE AS mode,
					lb.COL_SUBMODE AS submode,
					lb.COL_RST_SENT AS rst_sent,
					lb.COL_RST_RCVD AS rst_recv,
					lb.COL_STX AS serial_sent,
					lb.COL_SRX AS serial_recv,
					lb.COL_STX_STRING AS exch_sent,
					lb.COL_SRX_STRING AS exch_recv,
					lb.COL_GRIDSQUARE AS locator,
					lb.COL_OPERATOR AS operator,
					UNIX_TIMESTAMP(lb.last_modified) * 1000 AS last_modified_ms,
					de.lat AS dxcc_lat,
					de.long AS dxcc_lon
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN " . $this->config->item('table_name') . " lb ON lb.COL_PRIMARY_KEY = cq.qso_id
				LEFT JOIN dxcc_entities de ON de.adif = lb.COL_DXCC
				WHERE cq.contest_session_id = ?
				  AND (
				        UNIX_TIMESTAMP(lb.last_modified) > ?
				        OR (UNIX_TIMESTAMP(lb.last_modified) = ? AND cq.qso_id > ?)
				      )
				ORDER BY lb.last_modified ASC, cq.qso_id ASC";

		$query = $this->db->query($sql, $bindings);
		return $query->result_array();
	}

	/**
	 * Fetches a single QSO, verifying it belongs to the given contest session.
	 * Returns the row (including operator_callsign) or null if not found.
	 *
	 * @param int $qso_id
	 * @param int $contest_session_id
	 * @return array|null
	 */
	function get_contest_qso($qso_id, $contest_session_id) {
		$table = $this->config->item('table_name');
		$sql = "SELECT lb.COL_PRIMARY_KEY AS qso_id, lb.COL_OPERATOR AS operator
				FROM contest_qsos cq
				JOIN {$table} lb ON lb.COL_PRIMARY_KEY = cq.qso_id
				WHERE cq.qso_id = ? AND cq.contest_session_id = ?
				LIMIT 1";
		$query = $this->db->query($sql, [$qso_id, $contest_session_id]);
		return $query->num_rows() > 0 ? $query->row_array() : null;
	}

	/**
	 * Updates a subset of editable fields on a contest QSO.
	 * MySQL's ON UPDATE CURRENT_TIMESTAMP on last_modified handles the timestamp automatically.
	 *
	 * @param int   $qso_id
	 * @param array $fields  Whitelisted column → value pairs
	 * @return bool
	 */
	function update_contest_qso($qso_id, $fields) {
		$table = $this->config->item('table_name');
		$this->db->where('COL_PRIMARY_KEY', $qso_id)->update($table, $fields);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Links a QSO to a contest session.
	 *
	 * @param int $qso_id The ID of the QSO.
	 * @param int $contest_session_id The ID of the contest session.
	 * @return bool True on success.
	 */
	function link_qso($qso_id, $contest_session_id) {
		$sql = "INSERT INTO contest_qsos (contest_session_id, qso_id)
				VALUES (?, ?)";

		$bindings = [
			$contest_session_id,
			$qso_id
		];

		$this->db->query($sql, $bindings);
		return true;
	}

	/**
	 * Retrieves the total QSO count for a contest session.
	 *
	 * @param int $contest_session_id The ID of the contest session.
	 * @return int The total number of QSOs in the session.
	 */
	function get_session_qso_count($contest_session_id) {
		$sql = "SELECT COUNT(*) AS qso_count FROM contest_qsos WHERE contest_session_id = ?";
		$query = $this->db->query($sql, [$contest_session_id]);
		return (int)$query->row_array()['qso_count'];
	}

	/**
	 * Returns the Export-Format-specific settings sub-array stored in the session's settings JSON.
	 *
	 * @param int $contest_session_id
	 * @param string $exportformat
	 * @return array
	 */
	function get_exportformat_settings($contest_session_id, $exportformat) {
		$user_id = $this->session->userdata('user_id');
		$sql = "SELECT settings FROM contest_session WHERE id = ? AND user_id = ? LIMIT 1";
		$query = $this->db->query($sql, [$contest_session_id, $user_id]);
		$row = $query->row_array();
		if ($row && !empty($row['settings'])) {
			$settings = json_decode($row['settings'], true) ?? [];
			return $settings[$exportformat] ?? [];
		}
		return [];
	}

	/**
	 * Merges exportformat settings into the session's settings JSON without overwriting other fields.
	 *
	 * @param int $contest_session_id
	 * @param string $exportformat
	 * @param array $exportformat_settings
	 * @return bool
	 */
	function save_exportformat_settings($contest_session_id, $exportformat, $exportformat_settings) {
		$user_id = $this->session->userdata('user_id');
		$sql_sel = "SELECT settings FROM contest_session WHERE id = ? AND user_id = ? LIMIT 1";
		$query = $this->db->query($sql_sel, [$contest_session_id, $user_id]);
		$row = $query->row_array();

		$settings = [];
		if ($row && !empty($row['settings'])) {
			$settings = json_decode($row['settings'], true) ?? [];
		}
		$settings[$exportformat] = $exportformat_settings;

		$sql_upd = "UPDATE contest_session SET settings = ? WHERE id = ? AND user_id = ?";
		$this->db->query($sql_upd, [json_encode($settings), $contest_session_id, $user_id]);
		return true;
	}

	/**
	 * Returns all QSOs of a contest session as a CI DB result object suitable for AdifHelper::getAdifLine().
	 * Includes full logbook row + station profile + DXCC country name.
	 *
	 * @param int $contest_session_id
	 * @return CI_DB_result
	 */
	function get_session_qsos_for_adif($contest_session_id) {
		$user_id = $this->session->userdata('user_id');
		$table = $this->config->item('table_name');

		$sql = "SELECT {$table}.*, station_profile.*, dxcc_entities.name AS station_country
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN {$table} ON {$table}.COL_PRIMARY_KEY = cq.qso_id
				JOIN station_profile ON station_profile.station_id = {$table}.station_id
				LEFT JOIN dxcc_entities ON dxcc_entities.adif = station_profile.station_dxcc
				WHERE cq.contest_session_id = ? AND cs.user_id = ?
				ORDER BY {$table}.COL_TIME_ON ASC";

		return $this->db->query($sql, [$contest_session_id, $user_id]);
	}

	/**
	 * Returns a sorted, space-separated string of distinct operators logged in a contest session.
	 * Falls back to COL_STATION_CALLSIGN when COL_OPERATOR is empty.
	 *
	 * @param int $contest_session_id
	 * @return string e.g. "HB9ABC HB9DEF"
	 */
	function get_session_operators($contest_session_id) {
		$user_id = $this->session->userdata('user_id');
		$table   = $this->config->item('table_name');

		$sql = "SELECT DISTINCT UPPER(IFNULL(NULLIF(TRIM({$table}.COL_OPERATOR), ''), {$table}.COL_STATION_CALLSIGN)) AS operator
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN {$table} ON {$table}.COL_PRIMARY_KEY = cq.qso_id
				WHERE cq.contest_session_id = ? AND cs.user_id = ?
				ORDER BY operator ASC";

		$query = $this->db->query($sql, [$contest_session_id, $user_id]);
		$ops   = array_column($query->result_array(), 'operator');
		return implode(' ', $ops);
	}

	/**
	 * Returns a sorted array of bands logged in a contest session.
	 * Returns empty array if no qsos
	 *
	 * @param int $contest_session_id
	 * @return array e.g. ["160m", "80m", "70cm"]
	 */
	function get_session_bands($contest_session_id) {
		$user_id = $this->session->userdata('user_id');
		$table   = $this->config->item('table_name');

		$sql = "SELECT DISTINCT bands.band as band
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN {$table} ON {$table}.COL_PRIMARY_KEY = cq.qso_id
				JOIN bands ON bands.band = {$table}.COL_BAND
				WHERE cq.contest_session_id = ? AND cs.user_id = ? and bands.band != ?
				ORDER BY bands.ssb ASC";

		$query = $this->db->query($sql, [$contest_session_id, $user_id, "SAT"]);
		$bands   = array_column($query->result_array(), 'band');
		return $bands;
	}


	/**
	 * Returns all QSOs of a contest session as a CI DB result object suitable for Cabrilloformat::qso().
	 * Selects only the columns required for Cabrillo output.
	 *
	 * @param int $contest_session_id
	 * @param string $band
	 * @return CI_DB_result
	 */
	function get_session_qsos_for_exportformat($contest_session_id, $band = "all") {
		$user_id = $this->session->userdata('user_id');
		$table = $this->config->item('table_name');

		$band_constraint = '';
		$bindings = [$contest_session_id, $user_id];

		if ($band !== 'all') {
			$band_constraint = " AND {$table}.COL_BAND = ?";
			$bindings[] = $band;
		}

		$sql = "SELECT {$table}.COL_FREQ, {$table}.COL_MODE, {$table}.COL_TIME_ON,
					   {$table}.COL_CALL, {$table}.COL_RST_SENT, {$table}.COL_RST_RCVD,
					   {$table}.COL_STX, {$table}.COL_SRX,
					   {$table}.COL_STX_STRING, {$table}.COL_SRX_STRING,
					   {$table}.COL_GRIDSQUARE,
					   station_profile.station_callsign, station_profile.station_gridsquare
				FROM contest_qsos cq
				JOIN contest_session cs ON cs.id = cq.contest_session_id
				JOIN {$table} ON {$table}.COL_PRIMARY_KEY = cq.qso_id
				JOIN station_profile ON station_profile.station_id = {$table}.station_id
				WHERE cq.contest_session_id = ? AND cs.user_id = ? {$band_constraint}
				ORDER BY {$table}.COL_TIME_ON ASC";

		return $this->db->query($sql, $bindings);
		
	}

	function assignorcreatecontestsessions($qso_ids) {
		
		//get relevant supporting data
		$user_id = $this->session->userdata('user_id');
		$maintable = $this->config->item('table_name');

		//create table name for temporary table
		$data = random_bytes(16);
		$data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    	$data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
		$tmp_table = 'tmp_qso_' . bin2hex($data);

		//prepare QSO ids for temp table
		$insert_data = [];

		foreach ($qso_ids as $qso_id) {
			$qso_id = (int) $qso_id;

			if ($qso_id > 0) {
				$insert_data[] = [
					'qso_id' => $qso_id,
					'contest_session_id' => null
				];
			}
		}

		//abort if empty
		if (empty($insert_data)) {
			return;
		}

		//create temporary table
		$this->db->query("
        CREATE TEMPORARY TABLE `" . $tmp_table . "` (
            qso_id BIGINT(20) UNSIGNED NOT NULL,
			contest_session_id INT(20) UNSIGNED,
            PRIMARY KEY (qso_id)
        ) ENGINE=InnoDB
    	");

		//insert to temporary table
		$this->db->insert_batch($tmp_table, $insert_data);

		//get all contest QSOs with necessary info
		$query = $this->db
			->select('tmp.qso_id, tmp.contest_session_id, main.COL_CONTEST_ID, main.COL_TIME_ON, main.station_id')
			->from($tmp_table . ' AS tmp')
			->join($maintable . ' AS main', 'tmp.qso_id = main.COL_PRIMARY_KEY', 'inner')
			->where('main.COL_CONTEST_ID IS NOT NULL', null, false)
			->where('main.COL_CONTEST_ID !=', '');

		$queryresult = $this->db->get()->result();

		//abort if empty
		if(count($queryresult) < 1) {
			$this->dropTemporaryAssignmentTable($tmp_table);
			return;
		}

		//create cache
		$contest_sessions = [];
		$contest_names_to_other = [];

		//load contest admin model
		$this->load->is_loaded('contest_admin_model') ?: $this->load->model('contest_admin_model');

		//iterate through each qso
		foreach ($queryresult as $row) {
			
			//try to find assignment candidate from cache
			$assignment_candidate = $this->getContestSessionAssignmentCandidateFromCache($contest_sessions, in_array($row->COL_CONTEST_ID, $contest_names_to_other) ? "Other" : $row->COL_CONTEST_ID, $row->COL_TIME_ON, $row->station_id);
			
			//if not found, try the database
			if($assignment_candidate == null)
			{
				$assignment_candidate = $this->getContestSessionAssignmentCandidateFromDb(in_array($row->COL_CONTEST_ID, $contest_names_to_other) ? "Other" : $row->COL_CONTEST_ID, $row->COL_TIME_ON, $row->station_id);
			}

			//if we found nothing, create a new one and load it asap
			if(!$assignment_candidate){
				
				//check with the contest admin model if this contest exists
				$contest_info = $this->contest_admin_model->getActiveContestIDforADIFName($row->COL_CONTEST_ID);

				//if it does not exist, use "Other" and put the provided Contest_ID in the notes
				if($contest_info == null){
					$contest_id = 1;
					$notes = $row->COL_CONTEST_ID;
					$contest_names_to_other[] = $row->COL_CONTEST_ID;

					//recheck cache and db now that we know it is "other"
					//try to find assignment candidate from cache
					$assignment_candidate = $this->getContestSessionAssignmentCandidateFromCache($contest_sessions, in_array($row->COL_CONTEST_ID, $contest_names_to_other) ? "Other" : $row->COL_CONTEST_ID, $row->COL_TIME_ON, $row->station_id);
					
					//if not found, try the database
					if($assignment_candidate == null)
					{
						$assignment_candidate = $this->getContestSessionAssignmentCandidateFromDb(in_array($row->COL_CONTEST_ID, $contest_names_to_other) ? "Other" : $row->COL_CONTEST_ID, $row->COL_TIME_ON, $row->station_id);
					}
				}else{
					$contest_id = $contest_info->id;
					$notes = "";
				}

				//if we still don't have a candidate, create contest session and load it immediately
				if(!$assignment_candidate){
					$this->create_contest_session($contest_id,$row->COL_TIME_ON, $row->COL_TIME_ON, $row->station_id, $notes, false);
					$assignment_candidate = $this->getContestSessionAssignmentCandidateFromDb(in_array($row->COL_CONTEST_ID, $contest_names_to_other) ? "Other" : $row->COL_CONTEST_ID, $row->COL_TIME_ON, $row->station_id);
				}
			}

			//if we have a candidate, modify data and update temporary table
			if($assignment_candidate){
				
				//modify start and end of contest session, for now only in cache
				if($row->COL_TIME_ON <= $assignment_candidate['time_start']){
					$assignment_candidate['time_start'] = $row->COL_TIME_ON;
				}

				if($row->COL_TIME_ON >= $assignment_candidate['time_end']){
					$assignment_candidate['time_end'] = $row->COL_TIME_ON;
				}

				//save new state in cache
				$contest_sessions[$assignment_candidate['contest_session_id']] = $assignment_candidate;

				//update temporary table
				$this->updateTemporaryAssignmentTable($tmp_table, $row->qso_id, $assignment_candidate['contest_session_id']);

			}
			
		}

		//prepare SQL to transfer temporary table contents into final table
			$sql = "
				INSERT INTO contest_qsos (
					contest_session_id,
					qso_id
				)
				SELECT
					contest_session_id,
					qso_id
				FROM {$tmp_table}
				WHERE contest_session_id IS NOT NULL
			";

			//run query
			$this->db->query($sql);

			//save affected row count
			$updated_rows = $this->db->affected_rows();

			//update each contest session with the new start and end times from built cache
			foreach ($contest_sessions as $session_id => $session) {
				$this->db
					->where('id', $session_id)
					->update('contest_session', [
						'time_start' => $session['time_start'],
						'time_end' => $session['time_end'],
					]);
			}

			//finally drop temporary table
			$this->dropTemporaryAssignmentTable($tmp_table);

			//return updated rows
			return $updated_rows;

	}

	function getContestSessionAssignmentCandidateFromCache(array $contest_sessions, $contest_adifname, $qso_datetime, $station_id) {
		
		//get qso timestamp
		$qso_ts = strtotime($qso_datetime);

		//try to find a candidate from cache
		foreach ($contest_sessions as $session) {
			
			//abort if static values are not matching
			if ($session['contest_adifname'] !== $contest_adifname or $session['station_id'] !== $station_id) {
				continue;
			}

			//contest QSOs are usually within 48 hours of each other
			$start_ts = strtotime($session['time_start']) - (48 * 60 * 60);
			$end_ts   = strtotime($session['time_end']) + (48 * 60 * 60);

			//check if qso is inside that space
			if ($qso_ts > $start_ts && $qso_ts < $end_ts) {
				return $session;
			}
		}

		//return null if nothing is found
		return null;
	}

	function getContestSessionAssignmentCandidateFromDb($contest_id, $time, $station_id) {
		
		//get user id from session
		$user_id = $this->session->userdata('user_id');

		//prepare bindings
		$binding = [];

		//declare sql - contest QSOs are usually within 48 hours of each other
		//if there are multiple candidates, prefer the one with the later end time
		$sql = "SELECT cs.id AS contest_session_id,
				cs.time_start AS time_start,
				cs.time_end AS time_end,
				cs.comment AS comment,
				cs.settings AS settings,
				c.name AS contest_name,
				c.id AS contest_id,
				c.adifname AS contest_adifname,
				sp.station_id AS station_id,
				sp.station_callsign AS station_callsign,
				sp.station_gridsquare AS station_gridsquare
			FROM contest_session cs
			JOIN contest c ON c.id = cs.contest_adif_id
			JOIN station_profile sp ON sp.station_id = cs.station_id
			WHERE cs.user_id = ? and c.adifname = ? and cs.station_id = ?
			AND ? >= DATE_SUB(cs.time_start, INTERVAL 48 HOUR)
			AND ? <= DATE_ADD(cs.time_end, INTERVAL 48 HOUR)
			ORDER BY cs.time_end DESC
			LIMIT 1";
		
		//create bindings
		$binding = [$user_id, $contest_id, $station_id, $time, $time];
		
		//execute sql
		$query = $this->db->query($sql, $binding);
		
		//return as array
		return $query->row_array();
	}

	function dropTemporaryAssignmentTable($tablename) {
		$this->db->query("DROP TEMPORARY TABLE IF EXISTS " . $tablename . ";");
	}

	function updateTemporaryAssignmentTable($tmp_table, $qso_id, $contest_session_id)
	{
		
		//sanity checks
		if ($qso_id <= 0 || $contest_session_id <= 0) {
			return false;
		}

		//update temporary table
		$this->db
			->where('qso_id', $qso_id)
			->update($tmp_table, [
				'contest_session_id' => $contest_session_id
			]);

		//return affected rows
		return ($this->db->affected_rows() >= 0);
	}
}
