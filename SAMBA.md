If you have a samba mounted  directory (/srv/movies && /srv/shows) with your media, www-data and tracker-cli (if you run it non-root)
must have able to access to it RW.

In your apache config
 
<Directory /srv>
    allow from all
</Directory>


Transmission daemon must start after mounted and stop before mounted

systemctl file for transmission-daemon

[Unit]
...
After= network.target network-online.target remote-fs.target
Before=umount.target


In config->files there is a option for change group (trackerm-cli must have right for do it), 
i use example 'shares' and i add www-data to shares group
