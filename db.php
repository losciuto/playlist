<?php

    $basedati = "video.sq3";
    $dirdb = "";
    $tabella = "video";

try {
    $conn = new pdo('sqlite:' . $dirdb . $basedati);
}
catch( PDOException $Exception ) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    throw new MyDatabaseException( $Exception->getMessage( ) , $Exception->getCode( ) );
}


?>