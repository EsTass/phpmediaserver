package main


import (
    "fmt"
    "net/http"
    "html/template"
    "bytes"
    "strings"
    //"strconv"
    "time"
    "path/filepath"
    //"io"
)

//BASIC AUTENTIFICATION

func checkUserIP( r *http.Request ) bool {
    result := false
    userip := getUserIP( r )
    
    if userip == G_SERVER_BIND_IP {
        result = true
        showInfo("IP-CONTROL: Server IP - Valid: " + userip )
    }else if strInSliceOrStartWith( userip, G_GEOIPSAFE ) {
        result = true
        showInfo("IP-CONTROL: In Safe List - Valid: " + userip )
    }else if checkWhitelist( userip ) {
        showInfo("IP-CONTROL: In Whitelist List - Valid: " + userip )
        result = true
    } else if checkBans( userip ) {
        showInfo("IP-CONTROL: In Banned List - BANNED: " + userip )
        result = false
    }else if checkIPCountry( userip ) {
        showInfo("IP-CONTROL: In Country List - Valid: " + userip )
        result = true
    }
    
    return result
}

func checkBaseActionIP( w http.ResponseWriter, r *http.Request, actionident string ) bool {
    result := false
    userip := getUserIP( r )
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    //allways used by actions
    if G_SERVER_HTTPS {
        w.Header().Add("Strict-Transport-Security", "max-age=63072000; includeSubDomains")
    }

    if checkUserIP( r ) == false {
        //invalid IP
        showInfo( actionident + "-INVALID-IP " + userip )
        sqlite_log_insert( "badip", username, "INVALID IP", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        http.Error(w, "Error 404 Invalid Access.", 404)
    } else {
        result = true
    }
    
    return result
}

func checkBaseActionAuth( w http.ResponseWriter, r *http.Request, actionident string ) bool {
    result := false

    if checkBaseActionIP(w, r, actionident) == false {
        //invalid IP
    } else if checkLoguedUser( w, r ) == false {
        showInfo( actionident + ": no logged user" )
        //show login
        loginFormShow( w, r, "" )
    } else {
        result = true
    }
    
    return result
}

func checkBaseActionAuthAdmin( w http.ResponseWriter, r *http.Request, actionident string ) bool {
    result := false

    if checkBaseActionAuth(w, r, actionident) == false {
        //bad IP or user
    } else if checkLoguedUserAdmin( w, r ) == false {
        showInfo( actionident + ": no logged user ADMIN NEEDED" )
        //show login
        listBase( w, r )
    } else  {
        result = true
    }
    
    return result
}

func checkLoginErrorMax( ip string ) bool {
    result := false
    
    logindata := sqlite_getLogsLogins(ip)
    if len(logindata) >= 5 {
        result = true
    }
    
    return result
}

//LOGIN FORM

type loginForm struct {
    User    string
    Pass    string
    Msginfo string
}

func login(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionIP(w, r, "LOGIN") == false {
        //Invalid IP
    } else if len( username ) > 0 {
        showInfo( "LOGIN: logged user: " + username + " - " + sessionident )
        //Check login
        listBase( w, r )
    }else if r.Method != http.MethodPost {
        showInfo( "LOGIN-SHOW: form login show: " + " - " + sessionident )
        sqlite_log_insert( "login", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //show login
        loginFormShow( w, r, "" )
        //return
    } else {
        //post method: check valid login
        // Set user as authenticated
        //if r.FormValue("user") == "test" && r.FormValue("pass") == "test" {
        username := getParamPost(r, "user")
        pass := getParamPost(r, "pass")
        if checkLoginErrorMax( getUserIP( r ) ) {
            //Check for 5 trys in last 5 minutes
            loginFormShow( w, r, "Max trys reached!" )
        } else if checkUser( username, pass ) {
            showInfo( "LOGIN-TRY: OK loggin user: " + username + " - " + sessionident )
            sessiondata := sqlite_getSession( sessionident )
            if len( sessiondata ) > 0 {
                sqlite_session_update( sessionident, username, getUserIP( r ) )
            } else {
                sqlite_session_insert( sessionident, username, getUserIP( r ) )
            }
            sqlite_log_insert( "login-OK", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
            listBase( w, r )
        } else {
            showInfo( "LOGIN-RETRY: KO loggin user: " + username + " - " + sessionident )
            sqlite_log_insert( "login-KO", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
            loginFormShow( w, r, "User ERROR!" )
        }
    }
}

func loginFormShow(w http.ResponseWriter, r *http.Request, msginfo string) {
    tmpl := template.Must(template.ParseFiles("html/login.html"))
    formData := loginForm{
        User:   r.FormValue("user"),
        Pass:   "",
        Msginfo:    msginfo,
    }
    tmpl.Execute(w, formData)
}

func logout(w http.ResponseWriter, r *http.Request) {
    tmpl := template.Must(template.ParseFiles("html/login.html"))
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    sqlite_log_insert( "logout", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
    sqlite_session_update( sessionident, "", getUserIP( r ) )
    
    formData := loginForm{
        User:   r.FormValue("user"),
        Pass:   "",
        Msginfo:    "Logout!",
    }
    tmpl.Execute(w, formData)
    
    //fmt.Fprintln(w, "logout!")
}

//LIST BASE MEDIAINFO + MEDIA

type listBaseElement map[int]map[string]string //{ 0: { "date": "", "action": "", "user": "", "ip": "", "description": "", "url": "", "referer": "" } },

type listBaseTemp struct {
    Menu        template.HTML
    Title       string
    PageBack    string 
    PageNext    string
    Genre       string
    Actor       string
    Search      string
    Todos       listBaseElement
    //Todos     map[int]map[string]string {}
}

func listBase(w http.ResponseWriter, r *http.Request) {
    //exclude /assets/
    if checkBaseActionAuth(w, r, "LIST-BASE") == false {
        //Invalid IP or user
    } else if strInStr( r.URL.RequestURI(), "/assets/" ) == false {
        //sqlite_log_insert( "list", "user", "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )

        // Check if user is authenticated
        if checkLoguedUser( w, r ) == false {
            //show login
            loginFormShow( w, r, "Invalid Access!" )
            return
        } else {
            //Page
            page := getParam(r, "page")
            //Genre
            genre := getParam(r, "genre")
            //Actor
            actor := getParam(r, "actor")
            //Search
            search := getParam(r, "search")
            //saction for kodi/phpmediaserver compat
            saction := getParam(r, "saction")
            
            if page == "" && genre == "" && actor == "" && search == "" && saction == "" {
                //Frontal page
                listBaseFrontal(w,r)
            } else if saction != "" {
                //KODI actions, based on compat with phpmediaserver/kodi plugin
                
            } else {
                //List based on searchs
                pagenum := strToInt( page )
                var pb, pn string
                if pagenum > 0 {
                    pb = intToStr( ( pagenum - 1 ) )
                    pn = intToStr( ( pagenum + 1 ) )
                }else{
                    pb = ""
                    pn = intToStr( ( pagenum + 1 ) )
                }
                var listdata listBaseElement
                var htmltitle string
                if len( genre ) > 0 || len( actor ) > 0 || len( search ) > 0 {
                    listdata = sqlite_getMediaMediaInfoFilter( G_LISTSIZE, pagenum, genre, actor, search )
                    htmltitle = "Media List"
                    if len( genre ) > 0 {
                        htmltitle += " :: Genre: " + genre
                    }
                    if len( actor ) > 0 {
                        htmltitle += " :: Actor: " + actor
                    }
                    if len( search ) > 0 {
                        htmltitle += " :: Search: " + search
                    }
                } else {
                    listdata = sqlite_getMediaMediaInfo( G_LISTSIZE, pagenum )
                    htmltitle = "Media List"
                }
                if len( page ) > 0 {
                    htmltitle += " :: Page: " + page
                }
                //if list < max elements remove nexpage
                if len(listdata) < G_LISTSIZE {
                    pn = ""
                }
                //get files and add tags
                for k, midata := range listdata {
                    taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
                    //showInfo( "MEDIAINFO-LIST-TAGS: " + taglist + " => " + midata[ "file" ] )
                    listdata[k]["Tags"] = taglist
                }
                showInfo( "LIST-BASE-DATANUM:" + intToStr(len(listdata)) )
                tmpl := template.Must(template.ParseFiles("html/item.list.html"))
                menu := getMenu(w, r)
                formData := listBaseTemp{
                    Menu    :   template.HTML( menu ),
                    Title   :   htmltitle,
                    Todos   :   listdata,
                    PageBack:   pb,
                    PageNext:   pn,
                    Genre   :   genre,
                    Actor   :   actor,
                    Search  :   search,
                }
                showInfo( "LIST-BASE-LIST: showing data"  )
                tmpl.Execute(w, formData)   
            }
        }
    }
}

//MEDIAINFO FRONTAL LIST

type listBaseFrontalTemp struct {
    Menu        template.HTML
    //premiere
    Title1       string
    PageNext1    string
    Todos1       listBaseElement
    //premiere best rating
    Title2       string
    PageNext2    string
    Todos2       listBaseElement
    //premiere series
    Title3       string
    PageNext3    string
    Todos3       listBaseElement
    //continue
    Title4       string
    PageNext4    string
    Todos4       listBaseElement
    //livetv
    Title5       string
    PageNext5    string
    Todos5       listBaseElement
    //recomended
    Title6       string
    PageNext6    string
    Todos6       listBaseElement
    //last added
    Title7       string
    PageNext7    string
    Todos7       listBaseElement
}

func listBaseFrontal(w http.ResponseWriter, r *http.Request) {
    //Frontal page

    //Premiere
    listdata1 := sqlite_getMediaMediaInfo_Premiere( G_LISTSIZE_MIN )
    htmltitle1 := "Media List"
    pn1 := ""
    //get files and add tags
    for k, midata := range listdata1 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
        listdata1[k]["Tags"] = taglist
    }
    
    //Premiere best rating
    listdata2 := sqlite_getMediaMediaInfo_PremiereBR( G_LISTSIZE_MIN )
    htmltitle2 := "Media List Best Rating"
    pn2 := ""
    //get files and add tags
    for k, midata := range listdata2 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
        listdata2[k]["Tags"] = taglist
    }
    
    //Premiere series
    listdata3 := sqlite_getMediaMediaInfo_PremiereSeries( G_LISTSIZE_MIN )
    htmltitle3 := "Media List Best Rating"
    pn3 := ""
    //get files and add tags
    for k, midata := range listdata3 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
        listdata3[k]["Tags"] = taglist
    }
    
    //continue
    listdata4 := sqlite_getPlayedMediaInfoLimit( intToStr(G_LISTSIZE_MIN) )
    htmltitle4 := "Continue"
    pn4 := ""
    //get files and add tags
    for k, midata := range listdata4 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
        listdata4[k]["Tags"] = taglist
    }
    
    //LiveTV
    listdata5 := sqlite_getMediaLive( intToStr(G_LISTSIZE_MIN) )
    htmltitle5 := "LiveTV"
    pn5 := ""
    //get files and add tags
    for k, midata := range listdata5 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "url" ] ), " " )
        listdata5[k]["Tags"] = taglist
    }
    
    //Recomended
    listdata6 := make(listBaseElement)
    htmltitle6 := ""
    pn6 := ""
    if len(listdata4) > 0 {
        listdata6 = sqlite_getMediaMediaInfoRelatedID( listdata4[0]["idmediainfo"] )
        htmltitle6 = "Recomended"
        pn6 = ""
        //get files and add tags
        for k, midata := range listdata6 {
            taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
            listdata6[k]["Tags"] = taglist
        }
    }
    
    //Last Added
    listdata7 := sqlite_getMediaMediaInfo( G_LISTSIZE_MIN, 0 )
    htmltitle7 := "Last Added"
    pn7 := "1"
    //get files and add tags
    for k, midata := range listdata7 {
        taglist := strings.Join( fscrap_getFileTags( midata[ "file" ] ), " " )
        listdata7[k]["Tags"] = taglist
    }
    
    tmpl := template.Must(template.ParseFiles("html/item.list.frontal.html"))
    menu := getMenu(w, r)
    formData := listBaseFrontalTemp{
        Menu    :   template.HTML( menu ),
        Title1   :   htmltitle1,
        PageNext1:   pn1,
        Todos1   :   listdata1,
        Title2   :   htmltitle2,
        PageNext2:   pn2,
        Todos2   :   listdata2,
        Title3   :   htmltitle3,
        PageNext3:   pn3,
        Todos3   :   listdata3,
        Title4   :   htmltitle4,
        PageNext4:   pn4,
        Todos4   :   listdata4,
        Title5   :   htmltitle5,
        PageNext5:   pn5,
        Todos5   :   listdata5,
        Title6   :   htmltitle6,
        PageNext6:   pn6,
        Todos6   :   listdata6,
        Title7   :   htmltitle7,
        PageNext7:   pn7,
        Todos7   :   listdata7,
    }
    showInfo( "FRONTAL-LIST: showing data"  )
    tmpl.Execute(w, formData)
}

//MEDIAINFO SHOW INFO

type mediainfoListTemp struct {
    Menu    template.HTML
    Title   string
    Todos   logsListElement
    //Todos   map[int]map[string]string {}
    Actors   logsListElement
    Related  logsListElement
    Genres   logsListElement
    Tags     []string
    TimeEnd  string
}

func mediainfoInfo(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-SHOWINFO") == false {
        //Invalid IP or user
    } else {
        id := getParam(r, "id")
        showInfo( "MEDIAINFO-SHOWINFO: " + id  )
        tmpl := template.Must(template.ParseFiles("html/item.info.html"))
        listdata := sqlite_getMediaInfoID( id )
        menu := getMenu(w, r)
        var actors []string
        var actorslist2 map[int]map[string]string
        if len( listdata[ 0 ][ "actor" ] ) > 0 {
            actors = strings.Split( listdata[ 0 ][ "actor" ], "," )
            actorslist2 = sliceToMap( actors, "name" )
        }
        var relatedMediaInfo logsListElement
        var genres2 map[int]map[string]string
        if len( listdata[ 0 ][ "genre" ] ) > 0 {
            var genres []string
            genres = strings.Split( listdata[ 0 ][ "genre" ], "," )
            relatedMediaInfo = sqlite_getMediaMediaInfoRelated( id, genres )
            sliceTrim(genres)
            genres2 = sliceToMap( genres, "name" )
        }
        var timeend string
        if listdata[ 0 ][ "runtime" ] != "" {
            timein := time.Now().Local().Add(time.Minute * time.Duration( strToInt( listdata[ 0 ][ "runtime" ] ) ) )
            timeend = timein.Format("15:04")
        } else {
            timeend = "00:00"
        }
        //get files adn select first
        medialist := sqlite_getMediaWhithMediaInfoID( listdata[ 0 ][ "idmediainfo" ] )
        taglist := []string {}
        for _, mdata := range medialist {
            taglist = fscrap_getFileTags( mdata[ "file" ] )
            break
        }
        formData := mediainfoListTemp{
            Menu    :   template.HTML( menu ),
            Title   :   "Media Info",
            Todos   :   listdata,
            Actors  :   actorslist2,
            Related :   relatedMediaInfo,
            Genres  :   genres2,
            TimeEnd :   timeend,
            Tags    :   taglist,
        }
        tmpl.Execute(w, formData)
    }
}

//NEXT MEDIAINFO

func mediainfoNext(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-NEXT") == false {
        //Invalid IP or user
    } else {
        id := r.URL.Query().Get("id")
        showInfo( "MEDIAINFO-NEXT: " + id  )
        listdata := sqlite_getMediaInfoID( id )
        season := strToInt( listdata[ 0 ][ "season" ] )
        episode := strToInt( listdata[ 0 ][ "episode" ] )
        if season > 0 && episode > 0 {
            //series
            listdata2 := sqlite_getMediaInfoMediaInfoChaptersNext( listdata[ 0 ][ "title" ], listdata[ 0 ][ "year" ] )
            //showInfo( "MEDIAINFO-NEXT-SERIES-NOW: " + listdata[ 0 ][ "season" ] + "x" + listdata[ 0 ][ "episode" ] )
            next := false
            for x := 0; x < len(listdata2); x++ {
                if next {
                    listdata[ 0 ][ "idmediainfo" ] = listdata2[ x ][ "idmediainfo" ]
                    break
                } else if listdata2[ x ][ "season" ] == listdata[ 0 ][ "season" ] && listdata2[ x ][ "episode" ] == listdata[ 0 ][ "episode" ] {
                    listdata[ 0 ][ "idmediainfo" ] = "0"
                    next = true
                }
                //showInfo( "MEDIAINFO-NEXT-SERIES-NOW-CHECK: " + listdata2[ x ][ "season" ] + "x" + listdata2[ x ][ "episode" ] )
            }
            if listdata[ 0 ][ "idmediainfo" ] == "0" {
                listdata[ 0 ][ "idmediainfo" ] = listdata2[ 0 ][ "idmediainfo" ]
            }
            showInfo( "MEDIAINFO-NEXT-SERIES: " + listdata[ 0 ][ "idmediainfo" ] )
        } else {
            //movies
            if len( listdata[ 0 ][ "genre" ] ) > 0 {
                var genres []string
                genres = strings.Split( listdata[ 0 ][ "genre" ], "," )
                listdata2 := sqlite_getMediaMediaInfoRelated( id, genres )
                if listdata[ 0 ][ "idmediainfo" ] == listdata2[ 0 ][ "idmediainfo" ] || ( listdata[ 0 ][ "title" ] == listdata2[ 0 ][ "title" ] && listdata[ 0 ][ "year" ] == listdata2[ 0 ][ "year" ] ) {
                    listdata[ 0 ][ "idmediainfo" ] = listdata2[ 1 ][ "idmediainfo" ]
                } else {
                    listdata[ 0 ][ "idmediainfo" ] = listdata2[ 0 ][ "idmediainfo" ]
                }
                showInfo( "MEDIAINFO-NEXT-MOVIES: " + listdata[ 0 ][ "idmediainfo" ] )
            }
        }
        if sqlite_checkMediaInfoID( listdata[ 0 ][ "idmediainfo" ] ) == false {
            //Not valid, random
		    //http.Error(w, "File not found.", 404)
            listdata = sqlite_getMediaInfoRandom()
            showInfo( "MEDIAINFO-NEXT-RANDOM: " + listdata[ 0 ][ "idmediainfo" ] )
        }
        newUrl := "/mediainfo/?id=" + listdata[ 0 ][ "idmediainfo" ]
        showInfo( "MEDIAINFO-NEXT-URL: " + newUrl )
        http.Redirect(w, r, newUrl, http.StatusSeeOther)
    }
}

//CHAPTERS MEDIAINFO

func mediainfoChapters(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-CHAPTERS") == false {
        //Invalid IP or user
    } else {
        id := r.URL.Query().Get("id")
        showInfo( "MEDIAINFO-CHAPTERS: " + id  )
        listdata := sqlite_getMediaInfoID( id )
        //series
        listdata2 := sqlite_getMediaInfoMediaInfoChapters( listdata[ 0 ][ "title" ], listdata[ 0 ][ "year" ] )
        htmltitle := "Chapters List"
        tmpl := template.Must(template.ParseFiles("html/chapters.list.html"))
        menu := getMenu(w, r)
        formData := listBaseTemp{
            Menu    :   template.HTML( menu ),
            Title   :   htmltitle,
            Todos   :   listdata2,
            
        }
        showInfo( "LOGS-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}


//DOWNLOAD MEDIAINFO

func mediainfoDownload(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-DOWNLOAD") == false {
        //Invalid IP or user
    } else {
        id := r.URL.Query().Get("id")
        showInfo( "MEDIAINFO-DOWNLOAD: " + id  )
        var file string
        listdata := sqlite_getMediaInfoID( id )
        if listdata[ 0 ][ "file" ] != "" {
            file = listdata[ 0 ][ "file" ]
        }
        filedef := G_DEBUGFILEVIDEO
        
        if file != "" && fileExist( file ) {
            // path/to/whatever exists
            sendFile( w, r, file )
        } else if filedef != "" && fileExist( filedef ) {
            // path/to/whatever does *not* exist
            sendFile( w, r, filedef )
        } else {
            //File not found, send 404
		    http.Error(w, "File not found.", 404)
        }
    }
}

//IMG MEDIAINFO

func mediainfoImg(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-IMG") == false {
        //Invalid IP or user
    } else {
        id := r.URL.Query().Get("id")
        typeimg := r.URL.Query().Get("type")
        if len(typeimg) == 0 {
            typeimg = "poster"
        }
        typeimg2 := "poster"
        showInfo( "MEDIAINFO-IMG: " + id  )
        var file string
        if id == "back" || id == "next" {
            file = pathJoin( pathJoin( G_APPPATH, "assets" ), id + ".jpg" )
        } else if fileExist( pathJoin( G_IMAGES_FOLDER, id + "." + typeimg ) ){
            file = pathJoin( G_IMAGES_FOLDER, id + "." + typeimg )
            //file = "cache/mediadata/" + id + ".poster"
        } else {
            file = pathJoin( G_IMAGES_FOLDER, id + "." + typeimg2 )
        }
        filedef := "assets/def.jpg"
        if fileExist( file ) {
            // path/to/whatever exists
            sendFile( w, r, file )
        } else if fileExist( filedef ) {
            // path/to/whatever does *not* exist
            sendFile( w, r, filedef )
        } else {
            //File not found, send 404
		    http.Error(w, "File not found.", 404)
        }
    }
}

//IMG ACTOR

func actorImg(w http.ResponseWriter, r *http.Request) {
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "ACTOR-IMG") == false {
        //Invalid IP or user
    } else {
        id := getParam(r,"id")
        showInfo( "ACTOR-IMG: " + id  )
        file := pathJoin( G_IMAGES_FOLDER, id + "" )
        filedef := "assets/def.jpg"
        if fileExist( file ) {
            // path/to/whatever exists
            sendFile( w, r, file )
        } else if fileExist( filedef ) {
            // path/to/whatever does *not* exist
            sendFile( w, r, filedef )
        } else {
            //File not found, send 404
		    http.Error(w, "File not found.", 404)
        }
    }
}

//PLAYER

//MEDIAINFO PLAYER

type playerListTemp struct {
    Menu        template.HTML
    Title       string
    Todos       logsListElement
    //Todos     map[int]map[string]string {}
    Actors      logsListElement
    Related     logsListElement
    Genres      logsListElement
    Tags        []string
    TimeEnd     string
    Codecs      map[string]string
    FileTime    int
    PrevPlayed  int
    TracksAudio ffprobeStreams
    TracksSubs  ffprobeStreams
    TracksSubsL ffprobeStreams
}

func mediaPlayer(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-PLAYER") == false {
        //Invalid IP or user
    } else {
        //Params: id : idmediainfo
        id := getParam(r, "id")
        showInfo( "MEDIAINFO-PLAYER: " + id  )
        tmpl := template.Must(template.ParseFiles("html/item.player.html"))
        listdata := sqlite_getMediaInfoMediaInfoIDLast( id )
        menu := getMenu(w, r)
        //get file duration
        filetimes := ffprobeDuration( listdata[0]["file"] )
        filetime := 3600 * 2;
        if len(filetimes) > 0 {
            filetime = strToInt(filetimes)
        }
        //get preplayed time
        //playeddata := sqlite_getPlayedIDMediaInfo(id, username)
        playeddata := sqlite_getPlayedIDMediaInfo(listdata[0]["idmedia"], username)
        prevplayedtime := 0
        if len(playeddata) > 0 {
            tt := strToInt( playeddata[0]["now"] )
            if tt > 0 && tt < filetime {
                prevplayedtime = tt
            }
        }
        tracksaudio := ffprobeTracksAudio(listdata[0]["file"])
        trackssub   := ffprobeTracksSubsOCR(listdata[0]["file"])
        trackssub2   := ffprobeTracksSubsNOOCR(listdata[0]["file"])
        
        formData := playerListTemp{
            Menu        :   template.HTML( menu ),
            Title       :   "Media Player",
            Todos       :   listdata,
            //Actors    :   actorslist2,
            //Related   :   relatedMediaInfo,
            //Genres    :   genres2,
            Codecs      :   G_PLAYER_CODECS,
            FileTime    :   filetime,
            PrevPlayed  :   prevplayedtime,
            TracksAudio :   tracksaudio,
            TracksSubs  :   trackssub,
            TracksSubsL :   trackssub2,
        }
        tmpl.Execute(w, formData)
    }
}

func mediaPlaySetTime(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-SETTIME") == false {
        //Invalid IP or user
    } else {
        //Params: id : idmedia, timeplayed: time played, timemax : totaltime
        id := getParam(r, "id")
        timeplayed := getParam(r, "timeplayed")
        timemax := getParam(r, "timemax")
        showInfo( "MEDIAINFO-SETTIME: " + id  )
        msginfo := timeplayed + "/" + timemax
        sqlite_played_insert( id, username, timeplayed, timemax )
        showInfo( "MEDIAINFO-SETTIME: showing data"  )
        fmt.Fprintf(w, "Set Time: %s", msginfo)
    }
}

//MEDIAINFO PLAYTIME

func mediaPlayTime(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuth(w, r, "MEDIAINFO-PLAYTIME") == false {
        //Invalid IP or user
    } else if r.Method == http.MethodHead {
        //Exclude HEAD request
        showInfo( "MEDIAINFO-PLAYTIME: Exclude HEAD request." )
    } else {
        //Params: 
        //id:idmedia
        //timeplayed : int
        //mode : G_PLAYER_CODECS[mode]
        //audiotrack : int
        //subtrack : int
        //quality : sd|hd
        id := getParam(r, "id")
        timeplayed := getParam(r, "timeplayed")
        mode := getParam(r, "mode")
        audiotrack := getParam(r, "audiotrack")
        subtrack := getParam(r, "subtrack")
        quality := getParam(r, "quality")
        showInfo( "MEDIAINFO-PLAYTIME: " + id  )
        listdata := sqlite_getMediaID( id )
        if listdata[ 0 ][ "file" ] != "" && fileExist( listdata[ 0 ][ "file" ] ) {
            sqlite_played_insert( id, username, timeplayed, ffprobeDuration( listdata[ 0 ][ "file" ] ) )
            sqlite_playing_insert( id, username, mode, "" )
            showInfo( "MEDIAINFO-PLAYTIME: send file: " + listdata[ 0 ][ "file" ] )
            sendVideo(w, r, listdata[ 0 ][ "file" ], timeplayed, mode, audiotrack, subtrack, quality)
        } else {
            showInfo( "MEDIAINFO-PLAYTIME: send file ERROR: " + listdata[ 0 ][ "file" ] )
            if G_DEBUG {
                sqlite_played_insert( id, username, timeplayed, ffprobeDuration( G_DEBUGFILEVIDEO ) )
                sendVideo(w, r, G_DEBUGFILEVIDEO, timeplayed, mode, audiotrack, subtrack, quality)
            }
        }
        sqlite_playing_delete( id, username )
    }
}

//PLAYER LOAD SUBS

func mediaPlaySubLoad(w http.ResponseWriter, r *http.Request) {
    
    // Check if user is authenticated
    if checkBaseActionAuth(w, r, "MEDIAINFO-LOADSUB") == false {
        //Invalid IP or user
    } else {
        //Params: id : idmedia, subtrack: subtrack id (from total streams)
        msginfo := ""
        id := getParam(r, "id")
        subtrack := getParam(r, "subtrack")
        filesub := pathJoin( G_IMAGES_FOLDER, id + "." + subtrack + ".srt" )
        showInfo( "MEDIAINFO-LOADSUB: " + id + "-" + subtrack )
        if fileExist(filesub) == false {
            //extract new one
            midata := sqlite_getMediaID( id )
            if len(midata) > 0 && fileExist( midata[0]["file"] ) {
                ffmpegSubExtract( midata[0]["file"], filesub, subtrack )
            }
        }
        //wait create
        time.Sleep(time.Second)
        if fileExist(filesub) {
            msginfo = subToJson( filesub )
        }
        showInfo( "MEDIAINFO-LOADSUB: showing data" )
        fmt.Fprintf(w, msginfo)
    }
}

//LIVETV LIST

func liveTVList(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuth(w, r, "LIVETV-BASE") == false {
        //Invalid IP or user
    } else {
        listdata := sqlite_getMediaLiveAll()
        htmltitle := "LiveTV List"
        //get files and add tags
        for k, midata := range listdata {
            taglist := strings.Join( fscrap_getFileTags( midata[ "url" ] ), " " )
            //showInfo( "MEDIAINFO-LIST-TAGS: " + taglist + " => " + midata[ "file" ] )
            listdata[k]["Tags"] = taglist
        }
        showInfo( "LIVETV-BASE-DATANUM:" + intToStr(len(listdata)) )
        tmpl := template.Must(template.ParseFiles("html/item.livetv.list.html"))
        menu := getMenu(w, r)
        formData := listBaseTemp{
            Menu    :   template.HTML( menu ),
            Title   :   htmltitle,
            Todos   :   listdata,
            //PageBack:   pb,
            //PageNext:   pn,
            //Genre   :   genre,
            //Actor   :   actor,
            //Search  :   search,
        }
        showInfo( "LIVETV-BASE: showing data" )
        tmpl.Execute(w, formData)
    }
}

func liveTVPlayer(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuth(w, r, "LIVETV-PLAYER") == false {
        //Invalid IP or user
    } else {
        //Params: id : idmedialive
        id := getParam(r, "id")
        showInfo( "LIVETV-PLAYER: " + id  )
        tmpl := template.Must(template.ParseFiles("html/item.livetv.player.html"))
        listdata := sqlite_getMediaLiveID( id )
        menu := getMenu(w, r)
        formData := playerListTemp{
            Menu        :   template.HTML( menu ),
            Title       :   "Media Player",
            Todos       :   listdata,
            //Actors    :   actorslist2,
            //Related   :   relatedMediaInfo,
            //Genres    :   genres2,
            //Codecs      :   G_PLAYER_CODECS,
            //FileTime    :   filetime,
            //PrevPlayed  :   prevplayedtime,
            //TracksAudio :   tracksaudio,
            //TracksSubs  :   trackssub,
            //TracksSubsL :   trackssub2,
        }
        tmpl.Execute(w, formData)
    }
}

func liveTVPlayTime(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuth(w, r, "LIVETV-BASE") == false {
        //Invalid IP or user
    } else  if r.Method == http.MethodHead {
        //Exclude HEAD request
        showInfo( "LIVETV-PLAYTIME: Exclude HEAD request." )
    } else {
        //Params: 
        //id:idmedialive
        //timeplayed : int
        //mode : G_PLAYER_CODECS[mode]
        //audiotrack : int
        //subtrack : int
        //quality : sd|hd
        id := getParam(r, "id")
        timeplayed := getParam(r, "timeplayed")
        mode := getParam(r, "mode")
        audiotrack := getParam(r, "audiotrack")
        subtrack := getParam(r, "subtrack")
        quality := getParam(r, "quality")
        showInfo( "LIVETV-PLAYTIME: " + id  )
        listdata := sqlite_getMediaLiveID( id )
        if listdata[ 0 ][ "url" ] != "" {
            showInfo( "LIVETV-PLAYTIME: send file: " + listdata[ 0 ][ "url" ] )
            sendVideo(w, r, listdata[ 0 ][ "url" ], timeplayed, mode, audiotrack, subtrack, quality)
        } else {
            showInfo( "LIVETV-PLAYTIME: send file ERROR: " + listdata[ 0 ][ "url" ] )
        }
    }
}

//ADMINS ACTIONS

//LIST LOGS

type logsListElement map[int]map[string]string //{ 0: { "date": "", "action": "", "user": "", "ip": "", "description": "", "url": "", "referer": "" } },

type logsListTemp struct {
    Menu    template.HTML
    Title   string
    Search  string
    Todos   logsListElement
    //Todos   map[int]map[string]string {}
}

func logsList(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "LOGS-LIST") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "logs-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        tmpl := template.Must(template.ParseFiles("html/log.list.html"))
        search := getParam(r, "search")
        listdata := sqlite_getLogsFilter(search)
        menu := getMenu(w, r)
        formData := logsListTemp{
            Menu    :   template.HTML( menu ),
            Title   :   "Log List",
            Todos   :   listdata,
        }
        showInfo( "LOGS-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}

//USERS LIST

type usersListTemp struct {
    Menu    template.HTML
    Title   string
    Todos   logsListElement
    //Todos   map[int]map[string]string {}
    MsgInfo  string
}

func usersListTemplate(w http.ResponseWriter, r *http.Request, msginfo string) {
    
    tmpl := template.Must(template.ParseFiles("html/users.list.html"))
    listdata := sqlite_getUsers()
    menu := getMenu(w, r)
    formData := usersListTemp{
        Menu    :   template.HTML( menu ),
        Title   :   "Users List",
        Todos   :   listdata,
        MsgInfo :   msginfo,
    }
    showInfo( "USERS-LIST: showing data"  )
    tmpl.Execute(w, formData)
}

func usersListBase(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-LIST") == false {
        //Invalid IP or user or admin
    } else  {
        sqlite_log_insert( "users-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        usersListTemplate( w, r, "" )
    }
}

func usersListAdd(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-ADD") == false {
        //Invalid IP or user or admin
    } else  {
        sqlite_log_insert( "users-add", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2, useradmin
        fusername := getParamPost( r, "username" )
        pass1 := getParamPost( r, "pass1" )
        pass2 := getParamPost( r, "pass2" )
        useradmin := getParamPost( r, "useradmin" )

        if useradmin == "1" {
            useradmin = fusername
        }

        if len( fusername ) < 3 || len( fusername ) > 18 {
            msginfo = "Invalid Username: " + fusername + " 3-18 chars without spaces or simbols."
        }else if len( pass1 ) < 5 {
            msginfo = "Invalid Password: 3-18 chars."
        }else if pass1 != pass2 {
            msginfo = "Passwords missmatch."
        } else {
            sqlite_user_insert( fusername, pass1, useradmin )
            msginfo = "User Created: " + fusername
        }

        usersListTemplate( w, r, msginfo )
    }
}

func usersListDelete(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "users-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2, useradmin
        fusername := getParamPost( r, "username" )

        if len( fusername ) < 3 || len( fusername ) > 18 {
            msginfo = "Invalid Username: " + fusername + " 3-18 chars without spaces or simbols."
        }else  {
            sqlite_user_delete( fusername )
            msginfo = "User Deleted: " + fusername
        }

        usersListTemplate( w, r, msginfo )
    }
}

func usersListPassChange(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-PASS-CHANGE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "users-passchange", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2
        fusername := getParamPost( r, "username" )
        pass1 := getParamPost( r, "pass1" )
        pass2 := getParamPost( r, "pass2" )

        if len( fusername ) < 3 || len( fusername ) > 18 {
            msginfo = "Invalid Username: " + fusername + " 3-18 chars without spaces or simbols."
        }else if len( pass1 ) < 5 {
            msginfo = "Invalid Password: 3-18 chars."
        }else if pass1 != pass2 {
            msginfo = "Passwords missmatch."
        } else {
            sqlite_user_update_pass( fusername, pass1 )
            msginfo = "User Password Changed: " + fusername
        }

        usersListTemplate( w, r, msginfo )
    }
}

func usersListDelAdmin(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-DEL-ADMIN") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "users-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2
        fusername := getParamPost( r, "username" )
        useradmin := ""

        if len( fusername ) < 3 || len( fusername ) > 18 {
            msginfo = "Invalid Username: " + fusername + " 3-18 chars without spaces or simbols."
        }else {
            sqlite_user_update_admin( fusername, useradmin )
            msginfo = "User ADMIN Removed: " + fusername
        }

        usersListTemplate( w, r, msginfo )
    }
}

func usersListSetAdmin(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "USERS-SET-ADMIN") == false {
        //Invalid IP or user or admin
    } else  {
        sqlite_log_insert( "users-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2
        fusername := getParamPost( r, "username" )
        useradmin := fusername

        if len( fusername ) < 3 || len( fusername ) > 18 {
            msginfo = "Invalid Username: " + fusername + " 3-18 chars without spaces or simbols."
        }else {
            sqlite_user_update_admin( fusername, useradmin )
            msginfo = "User ADMIN Added: " + fusername
        }

        usersListTemplate( w, r, msginfo )
    }
}

//MEDIA LIST IDENTIFY

//type listMediaElement []map[string]string //{ 0: { "date": "", "action": "", "user": "", "ip": "", "description": "", "url": "", "referer": "" } },
type listMediaElement map[int]map[string]string //{ 0: { "date": "", "action": "", "user": "", "ip": "", "description": "", "url": "", "referer": "" } },

type listMedia struct {
    Menu    template.HTML
    Title   string
    Todos   listMediaElement
    //Todos   map[int]map[string]string {}
}

func mediaListIdent(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST") == false {
        //Invalid IP or user or admin
    } else {
        tmpl := template.Must(template.ParseFiles("html/identify.list.html"))
        sqlite_log_insert( "media-info", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        search := getParam(r, "search")
        listdata := sqlite_getMediaMediaInfoIdent( G_LISTSIZE, search )
        menu := getMenu(w, r)
        formData := listMedia{
            Menu    :   template.HTML( menu ),
            Title   :   "Identify List",
            //Todos   :   mapMediaToSlice( listdata ),
            Todos   :   listdata,
        }
        //fmt.Println( listdata )
        //fmt.Println( mapsMediaKeyOrder( listdata ) )
        showInfo( "IDENTIFY-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}

type listMediaIdentShow struct {
    Title       string
    Idmedia     string
    FTitle      string
    File        string
    BaseFile    string
    Season      string
    Episode     string
    MaxSeason   []string
    MaxEpisode  []string
    MsgInfo     string
    Scrappers   []string
}

func mediaListIdentShow(w http.ResponseWriter, r *http.Request) {
    //sessionident := getSessionID( w, r )
    //username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-SHOW") == false {
        //Invalid IP or user or admin
    } else {
        //sqlite_log_insert( "media-e-show", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        var mediatitle, ftitle, episode, season, mediafile, msginfo string
        idmedia := getParam(r, "id")
        media := sqlite_getMediaID( idmedia )
        if len(media) > 0 {
            msginfo = ""
            mediafile = media[0]["file"]
            ftitle = strings.Replace(mediafile, G_DOWNLOADS_FOLDER, "", -1)
            ftitle = filepath.Base(ftitle)
            mediatitle = fscrap_cleanTitle(ftitle)
            season, episode = fscrap_getSeasonChapter(ftitle)
            if season != "" {
                //cut on SxE
                mediatitle = getSubString( mediatitle, 0, fscrap_getSeasonChapterPos( mediatitle ) )
            }
        } else {
            msginfo = "IDMEDIA not found: " + idmedia
            mediafile = ""
            ftitle = ""
        }
        tmpl := template.Must(template.ParseFiles("html/identify.show.html"))
        formData := listMediaIdentShow{
            Title       :   "Identify Element",
            Idmedia     :   idmedia,
            FTitle      :   mediatitle,
            File        :   mediafile,
            BaseFile    :   ftitle,
            Season      :   season,
            Episode     :   episode,
            MsgInfo     :   msginfo,
            Scrappers   :   G_SCRAPPERLIST,
            MaxSeason   :   fscrap_getSeasons(),
            MaxEpisode  :   fscrap_getChapters(),
        }
        //fmt.Println( listdata )
        //fmt.Println( mapsMediaKeyOrder( listdata ) )
        showInfo( "IDENTIFY-LIST-SHOW: showing data"  )
        tmpl.Execute(w, formData)
    }
}

func mediaListIdentDelete(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "media-e-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        var mediafile, msginfo string
        //Params: id = idmedia
        idmedia := getParam(r, "id")
        media := sqlite_getMediaID( idmedia )
        if len(media) > 0 {
            mediafile = media[0]["file"]
            msginfo = "IDMEDIA DELETED: " + mediafile
            delTree(mediafile)
            sqlite_media_delete(idmedia)
        } else {
            msginfo = "IDMEDIA not found: " + idmedia
        }
        showInfo( "IDENTIFY-LIST-DELETE: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

type listMediaIdentSearchT struct {
    Title       string
    MsgInfo     string
    Todos       listMediaElement
}

func mediaListIdentSearch(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-WEBSEARCH") == false {
        //Invalid IP or user or admin
    } else {
        //Params: idmedia, ftitle, season, episode, fscrapper
        var idmedia, ftitle, season, episode, fscrapper, imdb, msginfo string
        var formData listMediaIdentSearchT
        idmedia     = getParamPost(r, "idmedia")
        ftitle      = getParamPost(r, "ftitle")
        season      = getParamPost(r, "season")
        episode     = getParamPost(r, "episode")
        fscrapper   = getParamPost(r, "fscrapper")
        imdb        = getParamPost(r, "imdb")
        showInfo( "IDENTIFY-LIST-WEBSEARCH: " + idmedia + " - " + ftitle + " - " + season + " - " + episode + " - " + fscrapper + " - "  + imdb  )
        data := webSearch( "imdb " + ftitle, "", "" )
        tmpl := template.Must(template.ParseFiles("html/identify.search.html"))
        if len(data) > 0 {
            msginfo = ""
            urllist := make(listMediaElement)
            x := 0
            for title, url := range data {
                idsearch := wscrap_getURLID( url )
                if len(idsearch) > 0 {
                    urllist[x] = map[string]string { "Title" : title, "URL" : url, "Idsearch" : idsearch }
                    x++
                }
            }
            formData = listMediaIdentSearchT{
                Title       :   "Search Title",
                MsgInfo     :   msginfo,
                Todos       :   urllist,
            }
            //fmt.Println( formData )
            //fmt.Println( mapsMediaKeyOrder( formData ) )
        } else {
            msginfo = "No Results"
            formData = listMediaIdentSearchT{
                Title       :   "Search Title",
                MsgInfo     :   msginfo,
                //Todos       :   urllist,
            }
            //fmt.Println( formData )
            //fmt.Println( mapsMediaKeyOrder( formData ) )
        }
        showInfo( "IDENTIFY-LIST-WEBSEARCH: showing data"  )
        tmpl.Execute(w, formData)
    }
}

func mediaListIdentAssign(w http.ResponseWriter, r *http.Request) {
    //sessionident := getSessionID( w, r )
    //username := sqlite_getSessionUser( sessionident )
    msginfo := "No data."
    
    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-ASSIGN") == false {
        //Invalid IP or user or admin
    } else {
        //sqlite_log_insert( "media-e-assing", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //Params: idmedia, ftitle, season, episode, fscrapper
        var idmedia, ftitle, season, episode, fscrapper, imdb string
        idmedia     = getParamPost(r, "idmedia")
        ftitle      = getParamPost(r, "ftitle")
        season      = getParamPost(r, "season")
        episode     = getParamPost(r, "episode")
        fscrapper   = getParamPost(r, "fscrapper")
        imdb        = getParamPost(r, "imdb")
        showInfo( "IDENTIFY-LIST-ASSIGN: " + idmedia + " - " + ftitle + " - " + season + " - " + episode + " - " + fscrapper + " - "  + imdb  )
        if sqlite_checkMediaIDFile( idmedia ) == false {
            showInfo( "IDENTIFY-LIST-ASSIGN: IDMEDIA file not found: " + idmedia )
            msginfo = "IDMEDIA ASSIGN: File not exist " + idmedia
        } else if sqlite_checkMediaID( idmedia ) {
            showInfo( "IDENTIFY-LIST-ASSIGN: Valid idmedia getting info"  )
            msginfo = "IDMEDIA ASSIGN: " + idmedia
            if season != "" {
                msginfo += "<br />Title: " + ftitle
            } else {
                msginfo += "<br />Title: " + ftitle + " " + season + "x" + fmt.Sprintf( "%02d", episode )
            }
            msginfo += "<br />Scrapper: " + fscrapper
            msginfo += "<br />IMDBid: " + imdb
            msginfo += "<br />"
            idmediainfo := identMedia( idmedia, fscrapper, ftitle, season, episode, imdb )
            showInfo( "IDENTIFY-LIST-ASSIGN: Ident Result: " + idmediainfo )
            if idmediainfo != "" {
                midata := sqlite_getMediaInfoID(idmediainfo)
                if len( midata ) > 0 {
                    if midata[0]["season"] != "" {
                        msginfo += "<br />IDMEDIA ASSIGN: " + midata[0]["title"] + " " + midata[0]["season"] + "x" + fmt.Sprintf( "%02d", strToInt(midata[0]["episode"]) ) + " (" + midata[0]["year"] + ") -> " + idmedia
                    } else {
                        msginfo += "<br />IDMEDIA ASSIGN: " + midata[0]["title"] + " (" + midata[0]["year"] + ") -> " + idmedia
                    }
                    showInfo( "IDENTIFY-LIST-ASSIGN: Assigned idmediainfo " + midata[0]["title"] + " (" + midata[0]["year"] + ") -> " + idmedia )
                } else {
                    msginfo += "<br />IDMEDIA ASSIGNED BUT NOT DATA MEDIAINFO: " + idmediainfo
                    showInfo( "IDENTIFY-LIST-ASSIGN: IDMEDIAINFO but no data: " + idmediainfo )
                }
            } else {
                msginfo += "<br />IDMEDIA FAILED no valid data: " + idmedia
                showInfo( "IDENTIFY-LIST-ASSIGN: FAILED no data for: " + idmedia )
            }
        } else {
            msginfo += "<br />IDMEDIA not found: " + idmedia
            showInfo( "IDENTIFY-LIST-ASSIGN: IDMEDIA not found: " + idmedia )
        }
        showInfo( "IDENTIFY-LIST-ASSIGN: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func mediaListIdentAssignD(w http.ResponseWriter, r *http.Request) {
    msginfo := "No data."
    
    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-ASSIGND") == false {
        //Invalid IP or user or admin
    } else {
        //Params: idmedia, ftitle, season, episode, fscrapper
        var idmedia, ftitle, season, episode, fscrapper, imdb string
        idmedia     = getParamPost(r, "idmedia")
        ftitle      = getParamPost(r, "ftitle")
        season      = getParamPost(r, "season")
        episode     = getParamPost(r, "episode")
        fscrapper   = getParamPost(r, "fscrapper")
        imdb        = getParamPost(r, "imdb")
        showInfo( "IDENTIFY-LIST-ASSIGND: " + idmedia + " - " + ftitle + " - " + season + " - " + episode + " - " + fscrapper + " - "  + imdb  )
        if sqlite_checkMediaIDFile( idmedia ) == false {
            showInfo( "IDENTIFY-LIST-ASSIGN: IDMEDIA file not found: " + idmedia )
            msginfo = "IDMEDIA ASSIGN: File not exist " + idmedia
        } else if sqlite_checkMediaID( idmedia ) {
            showInfo( "IDENTIFY-LIST-ASSIGND: Valid idmedia getting info"  )
            msginfo = "IDMEDIA ASSIGND: " + idmedia
            if season != "" {
                msginfo += "<br />Title: " + ftitle
            } else {
                msginfo += "<br />Title: " + ftitle + " " + season + "x" + fmt.Sprintf( "%02d", episode )
            }
            msginfo += "<br />Scrapper: EXACT SAME TITLE"
            msginfo += "<br />IMDBid: " + imdb
            msginfo += "<br />"
            idmediainfo := identMediaTitle( idmedia, fscrapper, ftitle, season, episode, imdb )
            showInfo( "IDENTIFY-LIST-ASSIGND: Ident Result: " + idmediainfo )
            if idmediainfo != "" {
                midata := sqlite_getMediaInfoID(idmediainfo)
                if len( midata ) > 0 {
                    if midata[0]["season"] != "" {
                        msginfo += "<br />IDMEDIA ASSIGND: " + midata[0]["title"] + " " + midata[0]["season"] + "x" + fmt.Sprintf( "%02d", strToInt(midata[0]["episode"]) ) + " (" + midata[0]["year"] + ") -> " + idmedia
                    } else {
                        msginfo += "<br />IDMEDIA ASSIGND: " + midata[0]["title"] + " (" + midata[0]["year"] + ") -> " + idmedia
                    }
                    sqlite_media_update_idmediainfo( idmedia, midata[0]["idmediainfo"] )
                    showInfo( "IDENTIFY-LIST-ASSIGND: Assigned idmediainfo " + midata[0]["title"] + " (" + midata[0]["year"] + ") -> " + idmedia )
                } else {
                    msginfo += "<br />IDMEDIA ASSIGNED BUT NOT DATA MEDIAINFO: " + idmediainfo
                    showInfo( "IDENTIFY-LIST-ASSIGND: IDMEDIAINFO but no data: " + idmediainfo )
                }
            } else {
                msginfo += "<br />IDMEDIA FAILED no valid data: " + idmedia
                showInfo( "IDENTIFY-LIST-ASSIGND: FAILED no data for: " + idmedia )
            }
        } else {
            msginfo += "<br />IDMEDIA not found: " + idmedia
            showInfo( "IDENTIFY-LIST-ASSIGND: IDMEDIA not found: " + idmedia )
        }
        showInfo( "IDENTIFY-LIST-ASSIGND: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func mediaListIdentAssignC(w http.ResponseWriter, r *http.Request) {
    msginfo := "No data."
    
    if checkBaseActionAuthAdmin(w, r, "IDENTIFY-LIST-ASSIGNC") == false {
        //Invalid IP or user or admin
    } else {
        //Params: idmedia, ftitle, season, episode, fscrapper
        var idmedia, ftitle, season, episode, fscrapper, imdb string
        idmedia     = getParamPost(r, "idmedia")
        ftitle      = getParamPost(r, "ftitle")
        season      = getParamPost(r, "season")
        episode     = getParamPost(r, "episode")
        fscrapper   = getParamPost(r, "fscrapper")
        imdb        = getParamPost(r, "imdb")
        showInfo( "IDENTIFY-LIST-ASSIGNC: " + idmedia + " - " + ftitle + " - " + season + " - " + episode + " - " + fscrapper + " - "  + imdb  )
        if sqlite_checkMediaID( idmedia ) {
            showInfo( "IDENTIFY-LIST-ASSIGNC: Valid idmedia clearing mediainfo"  )
            msginfo = "IDMEDIA ASSIGN CLEAR: " + idmedia
            sqlite_media_update_idmediainfo( idmedia, "0" )
        } else {
            msginfo += "<br />IDMEDIA not found: " + idmedia
            showInfo( "IDENTIFY-LIST-ASSIGNC: IDMEDIA not found: " + idmedia )
        }
        showInfo( "IDENTIFY-LIST-ASSIGNC: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

//MEDIAINFO LIST 

func mediaInfoListIdent(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "MEDIAINFO-ADMIN-LIST") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "mediainfo-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        tmpl := template.Must(template.ParseFiles("html/mediainfo.list.html"))
        search := getParam(r, "search")
        listdata := sqlite_getMediaInfoFilter( G_LISTSIZE, search )
        menu := getMenu(w, r)
        formData := listBaseTemp{
            Menu    :   template.HTML( menu ),
            Title   :   "MediaInfo List",
            Todos   :   listdata,
        }
        showInfo( "MEDIAINFO-ADMIN-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}

func mediaInfoListIdentDelete(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "MEDIAINFO-ADMIN-LIST-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "mediainfo-e-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        var msginfo string
        //Params: id = idmediainfo
        idmediainfo := getParam(r, "id")
        if sqlite_checkMediaInfoID(idmediainfo) {
            msginfo = "IDMEDIAINFO DELETED: " + idmediainfo
            sqlite_mediainfo_delete(idmediainfo)
        } else {
            msginfo = "IDMEDIAINFO not found: " + idmediainfo
        }
        showInfo( "MEDIAINFO-ADMIN-LIST-DELETE: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

//PLAYED LIST

type playedBaseTemp struct {
    Menu        template.HTML
    Title       string
    Todos       listBaseElement
    Title2      string
    Todos2      listBaseElement
    //Todos     map[int]map[string]string {}
}

func playedListBase(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "PLAYED-LIST") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "played-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        tmpl := template.Must(template.ParseFiles("html/played.list.html"))
        listdata := sqlite_getPlayedMediaInfo()
        listdata2 := sqlite_getPlayingMediaInfo()
        menu := getMenu(w, r)
        formData := playedBaseTemp{
            Menu    :   template.HTML( menu ),
            Title   :   "Played List",
            Todos   :   listdata,
            Title2  :   "Playing Now List",
            Todos2  :   listdata2,
        }
        showInfo( "PLAYED-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}

func playedListBaseDelete(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuthAdmin(w, r, "PLAYED-ADMIN-LIST-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        var msginfo string
        //Params: id = idmedia, user = username
        idmedia := getParam(r, "id")
        username := getParam(r, "user")
        msginfo = "PLAYED DELETED: " + idmedia + "/" + username
        sqlite_played_delete(idmedia, username)
        showInfo( "PLAYED-ADMIN-LIST-DELETE: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func playingListBaseDelete(w http.ResponseWriter, r *http.Request) {
    if checkBaseActionAuthAdmin(w, r, "PLAYING-ADMIN-LIST-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        var msginfo string
        //Params: id = idmedia, user = username
        idmedia := getParam(r, "id")
        username := getParam(r, "user")
        msginfo = "PLAYING DELETED: " + idmedia + "/" + username
        sqlite_playing_delete(idmedia, username)
        showInfo( "PLAYING-ADMIN-LIST-DELETE: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

//CRON LIST

type listCron struct {
    Menu        template.HTML
    Title       string
    CronShort   template.HTML
    CronLong    template.HTML
}

func cronListBase(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "CRON-LIST") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "cron-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        tmpl := template.Must(template.ParseFiles("html/cron.list.html"))
        menu := getMenu(w, r)
        cs := readAllToHTML( G_CRONSHORTTIME_FILE )
        cl := readAllToHTML( G_CRONLONGTIME_FILE )
        formData := listCron{
            Menu        :   template.HTML( menu ),
            Title       :   "Last Cron Info",
            CronShort   :   template.HTML( cs ),
            CronLong    :   template.HTML( cl ),
        }
        showInfo( "CRON-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}


//IPs LIST

type ipListTemp struct {
    Menu    template.HTML
    Title   string
    Todos   logsListElement
    Todos1   logsListElement
    //Todos   map[int]map[string]string {}
    MsgInfo  string
}

func ipListTemplate(w http.ResponseWriter, r *http.Request, msginfo string) {
    
    tmpl := template.Must(template.ParseFiles("html/ip.list.html"))
    listdata := sqlite_getWhitelist()
    listdata1 := sqlite_getBans()
    menu := getMenu(w, r)
    formData := ipListTemp{
        Menu    :   template.HTML( menu ),
        Title   :   "IP List",
        Todos   :   listdata,
        Todos1   :   listdata1,
        MsgInfo :   msginfo,
    }
    showInfo( "IP-LIST: showing data"  )
    tmpl.Execute(w, formData)
}

func ipListBase(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IP-LIST") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "ip-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        ipListTemplate( w, r, "" )
    }
}

func ipWlRemove(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IP-WHITELIST-DEL") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "ip-whitelist-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: ip = IP
        fip := getParam( r, "ip" )

        if len( fip ) == 0 {
            msginfo = "Invalid IP: " + fip
        }else  {
            sqlite_whitelist_delete( fip )
            msginfo = "IP Deleted: " + fip
        }

        ipListTemplate( w, r, msginfo )
    }
}

func ipWlAdd(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IP-WHITELIST-ADD") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "ip-whitelist-add", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: ip
        fip := getParamPost( r, "ip" )
        
        if len( fip ) == 0 {
            msginfo = "Invalid IP: " + fip
        } else {
            sqlite_whitelist_insert( fip )
            msginfo = "IP Created: " + fip
        }

        ipListTemplate( w, r, msginfo )
    }
}

func ipBansRemove(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IP-BANS-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "ip-bans-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: id = ipbans
        fip := getParam( r, "ip" )

        if len( fip ) == 0 {
            msginfo = "Invalid IP: " + fip
        }else  {
            sqlite_bans_delete( fip )
            msginfo = "IP Deleted: " + fip
        }

        ipListTemplate( w, r, msginfo )
    }
}

func ipBansAdd(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "IP-BANS-ADD") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "ip-bans-add", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: ip
        fip := getParamPost( r, "ip" )
        
        if len( fip ) == 0 {
            msginfo = "Invalid IP: " + fip
        } else {
            sqlite_bans_insert( fip )
            msginfo = "IP Created: " + fip
        }

        ipListTemplate( w, r, msginfo )
    }
}

//LIVETV

func liveTVAdminList(w http.ResponseWriter, r *http.Request) {
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )

    if checkBaseActionAuthAdmin(w, r, "LIVETV-LIST") == false {
        //Invalid IP or user or admin
    } else {
        tmpl := template.Must(template.ParseFiles("html/livetv.list.html"))
        sqlite_log_insert( "livetv-list", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //search := getParam(r, "search")
        listdata := sqlite_getMediaLiveAll()
        menu := getMenu(w, r)
        formData := listMedia{
            Menu    :   template.HTML( menu ),
            Title   :   "LiveTV List",
            //Todos   :   mapMediaToSlice( listdata ),
            Todos   :   listdata,
        }
        //fmt.Println( listdata )
        //fmt.Println( mapsMediaKeyOrder( listdata ) )
        showInfo( "LIVETV-LIST: showing data"  )
        tmpl.Execute(w, formData)
    }
}

func liveTVAdminRemove(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "LIVETV-DELETE") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "livetv-delete", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: id = idmedialive
        id := getParam( r, "id" )

        if len( id ) == 0 {
            msginfo = "Invalid IDMEDIALIVE: " + id
        }else  {
            sqlite_medialive_delete( id )
            msginfo = "LIVETV Deleted: " + id
        }
        showInfo( "LIVETV-ADMIN-LIST-DELETE: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func liveTVAdminAdd(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "LIVETV-ADD") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "livetv-add", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: username, pass1, pass2, useradmin
        data := getParamPost( r, "data" )
        showInfo( "LIVETV-ADMIN-LIST-ADD: " + intToStr(len(data)) )
        //Extract data
        added, exist, errors, total := liveTvDataAdd( data )
        msginfo = "URLs Added (added/exist/errors/total): " + intToStr(added) + "/" + intToStr(exist) + "/" + intToStr(errors) + "/" + intToStr(total)
        showInfo( "LIVETV-ADMIN-LIST-ADD: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func liveTVAdminCheck(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "LIVETV-CHECK") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "livetv-check", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: id = idmedialive, clean = 1
        id := getParam( r, "id" )
        clean := getParam( r, "clean" )

        if len( id ) == 0 {
            msginfo = "Invalid IDMEDIALIVE: " + id
        } else {
            medialive := sqlite_getMediaLiveID(id)
            if len(medialive) > 0 {
                codec := ffprobeVideoCodec( medialive[0]["url"] )
                if codec != "NOCODEC" {
                    msginfo = "VALID IDMEDIALIVE: " + id + " => " + codec
                } else {
                    msginfo = "Invalid IDMEDIALIVE url: " + id
                    if len(clean) > 0 {
                        msginfo = "<br />DELETE IDMEDIALIVE: " + id
                        sqlite_medialive_delete( id )
                    }
                }
            } else {
                msginfo = "Not Exist IDMEDIALIVE: " + id
            }
        }
        showInfo( "LIVETV-ADMIN-LIST-CHECK: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

func liveTVAdminCheckAll(w http.ResponseWriter, r *http.Request) {
    var msginfo string
    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    
    if checkBaseActionAuthAdmin(w, r, "LIVETV-CHECKALL") == false {
        //Invalid IP or user or admin
    } else {
        sqlite_log_insert( "livetv-check", username, "", getUserURL( r ), getUserReferer( r ), getUserIP( r ) )
        //params post: clean = 1
        clean := getParam( r, "clean" )
        medialive := sqlite_getMediaLiveAll()
        total := len(medialive)
        valid := 0
        nerror := 0
        for _, ml := range medialive {
            //action := ""
            codec := ffprobeVideoCodec( ml["url"] )
            if codec != "NOCODEC" {
                valid++
                //action = "="
            } else {
                nerror++
                if len(clean) > 0 {
                    sqlite_medialive_delete( ml["idmedialive"] )
                }
                //action = "-"
            }
            //io.WriteString(w, action)
            //w.(http.Flusher).Flush()
        }
        msginfo = "Result (valid/error/total): " + intToStr( valid ) + "/" + intToStr( nerror ) + "/" + intToStr( total )
        if len(clean) > 0 {
            msginfo = "<br />REMOVED: " + intToStr(nerror)
        }
        showInfo( "LIVETV-ADMIN-LIST-CHECKALL: showing data"  )
        fmt.Fprintf(w, "%s", msginfo)
    }
}

//MENU

type menuListElement map[int]map[string]string //{ 0: { "title": "", "url": "", "admin" : false } },

type menuListTemp struct {
    Title   string
    Search  string
    Todos   menuListElement
    //Todos   map[int]map[string]string {}
}

func getMenu( w http.ResponseWriter, r *http.Request ) string {
    result := ""
    tmpl := template.Must(template.ParseFiles("html/menu.html"))

    //Base Menu
    listdata := menuListElement {
        0 : { "title" : "HOME", "url" : "/", "admin" : "" },
    }
    
    //Genres List
    for _, element := range G_GENRES {
        listdata[ len(listdata) ] = map[string]string{ "title" : element, "url" : "/list/?genre=" + element, "admin" : "" }
    }
    
    //Admin Menu
    if checkLoguedUserAdmin( w, r ) {
        listdata[ len(listdata) ] = map[string]string{ "title" : "Logs", "url" : "/logs-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "CronInfo", "url" : "/cron-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "Identify", "url" : "/media-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "MediaInfo", "url" : "/mediainfo-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "LiveTV", "url" : "/livetv-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "Played", "url" : "/played-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "Users", "url" : "/users-list/?", "admin" : "1" }
        listdata[ len(listdata) ] = map[string]string{ "title" : "IPs", "url" : "/ip-list/?", "admin" : "1" }
    }
    
    //logOut
    listdata[ len(listdata) ] = map[string]string{ "title" : "LogOut", "url" : "/logout/?", "admin" : "" }
    
    //Search data
    search := getParam( r, "search" )
    
    formData := menuListTemp{
        Title   :   "Menu",
        Todos   :   listdata,
        Search  :   search,
    }
    tpl := &bytes.Buffer{}
    tmpl.Execute(tpl, formData)
    result = tpl.String()
    
    return result
}

//TESBASE

func testBase( w http.ResponseWriter, r *http.Request ){
    //TEST CODE
    //cronImagesLink()
    //cronShortRun()
    /*
    data := `#EXTM3U

#EXTINF:-1 tvg-logo="http://127.0.0.1/" group-title="Without category", hbo2 dual (eng esp) www.m3ugratis.com
http://161.0.157.5/PLTV/88888888/224/3221227026/index.m3u8

#EXTINF:-1 tvg-logo="http://puu.sh/mb4wQ/6cd02167d4.png" group-title="CINE", Fox Cinema www.m3ugratis.com
http://161.0.157.7/PLTV/88888888/224/3221226793/03.m3u8

#EXTINF:-1 tvg-logo="http://photocall.tv/images/fighttime.png" group-title="Deportes", Fight Time
http://node01.openfutbol.es/SVoriginOperatorEdge/128761.smil/.m3u8

#EXTINF:-1 tvg-logo="https://i.imgur.com/x5jNF2N.png" group-title="Deportes", MLB
http://mlblive-akc.mlb.com/ls01/mlbam/mlb_network/NETWORK_LINEAR_1/master_wired.m3u8
`
    liveTvDataAdd(data)
    */
    //x, y := ffprobeSize( "http://212.104.160.156:1935/live/lebrijatv2/playlist2.m3u8?wowzasessionid=1758058924.m3u8" )
    x := ""
    y := ""
    z := ""
    info := "ENDED: " + x + " -- " + y + " -- " + z
    fmt.Fprintf(w, "DATA: %s\n", info)
}
