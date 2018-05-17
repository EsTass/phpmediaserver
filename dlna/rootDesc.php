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
    
    //DLNA
	require( PPATH_CORE . DS . 'functions.dlna.php' );
    
    if( !defined( 'DLNA_ACTIVE' ) 
    || DLNA_ACTIVE == FALSE
    ){
        die( 'NODLNA' );
    }

    header("Content-type: text/xml; charset=utf-8");
?>
<?xml version="1.0"?>
<root xmlns="urn:schemas-upnp-org:device-1-0" xmlns:dlna="urn:schemas-dlna-org:device-1-0">
<specVersion>
<major>1</major>
<minor>0</minor>
</specVersion>
<device>
<deviceType>urn:schemas-upnp-org:device:MediaServer:1</deviceType>
<friendlyName>PHPMediaServer</friendlyName>
<manufacturer>noemail@noemail.com</manufacturer>
<manufacturerURL>https://github.com/EsTass/phpmediaserver</manufacturerURL>
<modelDescription>PHPMediaServer</modelDescription>
<modelName>PHPMediaServer</modelName>
<modelNumber>1</modelNumber>
<modelURL>http://127.0.0.1/modelURL</modelURL>
<serialNumber>87654321</serialNumber>
<UDN>uuid:<?php echo dlna_get_uuidStr(); ?></UDN>
<dlna:X_DLNADOC>DMS-1.50</dlna:X_DLNADOC>
<serviceList>
<service>
<serviceType>urn:schemas-upnp-org:service:ContentDirectory:1</serviceType>
<serviceId>urn:upnp-org:serviceId:ContentDirectory</serviceId>
<controlURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cdCtrl.php</controlURL>
<eventSubURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cdEvent.php</eventSubURL>
<SCPDURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cdScpd.xml</SCPDURL>
</service>
<service>
<serviceType>urn:schemas-upnp-org:service:ConnectionManager:1</serviceType>
<serviceId>urn:upnp-org:serviceId:ConnectionManager</serviceId>
<controlURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cmCtrl.php</controlURL>
<eventSubURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cmEvent.php</eventSubURL>
<SCPDURL><?php echo DLNA_WEB_BASEPATH_HTTP; ?>/dlna/cmScpd.xml</SCPDURL>
</service>
</serviceList>
</device>
<URLBase><?php echo DLNA_WEB_BASESERVER_HTTP; ?></URLBase>
</root>
