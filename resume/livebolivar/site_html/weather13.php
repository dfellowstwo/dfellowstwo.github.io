<!DOCTYPE HTML>
<html lang=en>
<head>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<meta name=viewport content="width=device-width, initial-scale=1.0">
<title>WEATHER</title>
<META HTTP-EQUIV="refresh" CONTENT="86400">


<link rel="apple-touch-icon" sizes="180x180" href="../images/weather-favicons/apple-touch-icon.png?v=PYAgJn4vA0">
<link rel="icon" type="image/png" sizes="32x32" href="../images/weather-favicons/favicon-32x32.png?v=PYAgJn4vA0">
<link rel="icon" type="image/png" sizes="194x194" href="../images/weather-favicons/favicon-194x194.png?v=PYAgJn4vA0">
<link rel="icon" type="image/png" sizes="192x192" href="../images/weather-favicons/android-chrome-192x192.png?v=PYAgJn4vA0">
<link rel="icon" type="image/png" sizes="16x16" href="../images/weather-favicons/favicon-16x16.png?v=PYAgJn4vA0">
<link rel="manifest" href="../images/weather-favicons/site.webmanifest?v=PYAgJn4vA0">
<link rel="mask-icon" href="../images/weather-favicons/safari-pinned-tab.svg?v=PYAgJn4vA0" color="#1a8bb3">
<link rel="shortcut icon" href="../images/weather-favicons/favicon.ico?v=PYAgJn4vA0">
<link rel="manifest" href="../images/weather-favicons/manifest.json">
<meta name="apple-mobile-web-app-title" content="WEATHER">
<meta name="application-name" content="WEATHER">
<meta name="msapplication-TileColor" content="#603cba">
<meta name="msapplication-config" content="../images/weather-favicons/browserconfig.xml?v=PYAgJn4vA0">
<meta name="theme-color" content="#ffffff">



<?php 

// SERVICED BY: 15, 21 AND 30.PHP

$GlobalexecutionStartTime = microtime(true); //measure performance 
date_default_timezone_set('America/Chicago');
$zweek = date('W');
$ztimestamp1 = date('h:i:sA mdy');// 12hr hhmmssAM/PM hhmmss AM/PM hhmmss AM PM hhmmssAMPM mmddyy
$ztoday1 = date('mdy');
// $zday1=date("d"); // 30, ....
// $zday2=date("l"); // Saturday, ...
// $zmonth=date("F"); // January, ...
// $z1="KY3.COM";
// $z3="https://webpubcontent.gray.tv/ky3/weather/7-day_AM.jpg";
// $z1="KY3.COM";
// $z3="http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg";


// BEGIN CREATE OR UPDATE ELEMENTS OLDER THAN ONE / TWENTY-FOUR HOUR. 
// vn.gif is updated ~the top of the hour. KSGF.xml is updated ~0-15 minutes after the hour
// https://www.php.net/manual/en/datetime.format.php
if ( ! (file_exists("KSGF.xml"))){
$zurls = array('http://w1.weather.gov/xml/current_obs/KSGF.xml','http://www.moonmodule.com/cs/dm/vn.gif');
 foreach ($zurls as $zurl) {	
file_put_contents(basename($zurl),file_get_contents($zurl));  // at least 60 MINUTES old. Download new copy.
} 
} else {
if ((time()-filemtime('KSGF.xml')) >=3600){
file_put_contents(('KSGF.xml'),file_get_contents('http://w1.weather.gov/xml/current_obs/KSGF.xml')); 
}
if ((time()-filemtime('vn.gif')) >=86400){
file_put_contents(('vn.gif'),file_get_contents('http://www.moonmodule.com/cs/dm/vn.gif')); 
}	
}
//    END CREATE OR UPDATE ELEMENTS OLDER THAN ONE HOUR

?>
<meta name="description" content="CURRENT ATMOSPHERIC CONDITIONS. LOCAL/REGIONAL RADAR. TEMPERATURE. SUNRISE/SUNSET. WIND SPEED/DIRECTION.  PHASE OF THE MOON. 7 DAY FORECAST GRAPHIC.">
<meta name="keywords" content="CLIMATE WEATHER DATA GUI/XML CHANCE OF RAIN SKY AND TELESCOPE SUN POSITION CALCULATOR  WEATHER.GOV SPRINGFIELD REGIONAL AIRPORT">
<link rel=stylesheet type=text/css href=screen_styles.css />
<link rel=stylesheet type=text/css href=screen_layout_large.css />
<link rel=stylesheet type=text/css media="only screen and (min-width:50px) and (max-width:600px)" href=screen_layout_small.css>
<link rel=stylesheet type=text/css media="only screen and (min-width:601px) and (max-width:800px)" href=screen_layout_medium.css>
<link rel=stylesheet type=text/css media=print href=print.css />
<!--[if lt IE 9]><script src=../scripts/html5shiv.js></script><![endif]-->
<style>
																																																																																																																																							

@media only screen and (min-width:881px) and (max-width:970px){article{padding:16.5em 0 0 0}}
@media only screen and (min-width:800px) and (max-width:880px){article{padding:19em 0 0 0}}
@media only screen and (min-width:718px) and (max-width:799px){article{padding:2.25em 0 0 0}}
@media only screen and (max-width:717px){article{padding:1em 0 0 0}header{display:none}article h2{display:none}.navrule1{display:none}.promo_container .promo.three{padding-top:.25em;width:100%;margin:0 auto;float:none}.promo_container .promo.three img{padding-top:.25em;width:100%;margin:0 auto;float:none}nav{display:block;position:static;padding:10px 0 10px 10px;background-color:#515673}}
@media only screen and (min-width:810px){.promo.three{display:none}}
@media only screen and (max-width:810px){.promo.seven{display:none}}
@media screen and (orientation:portrait){.promo.three{display:none}}
@media screen and (orientation:landscape){.promo.six{display:none}}.promo_container .promo.four img{width:940px}.promo_container .promo.five img{width:940px}.promo_container .promo.seven img{width:480px;margin:0 auto;float:none}.promo_container .promo.six img{padding-top:.25em;width:100%;margin:0 auto;float:none}.promo .one .content{line-height:1.45em}
@media only screen and (min-width:600px){.hidelg{display:none}}
.hide{display:none}
h2{
    color: #a6430a;
    margin: 0 0 .5em;
    font-size: 2em;
    font-weight: 400;
}
body{font-family:"Segoe UI Semibold","Segoe WP Semibold","Segoe WP","Segoe UI",Arial,Sans-Serif;}
</style>
</head>
<body>
<div class=page>
		
<header><a class=logo href=tel:+14173999579 title="Call me at 1-417-399-9579"></a></header>
		 
<article>
<h1 class=hide>WEATHER SPRINGFIELD / BOLIVAR, MISSOURI 65613. CURRENT ATMOSPHERIC CONDITIONS. LOCAL/REGIONAL RADAR. TEMPERATURE. SUNRISE/SUNSET. WIND SPEED/DIRECTION.  PHASE OF THE MOON. 7 DAY FORECAST GRAPHIC.</h1>
<h2>WEATHER SPRINGFIELD / BOLIVAR, MISSOURI 65613</h2>
</article>
<div class=promo_container>
<div class='promo one'>
<div class=content>
<?php 
/* BEGIN this is http://www.642weather.com/weather/scripts/sunrise-sunset.zip Sunrise - Sunset PHP Script by Mike Challis 
    FIND LAT/LONG geodata (gps coordinates) WITH https://www.suncalc.org/#/37.6018,-93.4155,18/2018.06.04/09:43/1/0
   */

$time_format = 'h:i A T'; // 08:53 PM PDT
$latitude=37.606697; // NORTH
$longitude=-93.416709; // WEST
$zenith = 90+(50/60); // True sunrise/sunset
$tzoffset = date('Z') / 60 / 60; // find time offset in hours
// date('P'); //offset in +-06.00 format
// determine sunrise time
$sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
$sunrise_time = date($time_format, strtotime(date('Y-m-d') . ' '. $sunrise));
// determine sunset time
$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
$sunset_time = date($time_format, strtotime(date('Y-m-d') . ' '. $sunset));
//   END this is http://www.642weather.com/weather/scripts/sunrise-sunset.zip Sunrise - Sunset PHP Script by Mike Challis 

// BEGIN DISPLAY WEATHER DATA
	$filename = 'KSGF.xml';
	$weather = simplexml_load_file($filename);
	{list($a,$b,$c)=explode(',',$weather->location);
	echo $b.'<br>';
	echo $a, $c.'<br>';}
	echo date('l, F jS, Y').'<br>';
	echo date('h:i:s A').'<br>';
	echo 'Conditions: '.$weather->weather.'<br>';
	echo 'TEMP: '.(float)$weather->temp_f. '&deg;F <br>';
	echo 'Sunrise: '.$sunrise_time.'<br>';
	echo 'Sunset: &nbsp;'.$sunset_time.'<br>';
	// include 'sunrise-sunset.php'; // www.642weather.com/weather/scripts/sunrise-sunset.zip IF PHP SUNRISE / SUNSET_TIME STOPS WORKING
	echo 'WIND from the';
	// ": Calm" or " Southwest at 15.0 MPH" or "from the Southwest at 15.0 gusting to 21.9 MPH (13 gusting to 19 KT)" to ":<br>Southwest at 15.0 gusting to 21.9 MPH"
	if(strlen($weather->wind_string)<=5){echo ': '.$weather->wind_string.'<br />'; goto end;}
      if(!preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo ' '.$a.'<br />';goto end;} else {list($a,$b)=explode('(',$weather->wind_string);echo substr($a,8,44).'<br />';goto end;}
	end:


?>
</div>
</div>
<div class='promo two'>
<a href=http://www.moonmodule.com/cs/dm/vn.gif>
<div class=content> 

<img src='vn.gif' width=128 height=196 title='Current Moon phase graphic at moonconnection.com' alt='CLICK TO GO TO http://www.moonmodule.com/cs/dm/vn.gif.<?php  echo ' Timestamp: '.$ztimestamp1;?>'>
</div></a>
</div>
<div class='promo three'>
<a href=http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg>
<div class=content>

<img src=weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg width=480 height=196  title='THANKS TO KY3.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg' alt='CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg. This is weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg.'>
</div>
</a>
</div>

<div class='promo six'>
<a href=http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg>
<!-- <a href=https://www.weatherforyou.com/reports/index.php?config=warningsbox&forecast=zandh&pands=&zipcode=65613&place=bolivar&state=mo&country=us&icao=> -->
<div class=content>
<img src=weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg width=480 height=196  title='THANKS TO KY3.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg' alt='CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg. This is weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg.'>
</div>
</a>
</div>

<div class='promo seven'>
<a href=http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg>
<div class=content>
<img src=weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg width=480 height=196  title='THANKS TO KY3.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg' alt='CLICK TO GO TO http://media.ozarksfirst.com/nxsglobal/ozarksfirst/photo/MAP/7day_New.jpeg. This is weather-seven-day-forecast-graphic<?php  echo $ztoday1; ?>.jpg.'>
</div>
</a>
</div>
<div class='promo four'>
<iframe width='100%' height='640px' src='https://noaa.maps.arcgis.com/apps/TimeAware/index.html?appid=3eb23d7688154631858c128c6ae83be2' frameborder='0' scrolling='no'></iframe>

																																																																																																																																																																																																				    
	
</div>
<div id=radar2 class='promo five'>
<a href='https://www.wunderground.com/weather-radar/united-states/mo/springfield/sgf/?region=jef'><img src='https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&showstorms=0&mapx=400&mapy=240&centerx=423.704347826087&centery=583.2&transx=23.704347826086973&transy=343.20000000000004&showlabels=1&severe=0&rainsnow=0&lightning=Hide&smooth=1&rand=25203102&lat=0&lon=0&label=you' width=940 height=705 alt='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND' title='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND'></a>
</div>
</div>
<div class=clear-fix></div>
<p class=navrule1>&nbsp;</p>

<nav>
<a href=../index.html>HOME</a>
<a href=tel:+14173999579>CALL</a>
<a href=https://www.ozarksfirst.com/weather/todays-forecast>(Nexstar CBS, FOX, OZARKSFIRST)</a>
<a href=http://www.kspr.com/weather>(local ABC weather KSPR)</a>
<a href=http://www.ky3.com/weather>(local NBC weather KY3)</a>
<a href=https://www.google.com/#q=weather+65613>googleweather</a>
<a href=https://weather.com/weather/today/l/USMO0087:1:US>(weather channel)</a>
<a href=https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox>(forecast weather underground)</a>
<a href=https://www.wunderground.com/weather-radar>(regional radar weather underground)</a>
<a href='https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1'>(local radar weather underground)</a>
<a href=https://skyandtelescope.org/observing/sky-at-a-glance/>sky and telescope</a>
<a href=https://www.almanac.com/astronomy/planets-rise-and-set/zipcode/65613/<?php echo date('Y-m-d')?>>(rise and set times of the planets)</a>
<a href=https://www.suncalc.org/#/37.6018,-93.4155,18/<?php echo date('Y.m.d'); ?>/<?php echo date('h:i');?>/1/0>SUN</a>
<a href=https://www.weather.gov/climate/index.php?map=2>weather.gov (click nearest city then nowdata tab)</a>
<a href=https://forecast.weather.gov/MapClick.php?textField1=37.2090&textField2=-93.2923#.YBihCXlMHcc>Weather.gov Forecast</a>
<a href=https://www.moonconnection.com/moon_module_2.phtml>moon1</a>
<a href=https://svs.gsfc.nasa.gov/Gallery/moonphase.html>moon2</a>
<a href=https://w1.weather.gov/xml/current_obs/KSGF.xml>WEATHER DATA (LEFT CLICK GUI / RIGHT CLICK "SAVE AS" XML DATA)</a>
<a href=https://wttr.in/%22bolivar,mo%22?fqn.png>WTTR1</a>
<a href=https://wttr.in/:help>WTTR2</a>
<a href=https://api.weather.gov/gridpoints/SGF/61,52/forecast>NWS</a>
<a href=https://ksgwxfan.github.io/5day/forecast.html>GRAPHIC</a>
<a href=../index.html>HOME</a>
</nav>

<footer style=page-break-inside:avoid>
<a href='https://www.google.com/search?source=hp&q=DOUG+FELLOWS+site%3Adfellows.rf.gd'>&#169; dfellows.rf.gd</a> &nbsp; 37.601835,-93.415584  &nbsp;  37&#176;36'06.6"N 93&#176;24'56.1"W
<br />
<!-- <a class=hidelg style='float:right; margin:0 0 0 30px' href='16.php'><img src='../images/placemark44x31.jpg' title='16.php ozarksfirst graphic will auto update if 13, 14, or 17.php has already been run.' alt='../images/placemark44x31.jpg'></a>
<a class=hidelg style='margin:0 80px 0 100px' href='31.php'><img src='../images/placemark44x31.jpg' title='31.php ozarksfirst graphic.' alt='alt=31.php ozarksfirst graphic'></a>
 -->
<p><a class=hidelg style='float:right; margin:2em,2em,2em,0' href='30.php'><img src='../images/placemark44x31.jpg' title='30.php ozarksfirst graphic' alt='alt=30.php ozarksfirst graphic'></a></p>
<p><a class=hidelg style='float:left; margin:2em,0,2em,2em;' href='.15.php'><img src='../images/placemark44x31.jpg' title='15.php ky3 graphic' alt='alt=15.php ky3 graphic'></a></p>
<div class=clear-fix></div>
</footer>
</div><br />
<!-- MSN WEATHER RADAR LOOP. go to https://www.msn.com/en-us/Weather/maps/bolivarmissouriunited-states/we-city?q=bolivar-missouri&iso=US&el=Hz7YgScYS6gU5Wr0AAMF1g%3d%3d 
copy <html> outer html from developer tools, paste into new doc,
search <div class="maps" data-type="radar" data-info  you need it. It does not change. You need <div id="mapdiv" class="baseMapDiv"      heavily edited.
search <div class="mimgs hide" data-imginfo    you need    <div class="mimgs hide" data-imginfo........ </div>   ~2655 characters. 
<head> is heavily edited.  delete <link rel="shortcut icon" href="//static-entertainment-eus-s-msn-com.akamaized.net/sc/90/60ea8f.ico">
So: 
<div class="maps" data-type="radar" data-info .......no closing div        unchanged.
<div id="mapdiv" class="baseMapDiv"      heavily edited.
php Create arrays of timestamps.
<div class="mimgs hide" data-imginfo ........</div>    Replace timestamps w/ variables.
</div> this is <div class="maps" closing div
-->
</body></html>
<?php 
$executionEndTime = microtime(true); //measure performance 
$zseconds = $executionEndTime - $GlobalexecutionStartTime; //measure performance 
// file_put_contents('weather-seven-day-forecast-graphicLOG.html','TimeStamp: '.date('H:i:s mdy ').basename(__FILE__). ' took '.$zseconds.' seconds.<br />', FILE_APPEND);
unset($ztimestamp1);
?>