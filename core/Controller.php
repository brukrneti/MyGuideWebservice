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
    }

    /*function getUserByAuth()
    {
        isset ($this->input->auth_token) ? $authtoken = $this->input->auth_token : $authtoken = "";
        $result = $this->dbQuery("SELECT id_korisnik FROM korisnik WHERE auth_token='$authtoken' AND aktivan=1 AND aktiviran=1");
        $this->id_korisnik = $result->fetch_assoc()['id_korisnik'];
        unset($this->input->auth_token);
    }*/


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
            $this->error = ("Update failed! SQL Error message: " . $con->error . "SQL that was executed: " . $sql);
            return false;
        }

        //$con->query("INSERT INTO log (id_korisnik, sql_upit, ip) VALUES (".USER_ID.", '".$con->real_escape_string($sql)."', '".USER_IP."')");

        return array("update_id" => $id);

    }

    function delete($id)
    {
        $tname = $this->table;
        $con = $this->db;
        $id=$con->real_escape_string($id);
        $sql = "UPDATE $tname SET aktivan=0 WHERE id_".$tname."=$id";
        $con->query($sql);

        if ($con->errno) {
            $this->error = ("Delete failed! SQL Error message: " . $con->error . "SQL that was executed: " . $sql);
            return false;
        }

        //$con->query("INSERT INTO log (id_korisnik, sql_upit, ip) VALUES (".USER_ID.", '".$con->real_escape_string($sql)."', '".USER_IP."')");

        return array("update_id" => $id);
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
            $this->error = ("Insert failed! SQL Error message: ".$con->error."SQL that was executed: ".$fullSql);
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
            $this->error = ("Query failed! SQL Error message: ".$con->error."SQL that was executed: ".$query);
            return false;
        }

        if ($res->num_rows == 1) {
            $data = $res->fetch_assoc();
            return $data;
        }
    }

    function outputResponse()
    {
        $r=["data"=>$this->data];
        if($this->error) $r["error"]=$this->error;
        echo json_encode($r);
    }

    public function dbQuery($query)
    {
        $result = mysqli_query($this->db, $query);
        if ($result===false) {
            $this->error = ("Query failed! SQL Error message: ".$this->db->error."SQL that was executed: ".$query);
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
        //var_dump($this->input);
        foreach ($this->input AS $key=>$value) {
            $this->input->$key = $this->db->escape_string($value);
          //  var_dump($this->input);
        }
    }

    /*function fileUpload($file, $thumb)
    {
        $target_dir = ROOT."/uploads/";
        try {

            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit.');
                default:
                    throw new RuntimeException('Unknown errors.');
            }

            if ($file['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            $filename = $file['name'];
            $actual_name = pathinfo($file['name'], PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $original_name = $actual_name;

            $i = 1;
            while (file_exists($target_dir . $actual_name . "." . $ext)) {
                $actual_name = (string)$original_name . " ($i)";
                $filename = $actual_name . "." . $ext;
                $i++;
            }

            if (!copy($file['tmp_name'], sprintf($target_dir . '%s', $filename))) {
                throw new RuntimeException('Failed to move uploaded file.');
            }

            //
            //Kod za kreiranje thumb slike
            $maxDim = 200;
            list($width, $height, $type, $attr) = getimagesize( $file['tmp_name'] );
            if ($thumb) {
                if ($width > $maxDim || $height > $maxDim) { //Ako je original slika vec dovoljna mala nije potrebno raditi thumb posebno
                    $target_filename = $file['tmp_name'];
                    $fn = $file['tmp_name'];
                    $size = getimagesize($fn);
                    $ratio = $size[0] / $size[1]; // width/height
                    if ($ratio > 1) {
                        $width = $maxDim;
                        $height = $maxDim / $ratio;
                    } else {
                        $width = $maxDim * $ratio;
                        $height = $maxDim;
                    }
                    $src = imagecreatefromstring(file_get_contents($fn));
                    $dst = imagecreatetruecolor($width, $height);
                    imagealphablending($dst, false); //Potrebno da se sačuva transparency
                    imagesavealpha($dst, true);      //Potrebno da se sačuva transparency
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
                    imagedestroy($src);
                    switch ($ext) {
                        case 'jpg':
                            imagejpeg($dst, $target_filename);
                            break;
                        case 'jpeg':
                            imagejpeg($dst, $target_filename);
                            break;
                        case 'png':
                            imagepng($dst, $target_filename);
                            break;
                        case 'gif':
                            imagegif($dst, $target_filename);
                            break;
                        default:
                            imagejpeg($dst, $target_filename);
                    }
                    imagedestroy($dst);
                    if (!move_uploaded_file($file['tmp_name'], sprintf($target_dir . '%s%s.%s', $actual_name, "-thumb", $ext))) {
                        throw new RuntimeException('Failed to move uploaded file.');
                    }
                    //Ako je kreiran thumb return vraća url original slike i thumba
                    return array("slika" => sprintf($target_dir . '%s', $filename), "thumb" => sprintf($target_dir . '%s%s', "thumb_", $filename));
                } else {
                    //Ako nije potrebno resizeat sliku kao thumb se samo spremi orignalna nemodificirana slika
                    if (!move_uploaded_file($file['tmp_name'], sprintf($target_dir . '%s%s.%s', $actual_name, "-thumb", $ext))) {
                        throw new RuntimeException('Failed to move uploaded file.');
                    }
                    return array("slika" => sprintf($target_dir . '%s', $filename), "thumb" => sprintf($target_dir . '%s%s', "thumb_", $filename));
                }

            }
            //kraj koda za kreiranje thumba

        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return sprintf($target_dir . '%s', $filename);
    }*/
}
