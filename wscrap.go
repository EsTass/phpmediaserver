package main


import (
	//"fmt"
    "strings"
    "encoding/json"
    //"os"
	"os/exec"
    //"net/url"
)

//GET URL ID (imdb,thetvdb, omdb, filmaffinity)

func wscrap_getURLID( url string ) string {
    result := ""
    
    //IMDB
    result = regExpGetDataFirst( url, `(tt\d{6,10})` )
    
    //themoviedb
    if result == "" {
        result = regExpGetDataFirst( url, `(\/movie\/[0-9]{1,10})` )
    }
    
    //thetvdb
    if result == "" {
        result = regExpGetDataFirst( url, `(id=[0-9]{1,10})` )
    }
    
    //FILMAFFINITY
    if result == "" {
        result = regExpGetDataFirst( url, `(\/film([0-9]{5,10})\.html)` )
    }
    
    return result
}

//CLEAN TITLES from websearch

func wscrap_cleanTitle( title string ) string {
    result := title
    
    result = strings.Replace( result, "- IMDb", "", -1 )
    result = strings.Replace( result, "- FilmAffinity", "", -1 )
    
    return result
}

//WEBSEARCH

func webSearch( s string, filterurl string, titleurl string ) map[string]string {
    result := make(map[string]string)
    
    d := webSearchDDG(s, filterurl, titleurl)
    if len(d) > 0 {
        mapsJoinSS(result, d)
        //result = d
    }
    if len(d) < 5 {
        d := webSearchGoogler(s, filterurl, titleurl)
        if len(d) > 0 {
            mapsJoinSS(result, d)
        }
    }
    
    return result
}

//DUCKDUCKGO

type wsDDGRTemplate []struct {
	Abstract string `json:"abstract"`
	Title    string `json:"title"`
	URL      string `json:"url"`
}

func webSearchDDG( s string, filterurl string, titleurl string ) map[string]string {
    //title => url
    result := make(map[string]string)
    //escapedQuery := url.QueryEscape(s)
    
    //over G_WS_DDG
    command := G_WS_DDG
    args := []string{
        "--json", 
        s,
    }
    
    out, _ := exec.Command( command, args... ).Output()
    //out, err := exec.Command( command, args... ).Output()
    //if err != nil {
        //log.Fatal(err)
    //}
    showInfo( "DDG-RESPONSE-SIZE: " + intToStr(len(out)) )
    //fmt.Printf("The date is %s\n", out)
    responseString := string(out)
    data := wsDDGRTemplate{}
    _ = json.Unmarshal([]byte(responseString), &data)
    for _, d := range data {
        showInfo( "DDG-RESPONSE-DATALINE: " + d.Title + " => " + d.URL )
        if ( len(filterurl) == 0 || strInStr( d.URL, filterurl ) ) && ( len(titleurl) == 0 || strInStr( d.Title, titleurl ) ) {
            result[wscrap_cleanTitle(d.Title)] = d.URL
        }
    }
    
    return result
}

//GOOGLER

func webSearchGoogler( s string, filterurl string, titleurl string ) map[string]string {
    //title => url
    result := make(map[string]string)
    //escapedQuery := url.QueryEscape(s)
    
    //over G_WS_GOOGLER
    command := G_WS_GOOGLER
    args := []string{
        "--json", 
        s,
    }
    
    out, _ := exec.Command( command, args... ).Output()
    //out, err := exec.Command( command, args... ).Output()
    //if err != nil {
        //log.Fatal(err)
    //}
    showInfo( "GOOGLER-RESPONSE-SIZE: " + intToStr(len(out)) )
    //fmt.Printf("The date is %s\n", out)
    responseString := string(out)
    data := wsDDGRTemplate{}
    _ = json.Unmarshal([]byte(responseString), &data)
    for _, d := range data {
        showInfo( "GOOGLER-RESPONSE-DATALINE: " + d.Title + " => " + d.URL )
        if ( len(filterurl) == 0 || strInStr( d.URL, filterurl ) ) && ( len(titleurl) == 0 || strInStr( d.Title, titleurl ) ) {
            result[d.Title] = d.URL
        }
    }
    
    return result
}

//LIVETV DATA EXTRACT

func liveTvDataAdd( data string ) (int, int, int, int) {
    added := 0
    exist := 0
    nerror := 0
    total := 0
    
    //Line to line detect titles, poster and url to add
    //add to medialive and download poster
    datalines := strings.Split( data, "\n" )
    ltitle := ""
    lgtitle := ""
    llogo := ""
    for _, line := range datalines {
        line =  strings.Trim(line, " \r\n")
        if len(line) == 0 {
            //ignore
            ltitle = ""
            lgtitle = ""
            llogo = ""
        } else if isValidUrl(line) {
            //url, check and add
            if sqlite_checkMediaLiveURLURL(line) {
                showInfo( "LIVETV-DATALINE-EXIST: " + ltitle + ", " + lgtitle + ", " + llogo + ", " + line )
                exist++
            } else {
                showInfo( "LIVETV-DATALINE-ADD: " + ltitle + ", " + lgtitle + ", " + llogo + ", " + line )
                if len(lgtitle) > 0 {
                    ltitle += " (" + lgtitle + ")"
                }
                codec := ffprobeVideoCodec( line )
                if codec != "NOCODEC" {
                    idnew := sqlite_medialive_insert(ltitle, line, llogo )
                    showInfo( "LIVETV-ADDED: " + intToStr(idnew) )
                    if idnew > 0 && len(llogo) > 0 {
                        tofile := pathJoin(G_IMAGES_FOLDER, intToStr(idnew) + ".livetv")
                        urlToFile(llogo, tofile)
                        if checkMimeImage(tofile) == false {
                            fileRemove(tofile)
                        }
                        added++
                    }
                } else {
                    showInfo( "LIVETV-URL-ERROR: Codec Error: " + codec )
                    nerror++
                }
            }
            total++
        } else if strStartWith( line, "#EXTINF" ) {
            //Extract data
            ltitle =  strings.Trim(fscrap_cleanTitle(regExpGetDataFirst(line, `.*,(.*)$`)), " ")
            llogo = strings.Trim(regExpGetDataFirst(line, `.*tvg-logo=["'](.*?)["'].*$`), " ")
            if isValidUrl(llogo) == false {
                llogo = ""
            }
            lgtitle =  strings.Trim(fscrap_cleanTitle(regExpGetDataFirst(line, `.*group-title=["'](.*?)["'].*$`)), " ")
        } else {
            //ignore
            ltitle = ""
            lgtitle = ""
            llogo = ""
        }
    }
    
    return added, exist, nerror, total
}