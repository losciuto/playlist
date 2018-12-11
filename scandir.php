<?PHP

  global $db, $conn, $truncate_tab;
  
  // effettua un truncate sulla tabella o meno
  $truncate_tab = false;
  
   // aggiorna la base dati o meno uso debug
  $db = true;
  
  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.
  function getFileList($dir, $recurse = FALSE){ 
    global $conn;
      $retval = [];
      // mime type incluse nella scansione
      $video = array('video/x-matroska',
                     'video/x-msvideo',
                     'video/mpeg',
                     'video/mp4',
                     'video/x-flv'
               );
      // add trailing slash if missing
      if(substr($dir, -1) != "/") {
        $dir .= "/";
      }
      // open pointer to directory and read list of files
      $d = @dir($dir) or die("\ngetFileList: Failed opening directory {$dir} for reading\n");
      while(FALSE !== ($entry = $d->read())) {
        // skip hidden files
        if($entry{0} == ".") continue;
        if(is_dir("{$dir}{$entry}")) {
          if($recurse && is_readable("{$dir}{$entry}/")) {
            $retval = array_merge($retval, getFileList("{$dir}{$entry}/", TRUE));
          }
        } elseif(is_readable("{$dir}{$entry}")) {
        	if(in_array(mime_content_type("{$dir}{$entry}"),$video)){
                // esclude l'estensione .sub e l'estensione .vob
                if((strtolower(substr("{$entry}",-4)) != ".sub") && (strtolower(substr("{$entry}",-4)) != ".vob")){
                        $retval[] = [
                            'name' => "'" .  substr("{$entry}",0,-4) . "'",
                            'file' => "'" . "{$dir}{$entry}" . "'",
                            'dname' => "'" . "{$dir}". "'",
                            'size' => "'" . filesize("{$dir}{$entry}") . "'",
                            'lastmod' => "'" . date ("Y-m-d H:i:s", filemtime("{$dir}{$entry}"))."'"
                        ];
                        echo ".";
                }
        	} else {
        	     //echo "File saltato: " . mime_content_type("{$dir}{$entry}") . "\n";
          }
        }
      }
      $d->close();
      return $retval;
  }

if($db) include_once("db.php");
// gestione dei parametri da riga di comando
$numdir = count($argv) -1;
$inizio = 1;

if((is_numeric( $argv[1] ) && $argv[1] == '0') || $argv[1] == "FALSE") {

  $truncate_tab = false;
  echo "I dati verranno aggiunti (messi in coda) alla tabella " . $tabella . "\n";

}
if((is_numeric( $argv[1] ) && $argv[1] == '1') || $argv[1] == "TRUE") {

  $truncate_tab = true;
  echo "La tabella " . $tabella . " verrà troncata (truncate)!!!\n";
}

foreach($argv as $key => $contenuto){
  if(trim($contenuto) != "" && $key >= '2'){
    //echo " Key: " . $key . " Contenuto: " .$contenuto . "\n";
    echo "\nFase raccolta dati dalla periferica directory: " . $contenuto . "...\n";    
    $dir = getFileList($contenuto,TRUE);
    echo "\nFase ordinamento vettore...\n";
    $ndir = array_sort($dir, 'file', SORT_ASC);
    if($db){
        echo "Fase aggiornamento base dati...";
        include('input.php');
    } else {
        // fase debug
        echo '<pre>';
        print_r($ndir);
        echo '</pre>';
    }    
  }
}

$conn = null;
echo "\nFine ricerca.\n";
// fine scandir

// funzione ordinamento vettori
// prelevato da php.net
function array_sort($array, $on, $order=SORT_DESC)
{
    $new_array = array();
    $sortable_array = array();
    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }
        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }
        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
}
?>
