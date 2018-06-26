<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	$G_LANGUAGE = array(
        'DEF_NOTSPECIFIED' => 'No especificado ',
        'DEF_FILENOTEXIST' => 'El archivo no existe ',
        'DEF_EXIST' => 'Elemento existente.',
        'DEF_NOTEXIST' => 'Elemento inexistente.',
        'DEF_DELETED' => 'Elemento Eliminado.',
        'DEF_DELETED_ERROR' => 'Error eliminando el elemento.',
        'DEF_COPYOK' => 'Elemento copiado: ',
        'DEF_COPYKO' => 'Error copiando el elemento: ',
        'DEF_EMPTYLIST' => 'Sin elementos que mostrar',
        'DEF_ELEMENTUPDATED' => 'Elemento actualizado ',
        'DEF_LOADING' => 'Obteniendo Datos... ',
	
        'LOGIN_ERRUSERPASS' => 'Error usuario/clave.',
        'LOGIN_NEEDED' => 'Es necesario Identificarse.',
        
        'LOGIN_FORM_TITLE' => 'Acceso',
        'LOGIN_FORM_USER' => 'Usuario',
        'LOGIN_FORM_PASS' => 'Clave',
        'LOGIN_FORM_BUTTON' => 'Login',
        
        'MENU_HOME_MS' => 'Menu',
        'MENU_HOME' => 'Home',
        'MENU_LOGOUT' => 'Salir',
        'MENU_SEARCH' => 'Buscar',
        'MENU_LOG' => 'Logs',
        'MENU_IDENTIFY' => 'Identificar',
        'MENU_IDENTIFY_AUTO' => 'Identificar Auto',
        'MENU_LOGMEDIA' => 'LogMedia',
        'MENU_LOGMEDIAINFO' => 'LogMediaInfo',
        'MENU_USERS' => 'Usuarios',
        'MENU_DELETE' => 'Eliminar',
        'MENU_DELETE_FILE' => 'Eliminar Archivo',
        'MENU_LOGPLAYED' => 'LogPlayed',
        'MENU_MEDIA_DELETE_ASSING' => 'Eliminar Asociación',
        'MENU_SETTITLE' => 'Asignar Título',
        'MENU_SETTITLE_FORCE' => 'Asignar Título Existente',
        'MENU_SEASON' => 'Temporada',
        'MENU_EPISODE' => 'Episodio',
        'MENU_ELEMENT' => 'Elemento',
        'MENU_TITLE' => 'Título',
        'MENU_SCRAPPER' => 'Scrapper',
        'MENU_SCRAPPERWEB' => 'ScrapperWeb',
        'MENU_TYPE' => 'Tipo',
        'MENU_ACTION' => 'Acción',
        'MENU_IMPORT' => 'Importar',
        'MENU_FOLDER' => 'Carpeta',
        'MENU_CRON' => 'Cron',
        'MENU_QUANTITY' => 'Cantidad',
        'MENU_IMDB' => 'IMDBid',
        'MENU_IP' => 'IP',
        'MENU_DESCRIPTION' => 'Descripción',
        'MENU_URL' => 'URL',
        'MENU_REFERER' => 'Referer',
        'MENU_DATE' => 'Fecha',
        'MENU_UPDATE' => 'Actualizar',
        'MENU_EDIT' => 'Editar',
        'MENU_CONFIG' => 'Config',
        'MENU_GETEPISODES' => 'Lista de Episodios',
        'MENU_JOINMEDIA' => 'Unir Elementos',
        'MENU_YEAR' => 'Año',
        'MENU_GENRE' => 'Generos',
        'MENU_RATING' => 'Puntuación',
        'MENU_ORDERBY' => 'Ordenar Por',
        'MENU_DELETE_IMGS' => 'Borrar Imágenes',
        'MENU_IMGS_SEARCH' => 'Añadir Poster',
        'MENU_MEDIAINFO_NEW' => 'Nuevo MediaInfo',
        'MENU_HDDCLEAN' => 'Recuperar Espacio',
        
        'MEDIA_TYPE_SERIE' => 'Series',
        'MEDIA_TYPE_MOVIES' => 'Peliculas',
        
        'IDENT_NOTDETECTED' => 'Busqueda sin datos, pruebe cambiando el Título',
        'IDENT_DETECTED' => 'Título detectado: ',
        'IDENT_DETECTEDOK' => 'Archivo asignado al Título: ',
        'IDENT_DETECTEDKO' => 'Error asignando el elemento al Título: ',
        'IDENT_FILETODETECTED' => 'Archivo a Detectar: ',
        
        'MENU_LOGMEDIA' => 'LOGMEDIA',
        'MENU_LOGMEDIAINFO' => 'LOGMINFO',
        
        'LIST_TITLE_CONTINUE' => 'Continuar',
        'LIST_TITLE_LAST' => 'Últimos Añadidos',
        'LIST_SEARCH_RESULT' => 'Resultado:',
        'LIST_TITLE_PREMIERE' => 'Estrenos',
        'LIST_TITLE_RECOMENDED' => 'Recomendados',
        'LIST_TITLE_NEXTPAGE' => 'Siguiente',
        'LIST_TITLE_PREVPAGE' => 'Anterior',
        'LIST_TITLE_PAGE' => 'Página',
        
        'INFO_PLAY' => 'Reproducir',
        'INFO_PLAY_LATER' => 'Reproducir Más Tarde',
        'INFO_PLAY_SAFE' => 'Reproducir (Modo Seguro)',
        'INFO_NEXT' => 'Siguiente',
        'INFO_DOWNLOAD' => 'Descargar',
        'INFO_CHAPTERLIST' => 'Capítulos',
        'INFO_ACTORS' => 'Actores',
        'INFO_RELATED' => 'Relacionadas',
        'INFO_TIMEEND' => 'Termina a: ',
        'INFO_FILELIST' => 'Archivos',
        
        'WEBSCRAP_SEARCH_ERROR' => 'Error, no se puede acceder a la URL: ',
        'WEBSCRAP_NOTHING' => 'Scrappers no definido.',
        'WEBSCRAP_FILEDOWNLOADED' => 'Archivo Descargado: ',
        'WEBSCRAP_FILEDOWNLOADED_ERROR' => 'Error en la descarga: ',
        'WEBSCRAP_CHECKSIZE_OK' => 'Tamaño OK: ',
        'WEBSCRAP_CHECKSIZE_KO' => 'Tamaño ERROR: ',
        'WEBSCRAP_ADD_URL' => 'Añadiendo URL: ',
        'WEBSCRAP_ADDOK' => 'Elemento Añadido: ',
        'WEBSCRAP_ADDKO' => 'Elemento Error: ',
        'WEBSCRAP_PASS_INVALID' => 'Clave no válida: ',
        'WEBSCRAP_PASS_NEW_VALID' => 'Clave nueva válida: ',
        'WEBSCRAP_PASTELINKS' => 'Añadir Enlaces',
        
        'CONFIG_FILEOK' => 'Archivo php válido: aplicando cambios.',
        'CONFIG_FILEKO' => 'Error en la configuración: ',
        'CONFIG_REPLACE_FILEOK' => 'Configuración aplicada.',
        'CONFIG_REPLACE_FILEKO' => 'Error aplicando la configuración.',
        'CONFIG_RECOVER_FILEOK' => 'Recuperación de la configuración completada.',
        'CONFIG_NOFILE' => 'Archivo de configuración inexistente, sistema dañado.',
        'CONFIG_VALID' => 'Archivo de configuración válido.',
        'CONFIG_NOTWRITABLE' => 'El archivo de configuración no es modificable.',
        
        'JOIN_REPLACETHIS' => 'Remplazar: ',
        'JOIN_WHITTHIS' => 'Con: ',
        'JOIN_BUTTONREPLACE' => 'Remplazar',
        
        'DOWNLOADS_USER_TITLE' => 'Descargar Elementos Similares',
        
        'LIVETV_TITLE' => 'LiveTV',
        
        'LIVETVURLS_TITLE' => 'LiveTV URLs',
	);
	
?>
