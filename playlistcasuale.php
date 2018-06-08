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

function creacasuale($numeromax, $nomepl, $video, $audio, $exclude, $genre = ""){
	global $id;
	$addexclude = "";
    $addinclude = "";

// creazione query per le parole da escludere
	if(strlen(trim($exclude)) >0){
			$exclude = trim($exclude);
			$toexclude = array();
			$nparole = 0;
			$ciclo = 0;
			if(contiene($exclude," ")) $toexclude = explode(" ", $exclude); else $toexclude[0] = $exclude;
			$nparole = count($toexclude);
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
	
    // creazione query per i generi da includere
	if(strlen(trim($genre)) >0){
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
	if(($addvideo != "" || $addaudio != "") && $exclude != "") $addexclude = " AND " . $addexclude;
	if(($addvideo != "" || $addaudio != "") && $genre != "") $addinclude = " AND " . $addinclude; // per il genere
	require("db.php");
	// ricerca casuale degli id dei titoli disponibili in archivio
	$sql = "SELECT id,name,file,duration FROM video " . $addwhere . $addvideo . $addand . $addaudio . $addexclude . $addinclude . " ORDER BY random() LIMIT " . $numeromax;
	//echo $sql . "<br>"; die(" controlla"); // per debug
        //exit;
            // intestazione play list tipo .xspf
            $inizio = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$playl = '<playlist xmlns="http://xspf.org/ns/0/" xmlns:vlc="http://www.videolan.org/vlc/playlist/ns/0/" version="1">' . "\n";
			$titolo = "\t<title>Scaletta del " . date("Y-m-d H:i:s") . "</title>\n";
			$trackListini = "\t<trackList>\n";
			
			$testo = $inizio . $playl . $titolo . $trackListini;

	if ($result = $conn->query($sql)) {
        
        $i = 0;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $i++;
				//$idcasuali[] = $row[0];
                $testo .=   "\t\t<track>\n\t\t\t<location>file:///" . $row['file'] .  "</location>\n\t\t\t<duration>" . $row['duration'] . "</duration>\n\t\t</track>\n";			
		}
		$result = null;
	}
	$conn = null;	
    $testo .= "\t</trackList>\n</playlist>\n";
	if($i > 0) {
        $ok = creafileplaylist($nomepl, $testo);
		// se tutto e' ok allora ritorno true, diversamente ritorno false
		if($ok) return true; else return false;
	} else {
		$id = 0; 
		//return false;
		echo "<pre>";
		echo $testo;
		echo "<pre>";
		die("Qualcosa e' andata storta. (idcasuali vuota)");
	}
}
function contiene($da, $cosa){
	$ris = false;
	if(strstr(strtolower($da),strtolower($cosa))) $ris = true;
	return $ris;
}
function creafileplaylist($nomepl,$testo){
		$file = fopen("scalette/" . $nomepl, "w");
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

// gestione dei parametri da linea di comando
$shortopts  = "";
$shortopts .= "n:";  // Required value
$shortopts .= "e::"; // Optional value
$shortopts .= "g::"; // Optional value
$shortopts .= "m::"; // Optional value
$shortopts .= "f::"; // Optional value
$shortopts .= "d::"; // Optional value
$longopts  = array(
    "required:",     // Required value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
    "optional::",    // Optional value
);
$options = getopt($shortopts, $longopts);
foreach($options as $key => $value){
    if($key == "n") $numeromax = $value;
    if($key == "e") $exclude = $value;
    if($key == "g") $genre = $value;
    if($key == "m") $media = $value;    
    if($key == "f") $nomepl = $value;    
    if($key == "d") $posdir = $value;    
}
echo "Numero componenti della playlist (-n): " . $numeromax . 
         ".\nTermini esclusi (-e): " . $exclude . 
         ".\nGeneri (-g): " . $genre . 
         ".\nMedia (-m): " . $media . 
         ".\nNome file playlist (-f): " . $nomepl . 
         ".\nPosizione della playlist (-d): " .$posdir . "\n";
// funzione per la creazione della playlist
creacasuale($numeromax, $nomepl, $video, $audio, $exclude, $genre);
// esecuzione del media con la playlist creata
shell_exec($media . ' ' . $posdir . '/' . $nomepl . '  &');
exit;
?>
