<?php
include "fonctions.php";

// contrôle de réception de paramètre
if(isset($_POST["operation"])){
	
	// demande de récupération du dernier profil
	if($_REQUEST["operation"]=="authentification"){
		
		try{
			print ("authentification%");
			// récupération des données en post
			$lesdonnees = $_REQUEST["lesdonnees"];
			$donnee = json_decode($lesdonnees);
			$identifiant = $donnee[0];
			$mdp = $donnee[1];
			$cnx = connexionPDO();
			
			// récupère le id 
			//$larequete2 = "select visiteur.id from visiteur where login='".$identifiant."'"." and mdp='".$mdp."'";
			//$req2 = $cnx->prepare($larequete2);
			//$req2->execute();
			//$rep = $req2->fetch();
			//print ($rep['id']);

			$larequete = "select * from visiteur where login='".$identifiant."'"." and mdp='".$mdp."'";
			$req = $cnx->prepare($larequete);
			$req->execute();
			
			//$nb_ligne = $req->rowCount();
			// s'il y a un profil, envoit de la réussite d'authenfication
			if($ligne = $req->fetch(PDO::FETCH_ASSOC)){
				print ("valide !%");
			}else{print ("non valide !%");}
		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
		
	// enregistrement Frais	
	}elseif($_REQUEST["operation"]=="enreg"){

		try{
                        print ("enreg%");
			// r�cup�ration des donn�es en post
			$lesdonnees = $_REQUEST["lesdonnees"];                   
			$donnee = json_decode($lesdonnees);
                        // r�cup�ration des donn�es d'authentification
                        $identifiant = $donnee[0];
                        $mdp = $donnee[1];
                        // r�cup�ration du nombre de ligne dans le tableau des donn�es
                        $size = count($donnee);
			// insertion dans la BD
        		$cnx = connexionPDO();
                        // r�cup�ration de l'id li� aux donn�ees d'authentification
			$larequeteID = "select visiteur.id from visiteur where login='".$identifiant."'"." and mdp='".$mdp."'";
			$reqID = $cnx->prepare($larequeteID);
			$reqID->execute();
			$repID = $reqID->fetch();
                        $ID = $repID['id'];
                        $moi = "";
                        // on dissocie les frais par date
                        for ($k = 2; $k< $size; $k++){
                            // on d�coupe la variable string qui contient toute les donn�es qu'on avait s�par� par un symbol
                            $ligneDonnee = explode ( "&" , $donnee[$k] ,  PHP_INT_MAX );
                            // on r�cup�re le mois 
                            $moi = substr($ligneDonnee[0],1, strlen($ligneDonnee[0]));
                            // requete de creation fichefrais avec le mois r�cup�r�
                            try {
                            $larequete = "insert into fichefrais (mois, idvisiteur)";
                            $larequete .= " values ('".$moi."', '".$ID."')";
                            $cnx = connexionPDO();
                            $req = $cnx->prepare($larequete);
                            $req->execute();
                            } catch(PDOException $e){
                                print "Erreur !%".$e->getMessage();
                                die();
                            }
                            // on v�rifie le type de de frais contenu dans chaque ligne de la liste $ligneDonnee
                            for ($i = 1 ; $i<count($ligneDonnee); $i++){
                                // on v�rifie si c'est un Type de Frais dans l'ID � trois caract�res
                                if (substr($ligneDonnee[$i], 0, 3)== "NUI" || substr($ligneDonnee[$i], 0, 3)== "ETP" || substr($ligneDonnee[$i], 0, 3)== "REP"){
                                    // requete d'enregistrement du frais
                                    $type = substr($ligneDonnee[$i], 0, 3);
                                    $quantite = substr($ligneDonnee[$i], 3, strlen($ligneDonnee[$i]));
                                    //insert into lignefraisforfait (idvisiteur, mois, idfraisforfait, quantite) VALUES ('b13', 1, 'KM', 10)
				if($quantite!=0) {
                                    try {
                                    $larequete = "insert into lignefraisforfait (idvisiteur, mois, idfraisforfait, quantite)";
                                    $larequete .= " values ('".$ID."', '".$moi."', '".$type."', ".$quantite.")";
                                    $cnx = connexionPDO();
                                    $req = $cnx->prepare($larequete);
                                    $req->execute();
                                    }  catch(PDOException $e){
                                        print "Erreur !%".$e->getMessage();
                                        die();
                                    }
				}
                                }else{
                                    // v�rifie si c'est un frais de KM
                                    if(substr($ligneDonnee[$i], 0, 2)== "KM"){
                                       // requete d'enregistrement du frais KM
                                       $type = substr($ligneDonnee[$i], 0, 2);
                                       $quantite = substr($ligneDonnee[$i], 2, strlen($ligneDonnee[$i]));
				if($quantite!=0) {
                                       try {
                                       $larequete = "insert into lignefraisforfait (idvisiteur, mois, idfraisforfait, quantite)";
                                       $larequete .= " values ('".$ID."', '".$moi."', '".$type."', ".$quantite.")";
                                       $cnx = connexionPDO();
                                       $req = $cnx->prepare($larequete);
                                       $req->execute();
                                       } catch(PDOException $e){
                                            print "Erreur !%".$e->getMessage();
                                            die();
                                       }
				}
                                    }else{
                                        // on s�pare les information d'un frais HF qu'on a s�par� par un symbol
                                        $hF = explode ( "|" , $ligneDonnee[$i], PHP_INT_MAX  );
                                        $jour = substr($hF[0], 2, strlen($hF[0])); // r�cup�ration du jour
                                        $annee = substr($hF[1], 1, strlen($hF[1])); // r�cup�ration ann�e
                                        $laDate = $annee."-".$moi."-".$jour; // variable contenant la date du frais hf
                                        for ($j = 2 ; $j<count($hF); $j++){
                                            // on r�cup�re les informations n�cessaire
                                           if( substr($hF[$j], 0, 3)== "MOT"){
                                               $motif = 
                                                       substr($hF[$j], 3, strlen($hF[$j]));
                                           }else{
                                               $montant = substr($hF[$j], 3, strlen($hF[$j]));
                                               // requete pour enregistrer Frais HF
                                               try {
                                               $larequete = "insert into lignefraishorsforfait (idvisiteur, mois, libelle, date, montant)";
                                               $larequete .= " values ('".$ID."', '".$moi."', '".$motif."', '".$laDate."', ".$montant.")";
                                               $cnx = connexionPDO();
                                               $req = $cnx->prepare($larequete);
                                               $req->execute();
                                               } catch(PDOException $e){
                                                    print "Erreur !%".$e->getMessage();
                                                    die();
                                               }
                                           }

                                        }
                                        
                                    }
                                }
                              
                            }
                        }
		}catch(PDOException $e){
			print "Erreur !%".$e->getMessage();
			die();
		}
		
	}
	
}