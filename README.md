# trackerm

![alt text](https://github.com/diegargon/trackerm/blob/master/screenshots/library-screenshot.png?raw=true)

## Description: 

**SPANISH**

Probando a realizar una alternativa Sonarr+Radar sobre servidor web.
Puedes ver sceenshots del aspecto (posiblemente desactualizado) en /screenshots aunque cambiara que el proyecto esta en fase temprana.

Warning: Gran parte del codigo fue realizado a correr (el grueso fue programado  en 3 intensos dias), no solo hay que pulirlo y reescribir mucho si no que 
hay que revisar la seguridad. Si lo instalas espera una aplicación alpha, osea un poco verde.


**ENGLISH**

Trying a Sonarr & Radarr alternative over a web server.
You can see screenshots of the  appearance (not latest probably) in /screenshots although it going to change since this proyect 
is in early stage.
Warning: Much of the code  was done in 3 days, have to polish alot/rewrite alot and check security. If you install expect a alpha version.

## LEGAL

ATTENTION: This software was made to maintain a private multimedia library of files of which you have rights of use. The misuse or illegal 
use of this program is solely responsibility of the user. 

This software uses third party search engines (added by the user) and it is the sole responsibility of the user to add a legal search engine and/or
click on the links whose content are ilegal in your country.

<b>Since we use third party search engines, we haven't control over search engine results</b>

This tool is for private/personal use, exposing this tool to internet for use by third parties may entail a crime according to your country 
and the rights of the material exposed. 

Please inform yourself of the laws in your country and use this software according to it.

## CURRENT STATUS

**ENGLISH**

About security, i begin adding checks but ins't secure yet for expose to internet (at least not well proven), beware, i recomend allow 
only local ips using .htaccess or other methods.

About code, after the changes the DB from plain/text to SQL among other things i have a lot of messy code that need rewrite and rewrite querys to database 
but for this type of application this is not a priority (performance).

You can use ISSUES for bugs and other things.

**SPANISH**

Sobre la seguridad, comence a añadir comprobaciones pero no es seguro todavía para esponerlo a internet, deberias de usar metodos añadidos como .htaccess 
o similar.

Sobre el codigo, despues de cambiar la BD de texto plano a SQL aparte de otras cosas el codigo esta muy enredado y necesito reescribir cosas así como querys
a la base de datos, pero para este tipo de aplicacion eso no es prioritario (rendimiento).

Puedes utilizar ISSUES para fallos y otras cuestiones/dudas.

## Requeriments

    Linux - PHP - Web Server - transmission-daemon - Jackett - themoviedb.org account&API key
    composer - sqlite3

    Version Compatibility? it's not the momment. I working on:
    Ubuntu 20.04 (probably any version that support the other deps versions works fine)
    Apache 2.4 (need >=2.4)
    Php 7.4 (php7 is necessary) 
    Sqlite 3.31.1 (need >=3)

    Phone Testing(visual): Android 9

## INSTALL

in INSTALL.es (Spanish) or in INSTALL.en, (bad english and probably not update).

## LATEST   

Go LATEST
