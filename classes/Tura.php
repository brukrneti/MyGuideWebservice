<?php

class Tura extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'tura';
    }

    function fetchAll($filter="")
    {

        $filter == "" ? $filterUvjet = "" : $filterUvjet = "WHERE t.naziv LIKE '%$filter%'";

        //$this->select();
        $result = $this->dbQuery("SELECT t.*, k.ime AS ime_vodica, k.prezime AS prezime_vodica, k.email AS email_vodica FROM tura t JOIN korisnik k USING (id_korisnik) $filterUvjet");
        $allRows = array();
        while($currentRow = $result->fetch_assoc()) {
            $allRows[] = $currentRow;
        }
        $this->data->success = true;
        $this->data->rows = $allRows;
    }

    function fetchByGuide () {
        $idVodica = isset($this->input->id_korisnik) ? $this->input->id_korisnik : "";

        if ($idVodica == "") {
            $this->error = "Foreign key not provided";
        }
        else {
            //$this->selectByFK("id_korisnik", $idVodica);

            $result = $this->dbQuery("SELECT t.*, k.ime AS ime_vodica, k.prezime AS prezime_vodica, k.email AS email_vodica FROM tura t JOIN korisnik k USING (id_korisnik) WHERE t.id_korisnik=$idVodica");
            $allRows = array();
            while($currentRow = $result->fetch_assoc()) {
                $allRows[] = $currentRow;
            }
            $this->data->success = true;
            $this->data->rows = $allRows;
        }
    }

    function fetchById() {
        $idTure = isset($this->input->id_tura) ? $this->input->id_tura : "";
        if ($idTure == "") {
            $this->error = "Foreign key not provided";
        }
        else {
            //$this->getById($idTure);
            $result = $this->dbQuery("SELECT t.*, k.ime AS ime_vodica, k.prezime AS prezime_vodica, k.email AS email_vodica FROM tura t JOIN korisnik k USING (id_korisnik) WHERE t.id_tura=$idTure");
            $allRows = array();
            while($currentRow = $result->fetch_assoc()) {
                $allRows[] = $currentRow;
            }
            $this->data->success = true;
            $this->data->rows = $allRows;
        }
    }

    function add() {
        $encodedImgString = $this->input->img_path;
        $imgName = $this->input->img_name;

        $image_upload = $this->fileUpload($encodedImgString, $imgName);

        if ($image_upload) {
            $response = $this->insert();

            if ($response!=false) {
                $this->data = $response;
            }
        }
        else {
            $this->data->success = false;
            $this->error = "Image upload failed";
        }
    }
    function edit() {
        $idTura = $this->input->id_tura;

        $encodedImgString = isset($this->input->img_path) ? $this->input->img_path : "";
        $imgName = isset($this->input->img_name) ? $this->input->img_name : "";

        if ($encodedImgString != "" && $imgName != "") {
            $image_upload = $this->fileUpload($encodedImgString, $imgName, $idTura);

            if ($image_upload) {
                unset ($this->input->id_tura);
                $response = $this->update($idTura);

                if ($response!=false) {
                    $this->data = $response;
                }
            }
            else {
                $this->data->success = false;
                $this->error = "Image upload failed";
            }
        }

        else { //ako se ne predaje slika samo ažuriraj ostale stupce
            unset ($this->input->id_tura);
            $response = $this->update($idTura);

            if ($response!=false) {
                $this->data->success = true;
                $this->data = $response;
            }
        }
    }

    function deleteTour() {
        $idTura = $this->input->id_tura;

        $response = $this->delete($idTura);
        if ($response!=false) {
            $this->data = $response;
        }
    }

}