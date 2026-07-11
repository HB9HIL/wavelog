<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Wavelog\Dxcc\Dxcc;

require_once APPPATH . '../src/Dxcc/Dxcc.php';

class Dxcluster extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }
		$this->load->is_loaded('dxcluster_model') ?: $this->load->model('dxcluster_model');
	}


	public function spots($band, $age = '', $de = '', $mode = 'All') {
		// Sanitize inputs
		$band = $this->security->xss_clean($band);
		$mode = $this->security->xss_clean($mode);


		// Only load cache driver if caching is enabled
		if (($this->config->item('enable_dxcluster_file_cache_band') ?? false) || ($this->config->item('enable_dxcluster_file_cache_worked') ?? false)) {
			$this->load->driver('cache', [
				'adapter' => $this->config->item('cache_adapter') ?? 'file', 
				'backup' => $this->config->item('cache_backup') ?? 'file',
				'key_prefix' => $this->config->item('cache_key_prefix') ?? ''
			]);
		}

		if ($age == '') {
			$age = $this->optionslib->get_option('dxcluster_maxage') ?? 60;
		} else {
			$age = (int)$age;
		}

		if ($de == '') {
			$de = $this->optionslib->get_option('dxcluster_decont') ?? 'EU';
		} else {
			$de = $this->security->xss_clean($de);
		}
		$calls_found = $this->dxcluster_model->dxc_spotlist($band, $age, $de, $mode);

		header('Content-Type: application/json');
		http_response_code(200);
		if ($calls_found && !empty($calls_found)) {
			echo json_encode($calls_found, JSON_PRETTY_PRINT);
		} else {
			echo json_encode([], JSON_PRETTY_PRINT);  // "error: not found" would be misleading here. No spots are not an error. Therefore we return an empty array
		}
	}

	/**
	 * Returns the per-user worked/confirmed CALLSIGN status + last-worked info for a batch
	 * of live spots. worked_dxcc/worked_continent are NOT returned here — the Bandmap
	 * resolves those locally from the pre-loaded worked-slots (see worked_slots()). The
	 * client only calls this for spots whose DXCC slot is already worked, so the lookup
	 * stays small.
	 */
	public function worked_status() {
		session_write_close();
		header('Content-Type: application/json');

		// Objects (not assoc) so the batch model methods can read $spot->spotted,
		// $spot->dxcc_spotted->dxcc_id, etc. — exactly like dxc_spotlist() spots.
		$spots = json_decode(file_get_contents('php://input'));
		if (!is_array($spots) || empty($spots)) {
			echo json_encode(['statuses' => new stdClass(), 'last_worked' => new stdClass()]);
			return;
		}

		// get_batch_spot_statuses() uses the cache driver when worked-status caching
		// is enabled — load it the same way spots() does, or the model call fails.
		if ($this->config->item('enable_dxcluster_file_cache_worked') ?? false) {
			$this->load->driver('cache', [
				'adapter'    => $this->config->item('cache_adapter') ?? 'file',
				'backup'     => $this->config->item('cache_backup') ?? 'file',
				'key_prefix' => $this->config->item('cache_key_prefix') ?? ''
			]);
		}

		$this->load->is_loaded('logbook_model') ?: $this->load->model('logbook_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

		// Only the call-level slice is needed here; worked_dxcc/continent come from the
		// pre-loaded worked-slots on the client (see get_worked_slots() / worked_slots()).
		$full = $this->logbook_model->get_batch_spot_statuses($spots, $logbooks_locations_array);
		$statuses = [];
		foreach ($full as $call => $s) {
			$statuses[$call] = ['worked_call' => $s['worked_call'], 'cnfmd_call' => $s['cnfmd_call']];
		}

		// Only fetch last-worked details for callsigns that are actually worked.
		$worked_spots = [];
		foreach ($spots as $spot) {
			$call = $spot->spotted ?? '';
			if ($call !== '' && isset($statuses[$call]) && !empty($statuses[$call]['worked_call'])) {
				$worked_spots[] = $spot;
			}
		}
		$last_worked = !empty($worked_spots)
			? $this->logbook_model->get_batch_last_worked($worked_spots, $logbooks_locations_array)
			: [];

		// Format LAST_QSO the same way dxc_spotlist() does, so a spot looks identical
		// whether it came from the seed fetch or the live feed.
		$custom_date_format = $this->session->userdata('user_date_format') ?: $this->config->item('qso_date_format');
		foreach ($last_worked as $call => $lw) {
			$ts = !empty($lw->LAST_QSO) ? strtotime($lw->LAST_QSO) : false;
			if ($ts !== false && $ts > 0) {
				$last_worked[$call]->LAST_QSO = date($custom_date_format, $ts);
			} else {
				unset($last_worked[$call]);
			}
		}

		echo json_encode(['statuses' => $statuses, 'last_worked' => $last_worked]);
	}

	/**
	 * Returns the active logbook's worked/confirmed DXCC + continent slots (band|mode-aware)
	 * for the Bandmap live feed. The browser matches live spots against these sets locally,
	 * so worked_dxcc/worked_continent never need a per-spot server lookup. Also refetched by
	 * the Bandmap when the user's qso_changed signal fires.
	 */
	public function worked_slots() {
		session_write_close();
		header('Content-Type: application/json');

		// get_worked_slots() uses the cache driver when worked-status caching is enabled.
		if ($this->config->item('enable_dxcluster_file_cache_worked') ?? false) {
			$this->load->driver('cache', [
				'adapter'    => $this->config->item('cache_adapter') ?? 'file',
				'backup'     => $this->config->item('cache_backup') ?? 'file',
				'key_prefix' => $this->config->item('cache_key_prefix') ?? ''
			]);
		}

		$this->load->is_loaded('logbook_model') ?: $this->load->model('logbook_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

		echo json_encode($this->logbook_model->get_worked_slots($logbooks_locations_array));
	}

	public function qrg_lookup($qrg) {
		$call_found = $this->dxcluster_model->dxc_qrg_lookup($this->security->xss_clean($qrg));
		header('Content-Type: application/json');
		http_response_code(200);
		if ($call_found) {
			echo json_encode($call_found, JSON_PRETTY_PRINT);
		} else {
			echo json_encode([], JSON_PRETTY_PRINT); // "error: not found" would be misleading here. No call is not an error, the call is just not in the spotlist. Therefore we return an empty array
		}
	}

	// TODO: Is this used anywhere? If not, remove it!
	public function call($call) {
		$date = date('Y-m-d', time());
		$dxccobj = new Dxcc();

		$dxcc = $dxccobj->dxcc_lookup($call, $date);

		header('Content-Type: application/json');
		http_response_code(200);
		if ($dxcc) {
			echo json_encode($dxcc, JSON_PRETTY_PRINT);
		} else {
			echo json_encode(['error' => 'not found'], JSON_PRETTY_PRINT);
		}
	}
}
