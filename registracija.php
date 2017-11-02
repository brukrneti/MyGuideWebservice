<?php
    $con = mysqli_connect("", "root","", "my_guide");
    $ime = $_POST["ime"];
    $prezime = $_POST["prezime"];
    $email = $_POST["email"];
    $korime= $_POST["korisnicko_ime"];
    $lozinka= $_POST["lozinka"];
    $id_tip_korisnika = $_POST["id_tip_korisnika"];
    
    $odgovor = mysqli_prepare($con, "INSERT INTO korisnik (ime,prezime,email,korisnicko_ime,lozinka, id_tip_korisnika) VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($odgovor,"sssssi", $ime, $prezime,$email,$korime,$lozinka,$id_tip_korisnika);
    mysqli_stmt_execute($odgovor);
   
    $odgovor = array();
    $odgovor["success"] = true;


    print_r(json_encode($odgovor));

?>