package main


import (
    "os"
    "fmt"
    //"log"
    "path/filepath"
    //"regexp"
	"os/exec"
    "strings"
    "time"
    "unicode/utf8"
    "net/http"
	"io"
	"strconv"
    "encoding/json"
    
    //go get -u github.com/kenshaw/imdb
    "github.com/kenshaw/imdb"
    
    //go get "github.com/pioz/tvdb"
    "github.com/pioz/tvdb"
)

//BASE

//CLEAN TITLE

func fscrap_cleanTitle( title string ) string {
    result := title
    
    cleanlist := config_getScrapperCleanTitle()
    for _, cre := range cleanlist {
        //showInfo( "TITLE-CLEAN: " + title + " => " + result + " => " + cre )
        result = regExpReplaceData( result, "", "(?i)" + cre )
    }
    result = regExpReplaceData( result, " ", `\s{2,}` )
    result = regExpReplaceData( result, " ", `\.{1,}` )
    result = regExpReplaceData( result, " ", `_{1,}` )
    result = strings.Trim( result, " .-_" )
    //extract and add year
    year := fscrap_getYear( title )
    if len(year) > 0 {
        result = strings.Replace( result, year, "", -1 )
        result += " " + year
    }
    showInfo( "TITLE-CLEAN-RESULT: " + title + " => " + result )
    
    return result
}

//NORMALIZE TITLE FOR COMPARE

func fscrap_normalizeTitle( title string ) string {
    result := title
    
    result = regExpReplaceData( result, "a", `([^\w\s])` )
    result = regExpReplaceData( result, "a", `([aeiou])` )
    result = strings.Trim( result, " .-_" )
    
    return result
}

//IMAGES TO MEDIAIDENT

func fscrap_images_assign( folder string, idmedia string ) {
    //Try images: poster, logo, banner, landscape, fanart, folder
    for _, img := range G_MEDIA_IMAGES {
        for _, imgt := range G_MEDIA_IMAGES_TYPE {
            fileimg := pathJoin( folder, img + "." + imgt )
            if fileExist( fileimg ) {
                tmpfile := pathJoin( G_IMAGES_FOLDER, idmedia + "." + img )
                //err := os.Symlink(fileimg, tmpfile)
                err := os.Link(fileimg, tmpfile)
                if err != nil {
                    fmt.Println(err)
                } else {
                    break
                }
            }
        }
    }
}

//YEAR

func fscrap_getYear( title string ) string {
    result := ""
    
    data := regExpGetData( title, `(\d{4})` )
    for _, y := range data {
        y2 := strToInt( y )
        showInfo( "YEAR-DETECT: " + y )
        if y2 > 1940 && y2 <= ( time.Now().Year() + 1 ) {
            showInfo( "YEAR-DETECT-FINDED: " + y )
            result = y
            break
        }
    }
    
    return result
}

//SEASONS x CHAPTERS

func fscrap_getSeasonChapter( title string ) ( string, string ) {
    sedetect := config_getScrapperSeasonDetect()
    for _, cre := range sedetect {
        showInfo( "EPISODE-DETECT: " + title + " => " + cre )
        data := regExpGetData2( title, cre )
        //fmt.Println( data )
        if len( data ) > 1 {
            v1 := strToInt( data[0] )
            v2 := strToInt( data[1] )
            v3 := strToInt( data[0] + data[1] )
            v4 := data[0] + data[1]
            showInfo( "EPISODE-DETECT-RESULT: " + data[0] + " => " + data[1] + " => " + data[0] + data[1] )
            if stringInSlice( v4, G_SCRAPPSEDETECTEXC ) == false && (v3 < 1940 || v3 > ( time.Now().Year() + 1 )) && v1 <= G_SCRAPPSEASONMAX && v1 > 0 && v2 <= G_SCRAPPEPISODEMAX && v2 > 0 {
                showInfo( "EPISODE-DETECT: " + title + " => " + data[0] + " => " + data[1] )
                return intToStr( strToInt( data[0] ) ), intToStr( strToInt( data[1] ) )
            }
        }
    }
    
    return "", ""
}

func fscrap_getSeasonChapterPos( title string ) int {
    result := len(title)
    sedetect := config_getScrapperSeasonDetect()
    for _, cre := range sedetect {
        showInfo( "EPISODE-DETECT-POS: " + title + " => " + cre )
        data := regExpGetData2( title, cre )
        //fmt.Println( data )
        if len( data ) > 1 {
            v1 := strToInt( data[0] )
            v2 := strToInt( data[1] )
            v3 := strToInt( data[0] + data[1] )
            v4 := data[0] + data[1]
            showInfo( "EPISODE-DETECT-POS-RESULT: " + data[0] + " => " + data[1] + " => " + data[0] + data[1] )
            if stringInSlice( v4, G_SCRAPPSEDETECTEXC ) == false && (v3 < 1940 || v3 > ( time.Now().Year() + 1 )) && v1 <= G_SCRAPPSEASONMAX && v1 > 0 && v2 <= G_SCRAPPEPISODEMAX && v2 > 0 {
                pos := regExpGetDataFirstPos( title, cre )
                if pos > -1 {
                    showInfo( "EPISODE-DETECT-POS: " + title + " => " + intToStr( pos ) )
                    result = pos
                    break
                }
            }
        }
    }
    
    return result
}

//SEASONS x CHAPTERS LIST

func fscrap_getSeasons() []string {
    var result []string
    
    for i := 1; i <= G_SCRAPPSEASONMAX; i++ {
        result = append(result, intToStr(i))
    }
    
    return result
}

func fscrap_getChapters() []string {
    var result []string
    
    for i := 1; i <= G_SCRAPPEPISODEMAX; i++ {
        result = append(result, intToStr(i))
    }
    
    return result
}

//STRINGS COMPARE

func fscrap_compareStrings( s1 string, s2 string) int {
    //words mode
    result := 0
    wordpc := 70
    replaces := []string { "_", ".", "-", ",", ":", ";", "?", "!" }
    for _, r := range replaces {
        s1 = strings.Replace(s1, r, " ", -1)
        s2 = strings.Replace(s2, r, " ", -1)
    }
    words1 := strings.Fields(s1)
    words2 := strings.Fields(s2)
    totalw := 0
    totalw1 := 0
    //showInfo( "FSCRAPP-COMPARESTRINGS-WORDS1: " + strings.Join( words1, ", " ) )
    //showInfo( "FSCRAPP-COMPARESTRINGS-WORDS2: " + strings.Join( words2, ", " ) )
    for _, word := range words1 {
        //showInfo( "FSCRAPP-COMPARESTRINGS-WORD: " + word )
        if len(word) > 2 {
            if stringInSlice( word, words2 ) {
                //showInfo( "FSCRAPP-COMPARESTRINGS-WORD-OK-100: " + word )
                totalw++
            } else {
                for _, word2 := range words2 {
                    if fscrap_compareWord( word, word2 ) > wordpc {
                        totalw++
                        break
                    }
                }
            }
            totalw1++
        }
	}
    if len(words1) > len(words2) {
        totalw -= ( len(words1) - len(words2) )
    } else if len(words1) < len(words2) {
        totalw -= ( len(words2) - len(words1) )
    }
    if totalw1 <= 0 {
        totalw1 = 1
    }
    //showInfo( "FSCRAPP-COMPARESTRINGS-TOTAL: " + intToStr( totalw ) + "/" + intToStr( totalw1 ) )
    
    if totalw > 0 {
        result = ( totalw * 100 ) / totalw1
    }
    
    return result
}

func fscrap_compareWord( s1 string, s2 string ) int {
    result := 0
    total := 0
    totalok := 0
    ns1 := utf8.RuneCountInString(s1)
    ns2 := utf8.RuneCountInString(s2)
    if ns1 == ns2 {
        for index, rv := range s1 {
            for index2, rv2 := range s2 {
                if index == index2 {
                    if rv == rv2 {
                        totalok++
                        break
                    }
                }
            }
            total++
        }
    }
    if totalok > 0 {
        result = ( ( totalok * 100 ) / total )
    }
    return result
}

//URL TO FILE

func urlToFile( url string, filename string ) bool {
    result := false
    file, err := os.Create(filename)
    if err == nil {
        client := http.Client{
            CheckRedirect: func(r *http.Request, via []*http.Request) error {
                r.URL.Opaque = r.URL.Path
                return nil
            },
        }
        resp, err := client.Get(url)
        if err == nil {
            defer resp.Body.Close()
            size, err := io.Copy(file, resp.Body)
            if err == nil && size > 0 {
                defer file.Close()
                result = true
            }
        }
    }
    return result
}

//MEDIAINFO FILE COPY

func copyImgsMediaInfo( idmia string, idmib string ){
    //G_MEDIA_IMAGES G_IMAGES_FOLDER
    for _, ft := range G_MEDIA_IMAGES {
        fa := pathJoin( G_IMAGES_FOLDER, idmia + "." + ft )
        fb := pathJoin( G_IMAGES_FOLDER, idmib + "." + ft )
        if fileExist( fa ) {
            os.Link( fa, fb )
        }
    }
}

//MEDIAINFO FILE DELETE

func deleteImgsMediaInfo( idmia string ){
    //G_MEDIA_IMAGES G_IMAGES_FOLDER
    for _, ft := range G_MEDIA_IMAGES {
        fa := pathJoin( G_IMAGES_FOLDER, idmia + "." + ft )
        if fileExist( fa ) {
            os.Remove(fa)
        }
    }
}

//FILE TAGS

func fscrap_getFileTags( file string ) []string {
    var result []string
    
    for tag, retag := range G_DOWNLOADS_FILETAGS {
        pos := regExpGetDataFirstPos( file, retag )
        if pos > -1 {
            result = append(result, strings.ToUpper(tag))
        }
    }
    
    return result
}

//GENERIC CALLER

func identMedia( idmedia string, scrapper string, ftitle string, fseason string, fepisode string, fimdb string ) string {
    result := ""
    nowpath, _ := filepath.Abs(G_DOWNLOADS_FOLDER)
    mediadata := sqlite_getMediaID( idmedia )
    showInfo( "FSCRAPP-IDENTMEDIA-DATA: " + idmedia )
    if len(mediadata) > 0 {
        media := mediadata[0]
        if media[ "file" ] != "" && fileExist( media[ "file" ] ) {
            var idmediainfo int
            var season, episode, cleantitle, filesub string
            if len(ftitle) > 0 {
                cleantitle = ftitle
                season = fseason
                episode = fepisode
            } else {
                _ = filesub
                _ = nowpath
                //filesub = strings.Replace(media[ "file" ], nowpath, "", -1)
                //cleantitle = fscrap_cleanTitle(filepath.Base(media[ "file" ]))
                //TODO better option foldername
                //season, episode = fscrap_getSeasonChapter( filesub )
            }
            switch scrapper{
                case "pymi":
                    idmediainfo = fscrap_pymi( media[ "file" ], cleantitle, season, episode, fimdb )
                case "mydb":
                    idmediainfo = fscrap_owndb( media[ "file" ], cleantitle, season, episode, fimdb )
                case "omdb":
                    idmediainfo = fscrap_omdb( media[ "file" ], cleantitle, season, episode, fimdb )
                case "thetvdb":
                    idmediainfo = fscrap_tvdb( media[ "file" ], cleantitle, season, episode, fimdb )
                case "filebot":
                    fallthrough
                default:
                    idmediainfo = fscrap_filebot( media[ "file" ], cleantitle, season, episode, fimdb )
            }
            if idmediainfo > 0 {
                //midata := sqlite_getMediaInfoID( intToStr( idmediainfo ) )
                sqlite_media_update_idmediainfo( media[ "idmedia" ], intToStr( idmediainfo ) )
                result = intToStr(idmediainfo)
            } else {
                sqlite_media_update_idmediainfo( media[ "idmedia" ], "-1" )
            }
        } else {
            showInfo( "FSCRAPP-IDENTMEDIA-DATA: File not exist" + media[ "file" ] )
        }
    }
    
    return result
}

//GENERIC CALLER FORCED TITLE

func identMediaTitle( idmedia string, scrapper string, ftitle string, fseason string, fepisode string, fimdb string ) string {
    result := ""
    
    showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE: " + ftitle )
    data := sqlite_getMediaInfoTitle( ftitle )
    if len(data) > 0 {
        showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE-RESULTS: " + intToStr( len(data) ) )
        for _, mediainfo := range data {
            if mediainfo["season"] == fseason && mediainfo["episode"] == fepisode {
                //Exist
                showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE: " + mediainfo["idmediainfo"] )
                result = mediainfo["idmediainfo"]
                break
            } else if len(fseason) == 0 {
                //Exist (movie)
                showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE: " + mediainfo["idmediainfo"] )
                result = mediainfo["idmediainfo"]
                break
            }
        }
        if result == "" && fseason != "" {
            //series without seasonxchapter exist, create a send
            for _, mediainfo := range data {
                if mediainfo["season"] != "" {
                    a := sqlite_mediainfo_copy( mediainfo["idmediainfo"], fseason, fepisode )
                    if a > 0 {
                        result = intToStr( a )
                        showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE: " + result )
                        break
                    }
                }
            }
        }
    }
    
    showInfo( "FSCRAPP-IDENTMEDIA-FORCE-TITLE-RESULT: " + result )
    return result
}

//OWNDB

func fscrap_owndb( file string, title string, season string, episode string, imdb string ) int {
    //Check movie or serie
    //check imdb
    //check cleaned name/title on db with same type (movie/serie)
    //check same filename on files with type (movie/serie)
    //return 0|idmediainfo
    result := 0
    nameminsim := 90
    fileminsim := 90
    btitle := title
    
    var nseason, nepisode string
    var ntitles []string
    
    //Base title or filename
    if len(title) > 0{
        ntitles = append( ntitles, title )
    }else{
        btitle = fscrap_cleanTitle(filepath.Base(file))
        ntitles = append( ntitles, btitle )
    }
    //folder title
    ttitle := fscrap_cleanTitle(filepath.Base(filepath.Dir(file)))
    if ttitle == fscrap_cleanTitle(filepath.Base(G_DOWNLOADS_FOLDER)) {
        ntitles = append( ntitles, ttitle )
    }
    //season forced or from file
    if season != "" {
        nseason = season
        nepisode = episode
    } else {
        nseason, nepisode = fscrap_getSeasonChapter(filepath.Base(file) )
        //from folder
        if season != "" {
            nseason, nepisode = fscrap_getSeasonChapter( ttitle )
        }
    }
    //On tv mode cut name on SxE
    if nseason != "" {
        ttitle = getSubString( btitle, 0, fscrap_getSeasonChapterPos( btitle ) )
        ntitles = append( ntitles, ttitle )
    }
    
    //IMDB
    if result == 0 && len(imdb) > 0 {
        //imdb mode
        showInfo( "OWNDB-IMDBMODE: " + imdb )
        midata := sqlite_getMediaInfoIMDB( imdb )
        if len(midata) > 0 {
            idmi := sqlite_checkMediaInfoExist( midata[0]["title"],  midata[0]["year"],  nseason,  nepisode )
            if idmi > 0 {
                //Exist
                showInfo( "OWNDB-IMDBMODE: IMDB EXIST: " + imdb )
                result = idmi
            } else {
                //Not exist, create
                showInfo( "OWNDB-IMDBMODE: IMDB NOT EXIST, COPY: " + imdb )
                result = sqlite_mediainfo_copy( intToStr( idmi ), nseason, nepisode )
            }
        } else {
            showInfo( "OWNDB-IMDBMODE: NO IMDB DATA: " + imdb )
        }
    }
    
    //FILE SIMILARITY
    //TODO filter best word
    allmedia := sqlite_getMediaAll()
    if result == 0 {
        nidmi := make(map[string]string)
        fileb := filepath.Base(file)
        npc := 0
        showInfo( "OWNDB-FILEMODE: " + fileb )
        for _, nowmedia := range allmedia {
            if file != nowmedia["file"] && strToInt( nowmedia["idmediainfo"] ) > 0 {
                pc := fscrap_compareStrings( nowmedia["file"], fileb )
                if pc >= fileminsim && pc > npc && ( ( nowmedia["season"] == "" && nseason == "" ) || ( nowmedia["season"] != "" && nseason != "" ) ) {
                    npc = pc
                    nidmi = nowmedia
                    if pc >= 100 {
                        showInfo( "OWNDB-FILEMODE-100%: " + nowmedia["idmediainfo"] )
                        break
                    }
                }
            }
        }
        if len( nidmi ) > 0 {
            nidmi2 := sqlite_getMediaInfoID( nidmi["idmediainfo"] )
            if len(nidmi2) > 0 {
                if nidmi2[0]["season"] == nseason && nidmi2[0]["episode"] == nepisode {
                    //Exist
                    showInfo( "OWNDB-FILEMODE-EXIST: " + nidmi2[0]["idmediainfo"] )
                    result = strToInt( nidmi2[0]["idmediainfo"] )
                } else if len(nseason) > 0 {
                    //Not exist, create, only series movies can have duplicates
                    showInfo( "OWNDB-FILEMODE-CREATE: " + nidmi2[0]["idmediainfo"] )
                    result = sqlite_mediainfo_copy( nidmi2[0]["idmediainfo"], nseason, nepisode )
                }
            }
        }
    }
    
    //TITLE SIMILARITY
    //TODO filter best word
    allmedia = sqlite_getMediaInfoAll()
    for _, ntitle := range ntitles {
        if result == 0 {
            nidmi := make(map[string]string)
            npc := 0
            pc := 0
            pc2 := 0
            showInfo( "OWNDB-TITLEMODE-ELEMENT: " + ntitle )
            for _, nowmedia := range allmedia {
                if nowmedia["title"] == ntitle {
                    pc = 100
                } else {
                    pc = fscrap_compareStrings( nowmedia["title"], ntitle )
                    pc2 = fscrap_compareStrings( fscrap_normalizeTitle(nowmedia["title"]), fscrap_normalizeTitle(ntitle) )
                    if pc2 > pc {
                        pc = pc2
                    }
                }
                if pc > nameminsim && pc > npc && ( ( nowmedia["season"] == "" && nseason == "" ) || ( nowmedia["season"] != "" && nseason != "" ) ) {
                    nidmi = nowmedia
                    if pc >= 100 {
                        showInfo( "OWNDB-TITLEMODE-100%: " + nowmedia["idmediainfo"] )
                        break
                    }
                }
            }
            if len( nidmi ) > 0 {
                if nidmi["season"] == nseason && nidmi["episode"] == nepisode {
                    //Exist
                    showInfo( "OWNDB-TITLEMODE-EXIST: " + nidmi["idmediainfo"] )
                    result = strToInt( nidmi["idmediainfo"] )
                } else if len(nseason) > 0 {
                    //Not exist, create, only series movies can have duplicates
                    showInfo( "OWNDB-TITLEMODE-CREATE: " + nidmi["idmediainfo"] + " => " + nidmi["title"] + " " + nidmi["season"] + "x" + nidmi["episode"] + " ()" + nidmi["year"] + ")"  )
                    result = sqlite_mediainfo_copy( nidmi["idmediainfo"], nseason, nepisode )
                }
                break
            }
        }
    }
    
    showInfo( "OWNDB-RESULT: " + intToStr( result ) )
    return result
}

//FILEBOT

func fscrap_filebot( file string, title string, season string, episode string, imdb string ) int {
    //Create temp folder
    //create link to temp folder with file and title
    //autodetect filebot G_FILEBOTCMD
    //wait for changes
    //recolect nfo data and images
    //return 0|idmediainfo
    result := 0
    
    ntitle := title
    if season != "" {
        ntitle += " " + season + "x" + episode
    }
    tmpfolder := newTmpFolder()
    time.Sleep(2 * time.Second)
    tmpfile := pathJoin(tmpfolder, ntitle) + ".avi" //force extension or filebot fail
    //err := os.Symlink(file, tmpfile)
    err := os.Link(file, tmpfile)
    if err != nil {
		fmt.Println(err)
	}
    showInfo( "FILEBOT-FILE: " + file + " => " + tmpfile )
    if fileExist( tmpfolder ) && fileExist( tmpfile ) {
        showInfo( "FILEBOT-NFO-CREATE: " + tmpfolder )
        fscrap_filebot_run( tmpfolder + "/", ntitle, season, episode, imdb )
        //check files nfo
        filelist := getFiles( tmpfolder, "nfo" )
        showInfo( "FILEBOT-NFO-SEARCH: " + intToStr( len(filelist) ) )
        for _, nfofile := range filelist {
            nfodata := fscrap_filebot_readnfo( nfofile )
            //Force season episode if setted
            if len( season ) > 0 {
                nfodata[ "season" ] = season
                nfodata[ "episode" ] = episode
            }
            //fmt.Println( nfodata )
            if len( nfodata[ "title" ] ) > 0 &&  len( nfodata[ "year" ] ) > 0 {
                showInfo( "FILEBOT-SCRAP:" + nfodata[ "title" ] )
                //check exist or create
                idmediainfo := sqlite_checkMediaInfoExist( nfodata[ "title" ], nfodata[ "year" ], nfodata[ "season" ], nfodata[ "episode" ] )
                if idmediainfo == 0 {
                    //Create
                    showInfo( "FILEBOT-CREATE: " + nfodata[ "title" ] )
                    result = sqlite_mediainfo_insert(nfodata)
                    //Try images
                } else {
                    //Exist
                    showInfo( "FILEBOT-EXIST: " +intToStr(idmediainfo) )
                    result = idmediainfo
                }
            } else {
                showInfo( "FILEBOT-NOTITLE: " + nfodata[ "title" ] )
            }
        }
    }
    
    showInfo( "FILEBOT-RESULT: " + intToStr( result ) )
    return result
}

func fscrap_filebot_run( file string, title string, season string, episode string, imdb string ) {
    var db, forcetype, moviefilter, seriesfilter, animefilter string
    
    //Clean xattr
    args1 := []string{
        "-script",
        "fn:xattr",
        "--action",
        "clear",
        file,
    }
    exec.Command(G_FILEBOTCMD, args1...).CombinedOutput()
    
    if season == "" {
        db = "TheMovieDB"
        forcetype = "ut_label=movie"
        moviefilter = "movieFormat={n}_{y}"
        seriesfilter = "seriesFormat=''"
        animefilter = "animeFormat=''"
    } else {
        db = "TheTVDB"
        forcetype = "ut_label=tv"
        moviefilter = "movieFormat=''"
        seriesfilter = "seriesFormat={n}_{sxe}_{t}"
        animefilter = "animeFormat={n}_{sxe}_{t}"
    }
    args := []string{
        "-script",
        "fn:amc",
        //"-Dfile.encoding=UTF-8"
        "--encoding",
        "UTF-8",
        "-non-strict",
        file,
        "--db",
        db,
        "--action",
        "hardlink", //move
        "--lang",
        G_FILEBOTLANG,
        "--conflict",
        "auto",
        "--def",
        forcetype,
        //"--def",
        //"clean=y",
        "--def",
        "artwork=y",
        "--def",
        moviefilter,
        "--def",
        seriesfilter,
        "--def",
        animefilter,
        `musicFormat=""`,
    }
    showInfo( "FILEBOT-CMD:" + G_FILEBOTCMD + " " + strings.Join(args, " ") )
    out, err := exec.Command(G_FILEBOTCMD, args...).CombinedOutput()
    showInfo( "FILEBOT-RUN:" + string(out) )
    if err == nil {
        showInfo( "FILEBOT-RUN: FAIL" )
    }
}

func fscrap_filebot_readnfo( nfofile string ) map[string]string {
    //Extract data from nfo
    //title
    //sorttitle
    //season
    //episode
    //year
    //rating
    //votes
    //mpaa
    //tagline
    //runtime
    //plot
    //height
    //width
    //codec
    //imdbid
    //imdb
    //tmdbid
    //tmdb
    //tvdbid
    //tvdb
    //titleepisode
    //genre
    //actor
    //audio
    //subtitle
    result := make(map[string]string)
    showInfo( "FILEBOT-NFO-FILE:" + nfofile )
    filedata := readAll( nfofile )
    showInfo( "FILEBOT-NFO-FILE-DATA:" + intToStr( len(filedata) ) )
    
    result["title"] = regExpGetDataFirst( filedata, `(?m)<originaltitle>(.*)<\/originaltitle>` )
    if len( result["title"] ) == 0 {
        result["title"] = regExpGetDataFirst( filedata, `(?m)<title>(.*)<\/title>` )
    }
    //showInfo( "FILEBOT-NFO-FILE-DATA-TITLE:" + result["title"] )
    
    result["year"] = regExpGetDataFirst( filedata, `(?m)<year>(.*)<\/year>` )
    
    result["sorttitle"] = regExpGetDataFirst( filedata, `(?m)<sorttitle>.*(\d{4}-\d{2}-\d{2}).*<\/sorttitle>` )
    if len( result["sorttitle"] ) == 0 {
        result["sorttitle"] = result["year"] + "-01-01"
    }
    
    result["season"] = regExpGetDataFirst( filedata, `(?m)<season>(.*)<\/season>` )
    result["episode"] = regExpGetDataFirst( filedata, `(?m)<episode>(.*)<\/episode>` )
    result["rating"] = regExpGetDataFirst( filedata, `(?m)<rating>(.*)<\/rating>` )
    result["votes"] = regExpGetDataFirst( filedata, `(?m)<votes>(.*)<\/votes>` )
    result["mpaa"] = regExpGetDataFirst( filedata, `(?m)<mpaa>(.*)<\/mpaa>` )
    result["tagline"] = regExpGetDataFirst( filedata, `(?m)<tagline>(.*)<\/tagline>` )
    result["runtime"] = regExpGetDataFirst( filedata, `(?m)<runtime>(.*)<\/runtime>` )
    result["plot"] = regExpGetDataFirst( filedata, `(?m)<plot>(.*)<\/plot>` )
    result["height"] = regExpGetDataFirst( filedata, `(?m)<height>(.*)<\/height>` )
    result["width"] = regExpGetDataFirst( filedata, `(?m)<width>(.*)<\/width>` )
    result["codec"] = regExpGetDataFirst( filedata, `(?m)<codec>(.*)<\/codec>` )
    result["imdbid"] = regExpGetDataFirst( filedata, `(?m)<imdb id='(.*)'>` )
    result["imdb"] = regExpGetDataFirst( filedata, `(?m)>(.*)</imdb>` )
    result["tmdbid"] = regExpGetDataFirst( filedata, `(?m)<tmdb id='(.*)'>` )
    result["tmdb"] = regExpGetDataFirst( filedata, `(?m)>(.*)</tmdb>` )
    result["tvdbid"] = regExpGetDataFirst( filedata, `(?m)<tvdb id='(.*)'>` )
    result["tvdb"] = regExpGetDataFirst( filedata, `(?m)>(.*)</tvdb>` )
    result["titleepisode"] = regExpGetDataFirst( filedata, `(?m)<titleepisode>(.*)<\/titleepisode>` )
    //, separated
    result["genre"] = strings.Join( regExpGetData( filedata, `(?m)<genre>(.*)<\/genre>` ), ", " )
    //, separated
    result["actor"] = strings.Join( regExpGetData( filedata, `(?m)<name>(.*)<\/name>` ), ", " )
    result["audio"] = ""
    result["subtitle"] = ""
    
    //fmt.Println( result )
    
    return result
}

//OMDB

func fscrap_omdb( file string, title string, season string, episode string, imdbid string ) int {
    //only over imdb
    //return 0|idmediainfo
    result := 0
    nfodata := make(map[string]string)
    showInfo( "OMDB-SEARCH: " + file + " => " + title + " " + season + "x" + episode + " (" + imdbid + ")"  )
    /*
    type MovieResult struct {
        Title      string
        Year       string
        Rated      string
        Released   string
        Runtime    string
        Genre      string
        Director   string
        Writer     string
        Actors     string
        Plot       string
        Language   string
        Country    string
        Awards     string
        Poster     string
        Metascore  string
        ImdbRating string
        ImdbVotes  string
        ImdbID     string
        Type       string
        Ratings    []Rating
        DVD        string
        BoxOffice  string
        Production string
        Website    string
        Response   string
        Error      string
    }
    */
    
    if len(imdbid) == 0 && len(title) > 0 && len(G_OMDB_APIKEY) > 0 && len(season) == 0 {
        showInfo( "OMDB-SEARCH-TITLE: " + title )
        cl := imdb.New(G_OMDB_APIKEY)
        year := fscrap_getYear( title )
        titlet := strings.Replace( title, year, "", -1 )
        res, err := cl.Search( titlet, year )
        if err == nil && len(res.Search) > 0 {
            for _, d := range res.Search {
                timdbid := wscrap_getURLID( d.ImdbID )
                if len(timdbid) > 0 {
                    showInfo( "OMDB-SEARCH-TITLE-IMDB: " + timdbid )
                    imdbid = timdbid
                    break
                }
            }
        }
    }
    
    if len(imdbid) > 0 && len(G_OMDB_APIKEY) > 0 && len(season) == 0 {
        cl := imdb.New(G_OMDB_APIKEY)
        //res, err := cl.Search("Fight Club", "")
        res, err := cl.MovieByImdbID(imdbid)
        if err != nil {
            showInfo( "OMDB-RESULT-ERROR: " + err.Error() )
        } else {
            nfodata["title"] = res.Title
            nfodata["year"] = regExpGetDataFirst( res.Year, `(\d{4})` )
            if strToInt(nfodata["year"]) < 1900 || strToInt(nfodata["year"]) > ( time.Now().Year() + 1 ) {
                nfodata["year"] = intToStr(time.Now().Year())
            }
            nfodata["sorttitle"] = fscrap_omdb_dateformat(res.Released)
            nfodata["season"] = ""
            nfodata["episode"] = ""
            nfodata["rating"] = res.ImdbRating
            nfodata["votes"] = res.ImdbVotes
            nfodata["mpaa"] = ""
            nfodata["tagline"] = ""
            nfodata["runtime"] = regExpGetDataFirst( res.Runtime, `(\d{1,3})` )
            if strToInt(nfodata["runtime"]) == 0 || strToInt(nfodata["runtime"]) > 320 {
                nfodata["runtime"] = "100"
            }
            nfodata["plot"] = res.Plot
            nfodata["height"] = ""
            nfodata["width"] = ""
            nfodata["codec"] = ""
            nfodata["imdbid"] = imdbid
            nfodata["imdb"] = "" //"https://www.imdb.com/title/" + imdbid
            nfodata["tmdbid"] = ""
            nfodata["tmdb"] = ""
            nfodata["tvdbid"] = ""
            nfodata["tvdb"] = ""
            nfodata["titleepisode"] = ""
            //, separated
            nfodata["genre"] = res.Genre
            //, separated
            nfodata["actor"] = res.Actors
            nfodata["audio"] = ""
            nfodata["subtitle"] = ""
            
            //fmt.Println( res )
            if len( nfodata[ "title" ] ) > 0 &&  len( nfodata[ "year" ] ) > 0 {
                showInfo( "OMDB-SCRAP:" + nfodata[ "title" ] )
                //check exist or create
                idmediainfo := sqlite_checkMediaInfoExist( nfodata[ "title" ], nfodata[ "year" ], nfodata[ "season" ], nfodata[ "episode" ] )
                if idmediainfo == 0 {
                    //Create
                    showInfo( "OMDB-CREATE: " + nfodata[ "title" ] )
                    result = sqlite_mediainfo_insert(nfodata)
                    //Try images
                    if result > 0 && isValidUrl(res.Poster) {
                        tofile := pathJoin( G_IMAGES_FOLDER, intToStr( result ) + ".poster" )
                        showInfo( "OMDB-SCRAP-GETIMAGE:" +tofile )
                        urlToFile( res.Poster, tofile )
                    }
                } else {
                    //Exist
                    showInfo( "OMDB-EXIST: " +intToStr(idmediainfo) )
                    result = idmediainfo
                }
            } else {
                showInfo( "OMDB-NOTITLE: " + nfodata[ "title" ] )
            }

        }
    }
    
    showInfo( "OMDB-RESULT: " + intToStr( result ) )
    return result
}

func fscrap_omdb_dateformat( date string ) string {
    result := ""
    
    //27 Jan 2019
    t := regExpGetDataFirst( date, `(\d{2} \w{3} \d{4})` )
    if len(t) > 0 {
        layout := "02 Jan 2006"
        t, err := time.Parse(layout, date)
        if err == nil {
            result = t.Format("2006-01-02")
        }
    }
    
    //2019-01-27
    t = regExpGetDataFirst( date, `(\d{4}-\d{2}-\d{2})` )
    if len(result) == 0 && len(t) > 0 {
        layout := "2006-01-02"
        t, err := time.Parse(layout, date)
        if err == nil {
            result = t.Format("2006-01-02")
        }
    }
    
    //27/01/2019
    t = regExpGetDataFirst( date, `(\d{2}\/\d{2}\/\d{4})` )
    if len(result) == 0 && len(t) > 0 {
        layout := "2006-01-02"
        t, err := time.Parse(layout, date)
        if err == nil {
            result = t.Format("2006-01-02")
        }
    }
    
    //2019-01
    t = regExpGetDataFirst( date, `(\d{4}-\d{2}` )
    if len(result) == 0 && len(t) > 0 {
        layout := "2006-01"
        t, err := time.Parse(layout, date)
        if err == nil {
            result = t.Format("2006-01") + "-01"
        }
    }
    
    //Y
    t = regExpGetDataFirst( date, `(\d{4})` )
    if len(result) == 0 && len(t) > 0 {
        layout := "2006-01-02"
        t, err := time.Parse(layout, date)
        if err == nil {
            result = t.Format("2006-01-02")
        }
    }
    
    return result
}

//THETVDB

func fscrap_tvdb( file string, title string, season string, episode string, imdbid string ) int {
    //only over imdb
    //return 0|idmediainfo
    result := 0
    nfodata := make(map[string]string)
    showInfo( "THETVDB-SEARCH: " + file + " => " + title + " " + season + "x" + episode + " (" + imdbid + ")"  )
    /*
    type Series struct {
        Added           string   `json:"added"`
        AddedBy         int      `json:"addedBy"`
        AirsDayOfWeek   string   `json:"airsDayOfWeek"`
        AirsTime        string   `json:"airsTime"`
        Aliases         []string `json:"aliases"`
        Banner          string   `json:"banner"`
        FirstAired      string   `json:"firstAired"`
        Genre           []string `json:"genre"`
        ID              int      `json:"id"`
        ImdbID          string   `json:"imdbId"`
        LastUpdated     int      `json:"lastUpdated"`
        Network         string   `json:"network"`
        NetworkID       string   `json:"networkId"`
        Overview        string   `json:"overview"`
        Rating          string   `json:"rating"`
        Runtime         string   `json:"runtime"`
        SeriesID        string   `json:"seriesId"`
        SeriesName      string   `json:"seriesName"`
        SiteRating      float32  `json:"siteRating"`
        SiteRatingCount int      `json:"siteRatingCount"`
        Status          string   `json:"status"`
        Zap2itID        string   `json:"zap2itId"`
        // Slice of the series actors, filled with GetSeriesActors method.
        Actors []Actor
        // Slice of the series episodes, filled with GetSeriesEpisodes method.
        Episodes []Episode
        // Slice of the series summary, filled with GetSeriesSummary method.
        Summary Summary
        // Slice of the series images.
        Images []Image
    }
    */
    var series tvdb.Series
    c := tvdb.Client{
        Apikey: G_THETVDB_APIKEY, 
        //No need except for /user paths
        Userkey: "", 
        Username: "", 
        Language: G_THETVDB_LANG,
    }
    if len(imdbid) == 0 && len(title) > 0 && len(G_THETVDB_APIKEY) > 0 {
        showInfo( "THETVDB-SEARCH-TITLE: " + title )
        err := c.Login()
        if err != nil {
            showInfo( "THETVDB-SEARCH-TITLE-OPEN-ERROR: " + err.Error() )
        } else {
            year := fscrap_getYear( title )
            titlet := strings.Replace( title, year, "", -1 )
            series, err := c.BestSearch(titlet)
            if err != nil {
                showInfo( "THETVDB-SEARCH-TITLE-SEARCH-ERROR: " + err.Error() )
            } else if series.Empty() == false {
                showInfo( "THETVDB-SEARCH-TITLE-FINDED: " + series.SeriesName + "(" + series.FirstAired + ")" )
            }
        }
    } else if len(imdbid) > 0 && len(G_THETVDB_APIKEY) > 0 {
        showInfo( "THETVDB-SEARCH-IMDBID: " + title )
        err := c.Login()
        if err != nil {
            showInfo( "THETVDB-SEARCH-IMDBID-OPEN-ERROR: " + err.Error() )
        } else {
            year := fscrap_getYear( title )
            titlet := strings.Replace( title, year, "", -1 )
            seriesl, err := c.SearchByImdbID(titlet)
            if err != nil {
                showInfo( "THETVDB-SEARCH-IMDBID-SEARCH-ERROR: " + err.Error() )
            } else if len(seriesl) > 0 {
                series := seriesl[0]
                showInfo( "THETVDB-SEARCH-IMDBID-FINDED: " + series.SeriesName + "(" + series.FirstAired + ")" )
            }
        }
    }
    
    if series.Empty() == false {
        //Get Extra data
        c.GetSeriesPosterImages(&series)
        c.GetSeriesFanartImages(&series)
        c.GetSeriesActors(&series)
        
        nfodata["title"] = series.SeriesName
        nfodata["year"] = regExpGetDataFirst( series.AirsTime, `(\d{4})` )
        if strToInt(nfodata["year"]) < 1900 || strToInt(nfodata["year"]) > ( time.Now().Year() + 1 ) {
            nfodata["year"] = intToStr(time.Now().Year())
        }
        nfodata["sorttitle"] = fscrap_omdb_dateformat(series.AirsTime)
        nfodata["season"] = season
        nfodata["episode"] = episode
        nfodata["rating"] = fmt.Sprintf("%2f", series.SiteRating)
        nfodata["votes"] = intToStr(series.SiteRatingCount)
        nfodata["mpaa"] = series.Rating
        nfodata["tagline"] = ""
        nfodata["runtime"] = regExpGetDataFirst( series.Runtime, `(\d{1,3})` )
        if strToInt(nfodata["runtime"]) == 0 || strToInt(nfodata["runtime"]) > 320 {
            nfodata["runtime"] = "60"
        }
        nfodata["plot"] = series.Overview
        nfodata["height"] = ""
        nfodata["width"] = ""
        nfodata["codec"] = ""
        nfodata["imdbid"] = imdbid
        nfodata["imdb"] = "" //"https://www.imdb.com/title/" + imdbid
        nfodata["tmdbid"] = ""
        nfodata["tmdb"] = ""
        nfodata["tvdbid"] = intToStr( series.ID )
        nfodata["tvdb"] = ""
        //, separated
        nfodata["genre"] = strings.Join(series.Genre, ", " )
        //, separated
        sactors := ""
        for _, sactor := range series.Actors {
            if len(sactors) > 0 {
                sactors += ", " + sactor.Name
            } else {
                sactors += sactor.Name
            }
            //Download Images
            fimage := pathJoin( G_IMAGES_FOLDER, sactor.Name )
            iurl := tvdb.ImageURL(sactor.Image)
            urlToFile(iurl,fimage)
        }
        nfodata["actor"] = sactors
        nfodata["audio"] = ""
        nfodata["subtitle"] = ""
        nfodata["titleepisode"] = ""

        fmt.Println( nfodata )
        if len( nfodata[ "title" ] ) > 0 &&  len( nfodata[ "year" ] ) > 0 {
            showInfo( "THETVDB-SCRAP:" + nfodata[ "title" ] )
            //check exist or create
            idmediainfo := sqlite_checkMediaInfoExist( nfodata[ "title" ], nfodata[ "year" ], nfodata[ "season" ], nfodata[ "episode" ] )
            if idmediainfo == 0 {
                //Create
                showInfo( "THETVDB-CREATE: " + nfodata[ "title" ] )
                result = sqlite_mediainfo_insert(nfodata)
                //Try images
                //poster, landscape, banner
                if result > 0 {
                    for _, iserie := range series.Images {
                        typei := ""
                        if iserie.KeyType == "poster" {
                            typei = "poster"
                        } else if iserie.KeyType == "landscape" {
                            typei = "landscape"
                        } else if iserie.KeyType == "banner" {
                            typei = "banner"
                        }
                        if typei != "" {
                            iurl := tvdb.ImageURL(iserie.FileName)
                            fimage := pathJoin( G_IMAGES_FOLDER, intToStr(result) + "." + typei )
                            if isValidUrl(iurl) {
                                showInfo( "THETVDB-SCRAP-GETIMAGE:" + fimage )
                                urlToFile( iurl, fimage )
                            }
                        }
                    }
                }
            } else {
                //Exist
                showInfo( "THETVDB-EXIST: " +intToStr(idmediainfo) )
                result = idmediainfo
                //Try images
                //poster, landscape, banner
                if result > 0 {
                    for _, iserie := range series.Images {
                        typei := ""
                        if iserie.KeyType == "poster" {
                            typei = "poster"
                        } else if iserie.KeyType == "landscape" {
                            typei = "landscape"
                        } else if iserie.KeyType == "banner" {
                            typei = "banner"
                        }
                        if typei != "" {
                            iurl := tvdb.ImageURL(iserie.FileName)
                            fimage := pathJoin( G_IMAGES_FOLDER, intToStr(result) + "." + typei )
                            if isValidUrl(iurl) {
                                showInfo( "THETVDB-SCRAP-GETIMAGE:" + fimage )
                                urlToFile( iurl, fimage )
                            }
                        }
                    }
                }
            }
        } else {
            showInfo( "THETVDB-NOTITLE: " + nfodata[ "title" ] )
        }
    }
    
    showInfo( "THETVDB-RESULT: " + intToStr( result ) )
    return result
}

//PYMEDIAIDENT

type pymiTemplate struct {
	Actors       string      `json:"actors"`
	Chapter      string      `json:"chapter"`
	Chaptertitle string      `json:"chaptertitle"`
	Director     string      `json:"director"`
	Genres       string      `json:"genres"`
	Kind         string      `json:"kind"`
	Mpaa         interface{} `json:"mpaa"`
	Plot         string      `json:"plot"`
	Plotshort    string      `json:"plotshort"`
	Rating       float64     `json:"rating"`
	Releasedate  string      `json:"releasedate"`
	Season       string      `json:"season"`
	Title        string      `json:"title"`
	Urlposter    string      `json:"urlposter"`
	Votes        string      `json:"votes"`
	Year         string      `json:"year"`
}

func fscrap_pymi( file string, title string, season string, episode string, imdb string ) int {
    //autodetect pymediainfo G_PYMICMD
    //wait for json
    //recolect json data and images
    //return 0|idmediainfo
    result := 0
    
    ntitle := title
    if season != "" {
        ntitle += " " + season + "x" + episode
    }
    showInfo( "PYMI-FILE: " + file + " => " + ntitle )
    nfofile := fscrap_pymi_run( ntitle, season, episode, imdb )
    if len(nfofile) > 0 {
        nfodata := fscrap_pymi_readdata( nfofile )
        //Force season episode if setted
        if len( season ) > 0 {
            nfodata[ "season" ] = season
            nfodata[ "episode" ] = episode
        }
        //fmt.Println( nfodata )
        if len( nfodata[ "title" ] ) > 0 &&  len( nfodata[ "year" ] ) > 0 {
            showInfo( "PYMI-SCRAP:" + nfodata[ "title" ] )
            //check exist or create
            idmediainfo := sqlite_checkMediaInfoExist( nfodata[ "title" ], nfodata[ "year" ], nfodata[ "season" ], nfodata[ "episode" ] )
            if idmediainfo == 0 {
                //Create
                showInfo( "PYMI-CREATE: " + nfodata[ "title" ] )
                result = sqlite_mediainfo_insert(nfodata)
            } else {
                //Exist
                showInfo( "PYMI-EXIST: " +intToStr(idmediainfo) )
                result = idmediainfo
            }
            //Try images: poster
            if isValidUrl( nfodata["posterimage"] ) {
                iurl := nfodata["posterimage"]
                fimage := pathJoin( G_IMAGES_FOLDER, intToStr(result) + ".poster" )
                showInfo( "PYMI-SCRAP-GETIMAGE:" + fimage )
                urlToFile( iurl, fimage )
            }
        } else {
            showInfo( "PYMI-NOTITLE: " + nfodata[ "title" ] )
        }
    }
    
    showInfo( "PYMI-RESULT: " + intToStr( result ) )
    return result
}

func fscrap_pymi_run( title string, season string, episode string, imdb string ) string {
    args := []string{
        "-s",
        "imdb",
        "-l",
        G_PYMI_LANG,
        "-fs",
        title,
        "-sid",
        imdb,
        "--json",
    }
    showInfo( "PYMI-CMD:" + G_PYMI_CMD + " " + strings.Join(args, " ") )
    out, err := exec.Command(G_PYMI_CMD, args...).CombinedOutput()
    showInfo( "PYMI-RUN:" + string(out) )
    if err == nil {
        showInfo( "PYMI-RUN: FAIL" )
    }
    
    return string(out)
}

func fscrap_pymi_readdata( nfofile string ) map[string]string {
    //Extract data from nfo
    //title
    //sorttitle
    //season
    //episode
    //year
    //rating
    //votes
    //mpaa
    //tagline
    //runtime
    //plot
    //height
    //width
    //codec
    //imdbid
    //imdb
    //tmdbid
    //tmdb
    //tvdbid
    //tvdb
    //titleepisode
    //genre
    //actor
    //audio
    //subtitle
    
    /*
    {
        "actors": "Mary Alice,Tanveer K. Atwal,Helmut Bakaitis,Kate Beahan,Francine Bell,Monica Bellucci,Rachel Blackman,Henry Blasingame,Ian Bliss,David Bowers,Zeke Castelli,Collin Chou,Essie Davis,Laurence Fishburne,Nona Gaye,Dion Horstmans,Lachy Hulme,Christopher Kirby,Peter Lamb,Nathaniel Lees",
        "chapter": "",
        "chaptertitle": "",
        "director": "Lana Wachowski",
        "genres": "Action,Sci-Fi",
        "kind": "movie",
        "mpaa": null,
        "plot": "As Neo somehow becomes trapped in a train station between The Matrix and the real world, Morpheus, Trinity, and Seraph set out to rescue him by means of the Merovingian. As this is going on, the Machines are approaching Zion with the intent of laying waste to every human there, but they do not realize that something is taking over The Matrix, with the intent of destroying everything: Smith. After Neo is freed, he and Trinity take the Logos and make a beeline for the Machine City. The Machines have reached Zion and the battle of a lifetime rages on. However, there is one way to save Zion and put an end to the war, and that is Neo. He must venture into the Matrix and put an end to Smith's evil plans once and for all, and if he succeeds, Zion will live to see another day. It's like the Oracle says, \"Everything that has a beginning has an end.\"::Roguemaster83",
        "plotshort": "The human city of Zion defends itself against the massive invasion of the machines as Neo fights to end the war at another front while also opposing the rogue Agent Smith.::Kenneth Chisholm",
        "rating": 6.7,
        "releasedate": "2003-11-05",
        "season": "",
        "title": "The Matrix Revolutions",
        "urlposter": "https://m.media-amazon.com/images/M/MV5BNzNlZTZjMDctZjYwNi00NzljLWIwN2QtZWZmYmJiYzQ0MTk2XkEyXkFqcGdeQXVyNTAyODkwOQ@@._V1_SY150_CR0,0,101,150_.jpg",
        "votes": "420001",
        "year": "2003"
    }

    */
    
    result := make(map[string]string)
    
    var res pymiTemplate
    err := json.Unmarshal([]byte(nfofile), &res)
    
    if err == nil {
        result["title"] = res.Title
        showInfo( "PYMI-DATA-TITLE:" + result["title"] )
        result["year"] = res.Year

        result["sorttitle"] = res.Releasedate
        if len( result["sorttitle"] ) == 0 {
            result["sorttitle"] = result["year"] + "-01-01"
        }
        result["season"] = res.Season
        result["episode"] = res.Chapter
        result["rating"] = strconv.FormatFloat(res.Rating, 'f', 1, 64)
        result["votes"] = res.Votes
        if res.Mpaa == nil {
            result["mpaa"] = ""
        } else {
            result["mpaa"] = res.Mpaa.(string)
        }
        result["tagline"] = res.Plotshort
        result["runtime"] = ""
        result["plot"] = res.Plot
        result["height"] = ""
        result["width"] = ""
        result["codec"] = ""
        result["imdbid"] = ""
        result["imdb"] = ""
        result["tmdbid"] = ""
        result["tmdb"] = ""
        result["tvdbid"] = ""
        result["tvdb"] = ""
        result["titleepisode"] = res.Chaptertitle
        //, separated
        result["genre"] = res.Genres
        //, separated
        result["actor"] = res.Actors
        result["audio"] = ""
        result["subtitle"] = ""
        result["posterimage"] = res.Urlposter
    } else {
        showInfo( "PYMI-JSON-ERROR:" + err.Error() )
    }
    
    fmt.Println( result )
    
    return result
}
