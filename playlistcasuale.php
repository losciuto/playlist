<?php

// funzione per la creazione degli id casuali da inserire nella palylist
// a cascata richiama funzione per l'inserimento della intestazione della playlist
// e della funzione di inserimento dei titoli creati casualmente
//
// $video = se includere i video musicali o meno
// $musicali = se inserire i brani musicali o meno
// $numeromax = il numero massimo di titoli da inserire
// $nomepl = nome della playlist
// 

function creacasuale($numeromax, $nomepl, $posdir, $video, $audio, $exclude, $includ="", $genre = "", $recenti=false,$director="",$ord="ASC",$media=""){
	global $id;
	$addexclude = "";
    $addinclude = "";
    $addinclud  = "";

    $exclude = trim($exclude);
    $includ  = trim($includ);
    $toexclude = array();
    $toinclud  = array();
    $paroleinclud  = 0;
    $paroleexclude = 0;

    // predefinizioni per le parole da includere e da escludere
    if(strlen($includ) > 0){
	    if(contiene($includ, " ")) $toinclud  = explode(" ", $includ ); else $toinclud[0]  = $includ;
	    $paroleinclud  = count($toinclud);  	
    }
    if(strlen($exclude) > 0){
		if(contiene($exclude," ")) $toexclude = explode(" ", $exclude); else $toexclude[0] = $exclude;
		$paroleexclude = count($toexclude);
	}    

    // confronto tra due array $includ ed $exclude per eliminare
    // le parole da escludere previste nelle parole da includere
    if(strlen($includ) > 0 && strlen($exclude) > 0){
    	$toexclude = array_diff($toexclude, $toinclud);
		$paroleexclude = count($toexclude);    
    	//print_r($toexclude);
    }

	// creazione query per le parole da escludere
	if($paroleexclude > 0){
			
			$nparole = $paroleexclude;
			$ciclo = 0;
			if($nparole > 1){
				foreach($toexclude as $excludeword){
					$ciclo++;
					// crea le singole ricerche
					$addexclude .= " (lower(file) NOT LIKE '%" . strtolower($excludeword) . "%' ) ";
					if($ciclo < $nparole) $addexclude .= " AND "; 
				}
				$addexclude = "(" . $addexclude . ")";
			} else {
				$addexclude = " (lower(file) NOT LIKE '%" . strtolower($toexclude[0]) . "%' ) ";
			}
	}

	// creazione query per le parole da includere
	if($paroleinclud > 0){
			
			$nparole = $paroleinclud;
			$ciclo = 0;
			if($nparole > 1){
				foreach($toinclud as $includword){
					$ciclo++;
					// crea le singole ricerche
					$addinclud .= " (lower(file) LIKE '%" . strtolower($includword) . "%' ) ";
					if($ciclo < $nparole) $addinclud .= " OR "; 
				}
				$addinclud = "(" . $addinclud . ")";
			} else {
				$addinclud = " (lower(file) LIKE '%" . strtolower($toinclud[0]) . "%' ) ";
			}
	}	
	
    // creazione query per i generi da includere
	if(strlen(trim($genre)) > 0){
		$genre = trim($genre);
		$toinclude = array();
		$nparole = 0;
		$ciclo = 0;
		if(contiene($genre," ")) $toinclude = explode(" ", $genre); else $toinclude[0] = $genre;
		$nparole = count($toinclude);
		if($nparole > 1){
			foreach($toinclude as $includeword){
				$ciclo++;
				// crea le singole ricerche
				$addinclude .= " lower(genre) LIKE '%" . strtolower($includeword) . "%'  ";
				if($ciclo < $nparole) $addinclude .= " OR "; 
			}
			$addinclude = "(" . $addinclude . ")";
		} else {
			$addinclude = " lower(genre) LIKE '%" . strtolower($toinclude[0]) . "%'  ";
    	}
	}
    
	// ridefinisco la query in funzione dei flag attivati o meno
	if($video == "on") $addvideo = " 1 "; else $addvideo = ""; // solo tipo video
	if($audio == "on") $addaudio = " tipo='2' "; else $addaudio = ""; // solo tipo audio
	if($video == "on" || $audio == "on") $addwhere = " WHERE "; else $addwhere = ""; // almeno uno dei due tipi
	if($video == "on" && $audio == "on") $addand = " OR "; else $addand = ""; // almeno uno dei due tipi

	if($video != "on" && $audio != "on") $addvideo = " WHERE tipo='0' "; // nessun tipo scelto
	if(($addvideo != "" || $addaudio != "") && $exclude != "") $addexclude = " AND " . $addexclude; // termini esclusi dal path e dal nome del video
	if(($addvideo != "" || $addaudio != "") && trim($genre) != "") $addinclude = " AND " . $addinclude; // per il genere
	if(($addvideo != "" || $addaudio != "") && trim($includ) != "") $addinclud = " AND " . $addinclud; // per i termini da includere
	require("db.php");

	// ridefinizione delle query
    if(strlen($director) > 0) {
        if(trim($director) != "") {
				$addinclude = "";
				$director = trim($director);
				$toinclude = array();
				$nparole = 0;
				$ciclo = 0;
				if(contiene($director," ")) $toinclude = explode(" ", $director); else $toinclude[0] = $director;
				$nparole = count($toinclude);
				if($nparole > 1){
					foreach($toinclude as $includeword){
						$ciclo++;
						// crea le singole ricerche
						$addinclude .= " lower(director) LIKE '%" . strtolower($includeword) . "%'  ";
						if($ciclo < $nparole) $addinclude .= " OR "; 
					}
					$addinclude = "(" . $addinclude . ")";
				} else {
					$addinclude = " lower(director) LIKE '%" . strtolower($toinclude[0]) . "%'  ";
	        	}
		    // query per registi
            //$sql = "SELECT id,name,file,duration FROM video WHERE director like '%" . $director . "%' ORDER BY year ". $ord . " LIMIT " . $numeromax;
            $sql = "SELECT id,name,file,dname,duration FROM video WHERE ". $addinclude . " ORDER BY year ". $ord . " LIMIT " . $numeromax;
        } else {
            $sql = "SELECT id,name,file,dname,duration FROM video WHERE duration = 0 ORDER BY lastmod ". $ord . " LIMIT " . $numeromax; //opzione non documentata che permmette la visione di tutti i titoli che non hanno un file .nfo
        }
    } else if(trim($recenti) == "TRUE") {
		// query per i film inseriti di recente
		$sql = "SELECT id,name,file,dname,duration FROM video " . $addwhere . $addvideo . $addand . $addaudio . $addexclude . $addinclude . $addinclud . " ORDER BY lastmod " . $ord . " LIMIT " . $numeromax;		
	} else {
		// query per la ricerca casuale degli id dei titoli disponibili in archivio
		$sql = "SELECT id,name,file,dname,duration FROM video " . $addwhere . $addvideo . $addand . $addaudio . $addexclude . $addinclude . $addinclud . " ORDER BY random() LIMIT " . $numeromax;
	}
	// per debug
	echo $sql . "\n\n"; // die(" controlla"); // per debug
    //exit;
    // intestazione play list tipo .xspf
    $inizio = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$playl = '<playlist version="1" xmlns="http://xspf.org/ns/0/" xmlns:vlc="http://www.videolan.org/vlc/playlist/ns/0/">' . "\n";
	$titolo = "\t<title>Scaletta del " . date("Y-m-d H:i:s") . "</title>\n";
	$trackListini = "\t<trackList>\n";
	
	$testo = $inizio . $playl . $titolo . $trackListini;

	if ($result = $conn->query($sql)) {
        
        $i = 0;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $i++;
				//$idcasuali[] = $row[0];
				$plnome = rawurlencode($row['name']);
				$ext = substr($row['file'], -4);
                $testo .=   "\t\t<track>\n\t\t\t<location>file:///" . $row['dname'] . $plnome .  $ext . "</location>
            <title>Pl by ML - " . $row['name'] . "</title>
            <duration>" . $row['duration'] . "</duration>\n\t\t</track>\n";			
		}
		$result = null;
	}
	$conn = null;	
    $testo .= "\t</trackList>\n</playlist>\n";
    //echo $testo;die("testo xml");
	if($i > 0) {
        $ok = creafileplaylist($nomepl, $posdir, $testo);
		// se tutto e' ok allora ritorno true, diversamente ritorno false
		if($ok) return true; else return false;
	} else {
		$id = 0; 
		//return false;
		/*
		echo "<pre>";
		echo $testo;
		echo "<pre>";
		*/
		die("Non ho trovato nessuna corrispondenza - riprova!. (id vuoto)\n" . $sql . "\n");
	}
}
function contiene($da, $cosa){
	$ris = false;
	if(strstr(strtolower($da),strtolower($cosa))) $ris = true;
	return $ris;
}
function creafileplaylist($nomepl, $posdir, $testo){
		$file = fopen($posdir . $nomepl, "w");
		fputs($file, $testo);
		fclose($file);
    return true;
    }
// parametri di default
$nomepl = "testpl.xml.xspf";
$video = "on";
$audio ="off";
$numeromax = 5;
$exclude = "cartoni inglesi cd1 cd2 originali vob serie volume originale";
//$genre = "sci-fi fantascenza";
$genre = "";
$posdir = "scalette/";
$media = "smplayer";
$director = "";
$ord = "ASC";
$recenti = "";
$includ="";

// gestione dei parametri da linea di comando
$shortopts  = "";
$shortopts .= "n:";  // Required value
$shortopts .= "e::"; // Optional value
$shortopts .= "g::"; // Optional value
$shortopts .= "m::"; // Optional value
$shortopts .= "f::"; // Optional value
$shortopts .= "d::"; // Optional value
$shortopts .= "u::"; // Optional value
$shortopts .= "r::"; // Optional value
$shortopts .= "o::"; // Optional value
$shortopts .= "i::"; // Optional value
$longopts  = array(
    "required:",     // Required value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
);
$options = getopt($shortopts, $longopts);
foreach($options as $key => $value){
    if($key == "n") $numeromax = $value;
    if($key == "e" && trim($value) != "") $exclude = $value;
    if($key == "g") $genre = $value;
    if($key == "m" && trim($value) != "") $media = $value;    
    if($key == "f" && trim($value) != "") $nomepl = $value;    
    if($key == "d" && trim($value) != "") $posdir = $value;    
    if($key == "u") $recenti = $value;    
    if($key == "r") $director = $value;    
    if($key == "o") $ord = $value;    
    if($key == "i" && trim($value) != "") $includ = $value;    
}
if(strtolower(substr($ord,0,1)) == "d" || trim($ord) == "TRUE") $ord = "DESC"; else $ord = "ASC"; // accetta solo il valore D per DESC, tutti gli altri valori sono per default ASC
if($numeromax == "0") $numeromax = "999999"; // nessun limite in presenza di 0
echo "Numero componenti della playlist (-n): " . $numeromax . 
         ".\nTermini esclusi (-e): " . $exclude . 
         ".\nTermini inclusi (-i): " . $includ . 
         ".\nGeneri (-g): " . $genre . 
         ".\nRegista (-r): " . strlen($director) . 
         ".\nUltimi inseriti (-u): " . $recenti . 
         ".\nOrdine (valido solo se usato con -r o -u) (-o): " . $ord . 
         ".\nMedia (-m): " . $media . 
         ".\nNome file playlist (-f): " . $nomepl . 
         ".\nPosizione della playlist (-d): " .$posdir . "\n";
// funzione per la creazione della playlist
creacasuale($numeromax, $nomepl, $posdir, $video, $audio, $exclude, $includ, $genre, $recenti, $director, $ord, $media);
// esecuzione del media con la playlist creata
shell_exec($media . ' ' . $posdir . '/' . $nomepl . '  &');
exit;
?>
