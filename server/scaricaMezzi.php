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

    //3 Verificare se non esiste già il record
    $query = "SELECT idMezzo, t.descr as territorio, td.descr as tipodati, 
        tv.descr as tipoveicolo, anno, val  
        FROM mezzi as m, territori as t, tipidati as td, tipiveicoli as tv
        WHERE m.idTer = t.idTer AND m.idTipo = td.idTipo AND
            m.idTipoVeicolo = tv.idTipoVeicolo";
    $ris = $conn->query($query);
    if($ris){
        $mezzi = array();
        $cont =0;
        if($ris->num_rows > 0){
            while($vet = $ris->fetch_assoc()){
                /*
                    [
                        {idMezzo:...., territorio:.... },
                        {}
                    ]
                */

                //METODO1
                $mezzi[$cont] = new stdClass();
                $mezzi[$cont]->idMezzo =  $vet["idMezzo"];
                $mezzi[$cont]->territorio =  $vet["territorio"];
                $mezzi[$cont]->tipodati =  $vet["tipodati"];
                $mezzi[$cont]->tipoveicolo =  $vet["tipoveicolo"];
                $mezzi[$cont]->anno =  $vet["anno"];
                $mezzi[$cont]->val =  $vet["val"];
                $cont++;

                //METODO 2
                /*$mezzo = new stdClass();
                $mezzo->idMezzo =  $vet["idMezzo"];
                $mezzo->territorio =  $vet["territorio"];
                $mezzo->tipodati =  $vet["tipodati"];
                $mezzo->tipoveicolo =  $vet["tipoveicolo"];
                $mezzo->anno =  $vet["anno"];
                $mezzo->val =  $vet["val"];
                array_push($mezzi, $mezzo);*/
            }
            $jObj->mezzi = $mezzi;
        }else{
            $jObj = preparaRisp(-1, "Non ho trovato mezzi");
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