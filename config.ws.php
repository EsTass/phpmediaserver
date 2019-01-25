<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	$G_WEBSCRAPPER = array(
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
			    'Latino',
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
				'Latino'
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
			    'Latino',
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
                                'Latino',
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
		
		//BASIC DirectDownloads For externals downloaders (like paste links action)
		//Linked to Jdownloader, can be changed on downloadfunction option

		//dd
		'dd1' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'dd mega (NO SEARCH)',
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
			'searchfunction' => '',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => '',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'mega.nz',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => '',
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
			    'Latino',
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
			    'urlvalid' => 'mega.nz',
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
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			    //DOWNLOAD function
			    'downloadfunction' => 'jdownloader_downloader',
			),
		    ),
		),

		//dd
		'dd2' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'dd2 1fichier (no buscar)',
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
			'searchfunction' => '',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => '',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => '1fichier.com',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => '',
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
			    'Latino',
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
			    'urlvalid' => '1fichier.com',
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
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			    //DOWNLOAD function
			    'downloadfunction' => 'jdownloader_downloader',
			),
		    ),
		),

		//dd3
		'dd3' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'dd3 openload (NO SEARCH)',
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
			'searchfunction' => '',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => '',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'openload.co',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => '',
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
			    'Latino',
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
			    'urlvalid' => 'openload.co',
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
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			    //DOWNLOAD function
			    'downloadfunction' => 'jdownloader_downloader',
			),
		    ),
		),

		//dd4
		'dd4' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'dd4 streamcloud (NO SEARCH)',
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
			'searchfunction' => '',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => '',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'streamcloud.eu',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => '',
			//html object have links: a
			'linksobject' => 'a',
			//String needed in linkTitle to be valid
			'linktitleneeded' => array(),
			//String needed in linkURL to be valid
			'linkurlneeded' => array(
			    'streamcloud.eu',
			),
			//String Exclude in linkTitle to be valid
			'linktitleexclude' => array(
			    '4K', 
			    'FullBluRay', 
			    'BDRemux',
			    'Latino',
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
			    'urlvalid' => 'streamcloud.eu',
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
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			    //DOWNLOAD function
			    'downloadfunction' => 'jdownloader_downloader',
			),
		    ),
		),

		//dd5
		'dd5' => array(
		    //Type: torrent|amule|magnets or function
		    'type' => 'torrent',
		    //Title: domain.com
		    'title' => 'dd4 streamplay.me (NO SEARCH)',
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
			'searchfunction' => '',
			//Web URL to search: 'torrents.com/?q='
			'urlsearch' => '',
			//Web URL to baselist: 'torrents.com/'
			'urlbase' => 'streamplay.me',
			//URL Append to links: add to links for incomplete URLs: domain.com/
			'linksappend' => '',
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
			    'Latino',
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
			    'urlvalid' => 'streamplay.me',
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
			    //DOWNLOAD MULTIPLE
			    'downloadmultiple' => FALSE,
			    //DOWNLOAD function
			    'downloadfunction' => 'jdownloader_downloader',
			),
		    ),
		),

	
	);
	
?>
