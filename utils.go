package main


import (
    "fmt"
    "time"
    "math/rand"
    
    "net/http"
    "net/url"
    
    "io"
	"os"
	"strconv"
    "strings"
    "path/filepath"
    "bufio"
    "sort"
    "io/ioutil"
    "log"
    "regexp"
    "crypto/md5"
)

//DEBUG INFO

func showInfo( info string ) {
    if G_DEBUG {
        fmt.Println( time.Now().Format("2006-01-02 15:04:05") + " -- " + info )
    }
}

func showInfoError( err error ) {
    //log.Fatal
    fmt.Println( "FATAL :: " + time.Now().Format("2006-01-02 15:04:05") + " -- ERROR -- " + err.Error() )
}

//PARAMS GET

func getParam( r *http.Request, ident string ) string {
    result := r.URL.Query().Get(ident)
    return result
}

//PARAMS POST

func getParamPost( r *http.Request, ident string ) string {
    result := r.FormValue(ident)
    return result
}

//CHECK LOGGED USER

func checkLoguedUser(w http.ResponseWriter, r *http.Request) bool {
    result := false

    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    // Check if user is authenticated
    if len( username ) > 0 {
        result = true
    }
    
    return result
}

func checkLoguedUserAdmin(w http.ResponseWriter, r *http.Request) bool {
    result := false

    sessionident := getSessionID( w, r )
    username := sqlite_getSessionUser( sessionident )
    // Check if user is authenticated
    if len( username ) > 0 && checkUserAdmin( username ) {
        result = true
    }
    
    return result
}

//GET SESSION ID

func getSessionID( w http.ResponseWriter, r *http.Request ) string {
    session, err := G_STORE.Get(r, "cookie-name")
    result := getRandomStringNum( 32 )
    if err != nil {
        http.Error(w, err.Error(), http.StatusInternalServerError)
        return ""
    }
    if session.Values["id"] == nil || len( session.Values["id"].(string) ) < 32 {
        session.Values["id"] = result
        session.Save(r, w)
    } else {
        result = session.Values["id"].(string)
    }
    //result := session.ID
    //fmt.Println( session )
    //fmt.Println( session.Values )
    showInfo( "SESSION-GET-ID: " + result )
    return result
}

//GET USER IP

func getUserIP(r *http.Request) string {
    IPAddress := r.Header.Get("X-Real-Ip")
    if IPAddress == "" {
        IPAddress = r.Header.Get("X-Forwarded-For")
    }
    if IPAddress == "" {
        IPAddress = r.RemoteAddr
    }
    //Clean Port
    IPAddress = regExpReplaceData( IPAddress, "", `:{1}\d{1,5}$` )
    return IPAddress
}

//GET USER REFERER

func getUserReferer( r *http.Request ) string {
    result := r.Referer();
    return result
}

//GET USER URL

func getUserURL( r *http.Request ) string {
    var result, scheme string
    if G_SERVER_HTTPS {
        scheme = "https"
    } else {
        scheme = "http"
    }
    result = scheme + "://" + G_SERVER_BIND_IP + r.URL.RequestURI()
    return result
}

//GET RANDOM STRING

func getRandomString( n int ) string {
    var letters = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
    rand.Seed(time.Now().UnixNano())
    
    b := make([]rune, n)
    for i := range b {
        b[i] = letters[rand.Intn(len(letters))]
    }
    return string(b)
}

func getRandomStringNum( n int ) string {
    var letters = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
    rand.Seed(time.Now().UnixNano())
    
    b := make([]rune, n)
    for i := range b {
        b[i] = letters[rand.Intn(len(letters))]
    }
    return string(b)
}

//SEND FILE RAW

func sendFile(w http.ResponseWriter, r *http.Request, file string) {
	
	//Check if file exists and open
	Openfile, _ := os.Open(file)

	//Get the Content-Type of the file
	//Create a buffer to store the header of the file in
	FileHeader := make([]byte, 512)
	//Copy the headers into the FileHeader buffer
	Openfile.Read(FileHeader)
	//Get content type of file
	FileContentType := http.DetectContentType(FileHeader)

	//Get the file size
	FileStat, _ := Openfile.Stat()                     //Get info from file
	FileSize := strconv.FormatInt(FileStat.Size(), 10) //Get file size as a string

	//Send the headers
	w.Header().Set("Content-Disposition", "attachment; filename="+filepath.Base(file))
	w.Header().Set("Content-Type", FileContentType)
	w.Header().Set("Content-Length", FileSize)

	//Send the file
	//We read 512 bytes from the file already, so we reset the offset back to 0
	Openfile.Seek(0, 0)
	io.Copy(w, Openfile) //'Copy' the file to the client
	return
}

//SLICE TO MAP

func sliceToMap( elements []string, key string ) map[int]map[string]string {
    result := make(map[int]map[string]string, len( elements ))
    var i int
    i = 0
    for _, element := range elements {
        result[ i ] = map[string]string{ key : element }
        i++
    }
    return result
}

//TRIM SLICE OF STRINGS

func sliceTrim( s []string ) {
    for i, d := range s {
        s[ i ] = strings.Trim( d, " " )
    }
}

//REMOVE ITEM FROM SLICE

func sliceRemoveItem(slice []string, s int) []string {
    return append(slice[:s], slice[s+1:]...)
}

//STRING TO INT

func strToInt( str string ) int {
    val, _ := strconv.ParseInt( str, 10, 32 )
    return int( val )
}

//STRING TO FLOAT 64

func strToFloat( str string ) float64 {
    val, _ := strconv.ParseFloat( str, 64 )
    return val
}

//INT TO STRING

func intToStr( i int ) string {
    return strconv.Itoa( i )
}

//FLOAT TO STRING

func floatToStr( i float64 ) string {
    return strconv.FormatFloat(i, 'f', 2, 64 )
}

//FLOAT TO INT

func floatToInt( i float64 ) int {
    return int( i )
}

//STR IN STR

func strInStr( base string, contain string ) bool {
    return strings.Contains( strings.ToUpper(base), strings.ToUpper(contain) )
}

//STR ENDS WITH

func strEndWith ( full string, ends string ) bool {
    return strings.HasSuffix(strings.ToUpper(full), strings.ToUpper(ends))
}

//STR START WITH

func strStartWith ( full string, ends string ) bool {
    return strings.HasPrefix(strings.ToUpper(full), strings.ToUpper(ends))
}

//STR BIGGEST WORD

func longestWord(s string) string {
    best, length := "", 0
    for _, word := range strings.Split(s, " ") {
        if len(word) > length {
            best, length = word, len(word)
        }
    }
    return best
}

//STR SUB STRING

func getSubString(s string, start int, end int) string {
    result := ""
    for pos, char := range s{
        if pos >= start && pos <= end {
            result += string(char)
        }
    }
    return result
}

//MAPS STRING:STRING JOINS

func mapsJoinSS( map1 map[string]string, map2 map[string]string ) {
    for k, v := range map2 {
        map1[ k ] = v
    }
}


//MAPS MEDIA JOINS

func mapsJoinMedia( map1 map[int]map[string]string, map2 map[int]map[string]string ) {
    for x := 0; x < len(map2); x++ {
        map1[ ( len(map1) ) ] = map2[x]
    }
}

//MAPS MEDIA KEY ORDER

func mapsMediaKeyOrder( map1 map[int]map[string]string ) []int {
    var keys []int
    for k := range map1 {
        keys = append(keys, k)
    }
    sort.Ints(keys)
    return keys
}

//MAPS MEDIA TO SLICE

func mapMediaToSlice( map1 map[int]map[string]string ) []map[string]string  {
    var result []map[string]string
    for x := 0; x < len(map1); x++ {
        result = append(result, map1[x])
    }
    
    return result
}

//CHECK STRING IN SLICE

func stringInSlice(str string, list []string) bool {
 	for _, v := range list {
 		if v == str {
 			return true
 		}
 	}
 	return false
}

//CHECK SLICE ELEMENTS IN STRING

func sliceInString(str string, list []string) bool {
 	for _, v := range list {
        if strInStr( str, v ) {
 			return true
 		}
 	}
 	return false
}

//GET POS STRING IN SLICE

func stringInSlicePos(str string, list []string) int {
    result := -1
 	for p, v := range list {
 		if v == str {
 			result = p
            break
 		}
 	}
 	return result
 }



//CHECK STRING IN SLICE OR ELEMENT START WITH STRING

func strInSliceOrStartWith(str string, list []string) bool {
 	for _, v := range list {
        if v == str || strStartWith( v, str ) {
 			return true
 		}
 	}
 	return false
 }

//APP PATH

func appPath() string {
    path, err := os.Executable()
    if err != nil {
        showInfo( "CONFIG-APPPATH: ERROR" )
        log.Fatal(err)
        path = ""
    }
    return path
}

//PATH JOIN

func pathJoin( a string, b string ) string {
    //filepath.Abs(a)
    return filepath.Join(a, b)
}

//FILES ON FOLDER

func getFiles( folder string, extension string ) []string {
    var result []string
    folder, _ = filepath.Abs(folder)
    files, err := ioutil.ReadDir(folder)
    if err != nil {
        log.Fatal(err)
    }

    for _, file := range files {
        f := pathJoin( folder, file.Name() )
        fmt.Println()
        if extension == "" || strEndWith( file.Name(), extension ) {
            if checkIsFile( f ) {
                result = append(result, f)
            }
        }
    }
    
    return result
}

//FILES ON FOLDER

func getFolders( folder string ) []string {
    var result []string
    folder, _ = filepath.Abs(folder)
    files, err := ioutil.ReadDir(folder)
    if err != nil {
        log.Fatal(err)
    }

    for _, file := range files {
        f := pathJoin( folder, file.Name() )
        fmt.Println()
        if checkIsDir( f ) {
            result = append(result, f)
        }
    }
    
    return result
}

//FILE EXIST

func fileExist( file string ) bool {
    result := false
    if _, err := os.Stat( file ); err == nil {
        result = true
    }
    return result
}

//FILE REMOVE

func fileRemove( file string ) bool {
    result := false
    if err := os.Remove(file); err == nil {
        result = true
    }
    return result
}

//FILE TEXT APPEND LINE

func fileAppendLine( file string, text string ) {     
    fileopen, _ := os.OpenFile(file, os.O_RDWR|os.O_APPEND|os.O_CREATE, 0644)
    defer fileopen.Close()
    //fileopen.WriteString("\n" +text)
    fmt.Fprintf(fileopen, "\n%s", text)
}

func fileAppendText( file string, text string ) {     
    fileopen, _ := os.OpenFile(file, os.O_RDWR|os.O_APPEND|os.O_CREATE, 0644)
    defer fileopen.Close()
    //fileopen.WriteString(text)
    fmt.Fprintf(fileopen, "%s", text)
}

//FILE TEXT READ

func readAll(filepath string) string {
    result := ""
 
    file, err := os.Open(filepath)
    if err != nil {
        return result
    }

    defer file.Close()
    scanner := bufio.NewScanner(file)
    scanner.Split(bufio.ScanLines) 
    
    for scanner.Scan() {
        result += "\n" + scanner.Text();
    }

    return result
}

func readAllLines(filepath string) []string {
    var result []string
 
    file, err := os.Open(filepath)
    if err != nil {
        return result
    }

    defer file.Close()
    scanner := bufio.NewScanner(file)
    scanner.Split(bufio.ScanLines) 
    
    for scanner.Scan() {
        result = append(result,scanner.Text())
    }

    return result
}

func readAllToHTML(filepath string) string {
    result := ""
 
    file, err := os.Open(filepath)
    if err != nil {
        return result
    }

    defer file.Close()
    scanner := bufio.NewScanner(file)
    scanner.Split(bufio.ScanLines) 
    
    for scanner.Scan() {
        result += "<br />" + scanner.Text();
    }

    return result
}

//DATE NOW

func dateGetNow() string{
    return time.Now().Format("2006-01-02 15:04:05")
}

//FILE MIME

func fileMime(file string) string {
    result := "application/octet-stream"
    
	//Check if file exists and open
	Openfile, _ := os.Open(file)

	//Get the Content-Type of the file
	//Create a buffer to store the header of the file in
	FileHeader := make([]byte, 512)
	//Copy the headers into the FileHeader buffer
	Openfile.Read(FileHeader)
	//Get content type of file
	FileContentType := http.DetectContentType(FileHeader)
    
    if len( FileContentType ) > 0 {
        result = FileContentType
    }

	return result
}

func checkMimeVideo(file string) bool {
    result := false
    mime := fileMime( file )
    if strInStr( mime, "video" ) {
        result = true
    }

	return result
}

func checkMimeImage(file string) bool {
    result := false
    mime := fileMime( file )
    valid := []string { "jpeg", "jpg", "png", "gif" }
    if strInStr( mime, "image" ) || sliceInString(mime, valid) {
        result = true
    }

	return result
}

//PATH IS FILE

func checkIsFile( path string ) bool {
    result := false
    file, err := os.Open(path)

    if err != nil {
        // handle the error and return
    }
    defer file.Close()
    // use a switch to make it a bit cleaner
    fi, err := file.Stat();
    switch {
      case err != nil:
        // handle the error and return
      case fi.IsDir():
        // it's a directory
      default:
        // it's not a directory
        result = true
    }
    return result
}

//PATH IS DIR

func checkIsDir( path string ) bool {
    result := false
    file, err := os.Open(path)

    if err != nil {
        // handle the error and return
    }
    defer file.Close()
    // use a switch to make it a bit cleaner
    fi, err := file.Stat();
    switch {
      case err != nil:
        // handle the error and return
      case fi.IsDir():
        // it's a directory
        result = true
      default:
        // it's not a directory
    }
    return result
}

//TMP FOLDER ADD

func newTmpFolder() string {
    var result string
    tmpfolder, err := filepath.Abs(G_TMP_FOLDER)
    if err == nil {
        result = pathJoin(tmpfolder, getRandomString(6))
        os.MkdirAll( result, os.ModePerm)
    } else {
        result = ""
    }
    return result
}

//TMP FOLDER REMOVAL

func delTree(dir string) bool {
    result := true
    d, err := os.Open(dir)
    if err != nil {
        result = false
    } else {
        defer d.Close()
        names, err := d.Readdirnames(-1)
        if err != nil {
            result = false
        } else {
            for _, name := range names {
                err = os.RemoveAll(pathJoin(dir, name))
                if err != nil {
                    result = false
                    break
                }
            }
            //won folder
            err = os.RemoveAll(dir)
        }
    }
    return result
}

//REGEXP EXTRACT DATA

func regExpGetData2( data string, re string ) []string {
    var result []string
    var d [][]string
    rec := regexp.MustCompile("(?i)"+re)
    d = rec.FindAllStringSubmatch(data, -1)
    //fmt.Println( d )
    for _, s := range d {
        if len(s) > 2 {
            result = append(result, s[1])
            result = append(result, s[2])
        }
    }
    return result
}

func regExpGetData( data string, re string ) []string {
    var result []string
    var d [][]string
    rec := regexp.MustCompile("(?i)"+re)
    d = rec.FindAllStringSubmatch(data, -1)
    //fmt.Println( d )
    for _, s := range d {
        if len(s) > 1 {
            result = append(result, s[1])
        }
    }
    return result
}

func regExpGetDataFirst( data string, re string ) string {
    var result string
    var d []string
    rec := regexp.MustCompile("(?i)"+re)
    d = rec.FindStringSubmatch(data)
    if len( d ) > 1 {
        result = d[1]
    } else {
        result = ""
    }
    //fmt.Println( d )
    return result
}

func regExpGetDataFirstPos( data string, re string ) int {
    var result int
    var d []int
    rec := regexp.MustCompile("(?i)"+re)
    //d = rec.FindStringSubmatch(data)
    d = rec.FindStringSubmatchIndex(data)
    if len( d ) > 0 {
        result = d[0] - 1
        if result <= 0 {
            result = 0
        }
    } else {
        result = -1
    }
    //fmt.Println( d )
    return result
}

//REGEXP REPLACE DATA

func regExpReplaceData( data string, replace string, re string ) string {
    var result string
    rec := regexp.MustCompile("(?i)"+re)
    result = rec.ReplaceAllString(data, replace)
    return result
}

//GEOIP

func checkIPCountry( ip string ) bool {
    result := true
    
    if len( G_GEOIPFILTER ) > 0 && len( ip ) > 6 {
        htmldata := getURLData( "http://www.geoplugin.net/json.gp?ip=" + ip )
        //fmt.Println( htmldata )
        //"geoplugin_countryCode":"NL",
        country := regExpGetDataFirst( htmldata, `\"geoplugin_countryCode\":\"(.+)\"` )
        country2 := regExpGetDataFirst( htmldata, `\"geoplugin_countryName\":\"(.+)\"` )
        //fmt.Println( country )
        if len( country ) <= 0 && len( country2 ) <= 0 {
            //error data?, blocked or not
            showInfo( "GEOIP-ERROR: NO DATA" + ip + " => " + country )
            sqlite_bans_insert( ip )
            result = false
        } else if( stringInSlice( country, G_GEOIPFILTER ) ) {
            //Country allowed
            showInfo( "GEOIP-ACCESS: Valid country ip: " + ip + " => " + country )
            sqlite_whitelist_insert( ip )
        } else if( stringInSlice( country2, G_GEOIPFILTER ) ) {
            //Country allowed
            showInfo( "GEOIP-ACCESS: Valid country2 ip: " + ip + " => " + country )
            sqlite_whitelist_insert( ip )
        } else {
            //Not allowed
            showInfo( "GEOIP-INVALID-ACCESS: Invalid country ip: " + ip + " => " + country )
            sqlite_bans_insert( ip )
            result = false
        }
    }
    
    return result
}

//GET DATA FROM URL

func getURLData( url string ) string {
    result := ""
    myClient := &http.Client{Timeout: 10 * time.Second}
    r, err := myClient.Get(url)
    if err != nil {
        result = "ERROR"
    } else {
        //result = string(r.Body)
        body, err := ioutil.ReadAll(r.Body)
        if err != nil {
            result = "ERROR2"
        } else {
            result = string(body)
        }
    }
    defer r.Body.Close()

    return result
}

// CHECK VALID URL

func isValidUrl( toTest string ) bool {
	_, err := url.ParseRequestURI(toTest)
	if err != nil {
		return false
	} else {
		return true
	}
}

//FILE HASH

func fileHash( file string ) string {
    result := ""
    if fileExist( file ) {
        input := strings.NewReader(file)
        hash := md5.New()
        if _, err := io.Copy(hash, input); err != nil {
            showInfoError(err)
        }
        sum := hash.Sum(nil)
        result = fmt.Sprintf("%x", sum)
    }
    return result
}
