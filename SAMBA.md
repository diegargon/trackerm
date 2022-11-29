If you have a samba mounted  directory (/srv/movies && /srv/shows) with your media, www-data and tracker-cli (if you run it non-root)
must have able to access to it RW.

In your apache config
 
<Directory /srv>
    allow from all
</Directory>


Transmission daemon must start after mounted and stop before mounted for prevent
Transmision startup errors (Missing files).

Example systemctl file for transmission-daemon (Ubuntu) 
/etc/systemd/system/multi-user.target.wants/transmission-daemon.service

[Unit]
Description=Transmission BitTorrent Daemon
After=network.target network-online.target remote-fs.target
Before=umount.target

In config->files there is a option for change the files group 

trackerm-cli must have rights for change it.

If you want rename files from web interface the www user must have the rights

Example: My samba shares group is 'shares' (RW) and i add www-data to shares group
