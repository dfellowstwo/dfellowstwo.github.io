<?php
/* 
https://github.com/TomLany/NOAA-Weather-Grabber

https://xml.weather.yahoo.com/forecastrss?p=65613&u=f
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

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">

<html>

<head>

    <!-- saved from url=(0014)https://livebolivar.com/ -->

    <meta http-equiv="Content-Type" content="text/html;

      charset=utf-8">

    <title>WEATHER RADAR</title>

<meta name="DESCRIPTION" content="WEATHER FOR BOLIVAR, MISSOURI 65613">
<META NAME="KEYWORDS" CONTENT="MO RADAR TEMP TEMPERATURE CONDITIONS WIND SPEED DIRECTION ASTRONOMY PLANETS MOON PHASE 7 DAY FORECAST PROPERTY RENTAL AND MANAGEMENT BOLIVAR MISSOURI 65613,  PROPERTY RENTAL 65613, BOLIVAR MISSOURI 65613, PROPERTY, PROPERTY RENTAL, MISSOURI, PROPERTY MANAGEMENT, RENTAL PROPERTY, SPRINGFIELD MISSOURI, HOUSES, FOR, IN, HOTELS, BOLIVAR.COM, IE TAB CLASSIC, COXHEALTH, ST. JOHNS REGIONAL HEALTH CENTER, MERCY HOSPITAL, BASS PRO, HAMMOND, PLASTER, SPRINGHILL FALLS, ZIP CODE, MENU, DATA, WEATHER, JOBS, HOMES, RENT, LEASE, 65613, POLK COUNTY, SOUTHWEST BAPTIST UNIVERSITY, CITIZENS MEMORIAL HOSPITAL, PUBLIC SCHOOL, SINGLE FAMILY HOME, SFH, TRIPLEXES, TRI-PLEX, TRIPLEX, DUPLEXES, DU-PLEX, DUPLEX, APARTMENTS, TRAILERS, OFFICE, BUSINESS, SPACE, RENTALPROPERTY, PROPERTYMANAGEMENT, CALL, AJ ELLIS, 1-417-327-3911, RENTAL PROPERTY MANAGEMENT, 417, HOUSES FOR RENT, HOUSES FOR LEASE, APARTMENTS FOR LEASE, APARTMENTS FOR RENT, MO, RENTAL, POLK, SPRINGFIELD, POLK, GREENE, POLK, GREENE, GREENE, SPRINGFIELD, RENTAL, GREENE, POLK, SPRINGFIELD, MISSOURI, SPRINGFIELD, RENTAL, 65613, RENTAL, 65613, MISSOURI, GREENE, RENTAL, PROPERTY, RENTAL, MISSOURI, 65613, HOMES FOR SALE, HOUSE FOR SALE, CONDO FOR SALE">
<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW">
<META NAME="GOOGLEBOT" CONTENT="INDEX,FOLLOW">
<META NAME="AUTHOR" CONTENT="DOUG FELLOWS">
<meta http-equiv="expires" content="Mon, 26 Jul 1997 05:00:00 GMT">
<meta http-equiv="pragma" content="no-cache" >
<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=.5">


		<link rel="icon" href="favicon.ico?v=1.1"> 

		<link href="images/apple-touch-icon.png" rel="apple-touch-icon">

		<link href="images/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76">

		<link href="images/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120">

		<link href="images/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152">

		<link href="images/apple-touch-icon-180x180.png" rel="apple-touch-icon" sizes="180x180">

		<link href="images/icon-hires.png" rel="icon" sizes="192x192">

		<link href="images/icon-normal.png" rel="icon" sizes="128x128">

		<!--[if lt IE 9]>

			<script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>

		<![endif]-->


<!--FORCE PAGE TO REFRESH AT 2AM
https://www.webdeveloper.com/forum/showthread.php?9533-Refresh-page-every-24-hours

<script language="javascript" type="text/javascript">
function reloadAt02(){
var d = new Date();
var time = d.getHours();
if(time==03){location.reload();}
}
window.setInterval("reloadAt02()", 1000);
</script> -->



<script type="text/javascript" src="../files/_jquery.min.js"></script>

<script type="text/javascript" src="../files/_stickytooltip.js"></script>

    <!--* Sticky Tooltip script- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)

* This notice MUST stay intact for legal use

* Visit Dynamic Drive at https://www.dynamicdrive.com/ for this script and 100s more

***********************************************/ -->

<link rel="stylesheet" type="text/css" href="../files/_stickytooltip.css">
<style type="text/css">
html{background-image:url(../images/background770.jpg);background-repeat:repeat;font-family:Arial, Helvetica, sans-serif;}

.container{width:750px;margin:0 auto;}
.left {
    max-width: 100%;
    border: #000000;
    white-space:nowrap;
    /*following line truncates "wind speed and direction" text between moonphase graphic and 7-day forecast graphic on smartphone screens
	overflow:hidden;
	*/
    text-overflow:ellipsis;
    -ms-text-overflow:ellipsis;
    float: left;
	padding:0px 5px 0px 0px;
	
}
.right {
    background:yellow;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    -ms-text-overflow:ellipsis;
    text-align: right;
}
.right img {float:right}

.wcond{font-size:11px;} 

.clear-fix { clear: both; line-height: 1px; }

</style>    

</head>

<body class="container" onLoad="runSlideShow();slideit()">

<p><img src="../images/_banner4.jpg" alt="CONTACT US" usemap="#Map" style="width: 750px; height: 135px;" height="135" width="750" border="0">
    
<map name="Map"><area shape="rect" coords="172,123,440,141" href="MAILTO:AJELLIS@LIVEBOLIVAR.COM" title="CLICK HERE TO SEND ME AN EMAIL" alt="CLICK HERE TO SEND ME AN EMAIL" data-tooltip="email"><area shape="rect" coords="456,121,622,141" href="SKYPE:AJELLIS3?CALL" title="CLICK HERE TO CALL ME WITH SKYPE" alt="CLICK HERE TO CALL ME WITH SKYPE" data-tooltip="skype"><area shape="rect" coords="-10,122,162,136" href="TEL:+14173273911" title="CLICK TO CALL ME" alt="CLICK TO CALL ME" data-tooltip="skype2"><area shape="rect" coords="162,1,652,107" href="#" onClick="history.go(-1);return false;" title="PROPERTY AVAILABLE NOW OR IN 30 DAYS" alt="PROPERTY AVAILABLE NOW OR IN 30 DAYS" data-tooltip="banner"><area shape="rect" coords="654,2,748,132" href="../site_html/map_office.html" title="CLICK HERE FOR A MAP TO THE OFFICE" alt="CLICK HERE FOR A MAP TO THE OFFICE" data-tooltip="office"><area shape="rect" coords="-51,-10,157,104" href="../site_html/about_us.html" title="CLICK HERE TO LEARN MORE ABOUT US" alt="" data-tooltip="aj">
  <area shape="rect" coords="0,108,652,121" href="../site_html/map_office.html" title="CLICK HERE FOR A MAP TO THE OFFICE" alt="CLICK HERE FOR A MAP TO THE OFFICE" data-tooltip="map_address">
</map>
<p>AJ ELLIS <a href="TEL:+14173273911">&#49;&#45;&#52;&#49;&#55;&#45;&#51;&#50;&#55;&#45;&#51;&#57;&#49;&#49;</a>&nbsp; <a href="https://www.google.com/maps/dir//LIVEBOLIVAR.COM,+1325+South+Lillian+Avenue+%23212,+Bolivar,+MO+65613/@37.6011562,-93.4147197,17z/data=!4m8!4m7!1m0!1m5!1m1!1s0x87c5bff73c37a583:0xb2990d5cec1a2b09!2m2!1d-93.4156382!2d37.6017893?hl=en" title="CLICK FOR A GOOGLE MAP" data-tooltip="map_address">1325 S. LILLIAN AVE #212, BOLIVAR, MISSOURI 65613</a>&nbsp;&nbsp;MON-FRI 9-5</p>

livebolivar.com/site_html/weather_sprngfld_mo.php<br>

<iframe src="https://free.timeanddate.com/clock/i1ofwrdu/n605/fs16/ahl/tt0/ta1" frameborder="0" height="20" width="380"></iframe> 
<br>
<br>



<!-- <div id="left">
<iframe id="forecast_embed" type="text/html" frameborder="0" height="245" width="50%" src="https://forecast.io/embed/#lat=37.5495&lon=-93.5454&name=Bolivar, MO"> </iframe> --!>
Weather Springfield, MO <br>
<a href="https://www.ozarksfirst.com/weather">CBS ozarksfirst.com</a><br>
<a href="https://www.kspr.com/weather">ABC kspr.com </a><br>
<a href="https://www.ky3.com/weather/">NBC ky3.com</a><br>
<a href="https://www.google.com/#q=weather+65613">GoogleWeather</a><br>
<a href="https://www.weather.gov/climate/index.php?map=2">Weather Facts.</a>&nbsp; Click on the nearest city, then NOWData tab.<br>
<span class="wcond"><a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">THE WEATHER CHANNEL</a> <BR>
<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&amp;cm_ven=googleonebox">WEATHER UNDERGROUND</a><br>
<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left" data-tooltip="sky_telescope_screenshot">SKY AND TELESCOPE</a><br></span>
<!--<div class="left">
<a href="https://api.usno.navy.mil/imagery/moon.png?v=<?php echo Date("Y.m.d.G.i.s")?>">
<img src="../publicbr549/moon_tonight.gif?v=<?php echo Date("Y.m.d.G.i.s")?>" alt="TONIGHT'S MOON PHASE IMAGE COURTESY OF THE UNITED STATES NAVAL OBSERVATORY, WASHINGTON, DC." 
title="TONIGHT'S MOON PHASE IMAGE COURTESY OF THE UNITED STATES NAVAL OBSERVATORY, WASHINGTON, DC." width="100" height="100"></a>
</div> -->

<!--../publicbr549/phase.gif?v=<?php echo Date("Y.m.d.G.i.s")?> -->
<!--https://tycho.usno.navy.mil/cgi-bin/phase.gif<?php echo Date("Y.m.d.G.i.s")?> low res, small dimensions. -->
<!--https://api.usno.navy.mil/imagery/moon.png?v=<?php echo Date("Y.m.d.G.i.s")?> hi resolution, large dimensions. -->
<!--https://www.usno.navy.mil/USNO/time/moon-phase-images?v=<?php echo Date("Y.m.d.G.i.s")?> page where phase.gif is served. -->
<!--$HOME/html/publicbr549/moon_tonight.gif -->
<!--$HOME/html/publicbr549/moon_tonight_sm.png -->
<!--$HOME/html/publicbr549/moon_tonight_lg.png -->
<!--<div class="left"> <img src="https://api.usno.navy.mil/imagery/moon.png" width="79" height="79" alt="What the Moon looks like now"></div>
 -->
<!--<div class="left" data-tooltip="moonphase" title="Moon Phase Software $10">
<a href="https://www.moonconnection.com/current_moon_phase.phtml">
<script language="JavaScript" type="text/javascript">var ccm_cfg = { pth:'https://www.moonmodule.com/cs/', fn:'ccm_v1.swf', lg:'en', hs:1, tc:'FFFFFF', bg:'000000', mc:'', fw:127, fh:227 } </script>
<script language="JavaScript" type="text/javascript" src="https://www.moonmodule.com/cs/ccm_fl.js"></script>
</a>
</div> -->
<div class="left">
<!-- // Begin Current Moon Phase HTML (c) MoonConnection.com // -->
<a href="https://www.moonconnection.com/moon_module.phtml"><img class="left" src="https://www.moonmodule.com/cs/dm/vn.gif" alt="" name="my_image" width="128" height="196" border="0" id="my_image"/></a>
<!-- // end moon phase HTML // -->

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
        // echo "Weather Springfield, MO" . "\n";
        echo date("D, M jS, Y ") . "<br />\n";
        echo "TEMP: " . $weather->temp . "&deg;F" . "<br />\n";
    	echo "Conditions: " . "<br />\n";
        // echo $weather->condition . "<br />\n";
		// over 40 characters pushes the adjacent graphic down
		$text = $weather->condition;
		$newtext = wordwrap($text, 32, "<br />\n");
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
// https://github.com/amwhalen/noaa  (SEE ABOVE "WIND SPEED AND DIRECTION")
echo "WIND from the: <br>";
// echo $current->getWindString() . "<br>";
// above produces the next line at its worst
// $a="from the Northwest at 12.7 gusting to 19.6 MPH (11 gusting to 17 KT)";
list ($a) = explode('(', $current->getWindString());
// above eliminates everything in () i.e. (11 gusting to 17 KT)
if(strlen($a) <= 26)
{
    echo $a . "<br />"; 
}
if(strlen($a) >= 26)
{
// REMOVE "FROM THE"
$b = substr($a, 8, 29); //places 29 chars from string $a, starting at position 8 (removes "from the") into a variable
// WHAT HAPPENS NEXT ??
$c = wordwrap($b, 29);
echo $c . "<br />"; 
echo substr($a, 37) . "<br />";  //echos all chars from string $a, starting at position 34
}
?>

<a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d'); ?>" data-tooltip="almanac_com">PLANETS</a>

</div>

<div> <a href="https://www.kspr.com/weather"><img src="https://gray.ftp.clickability.com/ksprwebftp/Seven_Day.JPG" alt="7 DAY FORECAST GRAPHIC. ABC SPRINGFIELD, MO KSPR.COM" TITLE="ABC SPRINGFIELD, MO WEATHER KSPR.COM" width="400" height="227" align="right"></a>
</div>

    <!-- https://static.lakana.com/nxsglobal/ozarksfirst/photo/MAP/59715121/59715121_Position1.JPG
https://gray.ftp.clickability.com/ky3webftp/kytv_new_7_day.jpg -->

  <div style="clear: both;"></div>

  <p>LAST 45 MINUTES REGIONAL RADAR
<a href="https://www.wunderground.com/radar/mosaic.asp">
   <img src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" alt="wunderground.com Central States US Nexrad Radar" title="LAST 30 MINUTES OF RADAR DATA" height="563" width="750"></a>
    <!-- <img src="https://icons.wunderground.com/data/640x480/2xradarb3_anim.gif" alt="wunderground.com Central States US Nexrad Radar" title="LAST 30 MINUTES OF RADAR DATA" height="563" width="750"></a> -->
    <!-- <a href="https://radblast-aa.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&amp;brand=wui&amp;num=6&amp;delay=15&amp;type=N0R&amp;frame=0&amp;scale=0.125&amp;noclutter=0&amp;t=1190809588&amp;lat=0&amp;lon=0&amp;label=you&amp;showstorms=0&amp;map.x=400&amp;map.y=240&amp;centerx=470&amp;centery=530&amp;transx=70&amp;transy=290&amp;showlabels=1&amp;severe=0&amp;rainsnow=0&amp;lightning=0"></a> -->
</p>
 <p>LAST 60 MINUTES LOCAL RADAR
<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&amp;lat=0&amp;lon=0&amp;label=you&amp;type=N0R&amp;zoommode=pan&amp;map.x=400&amp;map.y=240&amp;centerx=382&amp;centery=567&amp;prevzoom=pan&amp;num=10&amp;delay=15&amp;scale=0.125&amp;showlabels=1&amp;smooth=1&amp;noclutter=1&amp;showstorms=0&amp;rainsnow=1&amp;lightning=0&amp;remembersettings=on&amp;setprefs.0.key=RADNUM&amp;setprefs.0.val=10&amp;setprefs.1.key=RADSPD&amp;setprefs.1.val=15&amp;setprefs.2.key=RADC&amp;setprefs.2.val=1&amp;setprefs.3.key=RADSTM&amp;setprefs.3.val=0&amp;setprefs.4.key=SLABS&amp;setprefs.4.val=1&amp;setprefs.5.key=RADRMS&amp;setprefs.5.val=1&amp;setprefs.6.key=RADLIT&amp;setprefs.6.val=0&amp;setprefs.7.key=RADSMO&amp;setprefs.7.val=1"><img src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&amp;brand=wui&amp;num=10&amp;delay=15&amp;type=N0R&amp;frame=0&amp;scale=0.125&amp;noclutter=1&amp;lat=0&amp;lon=0&amp;label=you&amp;showstorms=0&amp;map.x=400&amp;map.y=240&amp;centerx=382&amp;centery=567&amp;transx=-18&amp;transy=327&amp;showlabels=1&amp;severe=0&amp;rainsnow=1&amp;lightning=0&amp;smooth=1" alt="RADBLAST-AA.WUNDERGROUND.COM 5 MILE ANIMATED RADAR." title="5 MILE RADAR AROUND BOLIVAR, MISSOURI" style="border: 0px solid;" border="0" height="563" width="750"></a>
   </p>
   
   

 <div id="mystickytooltip" class="stickytooltip">

  <div style="padding:5px">
      
      <div id="email" class="atip"> <img src="email.jpg" height="322" width="353" alt=""> </div>
      
      <div id="skype" class="atip"> <img src="skype.jpg" height="322" width="353" alt=""> </div>
      
      <div id="skype2" class="atip"> <img src="skype2.jpg" height="558" width="410" alt=""> </div>
      
      <div id="aj" class="atip"> <img src="aj.jpg" height="322" width="353" alt=""> </div>
      
      <div id="banner" class="atip"> <img src="banner.jpg" height="108" width="351" alt=""> </div>
      
      <div id="office" class="atip"> <img src="../images/map_office.jpg" height="438" width="353" alt=""> </div>
      
<!--        <div id="moonphase" class="atip"> <img src="moon_phase_screenshot2.jpg" height="660" width="660" alt=""> </div> -->
	  <div id="moonphase" class="atip"> <img src="moon_phase_screenshot3.jpg" height="400" width="500" alt=""> </div>
      
     <!--<div id="moonphase" class="atip"> <img src="moonphase_software.jpg" height="550" width="500" alt=""> </div>  -->
     
     <div id="myweather" class="atip"> <img src="myweather.jpg" height="462" width="554" alt=""></div>
      
      <div id="sky_telescope_screenshot" class="atip"> <img src="sky_telescope.jpg" height="586" width="346" alt=""> </div>
      
      <div id="almanac_com" class="atip"> <img src="weather_planets_screenshot.jpg" height="350" width="300" alt=""> </div>
     
    <!--<div id="map_screenshot" class="atip"> <img src="map_screenshot6.jpg" height="322" width="525" /> </div> -->
    <div id="map_address" class="atip"> <img src="map_screenshot6.jpg" height="322" width="525" alt=""> </div>
   
   <div class="stickystatus"></div> </div>

  </div>  </div>


</body>
</html>
