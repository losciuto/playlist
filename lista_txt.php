<?php
    $na_ord = "name";
    $ord = "ASC";
     //echo "ho ricevuto $argc argomenti\n";
     for ($i = 1; $i <  $argc ; $i++) {
        //echo "il valore dell'argomento $i è ".$argv[$i]."\n";
        if(strtolower(substr($argv[$i],0,2)) == "-g") $na_ord = "genre";
        if(strtolower(substr($argv[$i],0,2)) == "-d") $ord = "DESC";
    }
 
 
        include_once('db.php');
        $myfile = fopen("lista.csv", "w+") or die("Non posso aprire il file!");

        $sql = "SELECT id,name,genre,director,year FROM " . $tabella . " ORDER BY lower(" . $na_ord . ") " . $ord;
        $result = $conn->query($sql);
        
        //echo "\n" . $sql . "\n";
    
          echo "Scrivo lista video nella base dati...";  
            $txt = "Progr.;Titolo;Genere;Regista;Anno\n";
            fwrite($myfile, $txt);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo ".";
                $txt = $row['id'] . ";" . $row['name'] . ";" . $row['genre'] . ";" . $row['director'] . ";" . $row['year'] . "\n";
                fwrite($myfile, $txt);
            }

        $result = null;
        fclose($myfile);

        echo "\nFine scrittura lista\n";
?>
