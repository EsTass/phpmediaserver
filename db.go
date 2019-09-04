package main

import (
    "fmt"
    //"log"
    //"os"
    "crypto/sha1"
    //"encoding/base64"
    //"net/http"
    
    "time"
    "strconv"
    
    "strings"
    
    "database/sql"
    //go get "github.com/mattn/go-sqlite3"
    _ "github.com/mattn/go-sqlite3"
)

//INIT

func init() {
	
}

//BASE

func sqlite_encodeStrignFile(s string) string {
    result := strings.Replace(s, "'", "''", -1)
    
    return result
}

func sqlite_encodeStrign(s string) string {
    result := strings.Replace(s, "'", "''", -1)
    result = strings.Replace(s, "%", " ", -1)
    result = strings.Replace(s, "?", " ", -1)
    result = strings.Replace(s, "*", " ", -1)
    result = strings.Replace(s, "_", " ", -1)
    result = strings.Replace(s, "&", " ", -1)
    result = strings.Replace(s, "=", " ", -1)
    result = strings.Replace(s, "-", " ", -1)
    result = strings.Replace(s, "\"", " ", -1)
    
    return result
}

//USERS

//username, password, admin

func sqlite_map_users( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var username, password, admin string
		err = rows.Scan(&username, &password, &admin)
		if err != nil {
			showInfoError(err)
		}
        result[ numrow ] = map[string]string{ "username": username, "password": password, "admin": admin }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getUsers() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM users"
    result = sqlite_map_users( sqlselect )
    
    return result
}

func sqlite_user_insert( user string, pass string, admin string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    hasher := sha1.New()
    bv := []byte(pass) 
    hasher.Write(bv)
    //sha := base64.URLEncoding.EncodeToString(hasher.Sum(nil))
    sha := fmt.Sprintf("%x", hasher.Sum(nil))
    
    stmt, err := db.Prepare("INSERT OR IGNORE INTO users(username, password, admin) VALUES(?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( user, sha, admin )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_user_update_pass( user string, pass string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    hasher := sha1.New()
    bv := []byte(pass) 
    hasher.Write(bv)
    //sha := base64.URLEncoding.EncodeToString(hasher.Sum(nil))
    sha := fmt.Sprintf("%x", hasher.Sum(nil))
    
    stmt, err := db.Prepare("UPDATE users SET password = ? WHERE username LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( sha, user )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_user_update_admin( user string, admin string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("UPDATE users SET admin = ? WHERE username LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( admin, user )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_user_delete( user string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM users WHERE username LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( user )
    if err != nil {
        showInfoError(err)
    }
}

func checkUser( user string, pass string ) bool {
    result := false
    
    userslist := sqlite_getUsers()
    hasher := sha1.New()
    bv := []byte(pass) 
    hasher.Write(bv)
    //sha := base64.URLEncoding.EncodeToString(hasher.Sum(nil))
    sha := fmt.Sprintf("%x", hasher.Sum(nil))
    showInfo( "USERCHECK: " + user + "/" + sha )
    //fmt.Println( userslist )
    for key, row := range userslist {
        //fmt.Println( row )
        _ = key
        //showInfo( fmt.Sprintf( "USERCHECK-ITEM: %v/%v", row["username"], row["password"] ) )
        if val, ok := row["username"]; ok {
            _ = val
            if row["username"] == user && row["password"] == sha {
                result = true
                break
            }
        }
    }
    
    return result
}

func checkUserValid( user string ) bool {
    result := false
    
    userslist := sqlite_getUsers()
    showInfo( "USERCHECK-EXIST: " + user )
    //fmt.Println( userslist )
    for key, row := range userslist {
        //fmt.Println( row )
        _ = key
        //showInfo( fmt.Sprintf( "USERCHECK-ITEM: %v/%v", row["username"], row["password"] ) )
        if val, ok := row["username"]; ok {
            _ = val
            if row["username"] == user {
                result = true
                break
            }
        }
    }
    
    return result
}

func checkUserAdmin( username string ) bool {
    result := false
    
    userslist := sqlite_getUsers()
    showInfo( "USERCHECK-ADMIN: " + username )
    //fmt.Println( userslist )
    for key, row := range userslist {
        //fmt.Println( row )
        _ = key
        //showInfo( fmt.Sprintf( "USERCHECK-ITEM: %v/%v", row["username"], row["password"] ) )
        if val, ok := row["username"]; ok {
            _ = val
            if row["username"] == username && row["admin"] == username {
                result = true
                break
            }
        }
    }
    
    return result
}

//SESSION

//date, session, user, ip

func sqlite_map_sessions( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var date, session, user, ip string
        err = rows.Scan(&date, &session, &user, &ip)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "date": date, "session": session, "user": user, "ip": ip }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_session_insert( session string, user string, ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT OR IGNORE into sessions(date, session, user, ip) values(?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    date := time.Now().Format("2006-01-02 15:04:05")
    _, err = stmt.Exec( date, session, user, ip )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_session_update( session string, user string, ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("UPDATE sessions SET date = ?, session = ?, user = ?, ip = ? WHERE session LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    date := time.Now().Format("2006-01-02 15:04:05")
    _, err = stmt.Exec( date, session, user, ip, session )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_getSessions() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM sessions ORDER BY date DESC LIMIT 1000"
    result = sqlite_map_sessions( sqlselect )
    
    return result
}

func sqlite_getSession( session string ) map[string]string {
    result := map[string]string {}
    if len( session ) > 0 {
        sqlselect := "SELECT * FROM sessions WHERE session LIKE '" + sqlite_encodeStrign(session) + "' ORDER BY date DESC LIMIT 1000"
        sessiondata := sqlite_map_sessions( sqlselect )
        if len( sessiondata ) > 0 {
            result = sessiondata[ 0 ]
        }
    }
    
    return result
}

func sqlite_getSessionUser( session string ) string {
    result := ""
    showInfo( "SESSION-CHECK-USER: " + session )
    if len( session ) > 0 {
        sqlselect := "SELECT * FROM sessions WHERE session LIKE '" + sqlite_encodeStrign(session) + "' ORDER BY date DESC LIMIT 1000"
        sessiondata := sqlite_map_sessions( sqlselect )
        if len( sessiondata ) > 0 {
            username := sessiondata[ 0 ][ "user" ]
            if checkUserValid( username ) {
                showInfo( "SESSION-CHECK-USER-OK: " + username )
                result = username
            }
        }
    }
    
    return result
}

func sqlite_sessions_clean(){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM sessions WHERE date < DATETIME(CURRENT_DATE, '-1 years')")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec()
    if err != nil {
        showInfoError(err)
    }
}

//LOG

//date, action, user, ip, description, url, referer

func sqlite_log_insert( action string, user string, description string, url string, referer string, ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT or IGNORE into logs(date, action, user, ip, description, url, referer) values(?, ?, ?, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //date := time.Now().String()
    //date := "datetime('now')"
    date := time.Now().Format("2006-01-02 15:04:05")
    //action := action
    //user := user
    //ip := ip
    //description := description
    //url := url
    //referer := referer
    _, err = stmt.Exec( date, action, user, ip, description, url, referer )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_map_logs( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var date, action, user, ip, description, url, referer string
        err = rows.Scan(&date, &action, &user, &ip, &description, &url, &referer)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "date": date, "action": action, "user": user, "ip": ip, "description": description, "url": url, "referer": referer }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getLogs() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM logs ORDER BY date DESC LIMIT 1000"
    result = sqlite_map_logs( sqlselect )
    
    return result
}

func sqlite_getLogsFilter(search string) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqle := ""
    if len(search) > 0 {
        sqle = " WHERE action LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR user LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR ip LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR description LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR url LIKE '%" + sqlite_encodeStrign(search) + "%' "
    }
    sqlselect := "SELECT * FROM logs " + sqle + " ORDER BY date DESC LIMIT 1000"
    result = sqlite_map_logs( sqlselect )
    
    return result
}

func sqlite_getLogsLogins( ip string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqle := ""
    sqle += " WHERE action LIKE 'login-KO' "
    sqle += " AND ip LIKE '" + sqlite_encodeStrign(ip) + "' "
    sqle += " AND logs.date > DATETIME(CURRENT_TIMESTAMP, '-5 minutes') "
    sqlselect := "SELECT * FROM logs " + sqle + " ORDER BY date DESC LIMIT 1000"
    result = sqlite_map_logs( sqlselect )
    
    return result
}

//MEDIA ELEMENTS

//idmedia, file, langs, subs, idmediainfo

func sqlite_map_media( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmedia, file, langs, subs, idmediainfo string
        err = rows.Scan(&idmedia, &file, &langs, &subs, &idmediainfo)
		if err != nil {
			showInfoError(err)
		}
        //format date
        result[ numrow ] = map[string]string{ "idmedia": idmedia, "file": file, "langs": langs, "subs": subs, "idmediainfo": idmediainfo }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getMediaAll() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media ORDER BY idmedia DESC"
    result = sqlite_map_media( sqlselect )
    
    return result
}

func sqlite_getMedia() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media ORDER BY idmedia DESC LIMIT 1000"
    result = sqlite_map_media( sqlselect )
    
    return result
}

func sqlite_getMediaWhithMediaInfoID( idmediainfo string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media WHERE idmediainfo = " + idmediainfo + " ORDER BY idmedia DESC LIMIT 1000"
    result = sqlite_map_media( sqlselect )
    
    return result
}

func sqlite_getMediaID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM media WHERE idmedia = " + id + " ORDER BY idmedia DESC LIMIT 1"
        result = sqlite_map_media( sqlselect )
    }
    
    return result
}

func sqlite_checkMediaID( id string ) bool {
    result := false
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM media WHERE idmedia = " + id + " ORDER BY idmedia DESC LIMIT 1"
        data := sqlite_map_media( sqlselect )
        if len(data) > 0 && data[ 0 ][ "idmedia" ] == id {
            result = true
        }
    }
    
    return result
}

func sqlite_checkMediaIDFile( id string ) bool {
    result := false
    
    media := sqlite_getMediaID( id )
    if len(media) > 0 && fileExist( media[0]["file"] ) {
        result = true
    } else {
        //IMPORTANT auto delete
        sqlite_media_delete( id )
    }
    
    return result
}

func sqlite_checkMediaFile( file string ) bool {
    result := false
    
    sqlselect := "SELECT * FROM media WHERE file LIKE '" + sqlite_encodeStrignFile(file) + "' ORDER BY idmedia DESC LIMIT 1"
    data := sqlite_map_media( sqlselect )
    if len(data) > 0 && data[ 0 ][ "file" ] == file {
        result = true
    }
    
    return result
}

func sqlite_media_insert( file string, idmediainfo string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT or IGNORE into media(idmedia, file, langs, subs, idmediainfo) values(NULL, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //idmedia := nil
    //file := ""
    langs := ""
    subs := ""
    //idmediainfo := "-1"
    //_, err = stmt.Exec( idmedia, file, langs, subs, idmediainfo )
    _, err = stmt.Exec( file, langs, subs, idmediainfo )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_media_delete( id string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM media WHERE idmedia = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( id )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_getMediaIdentBad( size int ) map[int]map[string]string {
    //Bad idents
    sqlselect := "SELECT * FROM media WHERE media.idmediainfo = -1 ORDER BY media.idmedia DESC LIMIT " + intToStr( size ) + ""
    showInfo( "MEDIA-IDENTBAD-SQL: " + sqlselect )
    return sqlite_map_media( sqlselect )
}

func sqlite_getMediaIdentNow( size int ) map[int]map[string]string {
    //Bad idents
    sqlselect := "SELECT * FROM media WHERE media.idmediainfo = 0 ORDER BY media.idmedia DESC LIMIT " + intToStr( size ) + ""
    showInfo( "MEDIA-IDENTNOW-SQL: " + sqlselect )
    return sqlite_map_media( sqlselect )
}

func sqlite_media_update_idmediainfo( idmedia string, idmediainfo string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("UPDATE media SET idmediainfo = ? WHERE idmedia = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( idmediainfo, idmedia )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_media_clean_idmediainfo( idmediainfo string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("UPDATE media SET idmediainfo = '-1' WHERE idmediainfo = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( idmediainfo )
    if err != nil {
        showInfoError(err)
    }
}

//MEDIAINFO ELEMENTS

//idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode

func sqlite_map_mediainfo( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode string
        err = rows.Scan(&idmediainfo, &dateadded, &title, &sorttitle, &season, &episode, &year, &rating, &votes, &mpaa, &tagline, &runtime, &plot, &height, &width, &codec, &imdbid, &imdb, &tmdbid, &tmdb, &tvdbid, &tvdb, &genre, &actor, &audio, &subtitle, &titleepisode)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, dateadded)
        dateadded = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "idmediainfo": idmediainfo, "dateadded": dateadded, "title": title, "sorttitle": sorttitle, "season": season, "episode": episode, "year": year, "rating": rating, "votes": votes, "mpaa": mpaa, "tagline": tagline, "runtime": runtime, "plot": plot, "height": height, "width": width, "codec": codec, "imdbid": imdbid, "imdb": imdb, "tmdbid": tmdbid, "tmdb": tmdb, "tvdbid": tvdbid, "tvdb": tvdb, "genre": genre, "actor": actor, "audio": audio, "subtitle": subtitle, "titleepisode": titleepisode }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getMediaInfoAll() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM mediainfo ORDER BY idmediainfo DESC"
    result = sqlite_map_mediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaInfo( limit int ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM mediainfo ORDER BY idmediainfo DESC LIMIT " + intToStr( limit )
    result = sqlite_map_mediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaInfoFilter( limit int, search string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    //last Idents
    sqle := ""
    if len(search) > 0 {
        sqle = " WHERE title LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR plot LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR tagline LIKE '%" + sqlite_encodeStrign(search) + "%' "
    }
    sqlselect := "SELECT * FROM mediainfo " + sqle + " ORDER BY idmediainfo DESC LIMIT " + intToStr( limit )
    result = sqlite_map_mediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaInfoID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM mediainfo WHERE idmediainfo = " + id + " ORDER BY idmediainfo DESC LIMIT 1000"
        result = sqlite_map_mediainfo( sqlselect )
    }
    
    return result
}

func sqlite_checkMediaInfoID( id string ) bool {
    result := false
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM mediainfo WHERE idmediainfo = " + id + " ORDER BY idmediainfo DESC LIMIT 1000"
        data := sqlite_map_mediainfo( sqlselect )
        if len(data) > 0 && data[ 0 ][ "idmediainfo" ] == id {
            result = true
        }
    }
    
    return result
}

func sqlite_getMediaInfoIMDB( imdbid string ) map[int]map[string]string {
    var result map[int]map[string]string
    
    if len(imdbid) > 0 {
        sqlselect := "SELECT * FROM mediainfo WHERE imdbid LIKE '" + sqlite_encodeStrign(imdbid) + "' OR imdb LIKE '%" + sqlite_encodeStrign(imdbid) + "%' ORDER BY idmediainfo DESC LIMIT 1000"
        result = sqlite_map_mediainfo( sqlselect )
    }
    
    return result
}

func sqlite_getMediaInfoTitle( title string ) map[int]map[string]string {
    var result map[int]map[string]string
    
    if len(title) > 0 {
        sqlselect := "SELECT * FROM mediainfo WHERE title LIKE '" + sqlite_encodeStrign(title) + "' ORDER BY idmediainfo DESC LIMIT 1000"
        result = sqlite_map_mediainfo( sqlselect )
    }
    
    return result
}

func sqlite_getMediaInfoRandom() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM mediainfo ORDER BY RANDOM() LIMIT 1"
    result = sqlite_map_mediainfo( sqlselect )
    
    return result
}

func sqlite_checkMediaInfoExist( title string, year string, season string, episode string ) int {
    result := 0
    
    sqlselect := "SELECT * FROM mediainfo WHERE title LIKE '" + sqlite_encodeStrign(title) + "' AND year = " + year + ""
    if len(season) > 0 {
        sqlselect += " AND season = " + season + " "
    } else {
        sqlselect += " AND ( season IS NULL OR season = 0 OR season LIKE \"\" ) "
    }
    if len(episode) > 0 {
        sqlselect += " AND episode = " + episode + " "
    } else {
        sqlselect += " AND ( episode IS NULL OR episode = 0 OR episode LIKE '' ) "
    }
    sqlselect += " ORDER BY idmediainfo DESC LIMIT 1"
    data := sqlite_map_mediainfo( sqlselect )
    if len(data) > 0 && data[ 0 ][ "idmediainfo" ] != "" {
        result = strToInt( data[ 0 ][ "idmediainfo" ] )
    }
    
    return result
}

func sqlite_mediainfo_insert( fields map[string]string ) int {
    result := 0
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT or IGNORE into mediainfo(idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode) values(NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //idmediainfo, 
    var dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode string
    //idmediainfo     = fields["idmediainfo"]
    dateadded       = time.Now().Format("2006-01-02 15:04:05")
    title           = fields["title"]
    sorttitle       = fields["sorttitle"]
    season          = fields["season"]
    episode         = fields["episode"]
    year            = fields["year"]
    rating          = fields["rating"]
    votes           = fields["votes"]
    mpaa            = fields["mpaa"]
    tagline         = fields["tagline"]
    runtime         = fields["runtime"]
    plot            = fields["plot"]
    height          = fields["height"]
    width           = fields["width"]
    codec           = fields["codec"]
    imdbid          = fields["imdbid"]
    imdb            = fields["imdb"]
    tmdbid          = fields["tmdbid"]
    tmdb            = fields["tmdb"]
    tvdbid          = fields["tvdbid"]
    tvdb            = fields["tvdb"]
    genre           = fields["genre"]
    actor           = fields["actor"]
    audio           = fields["audio"]
    subtitle        = fields["subtitle"]
    titleepisode    = fields["titleepisode"]
    rr, err := stmt.Exec( dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode )
    if err != nil {
        showInfoError(err)
    } else {
        id, err := rr.LastInsertId()
        if err != nil {
            showInfoError(err)
        } else {
            result = int(id)
        }
    }
    
    return result
}

func sqlite_mediainfo_copy( idmediainfo string, season string, episode string ) int {
    result := 0
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    if len(season) == 0 {
        season = ""
    }
    if len(episode) == 0 {
        episode = ""
    }
    
    stmt, err := db.Prepare("INSERT INTO mediainfo(idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode ) SELECT NULL, dateadded, title, sorttitle, ?, ?, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, ? FROM mediainfo WHERE idmediainfo = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    rr, err := stmt.Exec( season, episode, "", idmediainfo )
    if err != nil {
        showInfoError(err)
    } else {
        id, err := rr.LastInsertId()
        if err != nil {
            showInfoError(err)
        } else {
            result = int(id)
            //copy images
            copyImgsMediaInfo( idmediainfo, intToStr(result) )
        }
    }
    
    return result
}

func sqlite_mediainfo_delete( id string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM mediainfo WHERE idmediainfo = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( id )
    if err != nil {
        showInfoError(err)
    } else {
        //Delete images
        deleteImgsMediaInfo( id )
        //unnasing idmedia
        sqlite_media_clean_idmediainfo( id )
    }
}

//MEDIA + MEDIAINFO ELEMENTS

//idmedia, file, langs, subs, idmediainfo
//idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode

func sqlite_map_mediamediainfo( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
        //var idmediainfo sql.NullString
		var idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode string
		var idmedia, file, langs, subs string
        err = rows.Scan(&idmedia, &file, &langs, &subs, &idmediainfo, &idmediainfo, &dateadded, &title, &sorttitle, &season, &episode, &year, &rating, &votes, &mpaa, &tagline, &runtime, &plot, &height, &width, &codec, &imdbid, &imdb, &tmdbid, &tmdb, &tvdbid, &tvdb, &genre, &actor, &audio, &subtitle, &titleepisode)
		if err != nil {
			showInfoError(err)
		}
        //format nullstring
        result[ numrow ] = map[string]string{ "idmedia": idmedia, "file": file, "langs": langs, "subs": subs, "idmediainfo": idmediainfo, "dateadded": dateadded, "title": title, "sorttitle": sorttitle, "season": season, "episode": episode, "year": year, "rating": rating, "votes": votes, "mpaa": mpaa, "tagline": tagline, "runtime": runtime, "plot": plot, "height": height, "width": width, "codec": codec, "imdbid": imdbid, "imdb": imdb, "tmdbid": tmdbid, "tmdb": tmdb, "tvdbid": tvdbid, "tvdb": tvdb, "genre": genre, "actor": actor, "audio": audio, "subtitle": subtitle, "titleepisode": titleepisode }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getMediaMediaInfo( size int, page int ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL ORDER BY idmedia DESC LIMIT " + intToStr( size ) + "  OFFSET " + intToStr( ( size * page ) )
    showInfo( "MEDIAINFOLIST-SQL: " + sqlselect )
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaMediaInfoFilter( size int, page int, genre string, actor string, search string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := ""
    subsqlselect := ""
    if len(genre) > 0 {
        pos := stringInSlicePos( genre, G_GENRES )
        if pos > -1 {
            if len(G_GENRES_ADAPT) >= pos && len(G_GENRES_ADAPT[pos]) > 0 {
                subsqlselect = " AND ( genre LIKE '%" + sqlite_encodeStrign(genre) + "%' OR genre LIKE '%" + sqlite_encodeStrign(G_GENRES_ADAPT[pos]) + "%' ) "
            }
        }
        if len(subsqlselect) == 0 {
            subsqlselect = " AND genre LIKE '%" + sqlite_encodeStrign(genre) + "%' "
        }
    }
    subsqlselect2 := ""
    if len(actor) > 0 {
        subsqlselect2 = " AND actor LIKE '%" + sqlite_encodeStrign(actor) + "%' "
    }
    subsqlselect3 := ""
    if len(search) > 0 {
        subsqlselect3 = " AND ( title LIKE '%" + sqlite_encodeStrign(search) + "%' OR plot LIKE '%" + sqlite_encodeStrign(search) + "%' OR tagline LIKE '%" + sqlite_encodeStrign(search) + "%' )"
    }
    sqlselect = "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL " + subsqlselect + " " + subsqlselect2 + " " + subsqlselect3 + " ORDER BY idmedia DESC LIMIT " + intToStr( size ) + "  OFFSET " + intToStr( ( size * page ) )
    showInfo( "MEDIAINFOGENRE-SQL: " + sqlselect )
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaMediaInfoIdent( size int, search string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    var sqlselect, sqle string
    
    //Bad idents
    mapsJoinMedia( result, sqlite_getMediaIdentBad( size ) )
    
    //Not ident
    mapsJoinMedia( result, sqlite_getMediaIdentNow( size ) )
    
    //Bad Idents
    sqlselect = "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL AND mediainfo.season = 1 AND ( mediainfo.episode = 0 OR mediainfo.episode = \"\" ) ORDER BY media.idmediainfo DESC LIMIT " + intToStr( size ) + ""
    showInfo( "MEDIAINFOIDENT-SQL: " + sqlselect )
    mapsJoinMedia( result, sqlite_map_mediamediainfo( sqlselect ) )
    
    //last Idents
    sqle = ""
    if len(search) > 0 {
        sqle = " AND ( title LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR plot LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR tagline LIKE '%" + sqlite_encodeStrign(search) + "%' "
        sqle += " OR file LIKE '%" + sqlite_encodeStrign(search) + "%' ) "
    }
    sqlselect = "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL " + sqle + " ORDER BY idmedia DESC LIMIT " + intToStr( size ) + ""
    showInfo( "MEDIAINFOIDENT-SQL: " + sqlselect )
    mapsJoinMedia( result, sqlite_map_mediamediainfo( sqlselect ) )
    
    return result
}

func sqlite_getMediaMediaInfoID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE idmedia = " + id + " AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL ORDER BY idmediainfo DESC LIMIT 100"
        result = sqlite_map_mediamediainfo( sqlselect )
    }
    
    return result
}

func sqlite_getMediaInfoMediaInfoID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE mediainfo.idmediainfo = " + id + " AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL ORDER BY idmediainfo DESC LIMIT 100"
        result = sqlite_map_mediamediainfo( sqlselect )
    }
    
    return result
}

func sqlite_getMediaInfoMediaInfoIDLast( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE mediainfo.idmediainfo = " + id + " AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL ORDER BY idmedia DESC LIMIT 1"
        result = sqlite_map_mediamediainfo( sqlselect )
    }
    
    return result
}

func sqlite_getMediaMediaInfoRelatedID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    mi := sqlite_getMediaInfoID( id )
    if len(mi) > 0 {
        genres := strings.Split(mi[0]["genre"], ",")
        result = sqlite_getMediaMediaInfoRelated(id, genres)
    }
    
    return result
}

func sqlite_getMediaMediaInfoRelated( id string, genre []string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    if len( genre ) > 0 {
        esql := ""
        for _, g := range genre {
            pos := stringInSlicePos( g, G_GENRES )
            if pos > -1 && len(G_GENRES_ADAPT) >= pos && len(G_GENRES_ADAPT[pos]) > 0 {
                esql += " ( mediainfo.genre LIKE '%" + sqlite_encodeStrign(strings.TrimSpace(g)) + "%' OR mediainfo.genre LIKE '%" + sqlite_encodeStrign(G_GENRES_ADAPT[pos]) + "%' ) AND "
            } else {
                esql += " mediainfo.genre LIKE '%" + sqlite_encodeStrign(strings.TrimSpace(g)) + "%' AND "
            }
        }
        if _, err := strconv.Atoi(id); err == nil {
            sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE " + esql + " media.idmediainfo != " + id + "  AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL GROUP BY mediainfo.idmediainfo ORDER BY idmediainfo DESC LIMIT " + intToStr( G_LISTSIZE_MIN )
            result = sqlite_map_mediamediainfo( sqlselect )
            genre2 := genre
            for len( result ) < G_LISTSIZE_MIN && len( genre2 ) > 1 {
                genre2 = genre2[:len(genre2)-1]
                //result2 := sqlite_getMediaMediaInfoRelated( id, genre2 )
                esql := ""
                for _, g := range genre2 {
                    pos := stringInSlicePos( g, G_GENRES )
                    if pos > -1 && len(G_GENRES_ADAPT) >= pos && len(G_GENRES_ADAPT[pos]) > 0 {
                        esql += " ( mediainfo.genre LIKE '%" + sqlite_encodeStrign(strings.TrimSpace(g)) + "%' OR mediainfo.genre LIKE '%" + sqlite_encodeStrign(G_GENRES_ADAPT[pos]) + "%' ) AND "
                    } else {
                        esql += " mediainfo.genre LIKE '%" + sqlite_encodeStrign(strings.TrimSpace(g)) + "%' AND "
                    }
                }
                sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE " + esql + " media.idmediainfo != " + id + "  AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL GROUP BY mediainfo.idmediainfo ORDER BY idmediainfo DESC LIMIT " + intToStr( ( G_LISTSIZE_MIN - len( result ) ) )
                result2 := sqlite_map_mediamediainfo( sqlselect )
                if len( result2 ) > 0 {
                    mapsJoinMedia( result, result2 )
                }
            }
        }
    }
    
    return result
}

func sqlite_getMediaInfoMediaInfoChapters( title string, year string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE mediainfo.title LIKE '" + sqlite_encodeStrign(title) + "' AND mediainfo.year LIKE '" + sqlite_encodeStrign(year) + "' AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL GROUP BY mediainfo.idmediainfo ORDER BY season DESC, episode ASC LIMIT 1000"
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaInfoMediaInfoChaptersNext( title string, year string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE mediainfo.title LIKE '" + sqlite_encodeStrign(title) + "' AND mediainfo.year LIKE '" + sqlite_encodeStrign(year) + "' AND media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL GROUP BY mediainfo.idmediainfo ORDER BY season, episode ASC LIMIT 1000"
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

//MEDIAINFO FRONTAL LISTS

func sqlite_getMediaMediaInfo_Premiere( size int ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL AND mediainfo.sorttitle < DATETIME(CURRENT_DATE, '-3 months') GROUP BY mediainfo.title ORDER BY mediainfo.sorttitle DESC LIMIT " + intToStr( size )
    showInfo( "MEDIAINFOLIST-SQL: " + sqlselect )
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaMediaInfo_PremiereBR( size int ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL AND mediainfo.sorttitle < DATETIME(CURRENT_DATE, '-3 months') GROUP BY mediainfo.title ORDER BY mediainfo.sorttitle DESC LIMIT " + intToStr( size )
    showInfo( "MEDIAINFOLIST-SQL: " + sqlselect )
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

func sqlite_getMediaMediaInfo_PremiereSeries( size int ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM media LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 AND mediainfo.idmediainfo IS NOT NULL AND ( mediainfo.sorttitle < DATETIME(CURRENT_DATE, '-3 months')  OR ( date( date( mediainfo.sorttitle, printf( '+%d years', mediainfo.season ) ), printf( '+%d days', ( mediainfo.episode * 7 ) ) ) ) > DATETIME(CURRENT_DATE, '-3 months') ) AND mediainfo.episode != '' AND mediainfo.season != '' GROUP BY mediainfo.title ORDER BY mediainfo.sorttitle DESC, mediainfo.season DESC, mediainfo.episode DESC LIMIT " + intToStr( size )
    showInfo( "MEDIAINFOLIST-SQL: " + sqlselect )
    result = sqlite_map_mediamediainfo( sqlselect )
    
    return result
}

//PLAYED MEDIA ELEMENTS

//idmedia, user, date, now, max

func sqlite_map_played( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmedia, user, date, now, max string
        err = rows.Scan(&idmedia, &user, &date, &now, &max)
		if err != nil {
			showInfoError(err)
		}
        //format date
        result[ numrow ] = map[string]string{ "idmedia": idmedia, "user": user, "date": date, "now": now, "max": max }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_map_playedext( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmedia, user, date, now, max string
        var idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode string
		var file, langs, subs string
        err = rows.Scan(&idmedia, &user, &date, &now, &max, &idmedia, &file, &langs, &subs, &idmediainfo, &idmediainfo, &dateadded, &title, &sorttitle, &season, &episode, &year, &rating, &votes, &mpaa, &tagline, &runtime, &plot, &height, &width, &codec, &imdbid, &imdb, &tmdbid, &tmdb, &tvdbid, &tvdb, &genre, &actor, &audio, &subtitle, &titleepisode)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "idmedia": idmedia, "user": user, "date": date, "now": now, "max": max, "file": file, "langs": langs, "subs": subs, "idmediainfo": idmediainfo, "dateadded": dateadded, "title": title, "sorttitle": sorttitle, "season": season, "episode": episode, "year": year, "rating": rating, "votes": votes, "mpaa": mpaa, "tagline": tagline, "runtime": runtime, "plot": plot, "height": height, "width": width, "codec": codec, "imdbid": imdbid, "imdb": imdb, "tmdbid": tmdbid, "tmdb": tmdb, "tvdbid": tvdbid, "tvdb": tvdb, "genre": genre, "actor": actor, "audio": audio, "subtitle": subtitle, "titleepisode": titleepisode }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getPlayed() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM played ORDER BY date DESC LIMIT 100"
    result = sqlite_map_played( sqlselect )
    
    return result
}

func sqlite_getPlayedMediaInfo() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM played LEFT JOIN media ON played.idmedia = media.idmedia LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 ORDER BY date DESC LIMIT 100"
    result = sqlite_map_playedext( sqlselect )
    
    return result
}

func sqlite_getPlayedMediaInfoLimit( size string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM played LEFT JOIN media ON played.idmedia = media.idmedia LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 ORDER BY date DESC LIMIT " + size
    result = sqlite_map_playedext( sqlselect )
    
    return result
}

func sqlite_getPlayedID( idmedia string, user string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(idmedia); err == nil {
        sqlselect := "SELECT * FROM played WHERE idmedia = " + idmedia + " AND user LIKE '" + sqlite_encodeStrign(user) + "' ORDER BY date DESC LIMIT 100"
        result = sqlite_map_played( sqlselect )
    }
    
    return result
}

func sqlite_getPlayedIDMediaInfo( idmedia string, user string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(idmedia); err == nil {
        sqlselect := "SELECT * FROM played LEFT JOIN media ON played.idmedia = media.idmedia LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE played.idmedia = " + idmedia + " AND played.user LIKE '" + sqlite_encodeStrign(user) + "' AND media.idmediainfo > 0 ORDER BY played.date DESC LIMIT 100"
        result = sqlite_map_playedext( sqlselect )
    }
    
    return result
}

func sqlite_played_insert( idmedia string, user string, now string, max string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT OR REPLACE INTO played(idmedia, user, date, now, max) values(?, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //date := time.Now().String()
    //date := "datetime('now')"
    date := time.Now().Format("2006-01-02 15:04:05")
    _, err = stmt.Exec( idmedia, user, date, now, max )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_played_delete( idmedia string, user string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM played WHERE idmedia = ? AND user = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( idmedia, user )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_played_clean(){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM played WHERE date < DATETIME(CURRENT_DATE, '-1 years')")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec()
    if err != nil {
        showInfoError(err)
    }
}

//PLAYING MEDIA ELEMENTS

//idplaying, user, idmedia, date, mode, pid

func sqlite_map_playing( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idplaying, user, idmedia, date, mode, pid string
        err = rows.Scan(&idplaying, &user, &idmedia, &date, &mode, &pid)
		if err != nil {
			showInfoError(err)
		}
        //format date
        result[ numrow ] = map[string]string{ "idplaying": idplaying, "user": user, "idmedia": idmedia, "date": date, "mode": mode, "pid": pid }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_map_playingext( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idplaying, user, idmedia, date, mode, pid string
		var idmediainfo, dateadded, title, sorttitle, season, episode, year, rating, votes, mpaa, tagline, runtime, plot, height, width, codec, imdbid, imdb, tmdbid, tmdb, tvdbid, tvdb, genre, actor, audio, subtitle, titleepisode string
		var file, langs, subs string
        err = rows.Scan(&idplaying, &user, &idmedia, &date, &mode, &pid, &idmedia, &file, &langs, &subs, &idmediainfo, &idmediainfo, &dateadded, &title, &sorttitle, &season, &episode, &year, &rating, &votes, &mpaa, &tagline, &runtime, &plot, &height, &width, &codec, &imdbid, &imdb, &tmdbid, &tmdb, &tvdbid, &tvdb, &genre, &actor, &audio, &subtitle, &titleepisode)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "idplaying": idplaying, "user": user, "idmedia": idmedia, "date": date, "mode": mode, "pid": pid, "file": file, "langs": langs, "subs": subs, "idmediainfo": idmediainfo, "dateadded": dateadded, "title": title, "sorttitle": sorttitle, "season": season, "episode": episode, "year": year, "rating": rating, "votes": votes, "mpaa": mpaa, "tagline": tagline, "runtime": runtime, "plot": plot, "height": height, "width": width, "codec": codec, "imdbid": imdbid, "imdb": imdb, "tmdbid": tmdbid, "tmdb": tmdb, "tvdbid": tvdbid, "tvdb": tvdb, "genre": genre, "actor": actor, "audio": audio, "subtitle": subtitle, "titleepisode": titleepisode }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getPlaying() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM playing ORDER BY date DESC LIMIT 100"
    result = sqlite_map_playing( sqlselect )
    
    return result
}

func sqlite_getPlayingMediaInfo() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM playing LEFT JOIN media ON playing.idmedia = media.idmedia LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE media.idmediainfo > 0 ORDER BY date DESC LIMIT 100"
    result = sqlite_map_playingext( sqlselect )
    
    return result
}

func sqlite_getPlayingFilter( idmedia string, user string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(idmedia); err == nil {
        sqlselect := "SELECT * FROM playing WHERE idmedia = " + idmedia + " AND user LIKE '" + sqlite_encodeStrign(user) + "' ORDER BY date DESC LIMIT 100"
        result = sqlite_map_playing( sqlselect )
    }
    
    return result
}

func sqlite_getPlayingIDMediaInfo( idmedia string, user string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(idmedia); err == nil {
        sqlselect := "SELECT * FROM playing LEFT JOIN media ON playing.idmedia = media.idmedia LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo WHERE played.idmedia = " + idmedia + " AND played.user LIKE '" + sqlite_encodeStrign(user) + "' AND media.idmediainfo > 0 ORDER BY played.date DESC LIMIT 100"
        result = sqlite_map_playingext( sqlselect )
    }
    
    return result
}

func sqlite_playing_insert( idmedia string, user string, mode string, pid string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT OR REPLACE INTO playing(idplaying, user, idmedia, date, mode, pid) values(NULL, ?, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //date := time.Now().String()
    //date := "datetime('now')"
    date := time.Now().Format("2006-01-02 15:04:05")
    _, err = stmt.Exec( user, idmedia, date, mode, pid )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_playing_delete( idmedia string, user string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM playing WHERE idmedia = ? AND user = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( idmedia, user )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_playing_clean(){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM playing WHERE date < DATETIME(CURRENT_TIMESTAMP, '-200 minutes')")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec()
    if err != nil {
        showInfoError(err)
    }
}

//WHITELIST IP

//ip, date

func sqlite_map_whitelist( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var ip, date string
		err = rows.Scan(&ip, &date)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "ip": ip, "date": date }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getWhitelist() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM whitelist"
    result = sqlite_map_whitelist( sqlselect )
    
    return result
}

func sqlite_whitelist_insert( ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT OR IGNORE INTO whitelist(ip, date) VALUES(?, ?)")
	if err != nil {
		showInfoError(err)
	}
    //add 1 year
    date := time.Now().AddDate(1, 0, 0).Format("2006-01-02 15:04:05")
	defer stmt.Close()
    _, err = stmt.Exec( ip, date )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_whitelist_delete( ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM whitelist WHERE ip LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( ip )
    if err != nil {
        showInfoError(err)
    }
}

func checkWhitelist( ip string ) bool {
    result := false
    
    iplist := sqlite_getWhitelist()
    showInfo( "WHITELIST-EXIST: " + ip )
    //fmt.Println( userslist )
    for key, row := range iplist {
        //fmt.Println( row )
        _ = key
        //showInfo( fmt.Sprintf( "WHITELIST-ITEM: %v", row["ip"] ) )
        if val, ok := row["ip"]; ok {
            _ = val
            if row["ip"] == ip {
                result = true
                break
            }
        }
    }
    
    return result
}

func sqlite_whitelist_clean(){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM whitelist WHERE date < DATETIME(CURRENT_DATE, '-1 years')")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec()
    if err != nil {
        showInfoError(err)
    }
}

//BANS IP

//ip, date

func sqlite_map_bans( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var ip, date string
		err = rows.Scan(&ip, &date)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "ip": ip, "date": date }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getBans() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM bans"
    result = sqlite_map_bans( sqlselect )
    
    return result
}

func sqlite_getBansActived() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM bans WHERE date > NOW()"
    result = sqlite_map_bans( sqlselect )
    
    return result
}

func sqlite_bans_insert( ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT OR IGNORE INTO bans(ip, date) VALUES(?, ?)")
	if err != nil {
		showInfoError(err)
	}
    //add 1 year
    date := time.Now().AddDate(1, 0, 0).Format("2006-01-02 15:04:05")
	defer stmt.Close()
    _, err = stmt.Exec( ip, date )
    if err != nil {
        showInfoError(err)
    }
}

func sqlite_bans_delete( ip string ){
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM whitelist WHERE ip LIKE ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( ip )
    if err != nil {
        showInfoError(err)
    }
}

func checkBans( ip string ) bool {
    result := false
    
    iplist := sqlite_getBansActived()
    showInfo( "BANS-EXIST: " + ip )
    //fmt.Println( userslist )
    for key, row := range iplist {
        //fmt.Println( row )
        _ = key
        //showInfo( fmt.Sprintf( "BANS-ITEM: %v", row["ip"] ) )
        if val, ok := row["ip"]; ok {
            _ = val
            if row["ip"] == ip {
                result = true
                break
            }
        }
    }
    
    return result
}

func sqlite_bans_clean(){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM bans WHERE date < DATETIME(CURRENT_DATE, '-1 years')")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec()
    if err != nil {
        showInfoError(err)
    }
}

//MEDIALIVE ELEMENTS

//idmedialive, title, url, poster, date

func sqlite_map_medialive( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmedialive, title, url, poster, date string
        err = rows.Scan(&idmedialive, &title, &url, &poster, &date)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "idmedialive": idmedialive, "title": title, "url": url, "poster": poster, "date": date }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getMediaLiveAll() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM medialive ORDER BY idmedialive DESC"
    result = sqlite_map_medialive( sqlselect )
    
    return result
}

func sqlite_getMediaLive( size string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM medialive ORDER BY idmedialive DESC LIMIT " + size
    result = sqlite_map_medialive( sqlselect )
    
    return result
}

func sqlite_getMediaLiveID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM medialive WHERE idmedialive = " + id + " ORDER BY idmedialive DESC LIMIT 1"
        result = sqlite_map_medialive( sqlselect )
    }
    
    return result
}

func sqlite_checkMediaLiveID( id string ) bool {
    result := false
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM medialive WHERE idmedialive = " + id + " ORDER BY idmedialive DESC LIMIT 1"
        data := sqlite_map_medialive( sqlselect )
        if len(data) > 0 && data[ 0 ][ "idmedialive" ] == id {
            result = true
        }
    }
    
    return result
}

func sqlite_checkMediaLiveURLURL( url string ) bool {
    result := false
    
    sqlselect := "SELECT * FROM medialive WHERE url LIKE '" + sqlite_encodeStrignFile(url) + "' ORDER BY idmedialive DESC LIMIT 1"
    data := sqlite_map_medialive( sqlselect )
    if len(data) > 0 && data[ 0 ][ "url" ] == url {
        result = true
    }
    
    return result
}

func sqlite_medialive_insert( title string, url string, poster string ) int {
    result := 0
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT or IGNORE into medialive(idmedialive, title, url, poster, date) values(NULL, ?, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //idmedia := nil
    date := time.Now().Format("2006-01-02 15:04:05")
    rr, err := stmt.Exec( title, url, poster, date )
    if err != nil {
        showInfoError(err)
    } else {
        id, err := rr.LastInsertId()
        if err != nil {
            showInfoError(err)
        } else {
            result = int(id)
        }
    }
    
    return result
}

func sqlite_medialive_delete( id string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM medialive WHERE idmedialive = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( id )
    if err != nil {
        showInfoError(err)
    } else {
        //remove files
        imgfile := pathJoin(G_IMAGES_FOLDER, id + ".livetv")
        if fileExist( imgfile ) {
            fileRemove(imgfile)
        }
    }
}

//MEDIALIVEURLS ELEMENTS

//idmedialiveurls, title, url, date

func sqlite_map_medialiveurls( sqlselect string ) map[int]map[string]string {
    // the map key is the field name
    result := map[int]map[string]string {}
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
	rows, err := db.Query( sqlselect )
	if err != nil {
		showInfoError(err)
	}
	defer rows.Close()
    numrow := 0
	for rows.Next() {
		var idmedialiveurls, title, url, date string
        err = rows.Scan(&idmedialiveurls, &title, &url, &date)
		if err != nil {
			showInfoError(err)
		}
        //format date
        layout := "2006-01-02T15:04:05Z"
        t, _ := time.Parse(layout, date)
        date = t.Format( "2006-01-02 15:04:05" )
        result[ numrow ] = map[string]string{ "idmedialiveurls": idmedialiveurls, "title": title, "url": url, "date": date }
        numrow++
	}
	err = rows.Err()
	if err != nil {
		showInfoError(err)
	}
    
    return result
}

func sqlite_getMediaLiveURLAll() map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM medialiveurls ORDER BY idmedialiveurls DESC"
    result = sqlite_map_medialiveurls( sqlselect )
    
    return result
}

func sqlite_getMediaLiveURL( size string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    sqlselect := "SELECT * FROM medialiveurls ORDER BY idmedialiveurls DESC LIMIT " + size
    result = sqlite_map_medialiveurls( sqlselect )
    
    return result
}

func sqlite_getMediaLiveURLID( id string ) map[int]map[string]string {
    result := map[int]map[string]string {}
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM medialiveurls WHERE idmedialiveurls = " + id + " ORDER BY idmedialiveurls DESC LIMIT 1"
        result = sqlite_map_medialiveurls( sqlselect )
    }
    
    return result
}

func sqlite_checkMediaLiveURLID( id string ) bool {
    result := false
    
    if _, err := strconv.Atoi(id); err == nil {
        sqlselect := "SELECT * FROM medialiveurls WHERE idmedialiveurls = " + id + " ORDER BY idmedialiveurls DESC LIMIT 1"
        data := sqlite_map_medialiveurls( sqlselect )
        if len(data) > 0 && data[ 0 ][ "idmedialiveurls" ] == id {
            result = true
        }
    }
    
    return result
}

func sqlite_medialiveurl_insert( title string, url string ) int {
    result := 0
    
    
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("INSERT or IGNORE into medialiveurls(idmedialiveurls, title, url, date) values(NULL, ?, ?, ?)")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    //idmedia := nil
    date := time.Now().Format("2006-01-02 15:04:05")
    //_, err = stmt.Exec( idmedia, file, langs, subs, idmediainfo )
    rr, err := stmt.Exec( title, url, date )
    if err != nil {
        showInfoError(err)
    } else {
        id, err := rr.LastInsertId()
        if err != nil {
            showInfoError(err)
        } else {
            result = int(id)
        }
    }
    
    return result
}

func sqlite_medialiveurl_delete( id string ){
	db, err := sql.Open( "sqlite3", G_SQLFILE )
	if err != nil {
		showInfoError(err)
	}
	defer db.Close()
    
    stmt, err := db.Prepare("DELETE FROM medialiveurls WHERE idmedialiveurls = ?")
	if err != nil {
		showInfoError(err)
	}
	defer stmt.Close()
    _, err = stmt.Exec( id )
    if err != nil {
        showInfoError(err)
    }
}
