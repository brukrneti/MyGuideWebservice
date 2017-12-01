<?php
//require_once __DIR__ . '/vendor/autoload.php';
//echo $_SERVER['DOCUMENT_ROOT'];
//var_dump(require_once '../../../vendor/autoload.php');

//require_once ($_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php");
//require_once ($_SERVER['DOCUMENT_ROOT'] . "/vendor-facebook-login/autoload.php");

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
            $this->error = ("Već postoji korisnik s istim korisničkim imenom ili mail adresom");
            return;
        }
        $hashed_password = password_hash($this->input->lozinka, PASSWORD_BCRYPT);
        $this->input->lozinka = $hashed_password;

        $response = $this->insert();

        if ($response!=false) {
            //$this->sendConfirmationMail($this->input->mail);
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

    /* ispalo je da se ne koristi odavde..
    function confirmUser($code)
    {
        $currentTime = date("Y-m-d H:i:s", time());
        //AKO POSTOJI REDAKVRATI REDAK INAČE VRATI FALSE
        $query = $this->dbSelect("SELECT * FROM aktivacijski_kod WHERE kod='$code' AND vrijeme_unosa >= DATE_SUB('$currentTime', INTERVAL 24 HOUR)");

        if ($query) {
            $result = $query->fetch_assoc();
            $this->dbUpdate("UPDATE aktivacijski_kod SET aktivan=1 WHERE id_aktivacijski_kod=$result[id_aktivacijski_kod]");
            $this->dbUpdate("UPDATE korisnik SET aktivan=1 WHERE id_korisnik=$result[id_korisnik]");

            $message = "Uspješno ste potvrdili registraciju!<br><br>Želimo Vam ugodno korištenje aplikacije!";
        }
        else {
            $message = "Link za aktivaciju je istekao.. Molimo obratite se administratoru..";
        }

        return $message;
    }*/

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
                $this->data->korisnicko_ime = $username;
                $this->data->ime = $row['ime'];
                $this->data->prezime = $row['prezime'];
                $this->data->email = $row['email'];
                $this->data->id_tip_korisnika = $row['id_tip_korisnika'];

                /*$auth_token = uniqid($row['id_korisnik'], true);
                $auth_token_valjanost = date('Y-m-d', strtotime('+1 years'));
                $this->input = (object) ["auth_token" => $auth_token, "auth_token_valjanost" => $auth_token_valjanost];
                if($this->update($row['id_korisnik'])) {
                    $this->data = ["auth_token" => $auth_token];
                }*/

            } else {
                $this->error = "Unesena lozinka nije ispravna";
            }
        }
    }
}