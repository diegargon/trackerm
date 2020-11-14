Description: 
Probando a realizar una alternativa Sonarr+Radar sobre servidor web

Warning: Codigo/esbozo realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo si no que esta sin seguridad, 
sin comprobaciones, con fallos, errores y todo eso... 

Probablemente de momento no deberias instalarlo.

1º Copiar los archivos de src a la carpeta destino (AKA: dest)

2º Instalar composer si no lo teneis, hay guias pero basicamente 

    $ curl -sS https://getcomposer.org/installer -o composer-setup.php

    $ php composer-setup.php --install-dir=/usr/local/bin --filename=composer

3º  Ir a la carpeta dest y teclear

    composer require irazasyed/php-transmission-sdk  php-http/httplug-pack  php-http/guzzle6-adapter


4º Para buscar, caratulas y demas necesitais una clave api de un proveedor, actualmente solo soporta themoviedb.com (el api key va en config.inc.php)

Otros:

* Necesitas transmission-daemon instalado y configurado y permitiendo las conexiones RPC a la ip del servidor

  NOTA 1: Aunque depende de la distro el archivo es settings.json en /etc/transmission y hay que parar el daemon primero antes de editar

  NOTA 2: Hay alguna version con un bug que obvia las ips rpc, si aparece un mensaje de error de whitelist prueba a desactivar el la rpc whitelist (a cuenta y riesgo)

  NOTA 3: Utilizo una libreria externa para el dialogo con transmission, esta "envuelta/wrapped" por si cambio de libreria, si fuera el caso espero acordarme de editar
        esto con las nuevas dependedncias de composer, si no...

  NOTA 4: Para el cache de las imagenes/posters se necesita allow_url_fopen en php.ini

  NOTA 5: Se necesitan permisos de lectura/escritura en cache y cache/images 775 si cambiamos el propietario al del servidor web o 777 (inseguro)
