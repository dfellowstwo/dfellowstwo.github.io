<!DOCTYPE HTML>
<html lang=en>
<head>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<meta name=viewport content="width=device-width, initial-scale=1.0">
<title>WEATHER</title>
<META HTTP-EQUIV="refresh" CONTENT="3600">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Expires" content="0" />
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

// 21.php If past the quarter hour {Update weather data once an hour, moonphase graphic once a day.}
// SERVICED BY: 34 (Manual update KY3 graphic), 21 (bust cache, create, update), 30 (CronJob or manual update ozarksfirst)

$GlobalexecutionStartTime = microtime(true); //measure performance 
date_default_timezone_set('America/Chicago');
$zweek = date('W');
$ztimestamp1 = date('h:i:sA mdy');// 12hr hhmmssAM/PM hhmmss AM/PM hhmmss AM PM hhmmssAMPM mmddyy
// $ztoday1 = date('mdy');
// $zday1=date("d"); // 30, ....
// $zday2=date("l"); // Saturday, ...
// $zmonth=date("F"); // January, ...

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

@media only screen and (min-width:881px) and (max-width:973px){article{padding:16.5em 0 0 0}}
@media only screen and (min-width:800px) and (max-width:880px){article{padding:19em 0 0 0}}
@media only screen and (min-width:718px) and (max-width:799px){article{padding:2.25em 0 0 0}}
@media only screen and (max-width:717px){
	article{padding:1em 0 0 0}header{display:none}article h2{display:none}.navrule1{display:none}.promo_container .promo.three{padding-top:.25em;width:100%;margin:0 auto;float:none}.promo_container .promo.three img{padding-top:.25em;width:100%;margin:0 auto;float:none}nav{display:block;position:static;padding:10px 0 10px 10px;background-color:#515673}footer a{display:inline-block;padding:10px 0;}
}
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
.container{display: flex;justify-content: space-between; }
.border {border-width: 1px; border-color: black; border-style:solid;}

</style>

<script language="javascript">
setTimeout(function(){
   window.location.reload(1);
}, 3600000);
</script>

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

$latitude=37.606697; // NORTH
$longitude=-93.416709; // WEST

// localhost php upgraded to 8.1.2 on 030222. See notes.php
// dfellows.gd.rf is using php 7
// php 8.1.2 deprecates date_sunrise and date_sunset
// $time_format = 'h:i A T'; // 08:53 PM PDT
// $zenith = 90+(50/60); // True sunrise/sunset
// $tzoffset = date('Z') / 60 / 60; // find time offset in hours
// $sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
// $sunrise_time = date($time_format, strtotime(date('Y-m-d') . ' '. $sunrise));
// $sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
// $sunset_time = date($time_format, strtotime(date('Y-m-d') . ' '. $sunset));
//   END this is http://www.642weather.com/weather/scripts/sunrise-sunset.zip Sunrise - Sunset PHP Script by Mike Challis 

// localhost php upgraded from 5.4.27 to 8.1.2 on 030222 
// dfellows.gd.rf is using php 7
// Sunrise and sunset with date_sun_info (PHP 5 > 5.4.27, PHP 7, PHP 8)
// See https://www.php.net/manual/en/function.date-sun-info.php
$sun_info = date_sun_info(strtotime(date('h:i:sa')), $latitude, $longitude);
// foreach ($sun_info as $key => $val) {
// echo "$key: " . date('h:i a T', $val) . '<br />';}; // Display all info produced

ob_start();// Begin supress output of extract 
extract($sun_info);
ob_end_clean();// Throw out output of extract 
$sunrise_time=date('h:i a T', $sunrise); // See Display all info produced
$sunset_time=date('h:i a T', $sunset);	


// BEGIN DISPLAY WEATHER DATA. Data updated by 21.php
	$weather = simplexml_load_file('KSGF.xml');
	{list($a,$b,$c)=explode(',',$weather->location);
	echo $b.'<br>';
	echo $a, $c.'<br>';}
	echo date('l, F jS, Y').'<br>';
	echo date('h:i:s A').'<br>';
	echo 'Conditions: '.$weather->weather.'<br>';
	echo '<a href=https://w1.weather.gov/xml/current_obs/KSGF.xml>TEMP:</a> '.(float)$weather->temp_f. '&deg;F<br>';
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
<a href=http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg>

<div class=content>
<img src=weather-seven-day-forecast-graphic.jpg?v=<?php echo date('mdy');?> width=480 height=196 title='THANKS TO OZARKSFIRST.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg' alt='CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg. This is weather-seven-day-forecast-graphic.jpg.'></div>
</a>
</div>

<div class='promo six'>
<a href=http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg>
<!-- <a href=https://www.weatherforyou.com/reports/index.php?config=warningsbox&forecast=zandh&pands=&zipcode=65613&place=bolivar&state=mo&country=us&icao=> -->
<div class=content>
<img src=weather-seven-day-forecast-graphic.jpg?v=<?php echo date('mdy');?> width=480 height=196 title='THANKS TO OZARKSFIRST.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg' alt='CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg. This is weather-seven-day-forecast-graphic.jpg.'>
</div>
</a>
</div>

<div class='promo seven'>
<a href=http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg>
<div class=content>
<img src=weather-seven-day-forecast-graphic.jpg?v=<?php echo date('mdy');?> width=480 height=196 title='THANKS TO OZARKSFIRST.COM FOR THIS WEATHER SEVEN DAY FORECAST GRAPHIC. CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg' alt='CLICK TO GO TO http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg. This is weather-seven-day-forecast-graphic.jpg.'>
</div>
</a>
</div>
<div class='promo four'>
<iframe width='100%' height='640px' src='https://radar.weather.gov/?settings=v1_eyJhZ2VuZGEiOnsiaWQiOiJuYXRpb25hbCIsImNlbnRlciI6Wy05My4wODQsMzcuNzk3XSwiem9vbSI6NS43NjY3MTUwNzc1MjY4NTcsImxheWVyIjoiYnJlZl9xY2QiLCJ0cmFuc3BhcmVudCI6dHJ1ZSwiYWxlcnRzT3ZlcmxheSI6dHJ1ZX0sImFuaW1hdGluZyI6dHJ1ZSwiYmFzZSI6InN0YW5kYXJkIiwiY291bnR5IjpmYWxzZSwiY3dhIjpmYWxzZSwic3RhdGUiOmZhbHNlLCJtZW51IjpmYWxzZSwic2hvcnRGdXNlZE9ubHkiOnRydWUsIm9wYWNpdHkiOnsiYWxlcnRzIjowLCJsb2NhbCI6MC42LCJsb2NhbFN0YXRpb25zIjowLjgsIm5hdGlvbmFsIjowLjZ9fQ%3D%3D' frameborder='0' scrolling='no'></iframe>


<!-- https://radar.weather.gov/?settings=v1_eyJhZ2VuZGEiOnsiaWQiOiJuYXRpb25hbCIsImNlbnRlciI6Wy05My4wODQsMzcuNzk3XSwiem9vbSI6NS43NjY3MTUwNzc1MjY4NTcsImxheWVyIjoiYnJlZl9xY2QiLCJ0cmFuc3BhcmVudCI6dHJ1ZSwiYWxlcnRzT3ZlcmxheSI6dHJ1ZX0sImFuaW1hdGluZyI6dHJ1ZSwiYmFzZSI6InN0YW5kYXJkIiwiY291bnR5IjpmYWxzZSwiY3dhIjpmYWxzZSwic3RhdGUiOmZhbHNlLCJtZW51IjpmYWxzZSwic2hvcnRGdXNlZE9ubHkiOnRydWUsIm9wYWNpdHkiOnsiYWxlcnRzIjowLCJsb2NhbCI6MC42LCJsb2NhbFN0YXRpb25zIjowLjgsIm5hdGlvbmFsIjowLjZ9fQ%3D%3D#/ -->

<!-- https://noaa.maps.arcgis.com/apps/TimeAware/index.html?appid=3eb23d7688154631858c128c6ae83be2 -->

																																																																																																																																																																																																				    
	
</div>
<div id=radar2 class='promo five'>
<a href='https://www.wunderground.com/radar/us/mo/springfield/sgf'>
<!-- <img src='https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&num=10&delay=15&type=N0Q&scale=0.125&transx=23&transy=143&smooth=1&noclutter=1' 
width=940 height=705 alt='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND' title='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND'>
</a>

'https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N1R&frame=0&scale=0.125&noclutter=0&showstorms=0&mapx=400&mapy=240&centerx=423.704347826087&centery=583.2&transx=23.704347826086973&transy=343.20000000000004&showlabels=1&severe=0&rainsnow=0&lightning=Hide&smooth=1&rand=25203102&lat=0&lon=0&label=you' width=940 height=705 alt='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND' title='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND'

-->
<img src='https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?num=10&type=N1R&mapx=400&mapy=240&brand=wui&delay=15&frame=0&scale=0.125&centerx=423.704347826087&centery=583.2&transx=23.704347826086973&transy=343.20000000000004&severe=0&smooth=1&station=SGF&rainsnow=0&lightning=0&noclutter=1&showlabels=1&showstorms=0&rand=27178699' width=940 height=705 alt='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND' title='LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND'>
</a>
</div>
</div>
<div class=clear-fix></div>
<p class=navrule1>&nbsp;</p>

<nav>
<a href=../index.html>HOME</a>
<a href=tel:+14173999579>CALL</a>
<a href=https://www.OZARKSFIRST.COM/weather/todays-forecast>(Nexstar CBS, FOX, OZARKSFIRST)</a>
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
<a href=https://www.weather.gov/wrh/Climate?wfo=sgf>weather.gov (Change location to Bolivar)</a>
<a href=https://forecast.weather.gov/MapClick.php?textField1=37.2090&textField2=-93.2923#.YBihCXlMHcc>Weather.gov Forecast</a>
<a href=https://www.moonconnection.com/moon_module_2.phtml>moon1</a>
<a href=https://svs.gsfc.nasa.gov/Gallery/moonphase.html>moon2</a>
<a href=https://wttr.in/%22bolivar,mo%22?fqn.png>WTTR1</a>
<a href=https://wttr.in/:help>WTTR2</a>
<a href=https://w1.weather.gov/xml/current_obs/KSGF.xml>WEATHER DATA (LEFT CLICK GUI / RIGHT CLICK "SAVE AS" XML DATA)</a>
<a href=https://api.weather.gov/gridpoints/SGF/61,52/forecast>NWS</a>
<a href=https://ksgwxfan.github.io/5day/forecast.html>GRAPHIC</a>
<a href=weather-seven-day-forecast-graphicLOG.html>WeatherLog</a>
<a href=../index.html>HOME</a>
</nav>

<footer style=page-break-inside:avoid>
&#169; dfellows.rf.gd &nbsp; 37.601835,-93.415584  &nbsp;  37&#176;36'06.6"N 93&#176;24'56.1"W <a href=https://w1.weather.gov/xml/current_obs/KSGF.xml>Weather data</a> and <a href=http://www.moonmodule.com/cs/dm/vn.gif>moonphase graphic</a> update every hour at ~00:17:00. <a href=http://media.psg.nexstardigital.net/kolr/weather/7day_New.jpeg>7 day graphic</a> at ~8:47am and ~11:47pm
<br />
<!-- <a class=hidelg style='float:right; margin:0 0 0 30px' href='16.php'><img src='../images/placemark44x31.jpg' title='16.php ozarksfirst graphic will auto update if 13, 14, or 17.php has already been run.' alt='../images/placemark44x31.jpg'></a>
<a class=hidelg style='margin:0 80px 0 100px' href='31.php'><img src='../images/placemark44x31.jpg' title='31.php ozarksfirst graphic.' alt='alt=31.php ozarksfirst graphic'></a>
 -->

<div class="container">
<div><a class=hidelg style="cursor: pointer" onclick="javascript: window.location = '34.php';"><img src='../images/placemark44x31.jpg' title='ky3 graphic' alt='alt=ky3 graphic'></a></div>
<div><a class=hidelg style="cursor: pointer" onclick="javascript: window.location = '33.php';"><img src='../images/placemark44x31.jpg' title='update weather data' alt='alt=update weather data'></a></div>
<div><a class=hidelg style="cursor: pointer" onclick="javascript: window.location = '30.php';"><img src='../images/placemark44x31.jpg' title='ozarksfirst graphic' alt='alt=ozarksfirst graphic'></a></div>

<!--
<div><a class=hidelg href='34.php'><img src='../images/placemark44x31.jpg' title='34.php ky3 graphic' alt='alt=ky3 graphic'></a></div>
<div><a class=hidelg href='33.php'><img src='../images/placemark44x31.jpg' title='33.php update weather data' alt='alt=33.php update weather data'></a></div>
<div><a class=hidelg href='30.php'><img src='../images/placemark44x31.jpg' title='30.php ozarksfirst graphic' alt='alt=30.php ozarksfirst graphic'></a></div>
-->

</div>
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