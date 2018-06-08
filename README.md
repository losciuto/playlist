# playlistcasuale
Playlist Casuale

Scopo di queste righe in php è quello di creare una base dati SQLite3 di video presenti sulle vostre periferiche, nel caso in cui avete file di tipo .nfo (gli .nfo che ho usato sono sati generati da Kodi media center) con i generi associati ai video, di aggiornare la basedati con i generi relativi ai video che avete memorizzato. Alla fine, lanciando playlistcasuale.php, creare ed eseguire una playlist creata casualmente dalla precedente basedati.
Vi direte a che serve: non ho molta voglia di scorrere i dischi pieni di video per crearmi una playlist per cui uso queste righe.

gli script sono stati testati con php 7.0 in linux, ma cio' non toglie che poche modifiche se non nessuna, possano funzionare egregiamente anche sotto mac e sotto windows.

Cosa serve:
1) il php 7.0 (se provate altre versioni fatemelo sapere) con i moduli PDO e PDO-SQLite attivati;
2) periferiche disco con tanti video;
3) questi script che basta solo decomprimere in una cartella di lavoro;
4) un terminale per usare la riga di comando.

Come:
Dopo aver abilitato i PDO, lanciare la creazione fisica della base dati: "php basedati.php";
lanciare il seguente comando che si occupera' di recupare i dati utili dei video e li memorizzerà nell base dati (video.sq3): "php scandir.php 0|1 le/cartelle/dove/sono/i/video/separate/da/spazi/se/sono/diverse". Esempio pratico: "php scandir.php 1 /media/root/TrecStor/Films /media/root/backup/Films"
Lo zero oppure l'uno subito dopo scandir.php determinano se la tabella video della base dati verrà svuotata (1) o meno (0). Usare lo zero è utile per accodare altre  cartelle senza dover riiniziare. Fate comunque attenzione ai duplicati per i quali questi script non hanno controlli. N.B.: le cartelle che contengono spazi vanno digitate dentro le virgolette;
Usare "php aggrionadb.php" è condizionato alla presenza dei file .nfo. Ovvero se non ci sono, non ha senso lanciarlo. In caso di .nfo sui dischi questo script associa i generi al video;
Per creare una play list casuale lanciare php playlistcasuale.php -n4 -e"vob cd1 cd2" -g"Animazione Family". -n è il numero di video da inserire nella play list, -e esclude i video che contegono le parole elencate, -g funziona solo se sono stati inseriti i generi, permette di filtrare i generi associati ai video.



