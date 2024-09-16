<?php

class Operatorstats_model extends CI_Model {

    public function get_operator_data() {
        // Get the count of distinct operators
        $this->db->select('COUNT(DISTINCT COL_OPERATOR) AS distinct_count');
        $distinct_query = $this->db->get($this->config->item('table_name'));
        $distinct_operators_count = $distinct_query->row()->distinct_count;
    
        // Get the operator entries with count per operator, sorted by count in descending order
        $this->db->select('COL_OPERATOR, COUNT(*) AS count_per_operator');
        $this->db->group_by('COL_OPERATOR');
        $this->db->order_by('count_per_operator', 'DESC');
        $entries_query = $this->db->get($this->config->item('table_name'));
        $operator_entries = $entries_query->result();
    
        return array(
            'distinct_operators_count' => $distinct_operators_count,
            'operator_entries' => $operator_entries
        );
    }

}