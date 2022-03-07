<!DOCTYPE HTML>
<!-- 
PHP DISPLAY ERRORS IS TURNED OFF.
TURN ON PHP DISPLAY ERRORS BY CHANGING ini_set('display_errors', 0); TO ini_set('display_errors',1);
LOGFILE '/home/content/86/9256686/html/ERROR-LOGS/PHP-ERROR.LOG'
-->
<html lang="en">
<head>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<meta name=viewport content="width=device-width, initial-scale=1.0">
<title>WEATHER RADAR SPRINGFIELD,MO</title>
<link rel=stylesheet type=text/css href=screen_styles.css />
<link rel=stylesheet type=text/css href=screen_layout_large.css />
<link rel=stylesheet type=text/css media="only screen and (min-width:50px) and (max-width:600px)" href=screen_layout_small.css>
<link rel=stylesheet type=text/css media="only screen and (min-width:601px) and (max-width:800px)" href=screen_layout_medium.css>
<link rel=stylesheet type=text/css media=print href=print.css />
<link rel=icon href="../favicon.ico?v=1.1">
<link href=../images/apple-touch-icon.png rel=apple-touch-icon>
<link href=../images/apple-touch-icon-76x76.png rel=apple-touch-icon sizes=76x76>
<link href=../images/apple-touch-icon-120x120.png rel=apple-touch-icon sizes=120x120>
<link href=../images/apple-touch-icon-152x152.png rel=apple-touch-icon sizes=152x152>
<link href=../images/apple-touch-icon-180x180.png rel=apple-touch-icon sizes=180x180>
<link href=../images/icon-hires.png rel=icon sizes="192x192">
<link href=../images/icon-normal.png rel=icon sizes="128x128">
<!--[if lt IE 9]>
<script src=../scripts/html5shiv.js></script>
<![endif]-->


<style type=text/css>
@media only screen and (max-width:800px){article{padding:1em 0 0 0}header{display:none}article h1{display:none}.navrule1{display:none}}
@media only screen and (min-width:801px) and (max-width:805px){article{padding:20em 0 0 0;text-align:center}}
@media only screen and (min-width:806px) and (max-width:810px){article{padding:17em 0 0 0;text-align:center}}
// @media only screen and (min-width:811px){article{padding:20em 0 0 0;text-align:center}}
// @media only screen and (min-width:851px){article{padding:19em 0 0 0;text-align:center}}
@media only screen and (min-width:810px){.promo.three{display:none}}
@media only screen and (max-width:810px){.promo.seven{display:none}}
@media screen and (orientation:portrait){.promo.three{display:none}}
@media screen and (orientation:landscape){.promo.six{display:none}}


</style>
</head>
<body>
<div class=page>
<header><a class=logo href=tel:+14173273911></a></header>
<h1 class="noPrint noScreen">PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5</h1>
<article>
<h1>WEATHER SPRINGFIELD, MO</h1>
</article>
<div class=promo_container>
<div class="promo one">
<div class=content>
 <?php 
/* CACHE BUSTING HASHSTAMP IN GRAPHICS FILENAMES */
// DATE FORMAT https://php.net/manual/en/function.date.php
ini_set('display_errors',0);ini_set('log_errors',1);ini_set('error_log','/home/content/86/9256686/html/ERROR-LOGS/PHP-ERROR.LOG');$hash1 = hash_file('crc32', '../images/moonphase.gif'); $hash2 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicl.jpg'); $hash3 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicp.jpg'); global $argc;$stationId="KSGF";require_once dirname(__FILE__).'/noaa/Forecaster.php';$config=new \noaa\weather\Configuration();$config->setCache(new \noaa\weather\cache\FileCache(dirname(__FILE__).'/cache'));$forecaster=new \noaa\Forecaster($config);try{$current=$forecaster->getCurrentWeather($stationId);}catch(\Exception $e){echo "Error: ".$e->getMessage()."\n";exit(1);}require_once('weather-Current-NOAA-Weather-Grabber.php');function noaa_weather_grabber_test($weather){if((isset($weather->okay))&&($weather->okay=="yes")){list($a,$b,$c)=explode(',',$weather->location);echo $b."<br>";echo $a.",",$c."<br>";echo date("l, F jS, Y ")."<br />\n";echo date("h:i:s A")."<br />\n";echo "TEMP: ".$weather->temp."&deg;F"."<br />\n";echo "Conditions: ";echo $weather->condition."<br />\n";include 'sunrise-sunset.php';echo "\n";}}$weather=noaa_weather_grabber('KSGF','yes','https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml');noaa_weather_grabber_test($weather);echo "WIND from the: "."<br>";if(strlen($current->getWindString())<=5){echo $current->getWindString()."<br>";goto end;}if(!preg_match('/^[f]/i',$current->getWindString())){list($a,$b)=explode('(',$current->getWindString());echo $a;goto end;}if(preg_match('/^[f]/i',$current->getWindString())){list($a,$b)=explode('(',$current->getWindString());echo substr($a,8,44);}end:?>

</div>
</div>
<div class="promo two">
			  
<a href=https://www.moonconnection.com/moon_module.phtml>
<div class=content>
<img src=../images/moonphase-<?php echo $hash1;?>.gif width=128 height=196 alt="MOON PHASE GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/moonphase-<?php echo $hash1;?>.gif">
</div></a>
</div>
    
<div class="promo three">
<!-- small screen landscape -->		    
<a href=https://www.fox5krbk.com/weather>
<div class=content>
<img src=../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg width="100%" height="100%" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
</div></a>
</div>

<div class="promo seven">
<!-- big screen landscape -->		
<a href=https://www.fox5krbk.com/weather>
<div class=content>
<img src=../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg width=473 height=196 title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
</div></a>
</div>
		    
<div class="promo six">
					   
<a href=https://www.fox5krbk.com/weather>
<div class=content>
<img src=../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg width=315 height=130 title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">
</div></a>
</div>
    
<div class="promo four">
<div class=content> <a href="https://www.wunderground.com/weather-radar/united-states-regional/ks/salina"><img src=https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif width=940px height=715px alt="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>
</div>
</div>
    
<div id=radar2 class="promo five">
<div class=content> <a href="https://www.wunderground.com/weather-radar/united-states/mo/springfield/sgf/?region=jef"><img src=https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&showstorms=0&mapx=400&mapy=240&centerx=382&centery=567&transx=-18&transy=327&showlabels=1&severe=0&rainsnow=1&lightning=0&smooth=1&rand=25127676&lat=0&lon=0&label=you width=940px height=715px alt="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>
</div>
</div>
    
</div>
		
<div class=clear-fix></div>
					    
		
<nav>
<a href=../index.html>HOME</a>
<a href=tel:+14173273911>CALL</a>
<a href=info13.html>INFO</a>
<a href=https://www.fox5krbk.com/weather>local fox weather KRBK</a>
<a href=https://www.ozarksfirst.com/weather>local cbs weather KOLR</a>
<a href=https://www.kspr.com/weather>local abc weather KSPR</a>
<a href=https://www.ky3.com/weather/>local nbc weather KY3</a>
<a href="https://www.google.com/#q=weather+65613">googleweather</a>
<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">weather channel</a>
<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">forecast weather underground</a>
<a href=https://www.wunderground.com/weather-radar/>regional radar weather underground</a>
<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">local radar weather underground</a>
<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
<a href=https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d');?>>rise and set times of the planets</a>
<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a>
<a href=https://www.moonconnection.com/moon_module.phtml>moon1</a>
<a href=https://aa.usno.navy.mil/imagery/moon>moon2</a>    

</nav>
<p class=navrule1>&nbsp;</p>
<div class="small noPrint">
<strong>Call AJ ELLIS at <a href=tel:+14173273911>1-417-327-3911</a> </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.
</div>
<footer style=page-break-inside:avoid>
<div class="print qr">
<img src=../images/qrcode.38781946.png width=100 style=float:right alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
<img src=../images/for_rent_sign.jpg width=105 alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
<em> This page printed from:</em><br />
livebolivar.com<br/>
<p> OFFICE: SPRINGHILL FALLS APARTMENTS #212 1325 S LILLIAN AVE BOLIVAR MONDAY - FRIDAY 10AM TO 5PM</p>
<p><strong>Call AJ ELLIS at 1-417-327-3911 </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.</p>
</div>
&#169; LIVEBOLIVAR.COM
<br>
<a class=noScreen href="https://www.google.com/#hl=en&amp;output=search&amp;sclient=psy-ab&amp;rlz=1C2_____enUS379&amp;q=doug+fellows+site:livebolivar.com&amp;oq=doug&amp;gs_l=hp.3.0.35i39j44i39i27j0j0i20.1460.4682.0.6146.7.6.1.0.0.0.140.706.0j6.6.0.les%3B..0.0...1c.1.5.psy-ab.l6XLIvERCyw&amp;pbx=1&amp;bav=on.2,or.r_gc.r_pw.r_cp.r_qf.&amp;bvm=bv.43148975,d.b2U&amp;fp=443df112168ae7b8&amp;biw=1440&amp;bih=762">&nbsp; &nbsp; WEBMASTER Doug Fellows</a>
<span class=noPrint>
<a href=https://jigsaw.w3.org/css-validator/check/referer><img class=cssgif src=vcss.gif alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
<a href=sitemap13.html><img class=cssgif src=placemark88x31.jpg alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
</span>
    

</footer>
<div class=clear-fix></div>
</div>

</body>
</html>
