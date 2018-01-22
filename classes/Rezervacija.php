<?php

class Rezervacija extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'rezervacija';
    }

    function add() {
        $response = $this->insert();
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function edit() {
        $id_rezervacija = $this->db->real_escape_string($this->input->id_rezervacija);
        $response = $this->update($id_rezervacija);
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function deleteReservation() {
        $id_rezervacija = $this->db->real_escape_string($this->input->id_rezervacija);

        $response = $this->delete($id_rezervacija);
        if ($response!=false) {
            $this->data = $response;
        }
    }
}