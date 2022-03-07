<!DOCTYPE html>
<!-- 37.6145, -93.4105 -->
<html lang="en">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>WEATHER RADAR</title>
	<link href="screen_styles.css" rel="stylesheet" type="text/css">
	<link href="screen_layout_large.css" rel="stylesheet" type="text/css">
	<link href="screen_layout_small.css" media="only screen and (min-width:50px) and (max-width:600px)" rel="stylesheet" type="text/css">
	<link href="screen_layout_medium.css" media="only screen and (min-width:601px) and (max-width:800px)" rel="stylesheet" type="text/css">
	<link href="print.css" media="print" rel="stylesheet" type="text/css">
	<link href="../images/_icon_weather_apple-touch-icon.png?v=almEKrgqpX" rel="apple-touch-icon" sizes="180x180">
	<link href="../images/_icon_favicon-32x32.png?v=almEKrgqpX" rel="icon" sizes="32x32" type="image/png">
	<link href="../images/_icon_favicon-16x16.png?v=almEKrgqpX" rel="icon" sizes="16x16" type="image/png">
	<link href="site.weather.webmanifest?v=almEKrgqpX" rel="manifest">
	<link href="../images/_icon_safari-pinned-tab.svg?v=almEKrgqpX" rel="mask-icon">
	<link href="../images/_icon_favicon.ico?v=almEKrgqpX" rel="shortcut icon">
	<meta content="#da532c" name="msapplication-TileColor">
	<meta content="browserconfig.xml?v=almEKrgqpX" name="msapplication-config">
	<meta content="#ffffff" name="theme-color"><!--[if lt IE 9]>
            <script src="../scripts/html5shiv.js">
            </script>
        <![endif]-->

	<style>
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
	           padding-top:.25em;
	           width:100%;
	           margin: 0 auto;
	           float: none;
	       }
	   .promo_container .promo.three img {
	           padding-top:.25em;
	           width:100%;
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
	   
	.promo .one .content {  line-height: 1.45em;}

	</style>
</head>
<body>
	<div class="page">
		<header>
			<a class="logo" href="tel:+14173999579" title="Call me at 1-417-399-9579"></a>
		</header>
		<article>
			<h1>WEATHER SPRINGFIELD, MISSOURI</h1>
		</article>
		<div class="promo_container">
			<div class="promo one">
				<div class="content">
					<?php
					/* CACHE BUSTING HASHSTAMP IN GRAPHICS FILENAMES */
					$hash1 = hash_file('crc32', '../images/moonphase.gif'); $hash2 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicl.jpg'); $hash3 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicp.jpg');

					/* BEGIN this is http://www.642weather.com/weather/scripts/sunrise-sunset.zip Sunrise - Sunset PHP Script by Mike Challis 
					    FIND geodata (gps coordinates) AT https://www.suncalc.org/#/37.6018,-93.4155,18/2018.06.04/09:43/1/0
					   */
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
					/* END this is www.642weather.com/weather/scripts/sunrise-sunset.zip Sunrise - Sunset PHP Script by Mike Challis  */
					                         
					// BEGIN UPDATE WEATHER DATA DISPLAYED ON THIS PAGE
					$location = 'http://w1.weather.gov/xml/current_obs/KSGF.xml'; // URL TO DOWNLOAD WEATHER DATA FROM
					$filename = 'ksgf.xml';
					// $filename = 'http://localhost/LIVEBOLIVAR.COM/site_html/ksgf.xml';
					$now  = time(); //seconds since Jan 01 1970. (UTC)
					// IF KSGF.XML EXISTS AND WAS MODIFIED W/N THE LAST ONE HOUR DO NOT DOWNLOAD IT.
					if (file_exists($filename)) {
					// ECHO "the # of seconds since 010170: $now <br>"; //
					// ECHO "the # of seconds since 010170 $filename was modified:" . filemtime($filename) . "<br>";
					// ECHO "If " . ($now - filemtime($filename)) . " is less than 3600 the weather data is up to date. <br>";
					// if ($now - filemtime($filename) <= 60 * 60) echo "Weather data is up to date. <br>";
					if ($now - filemtime($filename) >= 60 * 60) { // THE WEATHER DATA IS OUT OF DATE
					    // echo "Downloading the weather data ...<br> ";
					    exec("wget -O $filename $location"); // GET THE WEATHER DATA
					}
					} else {
					    // echo "local $filename does not exist. <br>Downloading the weather data ...<br>";
					        exec("wget -O $filename $location"); // GET THE WEATHER DATA
					        }
					// END UPDATE THE WEATHER DATA DISPLAYED ON THIS PAGE

					// IF NOT EXIST WEATHER DATA FILE DISPLAY MESSAGE AND GO TO END
					if(!file_exists($filename)){echo "<a href='$location'>WEATHER DATA. RIGHT CLICK / SAVE AS.</a>"; goto end;}

					// BEGIN DISPLAY WEATHER DATA
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
					    // include 'sunrise-sunset.php'; // www.642weather.com/weather/scripts/sunrise-sunset.zip IF PHP SUNRISE / SUNSET_TIME STOPS WORKING
					    
					    // BEGIN WIND DATA 
					        // CHECK FOR THE EXISTENCE OF THE WEATHER DATA FILE 
					        // IF IS EXISTS SEARCH IT FOR WIND DATA
					        // IF WIND DATA EXISTS AND IT IS LESS THAN 5 CHARACTERS REPORT CALM AND GO TO END
					        // IF IT IS MORE THAN FOUR CHARACTERS:
					            // if  not "from the" is present, return wind data and do not return the knots value. knots value shows up in parentheses (i.e (7KT)).
					            // if "from the" is present strip it out and do not return the knots value. knots value shows up in parentheses (i.e (7KT)).
					        // IF WIND DATA DOES NOT EXIST REPORT "NO WIND DATA AT THIS TIME"
					        echo "WIND from the:<br>";
					        // http://jafty.com/blog/quick-way-to-check-if-string-exists-in-a-file-with-php/
					        $searchString = 'wind_string';
					            if(file_exists($filename)){//if $filename exists check it for searchString.
					                if(exec('grep '.escapeshellarg($searchString).' '.$filename)) {
					                if(strlen($weather->wind_string)<=5){echo $weather->wind_string; goto end;}
					                if(!preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo $a;goto end;}
					                if(preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo substr($a,8,44);goto end;}
					                }
					                else 
					            {echo "No wind data at this time";}}
					        end:
					    // END WIND DATA 

					// END DISPLAY WEATHER DATA
					?>
				</div>
			</div>
			<div class="promo two">
				<a href="https://www.moonconnection.com/moon_module.phtml"></a>
				<div class="content"><img alt="MOON PHASE GRAPHIC. YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE. THIS UPDATES AT MIDNIGHT CST/CDT. REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE. ../images/moonphase-&lt;?php echo $hash1; ?&gt;.gif" height="196" src="../images/moonphase-%3C?php%20echo%20$hash1;%20?%3E.gif" width="128"></div>
			</div>
			<!-- BEGIN PROMO THREE -->
			<!-- LANDSCAPE FOX5KRBR.COM -->
			<div class="promo three">
				<a href="http://www.fox5krbk.com/weather"></a>
				<div class="content"><img alt="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC. YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE. THIS UPDATES AT MIDNIGHT CST/CDT. REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE. ../images/weather-seven-day-forecast-graphicl-&lt;?php echo $hash2; ?&gt;.jpg" src="../images/weather-seven-day-forecast-graphicl-%3C?php%20echo%20$hash2;%20?%3E.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC"></div>
			</div>
			<!-- LANDSCAPE KY3.COM -->
			<!-- <div class="promo three">
                
                        <a href="http://www.ky3.com/weather/">
                            <div class="content">
                                <img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg" width=473 height=196 title="THANKS TO KY3.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="THANKS TO KY3.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg">
                            </div>
                        </a>
                    </div> -->
			<!--    END PROMO THREE -->
			
			<!-- BEGIN PROMO SIX -->
			<!-- LANDSCAPE FOX5KRBR.COM -->
			<div class="promo six">
				<a href="http://www.fox5krbk.com/weather"></a>
				<div class="content"><img alt="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC. YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE. THIS UPDATES AT MIDNIGHT CST/CDT. REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE. ../images/weather-seven-day-forecast-graphicl-&lt;?php echo $hash2; ?&gt;.jpg" height="196" src="../images/weather-seven-day-forecast-graphicl-%3C?php%20echo%20$hash2;%20?%3E.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" width="473"></div>
			</div>
			<!-- LANDSCAPE KY3.COM -->
			<!-- <div class="promo six">
                
                        <a href="http://www.ky3.com/weather/">
                            <div class="content">
                                <img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg" width=473 height=196 title="THANKS TO KY3.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="THANKS TO KY3.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2; ?>.jpg">
                            </div>
                        </a>
                    </div> -->
			<!--    END PROMO SIX -->
			
			<!-- BEGIN PROMO SEVEN -->
				<!-- BEGIN BIG SCREEN LANDSCAPE -->
					<div class="promo seven">
						<a href="http://www.fox5krbk.com/weather"></a>
						<div class="content"><img alt="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC. YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE. THIS UPDATES AT MIDNIGHT CST/CDT. REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE. ../images/weather-seven-day-forecast-graphicl-&lt;?php echo $hash2; ?&gt;.jpg" src="../images/weather-seven-day-forecast-graphicl-%3C?php%20echo%20$hash2;%20?%3E.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC"></div>
					</div><!-- <div class="promo seven">
									<a href="http://www.ky3.com/weather/">
										<div class=content>
										<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" width=473 height=196 title="THANKS TO KY3.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
										</div>
									</a>
								</div> -->
				<!-- END BIG SCREEN LANDSCAPE -->
			<!--    END PROMO SEVEN -->
			
			<div class="promo four">
				<a href="https://www.wunderground.com/weather-radar/united-states-regional/ks/salina"><img alt="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND" height="715" src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" title="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND" width="940"></a>
			</div>
			<div class="promo five" id="radar2">
				<a href="https://www.wunderground.com/weather-radar/united-states/mo/springfield/sgf/?region=jef"><img alt="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND" height="705" src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&showstorms=0&mapx=400&mapy=240&centerx=423.704347826087&centery=583.2&transx=23.704347826086973&transy=343.20000000000004&showlabels=1&severe=0&rainsnow=0&lightning=Hide&smooth=1&rand=25203102&lat=0&lon=0&label=you" title="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND" width="940"></a>
			</div>
		</div>
		<div class="clear-fix"></div>
		<p class="navrule1">&nbsp;</p>
		<nav>
			<a href="../index.html">HOME</a> <a href="tel:+14173273911">CALL</a> <a href="info13.html">INFO</a> <a href="http://www.fox5krbk.com/weather">(local fox weather KRBK)</a> <a href="https://www.ozarksfirst.com/weather">(local cbs weather KOLR)</a> <a href="http://www.kspr.com/weather">(local abc weather KSPR)</a> <a href="http://www.ky3.com/weather/">(local nbc weather KY3)</a> <a href="https://www.google.com/#q=weather+65613">googleweather</a> <a href="https://weather.com/weather/today/l/USMO0087:1:US">(weather channel)</a> <a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">(forecast weather underground)</a> <a href="https://www.wunderground.com/weather-radar">(regional radar weather underground)</a> <a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">(local radar weather underground)</a> <a href="http://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a> <a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/">(rise and set times of the planets)</a> <a href="https://www.suncalc.org/#/37.6018,-93.4155,18/%3C?php%20echo%20date(">/<?php echo date("h:i");?>/1/0"&gt;SUN</a> <!-- https://www.suncalc.org/#/37.6018,-93.4155,18/YYYY.MM.DD/HH:MM/1/0 -->
			<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a> <a href="https://www.moonconnection.com/moon_module.phtml">moon1</a> <a href="http://aa.usno.navy.mil/imagery/moon">moon2</a> <a href="http://w1.weather.gov/xml/current_obs/KSGF.xml">WEATHER DATA (LEFT CLICK GUI / RIGHT CLICK "SAVE AS" XML DATA)</a> <a href="../index.html">HOME</a>
		</nav>
		<div class="clear-fix"></div>
		<footer style="page-break-inside:avoid;">
			<a href="https://www.google.com/search?source=hp&q=DOUG+FELLOWS+site%3Alivebolivar.com">&#169; LIVEBOLIVAR.COM</a>
		</footer>
	</div>
</body>
</html>