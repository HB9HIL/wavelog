<?php

class Plugins_model extends CI_Model {

    function get_all() {
        $this->db->select('*');
        $this->db->from('plugins');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();

        return $query->result();
    }

}