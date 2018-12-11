<?php
//generi.php
    $cartella    = "./params/";
    $scrivi      = true;
    $generi      = "";
    $colonna     = "genere";
    $ordinamento = "ASC";
    
    $col = "genere";
    $ord = "ASC";

    $shortopts = "c:";  // Optional value
    $shortopts .= "o:";  // Optional value

    $longopts  = array("optional::","optional::");

    $options = getopt($shortopts, $longopts);
    foreach($options as $key => $value){
        if($key == "c" && trim ($col) != "") $col = $value;         
        if($key == "o" && trim ($ord) != "") $ord = $value; 
    }
    
    if(strtolower($col) == "qty") $colonna = "volte";
    if(strtolower($ord) == "desc") $ordinamento = "DESC";

    echo "\n\nElenco generi e quantitativo dei film corrispondenti\n";
    echo "Esempio sintassi: php generi.php -oDESC -cQTY\n";
    echo "Il default, ovvero senza parametri, è ordine ascendente per genere\n";
    echo "Opzione attuale:\n";
    echo "ordinati per " . $colonna . " in modo " . $ordinamento . "endente\n\n";

    include_once('db.php');
    $sql = "SELECT genere, volte FROM generi ORDER BY " . $colonna . " " . $ordinamento . ";";
    $result = $conn->query($sql);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['genere'] . ", " . $row['volte'] . "\n";
        if($scrivi){
            $generi .= $row['genere'] . ",";
        }
    }
    if($scrivi && trim($generi) != ""){
        file_put_contents("params/generi.txt", $generi);        
    }
    
    $result = null;
    echo "\nFine lista\n";

?>