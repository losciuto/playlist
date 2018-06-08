<?php
    include("db.php");

    // elimina tabella video se esiste
    $sql = 'DROP TABLE ' . $tabella;
    $conn->exec($sql);

        // crea struttura della tabella
        $sql = "CREATE TABLE " . $tabella . "(
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT, 
                    file TEXT,
                    type TEXT,
                    size TEXT,
                    lastmod NUMERIC,
                    genre TEXT,
                    duration INTEGER
                    )";
        $conn->exec($sql);
        
        // aggiunge indice basato sul campo nome
        $sql="CREATE INDEX name_idx ON " . $tabella . " (name);";
        $conn->exec($sql);
        
        // creatabella generi
        $sql = "CREATE TABLE generi ( genere TEXT )";
        $conn->exec($sql);
        
        // aggiunge indice univoco alla tabella generi
        $sql = "CREATE UNIQUE INDEX genere_idx ON generi (genere)";
        $conn->exec($sql);
        
        $conn = null;
?>