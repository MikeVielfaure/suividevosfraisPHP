<?php
    function connexionPDO(){
        try {
            $conn = new PDO('mysql:host=mysql-saisiedevosfrais.alwaysdata.net;dbname=saisiedevosfrais_suivisdevosfrais;port=3306','226360','saisiedevosfrais');
            return $conn;
        } catch (PDOException $e) {
            print "Erreur de connexion PDO :".$e;
            die();
        }
    }
connexionPDO();
	
?>