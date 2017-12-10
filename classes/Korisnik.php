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
            $this->error = ("Već postoji korisnik s istim korisničkim imenom ili mail adresom");
            return;
        }
        $hashed_password = password_hash($this->input->lozinka, PASSWORD_BCRYPT);
        $this->input->lozinka = $hashed_password;

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
        $mail->addReplyTo();
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

        if ($result = $this->dbSelect($query, "Za uneseno korisničko ime nije pronađen aktivirani korisnik")) {
            $row = $result->fetch_assoc();
            $auth = password_verify($password, $row['lozinka']);

            if ($auth) {
                $this->data->success = true;
                $this->data->id_korisnik = $row['id_korisnik'];
                $this->data->korisnicko_ime = $username;
                $this->data->ime = $row['ime'];
                $this->data->prezime = $row['prezime'];
                $this->data->email = $row['email'];
                $this->data->id_tip_korisnika = $row['id_tip_korisnika'];
            } else {
                $this->data->success = false;
                $this->error = "Unesena lozinka nije ispravna";
            }
        }
    }
}