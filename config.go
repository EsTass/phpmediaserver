package main

import (
    "fmt"
    
    //go get -u "github.com/spf13/viper"
    "github.com/spf13/viper"
)

//CONFIG

func configLoad() {
    viper.SetConfigName("config") // name of config file (without extension)
    viper.AddConfigPath(".")      // optionally look for config in the working directory
    err := viper.ReadInConfig()   // Find and read the config file
    if err != nil { // Handle errors reading the config file
        panic(fmt.Errorf("Fatal error config file: %s \n", err))
    }
}

//SPECIFIC VALUES

//SERVER IP BIND

func config_getAPPName() string {
    ident := "appname"
    result := viper.GetString( ident )
    return result
}

//SERVER IP BIND

func config_getServerIP() string {
    ident := "Server.bindip"
    result := viper.GetString( ident )
    return result
}

func config_getServerPort() string {
    ident := "Server.bindport"
    str := viper.GetString( ident )
    var result string
    if len(str) == 0 {
        result = G_SERVER_BIND_PORT
    } else {
        result = str
    }
    return result
}

func config_getServerHttps() bool {
    ident := "Server.https"
    result := viper.GetBool( ident )
    return result
}

func config_getServerHttpsCert() string {
    ident := "Server.httpscrt"
    result := viper.GetString( ident )
    return result
}

func config_getServerKey() string {
    ident := "Server.httpskey"
    result := viper.GetString( ident )
    return result
}

//HTTP SESSION

func config_sessionkey() []byte {
    ident := "Sessions.sessionkey"
    str := viper.GetString( ident )
    result := []byte( str )
    if len( str ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, str))
    }
    return result
}

//GEOIP

func config_getGeoIPFilter() []string {
    ident := "GeoIP.geoipfilter"
    result := viper.GetStringSlice( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

func config_getGeoIPSafe() []string {
    ident := "GeoIP.geoipsafe"
    result := viper.GetStringSlice( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

//FFMPEG FFPROBE

func config_getFFmpegCMD() string {
    ident := "Ffmpeg.ffmpegcmd"
    result := viper.GetString( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

func config_getFFprobeCMD() string {
    ident := "Ffmpeg.ffprobecmd"
    result := viper.GetString( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

func config_getFFmpegLQMin() string {
    ident := "Ffmpeg.ffmpegminbrlq"
    result := viper.GetString( ident )
    return result
}

func config_getFFmpegLQMax() string {
    ident := "Ffmpeg.ffmpegmaxbrlq"
    result := viper.GetString( ident )
    return result
}

func config_getFFmpegHQMin() string {
    ident := "Ffmpeg.ffmpegminbrhq"
    result := viper.GetString( ident )
    return result
}

func config_getFFmpegHQMax() string {
    ident := "Ffmpeg.ffmpegmaxbrhq"
    result := viper.GetString( ident )
    return result
}

//DB

func config_getDBFile() string {
    ident := "DataBase.dbfile"
    result := viper.GetString( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

//DEBUG

func config_getDebug() bool {
    ident := "debug"
    result := viper.GetBool( ident )
    return result
}

//DEBUG FILE

func config_getDebugFile() string {
    ident := "debugfile"
    result := viper.GetString( ident )
    return result
}

//MENU

func config_getGenres() []string {
    ident := "Menu.genres"
    result := viper.GetStringSlice( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

func config_getGenresAdapt() []string {
    ident := "Menu.genresadapt"
    result := viper.GetStringSlice( ident )
    if len( result ) == 0 {
        panic(fmt.Errorf("Fatal error config file: %s - %s \n", ident, result))
    }
    return result
}

//LISTS

func config_getListSize() int {
    ident := "Lists.longsize"
    result := viper.GetInt( ident )
    return result
}

func config_getListSizeMin() int {
    ident := "Lists.shortsize"
    result := viper.GetInt( ident )
    return result
}

//CRON

func config_getCronShort() string {
    ident := "Cron.cronshorttime"
    result := viper.GetString( ident )
    return result
}

func config_getCronLong() string {
    ident := "Cron.cronlongtime"
    result := viper.GetString( ident )
    return result
}

//FOLDERS DOWNLOAD

func config_getDownloadFolder() string {
    ident := "Downloads.serverfolder"
    result := viper.GetString( ident )
    return result
}

func config_getDownloadFolderExc() []string {
    ident := "Downloads.serverfolderexc"
    result := viper.GetStringSlice( ident )
    return result
}

func config_getDownloadFileTags() map[string]string {
    ident := "FileInfoTags"
    result := viper.GetStringMapString( ident )
    return result
}

//FOLDER DOWNLOAD CLEANERS

func config_getRemoveSafeDays() int {
    ident := "Downloads.removesafedays"
    result := viper.GetInt( ident )
    return result
}

func config_getRemoveSizePriority() int {
    ident := "Downloads.removesizepriority"
    result := viper.GetInt( ident )
    return result
}

func config_getDownloadLowSpace() int {
    ident := "Downloads.downloadslowspace"
    result := viper.GetInt( ident )
    return result
}

func config_getCleanMode() string {
    ident := "Downloads.downloadscleanmode"
    result := viper.GetString( ident )
    return result
}

func config_getRemoveDirsSize() int {
    ident := "Downloads.removeolddirssize"
    result := viper.GetInt( ident )
    return result
}

//FILE SCRAPPERS

func config_getCronScrapper() string {
    ident := "FileScrapp.cronscrapper"
    result := viper.GetString( ident )
    return result
}

func config_getScrapperCleanTitle() []string {
    ident := "FileScrapp.titleclean"
    result := viper.GetStringSlice( ident )
    return result
}

func config_getScrapperSeasonDetect() []string {
    ident := "FileScrapp.seasonepisodedetect"
    result := viper.GetStringSlice( ident )
    return result
}

func config_getScrapperSeasonExc() []string {
    ident := "FileScrapp.seasonepisodeexclude"
    result := viper.GetStringSlice( ident )
    return result
}

func config_getScrapperSeasonMax() int {
    ident := "FileScrapp.seasonmax"
    result := viper.GetInt( ident )
    return result
}

func config_getScrapperEpisodeMax() int {
    ident := "FileScrapp.episodemax"
    result := viper.GetInt( ident )
    return result
}

//FILE SCRAPPERS: FILEBOT

func config_getFilebotCmd() string {
    ident := "Filebot.filebotcmd"
    result := viper.GetString( ident )
    return result
}

func config_getFilebotLang() string {
    ident := "Filebot.filebotlang"
    result := viper.GetString( ident )
    return result
}

//OMDB

func config_getOmdbApiKey() string {
    ident := "OMDb.omdbapikey"
    result := viper.GetString( ident )
    return result
}

//TheTVDB

func config_getTheTVDBApiKey() string {
    ident := "TheTVDB.thetvdbapikey"
    result := viper.GetString( ident )
    return result
}

func config_getTheTVDBLang() string {
    ident := "TheTVDB.thetvdblang"
    result := viper.GetString( ident )
    return result
}

//PyMediaIdent

func config_getPymiCmd() string {
    ident := "pymi.pymicmd"
    result := viper.GetString( ident )
    return result
}

func config_getPymiLang() string {
    ident := "pymi.pymilang"
    result := viper.GetString( ident )
    return result
}
