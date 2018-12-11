<?php

class CustomException extends PDOException {
   
    /**
     * Override constructor and set message and code properties.
     * Workaround PHP BUGS #51742, #39615
     */
    public function __construct($message=null, $code=null) {
        $this->message = $message;
        $this->code = $code;
    }
   
}


    $basedati = "video.sq3";
    $dirdb = "";
    $tabella = "video";

try {
    $conn = new pdo('sqlite:' . $dirdb . $basedati);
}catch( PDOException $Exception ) {
    // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
    // String.
    throw new CustomException( $Exception->getMessage( ) , $Exception->getCode( ) );
    
}





?>