# Sviluppo
:heavy_check_mark: Webserver Oxwall (enfasi posta sul tipo di storage utilizzato)
| Descrizione | File | Note |
|--|--|--|
| **Singolo** nodo con storage **locale** | [oxwall-web-singlenode-local.yaml](kubernetes/oxwall/web/oxwall-web-singlenode-local.yaml) | <ul><li>:thumbsdown: Non sfruttiamo le potenzialità di scalabilità di un cluster Kubernetes;</li><li>:thumbsdown: Con un volume effimero tutti i media caricati dagli utenti e altre modifiche fatte al sito (temi/plugin installati) vengono perse se il nodo crasha;</li><li>:thumbsdown: Con un volume persistente locale il pod non può essere rischedulato su un nuovo nodo;</li></ul> |
| **Singolo** nodo con storage **Cloud** | [oxwall-web-singlenode-cloud.yaml](kubernetes/oxwall/web/oxwall-web-singlenode-cloud.yaml) | <ul><li>:thumbsup: Lo storage viene mantenuto anche se il nodo crasha e il pod può essere rischedulato su un nuovo nodo in quanto il volume è separato e completamente gestito;</li><li>:thumbsdown: Essendo Oxwall basato su php (contenuti dinamici), avere un solo nodo che li elabora potrebbe rappresentare un collo di bottiglia</ul> |
| **Molteplici** nodi con storage **condiviso** (es. Cloud/NFS) | [oxwall-web-multinode-shared.yaml](kubernetes/oxwall/web/oxwall-web-multinode-shared.yaml) | <ul><li>:warning: Utilizzare volumi che supportano la modalità *ReadWriteMany*, sia nativi che tramite driver [CSI esterni](https://kubernetes-csi.github.io/docs/drivers.html);</li><li>:thumbsup: Lo storage viene mantenuto anche se il nodo crasha e i pod possono essere rischedulati su un nuovo nodo in quanto il volume è separato;</li><li>:thumbsup: Risolve il problema di sincronizzazione dei contenuti (media caricati dagli utenti, temi e plugin) tra i nodi, in quanto lo storage è univoco e condiviso;</li><li>:thumbsdown: Possibili latenze di rete per ogni operazione di lettura/scrittura su disco se lo storage è "lontano" dai nodi;</li><li>:warning: Inutile nei casi in cui vengono serviti contenuti statici (es. web app realizzate con framework come Angular, React, VueJS, ecc.);</li></ul>|
| **Molteplici** nodi con un proprio storage **locale**, ma **sincronizzati** | [oxwall-web-multinode-local.yaml](kubernetes/oxwall/web/oxwall-web-multinode-local.yaml) | <ul><li>:warning: Il *Deployment* diventa uno *StatefulSet* in quanto c'è necessità di un'identità statica per i pod;</li><li>:thumbsup: Risolve i problemi delle latenze degli storage di rete in quanto i dati vengono letti localmente da ogni nodo;</li><li>:thumbsup: Risolve il problema di sincronizzazione dei contenuti (media caricati dagli utenti, temi e plugin) tra i nodi, in quanto viene instaurata una rete P2P grazie al pod di Resilio Sync;</li><li>:thumbsdown: Anche qui con volumi effimeri perdiamo i dati se crashano tutti i nodi contemporaneamente, mentre con un volume persistente locale il pod non può essere rischedulato su un altro nodo;</li></ul> |

:hammer_and_wrench: Database Oxwall (enfasi posta sull'architettura del cluster MySQL)
| Descrizione | File | Note |
|--|--|--|
| **Singolo** nodo | [oxwall-db-singlenode.yml](kubernetes/oxwall/database/oxwall-db-singlenode.yaml) | <ul><li>:thumbsdown: Non garantisce alta disponibilità;</li><li>:thumbsdown: Può rappresentare un collo di bottiglia se le richieste sono tante;</li></ul> |
| **Molteplici** nodi (**master-slave**) | [oxwall-db-master-slave.yaml](kubernetes/oxwall/database/oxwall-db-master-slave.yaml) | <ul><li>:thumbsup: Richieste in lettura distribuite (e possibili anche se il nodo master crasha);</li><li>:thumbsdown: Per le richieste in scrittura siamo ancora obbligati a usare il nodo master;</li><li>:thumbsdown: Dobbiamo avere controllo del codice dell'applicazione per poter distinguere le query in scrittura da quelle in lettura (non è il caso di Oxwall);</li><li>:thumbsdown: Se il master fallisce, la promozione di uno degli slave come nuovo master non è automatica;</li><li>:warning: Valutare l'utilizzo di **ProxySQL/HAProxy** per separare le scritture dalle letture e reindirizzarle ai nodi giusti, anche quando non abbiamo controllo del codice dell'applicazione;</li></ul> |
| **Molteplici** nodi (**multi-master**) | [oxwall-db-multi-master.yaml](kubernetes/oxwall/database/oxwall-db-multi-master.yaml) | <ul><li>:warning: È necessario convertire tutte le tabelle da *MyISAM* a *InnoDB*, sia quelle che vengono generate da Oxwall in fase di installazione, che quelle generate dai plugin quando se ne installa uno;</li><li>:thumbsup: Alta disponibilità e scalabilità;</li><li>:thumbsup: Tutte le richieste (sia in scrittura che in lettura) possono essere fatte su uno qualsiasi dei nodi del cluster;</li><li>:thumbsdown: Per le scritture (sincrone) dobbiamo assicurarci che i nodi siano "vicini" e comunichino con basse latenze;</li></ul> |

:hammer_and_wrench: CKAN
TODO

# Note:
 - **Oxwall**
	 - :hammer_and_wrench: Creare un'immagine di container pronta per l'installazione che sia già compresa dei temi e plugin necessari, tramite [Dockerfile](docker/oxwall/Dockerfile)
	 - :heavy_check_mark: Implementare cron job per richiamare il file /ow_cron/run.php periodicamente tramite la risorsa CronJob di Kubernetes, in [oxwall-web-cronjob.yml](kubernetes/oxwall/web/oxwall-web-cronjob.yml)
	 - :hammer_and_wrench: Aggiungere un server FTP da affiancare al webserver all'interno del pod
	 - **Elementi**:
		 - Webserver Apache (php 5.6):
			 - :heavy_check_mark: **Problema:** non tutte le directory sono *stateless*. Alcune vengono utilizzate per memorizzare i file caricati dagli utenti (es. `ow_pluginfiles` o `ow_userfiles`). Il problema può essere risolto con uno storage condiviso (es. volume in Cloud o mount NFS). Diversamen,
        "post-update-cmd": [
            "Illuminate\\Foundation\\ComposerScripts::postUpdate",
            "php artisan optimize"
        ]te, se utilizziamo lo storage locale di ogni nodo, dobbiamo sincronizzare i loro contenuti. Soluzioni valutate:
				 - :star: [Sync](https://hub.docker.com/r/resilio/sync) - sincronizzazione P2P, attualmente utilizzato per mantenere i pod sincronizzati a runtime ([disabilitando](https://help.resilio.com/hc/en-us/articles/204754349-Can-I-force-Sync-to-do-local-network-LAN-syncing-only-and-not-sync-via-the-Internet) tracker e relay esterni);
				 - [Syncthing](https://github.com/syncthing/syncthing) - sincronizzazione P2P, valida alternativa open source a Resilio Sync;
				 - :star: rsync - non né bidiriezionale né real-time, ma si presta bene in fase di inizializzazione di un nuovo pod per sincronizzare i suoi contenuti con il pod che lo precede nell'ordinamento;
				 - unison - è bidirezionale, ma non è real-time;
				 - [lsyncd](https://github.com/lsyncd/lsyncd) - è real-time, ma non è bidirezionale;
				 - [mirror](https://github.com/stephenh/mirror) - è bidirezionale e real-time, ma segue un modello client-server (con più pod dovremmo creare un topologia a stella);
				 - [CephFS](https://ceph.io);
				 - [GlusterFS](http,
        "post-update-cmd": [
            "Illuminate\\Foundation\\ComposerScripts::postUpdate",
            "php artisan optimize"
        ]s://www.gluster.org);
			 - :heavy_check_mark: **Problema:** la sessione di un utente loggato è gestita interamente da PHP (non tramite token nel database). Ciò significa che, nel caso di più nodi, la sessione non è sincronizzata tra le istanze, ma sarà presente solamente sul nodo che ha servito l'accesso all'utente nel momento del login. Idea: implementare un meccanismo di *sticky sessions* grazie ad un *NGINX Ingress Controller*, realizzato in [oxwall-ingress.yaml](kubernetes/oxwall/oxwall-ingress.yaml)
		 - :hammer_and_wrench: Database MySQL (5.7):
			 - **Problema:** Oxwall per le sue tabelle utilizza come engine *MyISAM*, il quale non supporta le transazioni. Le soluzioni multi-master, invece, richiedono un engine che le supporti in modo da garantire una scrittura sincrona tra tutti i nodi partecipanti al cluster
			 - Configurazione **master-slave**: OK, ma non garantisce High-Availability (provato con MySQL 5.7)
			 - Configurazione **multi-master**:
				 - Percona XtraDB Cluster -> il database non riesce ad inizializzarsi in ambiente Kubernetes (basato su MySQL 5.7)
				 - MariaDB Galera Cluster -> OK, ma bisogna utilizzare l'engine *InnoDB* o utilizzare il flag sperimentale `--wsrep-replicate-myisam=1` (basato su MariaDB 10.4)
				 - NDB Cluster -> OK, ma bisogna utilizzare l'engine *NDBCLUSTER* (provato con MySQL Cluster 7.6, basato su MySQL 5.7)

 - **CKAN**
	 - [datastore e datapusher](https://docs.ckan.org/en/2.9/maintaining/installing/install-from-docker-compose.html#datastore-and-datapusher)
	 - automatizzare la creazione dell'utente [admin](https://docs.ckan.org/en/2.9/maintaining/installing/install-from-docker-compose.html#create-ckan-admin-user)
	 - **Elementi**:
		 - Webserver
		 - Database PostgreSQL
		 - datapusher, redis, solr (come gestire le repliche?)

# Comandi utili
Avvio cluster locale con minikube:

    minikube start

Creare/modificare le risorse da file YAML:

    kubectl apply -f <FILE>

Ottenere IP minikube (per accedere alle NodePort o modificare il file /etc/hosts per testare l'Ingress):

    minikube ip

Aprire shell in un pod:

    kubectl exec --stdin --tty POD_NAME -- /bin/bash
