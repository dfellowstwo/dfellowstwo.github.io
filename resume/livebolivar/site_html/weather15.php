<!DOCTYPE HTML>
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
<p class="noScreen"><strong>Call AJ ELLIS at 1-417-327-3911 </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.</p>
		<link rel="stylesheet" type="text/css" href="screen_styles.css" />
		<link rel="stylesheet" type="text/css" href="screen_layout_large.css" />
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:500px)"   href="screen_layout_small.css">
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:501px) and (max-width:800px)"  href="screen_layout_medium.css">
		<link rel="stylesheet" type="text/css" media="print" href="print.css" />						
*/
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1" />
		<title>INDEX5.PHP</title>
		
		
		
		<!--[if lt IE 9]>
			<script src="../files/html5shiv.js"></script>
		<![endif]-->
		<style type="text/css">
			body {
				color: #000;
				line-height: 1em;
				font-family: Arial;
				font-size: 14px;
				text-decoration: none;
				text-transform: uppercase;
				}
				
				nav a:hover { color: #000; }
				nav a {
					text-decoration: none;
					display: inline-block;
					font-weight: bold;
					font-size: .9em;
					margin: 5px 0px 4px 20px;  
					
					}
					
			@media only print { 
				body { font: 12pt Georgia, "Times New Roman", Times, serif;
					line-height: 1;
					background: #fff;
					color: #000;
					}
				nav { display: none;}
								}
			
			@media only screen {
				footer { display:none;}
				
			}
				
		</style>  
	</head>
	<body>

		<div class="page">
			<header>
				<img src="../images/print_banner_large.jpg" class="print" width="100%" />
				<a class="logo" href="#"></a>
			</header>
			<article>
				
			</article>
			<div class="promo_container">
				<div class="promo one">
					<div style="width:33%; float:left" class="content">
						<?php
				// WIND SPEED AND DIRECTION
				global $argc;
				// https://github.com/amwhalen/noaa
				// station ID
				$stationId = "KSGF";
												
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
					  // echo "Conditions: " . "<br />\n";
					  echo "Conditions: ";
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

				// Get the weather
				$weather = noaa_weather_grabber( 'KSGF', 'yes', 'https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml');
				// Run the weather test function
				noaa_weather_grabber_test( $weather );

				// GET THE WIND SPEED AND DIRECTION
				// https://github.com/amwhalen/noaa  (SEE ABOVE)
				echo "WIND from the: " . "<br>";
				// echo $current->getWindString() . "<br>";
				// if the wind is calm display calm.  list ($a, $b) will fail if the delimiter is not present.
				// so far the delimiter is present if the wind is not calm
				// Variable at 3.5 MPH (3 KT)
					if(strlen($current->getWindString()) <= 5){
					echo $current->getWindString() . "<br>";
					} else {
					list ($a, $b) = explode('(', $current->getWindString());}
					// preg_match checks to see if the first word is "from" and if it is uses substr to cut "from the".
					if (preg_match('/^[f]/i', $current->getWindString())) {
					echo substr($a, 8, 44) . "<br>"; // starting at the eighth character echo 44 characters
					} else {
					echo $a  . "<br>";}
				// echo $a  . "<br>";}
				// $a = "NORTHWEST AT 13.9 MPH GUSTING TO 20,000 MPH";
					echo "<br>";
					echo "Call AJ ELLIS at 1-417-327-3911 for all your property rental needs M-F 9-5.&nbsp; WELCOME TO BOLIVAR.";

				?>
					</div>
				</div>
				
				<div class="promo two">
					<div class="content">
						<a href="https://www.kspr.com/weather"><img style="width:43%; float:right" src="https://gray.ftp.clickability.com/ksprwebftp/Seven_Day.JPG" width="100%" alt=" "></a>
					</div>
				</div>
				<div class="promo three">
					<div class="content">
						<a href="https://www.moonconnection.com/moon_module.phtml"><img style="height: 250px;" src="https://www.moonmodule.com/cs/dm/vn.gif"></a>
					</div>
				</div>
		
				<div class="promo four">
					<div class="content">
						<a href="https://www.wunderground.com/radar/mosaic.asp"><img src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" width="100%" alt=" "></a>
			  		</div>
				</div>
				
				<div class="promo five">
					<div class="content">
						<a href="https://radblast-aa.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&amp;brand=wui&amp;num=6&amp;delay=15&amp;type=N0R&amp;frame=0&amp;scale=0.125&amp;noclutter=0&amp;t=1190809588&amp;lat=0&amp;lon=0&amp;label=you&amp;showstorms=0&amp;map.x=400&amp;map.y=240&amp;centerx=470&amp;centery=530&amp;transx=70&amp;transy=290&amp;showlabels=1&amp;severe=0&amp;rainsnow=0&amp;lightning=0">
						<img id="radar2" src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&lat=0&lon=0&label=you&showstorms=0&map.x=400&map.y=240&centerx=382&centery=567&transx=-18&transy=327&showlabels=1&severe=0&rainsnow=1&lightning=0&smooth=1" width="100%" alt=" ">
						</a>
                    
					</div>
				</div>
				<div class="clear-fix"></div>
			</div>
			<nav>
				 <a href="tel:+14173273911">CALL</a>
				<a href="info13.html">INFO</a>
				<a href="available.html">DESKTOP</a>
				<a href="https://www.ozarksfirst.com/weather">CBS</a>
				<a href="https://www.kspr.com/weather">ABC</a>
				<a href="https://www.ky3.com/weather/">NBC</a>
				<a href="https://www.google.com/#q=weather+65613">googleweather</a>
				<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">weather channel</a>
				<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">weather underground</a>
				<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
				<a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d'); ?>">rise/set planets</a>
				<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (nearest city then nowdata tab)</a>
			
			</nav>
			<footer style="page-break-inside:avoid;">

				<div class="print qr">
					<img src="qrcode.38781946.png" width="100" style="float:right;" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
					<img src="for_rent_sign.jpg" width="105" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
					<em>	This page printed from:</em><br />
							livebolivar.com<br/>
					 <p>	OFFICE: SPRINGHILL FALLS APARTMENTS #212 1325 S LILLIAN AVE BOLIVAR MONDAY - FRIDAY 10AM TO 5PM</p>
					<p><strong>Call AJ ELLIS at 1-417-327-3911 </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.</p>
				</div>
				&#169; LIVEBOLIVAR.COM
				<span class="noPrint">
				    <a href="https://jigsaw.w3.org/css-validator/check/referer"><img id="cssgif" src="vcss.gif" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				    <a href="site_html/sitemap13.html" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."><img id="cssgif" src="sitemap-placemark.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				    <a href="private" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."><img id="cssgif" src="sitemap-placemark.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				</span>
			</footer>
		</div>

	</body>
</html>
