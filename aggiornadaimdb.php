<?php
include_once('lib/simple_html_dom.php');

// funzione di ricerca su IMDb
function scraping_find_IMDB($url,$originale) {
    
    if($html = file_get_html($url)){

        $ret['link'] = "";

        $res = strtolower(trim($html->find('h1[class="findHeader"]',0)->innertext));

        if(substr($res, 0, 16) === "no results found"){
	    	return $ret;
	    }

	    // lista ricercata        
        foreach($html->find('td[class="result_text"]') as $td){
            //log_op("Innertext: " .$td->innertext);
            //log_op("Plaintext: " .$td->plaintext);
            $titolo = $td->plaintext;
            if (soundex($titolo) == soundex($originale)){
                $ret['link'] = $td->innertext;
                log_op("Titolo originale: " . $originale, "no");
                log_op("Link: " . $ret['link'], "no");
                break;
            }
        }

	    // clean up memory
	    $html->clear();
	    unset($html);
	} else {
		$ret['link'] = "";
	}

    return $ret;
}

function scraping_IMDB($url) {

    // create HTML DOM
   if($html = file_get_html($url)){

        $ret['Genere'] = "";

        // get title
        $ret['Titolo'] = $html->find('title', 0)->innertext;

        // get various
        $ret['Rating'] = trim(strip_tags($html->find('strong[title=] ', 0)->innertext));
        $ret['Locandina'] = trim($html->find('div[class="poster"]',0)->innertext);
        $ret['Trama'] = trim(strip_tags($html->find('div[class="inline canwrap"]',0)->innertext));
        $directors = str_replace("Director: ", '', trim(strip_tags($html->find('div[class="credit_summary_item"]', 0)->innertext)));
        $directors = str_replace("Directors: ", '', $directors);
        $ret['Regia'] = str_replace(",","", $directors);
        $ret['Durata'] = str_replace(" min",'',trim(strip_tags($html->find('time[datetime]',1)->innertext)));

        // get genres
        foreach($html->find('div[class="see-more inline canwrap"]') as $div) {

            $key = 0;
            $val = '';

            foreach($div->find('*') as $node) {
                $key++;

                if ($node->tag=='a' && $node->plaintext!='Certificate:')
                    $val .= trim(str_replace("\n", '', $node->plaintext)) . ' ';

                if ($node->tag=='text')
                    $val .= trim(str_replace("\n", '', $node->plaintext)) . ' ';
                    
            }

            $ret['Genere'] = trim($val);
        }
        
        // clean up memory
        $html->clear();
        unset($html);
    }

    return $ret;
}


function cerca_su_IMDb($id,$datrovare="",$conn){
	global $buonfine;
	global $noris;

    $base_scraping_url = "https://www.imdb.com/"; 

    // bonifica stringa da ricercare
    $datrovare = trim(strtolower($datrovare));
    $originale = $datrovare;

    // vettore delle parole e caratteri da eliminare dal titolo
    $daeliminare = array(
    "/[._-]/",
    "/\[/",
    "/\]/",  
    "/,/",  
	'/ +/',
	//'/ [0-9]{1} /',
	"/ita-eng/",    
    "/[._ ]-[._ ]/",
    "/[._ ]italia[._ ]/",    
    "/[._ ]italiano[._ ]/",    
    "/[._ ]italian[._ ]/",    
    "/[._ ]ita[._ ]/",
    "/[._ ]ita/",
    "/[._ ]eng[._ ]/",
    "/[._ ]english[._ ]/",    
    "/[._ ]eng/",
    "/[._ ]fre[._ ]/",
    "/[._ ]fra[._ ]/",
    "/[._ ]esp[._ ]/",
    "/[._ ]spa[._ ]/",
    "/[._ ]ger[._ ]/",
    "/[._ ]deu[._ ]/",
    "/[._ ]heb[._ ]/",
    "/[._ ]jap[._ ]/",
    "/[._ ]chn[._ ]/",
    "/[._ ]jap/",
    "/[._ ]hungarian/",
    "/[._ ]heb/",
    "/[._ ]nor/",
    "/[._ ]tur/",
    "/[._ ]chn/",
    "/[._ ]sub[._ ]/",
    "/[._ ]subs[._ ]/",
    "/subbed/",
    "/multilang/",
    "/chapters/",
    "/telesync/",
    "/webdl/",
    "/web dl/",
    "/webrip/",
    "/webmux/",
    "/dlmux/",
    "/brrip/",
    "/dbrip/",
    "/bdmux/",    
    "/x265/",
    "/x264/",
    "/h265/",
    "/h\.264/",
    "/h264/",
    "/xvid-genisys/",
    "/xvid/",
    "/divx/",
    "/480p/",
    "/720p/",
    "/1080p/",
    "/hdtv/",
    "/bdrip/",
    "/dvdscr/",
    "/dvd/",
    "/bluray/",
    "/bluworld/",
    "/blu ray/",
    "/ddv/",
    "/aac-bg/",
    "/aac/",
    "/dts/",
    "/avs/",
    "/sva/",
    "/vmr/",
    "/cam/",
    "/5[. ]1/",    
    "/2[. ]0/",    
    "/ac3[.]5[. ]1/",
    "/ac3/",
    "/ h$/",
    "/[._ ]icv[._ ]/",
    "/[._ ]sd[._ ]/",
    "/[._ ]mircrew[._ ]/",
    "/[._ ]mircrew/",
    "/[._ ]icv-mircrew[._ ]/",
    "/[._ ]icv-mircrew/",
    "/[._ ]nofaith/",
    "/[._ ]crusaders/",
    "/multisub/",
    "/mp4/",
    "/mp3/",
    "/icn/",
    "/idn/",
    "/264/",
    "/ min/",
    "/bd/",
    "/dd/",
    "/md/",
    "/rip/",
    "/hd/",
    "/edmz/",
    "/earine/",
    //"/ mt/",
    "/paso77/",
    "/foracrew/",
    "/titanscrew/",
    "/crew/",
    "/nahom/",
    "/bamax71/",
    "/bymonello78/",
	"/hevc\(teampremiumcracking\)/",
	"/prime mt/",
	"/race mt/",
	"/mux by little boy/",
	"/ba79/",
	"/\(by phadron\)/",
	"/zmachine/",
	"/bybrigante/",
	"/mux by robbyrs/",
	"/newzone/",
	"/t4p3/",
	"/by salvo/",
	"/cb01/",
	"/trtd team/",
	"/republic/",
	"/teampremium/",
	"/stv pdtv foolish cr3w/",
	"/by caccola/",
	"/by max/",
	"/nuita/",
	"/flash mt/",
	"/iperb/",
	"/ddlnextgeneration com/",
	"/morpheus/",
	"/cineblog/",
	"/blackbit/",
    "/tntvillage/",
    "/[0-9]{4}/",
    "/[0-9]{3}/",
    );
    
    if($datrovare != "") {

        $datrovare = preg_replace($daeliminare,' ',$datrovare,-1);
        $datrovare = preg_replace('#\s*\(.+\)\s*#U', "", $datrovare);
        $datrovare = trim($datrovare);
        $datrovare = preg_replace('/ +/',' ',$datrovare,-1);
        $datrovare = preg_replace('/\( \)/','',$datrovare,-1);
        $datrovare = preg_replace('/ +/',' ',$datrovare,-1);

        //log_op($datrovare);
        //return;

        // bonifica da spazi
        $datrovare = rawurlencode($datrovare);  

        // -----------------------------------------------------------------------------
        // find the link
    	$ricerca = $base_scraping_url . 'find?ref_=nv_sr_fn&q=' . $datrovare . '&s=tt';

		$ret = scraping_find_IMDB($ricerca, rawurldecode($datrovare));

        if(trim($ret['link']) != "") {
        
	        $v = str_replace("\n",'',$ret['link']);
	        list($dummy,$link,$dummy1) = explode("\"",$v);
	        list($dummy,$dummy1, $imdbcode,$dummy2) = explode("/",$link);
	       
	       // scaping page
	       $ret = scraping_IMDB($base_scraping_url . $link);

	        foreach($ret as $k=>$v){
	            // estraggo anno 
	            if($k == "Titolo") {
	                preg_match_all("(\((.*?)\))", $v, $ris ); 
	                $anno =  $ris[1][0];
	            }
            }
            
	        $ret['Link'] = $link;
	        $ret['CodIMDb'] = $imdbcode;
	        $ret['Anno'] = $anno;  

            foreach($ret as $k => $field){
                log_op('$ret[' . $k . ']' . $field, "no");
            }

            log_op('Titolo originale: ' . rawurldecode($datrovare), "no");

		    // aggiorna l'archivio
		    aggiornadb($id,$ret,$conn); 
		    $buonfine++;
		    return "Archivio aggiornato! " . $ricerca;

	    }  else {
            log_op('Titolo originale: ' . rawurldecode($datrovare), "no");
	    	$noris++;
            return "Ricerca non andata a buon fine: " . $ricerca;
            
	    }      
    }
}

// funzione di aggiornamento record corrente del video
function aggiornadb($id,$ret,$conn){

    $sql = "UPDATE video SET genre='" . $ret['Genere'] . "',duration='" . $ret['Durata'] . "',director='" . $ret['Regia'] . "',year='" . $ret['Anno']. "' WHERE id='" . $id ."'";
    $conn->query($sql); 

}

// log
function log_op($contenuto,$visualizza="si"){

    $filename = "./log/logfile.txt";

    if(trim($contenuto) != ""){
        if($visualizza == "si"){
            echo $contenuto . "\n";
        } else {
            if($visualizza == "both") echo $contenuto . "\n";
            $logcontent = "[" . date("Y-m-d H:i:s") . "] " . str_replace("\n","",$contenuto);
            file_put_contents($filename,$logcontent . "\n", FILE_APPEND);
        }
    }
}

// inizio applicazione
global $buonfine;
global $noris;

include_once('db.php');  

// scansione tabella video per associare i dati eventualmente grabbati dal sito www.imdb.com
$sql = "SELECT id,name FROM " . $tabella . " WHERE genre is NULL OR trim(genre) = '' ORDER BY name ASC;";
//$sql = "SELECT id,name FROM " . $tabella . " WHERE genre is NULL ORDER BY name ASC;";
$result = $conn->query($sql);

log_op("Scansione base dati...\n");   

$i = 0;
$buonfine = 0;
$noris = 0;
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    //echo ".";
    // ricerca del titolo 
    log_op(cerca_su_IMDb($row['id'],$row['name'],$conn) . "\n", "both");
    log_op("-----------------------------------------------------------\n", "both");
    /*
    if($i >= 150) break;
    $i++; 
    */
}

// pone a zero la durata per tutti i video non censiti 
$sql = "UPDATE video SET duration=0 WHERE duration is NULL OR trim(duration) = ''"; 
$conn->query($sql);

$result = null;
log_op( "Titoli aggiornati: " . $buonfine . "\n", "both");
log_op("Titoli non aggiornati: " . $noris . "\n", "both");
log_op("\nFine aggiornamento.\n\n", "both");

?>