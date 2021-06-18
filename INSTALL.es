
## Como funcionara

Este proyecto esta algo verde todavia.

Para usarlo renombre el archivo config.min.php  a /etc/trackerm.conf y rellene lo que se pide. Todos los datos ahí indicados son obligatorios.
Luego pinche en escanear la libreria y identifique sus archivos multimedia actuales.

En el tab de torrentes puedes buscar peliculas/shows y ponerlos a descargarlos en transmission pulsando download

En "Seguimiento" veras los archivos que se han puesto en seguimiento para descarga (no funciona todavía)
 
En "Publicado" veras los ultimos torrents publicados

En biblioteca podras ver tus peliculas/series, identificar y ver episodios y cuales faltan, tambien hay enlace para descargar localmente.

Es más que recomendable si tienes que borrar un archivo torrent de transmission añadido con trackerm hacerlo desde trackerm. Para el mejor funcionamiento
es mejor que todo lo hagas desde trackerm.

El sistema de seguimiento automatico se activa metiendo el archivo trackerm-cli.php en el cron y poner que se ejecute cada poco tiempo (hay otros mecanismos 
en el archivo de config que evitan sobrecargar el sistema si lo pones cada poco, 15 minutos, 30 minutos o o 1 hora estan bien si quieres que esten  todas las 
tareas bien actualizadas

## Permisos de directorio
    Your www server must have this permissions:
    * ( R/W ) to cache cache/log cache/images cahce/log : save db files and images and logs (755)
    * ( R ) to MOVIES_PATH : Where your movies library reside
    * ( R ) to SHOWS_PATH : Where your shows library reside
    * ( R ) to Transmission: Where transmission drop your files

    All automatic jobs are doing with trackerm-cli.php you must configure your contrab to execute it
    * ( RW ) to MOVIES_PATH : Where your movies library reside
    * ( RW ) to SHOWS_PATH : Where your shows library reside
    * ( RW ) to Transmission: Where transmission drop your files    


## Resumen requisitos:
Apache+Php7+sqlite3 , Jacket, Transmission, Composer, cuenta+api key themoviedb.org, CRON,

## Instalación
    * Copiar los archivos de src a la carpeta destino (AKA: dest)
    * Copia el archivo config.min.php a /etc/tracker.conf y rellenalo, todo los campos son necesarios

    * Instalar composer si no lo teneis, hay guias pero basicamente 

        $ curl -sS https://getcomposer.org/installer -o composer-setup.php

        $ php composer-setup.php --install-dir=/usr/local/bin --filename=composer

    *  Ir a la carpeta dest y teclear lo siguiente para cumplir las dependencias.

        $ composer require irazasyed/php-transmission-sdk  php-http/httplug-pack  php-http/guzzle6-adapter

    * sqlite3 normalmente viene instalada por defecto en muchas distros, hay que activarla tambien para apache/php, en ubuntu
    al instalarla se activa.

        $ apt-get install php-sqlite3

## CONFIGURACION
    Toda las opciones personalizables de configuración van en /etc/trackerm.conf copie el archivo de la carpeta config/config.min.php 
    a /etc y renombrelo como trackerm.conf y configurelo.

    Necesitas una themoviedb api key (aunque proveo una general que deberia funcionar), jacket server ip y key, los indexers  que queremos 
    utilizar (previamente activados en jackett) y basicamente rellenar todo de config.min.php en /etc/trackerm.conf

## Tareas automaticas: Seguimiento y mover a la libreria.

    Para usarlo hay que añadir trackerm-cli.php a CRON. Posiblemente seria recomendable moverlo fuera del ROOT_PATH. Si lo moviera
    tenga en cuenta que al actualizarlo tendra que moverlo otra vez y sobreescribir el antiguo.

    Como funcionara  :
    * Automaticamente buscara torrentes en "seguimiento" el dia seleccionado, si encuentra coincidencias que satisfaga los filtros lo descargara.
    * Automaticamente movera todos los archivos descargados por transmission a tu libreria, opcionalmente puedes configurarlo para que mueva solo los
    que descargastes pulsando dentro de la aplicación trackerm. Por defecto mueve todo los archivos multimedia (video) que encuentra en en pausa + finalizado
    en transmission
    * (no disponible todavía) Automaticamente y activandolo podra mover todos los archivos multimedia de determinas carpetas que configuremos.
    * Automaticamente descomprime archivos rar, no soporta contraseñas hasta que Jackett las soporte. Si avisa de que encontro un archivo protegido.

    El funcionamiento para que automaticamente se muevan los archivos bajados por transmission es el siguiente:
        Primero busca los archivos que indicamos descargar con trackerm, si los encuentra mira si esta parado/pausa o sirviendo(seeding) si es así en el primer caso
        parado, lo movera y borrara el torrent y otros archivos residuales de la descarga, en el segundo caso, creara un enlace simbolico para poder acceder al archivo 
        desde tu libreria hasta que sea parado o pare de "servirse" el archivo, en cuyo caso se movera.

    La linea basica para ejecutar las tareas automaticas  (ejemplo cada 15 minutos) es la siguiente (/etc/crontab)
    */15 *   * * *   root    /usr/bin/php  /path/to/trackerm-cli.php
    Puedes poner trackerm-cli.php en el directorio que quieras y cambiar de usuario si este tiene los permisos necesarios para las carpetas relacionadas. Si lo mueve
    recuerde hacerlo siempre que actualice trackerm.

## Lenguaje
    Php+Javascript (Más adelante probablemente Jquery)

## Requisitos: Detalles:

* Apache (o similar)
    Instalado y configurado, con soport php7,sqlite3, curl

    * Para el cache de las imagenes/posters se necesita allow_url_fopen en php.ini
    * Se necesitan permisos de lectura/escritura en cache y cache/images 775 si cambiamos el propietario al del servidor web o 777 (inseguro)

* Jackett
    Instalado y configurado añadiendo algunos indexers de peliculas/series.
    Necesitas la clave api de Jackett y acceso esterno activado para conectarse al servidor jackett, la clave ira en /etc/trackerm.conf

* Transmission-daemon
    Instalado/configurado y permitiendo las conexiones RPC a la ip del servidor

    * Aunque depende de la distro el archivo es settings.json en /etc/transmission y hay que parar el daemon primero antes de editar si no al parar/reiniciar sobreesribiran los cambios

    * Hay alguna version con un bug que obvia las ips rpc, si aparece un mensaje de error de whitelist prueba a desactivar el  rpc whitelist (a cuenta y riesgo)

    * Utilizo una libreria externa para el dialogo con transmission que hay que instalar via composer (ver instalación) 

* TheMovieDB.ORG    
    Es importante y no opcional para el correcto funcionamiento un API Ke, se utilizar para buscar peliculas/series, caratulas, identificar y demas.
    Necesitais una clave api de un proveedor, actualmente solo soporta themoviedb.org (el api key va en /etc/trackerm.conf utilizando la plantilla config/config.min.php)
    Quizas en el futuro se añadan otras alternativas pero de momento solo hay esta y es imprescindible. 
    Proporciono con la configuracion un API key que deberia funcionar pero podeis cambiar por uno propio.
