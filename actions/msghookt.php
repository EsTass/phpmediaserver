<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//r
	//action
	//saction = setwhtelegram|
	
	//Check Valid IP for Telegram webhook
	$validips = array();
	msgExternalIPs( $validips );
	
	if( array_key_exists( 'r', $G_DATA )
	&& !in_array( USER_IP, $validips ) 
	){
        header("HTTP/1.1 401 Unauthorized");
		echo "HTTP/1.1 401 Unauthorized";
		//10 years ban
		addBannedIP( USER_IP, 'webhookt ip invalid', ( 10 * 24 * 365 ) );
		sendMessage( 'IPBAN', 'webhookt ' . USER_IP );
		sqlite_log_insert( 'BANNEDIP-WebHook', 'TRY TO ACCESS: ' . USER_IP );
		exit();
	}else{
        //valid ip
        //on full load set webhook and show log, on partial load check push command
        if( array_key_exists( 'r', $G_DATA ) ){
            //partial load check command
            
            if( ( $mdata = @file_get_contents( 'php://input' ) ) != FALSE
            && ( $msgdata = @json_decode( $mdata, TRUE ) ) != FALSE
            && is_array( $msgdata )
            && array_key_exists( 'message', $msgdata )
            && is_array( $msgdata[ 'message' ] )
            && array_key_exists( 'chat', $msgdata[ 'message' ] )
            && is_array( $msgdata[ 'message' ][ 'chat' ] )
            && array_key_exists( 'id', $msgdata[ 'message' ][ 'chat' ] )
            && array_key_exists( 'text', $msgdata[ 'message' ] )
            && $msgdata[ "message" ][ "chat" ][ "id" ] == O_SEND_EXT_TELEGRAM_CHATID
            ){
                $chatId = $msgdata[ "message" ][ "chat" ][ "id" ];
                $message = $msgdata[ "message" ][ "text" ];
                switch( $message ){
                    case "/help":
                        $msg = "\n";
                        $msg .= 'Command List: ';
                        $msg .= "\n Command List: ";
                        $msg .= "\n /help : this help ";
                        $msg .= "\n /test : check command ";
                        $msg .= "\n /hi : check command ";
                        $msg .= "\n /log : get last 10 logs entrys ";
                        $msg .= "\n /fileslast : get last 20 files ";
                        $msg .= "\n /filesdl searchtext : set to donwload searchtext ";
                        $msg .= "\n /ipcheck IP : get status of IP ";
                        $msg .= "\n /ipban IP : ban IP ";
                        $msg .= "\n /ipunban IP : unban IP ";
                        $msg .= "\n /playnow : get list of playing items ";
                        $msg .= "\n /playlast : get list of played items ";
                        sendMessageTelegram( $msg );
                        break;
                    case "/test":
                        $msg = "\n";
                        $msg .= 'test OK';
                        sendMessageTelegram( $msg );
                        break;
                    case "/hi":
                        $msg = "\n";
                        $msg .= 'Hi!';
                        sendMessageTelegram( $msg );
                        break;
                    case "/log":
                        $msg = "\n";
                        $max = 5;
                        $now = 1;
                        if( ( $ldata = sqlite_log_getdata() ) != FALSE ){
                            foreach( $ldata AS $row ){
                                if( $row[ 'action' ] != 'msghookt' ){
                                    $msg .= $now . '/' . $max . "\n";
                                    $msg .= $row[ 'date' ] . "\n";
                                    $msg .= $row[ 'user' ] . "\n";
                                    $msg .= $row[ 'action' ] . "\n";
                                    $msg .= $row[ 'ip' ] . "\n";
                                    $msg .= $row[ 'description' ] . "\n";
                                    $msg .= "---\n";
                                    $now++;
                                    if( $now >= $max ){
                                        break;
                                    }
                                }
                            }
                        }
                        if( strlen( $msg ) <= 2 ){
                            $msg = 'No Data';
                        }
                        sendMessageTelegram( $msg );
                        break;
                    case "/fileslast":
                        $msg = "\n";
                        $search = '';
                        $max = 20;
                        $now = 1;
                        if( ( $ldata = sqlite_media_getdata_identify_added( $search, $max ) ) != FALSE ){
                            foreach( $ldata AS $row ){
                                $msg .= $now . '/' . $max . "\n";
                                $msg .= $row[ 'title' ] . ' (' . $row[ 'year' ] . ') ';
                                if( strlen( $row[ 'season' ] ) > 0 ){
                                    $msg .= $row[ 'season' ] . 'x' . $row[ 'episode' ] . '';
                                }
                                $msg .= "\n";
                                $now++;
                            }
                        }
                        if( strlen( $msg ) <= 2 ){
                            $msg = 'No Data';
                        }
                        sendMessageTelegram( $msg );
                        break;
                    case "/playnow":
                        $msg = "\n";
                        $max = 10;
                        $now = 1;
                        if( ( $ldata = sqlite_playing_getdata( FALSE, $max, TRUE ) ) != FALSE ){
                            foreach( $ldata AS $row ){
                                $msg .= $now . '/' . $max . "\n";
                                $msg .= $row[ 'date' ] . "\n";
                                $msg .= $row[ 'user' ] . "\n";
                                $msg .= $row[ 'mode' ] . "\n";
                                $msg .= $row[ 'title' ] . "";
                                if( strlen( $row[ 'season' ] ) > 0 ){
                                    $msg .= $row[ 'season' ] . 'x' . $row[ 'episode' ] . '';
                                }
                                $msg .= "\n";
                                $msg .= "---\n";
                                $now++;
                            }
                        }
                        if( strlen( $msg ) <= 2 ){
                            $msg = 'No Data';
                        }
                        sendMessageTelegram( $msg );
                        break;
                    case "/playlast":
                        $msg = '';
                        $max = 10;
                        $now = 1;
                        if( ( $ldata = sqlite_played_getdata_ext( FALSE, '', FALSE, $max ) ) != FALSE ){
                            foreach( $ldata AS $row ){
                                $msg .= $now . '/' . $max . "\n";
                                $msg .= $row[ 'date' ] . "\n";
                                $msg .= $row[ 'user' ] . "\n";
                                $msg .= secondsToTimeFormat( $row[ 'now' ] ) . "/" . secondsToTimeFormat( $row[ 'max' ] ) . "\n";
                                $msg .= $row[ 'title' ] . "";
                                if( strlen( $row[ 'season' ] ) > 0 ){
                                    $msg .= $row[ 'season' ] . 'x' . $row[ 'episode' ] . '';
                                }
                                $msg .= "\n";
                                $msg .= "---\n";
                                $now++;
                            }
                        }
                        if( strlen( $msg ) <= 2 ){
                            $msg = 'No Data';
                        }
                        sendMessageTelegram( $msg );
                        break;
                    default:
                        //extra params actions
                        if( startsWith( $message, '/filesdl ' ) ){
                            //set search param for download by adding to log search
                            $stext = str_ireplace( '/files dl ', '', $message );
                            sqlite_log_insert( 'list-webhook', 'adding download search: ' . $stext, '?action=list&search=' . urlencode( $stext ) );
                            sendMessageTelegram( " Added to dl: " . $stext );
                        }elseif( startsWith( $message, '/ipcheck ' ) ){
                            //check ip ban status
                            $stext = str_ireplace( '/ipcheck ', '', $message );
                            if( checkWhitedIP( $stext ) ){
                                sendMessageTelegram( " IP ON WHITELIST: " . $stext );
                            }elseif( checkBannedIP( $stext ) ){
                                sendMessageTelegram( " IP BANNED: " . $stext );
                            }else{
                                sendMessageTelegram( " UNCHECKED IP: " . $stext );
                            }
                        }elseif( startsWith( $message, '/ipban ' ) ){
                            //set ip ban
                            $stext = str_ireplace( '/ipban ', '', $message );
                            if( addBannedIP( $stext, 'webhookt banned action', ( 10 * 24 * 365 ) ) ){
                                sendMessageTelegram( " IP BANNED: " . $stext );
                            }else{
                                sendMessageTelegram( " Error BAN: " . $stext );
                            }
                        }elseif( startsWith( $message, '/ipunban ' ) ){
                            //set ip unban
                            $stext = str_ireplace( '/ipunban ', '', $message );
                            sqlite_bans_delete( $stext );
                            if( addWhitedIP( $stext ) 
                            ){
                                sendMessageTelegram( " IP UNBANNED: " . $stext );
                            }else{
                                sendMessageTelegram( " Error UNBAN: " . $stext );
                            }
                        }else{
                            sendMessageTelegram( " Invalid Action: " . $message );
                        }
                }
            }else{
                $idatainfo = 'No Info.';
                if( !isset( $mdata ) 
                || !is_string( $mdata )
                || strlen( $mdata ) == 0
                ){
                    $idatainfo = 'No Data.';
                }elseif( !isset( $msgdata ) 
                || !is_array( $msgdata )
                ){
                    $idatainfo = 'No JSON Data.';
                }elseif( !array_key_exists( 'message', $msgdata )
                || !is_array( $msgdata[ 'message' ] )
                ){
                    $idatainfo = 'No message Data.';
                }elseif( !array_key_exists( 'chat', $msgdata[ 'message' ] )
                || !is_array( $msgdata[ 'message' ][ 'chat' ] )
                ){
                    $idatainfo = 'No chat Data.';
                }elseif( !array_key_exists( 'id', $msgdata[ 'message' ][ 'chat' ] )
                ){
                    $idatainfo = 'No chat-id Data.';
                }elseif( !array_key_exists( 'text', $msgdata[ 'message' ] )
                ){
                    $idatainfo = 'No chat-text Data.';
                }elseif( $msgdata[ "message" ][ "chat" ][ "id" ] != O_SEND_EXT_TELEGRAM_CHATID 
                ){
                    $idatainfo = 'Invalid Chat id: ' . $msgdata[ "message" ][ "chat" ][ "id" ];
                }
                sqlite_log_insert( 'webhookt', 'ERROR Incomplete Data: ' . USER_IP . ' -> ' . $idatainfo );
            }
            
            if( defined( 'O_SEND_EXT_TELEGRAM_LOG' )
            && O_SEND_EXT_TELEGRAM_LOG == TRUE
            && isset( $mdata ) 
            && is_string( $mdata )
            ){
                logWebHookTelegram( $mdata );
            }
        }else{
            //Check admin
            check_mod_admin();
            $msgaction = '';
            //set webhook
            if( array_key_exists( 'saction', $G_DATA ) ){
                switch( $G_DATA[ 'saction' ] ){
                    case 'setwhtelegram':
                        removeWebHookTelegram();
                        if( setWebHookTelegram() ){
                            $msgaction =  "<br />Telegram WebHook set to: " . O_SEND_EXT_TELEGRAM_WEBHOOKURL;
                        }else{
                            $msgaction =  "<br />ERROR Telegram WebHook: " . O_SEND_EXT_TELEGRAM_WEBHOOKURL;
                        }
                        break;
                    case 'delwhtelegram':
                        removeWebHookTelegram();
                        $msgaction = 'WebHook Deleted.';
                        break;
                    case 'dellogtel':
                        $msgaction = 'Telegram LOG Deleted.';
                        logWebHookTelegramDel();
                        break;
                    default:
                }
            }
            //sep
            echo "<br /><br />";
            //show link to set webhook
            if( defined( 'O_SEND_EXT_TELEGRAM_TOKEN' ) 
            && defined( 'O_SEND_EXT_TELEGRAM_CHATID' ) 
            && defined( 'O_SEND_EXT_TELEGRAM_WEBHOOKURL' )
            && is_string( O_SEND_EXT_TELEGRAM_TOKEN )
            && strlen( O_SEND_EXT_TELEGRAM_TOKEN ) > 0
            && is_string( O_SEND_EXT_TELEGRAM_CHATID )
            && strlen( O_SEND_EXT_TELEGRAM_CHATID ) > 0
            && is_string( O_SEND_EXT_TELEGRAM_WEBHOOKURL )
            && strlen( O_SEND_EXT_TELEGRAM_WEBHOOKURL ) > 0
            ){
                echo "<div style='display:block; width: 100%;height: auto; border: 1px solid dimgray;background-color: gray;text-align: center;padding: 5px;'>";
                echo "<a class='aIdentSearchResult' style='padding: 5px;background-color: #F3F3F3;' href='?action=msghookt&saction=setwhtelegram' title='SET TO: " . O_SEND_EXT_TELEGRAM_WEBHOOKURL . "'>SET WEBHOOK</a>";
                echo "&nbsp;&nbsp;";
                echo "<a class='aIdentSearchResult' style='padding: 5px;background-color: #F3F3F3;' href='?action=msghookt&saction=delwhtelegram'>DELETE WEBHOOK</a>";
                echo "&nbsp;&nbsp;";
                echo "<a class='aIdentSearchResult' style='padding: 5px;background-color: #F3F3F3;' href='?action=msghookt&saction=dellogtel'>CLEAN LOG</a>";
                echo "</div>";
            }
            if( strlen( $msgaction ) > 0
            ){
                echo "<div style='display:block; width: 100%;height: auto; border: 1px solid dimgray;background-color: green;text-align: center;padding: 5px;'>";
                echo $msgaction;
                echo "</div>";
            }
            //show log
            echo "<div style='display:block; width: 100%;height: auto; border: 1px solid dimgray;background-color: gray;text-align: center;padding: 5px;'>";
            echo "TELEGRAM LOG";
            echo "</div>";
            echo "<div style='width: 100%;border: 1px solid dimgray;background-color: gray;padding: 5px;'>";
            $log = str_ireplace( "\n", "<br />", logWebHookTelegramGet() );
            $log = str_ireplace( "\t", "&nbsp;&nbsp;", $log );
            echo $log;
            echo "</div>";
            //sep
            echo "<br /><br />";
        }
	}
	
?>

