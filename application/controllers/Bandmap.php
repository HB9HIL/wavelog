<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bandmap extends CI_Controller {

	function __construct() {
		parent::__construct();

		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }
		$this->load->model('bands');
	}

	function list() {
		$this->load->model('cat');
		$this->load->model('bands');
		$data['radios'] = $this->cat->radios();
		$data['radio_last_updated'] = $this->cat->last_updated()->row();
		$data['bands'] = $this->bands->get_user_bands_for_qso_entry();

		$this->load->is_loaded('worker') ?: $this->load->library('worker');
		$data['worker_enabled'] = $this->worker->is_enabled(); // without this line worker.js is not loaded!
		$radio_worker_topics = [];
		if ($this->worker->is_enabled()) {
			foreach ($data['radios']->result() as $radio) {
				$topic = 'radio.' . $radio->id;
				$this->worker->register_topic($topic);
				$radio_worker_topics[$radio->id] = ['topic' => $topic, 'token' => $this->worker->create_token($topic)];
			}
		}
		$pageData['radio_worker_topics'] = $radio_worker_topics;

		// Live DX spot feed: only wire it when the worker's DX cluster relay module
		// is actually running (it registers the "dxspots" topic itself). Without it
		// the Bandmap keeps its normal 30 s polling — nothing changes.
		$pageData['dxspots_worker'] = null;
		if ($this->worker->is_enabled() && $this->worker->has_topic('dxspots')) {
			$pageData['dxspots_worker'] = [
				'topic' => 'dxspots',
				'token' => $this->worker->create_token('dxspots'),
			];
		}

		$pageData['dxcluster_worked_slots'] = null;
		$pageData['qso_worker'] = null;
		if ($pageData['dxspots_worker'] !== null) {
			$this->load->is_loaded('logbook_model') ?: $this->load->model('logbook_model');
			if ($this->config->item('enable_dxcluster_file_cache_worked') ?? false) {
				$this->load->driver('cache', [
					'adapter'    => $this->config->item('cache_adapter') ?? 'file',
					'backup'     => $this->config->item('cache_backup') ?? 'file',
					'key_prefix' => $this->config->item('cache_key_prefix') ?? ''
				]);
			}
			$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
			$pageData['dxcluster_worked_slots'] = $this->logbook_model->get_worked_slots($logbooks_locations_array);

			$qso_topic = 'qso.' . $this->session->userdata('user_id');
			$this->worker->register_topic($qso_topic);
			$pageData['qso_worker'] = ['topic' => $qso_topic, 'token' => $this->worker->create_token($qso_topic)];
		}

		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/moment.min.js',
			'assets/js/datetime-moment.js',
			'assets/js/cat.js',
			'assets/js/leaflet/leaflet.geodesic.js',
			'assets/js/leaflet.polylineDecorator.js',
			'assets/js/leaflet/L.Terminator.js',
			'assets/js/sections/callstats.js',
			'assets/js/sections/bandmap_list.js',
		];

		// Get Date format
		if($this->session->userdata('user_date_format')) {
			// If Logged in and session exists
			$pageData['custom_date_format'] = $this->session->userdata('user_date_format');
		} else {
			// Get Default date format from /config/wavelog.php
			$pageData['custom_date_format'] = $this->config->item('qso_date_format');
		}

		switch ($pageData['custom_date_format']) {
			case "d/m/y": $pageData['custom_date_format'] = 'DD/MM/YY'; break;
			case "d/m/Y": $pageData['custom_date_format'] = 'DD/MM/YYYY'; break;
			case "m/d/y": $pageData['custom_date_format'] = 'MM/DD/YY'; break;
			case "m/d/Y": $pageData['custom_date_format'] = 'MM/DD/YYYY'; break;
			case "d.m.Y": $pageData['custom_date_format'] = 'DD.MM.YYYY'; break;
			case "y/m/d": $pageData['custom_date_format'] = 'YY/MM/DD'; break;
			case "Y-m-d": $pageData['custom_date_format'] = 'YYYY-MM-DD'; break;
			case "M d, Y": $pageData['custom_date_format'] = 'MMM DD, YYYY'; break;
			case "M d, y": $pageData['custom_date_format'] = 'MMM DD, YY'; break;
			default: $pageData['custom_date_format'] = 'DD/MM/YYYY';
		}

		$data['dxcluster_refresh_time'] = $this->config->item('dxcluster_refresh_time') ?? 30;

		// Get user map colors for bandmap spot status (confirmed/worked/unworked)
		$map_custom = json_decode($this->optionslib->get_map_custom());
		$validHex = function($color, $default) {
			return preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '') ? $color : $default;
		};
		$pageData['user_color_confirmed'] = $validHex($map_custom->qsoconfirm->color ?? '', '#90EE90');
		$pageData['user_color_worked'] = $validHex($map_custom->qso->color ?? '', '#E5A50A');
		$pageData['user_color_unworked'] = $validHex($map_custom->unworked->color ?? '', '#CC372D');

		$data['page_title'] = __("DXCluster");
		$this->load->view('interface_assets/header', $data);
		$this->load->view('bandmap/list',$pageData);
		$this->load->view('interface_assets/footer', $footerData);
	}

	// Get user's active bands and modes/submodes
}
