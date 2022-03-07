<?php
/* https://xml.weather.yahoo.com/forecastrss?p=65613&u=f
https://www.phptoys.com/tutorial/get-actual-weather.html# 
https://www.phptoys.com/download/downloadItem.php?id=28
INCLUDED ITEMS:
sunrise-sunset.php
REQUIRE_ONCE ITEMS:
weather-Current-NOAA-Weather-Grabber.php
/noaa/Forecaster.php
	FORECASTER.PHP REQUIRE_ONCE:
			require_once 'weather/Base.php';
			require_once 'weather/Configuration.php';
			require_once 'weather/response/Response.php';
			require_once 'weather/response/CurrentWeather.php';
			require_once 'weather/cache/Cache.php';
			require_once 'weather/cache/ArrayCache.php';
			require_once 'weather/cache/FileCache.php';
			require_once 'weather/cache/NoCache.php';
*/

$ts = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: $ts");
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", FALSE);
?>

<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>index2.php</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0">
<!--        <meta name="viewport" content="width=device-width, initial-scale=1"> -->

        <link rel="apple-touch-icon" href="../apple-touch-icon.png">
        <!-- Place favicon.ico in the root directory -->

        
        
<style type="text/css">

.container{
width:750px;
margin:0 auto;
}
body{
	margin:0 auto;
	background-color:#FFFECD;
	-webkit-text-size-adjust: 100%;
	line-height: 1.5em;
	font-family: Arial;
	font-size: 16px;
}

</style> 

 
    </head>
    <body class="container">
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <div>        
        <h1>WEATHER SPRINGFIELD, MO</h1>

		<p>
 <div class="container">
 
  <?php
// WIND SPEED AND DIRECTION
global $argc;
// https://github.com/amwhalen/noaa
// retrieve the station ID from the command line if supplied
if ($argc > 1) {
	$stationId = $argv[1];
} else {
$stationId = "KSGF";
}

// Instantiate a Forecaster object using a file cache
// XML data from the NOAA API will be cached for 1 HOUR in the specified file location
require_once dirname(__FILE__) . '/noaa/Forecaster.php';
$config = new \noaa\weather\Configuration();
$config->setCache(new \noaa\weather\cache\FileCache(dirname(__FILE__) . '/cache'));
$forecaster = new \noaa\Forecaster($config);

// fetch a CurrentWeather instance for a specific station ID
// find station IDs here: https://www.weather.gov/xml/current_obs/
try {
	$current = $forecaster->getCurrentWeather($stationId);
} catch (\Exception $e) {
	echo "Error: " . $e->getMessage() . "\n";
	exit(1);
}

// display
// echo $current->getLocation() . "\n";
// echo $current->getObservationTime() . "\n";
// rtrim($current->getWindString, "()")
// substr_replace($current->getWindString(), "", -5)
// trim($getWindString, "()")
?>
		  
  <?php
// source https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml
// Require the weather function
require_once( 'weather-Current-NOAA-Weather-Grabber.php' );
// Standard display function
function noaa_weather_grabber_test( $weather ) {
    // Display each item
    if ((isset ( $weather->okay )) && ( $weather->okay == "yes" )) {
		list ($a, $b, $c) = explode(',', $weather->location);
		echo $b ."<br>";
		echo $a."," , $c . "<br>";
		// echo $weather->location . "<br>";
        echo date("D, M jS, Y ") . "<br />\n";
        echo "TEMP: " . $weather->temp . "&deg;F" . "<br />\n";
    	echo "Conditions: " . "<br />\n";
        // echo $weather->condition . "<br />\n";
		// over 40 characters pushes the adjacent graphic down
		$text = $weather->condition;
		$newtext = wordwrap($text, 40, "<br />\n");
		echo $newtext . "<br />\n";
		include 'sunrise-sunset.php';
		echo "\n";
	}
	
	// Dump the entire weather variable
	// This is just for testing -- do not include on your website.
    // var_dump( $weather );
}

?>
		  
  <?php
// Get the weather
$weather = noaa_weather_grabber( 'KSGF', 'yes', 'https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml');
// Run the weather test function
noaa_weather_grabber_test( $weather );
?>
		  
  <?php 
// GET THE WIND SPEED AND DIRECTION
// https://github.com/amwhalen/noaa  (SEE ABOVE)
echo "WIND from the: " . "<br>";
// https://stackoverflow.com/questions/5159086/php-split-string
// below delimits at the "(" (i.e. (9 KT), puts each part into a variable (list $a $b) and
// displays the first variable (echo $a)
// echo "from the southwest at 5.37 gusting to 23.7 MPH " . "<br>";
// $in ="from the southwest at 5.37 gusting to 23.7 MPH ";
// $out = strlen($in) > 26 ? substr($in,0,26) : $in;
// echo $out . "<br>";

$test ="from the southwest at 5.37 gusting to 23.7 MPH ";
list ($a, $b) = explode('(', $current->getWindString());
// echo $a  . "<br>";
// below from https://stackoverflow.com/questions/11434091/add-if-string-is-too-long-php
// $out = strlen($a) > 26 ? substr($a,9,39)  . "<br>": $a;
// $out = strlen($test) > 26 ? substr($test,9,39) . "<br>": $test;
// echo $out;

//echo substr($a, 8, 26) . "<br>"; //echos 26 chars from string $a, starting at position 8
//echo substr($a, 34) . "<br>"; //echos all chars from string $a, starting at position 34
// =
// West at 16.1 gusting to 2
// 5.3 MPH 


if(strlen($a) >= 26)
{
    echo substr($a, 8, 26) . "<br>"; //echos 26 chars from string $a, starting at position 8 (removes "from the")
	echo substr($a, 34) . "<br>"; //echos all chars from string $a, starting at position 34
}

if(strlen($a) <= 26)
{
    echo $a. "<br>"; 
}	

// below from https://stackoverflow.com/questions/31604471/break-long-string-variable-in-multiple-lines-php
// $array = str_split($a, 26); 
// echo implode("<br>",$array)  . "<br>";
// $array = str_split($out, 26); 
// echo implode("<br>",$array)  . "<br>";

// mb_strimwidth($test, 8, 34);
// USE THE TWO FOLLOWING LINES WHEN "GUSTING" IS PRESENT
// echo substr($a, 9, 21) . "<br>";
// echo substr($a, 30, 24) . "<br>";
// below displays the first x characters (i.e. 17)
// echo substr($current->getWindString(), 0, 17) . "<br>";

?>
  </div>
		  
		  
  <a href="https://www.kspr.com/weather"><img src="https://gray.ftp.clickability.com/ksprwebftp/Seven_Day.JPG" alt="7 DAY FORECAST GRAPHIC. ABC SPRINGFIELD, MO KSPR.COM" TITLE="ABC SPRINGFIELD, MO WEATHER KSPR.COM"></a>

<div>
<p><a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d'); ?>">RISE AND SET TIMES OF THE PLANETS</a> </p>
<!-- // Begin Current Moon Phase HTML (c) MoonConnection.com // -->
<a href="https://www.moonconnection.com/moon_module.phtml"><img src="https://www.moonmodule.com/cs/dm/vn.gif" alt="PHASE OF THE MOON GIF" id="my_image"/></a>
<!-- // end moon phase HTML // -->
</div>


<p>CURRENT REGIONAL RADAR</p>
<div>
<a href="https://www.wunderground.com/radar/mosaic.asp">
 <img src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" alt="wunderground.com Central States US Nexrad Radar" title="LAST 30 MINUTES OF RADAR DATA" height="563" width="750"></a>
</div>
<p>CURRENT LOCAL RADAR</p>
<div> 
<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&amp;lat=0&amp;lon=0&amp;label=you&amp;type=N0R&amp;zoommode=pan&amp;map.x=400&amp;map.y=240&amp;centerx=382&amp;centery=567&amp;prevzoom=pan&amp;num=10&amp;delay=15&amp;scale=0.125&amp;showlabels=1&amp;smooth=1&amp;noclutter=1&amp;showstorms=0&amp;rainsnow=1&amp;lightning=0&amp;remembersettings=on&amp;setprefs.0.key=RADNUM&amp;setprefs.0.val=10&amp;setprefs.1.key=RADSPD&amp;setprefs.1.val=15&amp;setprefs.2.key=RADC&amp;setprefs.2.val=1&amp;setprefs.3.key=RADSTM&amp;setprefs.3.val=0&amp;setprefs.4.key=SLABS&amp;setprefs.4.val=1&amp;setprefs.5.key=RADRMS&amp;setprefs.5.val=1&amp;setprefs.6.key=RADLIT&amp;setprefs.6.val=0&amp;setprefs.7.key=RADSMO&amp;setprefs.7.val=1">
<img src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&amp;brand=wui&amp;num=10&amp;delay=15&amp;type=N0R&amp;frame=0&amp;scale=0.125&amp;noclutter=1&amp;lat=0&amp;lon=0&amp;label=you&amp;showstorms=0&amp;map.x=400&amp;map.y=240&amp;centerx=382&amp;centery=567&amp;transx=-18&amp;transy=327&amp;showlabels=1&amp;severe=0&amp;rainsnow=1&amp;lightning=0&amp;smooth=1" alt="RADBLAST-AA.WUNDERGROUND.COM 5 MILE ANIMATED RADAR." title="5 MILE RADAR AROUND BOLIVAR, MISSOURI" style="border: 0px solid;" height="563" width="750" /></a>
</div>
<!--end container -->
</div>













<script src="../scripts/jquery-1.12.0.min.js"></script>
        <script>window.jQuery || document.write('<script src="scripts/jquery-1.12.0.min.js"><\/script>')</script>
        <script src="../scripts/plugins.js"></script>
        <script src="../scripts/main.js"></script>

        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            e.src='https://www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            ga('create','UA-40650885-1','auto');ga('send','pageview');
        </script>
    </body>
</html>
