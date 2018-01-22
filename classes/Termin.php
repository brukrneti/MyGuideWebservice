<?php

class Termin extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'termin';
    }

    function add() {
        $response = $this->insert();
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function edit() {
        $idTermin = $this->db->real_escape_string($this->input->id_termin);
        $response = $this->update($idTermin);
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function deleteTermin() {
        $idTermin = $this->db->real_escape_string($this->input->id_termin);

        $response = $this->delete($idTermin);
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function fetchTerminByTour() {
        $idTura = isset($this->input->id_tura) ? $this->input->id_tura : "";
        $idTura = $this->db->real_escape_string($idTura);

        if ($idTura == "") {
            $this->error = "Foreign key not provided";
        }
        else {
            $result = $this->dbQuery("SELECT t.*, ms.adresa AS adresa, ms.longitude AS longitude, ms.latitude AS latitude FROM termin t JOIN mjesto_sastanka ms USING (id_mjesto_sastanka) WHERE t.id_tura=$idTura");
            $allRows = array();
            while($currentRow = $result->fetch_assoc()) {
                $allRows[] = $currentRow;
            }
            $this->data->success = true;
            $this->data->rows = $allRows;
        }

    }
    function fetchTerminById() {
        $idTermin = $this->db->real_escape_string($this->input->id_termin);
        $this->getById($idTermin);
    }
}