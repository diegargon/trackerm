If you have a samba mounted  directory (/srv/movies && /srv/shows) with your media, www-data must have able to access to it and to your apache config
In 
<Directory /srv>
    allow from all
</Directory>