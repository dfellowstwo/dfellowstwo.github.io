<!DOCTYPE HTML>
<!-- 37.6145, -93.4105 -->
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WEATHER RADAR SPRINGFIELD,MO</title>
<link rel="stylesheet" type="text/css"  href="screen_styles.css" />
<link rel="stylesheet" type="text/css"  href="screen_layout_large.css" />
<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:600px)"    href="screen_layout_small.css">
<link rel="stylesheet" type="text/css" media="only screen and (min-width:601px) and (max-width:800px)"   href="screen_layout_medium.css">
<link rel="stylesheet" type="text/css" media="print"  href="print.css" />
<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon" href="../images/apple-touch-icon.png" />
<link rel="apple-touch-icon" sizes="57x57" href="../images/apple-touch-icon-57x57.png" />
<link rel="apple-touch-icon" sizes="72x72" href="../images/apple-touch-icon-72x72.png" />
<link rel="apple-touch-icon" sizes="76x76" href="../images/apple-touch-icon-76x76.png" />
<link rel="apple-touch-icon" sizes="114x114" href="../images/apple-touch-icon-114x114.png" />
<link rel="apple-touch-icon" sizes="120x120" href="../images/apple-touch-icon-120x120.png" />
<link rel="apple-touch-icon" sizes="144x144" href="../images/apple-touch-icon-144x144.png" />
<link rel="apple-touch-icon" sizes="152x152" href="../images/apple-touch-icon-152x152.png" />
<link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon-180x180.png" />
<link href=../images/icon-hires.png rel=icon sizes="192x192">
<link href=../images/icon-normal.png rel=icon sizes="128x128">

		<!--[if lt IE 9]>
			<script src="../scripts/html5shiv.js">
			</script>
        <![endif]-->
        
<style type=text/css>
/* ARTICLE PADDING.  START THE FIRST LINE OF TEXT BELOW THE NAVIGATION */
@media only screen and (min-width:891px){
article { padding: 14em 0 0 0; }
}
@media only screen and (min-width:801px)  and (max-width:890px){
article { padding: 16.5em 0 0 0; }
}

@media only screen and (max-width:750px){
	article{padding:1em 0 0 0}
	header{display:none}article h1{display:none}.navrule1{display:none}
	.promo_container .promo.three {
		width:450px;
		margin: 0 auto;
		float: none;
		}

	nav {
		display: block;
		position: static;
		padding: 10px 0px 10px 10px;
		background-color: #515673;
		}
}
 
@media only screen and (min-width:810px){.promo.three{display:none}}
@media only screen and (max-width:810px){.promo.seven{display:none}}
@media screen and (orientation:portrait){.promo.three{display:none}}
@media screen and (orientation:landscape){.promo.six{display:none}}

.promo_container .promo.four img {width: 940px;}	
.promo_container .promo.five img {width: 940px;} 
.promo_container .promo.two img {
	width: 128px;
	display: block;
	margin: 0 auto;
	}

.promo_container .promo.seven img {
	width:473px;
	margin: 0 auto;
	float: none;
	}	

.promo_container .promo.six img {
	padding-top:.25em;
	width:100%;
	margin: 0 auto;
	float: none;
	}
	
.promo .one .content {	line-height: 1.45em;}

</style>

	</head>
	<body>

		<div class="page">
			<header><a class="logo" href="tel:+14173999579" title="Call me at 1-417-399-9579"></a></header>
            
			<article>
				<h1>WEATHER SPRINGFIELD, MISSOURI</h1>
			</article>
			<div class="promo_container">
				<div class="promo one">
					<div class="content">

<?php
/* CACHE BUSTING HASHSTAMP IN GRAPHICS FILENAMES */
$hash1 = hash_file('crc32', '../images/moonphase.gif'); $hash2 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicl.jpg'); $hash3 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicp.jpg');

/* BEGIN Sunrise - Sunset PHP Script by Mike Challis https://www.wxforum.net/index.php?topic=30419.0 */
date_default_timezone_set('America/Chicago');
$time_format = 'h:i A T'; // 08:53 PM PDT
$latitude=37.606697;
$longitude=-93.416709;
$zenith = 90+(50/60); // True sunrise/sunset
$tzoffset = date("Z")/60 / 60; // find time offset in hours
// determine sunrise time
$sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
$sunrise_time = date($time_format, strtotime(date("Y-m-d") . ' '. $sunrise));
// determine sunset time
$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tzoffset);
$sunset_time = date($time_format, strtotime(date("Y-m-d") . ' '. $sunset));
/* END Sunrise - Sunset PHP Script by Mike Challis https://www.wxforum.net/index.php?topic=30419.0 */
					     
// BEGIN UPDATE WEATHER DATA DISPLAYED ON THIS PAGE
$location = 'http://w1.weather.gov/xml/current_obs/KSGF.xml'; // URL TO DOWNLOAD WEATHER DATA FROM
$filename = 'ksgf.xml';
// $filename = 'http://localhost/LIVEBOLIVAR.COM/site_html/ksgf.xml';
$now  = time(); //seconds since Jan 01 1970. (UTC)
// if ksgf.xml exists and was modified w/n the last one hour do not download it.
if (file_exists($filename)) {
// ECHO "the # of seconds since 010170: $now <br>"; //
// ECHO "the # of seconds since 010170 $filename was modified:" . filemtime($filename) . "<br>";
// ECHO "If " . ($now - filemtime($filename)) . " is less than 3600 the weather data is up to date. <br>";
// if ($now - filemtime($filename) <= 60 * 60) echo "Weather data is up to date. <br>";
if ($now - filemtime($filename) >= 60 * 60) { // 1 hour in seconds
	// echo "Downloading the weather data ...<br> ";
	exec("wget -O $filename $location"); // GET THE WEATHER DATA
}
} else {
	// echo "local $filename does not exist. <br>Downloading the weather data ...<br>";
		exec("wget -O $filename $location"); // GET THE WEATHER DATA
		}
// END UPDATE THE WEATHER DATA DISPLAYED ON THIS PAGE
$weather = simplexml_load_file($filename);
{list($a,$b,$c)=explode(',',$weather->location);
echo "$b <br>";
echo "$a, $c <br>";}
echo date("l, F jS, Y").'<br>';
echo date("h:i:s A").'<br>';
echo "Conditions:  $weather->weather <br>";
echo "TEMP: ".(float)$weather->temp_f. "&deg;F <br>";
echo "Sunrise: $sunrise_time<br>";
echo "Sunset: &nbsp;$sunset_time<br>";
// include 'sunrise-sunset.php'; // www.642weather.com/weather/scripts/sunrise-sunset.zip
echo "WIND from the:<br>";
// return wind data if it is calm. calm<=5
// if "from the" is not present, return wind data and do not return the knots value. knots value shows up in parentheses (i.e (7KT)).
// if "from the" is present strip it out and do not return the knots value. knots value shows up in parentheses (i.e (7KT)).
if(strlen($weather->wind_string)<=5){echo $weather->wind_string; goto end;}
if(!preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo $a;goto end;}
if(preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo substr($a,8,44);}
end:
?>
					</div>
				</div>
				<div class="promo two">
                    
					<a href="https://www.moonconnection.com/moon_module.phtml">
                    <div class="content">
						<img src="../images/moonphase-<?php echo $hash1; ?>.gif" width="128" height="196" alt="MOON PHASE GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/moonphase-<?php echo $hash1; ?>.gif">
					</div></a>
				</div>
				
				<div class="promo three">
                	<!--LANDSCAPE -->
           	    <a href="https://www.fox5krbk.com/weather">
					<div class="content">
						<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg" width=473 height=196 title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg"> 
                </div></a>
				</div>
                
<div class="promo seven">
<!-- big screen landscape -->		
<a href="https://www.fox5krbk.com/weather">
<div class=content>
<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" width=473 height=196 title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
</div></a>
</div>
		    
<div class="promo six">
					   
<a href="https://www.fox5krbk.com/weather">
<div class=content>
<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
</div></a>
</div>
    
<div class="promo four">
<a href="https://www.wunderground.com/weather-radar/united-states-regional/ks/salina"><img src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" width=940px height=715px alt="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>

</div>
    
<div id=radar2 class="promo five">
<a href="https://www.wunderground.com/weather-radar/united-states/mo/springfield/sgf/?region=jef"><img src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&showstorms=0&mapx=400&mapy=240&centerx=423.704347826087&centery=583.2&transx=23.704347826086973&transy=343.20000000000004&showlabels=1&severe=0&rainsnow=0&lightning=Hide&smooth=1&rand=25203102&lat=0&lon=0&label=you" width=940 height=705 alt="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>

</div>
				
			</div>
            
            <div class="clear-fix"></div>
			 <p class="navrule1">&nbsp;</p>
            
            <nav>
<a href="../index.html">HOME</a>
<a href="tel:+14173273911">CALL</a>
<a href="info13.html">INFO</a>
<a href="http://www.fox5krbk.com/weather">(local fox weather KRBK)</a>
<a href="https://www.ozarksfirst.com/weather">(local cbs weather KOLR)</a>
<a href="http://www.kspr.com/weather">(local abc weather KSPR)</a>
<a href="http://www.ky3.com/weather/">(local nbc weather KY3)</a>
<a href="https://www.google.com/#q=weather+65613">googleweather</a>
<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">(weather channel)</a>
<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">(forecast weather underground)</a>
<a href="https://www.wunderground.com/weather-radar">(regional radar weather underground)</a>
<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">(local radar weather underground)</a>
<a href="http://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
<a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d');?>">(rise and set times of the planets)</a>
<a href="https://www.suncalc.org/#/37.6018,-93.4155,18/2018.04.19/11:06/1/0">SUN</a>
<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a>
<a href="https://www.moonconnection.com/moon_module.phtml">moon1</a>
<a href="http://aa.usno.navy.mil/imagery/moon">moon2</a>    
<a href="http://w1.weather.gov/xml/current_obs/KSGF.xml">WEATHER DATA (LEFT CLICK GUI / RIGHT CLICK "SAVE AS" XML DATA) 
<a href="../index.html">HOME</a>
    

</nav>


			 <div class="clear-fix"></div>
			<footer style="page-break-inside:avoid;">

				
				
				<a href="https://www.google.com/search?source=hp&q=DOUG+FELLOWS+site%3Alivebolivar.com">&#169; LIVEBOLIVAR.COM</a>
				

			</footer>
           
		</div>

	</body>
</html>
