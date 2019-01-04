# Gestione playlist

Scopo di queste righe in php è quello di creare una base dati (SQLite3) di video presenti sulle vostre periferiche, nel caso in cui avete file di tipo .nfo (gli .nfo - file di tipo xml - che ho usato sono stai generati da Kodi media center) con i generi, i registi e l'anno associati ai video, di aggiornare la basedati con i generi relativi ai video che avete memorizzato. Alla fine, lanciando playlistcasuale.php, di creare ed eseguire una playlist creata casualmente o meno dalla precedente basedati.
In data 04/01/2019 ho aggiunto il file per lo scaping delle pagine IMDb in lingua inglese. Questo permette di "recuperare" direttamente dalle scede del sito (IMDb) i dati che inserisco successivamente nel database locale. Ho usato la [libreria di scaping](https://github.com/sunra/php-simple-html-dom-parser) di [sunra](https://github.com/sunra) che ringrazio come ringrazio, lo faccio spesso, ma senza di loro sarei stato troppo pigro per creale da me, tutti gli altri da cui ho preso idee e codice.
Vi direte a che serve: non ho molta voglia di scorrere i dischi pieni di video per crearmi una playlist per cui uso queste righe.

Gli script sono stati testati con php 7.0 in linux, ma cio' non toglie che poche modifiche se non nessuna, possano funzionare egregiamente anche sotto mac.

## Cosa serve:

1) il php 7.0 (se provate altre versioni fatemelo sapere) e relativo client con i moduli PDO e PDO-SQLite attivati;<br>
2) periferiche disco con tanti video;<br>
3) questi script, che basta solo decomprimere in una cartella di lavoro;<br>
4) un terminale per usare la riga di comando.<br>

## Come: 

<ul>
<li>
<b>1)</b>   Dopo aver abilitato i PDO, lanciare la creazione
			fisica della base dati: "php basedati.php" (non è sempre necessario
			specialmente se l'avete fatto almeno una volta);<br> 
</li>
<li>
<b>2)</b> 	lanciare il seguente comando che si occupera' di recupare i dati utili 
		  	dei video e li memorizzerà nell base dati (video.sq3): 
		  	"<i>php scandir.php 0|1 le/cartelle/dove/sono/i/video/separate/da/spazi/se/sono/diverse</i>".<br>
			<b>Esempio pratico:</b><br> 
			"<i>php scandir.php 1 /media/root/TrecStor/Films /media/root/backup/Films</i>". 
			Lo zero oppure l'uno subito dopo scandir.php
			determinano se la tabella video della base dati verrà svuotata (<i>1</i>) o
			meno (<i>0</i>). Usare lo zero è utile per accodare altre cartelle senza dover riiniziare.
			<b>N.B.:</b> le cartelle che contengono spazi vanno
			digitate dentro le virgolette (singole o doppie"); <br>
</li>
<li>
<b>3)</b> 	dopo aver usato scandir.php, usare "<i>php aggrionadb.php</i>" solo se avete dei file .nfo con 
			lo stesso nome dei video. Ovvero se non ci sono, non ha
			senso lanciarlo ed è meglio passare direttamente al punto 4). In caso di presenza di .nfo sui dischi questo script associa i
			generi, registi, anno e la durata al video, sempre che abbiano origine da Kodi; <br>
</li>
<li>
	<b>4)</b> 	con <i>php aggiornadaimdb.php</i> è possibile "recuperare" dalle pagine in inglese del sito IMDb tutti i dati che servono per completare la palylist (generi, registi, anno e durata). Recupera anche altri dati che al momento non sono gestiti dal database previsto per questi script. Nulla vieta di usare in combinazione il punto 3) ed il punto 4). <i>aggiornadaimdb.php</i> crea anche un log (nella cartella "log") per monitorare la ricerca dei titoli e l'aggiornamento della base dati.
</li>
	<li>
<b>5)</b> 	per creare una play list casuale,
			lanciare "<i>php playlistcasuale.php -n4 -e"vob cd1 cd2" -g"Animazione Family"</i>. 
			-n è il numero di video da inserire nella play list, 
			-e esclude i video che contegono le parole elencate nel path e nel nome del file,
			-g funziona solo se sono stati inseriti i generi, permette di filtrare i
			generi associati ai video.<br>
</li>
</ul>
	
Lo script "<i>generi.php</i>"" ha il solo scopo di elencare i generi presi dai file .nfo. Lo script "<i>list_txt</i>", esporta la lista dei video in archivio in formato csv/txt con separatore il punto e virgola (;). I parametri per list_txt.php sono:
<b>-g</b> per una lista ordinata per genere (per default è ordinata per ordine alfabetico crescente del titolo);
<b>-d</b> ordina per titolo o per genere (<i>-g</i>) in ordine descrescente. 
Gli altri file php sono degli include necessari per il funzionamento.

## Parametri possibili:

Tutti i parametri sono opzionali in quanto sono dati tutti di default.<br>

<b>-n</b> il numero di titoli che volete nella playlist (default "5"). Se si utilizza lo zero (0) il numero massimo verrà impostato a 9999.<br>
<b>-e</b> termini che volete escludere dalla visione. I termini in questione sono il path ed il nome del file. Se sono presenti degli spazi è meglio includere i termini fra virgolette singole o doppie (<i>default "cartoni inglesi cd1 cd2 originali vob serie volume originale"</i>);<br>
<b>-g</b> i generi da includere nella visione. Se sono più di uno, separare con uno spazio ed includere fra le virgolette (funziona solo se l'archivio è stato aggionato con <i>aggiornadb.php</i>.);<br>
<b>-m</b> il nome del player da utilizzare per la visione (<i>default "smplayer"</i>);<br>
<b>-f</b> il nome del file di playlist (<i>default "testpl.xml.xspf"</i>);<br>
<b>-d</b> la cartella dove va a risiedere il file di playlist (</i>default "scalette/"</i>);<br>
<b>-r</b> consente di selezionare i video dei registi da includere nella palylist in ordine di anno crescente o decresente determinato con l'opzione <i>-o</i> (<i>si perde il principio di casualità</i>);<br>
<b>-o</b> opzione di ordine crescente o decrescente utilizzato solo nelle opzioni -r o -u (<i>default crescente = A, opzione decrescente = D</i>);<br>
<b>-u</b> permette di visionare i titoli scaricati per <i>ultimi</i> o per <i>primi</i> (<i>dipende dalla opzione -o che è per default crescente ovvero i primi inseriti</i>) in funzione della data di modifica del file video. Anche con questa opzione, di fatto, si perde il principio di casualità;<br>
<b>-i</b> permette di includere dei termini da ricercare nel path e nel titolo, è predominante sui termini da escludere (<i>-e</i>), ovvero i termini da includere sono prioritari rispetto a quelli da escludere;<br>
<b>-p</b> permette di creare altre playlist. I formati gestiti sono: m3u e pls.<br> 

## Esempi:

<b>php playlistcasuale.php</b> (lancia la creazione di una playlist con i parametri di default e relativa visione);<br>
<b>php playlistcasuale.php -u</b> (lancia la creazione e la visione dei 5 video <i>più vecchi</i> con il resto dei parametri di default);<br>
<b>php playlistcasuale.php -u -o"D" -e""</b> (lancia la creazione e la visione di una playlist dei 5 video <i>più recenti, senza termini esclusi</i>);<br>
<b>php playlistcasuale.php -u -o"D" -e"" -g"Animazione Famiglia"</b> (lancia la creazione e la visione di una playlist di 5 video <i>più recenti, senza termini esclusi, dei generi: Animazione e Famiglia</i>);<br>
<b>php playlistcasuale.php -n3 -g"Fantascienza Sci-fi"</b> (lancia una playlist casuale per <i>3</i> video di genere <i>Fantascienza e Sci-fi</i> con esclusione dei termini di default);<br>
<b>php playlistcasuale.php -n3 -r"'Stanley Kubrick'"</b> (lancia una playlist casuale per <i>3</i> video del regista <i>'Stanley Kubrick'</i> dal piu' vecchio al piu' nuovo senza definizione di genere o esclusione dei termini di default);<br>
<b>php playlistcasuale.php -n3 -o"D" -r"'Rocco Papaleo'"</b> (lancia una playlist casuale per <i>3</i> video del regista <i>'Rocco Papaleo'</i> ad iniziare dal più <i>recente</i>, senza definizione di genere o esclusione dei termini di default).
...

# Interfaccia Yad

Sono state aggiunte alcune interfacce grafiche realizzate grazie a [Yad (Yet Another Dialog)](https://github.com/v1cont/yad) che rendono l'interazione più facile. [Vedi relativo repository](https://github.com/losciuto/yad-windows-playlist)

