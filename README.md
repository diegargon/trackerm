# trackerm

![alt text](https://github.com/diegargon/trackerm/blob/master/screenshots/library-screenshot.png?raw=true)

## Description: 

**SPANISH**

Probando a realizar una alternativa Sonarr+Radar sobre servidor web.
Puedes ver sceenshots del aspecto actual(posiblemente desactualizado) en /screenshots aunque cambiara que el proyecto esta en fase muy 
muy temprana.

Warning: Codigo/esbozo realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo y reescribir mucho si no que esta sin 
seguridad y asi continuara hasta que tenga una version con las funciones basicas.
Probablemente de momento no deberias instalarlo.


**ENGLISH**

Trying a Sonarr & Radarr alternative over a web server.
You can see screenshots of the current appearance (not latest probably) in /screenshots although it going to change since this proyect 
is in a very very early stage.
Warning: Fast coding (the bulk was done in 3 days) have to polish alot/rewrite alot and came without any security, and 
will remain like this until i have a working code with basic features.
Probably you shouldn't install it at this moment.

## LEGAL

ATTENTION: This software was made to maintain a private multimedia library of files of which you have rights of use. The misuse or illegal 
use of this program is solely responsibility of the user. 

This software  uses third  party search engines (add by the user) and it is the sole responsibility of the  user to click on the links whose 
content is legal to use in your country.

<b>Since we use third party search engines, we haven't control over search engine results</b>

This tool is for private/personal use, exposing this tool to the internet for use of third parties may entail a crime according to your country 
and the rights of the material you expose. 

Please inform yourself of the laws in your country.

## CURRENT STATUS

**ENGLISH**

Now we use a sql database (sqllite) instead of plain text, i can't guarantee backwards compatibility between versions yet, but will
not be something frequent if happens. Anyway, all work for setting from 0 is near automatic, just only click on rebuild the library and identify items.

About security, i begin adding checks but ins't secure yet for expose to internet (you can't set a security passwords for enter), you must still 
use other method like .htaccess or similar

About code, after the changes from plain/text among other things i have a lot of messy code that need rewrite and rewrite querys to database 
but for this type of application this is not a priority.

I 'fast coding' this app in about 10 days, now for a while i would have less time to update, and going to slow down this focusing to fix 
the messy code, bugs and security things than add new options.

You can use ISSUES for bugs and other things.

**SPANISH**

Ahora utilizo una base de datos sql en vez de archivos de texto, no puedo todavia garantizar compatibilidad entre versiones pero no sera 
frecuente si pasa. De todas formas, configurarlo de 0 es facil al ser casi automatico, escanear de nuevo la libreria y identificar los objetos/media

Sobre la seguridad, comence a añadir comprobaciones pero no es seguro todavía para esponerlo a internet (y no hay contraseñas de seguridad para entrar),
debes de usar otros metodos como .htaccess o similar.

Sobre el codigo, despues de cambiar de texto plano a SQL aparte de otras cosas el codigo esta muy enredado y necesito reescribir cosas así como querys
a la base de datos, pero para este tipo de aplicacion eso no es prioritario.

Escribi esta aplicacion tecleando codigo rapido en 10 dias, ahora porun tiempo tendre menos tiempo para actualizar y ralentizare esto un poco y me centrare
en mejorar el codigo que esta liado, bugs y temas relativos a la seguridad antes que añadir más opciones.

## WARNING

**ENGLISH**

There are no security mechanisms in any line of code yet, use on your own risk. The code is totally insecure. 
If you expose this code to internet you have a very high security problem. why? want this app "now" and 
have too much time but in few days,the solution was quick code without stopping and without pay attention
to security  details. 
Security and better code will comming more slowly

**SPANISH**

No hay ningun mecanismo de seguridad en ninguna linea del codigo todavia. El codigo es totalmente inseguro. 
Si expones este codigo a internet tendras un grave problema de seguridad. ¿por que? queria esta aplicación 
"ya" y tenia mucho tiempo pero en pocos dias, la solución fue teclear codigo rapido y sin pararme en detalles 
de seguridad.
Seguridad y mejor codigo vendra mucho mas despacio.


## Requeriments

    Linux - PHP - Web Server - transmission-daemon - Jackett - themoviedb.org account&API key
    composer - sqlite3

    Version Compatibility? it's not the momment. I working on:
    Ubuntu 20.04
    Php 7.4 (php7 is necessary)
    Sqlite 3.31.1

## INSTALL

in INSTALL.es (Spanish) or in INSTALL.en, (bad english and probably not update).

## LATEST   

Go LATEST
