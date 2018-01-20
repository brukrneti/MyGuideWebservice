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
        $id_termin = $this->real_escape_string($this->input->id_termin);
        $response = $this->update($id_termin);
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function deleteTermin() {
        $id_termin = $this->real_escape_string($this->input->id_termin);

        $response = $this->delete($id_termin);
        if ($response!=false) {
            $this->data = $response;
        }
    }
}