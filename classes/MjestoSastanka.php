<?php

class MjestoSastanka extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'mjesto_sastanka';
    }

    function add() {
        $response = $this->insert();
        if ($response!=false) {
            $this->data = $response;
        }
    }

    function edit() {
        $id_mjesto_sastanka = $this->db->real_escape_string($this->input->id_mjesto_sastanka);
        $response = $this->update($id_mjesto_sastanka);
        if ($response!=false) {
            $this->data = $response;
        }
    }

    function deleteMeetingPoint() {
        $id_mjesto_sastanka = $this->db->real_escape_string($this->input->id_mjesto_sastanka);

        $response = $this->delete($id_mjesto_sastanka);
        if ($response!=false) {
            $this->data = $response;
        }
    }

    function fetchMeetingPoints() {
        $this->select();
    }

    function fetchMeetingPointByTermin() {
        $idTermin = $this->db->real_escape_string($this->input->id_tura);
        $this->selectByFK("id_termin", $idTermin);
    }
}