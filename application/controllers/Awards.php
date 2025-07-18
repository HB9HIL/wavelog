<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Handles Displaying of information for awards.

	These are taken from comments fields or ADIF fields
*/

class Awards extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model('user_model');
		if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('error', __("You're not allowed to do that!")); redirect('dashboard'); }
	}

	public function index()
	{
		// Render Page
		$data['page_title'] = __("Awards");
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/index');
		$this->load->view('interface_assets/footer');
	}

	public function dok ()
	{

		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

		$this->load->model('dok');
		$this->load->model('bands');
		$this->load->model('modes');

		if($this->input->method() === 'post') {
			$postdata['doks'] = $this->security->xss_clean($this->input->post('doks'));
		} else {
			$postdata['doks'] = 'both';
		}

		$data['worked_bands'] = $this->bands->get_worked_bands('dok');
		$data['modes'] = $this->modes->active();

		if ($this->input->post('band') != NULL) {
			if ($this->input->post('band') == 'All') {
				$bands = $data['worked_bands'];
			} else {
				$bands[] = $this->security->xss_clean($this->input->post('band'));
			}
		} else {
			$bands = $data['worked_bands'];
		}

		$data['bands'] = $bands;

		if($this->input->method() === 'post') {
			$postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
			$postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
			$postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
			$postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
			$postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
			$postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
			$postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
			$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
		} else {
			$postdata['qsl'] = 1;
			$postdata['lotw'] = 1;
			$postdata['eqsl'] = 0;
			$postdata['qrz'] = 0;
			$postdata['clublog'] = 0;
			$postdata['worked'] = 1;
			$postdata['confirmed'] = 1;
			$postdata['band'] = 'All';
			$postdata['mode'] = 'All';
		}

		if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
			$data['dok_array'] = $this->dok->get_dok_array($bands, $postdata, $location_list);
			$data['dok_summary'] = $this->dok->get_dok_summary($bands, $postdata, $location_list);
		} else {
			$location_list = null;
			$data['dok_array'] = null;
			$data['dok_summary'] = null;
		}

		// Render Page
		$data['page_title'] = sprintf(__("Awards - %s"), __("DOK"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/dok/index');
		$this->load->view('interface_assets/footer');

	}

	public function dxcc ()	{
		$this->load->model('dxcc');
		$this->load->model('modes');
		$this->load->model('bands');

		$data['orbits'] = $this->bands->get_worked_orbits();
		$data['sats_available'] = $this->bands->get_worked_sats();
		$data['user_default_band'] = $this->session->userdata('user_default_band');

		$data['worked_bands'] = $this->bands->get_worked_bands('dxcc'); // Used in the view for band select
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

		if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
			if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
				$bands = $data['worked_bands'];
			} else {
				$bands[] = $this->security->xss_clean($this->input->post('band'));
			}
		} else {
			$bands = $data['worked_bands'];
		}

		$data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

		if($this->input->method() === 'post') {
			$postdata['qsl'] = ($this->input->post('qsl',true) ?? 0) == 0 ? NULL: 1;
			$postdata['lotw'] = ($this->input->post('lotw',true) ?? 0) == 0 ? NULL: 1;
			$postdata['eqsl'] = ($this->input->post('eqsl',true) ?? 0) == 0 ? NULL: 1;
			$postdata['qrz'] = ($this->input->post('qrz',true) ?? 0) == 0 ? NULL: 1;
			$postdata['clublog'] = ($this->input->post('clublog',true) ?? 0) == 0 ? NULL: 1;
			$postdata['worked'] = ($this->input->post('worked',true) ?? 0) == 0 ? NULL: 1;
			$postdata['confirmed'] = ($this->input->post('confirmed',true) ?? 0)  == 0 ? NULL: 1;
			$postdata['notworked'] = ($this->input->post('notworked',true) ?? 0)  == 0 ? NULL: 1;

			$postdata['includedeleted'] = ($this->input->post('includedeleted',true) ?? 0) == 0 ? NULL: 1;
			$postdata['Africa'] = ($this->input->post('Africa',true) ?? 0) == 0 ? NULL: 1;
			$postdata['Asia'] = ($this->input->post('Asia',true) ?? 0) == 0 ? NULL: 1;
			$postdata['Europe'] = ($this->input->post('Europe',true) ?? 0) == 0 ? NULL: 1;
			$postdata['NorthAmerica'] = ($this->input->post('NorthAmerica',true) ?? 0) == 0 ? NULL: 1;
			$postdata['SouthAmerica'] = ($this->input->post('SouthAmerica',true) ?? 0) == 0 ? NULL: 1;
			$postdata['Oceania'] = ($this->input->post('Oceania',true) ?? 0) == 0 ? NULL: 1;
			$postdata['Antarctica'] = ($this->input->post('Antarctica',true) ?? 0) == 0 ? NULL: 1;
			$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
			$postdata['sat'] = $this->security->xss_clean($this->input->post('sats'));
			$postdata['orbit'] = $this->security->xss_clean($this->input->post('orbits'));
		} else { // Setting default values at first load of page
			$postdata['qsl'] = 1;
			$postdata['lotw'] = 1;
			$postdata['eqsl'] = NULL;
			$postdata['qrz'] = NULL;
			$postdata['clublog'] = NULL;
			$postdata['worked'] = 1;
			$postdata['confirmed'] = 1;
			$postdata['notworked'] = 1;
			$postdata['includedeleted'] = NULL;
			$postdata['Africa'] = 1;
			$postdata['Asia'] = 1;
			$postdata['Europe'] = 1;
			$postdata['NorthAmerica'] = 1;
			$postdata['SouthAmerica'] = 1;
			$postdata['Oceania'] = 1;
			$postdata['Antarctica'] = 1;
			$postdata['band'] = 'All';
			$postdata['mode'] = 'All';
			$postdata['sat'] = 'All';
			$postdata['orbit'] = 'All';
		}

		$dxcclist = $this->dxcc->fetchdxcc($postdata);
		if ($dxcclist[0]->adif == "0") {
			unset($dxcclist[0]);
		}
		$data['dxcc_array'] = $this->dxcc->get_dxcc_array($dxcclist, $bands, $postdata);
		$data['dxcc_summary'] = $this->dxcc->get_dxcc_summary($bands, $postdata);

		// Render Page
		$data['page_title'] = sprintf(__("Awards - %s"), __("DXCC"));
		$data['posted_band']=$postdata['band'];
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/dxcc/index');
		$this->load->view('interface_assets/footer');
	}

	public function waja ()	{
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/wajamap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/wajamap.js")),
			'assets/js/leaflet/L.Maidenhead.js',
		];

		$this->load->model('waja');
		$this->load->model('modes');
		$this->load->model('bands');

		$data['worked_bands'] = $this->bands->get_worked_bands('waja');
		$data['modes'] = $this->modes->active();

		if ($this->input->post('band') != NULL) {   			// Band is not set when page first loads.
			if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
				$bands = $data['worked_bands'];
			}
			else {
				$bands[] = $this->security->xss_clean($this->input->post('band'));
			}
		}
		else {
			$bands = $data['worked_bands'];
		}

		$data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

		if($this->input->method() === 'post') {
			$postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
			$postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
			$postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
			$postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
			$postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
			$postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
			$postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
			$postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
			$postdata['includedeleted'] = $this->security->xss_clean($this->input->post('includedeleted'));
			$postdata['Africa'] = $this->security->xss_clean($this->input->post('Africa'));
			$postdata['Asia'] = $this->security->xss_clean($this->input->post('Asia'));
			$postdata['Europe'] = $this->security->xss_clean($this->input->post('Europe'));
			$postdata['NorthAmerica'] = $this->security->xss_clean($this->input->post('NorthAmerica'));
			$postdata['SouthAmerica'] = $this->security->xss_clean($this->input->post('SouthAmerica'));
			$postdata['Oceania'] = $this->security->xss_clean($this->input->post('Oceania'));
			$postdata['Antarctica'] = $this->security->xss_clean($this->input->post('Antarctica'));
			$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
		}
		else { // Setting default values at first load of page
			$postdata['qsl'] = 1;
			$postdata['lotw'] = 1;
			$postdata['eqsl'] = 0;
			$postdata['qrz'] = 0;
			$postdata['clublog'] = 0;
			$postdata['worked'] = 1;
			$postdata['confirmed'] = 1;
			$postdata['notworked'] = 1;
			$postdata['includedeleted'] = 0;
			$postdata['Africa'] = 1;
			$postdata['Asia'] = 1;
			$postdata['Europe'] = 1;
			$postdata['NorthAmerica'] = 1;
			$postdata['SouthAmerica'] = 1;
			$postdata['Oceania'] = 1;
			$postdata['Antarctica'] = 1;
			$postdata['band'] = 'All';
			$postdata['mode'] = 'All';
		}

		$data['waja_array'] = $this->waja->get_waja_array($bands, $postdata);
		$data['waja_summary'] = $this->waja->get_waja_summary($bands, $postdata);

		// Render Page
		$data['page_title'] =__( "Awards - WAJA");
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/waja/index');
		$this->load->view('interface_assets/footer', $footerData);
	}

	public function jcc () {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/jcc.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/jcc.js")),
			'assets/js/sections/jccmap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/jccmap.js"))
		];

		$this->load->model('jcc_model');
		$this->load->model('modes');
		$this->load->model('bands');

		$data['worked_bands'] = $this->bands->get_worked_bands('jcc');
		$data['modes'] = $this->modes->active();

		if ($this->input->post('band') != NULL) {   			// Band is not set when page first loads.
			if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
				$bands = $data['worked_bands'];
			} else {
				$bands[] = $this->security->xss_clean($this->input->post('band'));
			}
		} else {
			$bands = $data['worked_bands'];
		}

		$data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

		if($this->input->method() === 'post') {
			$postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
			$postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
			$postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
			$postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
			$postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
			$postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
			$postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
			$postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
			$postdata['includedeleted'] = $this->security->xss_clean($this->input->post('includedeleted'));
			$postdata['Africa'] = $this->security->xss_clean($this->input->post('Africa'));
			$postdata['Asia'] = $this->security->xss_clean($this->input->post('Asia'));
			$postdata['Europe'] = $this->security->xss_clean($this->input->post('Europe'));
			$postdata['NorthAmerica'] = $this->security->xss_clean($this->input->post('NorthAmerica'));
			$postdata['SouthAmerica'] = $this->security->xss_clean($this->input->post('SouthAmerica'));
			$postdata['Oceania'] = $this->security->xss_clean($this->input->post('Oceania'));
			$postdata['Antarctica'] = $this->security->xss_clean($this->input->post('Antarctica'));
			$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
		} else { // Setting default values at first load of page
			$postdata['qsl'] = 1;
			$postdata['lotw'] = 1;
			$postdata['eqsl'] = 0;
			$postdata['qrz'] = 0;
			$postdata['clublog'] = 0;
			$postdata['worked'] = 1;
			$postdata['confirmed'] = 1;
			$postdata['notworked'] = 0;
			$postdata['includedeleted'] = 0;
			$postdata['Africa'] = 1;
			$postdata['Asia'] = 1;
			$postdata['Europe'] = 1;
			$postdata['NorthAmerica'] = 1;
			$postdata['SouthAmerica'] = 1;
			$postdata['Oceania'] = 1;
			$postdata['Antarctica'] = 1;
			$postdata['band'] = 'All';
			$postdata['mode'] = 'All';
		}

		$data['jcc_array'] = $this->jcc_model->get_jcc_array($bands, $postdata);
		$data['jcc_summary'] = $this->jcc_model->get_jcc_summary($bands, $postdata);

		// Render Page
		$data['page_title'] = sprintf(__("Awards - %s"), __("JCC"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/jcc/index');
		$this->load->view('interface_assets/footer', $footerData);
	}

	public function jcc_export() {
		$this->load->model('Jcc_model');
		$postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
		$postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
		$postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
		$postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
		$postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
		$postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
		$postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
		$postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
		$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
		$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

		$qsos = $this->Jcc_model->exportJcc($postdata);

		$fp = fopen( 'php://output', 'w' );
		$i=1;
		fputcsv($fp, array('No', 'Callsign', 'Date', 'Band', 'Mode', 'Remarks'), ';');
		foreach ($qsos as $qso) {
			fputcsv($fp, array($i, $qso['call'], $qso['date'], ($qso['prop_mode'] != null ? $qso['band'].' / '.$qso['prop_mode'] : $qso['band']), $qso['mode'], $qso['cnty'].' - '.$qso['jcc']), ';');
			$i++;
		}
		fclose($fp);
		return;
	}

	public function jcc_cities() {
		$this->load->model('Jcc_model');
		$data = $this->Jcc_model->jccCities();
		header('Content-Type: application/json');
		echo json_encode($data, JSON_PRETTY_PRINT);
	}


	public function vucc()	{
		$this->load->model('vucc');
		$this->load->model('bands');
		$data['worked_bands'] = $this->bands->get_worked_bands('vucc');

		$data['vucc_array'] = $this->vucc->get_vucc_array($data);

		// Render Page
		$data['page_title'] = sprintf(__("Awards - %s"), __("VUCC"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/vucc/index');
		$this->load->view('interface_assets/footer');
	}

	public function vucc_band(){
		$this->load->model('vucc');
		$band = str_replace('"', "", $this->security->xss_clean($this->input->get("Band")));
		$type = str_replace('"', "", $this->security->xss_clean($this->input->get("Type")));
		$data['vucc_array'] = $this->vucc->vucc_details($band, $type);
		$data['type'] = $type;

		// Render Page
		$data['page_title'] = "VUCC - " .$band . " Band";
		$data['filter'] = "band ".$band;
		$data['band'] = $band;
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/vucc/band');
		$this->load->view('interface_assets/footer');
	}

	public function vucc_details_ajax(){
		$this->load->model('logbook_model');

		$gridsquare = str_replace('"', "", $this->security->xss_clean($this->input->post("Gridsquare")));
		$band = str_replace('"', "", $this->security->xss_clean($this->input->post("Band")));
		$data['results'] = $this->logbook_model->vucc_qso_details($gridsquare, $band);

		// Render Page
		$data['page_title'] = __("Log View - VUCC");
		$data['filter'] = "vucc " . $gridsquare . " and band ".$band;
		$this->load->view('awards/details', $data);
	}

	/*
	 * Used to fetch QSOs from the logbook in the awards
	 */
	public function qso_details_ajax() {
		$this->load->model('logbook_model');

		$searchphrase = str_replace('"', "", $this->security->xss_clean($this->input->post("Searchphrase")));
		$band = str_replace('"', "", $this->security->xss_clean($this->input->post("Band")));
		$mode = str_replace('"', "", $this->security->xss_clean($this->input->post("Mode")));
		$sat = str_replace('"', "", $this->security->xss_clean($this->input->post("Sat")));
		$orbit = str_replace('"', "", $this->security->xss_clean($this->input->post("Orbit")));
		$propagation = str_replace('"', "", $this->security->xss_clean($this->input->post("Propagation")) ?? '');
		$type = $this->security->xss_clean($this->input->post('Type'));
		$qsl = $this->input->post('QSL') == null ? '' : $this->security->xss_clean($this->input->post('QSL'));
		$searchmode = $this->input->post('searchmode') == null ? '' : $this->security->xss_clean($this->input->post('searchmode'));
		$data['results'] = $this->logbook_model->qso_details($searchphrase, $band, $mode, $type, $qsl, $sat, $orbit, $searchmode, $propagation);

		// This is done because we have two different ways to get dxcc info in Wavelog. Once is using the name (in awards), and the other one is using the ADIF DXCC.
		// We replace the values to make it look a bit nicer
		if ($type == 'DXCC2') {
			$type = 'DXCC';
			$dxccname = $this->logbook_model->get_entity($searchphrase);
			$searchphrase = $dxccname['name'];
		}

		$qsltype = [];
		if (strpos($qsl, "Q") !== false) {
			$qsltype[] = "QSL";
		}
		if (strpos($qsl, "L") !== false) {
			$qsltype[] = "LoTW";
		}
		if (strpos($qsl, "E") !== false) {
			$qsltype[] = "eQSL";
		}
		if (strpos($qsl, "Z") !== false) {
			$qsltype[] = "QRZ.com";
		}
		if (strpos($qsl, "C") !== false) {
			$qsltype[] = "Clublog";
		}

		// Render Page
		$data['page_title'] = __("Log View")." - " . $type;
		$data['filter'] = $type." ".$searchphrase.__(" and band ").$band;
		if ($band == 'SAT') {
			if ($sat != 'All' && $sat != null) {
				$data['filter'] .= __(" and sat ").$sat;
			}
			if ($orbit != 'All' && $orbit != null) {
				$data['filter'] .= __(" and orbit type ").$orbit;
			}
		}
		if ($propagation != '' && $propagation != null) {
			$data['filter'] .= __(" and propagation ").$propagation;
		}
		if ($mode != null && strtolower($mode) != 'all') {
			$data['filter'] .= __(" and mode ").$mode;
		}
		if (!empty($qsltype)) {
			$data['filter'] .= __(" and ").implode('/', $qsltype);
		}
		$this->load->view('awards/details', $data);
	}

	/*
		Handles showing worked SOTAs
		Comment field - SOTA:#
	*/
	public function sota() {

		// Grab all worked sota stations
		$this->load->model('sota');
		$data['sota_all'] = $this->sota->get_all();

		// Render page
		$data['page_title'] = sprintf(__("Awards - %s"), __("SOTA"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/sota/index');
		$this->load->view('interface_assets/footer');
	}

	/*
		Handles showing worked WWFFs
		Comment field - WWFF:#
	*/
	public function wwff() {

		// Grab all worked wwff stations
		$this->load->model('wwff');
		$data['wwff_all'] = $this->wwff->get_all();

		// Render page
		$data['page_title'] = sprintf(__("Awards - %s"), __("WWFF"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/wwff/index');
		$this->load->view('interface_assets/footer');
	}

	/*
		Handles showing worked POTAs
		Comment field - POTA:#
	*/
	public function pota() {

		// Grab all worked pota stations
		$this->load->model('pota');
		$data['pota_all'] = $this->pota->get_all();

		// Render page
		$data['page_title'] = sprintf(__("Awards - %s"), __("POTA"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/pota/index');
		$this->load->view('interface_assets/footer');
	}

	public function cq() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/cqmap_geojson.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/cqmap_geojson.js")),
			'assets/js/sections/cqmap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/cqmap.js"))
		];

		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

        $this->load->model('cq');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('cq');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->input->post('band');
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
            $data['cq_array'] = $this->cq->get_cq_array($bands, $postdata, $location_list);
            $data['cq_summary'] = $this->cq->get_cq_summary($bands, $postdata, $location_list);
		} else {
            $location_list = null;
            $data['cq_array'] = null;
            $data['cq_summary'] = null;
        }

        // Render page
        $data['page_title'] = sprintf(__("Awards - %s"), __("CQ WAZ (Worked All Zones)"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/cq/index');
		$this->load->view('interface_assets/footer', $footerData);
	}

    public function was() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/wasmap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/wasmap.js")),
			'assets/js/leaflet/L.Maidenhead.js',
		];

        $this->load->model('was');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('was');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->security->xss_clean($this->input->post('band'));
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        $data['was_array'] = $this->was->get_was_array($bands, $postdata);
        $data['was_summary'] = $this->was->get_was_summary($bands, $postdata);

        // Render Page
        $data['page_title'] = sprintf(__("Awards - %s"), __("Worked All States (WAS)"));;
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/was/index');
        $this->load->view('interface_assets/footer', $footerData);
    }

	public function rac() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/racmap_geojson.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/racmap_geojson.js")),
			'assets/js/sections/racmap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/racmap.js")),
			'assets/js/leaflet/L.Maidenhead.js',
		];

        $this->load->model('rac');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('rac');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->security->xss_clean($this->input->post('band'));
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        $data['rac_array'] = $this->rac->get_rac_array($bands, $postdata);
        $data['rac_summary'] = $this->rac->get_rac_summary($bands, $postdata);

        // Render Page
        $data['page_title'] = sprintf(__("Awards - %s"), __("RAC"));
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/rac/index');
        $this->load->view('interface_assets/footer', $footerData);
    }

    public function helvetia() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/helvetiamap_geojson.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/helvetiamap_geojson.js")),
			'assets/js/sections/helvetiamap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/helvetiamap.js")),
			'assets/js/leaflet/L.Maidenhead.js',
		];

        $this->load->model('helvetia_model');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('helvetia');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->security->xss_clean($this->input->post('band'));
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        $data['helvetia_array'] = $this->helvetia_model->get_helvetia_array($bands, $postdata);
        $data['helvetia_summary'] = $this->helvetia_model->get_helvetia_summary($bands, $postdata);

        // Render Page
        $data['page_title'] =sprintf(__("Awards - %s"), __("H26"));
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/helvetia/index');
        $this->load->view('interface_assets/footer', $footerData);
    }

    public function iota () {
	    $this->load->model('iota');
	    $this->load->model('modes');
	    $this->load->model('bands');
		$this->load->model('logbooks_model');

		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

		if (!$logbooks_locations_array) {
			return null;
		}

		$location_list = "'".implode("','",$logbooks_locations_array)."'";

	    $data['worked_bands'] = $this->bands->get_worked_bands('iota'); // Used in the view for band select

	    if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
		    if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
			    $bands = $data['worked_bands'];
		    } else {
			    $bands[] = $this->security->xss_clean($this->input->post('band'));
		    }
	    } else {
		    $bands = $data['worked_bands'];
	    }

	    $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view
	    $data['modes'] = $this->modes->active(); // Used in the view for mode select

	    if($this->input->method() === 'post') {
		    $postdata['qsl'] = ($this->input->post('qsl',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['lotw'] = ($this->input->post('lotw',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['eqsl'] = ($this->input->post('eqsl',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['qrz'] = ($this->input->post('qrz',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['clublog'] = ($this->input->post('clublog',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['worked'] = ($this->input->post('worked',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['confirmed'] = ($this->input->post('confirmed',true) ?? 0)  == 0 ? NULL: 1;
		    $postdata['notworked'] = ($this->input->post('notworked',true) ?? 0)  == 0 ? NULL: 1;

		    $postdata['includedeleted'] = ($this->input->post('includedeleted',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['Africa'] = ($this->input->post('Africa',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['Asia'] = ($this->input->post('Asia',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['Europe'] = ($this->input->post('Europe',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['NorthAmerica'] = ($this->input->post('NorthAmerica',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['SouthAmerica'] = ($this->input->post('SouthAmerica',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['Oceania'] = ($this->input->post('Oceania',true) ?? 0) == 0 ? NULL: 1;
		    $postdata['Antarctica'] = ($this->input->post('Antarctica',true) ?? 0) == 0 ? NULL: 1;

		    $postdata['band'] = $this->security->xss_clean($this->input->post('band')) ?? NULL;
		    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode')) ?? NULL;
	    } else { // Setting default values at first load of page
		    $postdata['qsl'] = 1;
		    $postdata['lotw'] = 1;
		    $postdata['eqsl'] = NULL;
		    $postdata['qrz'] = NULL;
		    $postdata['clublog'] = NULL;
		    $postdata['worked'] = 1;
		    $postdata['confirmed'] = 1;
		    $postdata['notworked'] = 1;
		    $postdata['includedeleted'] = NULL;
		    $postdata['Africa'] = 1;
		    $postdata['Asia'] = 1;
		    $postdata['Europe'] = 1;
		    $postdata['NorthAmerica'] = 1;
		    $postdata['SouthAmerica'] = 1;
		    $postdata['Oceania'] = 1;
		    $postdata['Antarctica'] = 1;
		    $postdata['band'] = 'All';
		    $postdata['mode'] = 'All';
	    }

	    $iotalist = $this->iota->fetchIota($postdata, $location_list);
	    $data['iota_array'] = $this->iota->get_iota_array($iotalist, $bands, $postdata, $location_list);
	    $data['iota_summary'] = $this->iota->get_iota_summary($bands, $postdata, $location_list);
	    $data['posted_band']=$postdata['band'];

	    // Render Page
	    $data['page_title'] = sprintf(__("Awards - %s"), __("IOTA (Island On The Air)"));
	    $this->load->view('interface_assets/header', $data);
	    $this->load->view('awards/iota/index');
	    $this->load->view('interface_assets/footer');
    }

    public function counties()	{
        $this->load->model('counties');
        $data['counties_array'] = $this->counties->get_counties_array();

        // Render Page
        $data['page_title'] = sprintf(__("Awards - %s"), __("US Counties"));
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/counties/index');
        $this->load->view('interface_assets/footer');
    }

    public function counties_details() {
        $this->load->model('counties');
        $state = str_replace('"', "", $this->security->xss_clean($this->input->get("State")));
        $type = str_replace('"', "", $this->security->xss_clean($this->input->get("Type")));
        $data['counties_array'] = $this->counties->counties_details($state, $type);
        $data['type'] = $type;

        // Render Page
        $data['page_title'] = __("US Counties");
        $data['filter'] = $type . " counties in state ".$state;
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/counties/details');
        $this->load->view('interface_assets/footer');
    }

    public function counties_details_ajax(){
        $this->load->model('logbook_model');

        $state = str_replace('"', "", $this->security->xss_clean($this->input->post("State")));
        $county = str_replace('"', "", $this->security->xss_clean($this->input->post("County")));
        $data['results'] = $this->logbook_model->county_qso_details($state, $county);

        // Render Page
        $data['page_title'] = __("Log View - Counties");
        $data['filter'] = "county " . $state;
        $this->load->view('awards/details', $data);
    }

    public function gridmaster($dxcc) {
      $dxcc = $this->security->xss_clean($dxcc);
      $data['page_title'] = __("Awards - ").strtoupper($dxcc)." Gridmaster";

      $this->load->model('bands');
      $this->load->model('gridmap_model');
      $this->load->model('stations');

      $data['homegrid']= explode(',', $this->stations->find_gridsquare());

      $data['modes'] = $this->gridmap_model->get_worked_modes();
      $data['bands']= $this->bands->get_worked_bands();
      $data['sats_available']= $this->bands->get_worked_sats();

      $data['layer']= $this->optionslib->get_option('option_map_tile_server');

      $data['attribution']= $this->optionslib->get_option('option_map_tile_server_copyright');

      $data['gridsquares_gridsquares']= __("Gridsquares");
      $data['gridsquares_gridsquares_worked']= __("Gridsquares worked");
      $data['gridsquares_gridsquares_lotw']= __("Gridsquares confirmed on LoTW");
      $data['gridsquares_gridsquares_paper']= __("Gridsquares confirmed by paper QSL");

      $indexData['dxcc'] = $dxcc;

      $footerData = [];
      $footerData['scripts']= [
         'assets/js/leaflet/geocoding.js',
         'assets/js/leaflet/L.MaidenheadColouredGridmasterMap.js',
         'assets/js/sections/gridmaster.js'
      ];

      $this->load->view('interface_assets/header',$data);
      $this->load->view('awards/gridmaster/index',$indexData);
      $this->load->view('interface_assets/footer',$footerData);
    }

	public function ffma() {
		$data['page_title'] = sprintf(__("Awards - %s"), __("Fred Fish Memorial Award (FFMA)"));

		$this->load->model('bands');
		$this->load->model('ffma_model');
		$this->load->model('stations');

		$data['homegrid']= explode(',', $this->stations->find_gridsquare());

		$data['layer']= $this->optionslib->get_option('option_map_tile_server');

		$data['attribution']= $this->optionslib->get_option('option_map_tile_server_copyright');

		$data['gridsquares_gridsquares']= __("Gridsquares");
		$data['gridsquares_gridsquares_worked']= __("Gridsquares worked");
		$data['gridsquares_gridsquares_lotw']= __("Gridsquares confirmed on LoTW");
		$data['gridsquares_gridsquares_paper']= __("Gridsquares confirmed by paper QSL");
		$data['grid_count'] = $this->ffma_model->get_grid_count();
		$data['grids'] = $this->ffma_model->get_grids();

		$footerData = [];
		$footerData['scripts']= [
			'assets/js/leaflet/geocoding.js',
			'assets/js/leaflet/L.MaidenheadColouredGridmasterMap.js',
			'assets/js/sections/ffma.js'
		];

		$this->load->view('interface_assets/header',$data);
		$this->load->view('awards/ffma/index');
		$this->load->view('interface_assets/footer',$footerData);
	}

	public function getFfmaGridsjs() {
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$location_list = "'".implode("','",$logbooks_locations_array)."'";
		$this->load->model('ffma_model');

		$array_grid_4char = array();
		$array_grid_4char_lotw = array();
		$array_grid_4char_paper = array();

		$grid_4char = "";
		$grid_4char_lotw = "";

		$query = $this->ffma_model->get_lotw($location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) 	{
				$grid_4char_lotw = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_4char_lotw, $array_grid_4char_lotw)){
					array_push($array_grid_4char_lotw, $grid_4char_lotw);
				}
			}
		}

		$query = $this->ffma_model->get_paper($location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) 	{
				$grid_4char_paper = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_4char_paper, $array_grid_4char_paper)){
					array_push($array_grid_4char_paper, $grid_4char_paper);
				}
			}
		}

		$query = $this->ffma_model->get_worked($location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$grid_four = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_four, $array_grid_4char)){
					array_push($array_grid_4char, $grid_four);
				}
			}
		}

		$vucc_grids = $this->ffma_model->get_vucc_lotw($location_list);
		foreach($vucc_grids as $key) {
			$grid_four_lotw = strtoupper(substr($key,0,4));
			if(!in_array($grid_four_lotw, $array_grid_4char_lotw)){
				array_push($array_grid_4char_lotw, $grid_four_lotw);
			}
		}

		$vucc_grids = $this->ffma_model->get_vucc_paper($location_list);
		foreach($vucc_grids as $key) {
			$grid_four_paper = strtoupper(substr($key,0,4));
			if(!in_array($grid_four_paper, $array_grid_4char_paper)){
				array_push($array_grid_4char_paper, $grid_four_paper);
			}
		}

		$vucc_grids = $this->ffma_model->get_vucc_worked($location_list);
		foreach($vucc_grids as $key) {
			$grid_four = strtoupper(substr($key,0,4));
			if(!in_array($grid_four, $array_grid_4char)){
				array_push($array_grid_4char, $grid_four);
			}
		}

		$data['grid_4char_lotw'] = ($array_grid_4char_lotw);
		$data['grid_4char_paper'] = ($array_grid_4char_paper);
		$data['grid_4char'] = ($array_grid_4char);
		$data['grid_count'] = $this->ffma_model->get_grid_count();
		$data['grids'] = $this->ffma_model->get_grids();

		header('Content-Type: application/json');
		echo json_encode($data);
	}

	public function getGridmasterGridsjs($dxcc) {
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$location_list = "'".implode("','",$logbooks_locations_array)."'";
		$this->load->model('gridmaster_model');

		$dxcc = $this->security->xss_clean($dxcc);

		$array_grid_4char = array();
		$array_grid_4char_lotw = array();
		$array_grid_4char_paper = array();

		$grid_4char = "";
		$grid_4char_lotw = "";

		$query = $this->gridmaster_model->get_lotw($dxcc, $location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) 	{
				$grid_4char_lotw = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_4char_lotw, $array_grid_4char_lotw)){
					array_push($array_grid_4char_lotw, $grid_4char_lotw);
				}
			}
		}

		$query = $this->gridmaster_model->get_paper($dxcc, $location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) 	{
				$grid_4char_paper = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_4char_paper, $array_grid_4char_paper)){
					array_push($array_grid_4char_paper, $grid_4char_paper);
				}
			}
		}

		$query = $this->gridmaster_model->get_worked($dxcc, $location_list);
		if ($query && $query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$grid_four = strtoupper(substr($row->GRID_SQUARES,0,4));
				if(!in_array($grid_four, $array_grid_4char)){
					array_push($array_grid_4char, $grid_four);
				}
			}
		}

		$vucc_grids = $this->gridmaster_model->get_vucc_lotw($dxcc, $location_list);
		foreach($vucc_grids as $key) {
			$grid_four_lotw = strtoupper(substr($key,0,4));
			if(!in_array($grid_four_lotw, $array_grid_4char_lotw)){
				array_push($array_grid_4char_lotw, $grid_four_lotw);
			}
		}

		$vucc_grids = $this->gridmaster_model->get_vucc_paper($dxcc, $location_list);
		foreach($vucc_grids as $key) {
			$grid_four_paper = strtoupper(substr($key,0,4));
			if(!in_array($grid_four_paper, $array_grid_4char_paper)){
				array_push($array_grid_4char_paper, $grid_four_paper);
			}
		}

		$vucc_grids = $this->gridmaster_model->get_vucc_worked($dxcc, $location_list);
		foreach($vucc_grids as $key) {
			$grid_four = strtoupper(substr($key,0,4));
			if(!in_array($grid_four, $array_grid_4char)){
				array_push($array_grid_4char, $grid_four);
			}
		}

		$data['grid_4char_lotw'] = ($array_grid_4char_lotw);
		$data['grid_4char_paper'] = ($array_grid_4char_paper);
		$data['grid_4char'] = ($array_grid_4char);
		$data['grid_count'] = $this->gridmaster_model->get_grid_count($dxcc);
		$data['grids'] = $this->gridmaster_model->get_grids($dxcc);
		$data['lat'] = $this->gridmaster_model->get_lat($dxcc);
		$data['lon'] = $this->gridmaster_model->get_lon($dxcc);
		$data['zoom'] = $this->gridmaster_model->get_zoom($dxcc);

		header('Content-Type: application/json');
		echo json_encode($data);
	}

	/*
		Handles showing worked Sigs
		Adif fields: my_sig
	*/
	public function sig() {
		// Grab all worked sig stations
		$this->load->model('sig');

		$data['sig_types'] = $this->sig->get_all_sig_types();

		// Render page
		$data['page_title'] = sprintf(__("Awards - %s"), __("SIG"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/sig/index');
		$this->load->view('interface_assets/footer');
	}

	/*
	Handles showing worked Sigs
	*/
	public function sig_details() {

		// Grab all worked sig stations
		$this->load->model('sig');
		$type = str_replace('"', "", $this->security->xss_clean($this->input->get("type")));
		$data['sig_all'] = $this->sig->get_all($type);
		$data['type'] = $type;

		// Render page
		$data['page_title'] = __("Awards - SIG - ") . $type;
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/sig/qso_list');
		$this->load->view('interface_assets/footer');
	}

	/*
	Handles exporting SIGS to ADIF
	*/
	public function sigexportadif() {
		// Set memory limit to unlimited to allow heavy usage
		ini_set('memory_limit', '-1');

		$this->load->model('adif_data');

		$type = $this->security->xss_clean($this->uri->segment(3));
		$data['qsos'] = $this->adif_data->sig_all($type);

		$this->load->view('adif/data/exportall', $data);
	}

    /*
        function was_map

        This displays the WAS map and requires the $band_type and $mode_type
    */
    public function was_map() {
		$stateString = 'AK,AL,AR,AZ,CA,CO,CT,DE,FL,GA,HI,IA,ID,IL,IN,KS,KY,LA,MA,MD,ME,MI,MN,MO,MS,MT,NC,ND,NE,NH,NJ,NM,NV,NY,OH,OK,OR,PA,RI,SC,SD,TN,TX,UT,VA,VT,WA,WI,WV,WY';
		$wasArray = explode(',', $stateString);

        $this->load->model('was');

		$bands[] = $this->security->xss_clean($this->input->post('band'));

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
        $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        $was_array = $this->was->get_was_array($bands, $postdata);

        $states = array();

		foreach ($wasArray as $state) {                  	 // Generating array for use in the table
            $states[$state] = '-';                   // Inits each state's count
        }


        foreach ($was_array as $was => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $states[$was] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $states[$was] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $states[$was] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($states);
    }

	public function wap() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/wapmap_geojson.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/wapmap_geojson.js")),
			'assets/js/sections/wapmap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/wapmap.js")),
			'assets/js/leaflet/L.Maidenhead.js',
		];

        $this->load->model('wap');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('wap');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->security->xss_clean($this->input->post('band'));
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        $data['wap_array'] = $this->wap->get_wap_array($bands, $postdata);
        $data['wap_summary'] = $this->wap->get_wap_summary($bands, $postdata);

        // Render Page
        $data['page_title'] = sprintf(__("Awards - %s"), __("WAP"));
        $this->load->view('interface_assets/header', $data);
        $this->load->view('awards/wap/index');
        $this->load->view('interface_assets/footer', $footerData);
    }

	/*
        function WAP_map

        This displays the WAP Worked All The Netherlands Provinces map and requires the $band_type and $mode_type
    */
    public function wap_map() {
		$stateString = 'DR,FL,FR,GD,GR,LB,NB,NH,OV,UT,ZH,ZL';
		$wapArray = explode(',', $stateString);

        $this->load->model('wap');

		$bands[] = $this->security->xss_clean($this->input->post('band'));

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
        $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        $wap_array = $this->wap->get_wap_array($bands, $postdata);

        $states = array();

		foreach ($wapArray as $state) {             // Generating array for use in the table
            $states[$state] = '-';                   // Inits each state's count
        }


        foreach ($wap_array as $was => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $states[$was] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $states[$was] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $states[$was] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($states);
    }

	/*
        function RAC_map

        This displays the RAC map and requires the $band_type and $mode_type
    */
    public function rac_map() {
		$stateString = 'AB,BC,MB,NB,NL,NT,NS,NU,ON,PE,QC,SK,YT';
		$racArray = explode(',', $stateString);

        $this->load->model('rac');

		$bands[] = $this->security->xss_clean($this->input->post('band'));

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
        $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        $rac_array = $this->rac->get_rac_array($bands, $postdata);

        $states = array();

		foreach ($racArray as $state) {                  	 // Generating array for use in the table
            $states[$state] = '-';                   // Inits each state's count
        }


        foreach ($rac_array as $was => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $states[$was] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $states[$was] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $states[$was] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($states);
    }

    /*
        function H26_map

        This displays the H26 map and requires the $band_type and $mode_type
    */
    public function helvetia_map() {
		$stateString = 'AG,AI,AR,BE,BL,BS,FR,GE,GL,GR,JU,LU,NE,NW,OW,SG,SH,SO,SZ,TG,TI,UR,VD,VS,ZG,ZH';
		$helvetiaArray = explode(',', $stateString);

        $this->load->model('helvetia_model');

		$bands[] = $this->security->xss_clean($this->input->post('band'));

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
        $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        $helvetia_array = $this->helvetia_model->get_helvetia_array($bands, $postdata);

        $states = array();

		foreach ($helvetiaArray as $state) {                  	 // Generating array for use in the table
            $states[$state] = '-';                   // Inits each state's count
        }


        foreach ($helvetia_array as $was => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $states[$was] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $states[$was] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $states[$was] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($states);
    }

    /*
        function cq_map
        This displays the CQ Zone map and requires the $band_type and $mode_type
    */
    public function cq_map() {
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

        $this->load->model('cq');

        $bands[] = $this->input->post('band');

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['clublog'] = $this->input->post('clublog') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
		$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
            $cq_array = $this->cq->get_cq_array($bands, $postdata, $location_list);
		} else {
            $location_list = null;
            $cq_array = null;
        }

		$zones = array();

        foreach ($cq_array as $cq => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $zones[] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $zones[] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $zones[] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($zones);
    }

	/*
        function waja_map
    */
    public function waja_map() {
		$prefectureString = '01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47';
		$wajaArray = explode(',', $prefectureString);

        $this->load->model('waja');
        $this->load->model('bands');

        $bands[] = $this->security->xss_clean($this->input->post('band'));

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['clublog'] = $this->input->post('clublog') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
        $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));


		$waja_array = $this->waja->get_waja_array($bands, $postdata);

		$prefectures = array();

		foreach ($wajaArray as $state) {                  	 // Generating array for use in the table
            $prefectures[$state] = '-';                   // Inits each state's count
        }


        foreach ($waja_array as $waja => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $prefectures[$waja] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $prefectures[$waja] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $prefectures[$waja] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($prefectures);
    }

    /*
        function dxcc_map
        This displays the DXCC map
    */
    public function dxcc_map() {
	    $this->load->model('dxcc');
	    $this->load->model('bands');

	    $bands[] = $this->security->xss_clean($this->input->post('band'));

	    $postdata['qsl'] = ($this->input->post('qsl',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['lotw'] = ($this->input->post('lotw',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['eqsl'] = ($this->input->post('eqsl',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['qrz'] = ($this->input->post('qrz',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['clublog'] = ($this->input->post('clublog',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['worked'] = ($this->input->post('worked',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['confirmed'] = ($this->input->post('confirmed',true) ?? 0)  == 0 ? NULL: 1;
	    $postdata['notworked'] = ($this->input->post('notworked',true) ?? 0)  == 0 ? NULL: 1;

	    $postdata['includedeleted'] = ($this->input->post('includedeleted',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Africa'] = ($this->input->post('Africa',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Asia'] = ($this->input->post('Asia',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Europe'] = ($this->input->post('Europe',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['NorthAmerica'] = ($this->input->post('NorthAmerica',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['SouthAmerica'] = ($this->input->post('SouthAmerica',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Oceania'] = ($this->input->post('Oceania',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Antarctica'] = ($this->input->post('Antarctica',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
	    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
	    $postdata['sat'] = $this->security->xss_clean($this->input->post('sat'));
	    $postdata['orbit'] = $this->security->xss_clean($this->input->post('orbit'));


	    $dxcclist = $this->dxcc->fetchdxcc($postdata);
	    if ($dxcclist[0]->adif == "0") {
		    unset($dxcclist[0]);
	    }

	    $dxcc_array = $this->dxcc->get_dxcc_array($dxcclist, $bands, $postdata);

	    $i = 0;

	    foreach ($dxcclist as $dxcc) {
		    $newdxcc[$i]['adif'] = $dxcc->adif;
		    $newdxcc[$i]['prefix'] = $dxcc->prefix;
		    $newdxcc[$i]['name'] = ucwords(strtolower($dxcc->name), "- (/");
		    if ($dxcc->Enddate!=null) {
			    $newdxcc[$i]['name'] .= ' (deleted)';
		    }
		    $newdxcc[$i]['lat'] = $dxcc->lat;
		    $newdxcc[$i]['long'] = $dxcc->long;
		    $newdxcc[$i++]['status'] = isset($dxcc_array[$dxcc->adif]) ? $this->returnStatus($dxcc_array[$dxcc->adif]) : 'x';
	    }

	    header('Content-Type: application/json');
	    echo json_encode($newdxcc);
    }

    /*
        function jcc_map
        This displays the DXCC map
    */
    public function jcc_map() {
	    $this->load->model('jcc_model');
	    $this->load->model('bands');

	    $bands[] = $this->security->xss_clean($this->input->post('band'));

	    $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
	    $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
	    $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
	    $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
	    $postdata['clublog'] = $this->input->post('clublog') == 0 ? NULL: 1;
	    $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
	    $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
	    $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
	    $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
	    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

	    $jcc_wkd = $this->jcc_model->fetch_jcc_wkd($postdata);
	    $jcc_cnfm = $this->jcc_model->fetch_jcc_cnfm($postdata);

	    $jccs = [];
	    foreach ($jcc_wkd as $jcc) {
		    $jccs[$jcc->COL_CNTY] = array(1, 0);
	    }
	    foreach ($jcc_cnfm as $jcc) {
		    $jccs[$jcc->COL_CNTY][1] = 1;
	    }

	    header('Content-Type: application/json');
	    echo json_encode($jccs);
    }

    /*
        function iota
        This displays the IOTA map
    */
    public function iota_map() {
	    $this->load->model('iota');
	    $this->load->model('bands');
	    $this->load->model('logbooks_model');
	    $logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

	    if (!$logbooks_locations_array) {
		    return null;
	    }

	    $location_list = "'".implode("','",$logbooks_locations_array)."'";
	    $bands[] = $this->security->xss_clean($this->input->post('band'));

	    $postdata['qsl'] = ($this->input->post('qsl',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['lotw'] = ($this->input->post('lotw',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['eqsl'] = ($this->input->post('eqsl',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['qrz'] = ($this->input->post('qrz',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['clublog'] = ($this->input->post('clublog',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['worked'] = ($this->input->post('worked',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['confirmed'] = ($this->input->post('confirmed',true) ?? 0)  == 0 ? NULL: 1;
	    $postdata['notworked'] = ($this->input->post('notworked',true) ?? 0)  == 0 ? NULL: 1;

	    $postdata['includedeleted'] = ($this->input->post('includedeleted',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Africa'] = ($this->input->post('Africa',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Asia'] = ($this->input->post('Asia',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Europe'] = ($this->input->post('Europe',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['NorthAmerica'] = ($this->input->post('NorthAmerica',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['SouthAmerica'] = ($this->input->post('SouthAmerica',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Oceania'] = ($this->input->post('Oceania',true) ?? 0) == 0 ? NULL: 1;
	    $postdata['Antarctica'] = ($this->input->post('Antarctica',true) ?? 0) == 0 ? NULL: 1;

	    $postdata['band'] = $this->security->xss_clean($this->input->post('band')) ?? NULL;
	    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode')) ?? NULL;

	    $iotalist = $this->iota->fetchIota($postdata, $location_list);

	    $iota_array = $this->iota->get_iota_array($iotalist, $bands, $postdata, $location_list);

	    $i = 0;

	    foreach ($iotalist as $iota) {
		    $newiota[$i]['tag'] = $iota->tag;
		    $newiota[$i]['prefix'] = $iota->prefix;
		    $newiota[$i]['name'] = ucwords(strtolower($iota->name), "- (/");
		    if ($iota->status == 'D') {
			    $newiota[$i]['name'] .= ' (deleted)';
		    }
		    $newiota[$i]['lat1'] = $iota->lat1;
		    $newiota[$i]['lon1'] = $iota->lon1;
		    $newiota[$i]['lat2'] = $iota->lat2;
		    $newiota[$i]['lon2'] = $iota->lon2;
		    $newiota[$i++]['status'] = isset($iota_array[$iota->tag]) ? $this->returnStatus($iota_array[$iota->tag]) : 'x';
	    }

	    header('Content-Type: application/json');
	    echo json_encode($newiota);
    }

    function returnStatus($string) {
        foreach ($string  as $key) {
            if($key != "") {
                if (strpos($key, '>W<') !== false) {
                    return 'W';
                }
                if (strpos($key, '>C<') !== false) {
                    return 'C';
                }
                if ($key == '-') {
                    return '-';
                }
            }
        }
    }

    public function wab() {
	    $this->load->model('bands');
	    $this->load->model('gridmap_model');
	    $this->load->model('stations');

	    $data['modes'] = $this->gridmap_model->get_worked_modes();
	    $data['bands'] = $this->bands->get_worked_bands();
	    $data['orbits'] = $this->bands->get_worked_orbits();
	    $data['sats_available'] = $this->bands->get_worked_sats();

	    $data['user_default_band'] = $this->session->userdata('user_default_band');
	    $data['user_default_confirmation'] = $this->session->userdata('user_default_confirmation');

	    $footerData = [];
	    $footerData['scripts'] = [
		    'assets/js/sections/wab.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/wab.js"))
	    ];

	    // Render page
	    $data['page_title'] = sprintf(__("Awards - %s"), "Worked All Britain");
	    $this->load->view('interface_assets/header', $data);
	    $this->load->view('awards/wab/index');
	    $this->load->view('interface_assets/footer', $footerData);
    }

    public function wab_map() {
	    $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
	    $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
	    $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
	    $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
	    $postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
	    $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
	    $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
	    $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
	    $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
	    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
	    $postdata['sat'] = $this->security->xss_clean($this->input->post('sat'));
	    $postdata['orbit'] = $this->security->xss_clean($this->input->post('orbit'));

	    $this->load->model('logbooks_model');
	    $logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

	    $this->load->model('wab');

	    if ($logbooks_locations_array) {
		    $location_list = "'".implode("','",$logbooks_locations_array)."'";
		    $wab_array = $this->wab->get_wab_array($location_list, $postdata);
	    } else {
		    $location_list = null;
		    $wab_array = null;
	    }

	    header('Content-Type: application/json');
	    echo json_encode($wab_array);
    }

    public function wab_list() {
	    $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
	    $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
	    $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
	    $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
	    $postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
	    $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
	    $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
	    $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
	    $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
	    $postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
	    $postdata['sat'] = $this->security->xss_clean($this->input->post('sat'));
	    $postdata['orbit'] = $this->security->xss_clean($this->input->post('orbit'));

	    $this->load->model('logbooks_model');
	    $logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

	    $this->load->model('wab');

	    if ($logbooks_locations_array) {
		    $location_list = "'".implode("','",$logbooks_locations_array)."'";
		    $wab_array = $this->wab->get_wab_list($location_list, $postdata);
	    } else {
		    $location_list = null;
		    $wab_array = null;
	    }

	    $data['wab_array'] = $wab_array;
	    $data['postdata']['band'] = $postdata['band'];
	    $data['postdata']['mode'] = $postdata['mode'];
	    $data['postdata']['sat'] = $postdata['sat'];
	    $data['postdata']['orbit'] = $postdata['orbit'];

	    $this->load->view('awards/wab/list', $data);
    }

	public function itu() {
		$footerData = [];
		$footerData['scripts'] = [
			'assets/js/sections/itumap_geojson.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/itumap_geojson.js")),
			'assets/js/sections/itumap.js?' . filemtime(realpath(__DIR__ . "/../../assets/js/sections/itumap.js"))
		];

		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

        $this->load->model('itu');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands('cq');
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->input->post('band');
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

	if($this->input->method() === 'post') {
		$postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
		$postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
		$postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
		$postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
		$postdata['clublog'] = $this->security->xss_clean($this->input->post('clublog'));
		$postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
		$postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
		$postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
		$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
		$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
	}
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['clublog'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
        }

        if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
            $data['itu_array'] = $this->itu->get_itu_array($bands, $postdata, $location_list);
            $data['itu_summary'] = $this->itu->get_itu_summary($bands, $postdata, $location_list);
		} else {
            $location_list = null;
            $data['itu_array'] = null;
            $data['itu_summary'] = null;
        }

        // Render page
        $data['page_title'] = sprintf(__("Awards - %s"), __("ITU Zones"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/itu/index');
		$this->load->view('interface_assets/footer', $footerData);
	}

	 /*
        function itu_map
        This displays the ITU Zone map and requires the $band_type and $mode_type
    */
    public function itu_map() {
        $this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

        $this->load->model('itu');

        $bands[] = $this->input->post('band');

        $postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
        $postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
        $postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
        $postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
        $postdata['clublog'] = $this->input->post('clublog') == 0 ? NULL: 1;
        $postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
        $postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
        $postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;
        $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
		$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));

        if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
            $itu_array = $this->itu->get_itu_array($bands, $postdata, $location_list);
		} else {
            $location_list = null;
            $itu_array = null;
        }

		$zones = array();

        foreach ($itu_array as $itu => $value) {
            foreach ($value  as $key) {
                if($key != "") {
                    if (strpos($key, '>W<') !== false) {
                        $zones[] = 'W';
                        break;
                    }
                    if (strpos($key, '>C<') !== false) {
                        $zones[] = 'C';
                        break;
                    }
                    if (strpos($key, '-') !== false) {
                        $zones[] = '-';
                        break;
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($zones);
    }

	public function wac() {
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));

        $this->load->model('wac');
		$this->load->model('modes');
        $this->load->model('bands');

        $data['worked_bands'] = $this->bands->get_worked_bands();
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

		$data['orbits'] = $this->bands->get_worked_orbits();
		$data['sats_available'] = $this->bands->get_worked_sats();
		$data['user_default_band'] = $this->session->userdata('user_default_band');

        if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->input->post('band');
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        if($this->input->method() === 'post') {
            $postdata['qsl'] = $this->security->xss_clean($this->input->post('qsl'));
            $postdata['lotw'] = $this->security->xss_clean($this->input->post('lotw'));
            $postdata['eqsl'] = $this->security->xss_clean($this->input->post('eqsl'));
            $postdata['qrz'] = $this->security->xss_clean($this->input->post('qrz'));
            $postdata['worked'] = $this->security->xss_clean($this->input->post('worked'));
            $postdata['confirmed'] = $this->security->xss_clean($this->input->post('confirmed'));
            $postdata['notworked'] = $this->security->xss_clean($this->input->post('notworked'));
            $postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
			$postdata['sat'] = $this->security->xss_clean($this->input->post('sats'));
			$postdata['orbit'] = $this->security->xss_clean($this->input->post('orbits'));
        }
        else { // Setting default values at first load of page
            $postdata['qsl'] = 1;
            $postdata['lotw'] = 1;
            $postdata['eqsl'] = 0;
            $postdata['qrz'] = 0;
            $postdata['worked'] = 1;
            $postdata['confirmed'] = 1;
            $postdata['notworked'] = 1;
            $postdata['band'] = 'All';
			$postdata['mode'] = 'All';
			$postdata['sat'] = 'All';
			$postdata['orbit'] = 'All';
        }

        if ($logbooks_locations_array) {
			$location_list = "'".implode("','",$logbooks_locations_array)."'";
            $data['wac_array'] = $this->wac->get_wac_array($bands, $postdata, $location_list);
            $data['wac_summary'] = $this->wac->get_wac_summary($bands, $postdata, $location_list);
		} else {
            $location_list = null;
            $data['wac_array'] = null;
            $data['wac_summary'] = null;
        }

        // Render page
        $data['page_title'] = sprintf(__("Awards - %s"), __("Worked All Continents (WAC)"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/wac/index');
		$this->load->view('interface_assets/footer');
	}

	public function wae () {
		$this->load->model('wae');
		$this->load->model('modes');
		$this->load->model('bands');

		$data['orbits'] = $this->bands->get_worked_orbits();
		$data['sats_available'] = $this->bands->get_worked_sats();
		$data['user_default_band'] = $this->session->userdata('user_default_band');

		$data['worked_bands'] = $this->bands->get_worked_bands('dxcc'); // Used in the view for band select
		$data['modes'] = $this->modes->active(); // Used in the view for mode select

		if ($this->input->post('band') != NULL) {   // Band is not set when page first loads.
			if ($this->input->post('band') == 'All') {         // Did the user specify a band? If not, use all bands
				$bands = $data['worked_bands'];
			} else {
				$bands[] = $this->security->xss_clean($this->input->post('band'));
			}
		} else {
			$bands = $data['worked_bands'];
		}

		$data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

		if($this->input->method() === 'post') {
			$postdata['qsl'] = $this->input->post('qsl') == 0 ? NULL: 1;
			$postdata['lotw'] = $this->input->post('lotw') == 0 ? NULL: 1;
			$postdata['eqsl'] = $this->input->post('eqsl') == 0 ? NULL: 1;
			$postdata['qrz'] = $this->input->post('qrz') == 0 ? NULL: 1;
			$postdata['clublog'] = $this->input->post('clublog') == 0 ? NULL: 1;
			$postdata['worked'] = $this->input->post('worked') == 0 ? NULL: 1;
			$postdata['confirmed'] = $this->input->post('confirmed')  == 0 ? NULL: 1;
			$postdata['notworked'] = $this->input->post('notworked')  == 0 ? NULL: 1;

			$postdata['includedeleted'] = $this->security->xss_clean($this->input->post('includedeleted'));
			$postdata['Africa'] = $this->security->xss_clean($this->input->post('Africa'));
			$postdata['Asia'] = $this->security->xss_clean($this->input->post('Asia'));
			$postdata['Europe'] = $this->security->xss_clean($this->input->post('Europe'));
			$postdata['NorthAmerica'] = $this->security->xss_clean($this->input->post('NorthAmerica'));
			$postdata['SouthAmerica'] = $this->security->xss_clean($this->input->post('SouthAmerica'));
			$postdata['Oceania'] = $this->security->xss_clean($this->input->post('Oceania'));
			$postdata['Antarctica'] = $this->security->xss_clean($this->input->post('Antarctica'));
			$postdata['band'] = $this->security->xss_clean($this->input->post('band'));
			$postdata['mode'] = $this->security->xss_clean($this->input->post('mode'));
			$postdata['sat'] = $this->security->xss_clean($this->input->post('sats'));
			$postdata['orbit'] = $this->security->xss_clean($this->input->post('orbits'));
		} else { // Setting default values at first load of page
			$postdata['qsl'] = 1;
			$postdata['lotw'] = 1;
			$postdata['eqsl'] = 0;
			$postdata['qrz'] = 0;
			$postdata['worked'] = 1;
			$postdata['confirmed'] = 1;
			$postdata['notworked'] = 1;
			$postdata['includedeleted'] = 0;
			$postdata['Africa'] = 1;
			$postdata['Asia'] = 1;
			$postdata['Europe'] = 1;
			$postdata['NorthAmerica'] = 1;
			$postdata['SouthAmerica'] = 1;
			$postdata['Oceania'] = 1;
			$postdata['Antarctica'] = 1;
			$postdata['band'] = 'All';
			$postdata['mode'] = 'All';
			$postdata['sat'] = 'All';
			$postdata['orbit'] = 'All';
		}

		$data['wae_array'] = $this->wae->get_wae_array($bands, $postdata);
		$data['wae_summary'] = $this->wae->get_wae_summary($bands, $postdata);

		// Render Page
		$data['page_title'] = sprintf(__("Awards - %s"), __("WAE"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/wae/index');
		$this->load->view('interface_assets/footer');
	}

	public function seven3on73 () {

		// Grab all worked stations on AO-73
		$this->load->model('Seven3on73');
		$data['seven3on73_array'] = $this->Seven3on73->get_all();

		$data['page_title'] = sprintf(__("Awards - %s"), __("73 on 73"));
		$this->load->view('interface_assets/header', $data);
		$this->load->view('awards/73on73/index');
		$this->load->view('interface_assets/footer');
	}
}
