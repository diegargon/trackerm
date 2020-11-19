# tracketm

Description: 
Probando a realizar una alternativa Sonarr+Radar sobre servidor web

Warning: Codigo/esbozo realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo si no que esta sin seguridad, 
sin comprobaciones, con fallos, errores y todo eso... 

Probablemente de momento no deberias instalarlo.

Puedes ver sceenshots del aspecto actual en /screenshots aunque cambiara que el proyecto esta en fase muy muy temprana.

# How WILL works. (EXCUSE MY ENGLISH)

Select the path (config file only atm) where your movies/shows reside, click on rebuild buttons and identify your movies/shows.

In "Themoviedb" tab you can search for movies/shows and  automatically will show you the torrents, if there is none you can choose 'wanted' and (FEATURE UPCOMMING) 
trackm will check released torrents and automatically download when a match appears. 

In Torrents tab you can search movies/shows torrents and clicking in download will automatically send to Transmission-daemon

In Wanted/Seguimiento you can see what movies/shows trackm are tracking for download (feature not yet avaible)

In Released/Publicado you can see the latest torrent releases

In Library/Biblioteca you can see your movies/shows, identify, show seasons and check missing episodes.

In the future trackm will track your transmission movie/shows downloads and automatically move to your library path. (not yet available)


# Lenguaje
    Php+Javascript (Más adelante probablemente Jquery)

# Resumen requisitivos:
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

    