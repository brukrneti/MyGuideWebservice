<?php

const USER_ID = 0;
const USER_IP = 0;

abstract class Controller
{
    var $authNeeded=true;
    var $input;
    var $data;
    var $error;
    var $db;
    var $table;
    var $id_korisnik;

    function __construct()
    {
        $this->data=new stdClass();
        $this->error=false;
        $this->db = Database::getInstance()->getConnection();
        $this->db->set_charset("utf8");
        //$this->input = json_decode(file_get_contents('php://input'),false);
        $this->input = (object)$_POST;
        $this->escapePost();
    }

    function select() {

        $tname = $this->table;
        $con = $this->db;
        $query = "SELECT * FROM $tname WHERE aktivan=1 ";
        $res = $con->query($query);

        if($con->errno) {
            $this->error = ("Query failed! SQL Error message: ".$con->error.". SQL that was executed: ".$query);
            $this->data->success = false;
        }

        else {
            $allRows = array();
            while($currentRow = $res->fetch_assoc()) {
                $allRows[] = $currentRow;
            }
            $this->data->success = true;
            $this->data->rows = $allRows;
        }

    }

    function selectByFK($fk, $fkValue) {
        $tname = $this->table;
        $con = $this->db;
        $query = "SELECT * FROM $tname WHERE $fk = $fkValue AND aktivan=1 ";
        $res = $con->query($query);

        if($con->errno) {
            $this->data->success = false;
            $this->error = ("Query failed! SQL Error message: ".$con->error.". SQL that was executed: ".$query);
        }
        else {
            $allRows = array();
            while($currentRow = $res->fetch_assoc()) {
                $allRows[] = $currentRow;
            }
            $this->data->success = true;
            $this->data->rows = $allRows;
        }
    }

    function update($id)
    {
        $tname = $this->table;
        $con = $this->db;
        $params = $this->input;
        $sql = "UPDATE $tname SET ";

        if (sizeof($params)) {
            foreach ($params as $key => $value) {
                $sql .= ($key . "='" . $con->real_escape_string($value) . "', ");
            }
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE id_" . $tname . "=" . $id;

        $con->query($sql);

        if ($con->errno) {
            $this->data->success = false;
            $this->error = ("Update failed! SQL Error message: " . $con->error . ". SQL that was executed: " . $sql);
            return false;
        }

        //$con->query("INSERT INTO log (id_korisnik, sql_upit, ip) VALUES (".USER_ID.", '".$con->real_escape_string($sql)."', '".USER_IP."')");

        //$this->data->update_id = $id;

        return array("success"=>true, "update_id" => $id);
    }

    function delete($id)
    {
        $tname = $this->table;
        $con = $this->db;
        $id=$con->real_escape_string($id);
        $sql = "UPDATE $tname SET aktivan=0 WHERE id_".$tname."=$id";
        $con->query($sql);

        if ($con->errno) {
            $this->error = ("Delete failed! SQL Error message: " . $con->error . ". SQL that was executed: " . $sql);
            $this->data->success = false;
            return false;
        }

        //$con->query("INSERT INTO log (id_korisnik, sql_upit, ip) VALUES (".USER_ID.", '".$con->real_escape_string($sql)."', '".USER_IP."')");

        return array("success"=>true, "update_id" => $id);
    }

    function insert()
    {

        $tname = $this->table;
        $con = $this->db;
        $params = $this->input;
        $sql = "INSERT INTO $tname (";
        $sql2 = "(";

        if(sizeof($params)) {
            foreach ($params as $key => $value) {
                $sql .= " $key,";
                $sql2 .= (" '" . $con->real_escape_string($value) . "',");
            }
            $sql = substr($sql, 0, -1);
            $sql2 = substr($sql2, 0, -1);
        }

        $fullSql=($sql . ") VALUES " . $sql2 . ")");
        $con->query($fullSql);
        $insertID=$con->insert_id;

        if($con->errno) {
            $this->data->success = false;
            $this->error = ("Insert failed! SQL Error message: ".$con->error.". SQL that was executed: ".$fullSql);
            return false;
        }
        //$con->query("INSERT INTO log (id_korisnik, sql_upit, ip) VALUES (".USER_ID.", '".$con->real_escape_string($fullSql)."', '".USER_IP."')");
        return array("success" => true, "insert_id" => $insertID);
    }

    function getById($id) {
        $tname = $this->table;
        $con = $this->db;
        $query = "SELECT * FROM $tname WHERE id_$tname = $id AND aktivan=1 ";
        $res = $con->query($query);

        if($con->errno) {
            $this->data->success = false;
            $this->error = ("Query failed! SQL Error message: ".$con->error.". SQL that was executed: ".$query);
            return false;
        }

        if ($res->num_rows == 1) {
            $this->data->success = true;
            $this->data->rows = $res->fetch_assoc();
        }
    }

    function outputResponse()
    {
        $r=["data"=>$this->data];
        if($this->error) $r["error"]=$this->error;
        echo json_encode($r, JSON_UNESCAPED_SLASHES);
    }

    public function dbQuery($query)
    {
        $result = mysqli_query($this->db, $query);
        if ($result===false) {
            $this->error = ("Query failed! SQL Error message: ".$this->db->error.". SQL that was executed: ".$query);
        }
        return $result;
    }

    public function dbSelect($query, $error)
    {
        if ($result = $this->dbQuery($query)) {
            if ($result->num_rows>0) {
                return $result;
            } else {
                $this->error = $error;
                $this->data->success = false;
                return false;
            }
        }
        return false;
    }

    public function dbUpdate($query, $error)
    {
        if ($result = $this->dbQuery($query)) {
            if ($this->db->affected_rows>0) {
                return $result;
            } else {
                $this->error = $error;
                return false;
            }
        }
        return false;
    }

    public function escapePost () {
        foreach ($this->input AS $key=>$value) {
            $this->input->$key = $this->db->escape_string($value);
//            var_dump($this->input);
        }
    }

    function fileUpload($imgEncodedString, $imgName, $currentPicRowID="")
    {
        if ($currentPicRowID != "") { //ako je predan id znači da se slika mijenja pa brišemo trenutnu
            $tname = $this->table;
            $queryRes = $this->dbQuery("SELECT * FROM $tname WHERE id_$tname = $currentPicRowID")->fetch_assoc();
            $imgPathToDelete = $queryRes["img_path"];
            $imgPathToDelete = str_replace("http://yeloo.hr", "/home/yeloohr/public_html", $imgPathToDelete);

            unlink($imgPathToDelete);
        }

        $target_dir = ROOT . "/img/";

        $decoded_string = base64_decode($imgEncodedString);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $decoded_string, FILEINFO_MIME_TYPE);

        //$imgSize = strlen($decoded_string);

        $allowedMimeTypes = array (
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
        );

        if (!in_array($mime_type, $allowedMimeTypes)) { //ako ne zadovoljava formate ili je veća od 1mb -> dodat ako treba || $imgSize>1000000
            return false;
        }
        else {
            $original_name = pathinfo($imgName, PATHINFO_FILENAME);
            $ext = pathinfo($imgName, PATHINFO_EXTENSION);

            $current_name = $original_name;

            $final_imgName = $original_name . "." . $ext; //ako se sprema slikaprvi put s određenim imenom moramo tu odma spremit
            $i = 1;
            while (file_exists($target_dir . $current_name . "." . $ext)) {
                $current_name = (string)$original_name . "($i)";
                $final_imgName = $current_name . "." . $ext;
                $i++;
            }

            $remotePath = "http://yeloo.hr/AiR/MyGuideWebServices/img/" . $final_imgName;
            $this->input->img_path = $remotePath;

            $pathToSaveTo = $target_dir . $final_imgName;
            $is_written = file_put_contents($pathToSaveTo, $decoded_string);

            if ($is_written > 0) {
                return true;
            }
            else {
                return false;
            }
        }
    }
}
