<?php
//basedati.php
    include("db.php");

    // elimina tabella video se esiste
    $sql = 'DROP TABLE ' . $tabella;
    $conn->exec($sql);

    $sql = 'VACUUM ' . $tabella;
    $conn->exec($sql);     

    echo "Cancella tabella (drop table): " . $tabella . "\n";

        // crea struttura della tabella
        $sql = "CREATE TABLE " . $tabella . "(
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT, 
                    file TEXT,
                    dname TEXT,
                    size TEXT,
                    lastmod NUMERIC,
                    genre TEXT,
                    director TEXT,
                    year TEXT,
                    duration INTEGER
                    )";
        $conn->exec($sql);

        echo "Ricrea la tabella:" . $tabella . "\n";
        
        // aggiunge indice basato sul campo nome
        $sql="CREATE INDEX name_idx ON " . $tabella . " (name);";
        $conn->exec($sql);

        // aggiunge indice unico per il campo file
        $sql = "CREATE UNIQUE INDEX IF NOT EXISTS unique_file ON ". $tabella . " (file);";
        $conn->exec($sql);

        echo "Ricrea indirizzi della tabella: " . $tabella . "\n";        
        
        // elimina tabella video se esiste
        $sql = 'DROP TABLE generi';
        $conn->exec($sql);

        $sql = 'VACUUM generi';
        $conn->exec($sql);     


        echo "Cancella tabella (drop table): generi\n";

        // creatabella generi
        $sql = "CREATE TABLE generi (
                    genere TEXT KEY UNIQUE, 
                    volte INTEGER DEFAULT 1)";
        $conn->exec($sql);

        echo "Ricrea la tabella: generi\n";        
        
        // aggiunge indice univoco alla tabella generi
        $sql = "CREATE UNIQUE INDEX genere_idx ON generi (genere)";
        $conn->exec($sql);
        echo "Ricrea indirizzo della tabella: generi\n";                
        
        $conn = null;
        echo "\nFine creazione DB.\n"
?>