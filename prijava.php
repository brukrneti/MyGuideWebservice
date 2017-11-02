<?php
    $con = mysqli_connect("", "root","", "my_guide");
    $korime = $_POST["korisnicko_ime"];
    $lozinka = $_POST["lozinka"];
    $upit  = mysqli_prepare($con, "SELECT * FROM korisnik WHERE korisnicko_ime = ? AND lozinka = ?");
    mysqli_stmt_bind_param($upit, "ss", $korime, $lozinka);
    mysqli_stmt_execute($upit);
    
    mysqli_stmt_store_result($upit);
    mysqli_stmt_bind_result($upit, $korisnici_id, $ime, $prezime, $email,$korime,$lozinka, $tip_korisnika);
    
    $odgovor = array();
    $odgovor["success"] = false;
    
    while(mysqli_stmt_fetch($upit)){
        $odgovor["success"] = true;
        $odgovor["ime"] = $ime;
        $odgovor["prezime"] = $prezime;
        $odgovor["email"] = $email;
        $odgovor["korisnicko_ime"] = $korime;
        $odgovor["lozinka"] = $lozinka;
        $odgovor["id_tip_korisnika"] = $tip_korisnika;
        
    }
    print_r(json_encode($odgovor));

?>

