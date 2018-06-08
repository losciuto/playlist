<?php

function cerca_nfo($id,$file,$conn){
    $nfile = substr($file,0,-4) . ".nfo";
    if(is_file($nfile)){
        //echo $nfile . " trovato!<br>\n";
        xml_parser($id,$nfile,$conn);
        
    }    
}

function xml_parser($id,$file,$conn){
    $genre = "";
    $xml=simplexml_load_file($file) or die("Error: Cannot create object");
  // print_r($xml);
   // if($id <= 100){
        $g_cnt = count($xml->genre);
        for($i = 0; $i < $g_cnt; $i++) {
          // aggiona tabella generi disponibili
          tabgenere($xml->genre[$i],$conn);
          $genre .= " " . $xml->genre[$i];
          
        } 
        $genre = trim($genre);
        if(strlen($genre) > 0) aggiornagenere($id,$genre,$conn);
        // $xml->year; // anno
        // $xml->rating
        // $xml->plot // trama
        // $xml->country
        //...
        //exit;
    //}
}

function tabgenere($gen,$conn){
    $sql = "REPLACE INTO generi (genere) VALUES ('" . $gen . "')";
    $conn->query($sql);
}

function aggiornagenere($id,$genre,$conn){

    $sql = "UPDATE video SET genre='" . $genre . "' WHERE id='" . $id ."'";
    $conn->query($sql);
    
}

    include_once('db.php');
    $sql = "SELECT id,file FROM " . $tabella . " ORDER BY file ASC;";
    $result = $conn->query($sql);
          echo "scansione base dati...";   
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo ".";
                cerca_nfo($row['id'],$row['file'],$conn);
            }
    $result = null;
?>