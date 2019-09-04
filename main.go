package main

import (
    "fmt"
    "net/http"
    "crypto/tls"
    //"html/template"
    //"crypto/sha1"
    //"encoding/base64"
    "time"
    "log"
    "os"
    //"strings"
    //"path/filepath"
    
    //go get github.com/gorilla/sessions
    "github.com/gorilla/sessions"
    
    //go get "github.com/mattn/go-sqlite3"
    //"database/sql"
    //_ "github.com/mattn/go-sqlite3"
    
    //go get -u "github.com/spf13/viper"
    //"github.com/spf13/viper"
    
    //go get "github.com/robfig/cron"
    //"github.com/robfig/cron"
    
    //go get -u github.com/kenshaw/imdb
    //"github.com/kenshaw/imdb"
    
    //go get "github.com/pioz/tvdb"
    //"github.com/pioz/tvdb"
)

//CONFIG

var (
    //PATHS
    G_APPPATH           = ""
    G_CACHE_FOLDER      = pathJoin(".", "cache")
    G_TMP_FOLDER        = pathJoin( G_CACHE_FOLDER, "temp" )
    G_IMAGES_FOLDER     = pathJoin( G_CACHE_FOLDER, "mediadata" )
    
    //App Data
    G_APPNAME           = "GMS"
    
    //Server data
    G_SERVER_BIND_IP    = "127.0.0.1"
    G_SERVER_BIND_PORT  = "8080"
    G_SERVER_HTTPS      = false
    G_SERVER_HTTPS_CERT = pathJoin(".", "certificate.crt")
    G_SERVER_HTTPS_KEY  = pathJoin(".", "private.key")
    
    //FFMPEG
    G_FFMPEG_CMD        = "ffmpeg"
    G_FFPROBE_CMD       = "ffprobe"
    G_FFMPEG_LQ_MIN     = "1M"
    G_FFMPEG_LQ_MAX     = "1M"
    G_FFMPEG_HQ_MIN     = "2M"
    G_FFMPEG_HQ_MAX     = "2M"
    
    //GEOIP
    G_GEOIPFILTER       = []string {}
    G_GEOIPSAFE         = []string {}
    
    //DEBUG
    G_DEBUG             = true
    G_DEBUGFILEVIDEO    = ""
    
    //Sessions Keys
    //G_STORE = sessions.NewCookieStore(config_sessionkey())
    G_STORE             *sessions.CookieStore
    
    //SQLITE
    G_SQLFILE           = pathJoin( G_CACHE_FOLDER, "data.db" )
    
    //GENRES BASE
    G_GENRES            = []string { "Comedy", "Action", "Fiction", "Mistery", "Terror", "Family", "Documental" }
    G_GENRES_ADAPT      = []string {}
    
    //Lists Elements
    G_LISTSIZE          = 128
    G_LISTSIZE_MIN      = 16
    
    //Cron
    G_CRONSHORTTIME     = ""
    G_CRONSHORTTIME_FILE= pathJoin( G_CACHE_FOLDER, "cronshort.log" )
    G_CRONLONGTIME      = ""
    G_CRONLONGTIME_FILE = pathJoin( G_CACHE_FOLDER, "cronlong.log" )
    
    //Downloads TODO
    G_DOWNLOADS_FOLDER      = pathJoin( G_CACHE_FOLDER, "downloads" )
    G_DOWNLOADS_FOLDER_EXC  = []string {".tmp"}
    G_DOWNLOADS_FILETAGS    = map[string]string {}
    G_DOWN_SAFEDAYS         = 0
    G_DOWN_SIZEPRIO         = 0
    G_DOWN_LOWSPACE         = 0
    G_DOWN_CLEANMODE        = ""
    G_DOWN_DIRMINSIZE       = 0
    
    //Scrapping
    G_SCRAPPERLIST      = []string { "mydb", "filebot", "omdb", "thetvdb", "pymi" }
    G_CRONSCRAPPER      = "filebot"
    G_SCRAPPSEDETECT    = []string { `/([0-9]{1,2}) {0,1}[x,X]([0-9]{1,3})/` }
    G_SCRAPPSEDETECTEXC = []string { "1080" }
    G_SCRAPPSEASONMAX   = 30
    G_SCRAPPEPISODEMAX  = 300
    //Filebot
    G_FILEBOTCMD        = "filebot"
    G_FILEBOTLANG       = "en"
    
    //Image Types
    G_MEDIA_IMAGES      = []string { "poster", "logo", "banner", "landscape", "fanart", "folder" }
    G_MEDIA_IMAGES_TYPE = []string { "jpg", "png", "jpeg", "gif" }
    
    //WebSearch
    G_WS_DDG            = "ddgr"
    G_WS_GOOGLER        = "googler"
    
    //OMDB
    G_OMDB_APIKEY       = ""
    
    //THETVDB
    G_THETVDB_APIKEY    = ""
    G_THETVDB_LANG      = ""
    
    //PYMEDIAIDENT
    G_PYMI_CMD          = ""
    G_PYMI_LANG         = ""
    
    //PLAYER
    G_PLAYER_CODECS     = map[string]string {
        //diret modes only to external players (Kodi, NOHTML5)
        //"direct" : "video/matroska",  
        //"fast" : "video/matroska", 
        "webm" : "video/webm", 
        "mp4" : "video/mp4", 
        "webm2" : "video/webm", 
    }
)

//EXTRAS

//Force JSON to send Float as Float
type JsonFloat float64

func (n JsonFloat) MarshalJSON() ([]byte, error) {
	return []byte(fmt.Sprintf("%f", n)), nil
}

//MAIN

func init() {
    //Configs
    showInfo( "CONFIGS-LOAD " )
	configLoad()
    
    G_APPNAME = config_getAPPName()
    //check testing on tmp folder
    if strInStr(os.Args[0], "tmp") {
        G_APPPATH        = "."
    } else {
        G_APPPATH        = appPath()
    }
    showInfo( "CONFIG-G_APPPATH: " + G_APPPATH )
    
    G_SERVER_BIND_IP        = config_getServerIP()
    showInfo( "CONFIG-G_SERVER_BIND_IP: " + G_SERVER_BIND_IP )
    G_SERVER_BIND_PORT      = config_getServerPort()
    showInfo( "CONFIG-G_SERVER_BIND_PORT: " + G_SERVER_BIND_PORT )
    
    G_GEOIPFILTER           = config_getGeoIPFilter()
    G_GEOIPSAFE             = config_getGeoIPSafe()
    
    G_SERVER_HTTPS          = config_getServerHttps()
    G_SERVER_HTTPS_CERT     = config_getServerHttpsCert()
    G_SERVER_HTTPS_KEY      = config_getServerKey()
    if G_SERVER_HTTPS {
        showInfo( "CONFIG-G_SERVER_HTTPS: HTTPS MODE" )
    }
    
    //FFMPEG
    G_FFMPEG_CMD            = config_getFFmpegCMD()
    G_FFPROBE_CMD           = config_getFFprobeCMD()
    G_FFMPEG_LQ_MIN         = config_getFFmpegLQMin()
    G_FFMPEG_LQ_MAX         = config_getFFmpegLQMax()
    G_FFMPEG_HQ_MIN         = config_getFFmpegHQMin()
    G_FFMPEG_HQ_MAX         = config_getFFmpegHQMax()
    
    G_STORE                 = sessions.NewCookieStore(config_sessionkey())
    
    G_SQLFILE               = config_getDBFile()
    showInfo( "CONFIG-G_SQLFILE: " + G_SQLFILE )
    
    G_DEBUG                 = config_getDebug()
    if G_DEBUG {
        showInfo( "CONFIG-G_DEBUG: DEBUG MODE ON" )
    }
    G_DEBUGFILEVIDEO        = config_getDebugFile()
    showInfo( "CONFIG-G_DEBUGFILEVIDEO: " + G_DEBUGFILEVIDEO )
    
    //MENU
    G_GENRES                = config_getGenres()
    G_GENRES_ADAPT          = config_getGenresAdapt()
    
    G_LISTSIZE              = config_getListSize()
    G_LISTSIZE_MIN          = config_getListSizeMin()
    
    G_CRONSHORTTIME         = config_getCronShort()
    G_CRONLONGTIME          = config_getCronLong()
    
    G_DOWNLOADS_FOLDER      = config_getDownloadFolder()
    showInfo( "CONFIG-G_DOWNLOADS_FOLDER: " + G_DOWNLOADS_FOLDER )
    G_DOWNLOADS_FOLDER_EXC  = config_getDownloadFolderExc()
    G_DOWNLOADS_FILETAGS    = config_getDownloadFileTags()
    
    //G_TMP_FOLDER            = "./cache/temp"
    G_DOWN_SAFEDAYS         = config_getRemoveSafeDays()
    G_DOWN_SIZEPRIO         = config_getRemoveSizePriority()
    G_DOWN_LOWSPACE         = config_getDownloadLowSpace()
    G_DOWN_CLEANMODE        = config_getCleanMode()
    G_DOWN_DIRMINSIZE       = config_getRemoveDirsSize()
    
    //Scrapping
    G_CRONSCRAPPER          = config_getCronScrapper()
    showInfo( "CONFIG-G_CRONSCRAPPER: " + G_CRONSCRAPPER )
    G_SCRAPPSEDETECT        = config_getScrapperSeasonDetect()
    G_SCRAPPSEDETECTEXC     = config_getScrapperSeasonExc()
    G_SCRAPPSEASONMAX       = config_getScrapperSeasonMax()
    G_SCRAPPEPISODEMAX      = config_getScrapperEpisodeMax()
    //Filebot
    G_FILEBOTCMD            = config_getFilebotCmd()
    G_FILEBOTLANG           = config_getFilebotLang()
    
    //OMDB
    G_OMDB_APIKEY           = config_getOmdbApiKey()
    
    //THETVDB
    G_THETVDB_APIKEY        = config_getTheTVDBApiKey()
    G_THETVDB_LANG          = config_getTheTVDBLang()
    
    //PYMEDIAIDENT
    G_PYMI_CMD              = config_getPymiCmd()
    G_PYMI_LANG             = config_getPymiLang()
    
    //PLAYER
    //direct, fast, mp4, webm, webm2
    /*
    G_PLAYER_CODECS         = {
        "direct" : "video/matroska",  
        "fast" : "video/matroska", 
        "mp4" : "video/mp4", 
        "webm" : "video/webm", 
        "webm2" : "video/webm", 
    }
    */
    
    showInfo( "CONFIGS-LOAD-END " )
}

func main() {
    
    showInfo( "CRON-STARTING" )
    cronSet()
    showInfo( "CRON-STARTED" )
    
    showInfo( "HTTP-SERVER-STARTING" )
    
    serverdata := http.NewServeMux()
    
    //Assets direct files
    fs := http.FileServer(http.Dir(pathJoin(G_APPPATH, "assets")))
    serverdata.Handle("/assets/", http.StripPrefix("/assets/", fs))
    
    //Debug Mode TEST
    if G_DEBUG {
        serverdata.HandleFunc("/test/", testBase)
    }
    
    //Base Users
    serverdata.HandleFunc("/", listBase)
    serverdata.HandleFunc("/list/", listBase)
    serverdata.HandleFunc("/login/", login)
    serverdata.HandleFunc("/logout/", logout)
    
    serverdata.HandleFunc("/mediainfo/", mediainfoInfo)
    serverdata.HandleFunc("/mediainfo-next/", mediainfoNext)
    serverdata.HandleFunc("/mediainfo-chapters/", mediainfoChapters)
    serverdata.HandleFunc("/mediainfo-img/", mediainfoImg)
    serverdata.HandleFunc("/mediainfo-download/", mediainfoDownload)
    
    serverdata.HandleFunc("/livetv/", liveTVList)
    serverdata.HandleFunc("/livetv-player/", liveTVPlayer)
    serverdata.HandleFunc("/livetv-play-time/", liveTVPlayTime)
    
    serverdata.HandleFunc("/actor-img/", actorImg)
    
    serverdata.HandleFunc("/media-player/", mediaPlayer)
    serverdata.HandleFunc("/media-play-time/", mediaPlayTime)
    serverdata.HandleFunc("/media-play-settime/", mediaPlaySetTime)
    serverdata.HandleFunc("/media-subs-load/", mediaPlaySubLoad)
    
    //Admins
    //Logs
    serverdata.HandleFunc("/logs-list/", logsList)
    //Media
    serverdata.HandleFunc("/media-list/", mediaListIdent)
    serverdata.HandleFunc("/identify-e-show/", mediaListIdentShow)
    serverdata.HandleFunc("/identify-e-delete/", mediaListIdentDelete)
    serverdata.HandleFunc("/identify-e-assign/", mediaListIdentAssign)
    serverdata.HandleFunc("/identify-e-search/", mediaListIdentSearch)
    serverdata.HandleFunc("/identify-e-assignd/", mediaListIdentAssignD)
    serverdata.HandleFunc("/identify-e-clean/", mediaListIdentAssignC)
    //Mediainfo
    serverdata.HandleFunc("/mediainfo-list/", mediaInfoListIdent)
    serverdata.HandleFunc("/mediainfo-e-delete/", mediaInfoListIdentDelete)
    //LiveTV
    serverdata.HandleFunc("/livetv-list/", liveTVAdminList)
    serverdata.HandleFunc("/livetv-e-delete/", liveTVAdminRemove)
    serverdata.HandleFunc("/livetv-e-add/", liveTVAdminAdd)
    serverdata.HandleFunc("/livetv-e-check/", liveTVAdminCheck)
    serverdata.HandleFunc("/livetv-all-check/", liveTVAdminCheckAll)
    //Users
    serverdata.HandleFunc("/users-list/", usersListBase)
    serverdata.HandleFunc("/users-add/", usersListAdd)
    serverdata.HandleFunc("/users-change-pass/", usersListPassChange)
    serverdata.HandleFunc("/users-delete/", usersListDelete)
    serverdata.HandleFunc("/users-deladmin/", usersListDelAdmin)
    serverdata.HandleFunc("/users-setadmin/", usersListSetAdmin)
    //Played
    serverdata.HandleFunc("/played-list/", playedListBase)
    serverdata.HandleFunc("/played-e-delete/", playedListBaseDelete)
    serverdata.HandleFunc("/playing-e-delete/", playingListBaseDelete)
    //Cron info
    serverdata.HandleFunc("/cron-list/", cronListBase)
    //IPs info
    serverdata.HandleFunc("/ip-list/", ipListBase)
    serverdata.HandleFunc("/ip-wl-add/", ipWlAdd)
    serverdata.HandleFunc("/ip-wl-remove/", ipWlRemove)
    serverdata.HandleFunc("/ip-bans-add/", ipBansAdd)
    serverdata.HandleFunc("/ip-bans-remove/", ipBansRemove)
    
    showInfo( "HTTP-SERVER-STARTED " )
    
    if G_SERVER_HTTPS == false {
        server := &http.Server{
            Addr                : G_SERVER_BIND_IP + ":" + G_SERVER_BIND_PORT,
            Handler             : serverdata,
            ReadTimeout         : 10 * time.Second,
            WriteTimeout        : 10 * time.Second,
            MaxHeaderBytes      : 1 << 20,
        }
        server.ListenAndServe()
    } else {
        cfg := &tls.Config{
            MinVersion                  : tls.VersionTLS12,
            CurvePreferences            : []tls.CurveID{tls.CurveP521, tls.CurveP384, tls.CurveP256},
            PreferServerCipherSuites    : true,
            CipherSuites                : []uint16{
                tls.TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384,
                tls.TLS_ECDHE_RSA_WITH_AES_256_CBC_SHA,
                tls.TLS_RSA_WITH_AES_256_GCM_SHA384,
                tls.TLS_RSA_WITH_AES_256_CBC_SHA,
            },
        }
        server := &http.Server{
            Addr                : G_SERVER_BIND_IP + ":" + G_SERVER_BIND_PORT,
            Handler             : serverdata,
            ReadTimeout         : 10 * time.Second,
            WriteTimeout        : 10 * time.Second,
            MaxHeaderBytes      : 1 << 20,
            TLSConfig           : cfg,
            TLSNextProto        : make(map[string]func(*http.Server, *tls.Conn, http.Handler), 0),
        }
        log.Fatal(server.ListenAndServeTLS(G_SERVER_HTTPS_CERT, G_SERVER_HTTPS_KEY))
    }
    
    showInfo( "HTTP-SERVER-ENDED" )
}