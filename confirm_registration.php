<?php
error_reporting(E_ALL);
ini_set("display_errors","On");
require_once ("core/ClassDatabase.php");


function confirmUser($code)
{
    $db = Database::getInstance()->getConnection();
    $db->set_charset("utf8");

    $currentTime = date("Y-m-d H:i:s", time());
    //AKO POSTOJI REDAKVRATI REDAK INAČE VRATI FALSE
    $query = $db->query("SELECT * FROM aktivacijski_kod WHERE kod='$code' AND vrijeme_unosa >= DATE_SUB('$currentTime', INTERVAL 24 HOUR)");

    if ($query->num_rows>0) {
        $result = $query->fetch_assoc();

        if ($result["aktivan"] == 1) {
            $message = "Korisnički račun već je aktiviran.";
        }
        else {
            $db->query("UPDATE aktivacijski_kod SET aktivan=1 WHERE id_aktivacijski_kod=$result[id_aktivacijski_kod]");
            $db->query("UPDATE korisnik SET aktivan=1 WHERE id_korisnik=$result[id_korisnik]");

            $message = "Uspješno ste potvrdili registraciju!<br><br>Želimo Vam ugodno korištenje aplikacije!";
        }
    }
    else {
        $message = "Link za aktivaciju je istekao.. Molimo obratite se administratoru..";
    }

    return $message;
}


isset($_GET["code"]) ? $code=$_GET["code"] : $code = "";

if ($code != "") {
    $message = confirmUser($code);
}

else {
    die("Invalid request");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Aktivacija korisničkog računa</title>
</head>
<body>

<h2><?php echo $message?></h2>


</body>
</html>

