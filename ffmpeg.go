package main

import (
    "fmt"
	"io"
	"net/http"
	"os/exec"
    "strings"
	"strconv"
    
    //"log"
    //"bufio"
    //"time"
    
    "encoding/json"
    
	"math"
)

func sendVideo(w http.ResponseWriter, r *http.Request, file string, timeplayed string, mode string, audiotrack string, subtrack string, quality string) {
    //command := "ffmpeg  -nostdin  -i \"file\" -c:v libvpx -quality realtime -b:v 1M -maxrate 1M -bufsize 1000k -pix_fmt yuv420p -aspect 16:9 -preset baseline -level 4.0 -c:a libvorbis -f webm - "
    
    command := G_FFMPEG_CMD
    var args []string
    var headercontent string
    
    if len(mode) == 0 {
        mode = "webm"
    }
    
    if len(timeplayed) == 0 {
        timeplayed = "0"
    }
    
    var qmin, qmax string
    if quality != "hd" {
        qmin = G_FFMPEG_LQ_MIN
        qmax = G_FFMPEG_LQ_MAX
    } else {
        qmin = G_FFMPEG_HQ_MIN
        qmax = G_FFMPEG_HQ_MAX
    }
    
    var audiotrack1, audiotrack2, audiotrack3, audiotrack4 string
    if strToInt(audiotrack) > 0 {
        audiotrack1 = "-map"
        audiotrack2 = "0:0"
        audiotrack3 = "-map"
        audiotrack4 = "0:" + audiotrack
    } else {
        audiotrack1 = ""
        audiotrack2 = ""
        audiotrack3 = ""
        audiotrack4 = ""
    }
    
    var scale1, scale2 string
    var subtrack1, subtrack2 string
    if strToInt( subtrack ) > -1 {
        showInfo( "FFMPEG-SET-SUBS: " + subtrack )
        scale1 = ""
        scale2 = ""
        subtrack1 = "-filter_complex"
        subtrack2 = "[0:v][0:s:" + subtrack + "]overlay=(main_w-overlay_w)/2:main_h-overlay_h,scale=trunc(iw/2)*2:trunc(ih/2)*2,scale=-2:iw"
    } else {
        scale1 = "-vf"
        scale2 = "scale=trunc(iw/2)*2:trunc(ih/2)*2"
        subtrack1 = ""
        subtrack2 = ""
    }
    
    //CHECK LIVETV .ts mode
    var liveparams1, liveparams2, liveparams3, liveparams4 string
    if strEndWith( file, ".ts" ) {
        liveparams1 = "-fflags"
        liveparams2 = "+genpts"
        liveparams3 = "-stream_loop"
        liveparams4 = "-1"
    } else {
        liveparams1 = ""
        liveparams2 = ""
        liveparams3 = ""
        liveparams4 = ""
    }
    
    //mode
    switch mode {
        case "direct":
            //test
            sendFile(w,r, file)
            return
        case "fast":
            //test
            args = []string{
                "-nostdin", 
                "-ss",
                timeplayed,
                "-i", 
                file,
                "-vcodec",
                "libx264",
                "-crf",
                "23",
                "-preset",
                "ultrafast",
                "-c:a",
                "copy",
                "-f",
                "matroska",
                "-",
                
            }
            headercontent = "video/matroska"
        case "livetv":
            args = []string{
                "-nostdin", 
                liveparams1,
                liveparams2,
                liveparams3,
                liveparams4,
                "-i", 
                file,
                "-c:v", 
                "libvpx", 
                "-threads",
                "0",
                "-quality",
                "realtime",
                "-b:v",
                qmin,
                "-maxrate",
                qmax,
                "-bufsize",
                "1000k",
                "-pix_fmt",
                "yuv420p",
                scale1,
                scale2,
                "-aspect",
                "16:9",
                "-preset",
                "baseline",
                "-level",
                "3.0",
                "-c:a",
                "libvorbis",
                "-f",
                "webm",
                "-",
            }
            headercontent = "video/webm"
        case "mp4":
            args = []string{
                "-nostdin", 
                "-ss",
                timeplayed,
                "-i", 
                file,
                subtrack1,
                subtrack2,
                audiotrack1,
                audiotrack2,
                audiotrack3,
                audiotrack4,
                "-c:v", 
                "libx264", 
                "-threads",
                "0",
                "-quality",
                "realtime",
                "-b:v",
                qmin,
                "-maxrate",
                qmax,
                "-movflags",
                "+faststart",
                "-bufsize",
                "1000k",
                "-g",
                "74",
                "-strict",
                "experimental",
                "-pix_fmt",
                "yuv420p",
                scale1,
                scale2,
                "-aspect",
                "16:9",
                "-profile:v",
                "baseline",
                "-level",
                "3.0",
                "-preset",
                "ultrafast",
                "-tune",
                "zerolatency",
                "-c:a",
                "aac",
                "-ab",
                "128k",
                "-f",
                "mp4",
                "-movflags",
                "frag_keyframe+empty_moov",
                "-",
            }
            headercontent = "video/mp4"
        case "webm":
            args = []string{
                "-nostdin", 
                "-ss",
                timeplayed,
                "-i", 
                file,
                subtrack1,
                subtrack2,
                audiotrack1,
                audiotrack2,
                audiotrack3,
                audiotrack4,
                "-c:v", 
                "libvpx", 
                "-threads",
                "0",
                "-quality",
                "realtime",
                "-b:v",
                qmin,
                "-maxrate",
                qmax,
                "-bufsize",
                "1000k",
                "-pix_fmt",
                "yuv420p",
                scale1,
                scale2,
                "-aspect",
                "16:9",
                "-preset",
                "baseline",
                "-level",
                "3.0",
                "-c:a",
                "libvorbis",
                "-f",
                "webm",
                "-",
            }
            headercontent = "video/webm"
        
        case "webm2":
            args = []string{
                "-nostdin", 
                "-ss",
                timeplayed,
                "-i", 
                file,
                subtrack1,
                subtrack2,
                audiotrack1,
                audiotrack2,
                audiotrack3,
                audiotrack4,
                "-c:v", 
                "libvpx-vp9",
                "-threads",
                "0",
                "-speed",
                "8",
                "-quality",
                "realtime",
                "-b:v",
                qmin,
                "-maxrate",
                qmax,
                "-bufsize",
                "1000k",
                "-pix_fmt",
                "yuv420p",
                scale1,
                scale2,
                "-aspect",
                "16:9",
                "-preset",
                "baseline",
                "-level",
                "3.0",
                "-c:a",
                "libvorbis",
                "-f",
                "webm",
                "-",
            }
            headercontent = "video/webm"
    }
    
    //Cleam Empty element from arguments
    var args2 []string
    for _, v := range args {
        if len(v) > 0 {
            args2 = append(args2, v)
        }
    }
    args = args2
    
	//w.Header().Set("Content-Disposition", "attachment; filename="+file)
    w.Header().Set("Content-Type", headercontent)
	//w.Header().Set("Content-Length", FileSize)
    
    showInfo( "FFMPEG-SEND-INIT: " )
	cmd := exec.Command( command, args... )
	pipeReader, pipeWriter := io.Pipe()
	cmd.Stdout = pipeWriter
	//cmd.Stderr = pipeWriter
    cmd.Start()
	//cmd.Run()
	writeCmdOutput(w, r, pipeReader, cmd)
	pipeWriter.Close()
    showInfo( "FFMPEG-SEND-END: " )
}

func writeCmdOutput(w http.ResponseWriter, r *http.Request, pipeReader *io.PipeReader, cmd *exec.Cmd) {
	buffer := make([]byte, 1024)
	for {
		n, err := pipeReader.Read(buffer)
		if err != nil {
			pipeReader.Close()
			break
		}

		data := buffer[0:n]
		w.Write(data)
		if f, ok := w.(http.Flusher); ok {
            //showInfo( "FFMPEG-SEND-FLUSH: " + intToStr( len( data ) ) )
			f.Flush()
            //detect cancelled
            select {
                case <-r.Context().Done():
                    // Client gave up
                    showInfo( "FFMPEG-SEND-DISCONNECT: Kill process ffmpeg" )
                    cmd.Process.Kill()
                    return
                default:
            }
		}
		//reset buffer
		for i := 0; i < n; i++ {
			buffer[i] = 0
		}
	}
}

//EXTRACT SUB TO FILE

func ffmpegSubExtract ( file string, filesub string, track string ) bool {
    result := false
    
    command := G_FFMPEG_CMD
    args := []string{
        "-i",
        file,
        "-map", 
        "0:" + track, 
        filesub,
    }
    exec.Command( command, args... ).Output()
    if fileExist(filesub) {
        return true
    }
    
    return result
}

//SUBS FILE TO JSON FOR PLAYER

type subToJSONTemplateE struct {
    Timestart   JsonFloat `json:"timestart"`
    Timeend     JsonFloat `json:"timeend"`
    Text        string `json:"text"`
}

type subToJSONTemplate []subToJSONTemplateE

func subToJson( file string ) string {
    var result subToJSONTemplate
    var result2 string
    /*
    1
    00:00:02,600 --> 00:00:08,869
    Al menos la mitad de esta historia
    está documentada como hecho histórico.

    2
    ...
    */
    /*
    [{"timestart":0,"timeend":0,"text":""},{"timestart":70.22,"timeend":71.51700000000001,"text":"Anhelo.<br>"} ...
    */
    data := readAllLines( file )
    var timeend JsonFloat
    var timestart JsonFloat
    nowline := ""
    for _, line := range data {
        lines := string(line)
        lines = strings.Trim( lines, " " )
        
        if len(lines) == 0 {
            //empty line, add item if have data
            if timeend > 0 && timestart > 0 && len(nowline) > 0 {
                dd := subToJSONTemplateE { Timestart:timestart, Timeend:timeend, Text:nowline }
                result = append(result, dd)
            }
            timestart = 0
            timeend = 0
            nowline = ""
        } else if _, err := strconv.Atoi(lines); err == nil {
            //numeric lines
        } else if strInStr( lines, "-->" ) {
            //time lines
            timestart = subToJsonGetTime( lines, 0 )
            timeend = subToJsonGetTime( lines, 1 )
        } else {
            //text to add
            if len(nowline) > 0 {
                nowline += "<br />" + lines
            } else {
                nowline += lines
            }
        }
    }
    b, err := json.Marshal(result)
    if err == nil {
        result2 = string(b)
    }
    
    return result2
}

func subToJsonGetTime( line string, pos int ) JsonFloat {
    var result JsonFloat
    result = 0.00
    
    sstring := strings.Split(line, "-->")
    if len(sstring) < 2 {
        //No data
    } else if pos == 0 {
        result = subToJsonParseTime(strings.Trim(sstring[0], " "))
    } else {
        result = subToJsonParseTime(strings.Trim(sstring[1], " "))
    }
    
    return result
}

func subToJsonParseTime( data string ) JsonFloat {
    var result JsonFloat
    result = 0.00
    data = strings.Replace(data, ",", ".", -1)
    sstring := strings.Split(data, ":")
    if len(sstring) > 0 {
        for k, v := range sstring {
            v = strings.Trim(v, " ")
            vn := strToFloat( v )
            if vn > 0 {
                result += JsonFloat( ( math.Pow(60, float64(len(sstring) - k - 1)) ) * vn )
            }
        }
    }
    
    return result
}

//FFPROBE

type ffprobeStreams []struct {
    AvgFrameRate       string `json:"avg_frame_rate"`
    BitsPerRawSample   string `json:"bits_per_raw_sample"`
    ChromaLocation     string `json:"chroma_location"`
    CodecLongName      string `json:"codec_long_name"`
    CodecName          string `json:"codec_name"`
    CodecTag           string `json:"codec_tag"`
    CodecTagString     string `json:"codec_tag_string"`
    CodecTimeBase      string `json:"codec_time_base"`
    CodecType          string `json:"codec_type"`
    CodedHeight        int    `json:"coded_height"`
    CodedWidth         int    `json:"coded_width"`
    ColorPrimaries     string `json:"color_primaries"`
    ColorRange         string `json:"color_range"`
    ColorSpace         string `json:"color_space"`
    ColorTransfer      string `json:"color_transfer"`
    DisplayAspectRatio string `json:"display_aspect_ratio"`
    Disposition        struct {
        AttachedPic     int `json:"attached_pic"`
        CleanEffects    int `json:"clean_effects"`
        Comment         int `json:"comment"`
        Default         int `json:"default"`
        Dub             int `json:"dub"`
        Forced          int `json:"forced"`
        HearingImpaired int `json:"hearing_impaired"`
        Karaoke         int `json:"karaoke"`
        Lyrics          int `json:"lyrics"`
        Original        int `json:"original"`
        TimedThumbnails int `json:"timed_thumbnails"`
        VisualImpaired  int `json:"visual_impaired"`
    } `json:"disposition"`
    FieldOrder        string `json:"field_order"`
    HasBFrames        int    `json:"has_b_frames"`
    Height            int    `json:"height"`
    Index             int    `json:"index"`
    IsAvc             string `json:"is_avc"`
    Level             int    `json:"level"`
    NalLengthSize     string `json:"nal_length_size"`
    PixFmt            string `json:"pix_fmt"`
    Profile           string `json:"profile"`
    RFrameRate        string `json:"r_frame_rate"`
    Refs              int    `json:"refs"`
    SampleAspectRatio string `json:"sample_aspect_ratio"`
    StartPts          int    `json:"start_pts"`
    StartTime         string `json:"start_time"`
    Tags              struct {
        Language    string `json:"language"`
        Title       string `json:"title"`
    } `json:"tags"`
    TimeBase string `json:"time_base"`
    Width    int    `json:"width"`
}

type ffprobeJson struct {
	Format struct {
		BitRate        string `json:"bit_rate"`
		Duration       string `json:"duration"`
		Filename       string `json:"filename"`
		FormatLongName string `json:"format_long_name"`
		FormatName     string `json:"format_name"`
		NbPrograms     int    `json:"nb_programs"`
		NbStreams      int    `json:"nb_streams"`
		ProbeScore     int    `json:"probe_score"`
		Size           string `json:"size"`
		StartTime      string `json:"start_time"`
		Tags           struct {
			CreationTime string `json:"creation_time"`
			Encoder      string `json:"encoder"`
			Title        string `json:"title"`
		} `json:"tags"`
	} `json:"format"`
	Streams             ffprobeStreams `json:"streams"`
}

func ffprobeGetData( filepath string) ffprobeJson {
    var result ffprobeJson
    
    command := G_FFPROBE_CMD
    args := []string{
        "-rw_timeout",
        "1M",
        "-v", 
        "quiet", 
        "-print_format",
        "json",
        "-show_format",
        "-show_streams", 
        "-hide_banner",
        filepath, 
    }
    out, _ := exec.Command( command, args... ).Output()
    json.Unmarshal(out, &result)
    
    return result
}

func ffprobeDuration( filepath string) string {
    result := "3600"
    data := ffprobeGetData(filepath)
    //fmt.Println(data.Format.Duration)
    t := intToStr(floatToInt(strToFloat(data.Format.Duration)))
    if len(t) > 0 && t != "0" {
        result = t
    }
    
    return result
}

func ffprobeSize( filepath string) (string, string) {
    result := "720"
    result2 := "480"
    data := ffprobeGetData(filepath)
    if len(data.Streams) > 0 {
        t := data.Streams[0].Width
        t1 := data.Streams[0].Height
        if t > 0 && t1 > 0 {
            result = intToStr(t)
            result2 = intToStr(t1)
        }
    }
    
    return result, result2
}

func ffprobeVideoCodec( filepath string) string {
    result := "NOCODEC"
    data := ffprobeGetData(filepath)
    if len(data.Streams) > 0 {
        result = data.Streams[0].CodecName
    }
    
    return result
}

func ffprobeTracks( filepath string ) ffprobeStreams {
    var result ffprobeStreams
    data := ffprobeGetData(filepath)
    fmt.Println(data.Format.Duration)
    if len(data.Streams) > 0 {
        result = data.Streams
    }
    
    return result
}

func ffprobeTracksAudio( filepath string ) ffprobeStreams {
    var result ffprobeStreams
    data := ffprobeTracks(filepath)
    if len(data) > 0 {
        for _, x := range data {
            if x.CodecType == "audio" {
                result = append(result,x)
            }
        }
    }
    
    return result
}

func ffprobeTracksSubsOCR( filepath string ) ffprobeStreams {
    var result ffprobeStreams
    data := ffprobeTracks(filepath)
    if len(data) > 0 {
        for _, x := range data {
            if x.CodecType == "subtitle" && x.CodecName != "dvd_subtitle" {
                result = append(result,x)
            }
        }
    }
    
    return result
}

func ffprobeTracksSubsNOOCR( filepath string ) ffprobeStreams {
    var result ffprobeStreams
    data := ffprobeTracks(filepath)
    if len(data) > 0 {
        for _, x := range data {
            if x.CodecType == "subtitle" && x.CodecName == "dvd_subtitle" {
                result = append(result,x)
            }
        }
    }
    
    return result
}