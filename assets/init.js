$(function () {
    $.ajaxSetup({
        //type: 'POST',
        timeout: 1200000,
    });
});

//VARS

var g_debug = true;

//GET

function g_get( url, targetid ) {
    scrollTo( targetid );
    if( g_debug ) console.log( 'GET: ' + url );
    $( '#' + targetid ).html( "Loading..." );
    loading_show();
    $.get( url, 
        function( data ) {
            loading_hide();
            scrollTo( targetid );
            if( data.length == 0 ){
                data = "EMPTY";
            }else{
                $( '#' + targetid ).html( data );
            }
        }
    );

    return false;
}

//POST

function g_post( url, datasend, targetid ) {
    if( g_debug ) console.log( 'POST: ' + url );
    $( '#' + targetid ).html( "Loading..." );
    loading_show();
    $.post( url, 
        datasend,
        function( data ) {
            loading_hide();
            scrollTo( targetid );
            if( data.length == 0 ){
                data = "EMPTY";
                $( '#' + targetid ).html( data );
            }else{
                $( '#' + targetid ).html( data );
            }
        }
    );
    
    return false;
}

function g_post_form( url, idform, targetid ) {
    if( g_debug ) console.log( 'POST: ' + url );
    $( '#' + targetid ).html( "Loading..." );
    var datasend = $( "#" + idform ).serialize();
    loading_show();
    $.post( url, 
        datasend,
        function( data ) {
            loading_hide();
            scrollTo( targetid );
            if( data.length == 0 ){
                data = "EMPTY";
                $( '#' + targetid ).html( data );
            }else{
                $( '#' + targetid ).html( data );
            }
        }
    );
    
    return false;
}

//SCROLL TO

function scrollTo( idelement ){
    if( $( "#" + idelement ).length > 0 
    ){
        var posy = $( "#" + idelement ).offset().top - $( window ).height();
        if( g_debug ) console.log( 'SCROLLTO: ' + idelement + '->' + $( "#" + idelement ).offset().top );
        if( g_debug ) console.log( 'SCROLLTOPX: ' + posy + '->' + $( window ).height() );
        $('html, body').animate({
            scrollTop: posy
        }, 1000);
    }
}

//GO TO

function goToURL( url ){
    window.location.href = url;
}

//NEW TAB

function openTab( url ){
    window.open(url, '_blank')
}

//LOADING OVERLAY

function loading_show(){
    $( '.boxLoadingOverlay' ).fadeIn( 'fast' );
}
function loading_hide(){
    $( '.boxLoadingOverlay' ).fadeOut( 'fast' );
}
