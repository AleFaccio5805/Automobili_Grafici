<?php
    $jObj = null;

    //1. Collegarci al db
    $indirizzoServerDBMS = "localhost";
    $nomeDb = "4a_mezzi";
    $conn = mysqli_connect($indirizzoServerDBMS, "root", "", $nomeDb);
    if($conn->connect_errno>0){
        $jObj = preparaRisp(-1, "Connessione rifiutata");
    }else{
        $jObj = preparaRisp(0, "Connessione ok");
    }


    //2. Prelevare un dato json che arriva dal client
    $record = file_get_contents("php://input");
    $record = json_decode($record);
    $jObj->record = $record;

    //3 Verificare se non esiste già il record
    $query = "SELECT * 
        FROM mezzi as m, territori as t, tipidati as td, tipiveicoli as tv
        WHERE m.idTer = t.idTer AND m.idTipo = td.idTipo AND
            m.idTipoVeicolo = tv.idTipoVeicolo AND 
            t.descr = '".$record[1]."' AND  td.descr = '".$record[3]."'
            AND  tv.descr = '".$record[5]."' AND m.anno = ".$record[7]."
            AND m.val = ".$record[8];
    $ris = $conn->query($query);
    if($ris){
        //Quando la query non ha errori -> finisco qua anche con tabella vuota
        if($ris->num_rows > 0){
            $jObj = preparaRisp(0, "Record presente", $jObj);
            $jObj->risp = $ris->num_rows;
        }else{
            $jObj = preparaRisp(-1, "Record non presente", $jObj);

            //Prelevo l'id territorio
            $rispDb = getIdTerritorio($record[1], $conn);
            $jObj-> territorio = $rispDb;

            //prelevare l'id tipo veicolo
            $rispDb = getIdTipoVeicolo($record[5], $conn);
            $jObj-> tipoVeicolo = $rispDb;

            //Prelevare l'id tipo dato
            $rispDb = getIdTipoDato($record[3], $conn);
            $jObj-> tipoDato = $rispDb;

            //4. Costruire la INSERT
            $query = "INSERT INTO mezzi (idTer, idTipo, idTipoVeicolo, anno, val)
                        VALUES (".$jObj->territorio->idTer.", ".$jObj->tipoDato->idTipo.",
                        ".$jObj->tipoVeicolo->idTipoVeicolo.", ".$record[7].", ".$record[8].")";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                $jObj = preparaRisp(0, "Inserimento del mezzo avvenuto con successo");
            }else{
                $jObj = preparaRisp(-2, "Errore nella query: ".$conn->error);
            }
        }
    }else{
        //Quando ci sono errori
        $jObj = preparaRisp(-1, "Errore nella query: ".$conn->error);
    }

    //Rispondo al javascript (al client)
    echo json_encode($jObj);


function preparaRisp($cod, $desc, $jObj = null){
    if(is_null($jObj)){
        $jObj = new stdClass();
    }
    $jObj->cod = $cod;
    $jObj->desc = $desc;
    return $jObj;
}

function getIdTerritorio($desc, $conn){
    //Ritornare l'id
    $query = "SELECT idTer FROM territori WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            //trasforma la tabella ritornata in un vettore associativo 
            $vet = $ris->fetch_assoc();
            $jObj->idTer = $vet["idTer"];
        }else{  
            $query = "INSERT INTO territori (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                //Richiedo l'id
                /*$query = "SELECT idTer FROM territori WHERE descr='".$desc."'";
                $ris = $conn->query($query);
                if($ris && $ris->num_rows > 0){
                    $vet = $ris->fetch_assoc();
                    $jObj->idTer = $vet["idTer"];
                }*/

                $jObj = getIdTerritorio($desc, $conn);//Sostituisce il commento precedente
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}

function getIdTipoVeicolo($desc, $conn){
    $query = "SELECT idTipoVeicolo FROM tipiveicoli WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            $vet = $ris->fetch_assoc();
            $jObj->idTipoVeicolo = $vet["idTipoVeicolo"];
        }else{  
            $query = "INSERT INTO tipiveicoli (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                $jObj = getIdTipoVeicolo($desc, $conn);
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}

function getIdTipoDato($desc, $conn){
    $query = "SELECT idTipo FROM tipidati WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            $vet = $ris->fetch_assoc();
            $jObj->idTipo = $vet["idTipo"];
        }else{  
            $query = "INSERT INTO tipidati (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                $jObj = getIdTipoDato($desc, $conn);
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}