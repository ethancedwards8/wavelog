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
		if ($station_id != 'All') {
			$this->db->where($this->config->item('table_name').'.station_id', $station_id);
		}

		$this->db->join('station_profile', 'station_profile.station_id = '.$this->config->item('table_name').'.station_id');
		$this->db->join('oqrs', 'oqrs.qsoid = '.$this->config->item('table_name').'.COL_PRIMARY_KEY', 'left outer');
		// always filter user. this ensures that even if the station_id is from another user no inaccesible QSOs will be returned
		$this->db->where('station_profile.user_id', $this->session->userdata('user_id'));
		$this->db->where_in('COL_QSL_SENT', array('R', 'Q'));
		$this->db->order_by("COL_DXCC", "ASC");
		$this->db->order_by("COL_CALL", "ASC");
		$this->db->order_by("COL_SAT_NAME", "ASC");
		$this->db->order_by("COL_SAT_MODE", "ASC");
		$this->db->order_by("COL_BAND_RX", "ASC");
		$this->db->order_by("COL_TIME_ON", "ASC");
		$this->db->order_by("COL_MODE", "ASC");
		$this->db->limit(1000);
		$query = $this->db->get($this->config->item('table_name'));

		return $query;
	}

	function get_qsos_for_print_ajax($station_id) {
		$query = $this->get_qsos_for_print($station_id);

		return $query;
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

		return true;
	}

	function open_qso_list($callsign) {
		$this->db->join('station_profile', 'station_profile.station_id = '.$this->config->item('table_name').'.station_id');
		// always filter user. this ensures that no inaccesible QSOs will be returned
		$this->db->where('station_profile.user_id', $this->session->userdata('user_id'));
		$this->db->where('(COL_CALL like "%/'.$callsign.'/%" OR COL_CALL like "%/'.$callsign.'" OR COL_CALL like "'.$callsign.'/%" OR COL_CALL = "'.$callsign.'")');
		$this->db->where('coalesce(COL_QSL_SENT, "") not in ("R", "Q")');
		$this->db->order_by("COL_DXCC", "ASC");
		$this->db->order_by("COL_CALL", "ASC");
		$this->db->order_by("COL_SAT_NAME", "ASC");
		$this->db->order_by("COL_SAT_MODE", "ASC");
		$this->db->order_by("COL_BAND_RX", "ASC");
		$this->db->order_by("COL_TIME_ON", "ASC");
		$this->db->order_by("COL_MODE", "ASC");
		$query = $this->db->get($this->config->item('table_name'));

		return $query;
	}

	function show_oqrs($id) {
		$this->db->select('requesttime as "Request time", requestcallsign as "Requester", email as "Email", note as "Note"');
		$this->db->join('station_profile', 'station_profile.station_id = oqrs.station_id');
		// always filter user. this ensures that no inaccesible QSOs will be returned
		$this->db->where('station_profile.user_id', $this->session->userdata('user_id'));
		$this->db->where('oqrs.id = ' .$id);
		$query = $this->db->get('oqrs');

		return $query->result();
	}

	function check_for_qsls_by_callsigns($callsigns) {
		if (empty($callsigns)) {
			return [];
		}

		$this->load->model('stations');
		$station_ids = $this->stations->all_station_ids_of_user();
	
		$this->db->select('COL_CALL, COUNT(COL_PRIMARY_KEY) as count');
		$this->db->from($this->config->item('table_name'));
		$this->db->where_in('COL_CALL', $callsigns);
		$this->db->where_in('station_id', explode(',', $station_ids));
		$this->db->where('COL_QSL_SENT', 'Y');
		$this->db->group_by('COL_CALL');
	
		$query = $this->db->get();
		return $query->result();
	}

}

?>
