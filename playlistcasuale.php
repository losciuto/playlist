<?php
//playlistcasuale.php
// funzione per la creazione degli id casuali da inserire nella palylist
// a cascata richiama funzione per l'inserimento della intestazione della playlist
// e della funzione di inserimento dei titoli creati casualmente
//
// $video = se includere i video musicali o meno (funzione per uso futuro)
// $audio = se inserire i brani musicali o meno (funzione per uso futuro)
// $numeromax = il numero massimo di titoli da inserire
// $nomepl = nome delle playlist
// $posdir = cartella di memorizzazione delle playlist
// $exclude = termini da *escludere* nella composizione delle playlist
// $includ =  termini da *includere* nella composizione delle playlist
// $genre = generi da includere nella composizione delle playlist
// $recenti = cambia l'ordine di composizione delle playlist da random() a lastmod
// $director = elenco dei registi con cui comporre le playlist
// $ord = determina l'ordine crescente o descrescente delle playlist
// di default crea il formato .xspf e i formati .m3u e .pls non estesi ovveto formato testo semplice

function creacasuale($numeromax, $nomepl, $posdir, $video="on", $audio="off", $exclude, $includ="", $genre="", $recenti=false, $director="", $ord="ASC"){

	$addexclude = "";
    $addinclude = "";
    $addinclud  = "";

    $exclude = trim($exclude);
    $includ  = trim($includ);
    $toexclude = array();
    $toinclud  = array();
    $paroleinclud  = 0;
    $paroleexclude = 0;

    // ** per una migliore lettura del programma sarebbe necessario creare 
    // una funzione che ritorna ogni componente per la query

    // ** predefinizioni per le parole da includere e da escludere
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
    }

	// ** creazione query per le parole da escludere
	if($paroleexclude > 0){
		$nparole = $paroleexclude;
		$ciclo = 0;
		if($nparole > 1){
			// ciclo per più parole da escludere
			foreach($toexclude as $excludeword){
				$ciclo++;
				// crea le singole componenti
				$addexclude .= " (lower(file) NOT LIKE '%" . strtolower($excludeword) . "%' ) ";
				if($ciclo < $nparole) $addexclude .= " AND "; 
			}
			$addexclude = "(" . $addexclude . ")";
		} else {
			// componente per singola parola da escludere
			$addexclude = " (lower(file) NOT LIKE '%" . strtolower($toexclude[0]) . "%' ) ";
		}
	}

	// ** creazione query per le parole da includere
	if($paroleinclud > 0){
		$nparole = $paroleinclud;
		$ciclo = 0;
		if($nparole > 1){
			// ciclo per più parole da includere
			foreach($toinclud as $includword){
				$ciclo++;
				// crea le singole compomenti 
				$addinclud .= " (lower(file) LIKE '%" . strtolower($includword) . "%' ) ";
				if($ciclo < $nparole) $addinclud .= " OR "; 
			}
			$addinclud = "(" . $addinclud . ")";
		} else {
			// componente per singola parola da includere			
			$addinclud = " (lower(file) LIKE '%" . strtolower($toinclud[0]) . "%' ) ";
		}
	}	
	
    // ** creazione query per i generi da includere
	if(strlen(trim($genre)) > 0){
		$genre = trim($genre);
		$toinclude = array();
		$nparole = 0;
		$ciclo = 0;
		// definizione del vettore dei generi
		if(contiene($genre," ")) $toinclude = explode(" ", $genre); else $toinclude[0] = $genre;
		$nparole = count($toinclude);
		if($nparole > 1){
			// ciclo per più generi da includere			
			foreach($toinclude as $includeword){
				$ciclo++;
				// crea le singole componenti
				$addinclude .= " lower(genre) LIKE '%" . strtolower($includeword) . "%'  ";
				if($ciclo < $nparole) $addinclude .= " OR "; 
			}
			$addinclude = "(" . $addinclude . ")";
		} else {
			// componente per singolo genere da includere						
			$addinclude = " lower(genre) LIKE '%" . strtolower($toinclude[0]) . "%'  ";
    	}
	}
    
	// defnisco la query in funzione dei flag attivati o meno e/o dei parametri passati
	if($video == "on") $addvideo = " 1 "; else $addvideo = ""; // solo tipo video
	if($audio == "on") $addaudio = " 2 "; else $addaudio = ""; // solo tipo audio
	if($video == "on" || $audio == "on") $addwhere = " WHERE "; else $addwhere = ""; // almeno uno dei due tipi
	if($video == "on" && $audio == "on") $addand = " OR "; else $addand = ""; // almeno uno dei due tipi

	if($video != "on" && $audio != "on") $addvideo = " WHERE tipo='0' "; // nessun tipo scelto
	if(($addvideo != "" || $addaudio != "") && $exclude != "") $addexclude = " AND " . $addexclude; // termini da escludere dal path e dal nome del video
	if(($addvideo != "" || $addaudio != "") && trim($genre) != "") $addinclude = " AND " . $addinclude; // per il genere
	if(($addvideo != "" || $addaudio != "") && trim($includ) != "") $addinclud = " AND " . $addinclud; // per i termini da includere perentoriamente

	// importa apertura della base dati
	require("db.php");

	// completamento delle query
    if(strlen($director) > 0) {
    	// definizione della query per i registi
        if(trim($director) != "") {
				$addinclude = "";
				$director = trim($director);
				$toinclude = array();
				$nparole = 0;
				$ciclo = 0;
				// definizione del vettore dei generi				
				if(contiene($director," ")) $toinclude = explode(" ", $director); else $toinclude[0] = $director;
				$nparole = count($toinclude);
				if($nparole > 1){
					// ciclo per più registi da includere			
					foreach($toinclude as $includeword){
						$ciclo++;
						// crea le singole ricerche
						$addinclude .= " lower(director) LIKE '%" . strtolower($includeword) . "%'  ";
						if($ciclo < $nparole) $addinclude .= " OR "; 
					}
					$addinclude = "(" . $addinclude . ")";
				} else {
					// componente per singolo regista											
					$addinclude = " lower(director) LIKE '%" . strtolower($toinclude[0]) . "%'  ";
	        	}

		    // query completa per registi
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
	echo "\n" . $sql . "\n\n"; // die(" controlla"); // per debug
    //exit;

	// composizione delle playlist
	//
    // intestazione play list tipo .xspf in xml
    // 
    $inizio = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$playl = '<playlist version="1" xmlns="http://xspf.org/ns/0/" xmlns:vlc="http://www.videolan.org/vlc/playlist/ns/0/">' . "\n";
	$titolo = "\t<title>Scaletta del " . date("Y-m-d H:i:s") . "</title>\n";
	$trackListini = "\t<trackList>\n";
	
	$testo = $inizio . $playl . $titolo . $trackListini;
	$testotxt = "";
	// testate altri formati
	if(playlistformat("m3u")) $testom3u = "#EXTM3U\n\n"; else $testom3u = "";
	if(playlistformat("pls")) $testopls = "[playlist]\n\n"; else $testopls = "";

	if ($result = $conn->query($sql)) {
        // ciclo inserimento singolo titolo nelle playlist
        $i = 0;
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $i++;
				// codifico per la trasformazione delle accentate e caratteri speciali in hex utf-8
				$name8 = rawurlencode($row['file']);
				// riga composta dall'indirizzo del file, del nome e della durata (calcolata in modo fittizzio)
                $testo .=   "\t\t<track>\n\t\t\t<location>file:///" . $name8 . "</location>
            		<title>Pl by ML - " . $row['name'] . "</title>
           		<duration>" . $row['duration'] . "</duration>\n\t\t</track>\n";
            	// playlist .m3u e pls non estesa ovvero in list in formato txt
            	$testotxt .= $row['file'] . "\n";
            	// riga formato playlist m3u esteso
            	if(playlistformat("m3u")){
	            	// composizione della playlist estesa .m3u, piu' semplice del formato xml
	            	// testata per ogni riga composta da durata e nome del titolo
	            	//$testom3u .= "#EXTINF:" . $row['duration'] . "," . $row['name'] . "\n"; // inibita
	            	// per comodità preferisco fissare a -1 la durata, visto che è calcolata in modo fittizio
	            	$testom3u .= "#EXTINF:-1,Pl by ML - " . $row['name'] . "\n";
	            	// riga utilizzata per la lettura del file video
	            	$testom3u .= $row['file'] . "\n";
            	}
            	// riga formato playlist pls estesa
            	if(playlistformat("pls")){
            		// composizione della playlist estesa .pls
            		$testopls .= "File" . $i ."=" . $row['file'] . "\n";
            		$testopls .= "Title" . $i ."=" . "Pl by ML - " . $row['name'] . "\n";
            		//$testopls .= "Length" .$i . "=" . $row['duration'] . "\n\n"; // inibita
            		$testopls .= "Length" .$i . "=-1\n\n";
            	}
		}
		$result = null;
	}
	$conn = null;	
    $testo .= "\t</trackList>\n</playlist>\n";
    if(playlistformat("pls")) $testopls .= "\nNumberOfEntries=" . $i . "\n\nVersion=2\n";
    //echo $testo;die("testo xml"); // debug
    // se è stata trattata almeno uno riga
	if($i > 0) {
		// richiamo a funcione che crea le playlist
        $ok = creafileplaylist($nomepl, $posdir, $testo, $testotxt, $testom3u, $testopls);
		// se tutto e' ok allora ritorno true, diversamente ritorno false
		if($ok) return true; else return false;
	} else { // se non ho trovato nessuna corrispondenza allora seganlo l'anomalia
		/*  debug
		echo "<pre>";
		echo $testo;
		echo "<pre>";
		*/
		die("Non ho trovato nessuna corrispondenza - riprova!. (id vuoto)\n" . $sql . "\n");
	}
}

// funzione di comodo per verificare il conetunuto di una stringa
function contiene($da, $cosa){
	$ris = false;
	if(strstr(strtolower($da),strtolower($cosa))) $ris = true;
	return $ris;
}

// scrive le playlist nella cartella di destinazione
function creafileplaylist($nomepl, $posdir, $testo, $testotxt, $testom3u="", $testopls=""){
		// scrive playlist in formato .xspf
	    file_put_contents($posdir . $nomepl . ".xspf", $testo);		
		// scrive playlist in formato .m3u e pls non estesi ovvero formato txt
	    file_put_contents($posdir . $nomepl . ".pls.m3u.txt", $testotxt);
	    // altri formati (.m3u estesa e .pls estesa)
		if(playlistformat("m3u")){
			// scrive playlist formato .mu3 estesa
	        file_put_contents($posdir . $nomepl . ".m3u", $testom3u);
        }
        if(playlistformat("pls")){
        	// provo a convertire in codifica utf-8 il testo, non sapendo da che codifica ha origine
        	$testopls = iconv(mb_detect_encoding($testopls, mb_detect_order(), true), "UTF-8", $testopls);
			// scrive playlist formato .pls estesa
	        file_put_contents($posdir . $nomepl . ".pls", $testopls);        	
        }		
    return true;
}

// funzione per stabilire se il formato playlist è da creare o meno
// $formato = stringa che riassume la fattibilità di un formato
function playlistformat($formato){
	global $formati;
	if(in_array($formato,$formati)) return true;
	return false;
}

// parametri di default
$video = "on"; // parametro futuro 
$audio ="off"; // parametro futuro
$nomepl = "pl.xml"; // nome del file di playlist
$numeromax = 5; // numero massimo di video nella playlist
$exclude = "cartoni inglesi cd1 cd2 originali vob serie volume originale"; // termini esclusi
$genre = ""; // generi
$posdir = "scalette/"; // cartella di default dove trovare la playlist
$media = "smplayer"; // player di default
$director = ""; // registi
$ord = "ASC"; // ordine di default
$recenti = ""; // se recenti o meno
$includ=""; // termini inclusi
$formati = array("m3u");

// gestione dei parametri da linea di comando
$shortopts  = "";
$shortopts .= "n:";  // Opzional value $numeromax
$shortopts .= "e::"; // Optional value $exclude
$shortopts .= "g::"; // Optional value $genre
$shortopts .= "m::"; // Optional value $media
$shortopts .= "f::"; // Optional value $nomepl
$shortopts .= "d::"; // Optional value $posdir
$shortopts .= "u::"; // Optional value $recenti
$shortopts .= "r::"; // Optional value $director
$shortopts .= "o::"; // Optional value $ord
$shortopts .= "i::"; // Optional value $includ
$shortopts .= "p::"; // Optional value $formati

$longopts  = array(
    "optional::",     // Optional value
    "optional::",    // Optional value
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
    if($key == "m" && trim($value) != "") $media  = $value;    
    if($key == "f" && trim($value) != "") $nomepl = $value;    
    if($key == "d" && trim($value) != "") $posdir = $value;    
    if($key == "u") $recenti = $value;    
    if($key == "r") $director = $value;    
    if($key == "o") $ord = $value;    
    if($key == "i" && trim($value) != "") $includ  = $value;    
    if($key == "p" && trim($value) != "") $formati = explode('-', $value);    
}

if(strtolower(substr($ord,0,1)) == "d" || trim($ord) == "TRUE") $ord = "DESC"; else $ord = "ASC"; // accetta solo il valore D per DESC, tutti gli altri valori sono per default ASC
// prepara i formati per la visualizzazione
$formatxt = "";
if(is_array($formati)){
	foreach($formati as $tformat) { $formatxt .= $tformat . " "; }
} 
// in caso di zero al parametro numero di filmati da visualizzare, lo imposta al massimo di 9999
if($numeromax == "0") $numeromax = "9999"; // nessun limite in presenza di 0
// visualizzazione della sintassi e dei parametri effettivamente elaborati
echo "Numero componenti della playlist (-n): " . $numeromax . 
         ".\nTermini esclusi (-e): " . $exclude . 
         ".\nTermini inclusi (-i): " . $includ . 
         ".\nGeneri (-g): " . $genre . 
         ".\nRegista (-r): " . strlen($director) . 
         ".\nUltimi inseriti (-u): " . $recenti . 
         ".\nOrdine (valido solo se usato con -r o -u) (-o): " . $ord . 
         ".\nMedia (-m): " . $media . 
         ".\nNome file playlist (-f): " . $nomepl . 
         ".\nPosizione della playlist (-d): " .$posdir .
         ".\nTipi di playlist (-p): " . $formatxt . "\n";
// funzione per la creazione della playlist
creacasuale($numeromax, $nomepl, $posdir, $video, $audio, $exclude, $includ, $genre, $recenti, $director, $ord);
// esecuzione del media con la playlist creata
shell_exec($media . ' ' . $posdir . '/' . $nomepl . '.xspf  &');
exit;
?>
