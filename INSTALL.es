
## Como funcionara

Este proyecto esta muy muy verde todavia, cambiara bastante entre commits y no puede y no tendra compatibilidad entre versiones anteriores de momento.
Si lo instalas y utilizas probablemente tendras que recrear (borrar y volver a crear) las base de datos y estar al tanto de los cambios en el config.inc.php.

Para usarlo edite/configure en configure.inc.php, y pulse rescanear los directorios y identifique sus peliculas/shows.

En el tab de torrentes puedes buscar peliculas/shows y ponerlos a descargarlos en transmission pulsando download

En "Seguimiento" veras los archivos que se han puesto en seguimiento para descarga (no funciona todavía)
 
En "Publicado" veras los ultimos torrents publicados

En biblioteca podras ver tus peliculas/series, identificar y ver episodios y cuales faltan, tambien hay enlace para descargar localmente.

En el futuro trackerm podra seguir tus series, descargarlas automaticamente así como mover los archivos automatica a tu libreria.

Es más que recomendable si tienes que borrar un archivo torrent de transmission añadido con trackm hacerlo desde trackm 

Atencion: En estos momentos no estoy utilizando ninguna base de datos , solo guardando los datos en archivos, esto cambiar en el futuro y rompera
cualquier compatibilidad hacia atras. Y actualmente es facil corrompible, sobre todo en entornos multiusuario.

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

## CONFIGURACION
    Toda las opcines de configuración  va en config/config.inc.php excepto para la parte de la "utilidad automatica" que hay que cambiar la
    variable ROOT_PATH a donde reside la web. ex: $ROOT_PATH = '/var/www/trackerm' o por las actualizaciones, creando un archivo 
    /etc/trackerm_root_path que incluya una simple linea con el  ROOT_PATH, ex: /var/www/trackerm

## Tareas automaticas: Seguimiento y mover a la libreria.
    En estos momentos todos los mecanismos automaticos estan en desarrollo y no funciona o funcionan parcialmente
    No recomiendo su uso.
    Para su actual o uso futuro hay que añadir trackerm-cli.php a CRON, seria recomendable moverlo fuera del ROOT_PATH.

    Como funcionara (no lo hace todavía) :
    * Automaticamente buscara torrentes en "seguimiento" los dias que configuraras, si encuentra coincidencia que satisfaga los filtros lo descargara.
    * Automaticamente movera todos los archivos descargados por transmission a tu libreria siempre que se pulsara el descargar desde trackrm, opcionalmente tambien podra
        mover todos los archivos mientras sean multimedia si así se configura
    * Automaticamente y activalando buscara y movera archivos huerfanos en el directorio de descargas de transmission. Los archivos huerfanos son aquellos
     que no constan en transmission ya sea por que los hemos borrado manualmente en vez de pararlos/pausarlos u otro motivo
    * Automaticamente y activandolo podra mover todos los archivos multimedia de determinas carpetas que configuremos.
    * Automaticamente descomprime archivos rar, no soporta contraseñas hasta que Jackett las soporte. (el cli actualmente se bloquea si encuentra uno)
    El funcionamiento para que automaticamente se muevan los archivos bajados por transmission es el siguiente:
        Primero busca los archivos que indicamos en trackrm descargar, si los encuentra mira si esta parado/pausa o sirviendo(seeding) si es así en el primer caso
        parado, lo movera y borrar el torrent, en el segundo caso (aun no programado), creara un enlace simbolico para poder acceder al archivo desde tu libreria
        hasta que sea parado o pare de "servirse" el archivo, en cuyo caso se movera.
        Actualmente solo mueve los archivos parados/pausa.

## VERSION
    Advertencia: Puedes comprobar la version en el archivo VERSION. Mientras este en alpha (0.0.X) hasta la versión 0.1, toda version es factible de romper
    la compatibilidad hacia atras, y lo hara. Si actualizas y hay errores posiblemente tengas que borrar los archivos de la base de datos cache/*.db y comprobar
    el archivo config.inc contra _config.inc si hay nuevas variables.

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

