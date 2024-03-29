# phpmediaserver

![image.png](https://github.com/leonardoderoy/phpmediaserver/blob/master/imgs/logo/1.png?raw=true)

## Screenshots

![phpmediaserver](https://media.giphy.com/media/kBkna0rSjksQyQlfZ3/source.gif)

[IMG1](https://i.postimg.cc/d1DTSmDV/1.png)
[IMG2](https://i.postimg.cc/yxZ371mq/2.png)
[IMG3](https://i.postimg.cc/DZDbVJbd/3.png)

## App version (portable exe)

![gomediaserver](https://github.com/EsTass/gomediaserver)

## Description
A bunch of utilities for:
 - Html5 web player with php7 + sqlite + jquery + ffmpeg
 - WebPlayer support for audio and subs tracks selector
 - Poster list with search by genres, actors, years or rating
 - Groups by premiere, continue, recomended and last added
 - Easy configuration with config.php and config.ws.php
 - Admins and player users
 - Realtime ffmpeg transcoding of any type of video supported by ffmpeg, not needed to reencode before play or create temp files
 - Identify media files thanks to: [pymediaident](https://github.com/EsTass/pymediaident), www.filebot.net, www.omdbapi.com, www.thetvdb.com, [PHP-IMDB-Grabber](https://github.com/FabianBeiner/PHP-IMDB-Grabber) (cron, manual and helped).
 - Search and download new media from web adding scrappers with config.ws.php (youtube, elinks, magnets, torrents and dd supported, cron or manual with any external program like transmission, jdownloader, amule, qbittorent, etc).
 - Clean duplicates by quality with safe seeding (min days to seed) and max filesize to maintanin
 - Country IP block thanks to www.geoplugin.net and www.ipgeolocation.io (apikey needed)
 - IP whitelist/blacklist (autoban non included countrys)
 - Media info in configured language (if possible).
 - Lazy Load Images thanks to: http://jquery.eisbehr.de/lazy
 - Extract files on cron (php support needed Zip and Rar)
 - Filtered remove files to recover extra free space (manual, helped and cron)
 - Stop adding downloads on min space config
 - Mini dlna server thanks to: https://github.com/ttyridal/phpdlna && https://github.com/ampache/ampache/ (tested vlc and android)
 - [Kodi pluging](https://github.com/EsTass/phpmediaserver-kodi)
 - Multilanguaje (lang/CODE.php and select on config, default ENG)
 - Logo thanks to [leonardoderoy](https://github.com/leonardoderoy)
 - AMD Hardware vaapi transcoding support
 - NVIDIA Hardware nvenc transcoding support

## Default User (Important: change pass on first login)
 - User: admin
 - Pass: admin01020304
 
## Install

[Full Install on Linux(arch/apache)](https://github.com/EsTass/phpmediaserver/wiki/Install-In-linux-(arch-apache))

[Full Install on Linux(arch/nginx)](https://github.com/EsTass/phpmediaserver/wiki/Install-in-Linux-(arch-nginx))

Copy all files to your server folder and edit:
 - `config.php`: basic configuration, read comments and edit needed
 - `config.ws.php`: only if want to add more web searchs for media (examples provided)

## Install: Apache vhost
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
   #COMMENT IF DLNA SERVER or calls changed to https or only use domainname
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

## Install: PHP config

If needed add paths to php.ini (needed access to download folder):
```
open_basedir = ...:/path/to/server/:/path/to/downloads
```

Needed extension in php:
```
extension=curl.so
extension=iconv.so
extension=pdo_sqlite.so
extension=sqlite3.so
extension=sockets.so
extension=xmlrpc.so
extension=zip.so
#for dlna server
extension=soap.so
```

Restart apache, and login with default user/pass.

## Needed
 - [ffmpeg and ffprobe](https://ffmpeg.org/)
 
## Recomended
- [pymediaident](https://github.com/EsTass/pymediaident)
- [Filebot](https://www.filebot.net)
 - [wget](https://www.gnu.org/software/wget/)
 - [omdbapi APIKEY](https://www.omdbapi.com)
 - [www.thetvdb.com APIKEY](https://www.thetvdb.com)
 - [PHP-IMDB-Grabber](https://github.com/FabianBeiner/PHP-IMDB-Grabber)
 - [googler](https://pypi.python.org/pypi/googler)
 - [ddgr](https://github.com/jarun/ddgr)

## Admin SQLite DB

Adding https://www.phpliteadmin.org/ file to base folder add a menu entry to access sqlite DB file (need to configure db file and user in phpliteadmin file).
