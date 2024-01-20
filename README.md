<p align="center">
<img src="https://github.com/diegargon/trackerm/blob/master/screenshots/library-screenshot.png?raw=true" width="100%">
</p>

# WARN third party library transmission-rpc is deprected/unmaintain (php8) and haven't time yet for fix it
# TrackerM

## Features 
    * For Movies & Shows 
    * UI Language: English/Spanish
    * Works with Jackett
    * Track shows: Download automatically episodes specifying the starting episode. Complete missing episodes.
    * Wanted List: Adding movies/shows to the wanted list will make TrackerM download automatically when released by any tracker.
    * Multiple options for tracker/wanted: Ignore/Require Keywords, Quality Preference, PROPER, etc
    * transmission-daemon integration (Sorry only works with transmission-daemon atm)
    * TheMovieDB search, show popular, trending, covers and other data all integrated
    * Torrents search using Jackett: Filter by Indexer, Words, Size, Freelech
    * List latest releases from your favorites indexers/trackers.
    * trailers & Covers for library & torrents (guess trailer/covers when missing)
    * Library: Search you library, Download media, Stats, Mark Seen/Unseen media. Show Collection (TMDB Collections, Genres, Director, Cast, Writer).
    * Library: Delete media
    * Library: Local Player (no active by default, see config options), play your movies/shows  launching VLC just clicking (the media share volume must be accesible)
    * Users: Basic IU designed for multiple users, password access, limit local ips (config)
    * Email: Automatically send email when TrackerM add new media to your library
    * Automatically link(when seeding) and move (finish seeding) from transmission-daemon  download directory to your library.
    * Proxy support
    * Display/Section list Transmission downloads
    * Cache Covers for fast experience
    * Automatically disabling (temporally) indexers offline or slow
    * Use SQLlite, not need a more complex database. (edit) well perhaps would be good for fast experience add another option like mysql.

## Description: 

**ENGLISH**

TrackerM is a Sonarr & Radarr alternative that run on a web server. TrackerM use jackett.

Jackett is an application that acts as an intermediary/gateway between trackers, forums or web pages that offer torrent files
and thus be able to obtain results in a standard way independent of the source.

Sonarr + Radarr, are applications that use jackett to maintain, organize, program and download multimedia files. One is
focused on shows and the other on movies.

TrackerM is a mixture of both made in php instead of C# (Sonarr / Radarr) that requires the installation of a web server.
Unlike the other TrackerM  obviates the official "release / publication dates" which are highly dependent on each
country. 

TrackerM only supports linux/unix, and transmission-daemon 

In /screenshots you will find some probably outdated screenshots

Warn: The application is functional but i need polish it and rewrite a lot of code 
and review security.

**SPANISH**

Alternativa a Sonarr+Radarr sobre servidor web haciendo uso como estos de jackett. 

Jackett es una aplicacion que hace intermediario o pasarela entre trackers, foros o paginas web que ofrecen archivos torrentes
y así poder obtener resultados de una forma standard independiente de la fuente.

Sonarr+Radarr, son aplicacines que utilizan jackett para mantener, organizar, programar, descargar archivos multimedia. Una esta 
centrada en series y otra en peliculas.

TrackerM es una mezcla de ambas realizada en php en vez C# (Sonarr/Radarr) que necesita de la instalación de un servidor web.
Al contrario que las otras trackerm obvia los "fechas de lanzamiento/publicacion" oficales que son muy dependientes de cada
pais. Las busquedas de series por ejemplo las hace semanalmente estableciendo por el usuario un dia.

TrackerM solo soporta linux/unix y transmission-daemon

En /screenshots encontraras algunos sceenshots del aspecto posiblemente desactualizados

Atención: Gran parte del codigo fue realizado a correr (el grueso fue programado  en 3 intensos dias). La aplicación es funcional pero  hay que pulirlo y reescribir mucho 
y revisar la seguridad. Si lo instalas espera una aplicación alpha.
 
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

About security: Begin adding checks but ins't secure yet for expose to internet , beware, i recomend allow 
only local ips using .htaccess or other methods.

About code: After the changes the DB from plain/text to SQL among other things i have a lot of messy code that need rewrite and rewrite querys to database 
but for this type of application this is not a priority (performance).

You can use ISSUES for bugs and other things.

**SPANISH**

Sobre la seguridad: Comence a añadir comprobaciones pero no es seguro todavía para esponerlo a internet, deberias de usar metodos añadidos como .htaccess 
o similar.

Sobre el codigo: Despues de cambiar la BD de texto plano a SQL aparte de otras cosas el codigo esta muy enredado y necesito reescribir cosas así como querys
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
    Curl
    Phone Testing(visual): Android 9

## INSTALL

in INSTALL.es (Spanish) or in INSTALL.en, (bad english and probably not update).

## LATEST   

Go LATEST
