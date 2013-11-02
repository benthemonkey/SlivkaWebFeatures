<?php
header('Content-type: text/html; charset=utf-8');

$gcal = "http://www.google.com/calendar/feeds/slivkait%40gmail.com/public/basic";

function get_url_contents( $url ){
    if ( function_exists('curl_init') ) {
        $crl = curl_init();
        $timeout = 5;
        curl_setopt ($crl, CURLOPT_URL, $url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
    } else {
        return file_get_contents( $url );
    }
    return 'Could not retrieve url';
}

echo get_url_contents($gcal);

?>