<?php

class Qslprint_model extends CI_Model {

	function mark_qsos_printed($station_id2 = NULL) {
		$this->load->model('Stations');
		$station_ids = array();

		if ($station_id2 == NULL) {
			$station_id = $this->Stations->find_active();
			array_push($station_ids, $station_id);
		} else if ($station_id2 == 'All') {
			// get all stations of user
			$stations = $this->Stations->all_of_user();
			$station_ids = array();
			foreach ($stations->result() as $row) {
				array_push($station_ids, $row->station_id);
			}
		} else {
			// be sure that station belongs to user
			if (!$this->Stations->check_station_is_accessible($station_id2)) {
				return;
			}
			array_push($station_ids, $station_id2);
		}

		$this->update_qsos_bureau($station_ids);

		$this->update_qsos($station_ids);
	}

	/*
	 * Updates the QSOs that do not have any COL_QSL_SENT_VIA set
	 */
	 function update_qsos_bureau($station_ids) {
		$data = array(
			'COL_QSLSDATE' => date('Y-m-d'),
			'COL_QSL_SENT' => "Y",
			'COL_QSL_SENT_VIA' => "B",
		);

		$this->db->where_in("station_id", $station_ids);
		$this->db->where_in("COL_QSL_SENT", array("R","Q"));
		$this->db->where("coalesce(COL_QSL_SENT_VIA, '') = ''");

		$this->db->update($this->config->item('table_name'), $data);
	}

	/*
	 * Updates the QSOs that do have COL_QSL_SENT_VIA set
	 */
	function update_qsos($station_ids) {
		$data = array(
			'COL_QSLSDATE' => date('Y-m-d'),
			'COL_QSL_SENT' => "Y",
		);

		$this->db->where_in("station_id", $station_ids);
		$this->db->where_in("COL_QSL_SENT", array("R","Q"));
		$this->db->where("coalesce(COL_QSL_SENT_VIA, '') != ''");

		$this->db->update($this->config->item('table_name'), $data);
	}

	/*
	 * We list out the QSL's ready for print.
	 * station_id is not provided when loading page.
	 * It will be provided when calling the function when the dropdown is changed and the javascript fires
	 */
	function get_qsos_for_print($station_id = 'All') {
		$binding = [];
		$binding[] = $this->session->userdata('user_id');
		$sql = "SELECT
			COALESCE(prev.previous_qsl, 0) AS previous_qsl,
			COALESCE(sr.qsl_sent_to_call, 0) AS qsl_sent_to_call,
			COALESCE(sr.qsl_rcvd_from_call, 0) AS qsl_rcvd_from_call,
			log.COL_QSL_SENT, log.COL_PRIMARY_KEY, log.COL_DXCC, log.COL_CALL, log.COL_SAT_NAME, log.COL_SAT_MODE, log.COL_BAND_RX, log.COL_FREQ as frequency, log.COL_FREQ_RX as frequency_rx, log.COL_TIME_ON, log.COL_MODE, log.COL_RST_SENT, log.COL_RST_RCVD, log.COL_QSL_VIA, log.COL_QSL_SENT_VIA, log.COL_SUBMODE, log.COL_BAND, sp.station_id, sp.station_callsign, sp.station_profile_name, o.qsoid
			FROM ".$this->config->item('table_name')." log
			INNER JOIN station_profile sp ON sp.`station_id` = log.`station_id`
			LEFT OUTER JOIN oqrs o ON o.`qsoid` = log.`COL_PRIMARY_KEY`
			LEFT JOIN (
				SELECT station_id, COL_CALL,
					SUM(CASE WHEN COL_QSL_SENT = 'Y' THEN 1 ELSE 0 END) AS qsl_sent_to_call,
					SUM(CASE WHEN COL_QSL_RCVD = 'Y' THEN 1 ELSE 0 END) AS qsl_rcvd_from_call
				FROM ".$this->config->item('table_name')."
				WHERE COL_QSL_SENT = 'Y' OR COL_QSL_RCVD = 'Y'
				GROUP BY station_id, COL_CALL
			) sr ON sr.station_id = log.station_id AND sr.COL_CALL = log.COL_CALL
			LEFT JOIN (
				SELECT station_id, COL_CALL, COL_BAND, COL_MODE, COL_SAT_NAME, COUNT(*) AS previous_qsl
				FROM ".$this->config->item('table_name')."
				WHERE COL_QSL_SENT = 'Y'
				GROUP BY station_id, COL_CALL, COL_BAND, COL_MODE, COL_SAT_NAME
			) prev ON prev.station_id = log.station_id
				AND prev.COL_CALL = log.COL_CALL
				AND prev.COL_BAND = log.COL_BAND
				AND prev.COL_MODE = log.COL_MODE
				AND COALESCE(prev.COL_SAT_NAME, '') = COALESCE(log.COL_SAT_NAME, '')
			WHERE sp.`user_id` = ?";
		if ($station_id != 'All') {
			$sql .= ' AND log.`station_id` = ?';
			$binding[] = $station_id;
		}
		$sql .= " AND log.`COL_QSL_SENT` IN('R', 'Q')
			ORDER BY log.`COL_DXCC` ASC, log.`COL_CALL` ASC, log.`COL_SAT_NAME` ASC, log.`COL_SAT_MODE` ASC, log.`COL_BAND_RX` ASC, log.`COL_TIME_ON` ASC, log.`COL_MODE` ASC LIMIT 1000";

		$query = $this->db->query($sql, $binding);
		return $query;
	}

	/*
	 * DataTables column index -> SQL expression, used for ordering and for the
	 * per-column filter dropdowns. Only indexes listed here are sortable or
	 * filterable, so no client supplied string ever reaches the SQL.
	 */
	private function print_column_map() {
		return array(
			1  => 'log.COL_CALL',
			2  => 'DATE(log.COL_TIME_ON)',
			4  => "COALESCE(NULLIF(log.COL_SUBMODE, ''), log.COL_MODE)",
			5  => 'LOWER(log.COL_BAND)',
			6  => 'log.COL_FREQ',
			7  => 'log.COL_RST_SENT',
			8  => 'log.COL_RST_RCVD',
			9  => 'log.COL_QSL_VIA',
			10 => 'sp.station_callsign',
			11 => 'sp.station_profile_name',
			12 => "COALESCE(log.COL_QSL_SENT_VIA, '')",
		);
	}

	private function print_queue_from() {
		return " FROM ".$this->config->item('table_name')." log
			INNER JOIN station_profile sp ON sp.`station_id` = log.`station_id`";
	}

	/*
	 * Builds the WHERE for the print queue. $params may carry the DataTables
	 * global search and the per-column filters; pass an empty array to get the
	 * unfiltered queue.
	 */
	private function print_queue_where($station_id, $params, &$binding) {
		$where = " WHERE sp.`user_id` = ?";
		$binding[] = $this->session->userdata('user_id');

		if ($station_id != 'All') {
			$where .= " AND log.`station_id` = ?";
			$binding[] = $station_id;
		}

		$where .= " AND log.`COL_QSL_SENT` IN('R', 'Q')";

		foreach ($this->print_column_map() as $index => $expression) {
			if (isset($params['column_search'][$index]) && $params['column_search'][$index] !== '') {
				$where .= " AND ".$expression." = ?";
				$binding[] = $params['column_search'][$index];
			}
		}

		if (isset($params['search']) && $params['search'] !== '') {
			$columns = array('log.COL_CALL', 'log.COL_QSL_VIA', 'sp.station_callsign', 'sp.station_profile_name', 'log.COL_MODE', 'log.COL_SUBMODE', 'log.COL_BAND');
			$where .= ' AND ('.implode(' LIKE ? OR ', $columns).' LIKE ?)';
			$binding = array_merge($binding, array_fill(0, count($columns), '%'.$params['search'].'%'));
		}

		return $where;
	}

	private function print_queue_order($params) {
		$map = $this->print_column_map();

		if (isset($params['order_column']) && isset($map[$params['order_column']])) {
			$direction = (strtolower($params['order_dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';
			return ' ORDER BY '.$map[$params['order_column']].' '.$direction;
		}

		return " ORDER BY log.`COL_DXCC` ASC, log.`COL_CALL` ASC, log.`COL_SAT_NAME` ASC, log.`COL_SAT_MODE` ASC, log.`COL_BAND_RX` ASC, log.`COL_TIME_ON` ASC, log.`COL_MODE` ASC";
	}

	/*
	 * One page of the print queue for the DataTable.
	 * The QSL counters are not joined in, they are looked up for the callsigns of
	 * this page only (see print_queue_stats), otherwise every draw would run a
	 * GROUP BY over the whole logbook.
	 */
	function get_qsos_for_print_paged($station_id, $params) {
		$binding = array();
		$total = $this->db->query("SELECT COUNT(*) AS cnt".$this->print_queue_from().$this->print_queue_where($station_id, array(), $binding), $binding)->row()->cnt;

		$binding = array();
		$where = $this->print_queue_where($station_id, $params, $binding);
		$filtered = $this->db->query("SELECT COUNT(*) AS cnt".$this->print_queue_from().$where, $binding)->row()->cnt;

		$start = max(0, (int) ($params['start'] ?? 0));
		$length = (int) ($params['length'] ?? 25);

		$sql = "SELECT log.COL_PRIMARY_KEY, log.COL_CALL, log.COL_TIME_ON, log.COL_MODE, log.COL_SUBMODE,
			log.COL_BAND, log.COL_BAND_RX, log.COL_FREQ as frequency, log.COL_FREQ_RX as frequency_rx,
			log.COL_SAT_NAME, log.COL_SAT_MODE, log.COL_RST_SENT, log.COL_RST_RCVD,
			log.COL_QSL_VIA, log.COL_QSL_SENT_VIA, sp.station_id, sp.station_callsign, sp.station_profile_name"
			.$this->print_queue_from().$where.$this->print_queue_order($params);

		// DataTables sends -1 for "All"
		if ($length > 0) {
			$sql .= " LIMIT ".$length." OFFSET ".$start;	// both cast to int above
		}

		$rows = $this->db->query($sql, $binding)->result();
		$this->print_queue_stats($rows);

		return array(
			'data' => $rows,
			'recordsTotal' => (int) $total,
			'recordsFiltered' => (int) $filtered,
		);
	}

	/*
	 * Adds previous_qsl / qsl_sent_to_call / qsl_rcvd_from_call to the given rows,
	 * looking them up for the callsigns of those rows only.
	 */
	private function print_queue_stats($rows) {
		if (!$rows) {
			return;
		}

		$station_ids = array();
		$calls = array();
		foreach ($rows as $row) {
			$station_ids[$row->station_id] = $row->station_id;
			$calls[$row->COL_CALL] = $row->COL_CALL;
		}
		$binding = array_merge(array_values($station_ids), array_values($calls));
		$scope = " AND station_id IN (".implode(',', array_fill(0, count($station_ids), '?')).")"
			." AND COL_CALL IN (".implode(',', array_fill(0, count($calls), '?')).")";

		$totals = array();
		$sql = "SELECT station_id, COL_CALL,
			SUM(CASE WHEN COL_QSL_SENT = 'Y' THEN 1 ELSE 0 END) AS qsl_sent_to_call,
			SUM(CASE WHEN COL_QSL_RCVD = 'Y' THEN 1 ELSE 0 END) AS qsl_rcvd_from_call
			FROM ".$this->config->item('table_name')."
			WHERE (COL_QSL_SENT = 'Y' OR COL_QSL_RCVD = 'Y')".$scope."
			GROUP BY station_id, COL_CALL";
		foreach ($this->db->query($sql, $binding)->result() as $row) {
			$totals[$row->station_id.'|'.$row->COL_CALL] = $row;
		}

		$previous = array();
		$sql = "SELECT station_id, COL_CALL, COL_BAND, COL_MODE, COALESCE(COL_SAT_NAME, '') AS sat_name, COUNT(*) AS previous_qsl
			FROM ".$this->config->item('table_name')."
			WHERE COL_QSL_SENT = 'Y'".$scope."
			GROUP BY station_id, COL_CALL, COL_BAND, COL_MODE, COALESCE(COL_SAT_NAME, '')";
		foreach ($this->db->query($sql, $binding)->result() as $row) {
			$previous[$row->station_id.'|'.$row->COL_CALL.'|'.$row->COL_BAND.'|'.$row->COL_MODE.'|'.$row->sat_name] = $row->previous_qsl;
		}

		foreach ($rows as $row) {
			$total = $totals[$row->station_id.'|'.$row->COL_CALL] ?? null;
			$row->qsl_sent_to_call = $total ? (int) $total->qsl_sent_to_call : 0;
			$row->qsl_rcvd_from_call = $total ? (int) $total->qsl_rcvd_from_call : 0;

			$key = $row->station_id.'|'.$row->COL_CALL.'|'.$row->COL_BAND.'|'.$row->COL_MODE.'|'.($row->COL_SAT_NAME ?? '');
			$row->previous_qsl = (int) ($previous[$key] ?? 0);
		}
	}

	/*
	 * Distinct values for the filter dropdowns above the table. One pass over the
	 * queue, deduplicated per column afterwards.
	 */
	function get_print_filter_values($station_id) {
		$binding = array();
		$where = $this->print_queue_where($station_id, array(), $binding);

		$sql = "SELECT DISTINCT UPPER(log.COL_CALL) AS callsign,
			DATE(log.COL_TIME_ON) AS qso_date,
			COALESCE(NULLIF(log.COL_SUBMODE, ''), log.COL_MODE) AS mode,
			LOWER(log.COL_BAND) AS band,
			sp.station_callsign AS station,
			COALESCE(log.COL_QSL_SENT_VIA, '') AS sent_via"
			.$this->print_queue_from().$where;

		$values = array('callsign' => array(), 'qso_date' => array(), 'mode' => array(), 'band' => array(), 'station' => array(), 'sent_via' => array());
		foreach ($this->db->query($sql, $binding)->result_array() as $row) {
			foreach (array_keys($values) as $column) {
				if ($row[$column] !== '' && $row[$column] !== null) {
					$values[$column][$row[$column]] = true;
				}
			}
		}

		foreach ($values as $column => $unique) {
			$values[$column] = array_keys($unique);
			sort($values[$column]);
		}

		return $values;
	}

	function delete_from_qsl_queue($id) {
		// be sure that QSO belongs to user
		$this->load->model('logbook_model');
		if (!$this->logbook_model->check_qso_is_accessible($id)) {
			return;
		}

		$data = array(
			'COL_QSL_SENT' => "N",
		);

		$this->db->where("COL_PRIMARY_KEY", $id);
		$this->db->update($this->config->item('table_name'), $data);

		return true;
	}

	function add_qso_to_print_queue($id) {
		// be sure that QSO belongs to user
		$this->load->model('logbook_model');
		if (!$this->logbook_model->check_qso_is_accessible($id)) {
			return;
		}

		$data = array(
			'COL_QSL_SENT' => "R",
		);

		$this->db->where("COL_PRIMARY_KEY", $id);
		$this->db->update($this->config->item('table_name'), $data);

		$this->db->where('qsoid', $id);
		$this->db->where_not_in('status', [2, 4]);
		$this->db->update('oqrs', ['status' => '3']);

		return true;
	}

	function open_qso_list($callsign) {
		$binding = [];

		$sql = "SELECT log.COL_QSL_SENT, log.COL_QSLRDATE, log.COL_QSL_RCVD, log.COL_QSL_RCVD_VIA, log.COL_QSLSDATE, log.COL_QSL_VIA, log.COL_QSL_SENT_VIA,
			log.COL_LOTW_QSL_SENT, log.COL_LOTW_QSLSDATE, log.COL_LOTW_QSL_RCVD, log.COL_LOTW_QSLRDATE,
			log.COL_PRIMARY_KEY, log.COL_DXCC, log.COL_CALL, log.COL_SAT_NAME, log.COL_SAT_MODE, log.COL_BAND_RX, log.COL_FREQ,
			log.COL_FREQ_RX, log.COL_TIME_ON, log.COL_MODE, log.COL_RST_SENT, log.COL_RST_RCVD,
			log.COL_SUBMODE, log.COL_BAND, sp.station_id, sp.station_callsign, sp.station_profile_name,
			(SELECT COUNT(*) FROM ".$this->config->item('table_name')." sentlog WHERE sentlog.COL_QSL_SENT = 'Y' AND sentlog.station_id = log.station_id AND sentlog.COL_CALL = log.COL_CALL) as qsl_sent_to_call,
			(SELECT COUNT(*) FROM ".$this->config->item('table_name')." rcvdlog WHERE rcvdlog.COL_QSL_RCVD = 'Y' AND rcvdlog.station_id = log.station_id AND rcvdlog.COL_CALL = log.COL_CALL) as qsl_rcvd_from_call,
			(SELECT COUNT(*) FROM ".$this->config->item('table_name')." prevlog WHERE prevlog.COL_QSL_SENT = 'Y' AND prevlog.station_id = log.station_id AND prevlog.COL_BAND = log.COL_BAND AND prevlog.COL_CALL = log.COL_CALL AND prevlog.COL_MODE = log.COL_MODE AND COALESCE(prevlog.COL_SAT_NAME, '') = COALESCE(log.COL_SAT_NAME, '') AND prevlog.COL_PRIMARY_KEY != log.COL_PRIMARY_KEY) as previous_qsl
			FROM ".$this->config->item('table_name')." log
			JOIN station_profile sp ON sp.`station_id` = log.`station_id`
			WHERE sp.`user_id` = ?
			AND (log.COL_CALL like ? OR log.COL_CALL like ? OR log.COL_CALL like ? OR log.COL_CALL = ?)
			AND coalesce(log.COL_QSL_SENT, '') not in ('R', 'Q')
			ORDER BY log.`COL_DXCC` ASC, log.`COL_CALL` ASC, log.`COL_SAT_NAME` ASC, log.`COL_SAT_MODE` ASC, log.`COL_BAND_RX` ASC, log.`COL_TIME_ON` ASC, log.`COL_MODE` ASC LIMIT 1000";

		$binding[] = $this->session->userdata('user_id');
		$binding[] = "%/".$callsign."/%";
		$binding[] = "%/".$callsign;
		$binding[] = $callsign."/%";
		$binding[] = $callsign;

		$query = $this->db->query($sql, $binding);
		return $query;
	}

	function show_oqrs($id) {
		$sql = "SELECT requesttime as 'Request time', requestcallsign as 'Requester', email as 'Email', note as 'Note'
		FROM oqrs
		JOIN station_profile ON station_profile.station_id = oqrs.station_id
		WHERE station_profile.user_id = ?
		AND oqrs.id = ?";

		$binding = [];
		$binding[] = $this->session->userdata('user_id');
		$binding[] = $id;

		return $this->db->query($sql, $binding)->result();
	}

}

?>
