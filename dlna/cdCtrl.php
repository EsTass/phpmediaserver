<?php

//CORE BASE

define( 'ACCESS', TRUE );
	
define( 'DS', DIRECTORY_SEPARATOR );
define( 'PPATH_BASE', dirname( dirname( __FILE__ ) ) );
define( 'PPATH_CORE', PPATH_BASE . DS . 'core'  );
define( 'PPATH_ACTIONS', PPATH_BASE . DS . 'actions'  );
define( 'PPATH_CACHE', PPATH_BASE . DS . 'cache'  );
define( 'PPATH_TEMP', PPATH_CACHE . DS . 'temp'  );
define( 'PPATH_LANG', PPATH_BASE . DS . 'lang'  );
define( 'PPATH_MEDIAINFO', PPATH_CACHE . DS . 'mediadata' );//file: idmedia.type (poster, landscape, nfo, etc)
define( 'PPATH_IMGS', PPATH_BASE . DS . 'imgs' );

//CORE BASE
require( PPATH_CORE . DS . 'functions.php' );

//CONFIG
require( PPATH_BASE . DS . 'config.php' );

//CORE EXT
require( PPATH_CORE . DS . 'functions.bd.php' );
require( PPATH_CORE . DS . 'functions.dlna.php' );

$IPALLOWED = explode( '.', DLNA_BINDIP );
if( is_array( $IPALLOWED )
&& count( $IPALLOWED ) == 4
&& (
    startsWith( USER_IP, '127.0.0.1' )
    || startsWith( USER_IP, $IPALLOWED[ 0 ] . '.' . $IPALLOWED[ 1 ] . '.' . $IPALLOWED[ 2 ] )
    )
){

}else{
    die( 'HTTP/1.0 401 Unauthorized.<br />' );
}

if( !defined( 'DLNA_ACTIVE' ) 
|| DLNA_ACTIVE == FALSE
){
    die( 'NODLNA' );
}

//GENRES COPY
$genres = array();
foreach( O_MENU_GENRES AS $gv => $gt ){
    $genres[] = $gv;
}

//END CORE BASE

function get_dlna_profile($path) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $ct = finfo_file($finfo, $path);
    switch ($ct) {
        case 'image/png': $profile = 'DLNA.ORG_PN=PNG_TN'; break;
        case 'image/jpeg': $profile = 'DLNA.ORG_PN=JPEG_TN'; break;
        default: $profile = '*';
    }
    finfo_close($finfo);
    return $profile;
}

require_once("didl.php");

class InvalidInputException extends Exception { }

class ContentDirectory {
    
    function Search($req) {
        //TODO
        $items = new DIDL(DIDL::ROOT_PARENT_ID);
        return array('Result'=>$items->getXML(), 'NumberReturned'=>$items->count, 'TotalMatches'=>$items->count, 'UpdateID'=>$this->SystemUpdateID);
    }

    protected function BrowseMetadata($req)
    {
        
        if ($req->ObjectID == DIDL::ROOT_ID) {
            //ROOT base directory
            $items = new DIDL(DIDL::ROOT_PARENT_ID);
            
            $items->addFolder('root', $req->ObjectID)
                ->searchclass(DIDL::ITEM_CLASS_AUDIO)
                ->searchclass(DIDL::ITEM_CLASS_VIDEO);
        }else{
            //Rest  from elements objectID as id from root=0 to genres=X
            //NEW
            //get elements from genres and select number BASE=0.NUMBERFOLDERGENRE.IDMEDIAINFO
            $id = $req->ObjectID;
            $idd = explode( '.', $id );
            $pid = 0;
            
            if( $id == '0' ){
                $pid = DIDL::ROOT_ID;
            }else{
                if (is_dir($path))
                    $pid = implode('.', array_slice(explode('.', $req->ObjectID), 0, -1));
                else
                    $pid = explode('$', $req->ObjectID)[0];
            }
            $items = new DIDL($pid);

            if( is_array( $idd )
            && count( $idd ) == 2
            && is_numeric( $idd[ 0 ] )
            && array_key_exists( $idd[ 0 ] - 1, $genres )
            && ( $edata = sqlite_media_getdata( $idd[ 1 ] ) ) != FALSE 
            && is_array( $edata )
            && count( $edata ) > 0
            && ( $idata = sqlite_media_getdata_order_mediainfo( $idd[ 1 ] ) ) != FALSE
            && is_array( $idata )
            && count( $idata ) > 0
            ){
                //2 elements: BASE.idgenre.idmedia
                $items = new DIDL( $idd[ 0 ] );
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                
                $ct = finfo_file( $finfo, $edata[ 'file' ] );
                $cls = DIDL::class_from_mime( $ct );
                if (!$cls){
                    return array( 'illegal' );
                }
                
                $title = $idata[ 'title' ];
                if( $idata[ 'season' ] > 0 ){
                    $title .= ' ' . sprintf( '%02d', $idata[ 'season' ] ) . 'x' . sprintf( '%02d', $idata[ 'episode' ] );
                }
                
                $itm = $items->addItem($cls, $idata[ 'title' ], $req->ObjectID);
                $itm->resource(
                    DLNA_WEB_BASEFOLDER_HTTP . '?r=r&action=playtime&mode=' . DLNA_ENCODEMODE . '&idmedia=' . $f[ 'idmedia' ] . '&PHPSESSION=' . get_dlna_user_session(), 
                    array(
                        'filesize' =>filesize( $edata[ 'file' ] ),
                        'protocolInfo' => 'http-get:*:'.$ct.':*'
                    )
                );
                finfo_close($finfo);
                
            }elseif( is_array( $idd )
            && count( $idd ) == 1
            && is_numeric( $idd[ 0 ] )
            && array_key_exists( $idd[ 0 ] - 1, $genres )
            && ( $edata = sqlite_media_getdata_filtered( $genres[ ( $idd[ 0 ] - 1 ) ], O_LIST_BIG_QUANTITY, $G_PAGE ) ) != FALSE 
            ){
                //1 element: only root or base items
                $items = new DIDL($idd[ 0 ]);
                //TODO icon
            }else{
                //X elements ???: BASE.idgenre.idmediainfo
                //NO BASE?
                return array('illegal'); 
            }
        }
        return array('Result'=>$items->getXML(), 'NumberReturned'=>1, 'TotalMatches'=>1, 'UpdateID'=>$this->SystemUpdateID);
    }

    protected function BrowseDirectChildren($req)
    {
        //TODO
        $items = new DIDL($req->ObjectID);
        $folderid = 0;
        $fileid = 0;
        global $genres;
        
        if ($req->ObjectID == DIDL::ROOT_ID) {
            //Base root
            foreach( $genres AS $gv => $gk ){
                $items->addFolder( $gk, sprintf("%d", ++$folderid))
                    ->creator('PHPMediaServer')
                    ->genre($gk)
                    ->artist('')
                    ->author('PHPMediaServer')
                    ->album('')
                    ->date(date( 'Y-m-d' ))
                    ->actor('')
                    ->director('')
                    ->icon( DLNA_WEB_BASEFOLDER . 'imgs/bg.jpg');
                    ;
            }
        } else {
            //Subfolders
            //NEW
            //get elements from genres and select number BASE=0.NUMBERFOLDERGENRE.IDMEDIAINFO
            $id = $req->ObjectID;
            $idd = explode( '.', $id );
            $G_PAGE = 0;
            
            if( is_array( $idd )
            && count( $idd ) == 1
            && is_numeric( $idd[ 0 ] )
            && array_key_exists( $idd[ 0 ] - 1, $genres )
            && ( $edata = sqlite_media_getdata_filtered( $genres[ ( $idd[ 0 ] - 1 ) ], O_LIST_BIG_QUANTITY, $G_PAGE ) ) != FALSE 
            ){
                //1 elements: BASE.idgenre
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                foreach( $edata as $f ){
                    $ct = finfo_file($finfo, $f[ 'file' ]);
                    $cls = DIDL::class_from_mime($ct);
                    if (!$cls) continue;
                    
                    $title = $f[ 'title' ];
                    if( $f[ 'season' ] > 0 ){
                        $title .= ' ' . sprintf( '%02d', $f[ 'season' ] ) . 'x' . sprintf( '%02d', $f[ 'episode' ] );
                    }
                    
                    $itm = $items->addItem($cls, $title, sprintf('%s.%d', $req->ObjectID, $f[ 'idmedia' ]));
                    $itm->resource(
                        DLNA_WEB_BASEFOLDER_HTTP . '?r=r&action=playtime&mode=direct&idmedia=' . $f[ 'idmedia' ] . '&PHPSESSION=' . get_dlna_user_session(), 
                        array(
                            'filesize' =>filesize( $f[ 'file' ] ),
                            'protocolInfo' => 'http-get:*:'.$ct.':*'
                        )
                    );
                    
                    /* TEST
                    $icon = DLNA_WEB_BASEFOLDER_HTTP . getURLImg( $f[ 'idmedia' ], FALSE, 'poster' );
                    if($icon) {
                        $itm->resource($icon, array('protocolInfo'=>'http-get:*:'.$ct.':'.get_dlna_profile($icon)));
                        $itm->icon($icon);
                    }
                    */
                }
                finfo_close($finfo);
                
            }elseif(  is_array( $idd )
            && count( $idd ) == 2
            && is_numeric( $idd[ 0 ] )
            && array_key_exists( $idd[ 0 ] - 1, $genres )
            //&& ( $edata = sqlite_media_getdata_filtered( $genres[ ( $idd[ 0 ] - 1 ) ], O_LIST_BIG_QUANTITY, $G_PAGE ) ) != FALSE 
            ){
                //2 elements: BASE.idgenre.???
                return array('illegal');
            }else{
                //X elements ???: BASE.idgenre.idmediainfo
                //NO BASE?
                return array('illegal'); 
            }
        }
        $totalMatches=$items->count;
        $items = $items->slice($req->StartingIndex, $req->RequestedCount);
        return array('Result'=>$items->getXML(), 'NumberReturned'=>$items->count, 'TotalMatches'=>$totalMatches, 'UpdateID'=>$this->SystemUpdateID);
    }

    function Browse($req) {
        if ($req->BrowseFlag == 'BrowseMetadata')
            return $this->BrowseMetadata($req);
        else
            return $this->BrowseDirectChildren($req);
    }

    function GetSystemUpdateID() {
        return array('Id'=>$this->SystemUpdateID);
    }

    function GetSearchCapabilities() {
//         return array('SearchCaps'=>'dc:creator,dc:title,upnp:album,upnp:actor,upnp:artist,upnp:class,upnp:genre,@refID');
        return array('SearchCaps'=>'');
    }
    function GetSortCapabilities() {
//         return array('SortCaps'=>'dc:title,dc:date,upnp:class,upnp:originalTrackNumber');
        return array('SortCaps'=>'');
    }

    /* From ConnectionManager.. but simple enough to handle it here */
    function GetProtocolInfo() {
        return array('Source' => file_get_contents('protocol_info.txt'), 'Sink'=>'');
    }
}

function move_namespace_to_first_user($soapXml, $ns='ns1')
{
    $marker1 = "xmlns:$ns=";
    $marker2 = "<$ns:";
    $startpos = strpos($soapXml, $marker1);
    $endpos = strpos($soapXml, "\"", $startpos + strlen($marker1) + 1);
    if ($startpos === FALSE) return $soapXml;

    $namespace = substr( $soapXml, $startpos, $endpos - $startpos + 1);

    $soapXml = str_replace(' '.$namespace, '', $soapXml);

    $insertpos = strpos($soapXml, '>', strpos($soapXml, $marker2));

    $soapXml = substr_replace( $soapXml, ' '.$namespace, $insertpos, 0 );
    return $soapXml;
}

$headers=array_change_key_case(getallheaders());
$body = @file_get_contents('php://input');

$srv = new SoapServer("wsdl/upnp_av.wsdl");
$srv->setClass('ContentDirectory');

ob_start();
$srv->handle($body);
$soapXml = ob_get_contents();
ob_end_clean();

$soapXml = str_replace('<SOAP-ENV:Envelope', '<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"', $soapXml);
$soapXml = move_namespace_to_first_user($soapXml);

$length = strlen($soapXml);
header("Content-Length: ".$length);
echo $soapXml;
?>
