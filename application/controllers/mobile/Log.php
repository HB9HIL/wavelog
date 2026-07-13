<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mobile QSO logging.
 *
 * index() renders the offline-capable entry form; sync() is the endpoint the
 * client-side IndexedDB queue (assets/js/mobile/offline-log.js) posts buffered
 * QSOs to once connectivity returns. Session-authenticated via Mobile_Controller,
 * so the active station profile is taken straight from the session.
 */
class Log extends Mobile_Controller {

	public function index() {
		$this->load->model('usermodes');

		$data['page_title'] = __('Log QSO');
		$data['modes']      = $this->usermodes->active();
		$data['bands']      = $this->config->item('bands_available');

		$this->render('mobile/log', $data);
	}

	/**
	 * Sync endpoint: accept a JSON batch of offline-queued QSOs and store them.
	 * Responds with a per-QSO tmp_id -> server_id mapping plus an error list so
	 * the client can mark records synced or keep them pending.
	 */
	public function sync() {
		header('Content-Type: application/json');

		if ($this->input->method() !== 'post') {
			http_response_code(405);
			echo json_encode(['success' => false, 'error' => 'POST required']);
			return;
		}

		// Release the session lock early so a large batch does not block other requests
		session_write_close();

		$payload = json_decode($this->input->raw_input_stream, true);
		if (!is_array($payload) || !isset($payload['qsos']) || !is_array($payload['qsos'])) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => 'Invalid payload']);
			return;
		}

		$this->load->model('logbook_model');

		$saved  = [];
		$errors = [];

		foreach ($payload['qsos'] as $q) {
			$tmp_id = $q['tmp_id'] ?? null;
			if (!$tmp_id) {
				continue;
			}

			try {
				$qso_data = $this->_map_qso($q);
				$result   = $this->logbook_model->create_qso($qso_data, false);

				if (is_array($result) && !empty($result['qso_id'])) {
					$saved[] = ['tmp_id' => $tmp_id, 'server_id' => $result['qso_id']];
				} else {
					// create_qso returns a string message on "Station not accessible" or false on failure
					$errors[] = ['tmp_id' => $tmp_id, 'error' => is_string($result) ? $result : __('Failed to save QSO')];
				}
			} catch (Exception $e) {
				$errors[] = ['tmp_id' => $tmp_id, 'error' => $e->getMessage()];
			}
		}

		echo json_encode([
			'success' => true,
			'saved'   => $saved,
			'errors'  => $errors,
		]);
	}

	/**
	 * Map a client QSO record to the array shape expected by Logbook_model::create_qso().
	 * Mirrors the 'save_qso' mapping in Contesting::_processCommand(), minus contest fields.
	 * With manual=1 create_qso parses date as Y-m-d and time as H:i (see create_qso()).
	 */
	private function _map_qso($q) {
		$callsign = $this->_validate_callsign($q['callsign'] ?? '');

		$band = trim((string)($q['band'] ?? ''));
		$freq = trim((string)($q['frequency'] ?? ''));
		if ($band === '' && $freq === '') {
			throw new Exception(__('Band or frequency required'));
		}

		$qso_data = [
			'manual'            => 1,
			'start_date'        => $q['date'] ?? date('Y-m-d'),
			'start_time'        => $q['time'] ?? date('H:i'),
			'end_time'          => $q['time'] ?? date('H:i'),
			'callsign'          => $callsign,
			'freq_display'      => $freq !== '' ? $freq : NULL,
			'mode'              => $q['mode'] ?? NULL,
			'rst_sent'          => $q['rst_sent'] ?? NULL,
			'rst_rcvd'          => $q['rst_rcvd'] ?? NULL,
			'locator'           => $q['gridsquare'] ?? NULL,
			'name'              => $q['name'] ?? NULL,
			'comment'           => $q['comment'] ?? NULL,
			'operator_callsign' => strtoupper(trim($this->session->userdata('operator_callsign') ?: $this->session->userdata('user_callsign'))),
		];

		// Only set 'band' when non-empty: create_qso() uses isset(), and a NULL here
		// would wrongly trigger a frequency-based band lookup on an empty frequency.
		if ($band !== '') {
			$qso_data['band'] = $band;
		}

		return $qso_data;
	}

	/**
	 * Validate a callsign, matching Contesting::_validateCallsign().
	 */
	private function _validate_callsign($callsign) {
		$call = str_replace('Ø', '0', strtoupper(trim((string)$callsign)));
		if ($call === '' || !preg_match('/^[A-Z0-9\/]+$/', $call)) {
			throw new Exception(__('Invalid callsign'));
		}
		return $call;
	}
}
