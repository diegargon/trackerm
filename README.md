# trackerm

Description: 
Probando a realizar una alternativa Sonarr+Radar sobre servidor web
Trying a Sonarr & Radarr alternative over a web server.

Warning: Codigo/esbozo realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo si no que esta sin seguridad, 
Warning: Outline code, "fast coding" (the bulk was done in 3 days) have to polish a lot and it is without security.

Probablemente de momento no deberias instalarlo.
Probably shouldn't install it at the moment.

Puedes ver sceenshots del aspecto actual en /screenshots aunque cambiara que el proyecto esta en fase muy muy temprana.
You can see screenshots of the current appearance in /screenshots although it going to change since this proyect is in a very very early stage.

## WARNING
    There is no security check in the code yet use on your own risk. The code are totatally insecure. If you expose this code to internet you have a very high
    security problem. why? want this app "now", have too much time in few days. Security and better code will comming more slowly

    No hay ningun mecanismo deseguridad en el codigo todavia. El codigo es totalmente inseguro. Si expones este codigo a internet tendras un grave problema
    de seguridad. ¿por que? queria esta aplicación "ya", tenia mucho tiempo pero pocos dias. Seguridad y mejor cidog vendra mucho mas despacio.

# How WILL works. (EXCUSE MY ENGLISH)

This proyect it in very very early stage, will change alot between commits and files and database will not be backwards compatible, if you want use
now and update tomorrow you must probably remove all database entrys in cache and begin identified applications again if errors appears (and going to appear).

Select the path (config file only atm) where your movies/shows reside, click on rebuild buttons and identify your movies/shows.

In "Themoviedb" tab you can search for movies/shows and  automatically will show you the torrents, if there is none you can choose 'wanted' and (FEATURE UPCOMMING) 
trackm will check released torrents and automatically download when a match appears. 

In Torrents tab you can search movies/shows torrents and clicking in download will automatically send to Transmission-daemon

In Wanted/Seguimiento you can see what movies/shows trackm are tracking for download (feature not yet avaible)

In Released/Publicado you can see the latest torrent releases

In Library/Biblioteca you can see your movies/shows, identify, show seasons and check missing episodes.

In the future trackm will track your transmission movie/shows downloads and automatically move to your library path. (not yet available)

WARNING: At this momment i not using any database, just save in json files the data, that will change in the future and break backwards compatibility.

## VERSION
    WARNING: You can check the version on VERSION file. While in alpha 0.0.X and until 0.1 on every version update probably you going to need 
    recreate all DB files (delete old) and check config.inc.php agains _config.inc.php values for new/modify ones, and this will be change very very often.
    No backwards compatible yet.

# Lenguaje
    Php+Javascript (Más adelante probablemente Jquery)

# Resumen requisitos:
Apache+Php (o similar), Jacket, Transmission, Composer, cuenta+api key themoviedb.org, CRON

# Instalación
    * Copiar los archivos de src a la carpeta destino (AKA: dest)

    * Instalar composer si no lo teneis, hay guias pero basicamente 

        $ curl -sS https://getcomposer.org/installer -o composer-setup.php

        $ php composer-setup.php --install-dir=/usr/local/bin --filename=composer

    *  Ir a la carpeta dest y teclear

        $ composer require irazasyed/php-transmission-sdk  php-http/httplug-pack  php-http/guzzle6-adapter

    * Renombrar _config.inc.php a config.inc.php y configurarlo
        Importante añadir, themoviedb api key, jacket server ip y key, los indexers  que queremos utilizar (previamente activados en jackett)
# Requisitos: Detalles:

* Apache (o similar)
    Instalado y configurado, con soport php, curl

    * Para el cache de las imagenes/posters se necesita allow_url_fopen en php.ini
    * Se necesitan permisos de lectura/escritura en cache y cache/images 775 si cambiamos el propietario al del servidor web o 777 (inseguro)
      Database files and images will be stored in cache dir, must be writable.
* Jackett
    Instalado y configurado añadiendo algunos indexers de peliculas/series.
    Necesitas la clave api para conectarse al servidor jackett, esta ira en config.inc.php

* Transmission-daemon
    Instalado/configurado y permitiendo las conexiones RPC a la ip del servidor

    * Aunque depende de la distro el archivo es settings.json en /etc/transmission y hay que parar el daemon primero antes de editar si no al parar/reiniciar sobreesribiran los cambios

    * Hay alguna version con un bug que obvia las ips rpc, si aparece un mensaje de error de whitelist prueba a desactivar el la rpc whitelist (a cuenta y riesgo)

    * Utilizo una libreria externa para el dialogo con transmission que hay que instalar via composer (ver instalación) 

* TheMovieDB.ORG    
    Es importante para el correcto funcionamiento crearse una cuenta en dicha pagina, se utilizar para buscar peliculas/series, caratulas, identificar y demas.
    Necesitais una clave api de un proveedor, actualmente solo soporta themoviedb.org (el api key va en config.inc.php)
    Quizas en el futuro se añadan otras alternativas pero de momento solo hay esta.

## Latest Changes
    