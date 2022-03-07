<!DOCTYPE HTML>
<html lang=en>
<head>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<meta name=viewport content="width=device-width, initial-scale=1.0">
<title>WEATHER19 SPRINGFIELD/BOLIVAR, MISSOURI 65613</title>
</head>
<?php
// see working version https://www.livebolivar.com/site_html/weather19.php
// thanks to http://thejaffes.org/2012-05-26/displaying-weather-xml-noaa-using-php-and-xslt
// did not work for me.  modified the php and validated / modified the xsl.
// https://www.xmlvalidation.com
// runs online on linux server running CentOS release 5.5 (Final) and php 5.3
// runs locally on windows 10 and PHP: 5.4.31
// output is heavy on text and uses crappy graphics but it is a start.  see the screenshot.
// zipped it all up
// https://www.livebolivar.com/publicbr549/weather19.7z

$xsl = new DOMDocument ;
$xsl->load($_SERVER['DOCUMENT_ROOT'].'/livebolivar.com/site_html/weather19.xsl') ; //served localhost.  change to fit your path
// $xsl->load($_SERVER['DOCUMENT_ROOT'].'/site_html/weather19.xsl') ; //served online. change to fit your path
$xslProc = new XSLTProcessor() ;
$xslProc->importStylesheet($xsl) ; 
$xmldoc = new DOMDocument ;
// forecast.weather.gov gave HTTP 403 Forbidden when trying to use $xmldoc->load
$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);
$xmldoc = file_get_contents("https://forecast.weather.gov/MapClick.php?lat=37.1962&lon=-93.2861&unit=0&lg=english&FcstType=dwml", false, $context);
// convert string to object
$xmldoc= simplexml_load_string($xmldoc);
echo $xslProc->transformToXML($xmldoc) ;
// shazaam
?>