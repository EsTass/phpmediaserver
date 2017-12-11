# phpmediaserver
A bunch of utilities for:
 - Easy html5 web player with php7 + sqlite + jquery + ffmpeg.
 - Easy config.php and config.ws.php
 - Admins and player users.
 - Realtime transcoding, not needed to reencode before play.
 - Identify media files thanks to: www.filebot.net, www.omdbapi.com, www.thetvdb.com (cron, manual and helped).
 - Search new media to downloads adding webs with simple config (elinks, magnets and torrents supported, cron or manual).
 - Clean duplicates by quality with safe seeding (min days to seed)
 - Country IP block thanks to www.geoplugin.net.
 - IP whitelist/blacklist.
 - Media info in configured language (if possible).
 - Lazy Load Images thanks to: http://jquery.eisbehr.de/lazy
 - Extract files on cron (php support needed Zip and Rar)

## Default User (Important: change pass on first login)
 - User: admin
 - Pass: admin01020304
 
## Install
Copy files to your server folder and edit:
 - `config.php`: basic configuration, read comments and edit needed
 - `config.ws.php`: only if want to add more web searchs for media (example provided)

## Apache vhost
If needed add vhost file to apache and include or edit main host conf. 

Example vhost:
```
<Directory /path/to/server/>
    Options FollowSymlinks
    AllowOverride all
    Require all granted
    #Edit Paths
    php_admin_value open_basedir "/path/to/server/:/path/to/downloads"
    #block all except my IP
    #Order deny,allow
    #Deny from all
    #Allow from MYIP
    #all
    Allow from all
    DirectoryIndex index.php
</Directory>

NameVirtualHost *:80
<VirtualHost *:80>
   ServerName YOURDOMAIN
   Redirect permanent / https://YOURDOMAIN
</VirtualHost>
<VirtualHost *:443>
   ServerName YOURDOMAIN
   ServerAdmin YOUREMAIL@email.com
   DocumentRoot /path/to/server
   ServerName YOURDOMAIN
   ErrorLog /var/log/httpd/YOURLOGNAME
   CustomLog /var/log/httpd/YOURLOGNAME common
   <IfModule mod_headers.c>
      Header always set Strict-Transport-Security "max-age=15768000; includeSubDomains; preload"
   </IfModule>

</VirtualHost>
```

## PHP config

If needed add paths to php.ini:
```
open_basedir = ...:/path/to/server/:/path/to/downloads
```

Needed extension in php:
```
extension=curl.so
extension=iconv.so
extension=mcrypt.so
extension=pdo_sqlite.so
extension=sqlite3.so
extension=sockets.so
extension=xmlrpc.so
extension=zip.so
```

Restart apache, and login with default user/pass.
