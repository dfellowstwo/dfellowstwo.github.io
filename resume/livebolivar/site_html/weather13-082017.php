<!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1" />-->
		<title>WEATHER RADAR SPRINGFIELD,MO</title>
        <!--<link rel="stylesheet" type="text/css"  href="reset.css" /> -->
		<link rel="stylesheet" type="text/css"  href="screen_styles.css" />
		<link rel="stylesheet" type="text/css"  href="screen_layout_large.css" />
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:600px)"    href="screen_layout_small.css">
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:601px) and (max-width:800px)"   href="screen_layout_medium.css">
		
		<link rel="stylesheet" type="text/css" media="print"  href="print.css" />
        
        <link rel="icon" href="../favicon.ico?v=1.1"> 
		<link href="../images/apple-touch-icon.png" rel="apple-touch-icon">
		<link href="../images/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76">
		<link href="../images/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120">
		<link href="../images/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152">
		<link href="../images/apple-touch-icon-180x180.png" rel="apple-touch-icon" sizes="180x180">
		<link href="../images/icon-hires.png" rel="icon" sizes="192x192">
		<link href="../images/icon-normal.png" rel="icon" sizes="128x128">
		
		<!--[if lt IE 9]>
			<script src="html5shiv.js">
			</script> -->
		<style type="text/css">
			/*DOUG*/ /*CORRECT VERTICAL SPACING BETWEEN EVER CHANGING NAV HEIGHT AND ARTICLE*/
			
			@media only screen and (max-width:600px) {
			article { padding:1em 0 0 0;}
			article h1 { display:none; }
			}
						
			@media only screen and (min-width:801px) and (max-width:835px) {
			article { padding: 14em 0 0 0; text-align: center; }
			}
			
			
			@media only screen and (min-width:836px){
			article { padding: 11em 0 0 0; text-align: center; }
			}
			
		</style>
	</head>
	<body>

		<div class="page">
			<header><a class="logo" href="tel:+14173273911"></a></header>
            <h1 class="noPrint noScreen">PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5</h1>
			<article>
				<h1>WEATHER SPRINGFIELD, MO</h1>
			</article>
			<div class="promo_container">
				<div class="promo one">
					<div class="content">
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
							  echo $weather->condition . "<br />\n";
							  // over 40 characters pushes the adjacent graphic down
							  // $text = $weather->condition;
							  // $newtext = wordwrap($text, 40, "<br />\n");
							 //  echo $newtext . "<br />\n";
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
							goto end;}
							//if (!preg_match('/^[f]/i', $current->getWindString())) { echo $current->getWindString() ;} 
							//goto end;
							if (!preg_match('/^[f]/i', $current->getWindString())) {
							list ($a, $b) = explode('(', $current->getWindString());
							// preg_match checks to see if the first word is "from" and if it is uses substr to cut "from the".
							echo $a;
							goto end; }
							if (preg_match('/^[f]/i', $current->getWindString())) {
							list ($a, $b) = explode('(', $current->getWindString());
							echo substr($a, 8, 44);}					
							
							end:
						// echo $a  . "<br>";}
						// $a = "NORTHWEST AT 13.9 MPH GUSTING TO 20,000 MPH";
							// echo "<br>";
							// echo "Call AJ ELLIS at 1-417-327-3911 for all your property rental needs M-F 9-5.&nbsp; WELCOME TO BOLIVAR.";

						?>
					</div>
				</div>
				<div class="promo two">
					<a href="https://www.moonconnection.com/moon_module.phtml">
                    <div class="content">
						<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
					</div></a>
				</div>
				
				<div class="promo three">
					<div class="content">
						<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
					</div>
				</div>
				
                <div class="clear-fix"></div>
                
				<div class="promo four">
					<div class="content"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>						<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
					</div>
				</div>
				
				<div id="radar2" class="promo five">
					<div class="content"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
					</div>
				</div>
				
			</div>

			<div class="clear-fix"></div>
            <nav>
				<a href="../index.html">HOME</a>
				<a href="tel:+14173273911">CALL</a>
				<a href="info13.html">INFO</a>
				<a href="https://www.ozarksfirst.com/weather">local cbs weather</a>
				<a href="https://www.kspr.com/weather">local abc weather</a>
				<a href="https://www.ky3.com/weather/">local nbc weather</a>
				<a href="https://www.google.com/#q=weather+65613">googleweather</a>
				<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">weather channel</a>
				<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">forecast weather underground</a>
				<a href="https://www.wunderground.com/weather-radar/">regional radar weather underground</a>
				<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">local radar weather underground</a>
				<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
				<a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d'); ?>">rise and set times of the planets</a>
				<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a>
				<a href="https://www.moonconnection.com/moon_module.phtml">moon phase</a>
				<a href="../prop_html/available.html">DESKTOP</a>

			</nav>
            <span class="small noPrint">
				<p><strong>Call AJ ELLIS at 1-417-327-3911 </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.</p>
			</span>
			<p>&nbsp; </p>
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
				    <a href="site_html/sitemap13.html" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."><img id="cssgif" src="placemark88x31.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				    <a href="../private" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."><img id="cssgif" src="placemark88x31.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				</span>
			</footer>
		</div>

	</body>
</html>
