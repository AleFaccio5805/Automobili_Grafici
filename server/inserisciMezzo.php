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

            //Prelevare l'id tipo dato

        }
    }else{
        //Quando ci sono errori
        $jObj = preparaRisp(-1, "Errore nella query: ".$conn->error);
    }
 

    //4. Costruire la INSERT


    //5. Verificare il risultato


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
            
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}