<?php

class Korisnik extends Controller
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'korisnik';
    }

    function register()
    {
        if ($this->userExists()) {
            $this->data->success = false;
            $this->error = ("The username has already been taken");
            return;
        }
        $hashed_password = password_hash($this->input->lozinka, PASSWORD_BCRYPT);
        $this->input->lozinka = $hashed_password;

        //$response = $this->insert();

        $encodedImgString = $this->input->img_path;
        $imgName = $this->input->img_name;

        $image_upload = $this->fileUpload($encodedImgString, $imgName);

        if ($image_upload) {
            $response = $this->insert();

            if ($response!=false) {
                $this->data = $response;
                //slanje aktivacijskog koda na mail
                $currentTime = date("Y-m-d H:i:s", time());
                $korisnicko_ime = $this->db->real_escape_string($this->input->korisnicko_ime);
                $activationCode = md5(uniqid(rand(), true ) . $korisnicko_ime);

                $this->dbQuery("INSERT INTO aktivacijski_kod VALUES (default, '$activationCode', '$currentTime', $response[insert_id], 0)");

                $email = $this->db->real_escape_string($this->input->email);
                $this->sendConfirmationMail($email, $activationCode);
            }
        }
        else {
            $this->data->success = false;
            $this->error = "Image upload failed.";
        }
    }

    function userExists()
    {
        $email = $this->db->real_escape_string($this->input->email);
        $korisnicko_ime = $this->db->real_escape_string($this->input->korisnicko_ime);
        $query = "SELECT * FROM korisnik WHERE email= '".$email."' || korisnicko_ime='".$korisnicko_ime."'";
        $result = $this->dbQuery($query);
        return $result->num_rows>0;
    }

    function sendConfirmationMail($mailTo, $activationCode)
    {
        $html = "<p>Molimo da klikom na ovaj link potvrdite svoju registraciju:</p><br><a href='http://www.yeloo.hr/AiR/MyGuideWebServices/confirm_registration.php?code=$activationCode'>CONFIRM REGISTRATION</a>";
        $mail=new PHPMailer;
        $mail->setFrom('donotreply@myguide.hr', 'MyGuide AutoMessage');
        $mail->addAddress($mailTo);
        //$mail->addReplyTo();
        $mail->isHTML(true);
        $mail->Subject = '[MyGuide] Potvrda registracije';
        $mail->Body    = $html;
        $mail->AltBody = 'To read this email please use client with HTML email capability';

        if(!$mail->send()) {
            $this->error = ("Mail nije uspješno poslan");
        }
    }

    function login()
    {
        //$mail = $this->db->real_escape_string($this->input->mail);
        $username = $this->db->real_escape_string($this->input->korisnicko_ime);
        $password = $this->db->real_escape_string($this->input->lozinka);
        $query = "SELECT * FROM korisnik WHERE aktivan=1 AND korisnicko_ime= '" . $username . "'";

        if ($result = $this->dbSelect($query, "Invalid username or password.")) {
            $row = $result->fetch_assoc();
            $auth = password_verify($password, $row['lozinka']);

            if ($auth) {
                $this->data->success = true;
                $this->data->id_korisnik = $row['id_korisnik'];
                $this->data->korisnicko_ime = $username;
                $this->data->ime = $row['ime'];
                $this->data->prezime = $row['prezime'];
                $this->data->email = $row['email'];
                $this->data->img_path = $row['img_path'];
                $this->data->img_name = $row['img_name'];
                $this->data->id_tip_korisnika = $row['id_tip_korisnika'];
            } else {
                $this->data->success = false;
                $this->error = "Invalid username or password.";
            }
        }
    }

    function editProfile() {
        $idKorisnik = $this->input->id_korisnik;

        if (isset($this->input->lozinka)) {
            $hashed_password = password_hash($this->input->lozinka, PASSWORD_BCRYPT);
            $this->input->lozinka = $hashed_password;
        }

        $encodedImgString = isset($this->input->img_path) ? $this->input->img_path : "";
        $imgName = isset($this->input->img_name) ? $this->input->img_name : "";

        if ($encodedImgString != "" && $imgName != "") {
            $image_upload = $this->fileUpload($encodedImgString, $imgName, $idKorisnik);

            if ($image_upload) {
                unset ($this->input->id_korisnik);
                $response = $this->update($idKorisnik);

                if ($response!=false) {
                    $result = $this->dbQuery("SELECT * FROM korisnik WHERE id_korisnik=$idKorisnik");
                    $row = $result->fetch_assoc();
                    $this->data->success = true;
                    $this->data->id_korisnik = $row['id_korisnik'];
                    $this->data->korisnicko_ime = $row['korisnicko_ime'];
                    $this->data->ime = $row['ime'];
                    $this->data->prezime = $row['prezime'];
                    $this->data->email = $row['email'];
                    $this->data->img_path = $row['img_path'];
                    $this->data->img_name = $row['img_name'];
                    $this->data->id_tip_korisnika = $row['id_tip_korisnika'];
                }
            }
            else {
                $this->data->success = false;
                $this->error = "Image upload failed";
            }
        }

        else { //ako se ne predaje slika samo ažuriraj ostale stupce
            unset ($this->input->id_korisnik);

            $response = $this->update($idKorisnik);

            if ($response!=false) {
                $result = $this->dbQuery("SELECT * FROM korisnik WHERE id_korisnik=$idKorisnik");
                $row = $result->fetch_assoc();
                $this->data->success = true;
                $this->data->id_korisnik = $row['id_korisnik'];
                $this->data->korisnicko_ime = $row['korisnicko_ime'];
                $this->data->ime = $row['ime'];
                $this->data->prezime = $row['prezime'];
                $this->data->email = $row['email'];
                $this->data->img_path = $row['img_path'];
                $this->data->img_name = $row['img_name'];
                $this->data->id_tip_korisnika = $row['id_tip_korisnika'];
            }

        }
    }
}