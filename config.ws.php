<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//DD domain helpers

	$G_DD_RE = '/(mega\.nz|streamcloud\.eu|rapidvideo\.com|ok\.ru|streamplay\.to|openload\.co|1fichier\.com|uptobox\.com|uploaded\.net|dfiles\.eu|filefactory\.com|rapidgator\.net|streamango\.com|hqq\.watch|vidoza\.net|streamplay\.me|streamplay\.to|powvideo\.net|up\.to|flashx\.tv|waaw\.tv|gamovideo\.com)/';
	
	$G_DD_BLOCKS = array(
        'https://mega.nz/',
        'https://streamcloud.eu',
        'https://www.rapidvideo.com/',
        'http://ok.ru/',
        'https://streamplay.to',
        'https://openload.co/',
        'https://1fichier.com/',
        'https://uptobox.com',
        'https://uploaded.net/',
        'https://dfiles.eu/',
        'https://filefactory.com/',
        'https://rapidgator.net/',
        'https://streamango.com/',
        'https://hqq.watch/',
        'https://vidoza.net/',
        'https://streamplay.me/',
        'https://streamplay.to/',
        'https://powvideo.net/',
        'https://up.to/',
        'https://flashx.tv/',
        'https://waaw.tv/',
        'https://gamovideo.com/',
	);
	
	$G_WEBSCRAPPER = array(
		
        //BASIC DirectDownloads For externals downloaders (like paste links action)
        //Linked to Jdownloader, can be changed on downloadfunction option
        
        //dd all
        'ddall' => array(
            //Type: torrent|amule|magnets or function
            'type' => 'torrent',
            //Title: domain.com
            'title' => 'dd all (nosearch)',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 0,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => FALSE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                'Subs',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => '',
                //Web URL to baselist: 'torrents.com/'
                'urlbase' => '',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => '',
                //html object have links: a
                'linksobject' => 'a',
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 0,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => '',
                'filtersizetextpos' => '',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 0,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    //'urlvalid' => 'mega.nz/',
                    'urlvalidpreg' => $G_DD_RE,
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                            
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                            
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => 0,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                    //DOWNLOAD function
                    'downloadfunction' => 'jdownloader_downloader',
                ),
            ),
        ),
        
		//ThePirateBay.org
		'ThePirateBayOrg' => array(
		    //Type: torrent|amule|magnets
		    'type' => 'magnets',
		    //Title: domain.com
		    'title' => 'ThePirateBay.org',
		    //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
		    'passnumber' => 2,
		    //HTML Code Format: UTF-8, ANSI, ...
		    'htmlformat' => 'UTF-8',
		    //Check Duplicates: search if file media title exist and cancel download
		    'duplicatescheck' => FALSE,
		    //Title Clean, remove strings from title for duplicates scan
		    'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                'Integrados',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
		    ),
		    //Search Data In Web
		    'searchdata' => array(
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => 'https://thepiratebay.org/search/',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'https://thepiratebay.org/browse/200',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => 'https://thepiratebay.org',
			//html object have links: a
			'linksobject' => 'a',
			//String needed in linkTitle to be valid
			'linktitleneeded' => array(),
			//String needed in linkURL to be valid
			'linkurlneeded' => array(
			    '/torrent/',
			),
			//String Exclude in linkTitle to be valid
			'linktitleexclude' => array(
			    '4K', 
			    'FullBluRay', 
			    'BDRemux',
			),
			//String Exclude in linkURL to be valid
			'linkurlexclude' => array(),
			//FILTER SIZE
			//Max File Size: 0 disabled|X megabytes
			'filtersizemax' => 20000,
			//FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
			'filtersizetextpre' => ', Size',
			'filtersizetextpos' => ', Uled',
			//FILTER SIZE: max distance from link
			'filtersizetextdistance' => 1200,
			//FILTER SIZE: especific size(MB)=function( $html )
			'filtersizefunction' => '',
		    ),
		    //Pass Config
		    'passdata' => array(
			//Pass 1 Links
			0 => array(
			    //Needed In URL to be valid, if pass not valid search for valid pass to launch
			    'urlvalid' => '/torrent/',
			    //Next pass: int|FALSE, if FALSE try to download file
			    'passnext' => 1,
			    //URL Append to links: add to links for incomplete URLs: domain.com/
			    'linksappend' => '',
			    //html object have links: a
			    'linksobject' => 'a',
			    //String needed in linkTitle to be valid
			    'linktitleneeded' => array(
			    ),
			    //String needed in linkURL to be valid
			    'linkurlneeded' => array(
                    'magnet:'
			    ),
			    //String Exclude in linkTitle to be valid
			    'linktitleexclude' => array(
                    ''
			    ),
			    //String Exclude in linkURL to be valid
			    'linkurlexclude' => array(),
			    //FILTER SIZE
			    //Max File Size: 0 disabled|X megabytes
			    'filtersizemax' => 20000,
			    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
			    'filtersizetextpre' => 'Size: ',
			    'filtersizetextpos' => ' (',
			    //FILTER SIZE: max distance from link
			    'filtersizetextdistance' => 1200,
			    //FILTER SIZE: especific size(MB)=function( $html )
			    'filtersizefunction' => '',
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			),
			//Pass 2 Add magnet
			1 => array(
			    //Needed In URL to be valid, if pass not valid search for valid pass to launch
			    'urlvalid' => 'magnet:',
			    //Next pass: int|FALSE, if FALSE try to download file
			    'passnext' => FALSE,
			    //URL Append to links: add to links for incomplete URLs: domain.com/
			    'linksappend' => '',
			    //html object have links: a
			    'linksobject' => 'a',
			    //String needed in linkTitle to be valid
			    'linktitleneeded' => array(
			    ),
			    //String needed in linkURL to be valid
			    'linkurlneeded' => array(
			    ),
			    //String Exclude in linkTitle to be valid
			    'linktitleexclude' => array(
			    ),
			    //String Exclude in linkURL to be valid
			    'linkurlexclude' => array(),
			    //FILTER SIZE
			    //Max File Size: 0 disabled|X megabytes
			    'filtersizemax' => 0,
			    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
			    'filtersizetextpre' => '',
			    'filtersizetextpos' => '',
			    //FILTER SIZE: especific size(MB)=function( $html )
			    'filtersizefunction' => '',
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			),
		    ),
		),
		
		//Basic Youtube Example, search and download
		
		//youtube.com
		'Youtube.com' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'YouTube.com',
		    //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
		    'passnumber' => 2,
		    //HTML Code Format: UTF-8, ANSI, ...
		    'htmlformat' => 'UTF-8',
		    //Check Duplicates: search if file media title exist and cancel download
		    'duplicatescheck' => FALSE,
		    //Title Clean, remove strings from title for duplicates scan
		    'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                'Integrados',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
		    ),
		    //Search Data In Web
		    'searchdata' => array(
			//Own search function
			'searchfunction' => 'youtube_search',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => 'https://www.youtube.com/results?search_query=',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'https://www.youtube.com/',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => 'https://www.youtube.com',
			//html object have links: a
			'linksobject' => 'a',
			//String needed in linkTitle to be valid
			'linktitleneeded' => array(),
			//String needed in linkURL to be valid
			'linkurlneeded' => array(
			    '/watch?v=',
			),
			//String Exclude in linkTitle to be valid
			'linktitleexclude' => array(
			    '4K', 
			    'FullBluRay', 
			    'BDRemux',
			    '',
			),
			//String Exclude in linkURL to be valid
			'linkurlexclude' => array(),
			//FILTER SIZE
			//Max File Size: 0 disabled|X megabytes
			'filtersizemax' => 0,
			//FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
			'filtersizetextpre' => '',
			'filtersizetextpos' => '',
			//FILTER SIZE: max distance from link
			'filtersizetextdistance' => 0,
			//FILTER SIZE: especific size(MB)=function( $html )
			'filtersizefunction' => '',
		    ),
		    //Pass Config
		    'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => '/watch?v=',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => 'https://www.youtube.com',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
    
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
    
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => 0,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                    //DOWNLOAD function
                    'downloadfunction' => 'youtube_download',
                ),
		    ),
		),

		                
        //Example: https://www.limetorrents.info
        'limetorrents' => array(
            //Type: torrent|amule|magnets
            'type' => 'magnets',
            //Title: domain.com
            'title' => 'limetorrents.info',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 2,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => FALSE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => 'https://www.limetorrents.info/search/all/',
                //Web URL to baselist: 'torrents.com/'
                'urlbase' => 'https://www.limetorrents.info/home/',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => 'https://www.limetorrents.info/',
                //html object have links: a
                'linksobject' => 'a',
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                    '-torrent-',
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                    '4K', 
                    'FullBluRay', 
                    'BDRemux',
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 2000,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => '</a></td><td class="tdnormal">',
                'filtersizetextpos' => '</td>',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 400,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => '-torrent-',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => 1,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                        'magnet:'
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                        '',
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 2000,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '<b>Size</b> :</td><td>',
                    'filtersizetextpos' => '</td>',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => -2500,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
                //Pass 2 Add magnet
                1 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'magnet:',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
            ),
        ),
    
        //idope.se
        'idope' => array(
            //Type: torrent|amule|magnets
            'type' => 'magnets',
            //Title: domain.com
            'title' => 'idope.se',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 2,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => TRUE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => 'https://idope.se/torrent-list/',
                //Web URL to baselist: 'torrents.com/' https://idope.se/torrent-list/la%20que%20se%20avecina/
                'urlbase' => 'https://idope.se/browse.html?&c=2',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => 'https://idope.se',
                //html object have links: a
                'linksobject' => 'a',
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                    '/torrent/',
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                    '4K', 
                    'FullBluRay', 
                    'BDRemux',
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 2000,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => 'resultdivbottonlength">',
                'filtersizetextpos' => '</div>',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 1200,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => '/torrent/',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => 1,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                        'magnet:'
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                        ''
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 2000,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => 'infotagtext2">',
                    'filtersizetextpos' => '</div>',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => 1200,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
                //Pass 2 Add magnet
                1 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'magnet:',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
            ),
        ),
        
        //monova.org
        'monovaorg' => array(
            //Type: torrent|amule|magnets
            'type' => 'magnets',
            //Title: domain.com
            'title' => 'monova.org',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 1,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => TRUE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => 'https://monova.org/search?term=',
                //Web URL to baselist: 'torrents.com/'
                'urlbase' => 'https://monova.org/search?term=',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => 'https:',
                //html object have links: a
                'linksobject' => 'a',
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(
                    '',
                ),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                    'monova.org/',
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                    '4K', 
                    'FullBluRay', 
                    'BDRemux',
                    'FullBluRay', 
                    'BDRemux',
                    'BluRay', 
                    'BlueRay', 
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(
                    'FullBluRay', 
                    'BDRemux',
                    'BluRay', 
                    'BlueRay', 
                    'search/', 
                ),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 2000,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => 'align">',
                'filtersizetextpos' => '</td>',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 400,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'monova.org/',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => 1,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                        'magnet:'
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                        '',
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(
                        '',
                    ),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 2000,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '<td>',
                    'filtersizetextpos' => '</td>',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => -2000,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
                //Pass 2 Add magnet
                1 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'magnet:',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
            ),
        ),
        
        //bt4g.com
        'bt4gcom' => array(
            //Type: torrent|amule|magnets
            'type' => 'magnets',
            //Title: domain.com
            'title' => 'bt4g.com',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 1,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => TRUE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => 'https://bt4g.com/search/',
                //Web URL to baselist: 'torrents.com/'
                'urlbase' => 'https://bt4g.com/',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => 'https://bt4g.com',
                //html object have links: a
                'linksobject' => 'a',
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(
                    '',
                ),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                    '/magnet/',
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                    '4K', 
                    'FullBluRay', 
                    'BDRemux',
                    'FullBluRay', 
                    'BDRemux',
                    'BluRay', 
                    'BlueRay', 
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(
                    'FullBluRay', 
                    'BDRemux',
                    'BluRay', 
                    'BlueRay', 
                    'search/', 
                ),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 4000,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => 'yellow-pill">',
                'filtersizetextpos' => '</b>',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 800,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => '/magnet/',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => 1,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                        'magnet:'
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                        '',
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(
                        '',
                    ),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 4000,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '<td>',
                    'filtersizetextpos' => 'B</td>',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => -1000,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
                //Pass 2 Add magnet
                1 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'magnet:',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
            ),
        ),
        
        //https://solidtorrents.net
        'solidtorrents.net' => array(
            //Type: torrent|amule|magnets
            'type' => 'magnets',
            //Title: domain.com
            'title' => 'solidtorrents.net',
            //Pass needed to get torrent/amule, from base page search, 1 pass if torrent/amule in next, 2 if hava second page to link, ...
            'passnumber' => 1,
            //HTML Code Format: UTF-8, ANSI, ...
            'htmlformat' => 'UTF-8',
            //Check Duplicates: search if file media title exist and cancel download
            'duplicatescheck' => FALSE,
            //Title Clean, remove strings from title for duplicates scan
            'titleclean' => array(
                ':',
                'TS-Screeener',
                'TSScreeener',
                'TV-Screeener',
                'TVScreeener',
                'La-Screeener',
                'LaScreeener',
                'Screeener',
                'TS-Screener',
                'TSScreener',
                'TV-Screener',
                'TVScreener',
                'La-Screener',
                'LaScreener',
                'Screener',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
                'Subs',
                'Integrados',
                '[',
                ']',
                'FullBluRay', 
                'BDRemux',
                'BluRay', 
                'BlueRay', 
                'MicroHD', 
                'DVD',
                'HD',
                'DVD',
                'DVDRip',
                '1080',
                '720',
                '1080p',
                '720p',
                '..',
                '...',
            ),
            //Search Data In Web
            'searchdata' => array(
                //Own search function
                'searchfunction' => '',
                //Web URL to search: 'torrents.com/?q='
                'urlsearch' => 'https://solidtorrents.net/search?sort=date&category=Video&fuv=yes&q=',
                //Web URL to baselist: 'torrents.com/'
                'urlbase' => 'https://solidtorrents.net/search?sort=date&category=Video&fuv=yes',
                //URL Append to links: add to links for incomplete URLs: domain.com/
                'linksappend' => 'https://solidtorrents.net',
                //html object have links: a
                'linksobject' => 'a',
                //links title alternative extraction mode
                //'linkstitle' => array( 'inurl', '/view/', '/' ),
                //'linkstitle' => array( 'inhtml', -200, ' title="', '" rel=' ),
                //Extract image from search and use on list
                //'linksimage' => array( 'inhtml', 800, 'src="', '" border' ),
                //'linksimage' => array( 'near' ),
                //String needed in linkTitle to be valid
                'linktitleneeded' => array(
                    '',
                ),
                //String needed in linkURL to be valid
                'linkurlneeded' => array(
                    '/view/',
                ),
                //String Exclude in linkTitle to be valid
                'linktitleexclude' => array(
                    
                ),
                //String Exclude in linkURL to be valid
                'linkurlexclude' => array(
                    'search/', 
                ),
                //FILTER SIZE
                //Max File Size: 0 disabled|X megabytes
                'filtersizemax' => 0,
                //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                'filtersizetextpre' => '">',
                'filtersizetextpos' => '</strong>',
                //FILTER SIZE: max distance from link
                'filtersizetextdistance' => 800,
                //FILTER SIZE: especific size(MB)=function( $html )
                'filtersizefunction' => '',
            ),
            //Pass Config
            'passdata' => array(
                //Pass 1 Links
                0 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => '/view/',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => 1,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                        'magnet:'
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                        'Latino',
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(
                        'Latino',
                    ),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => ', ',
                    'filtersizetextpos' => '</span>',
                    //FILTER SIZE: max distance from link
                    'filtersizetextdistance' => -2000,
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
                //Pass 2 Add magnet
                1 => array(
                    //Needed In URL to be valid, if pass not valid search for valid pass to launch
                    'urlvalid' => 'magnet:',
                    //Next pass: int|FALSE, if FALSE try to download file
                    'passnext' => FALSE,
                    //URL Append to links: add to links for incomplete URLs: domain.com/
                    'linksappend' => '',
                    //html object have links: a
                    'linksobject' => 'a',
                    //String needed in linkTitle to be valid
                    'linktitleneeded' => array(
                    ),
                    //String needed in linkURL to be valid
                    'linkurlneeded' => array(
                    ),
                    //String Exclude in linkTitle to be valid
                    'linktitleexclude' => array(
                    ),
                    //String Exclude in linkURL to be valid
                    'linkurlexclude' => array(),
                    //FILTER SIZE
                    //Max File Size: 0 disabled|X megabytes
                    'filtersizemax' => 0,
                    //FILTER SIZE: get size between text: textpre + XX.XX Gb|Mb + textpos
                    'filtersizetextpre' => '',
                    'filtersizetextpos' => '',
                    //FILTER SIZE: especific size(MB)=function( $html )
                    'filtersizefunction' => '',
                    //DOWNLOAD MULTIPLE
                    'downloadmultiple' => FALSE,
                ),
            ),
        ),
        
    );
	
?>
