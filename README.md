<a href="#Cosa serve:">Cosa serve</a>

# Gestione playlist
Scopo di queste righe in php è quello di creare una base dati SQLite3 di video presenti sulle vostre periferiche, nel caso in cui avete file di tipo .nfo (gli .nfo che ho usato sono sati generati da Kodi media center) con i generi associati ai video, di aggiornare la basedati con i generi relativi ai video che avete memorizzato. Alla fine, lanciando playlistcasuale.php, creare ed eseguire una playlist creata casualmente dalla precedente basedati.
Vi direte a che serve: non ho molta voglia di scorrere i dischi pieni di video per crearmi una playlist per cui uso queste righe.

gli script sono stati testati con php 7.0 in linux, ma cio' non toglie che poche modifiche se non nessuna, possano funzionare egregiamente anche sotto mac e sotto windows.

# Cosa serve:
1) il php 7.0 (se provate altre versioni fatemelo sapere) con i moduli PDO e PDO-SQLite attivati;
2) periferiche disco con tanti video;
3) questi script che basta solo decomprimere in una cartella di lavoro;
4) un terminale per usare la riga di comando.

# Come:
1) Dopo aver abilitato i PDO, lanciare la creazione fisica della base dati: "php basedati.php" (non è sempre necessario specialmente se l'avete fatto almeno una volta);
2) lanciare il seguente comando che si occupera' di recupare i dati utili dei video e li memorizzerà nell base dati (video.sq3): "php scandir.php 0|1 le/cartelle/dove/sono/i/video/separate/da/spazi/se/sono/diverse". Esempio pratico: "php scandir.php 1 /media/root/TrecStor/Films /media/root/backup/Films". Lo zero oppure l'uno subito dopo scandir.php determinano se la tabella video della base dati verrà svuotata (1) o meno (0). Usare lo zero è utile per accodare altre cartelle senza dover riiniziare. Fate comunque attenzione ai duplicati per i quali questi script non hanno controlli. N.B.: le cartelle che contengono spazi vanno digitate dentro le virgolette;
3) usare "php aggrionadb.php" è condizionato alla presenza dei file .nfo. Ovvero se non ci sono, non ha senso lanciarlo. In caso di .nfo sui dischi questo script associa i generi e la durata al video;
4) per creare una play list casuale lanciare php playlistcasuale.php -n4 -e"vob cd1 cd2" -g"Animazione Family". -n è il numero di video da inserire nella play list, -e esclude i video che contegono le parole elencate nel path e nel nome del file, -g funziona solo se sono stati inseriti i generi, permette di filtrare i generi associati ai video.

Lo script generi.php ha il solo scopo di elencare i generi grabbati dai file .nfo. Gli altri file php sono degli include necessari per il funzionamento.

# Parametri possibili:
Tutti i parametri sono opzionali in quanto quelli necessari sono dati di default.
-n il numero di titoli che volete nella playlist (default "5"). Se si utilizza lo zero (0) il numero massimo verrà impostato a 999999.
-e termini che volete escludere dalla visione. I termini in questione sono il path ed il nome del file. Se sono presenti degli spazi è meglio includere i termini fra virgolette (default "cartoni inglesi cd1 cd2 originali vob serie volume originale");
-g i generi da includere nella visione. Se sono più di uno, separare con uno spazio ed includere fra le virgolette (funziona solo se l'archivio è stato aggionato con aggiornadb.php che si basa su file .nfo);
-m il nome del player da utilizzare per la visione (default "smplayer");
-f il nome del file di playlist (default "testpl.xml.xspf");
-d la cartella dove va a risiedere il file di playlist (default "scalette/");
-r consente di scegliere il regista di cui permettere la visualizzazione in ordine di anno crescente o decresente determinato con l'opzione -o (si perde il principio di casualità);
-o opzione di ordine crescente o decrescente utilizzato solo nelle opzioni -r o -u (default crescente = A, opzione decrescente = D);
-u permette di visionare i titoli scaricati per ultimi o per primi (dipende dalla opzione -o che e' per default crescente) con i filtri eventualmente inseriti in -e e in -g (default "falso"). Con questa opzione, di fatto, si perde il principio di casualità;
-i permette di uncludere dei termini da ricercare nel path e nel titolo, è predominante sui termini da escludere ovvero i termini da includere sono prioritari rispetto a quelli da escludere.

# Esempi:
php playlistcasuale.php (viene lanciata la creazione di una playlist con i parametri di default e relativa visione col player di default);
php playlistcasuale.php -u (viene lanciata la creazione e la visione dei 5 video più vecchi con esclusione di default);
php playlistcasuale.php -u -o"D" -e"" (viene lanciata la creazione e la visione di una playlist dei 5 video più recenti senza esclusioni);
php playlistcasuale.php -u -o"D" -e"" -g"Animazione Famiglia" (viene lanciata la creazione e la visione di una playlist di 5 video più recenti senza esclusioni del genere Animazione e Famiglia);
php playlistcasuale.php -n3 -g"Fantascienza Sci-fi" (viene creata una playlist casuale per tre video di genere Fantascienza e Sci-fi con esclusione dei termini di default);
php playlistcasuale.php -n3 -r"Stanley Kubrick" (viene creata una playlist casuale per tre video del regista Stanley Kubrick dal piu' vecchio al piu' nuovo senza definizione di genere o esclusione dei termini di default);
php playlistcasuale.php -n3 -o"D" -r"Rocco Papaleo" (viene creata una playlist casuale per tre video del regista Rocco Papaleo ad iniziare dal piu' recente senza definizione di genere o esclusione dei termini di default);


<b>N.B.:</b> alcuni file hanno solo funzioni di servizio: generi.php visualizza i generi presenti nella base dati; 
list_txt.php scrive sul file "lista.txt" l'intera lista dei video, il formato è quello di un csv per cui importabile su di un foglio di calcolo.
I parametri per list_txt.php sono -g per una lista ordinata per genere (per default è ordinata per ordine alfabetico crescente del titolo);
-d ordina per titolo o per genere (-g) in ordine descrescente.

Sono state aggiunte alcune interfacce grafiche realizzate grazie a Yad (Yet Another Dialog) che rendono l'interazione più facile. Vedi relativo repository

