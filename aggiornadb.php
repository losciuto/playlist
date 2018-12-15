<?php
include_once('db.php');

// funzione di ricerca file .nfo associati al video
function cerca_nfo($id,$file,$conn){
    // sostituisce l'estenzione del file video con .nfo
    $nfile = substr($file,0,-4) . ".nfo";
    // se il file .nfo associato è presente...
    if(is_file($nfile)){
        //echo $nfile . " trovato!<br>\n";
        // ne legge id dati
        xml_parser($id,$nfile,$conn);
        
    } 
}

// parser xml per i file .nfo
function xml_parser($id,$file,$conn){
    $genre = "";
    // legge il contenuto del file .nfo (in formato xml)
    $xml=simplexml_load_file($file) or die("Error: Cannot create object");
    // ricerca il nodo generi
    // è possibile che il video in questione
    // sia catalogato in più generi
    $g_cnt = count($xml->genre);
    // inizio cilo in presenza di più generi
    for($i = 0; $i < $g_cnt; $i++) {
      // aggiona tabella generi disponibili
      tabgenere($xml->genre[$i],$conn);
      // crea stringa generi separati da blank
      $genre .= " " . $xml->genre[$i]; 
    } 
    $genre = trim($genre);
    // leggo durata, regista ed anno di uscita
    $duration = $xml->runtime;
    $director = $xml->director;
    $year = $xml->year;
    // eventuali altri dati per futura implementazione del record 
    // $xml->rating
    // $xml->plot // trama
    // $xml->country
    //...
    // aggiorno con i su indicati dati il record del vido in questione
    if(strlen($genre) > 0) aggiornagenere($id,$genre,$conn,$duration,$director,$year);
}

function tabgenere($gen,$conn){
    
    $gen = trim($gen);
    $i = 0;
  
    $sql = "INSERT OR REPLACE INTO generi (genere,volte) VALUES ('" . $gen . "',
    ifnull((SELECT volte FROM generi WHERE genere = '" . $gen . "'), 0) + 1);";

    try {
        if($conn->query($sql)) {} else {
            $errore = "";
            foreach( $conn->errorInfo() as $parte){
                    $errore .= $parte . " ";
                }
            die("\n".$errore. "\n" .$sql ."\n");
        }
    }catch( PDOException $Exception ) {
        // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
        // String.
        throw new CustomException( $Exception->getMessage( ) , $Exception->getCode( ) );
    }
}

// funzione di aggiornamento record del video corrente
function aggiornagenere($id,$genre,$conn,$duration,$director,$year){

    $sql = "UPDATE video SET genre='" . $genre . "',duration='" . $duration . "',director='" . $director . "',year='" . $year . "' WHERE id='" . $id ."'";
    $conn->query($sql);   
}

// inizio applicazione

// azzero la tabella generi
$sql = 'delete from generi';
$conn->query($sql);

$sql = 'VACUUM generi';
$conn->query($sql);  

// scansione tabella video per associare i dati grabbati dai file .nfo
// query di loop
$sql = "SELECT id,file FROM " . $tabella . " ORDER BY file ASC;";
$result = $conn->query($sql);

echo "Scansione base dati...\n";   
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo ".";
    // ricerca del file .nfo che ha lo stesso nome
    // del video, ma estensione .nfo
    cerca_nfo($row['id'],$row['file'],$conn);
}

// pone a zero la durata per tutti i video non censiti con i file .nfo
$sql = "UPDATE video SET duration=0 WHERE duration is null"; 
$conn->query($sql);

$result = null;
echo "\nFine aggiornamento.\n\n"
?>