<?php

class TipKorisnika extends Controller
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'tip_korisnika';
    }

    function add() {
        $response = $this->insert();
        if ($response!=false) {
            $this->data = $response;
        }
    }
    function edit() {
        $id_tip_korisnika = $this->real_escape_string($this->input->id_tip_korisnika);
        $response = $this->update($id_tip_korisnika);
        if ($response!=false) {
            $this->data = $response;
        }
    }

}

?>