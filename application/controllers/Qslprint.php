<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class QSLPrint extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));


		// Check if users logged in

		if($this->user_model->validate_session() == 0) {
			// user is not logged in
			redirect('user/login');
		}
	}

	public function index($station_id = 'All')
	{
		// Check if users logged in
		if(!$this->user_model->authorize(2) || !clubaccess_check(9)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }

		$this->load->model('stations');
		$data['station_id'] = $this->security->xss_clean($station_id);
		$data['station_profile'] = $this->stations->all_of_user();

		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/qslprint.js',
		];

		$data['page_title'] = __("Print Requested QSLs");

		$this->load->view('interface_assets/header', $data);
		$this->load->view('qslprint/index');
		$this->load->view('interface_assets/footer', $footerData);

	}

	public function exportadif()
	{
		// Set memory limit to unlimited to allow heavy usage
		ini_set('memory_limit', '-1');

		if ($this->uri->segment(3) == 'all') {
			$station_id = 'All';
		} else {
			$station_id = $this->security->xss_clean($this->uri->segment(3));
		}

		$this->load->model('adif_data');

		$data['qsos'] = $this->adif_data->export_printrequested($station_id);

		$this->load->view('adif/data/exportall', $data);
	}

	public function exportcsv()
	{
		// Set memory limit to unlimited to allow heavy usage
		ini_set('memory_limit', '-1');

		if ($this->uri->segment(3) == 'all') {
			$station_id = 'All';
		} else {
			$station_id = $this->security->xss_clean($this->uri->segment(3));
		}

		$this->load->model('logbook_model');

		$myData = $this->logbook_model->get_qsos_for_printing($station_id);

		// file name
		$filename = 'qsl_export.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv;charset=iso-8859-1");

		// file creation
		$file = fopen('php://output', 'w');

		$header = array("STATION_CALLSIGN",
						"CALL",
						"QSL_VIA",
						"DATE_ON",
						"TIME_ON",
						"MODE",
						"SUBMODE",
						"FREQ",
						"BAND",
						"RST_SENT",
						"SAT_NAME",
						"SAT_MODE",
						"PROP_MODE",
						"QSL_RCVD",
						"COMMENT",
						"ROUTING",
						"ADIF",
						"ENTITY",
						"GRIDSQUARE",
						"STATION_GRIDSQUARE");

		fputcsv($file, $header, separator: ",", enclosure: "\"", escape: "\\");

		foreach ($myData->result() as $qso) {
				$datetimeObj = new DateTime($qso->COL_TIME_ON);
				$date = $datetimeObj->format('Y-m-d');
				$time = $datetimeObj->format('H:i:s');

			fputcsv($file,
				array($qso->STATION_CALLSIGN,
				$qso->COL_CALL,
				$qso->COL_QSL_VIA!=""?"via ".$qso->COL_QSL_VIA:"",
				$date,
				$time,
				$qso->COL_MODE,
				$qso->COL_SUBMODE,
				$qso->COL_FREQ,
				$qso->COL_BAND,
				$qso->COL_RST_SENT,
				$qso->COL_SAT_NAME,
				$qso->COL_SAT_MODE,
				$qso->COL_PROP_MODE,
				$qso->COL_QSL_RCVD =='Y'?'TNX QSL':'PSE QSL',
				$qso->COL_COMMENT,
				$qso->COL_ROUTING,
				$qso->ADIF,
				$qso->ENTITY,
				$qso->COL_GRIDSQUARE,
				$qso->COL_MY_GRIDSQUARE), separator: ",", enclosure: "\"", escape: "\\");
		}

		fclose($file);
		exit;
	}

	function qsl_printed() {

		if ($this->uri->segment(3) == 'all') {
			$station_id = 'All';
		} else {
			$station_id = $this->security->xss_clean($this->uri->segment(3));
		}

		$this->load->model('qslprint_model');
		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }

			// Update Logbook to Mark Paper Card Sent

			$this->qslprint_model->mark_qsos_printed($station_id);

			$this->session->set_flashdata('notice', 'QSOs are marked as sent');

			redirect('logbook');
	}

	public function delete_from_qsl_queue() {
		$id = $this->input->post('id', true);
		$this->load->model('qslprint_model');

		$this->qslprint_model->delete_from_qsl_queue($id);
	}

	/*
	 * Serves one page of the print queue to the DataTable (server side processing).
	 */
	public function qsos_datatable() {
		if(!$this->user_model->authorize(2) || !clubaccess_check(9)) {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error' => __("You're not allowed to do that!")]));
			return;
		}

		$this->load->model('stations');
		$this->load->model('qslprint_model');

		$station_id = $this->input->post('station_id', true);
		if ($station_id != 'All' && !$this->stations->check_station_is_accessible($station_id)) {
			$station_id = 'All';
		}

		$order = $this->input->post('order');
		$search = $this->input->post('search');
		$columns = $this->input->post('columns');

		$column_search = [];
		if (is_array($columns)) {
			foreach ($columns as $index => $column) {
				$column_search[(int) $index] = (string) ($column['search']['value'] ?? '');
			}
		}

		$params = [
			'start' => (int) $this->input->post('start'),
			'length' => (int) $this->input->post('length'),
			'search' => (string) ($search['value'] ?? ''),
			'column_search' => $column_search,
			'order_column' => isset($order[0]['column']) ? (int) $order[0]['column'] : null,
			'order_dir' => (string) ($order[0]['dir'] ?? ''),
		];

		$result = $this->qslprint_model->get_qsos_for_print_paged($station_id, $params);

		$response = [
			'draw' => (int) $this->input->post('draw'),
			'recordsTotal' => $result['recordsTotal'],
			'recordsFiltered' => $result['recordsFiltered'],
			'data' => array_map([$this, 'print_row'], $result['data']),
		];

		// The filter dropdowns are built once, on the first draw
		if ($response['draw'] <= 1) {
			$response['filters'] = $this->print_filters($station_id);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	/*
	 * Renders one queue entry into the cells the DataTable expects.
	 */
	private function print_row($qsl) {
		$timestamp = strtotime($qsl->COL_TIME_ON);
		$call = html_escape(strtoupper($qsl->COL_CALL));

		$icons = '<a target="_blank" href="https://www.qrz.com/db/'.$call.'"><img width="16" height="16" src="'.base_url().'images/icons/qrz.png" alt="Lookup '.$call.' on QRZ.com"></a>'
			.'<a target="_blank" href="https://www.hamqth.com/'.$call.'"><img width="16" height="16" src="'.base_url().'images/icons/hamqth.png" alt="Lookup '.$call.' on HamQTH"></a>'
			.'<a target="_blank" href="http://www.eqsl.cc/Member.cfm?'.$call.'"><img width="16" height="16" src="'.base_url().'images/icons/eqsl.png" alt="Lookup '.$call.' on eQSL.cc"></a>';

		if ($qsl->COL_SAT_NAME != null) {
			$band = __("SAT").' '.html_escape($qsl->COL_SAT_NAME).' '.html_escape(strtolower($qsl->COL_BAND)).'/'.html_escape(strtolower($qsl->COL_BAND_RX));
			$freq = __("SAT").' '.html_escape($qsl->COL_SAT_NAME).' '.$this->frequency->qrg_conversion($qsl->frequency).'/'.$this->frequency->qrg_conversion($qsl->frequency_rx);
		} else {
			$band = html_escape(strtolower($qsl->COL_BAND));
			$freq = $this->frequency->qrg_conversion($qsl->frequency);
		}

		$sent_via = ['B' => __("Bureau"), 'D' => __("Direct"), 'E' => __("Electronic")];

		return [
			'DT_RowId' => 'qslprint_'.(int) $qsl->COL_PRIMARY_KEY,
			'checkbox' => '<div class="form-check"><input class="form-check-input" type="checkbox" name="selected_qsos[]" value="'.(int) $qsl->COL_PRIMARY_KEY.'" /></div>',
			'callsign' => '<span class="qso_call d-flex align-items-center justify-content-between">'
				.'<a id="edit_qso" class="callsign" href="javascript:displayQso('.(int) $qsl->COL_PRIMARY_KEY.');">'.$call.'</a>'
				.'<span class="qso_icons ms-3 d-flex align-items-center" style="gap: 2px;">'.$icons.'</span></span>',
			'date' => date($this->print_date_format(), $timestamp),
			'time' => date('H:i', $timestamp),
			'mode' => $qsl->COL_SUBMODE == null ? html_escape($qsl->COL_MODE) : html_escape($qsl->COL_SUBMODE),
			'band' => $band,
			'frequency' => $freq,
			'rst_sent' => html_escape($qsl->COL_RST_SENT),
			'rst_rcvd' => html_escape($qsl->COL_RST_RCVD),
			'qsl_via' => '<span class="callsign">'.html_escape($qsl->COL_QSL_VIA).'</span>',
			'station' => '<span class="badge text-bg-light">'.html_escape($qsl->station_callsign).'</span>',
			'profile' => html_escape($qsl->station_profile_name),
			'sent_via' => $sent_via[$qsl->COL_QSL_SENT_VIA] ?? '',
			'previous' => '<span class="badge '.($qsl->previous_qsl > 0 ? 'bg-warning' : 'bg-success').'" data-bs-toggle="tooltip" data-bs-title="'.__("Previous QSL sent (same band/mode)").'">'.$qsl->previous_qsl.'</span> / '
				.'<span class="badge bg-info" data-bs-toggle="tooltip" data-bs-title="'.__("QSL sent to callsign (total)").'">'.$qsl->qsl_sent_to_call.'</span> / '
				.'<span class="badge bg-info" data-bs-toggle="tooltip" data-bs-title="'.__("QSL received from callsign (total)").'">'.$qsl->qsl_rcvd_from_call.'</span>',
			'actions' => '<div class="d-inline-flex align-items-center gap-1">'
				.'<button type="button" onclick="mark_qsl_sent(\''.(int) $qsl->COL_PRIMARY_KEY.'\', \''.html_escape($qsl->COL_QSL_SENT_VIA).'\')" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-title="'.__("Mark as sent").'"><i class="fa fa-check"></i></button>'
				.'<button type="button" onclick="deleteFromQslQueue(\''.(int) $qsl->COL_PRIMARY_KEY.'\')" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-title="'.__("Remove").'"><i class="fas fa-trash-alt"></i></button>'
				.'<button type="button" onclick="openQsoList(\''.html_escape($qsl->COL_CALL).'\')" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-title="'.__("QSO List").'"><i class="fas fa-search"></i></button>'
				.'</div>',
		];
	}

	private function print_date_format() {
		return $this->session->userdata('user_date_format') ?: $this->config->item('qso_date_format');
	}

	/*
	 * Values for the filter dropdowns. Date and send method need a separate label,
	 * the rest is shown as it is stored.
	 */
	private function print_filters($station_id) {
		$values = $this->qslprint_model->get_print_filter_values($station_id);
		$date_format = $this->print_date_format();
		$sent_via = ['B' => __("Bureau"), 'D' => __("Direct"), 'E' => __("Electronic")];

		$filters = [
			1 => array_map(function($v) { return ['value' => $v, 'label' => $v]; }, $values['callsign']),
			2 => array_map(function($v) use ($date_format) { return ['value' => $v, 'label' => date($date_format, strtotime($v))]; }, $values['qso_date']),
			4 => array_map(function($v) { return ['value' => $v, 'label' => $v]; }, $values['mode']),
			5 => array_map(function($v) { return ['value' => $v, 'label' => $v]; }, $values['band']),
			10 => array_map(function($v) { return ['value' => $v, 'label' => $v]; }, $values['station']),
			12 => [],
		];

		foreach ($values['sent_via'] as $via) {
			if (isset($sent_via[$via])) {
				$filters[12][] = ['value' => $via, 'label' => $sent_via[$via]];
			}
		}

		return $filters;
	}

	public function open_qso_list() {
		$callsign = $this->input->post('callsign', true);
		$this->load->model('qslprint_model');

		$data['qsos'] = $this->qslprint_model->open_qso_list($callsign);
		$this->load->view('qslprint/qsolist', $data);
	}

	public function add_qso_to_print_queue() {
		$id = $this->input->post('id', true);
		$this->load->model('qslprint_model');

		$this->qslprint_model->add_qso_to_print_queue($id);
	}

	public function show_oqrs() {
		$id = $this->input->post('id', true);

		$this->load->model('qslprint_model');

		$data['result'] = $this->qslprint_model->show_oqrs($id);
		$this->load->view('oqrs/showoqrs', $data);
	}

	public function printdialog() {
		$data['type'] = $this->input->post('printType', true);
		$data['id_list'] = $this->input->post('id_list', true);
		$data['printAll'] = $this->input->post('printAll', true);

		if ($data['type'] === 'label') {
			$this->load->view('qslprint/printlabel', $data);
		} else {
			$this->load->model('Qslpostcard_model');
			$data['templates'] = $this->Qslpostcard_model->list_templates();
			$this->load->view('qslprint/printqsl', $data);
		}
	}

}
