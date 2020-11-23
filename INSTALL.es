
## Como funcionara

Este proyecto esta muy muy verde todavia, cambiara bastante entre commits y no puede y no tendra compatibilidad entre versiones anteriores de momento.
Si lo instalas y utilizas probablemente tendras que recrear (borrar y volver a crear) las base de datos y estar al tanto de los cambios en el config.inc.php.

Para usarlo edite/configure en configure.inc.php, y pulse rescanear los directorios y identifique sus peliculas/shows.

En el tab de torrentes puedes buscar peliculas/shows y ponerlos a descargarlos en transmission pulsando download

En "Seguimiento" veras los archivos que se han puesto en seguimiento para descarga (no funciona todavía)
 
En "Publicado" veras los ultimos torrents publicados

En biblioteca podras ver tus peliculas/series, identificar y ver episodios y cuales faltan, tambien hay enlace para descargar localmente.

En el futuro trackerm podra seguir tus series, descargarlas automaticamente así como mover los archivos automatica a tu libreria.

Atencion: En estos momentos no estoy utilizando ninguna base de datos , solo guardando los datos en archivos, esto cambiar en el futuro y rompera
cualquier compatibilidad hacia atras.
 

## Permisos de directorio
    Your www server must have this permissions:
    * ( R/W )to cache : save db files and images
    * ( R ) to MOVIES_PATH : Where your movies library reside
    * ( R ) to SHOWS_PATH : Where your shows library reside
    * ( R ) to Transmission: Where transmission drop your files

    All automatic jobs are doing with trackerm-cli.php you must configure your contrab to execute it
    * ( RW ) to MOVIES_PATH : Where your movies library reside
    * ( RW ) to SHOWS_PATH : Where your shows library reside
    * ( RW ) to Transmission: Where transmission drop your files    

## CONFIGURE
    All configuration options going in config/config.inc.php except for the "automatic tool" you need change in trackerm-cli.php the variable 
    ROOT_PATH . ex: $ROOT_PATH = '/var/www/trackerm' 

## Automatic search for wanted media and "automatic moving file"  mechanism.
    At this moment all auto mechanism are in develop, i do not recommend using it.
    trackerm-cli.php must be added to your cron.

    How will works (not yet):
    * Will looking for wanted items the days configured and automatic download if found any coincidence.
    * Will move all media in the transmission-daemon "completed" directory to your library directory (you can configure if move all or only the media download
    with this software.
  
## VERSION
    WARNING: You can check the version on VERSION file. While in alpha 0.0.X and until 0.1 on every version update probably you going to need 
    recreate all DB files (delete old) and check config.inc.php agains _config.inc.php values for new/modify ones, and this will be change very very often.
    No backwards compatible yet.

## Lenguaje
    Php+Javascript (Más adelante probablemente Jquery)

## Resumen requisitos:
Apache+Php (o similar), Jacket, Transmission, Composer, cuenta+api key themoviedb.org, CRON

## Instalación
    * Copiar los archivos de src a la carpeta destino (AKA: dest)

    * Instalar composer si no lo teneis, hay guias pero basicamente 

        $ curl -sS https://getcomposer.org/installer -o composer-setup.php

        $ php composer-setup.php --install-dir=/usr/local/bin --filename=composer

    *  Ir a la carpeta dest y teclear

        $ composer require irazasyed/php-transmission-sdk  php-http/httplug-pack  php-http/guzzle6-adapter

    * Renombrar _config.inc.php a config.inc.php y configurarlo
        Importante añadir, themoviedb api key, jacket server ip y key, los indexers  que queremos utilizar (previamente activados en jackett)
## Requisitos: Detalles:

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

