<?php

    include_once('db.php');
    $col = 0;
    $sql = "SELECT genere FROM generi ORDER BY genere ASC;";
    $result = $conn->query($sql);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo $row['genere'] . ", ";
                $col++;
                if(($col % 4) == 0) echo "\n";
        }
    echo "\n";
    $result = null;


?>