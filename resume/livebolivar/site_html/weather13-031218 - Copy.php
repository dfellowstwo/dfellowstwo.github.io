<!-- 
WRITTEN FOR PHP Version 5.3.24 
https://www.lynda.com/PHP-tutorials/Up-Running-PHP-SimpleXML/370013-2.html">Learning PHP SimpleXML - LYNDA.COM // USING SIMPLEXML TO PARSE THE WEATHER DATA FROM
https://w1.weather.gov/xml/current_obs/KSGF.xml
https://www.642weather.com/weather/sunrise-sunset.php
lat=37.6268&lon=-93.4313 // changed lat and lon from 37.62 and -93.43 to 37.6268 and -93.4313 in sunrise-sunset.php
ANOTHER SOURCE OF WEATHER DATA:
wget -O 1.xml "https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml" --no-check-certificate
"id": 4409896 // springfield,mo id
https://forecast-v3.weather.gov/documentation?redirect=legacy
https://api.weather.gov/stations/KSGF/observations/ksgf
https://api.weather.gov/stations/KSGF
PHP DISPLAY ERRORS IS TURNED OFF.
TURN ON PHP DISPLAY ERRORS BY CHANGING ini_set('display_errors', 0); TO ini_set('display_errors',1);
LOGFILE '/home/content/86/9256686/html/ERROR-LOGS/PHP-ERROR.LOG'
-->
<!DOCTYPE HTML>
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
<script src=../scripts/html5shiv.js></script>
<![endif]-->

<style type=text/css>
<!-- BEGIN RESET CSS -->
<!-- END RESET CSS -->

html{font-family:Arial, Helvetica, sans-serif;}
body{line-height:1em;font-size:14px;}
.page{width:960px;margin:0 auto;}

@media only screen and (min-width:976px){
article { padding: 12em 0 0 0; }
}
@media only screen and (min-width:936px) and (max-width:975px){
article { padding: 14em 0 0 0; }
}
@media only screen and (max-width:750px){article{padding:1em 0 0 0}
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
@media only screen and (max-width:430px){
.promo_container .promo.three img {
width:473px;
margin: 0 auto;
float: none;
}
}
/* ARTICLE PADDING. START THE FIRST LINE OF TEXT BELOW THE NAVIGATION */
@media only screen and (min-width:810px){.promo.three{display:none}}
@media only screen and (max-width:810px){.promo.seven{display:none}}
@media screen and (orientation:portrait){.promo.three{display:none}}
@media screen and (orientation:landscape){.promo.six{display:none}}
.promo_container .promo.four img {
width: 940px;
}
.promo_container .promo.five img {
width: 940px;
}
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
width:315px;
margin: 0 auto;
float: none;
}
.promo .one .content {
line-height: 1em;
}



@media only screen and (min-width:50px) and (max-width:600px){
.promo_container .promo.one {width: 33%; float:none;}
.promo_container .promo.two {width: 33%; float:left;}
.promo_container .promo.three {width: 33%; float:none;}
.clear-fix{clear:both;line-height:1px}
}

</style>
<!-- BEGIN JAVASCRIPT HH:MM:SS AM/PM -->
<script type="text/javascript">/*<![CDATA[*/window.onload=function(){DisplayCurrentTime()};function DisplayCurrentTime(){var d=new Date();var b=d.getHours()>12?d.getHours()-12:d.getHours();var a=d.getHours()>=12?"PM":"AM";b=b<10?"0"+b:b; b=b>00<01?"12"-b:b;var e=d.getMinutes()<10?"0"+d.getMinutes():d.getMinutes();var f=d.getSeconds()<10?"0"+d.getSeconds():d.getSeconds();time=b+":"+e+":"+f+" "+a;var c=document.getElementById("ztime");c.innerHTML=time};/*]]>*/
</script>
<!-- END JAVASCRIPT HH:MM:SS AM/PM -->

</head>

<body>
<div class=page>
<header><a class=logo href=tel:+14173273911></a></header>
<h1 class="noPrint noScreen">PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5</h1>
<article>
<h1>WEATHER SPRINGFIELD, MISSOURI</h1>
</article>
<div class=promo_container>
<div class="promo one">
<div class=content>

 <?php 

/*	1.  CACHE BUSTING HASHSTAMP IN GRAPHICS FILENAMES.
	2.	GOTO END; AROUND LINE 214.
	3.	WRITTEN FOR PHP Version 5.3.24 
 */

// DATE FORMAT https://php.net/manual/en/function.date.php
ini_set('display_errors',0);ini_set('log_errors',1);ini_set('error_log','/home/content/86/9256686/html/ERROR-LOGS/PHP-ERROR.LOG');
$hash1 = hash_file('crc32', '../images/moonphase.gif'); $hash2 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicl.jpg'); 
$hash3 = hash_file('crc32', '../images/weather-seven-day-forecast-graphicp.jpg');
date_default_timezone_set('America/Chicago');
// BEGIN UPDATE WEATHER DATA DISPLAYED ON THIS PAGE
$url56 = 'https://w1.weather.gov/xml/current_obs/KSGF.xml'; // DOWNLOAD WEATHER DATA
$filename = 'ksgf.xml';
$now  = time(); //seconds since Jan 01 1970. (UTC)
// if ksgf.xml exists and was modified w/n the last one hour do not download it.
if (file_exists($filename)) {
// ECHO "the # of seconds since 010170: $now <br>"; //
// ECHO "the # of seconds since 010170 $filename was modified:" . filemtime($filename) . "<br>";
// ECHO "If " . ($now - filemtime($filename)) . " is less than 3600 the weather data is up to date. <br>";
// if ($now - filemtime($filename) <= 60 * 60) echo "Weather data is up to date. <br>";
if ($now - filemtime($filename) >= 60 * 60) { // 1 hour in seconds
	// echo "Downloading the weather data ...<br> ";
	exec("wget -O $filename $url56"); // GET THE WEATHER DATA
}
} else {
	// echo "local $filename does not exist. <br>Downloading the weather data ...<br>";
		exec("wget -O $filename $url56"); // GET THE WEATHER DATA
		}
// END UPDATE THE WEATHER DATA DISPLAYED ON THIS PAGE

$weather = simplexml_load_file($filename);
{list($a,$b,$c)=explode(',',$weather->location);
echo "$b <br>";
echo "$a, $c <br>";}
echo date("l, F jS, Y").'<br>';
// echo date("h:i:s A").'<br>';
// echo "document.body.innerHTML = dayNames[d.getDay()-1] +\", \"+ month +\" \"+ date+nth(date) +\", \"+fortnightAway.getFullYear().<br>";
// echo "<script>document.body.innerHTML = dayNames[d.getDay()-1] +\", \"+ month +\" \"+ date+nth(date) +\", \"+fortnightAway.getFullYear();</script>";

?>



<?php
// echo "<span id="ztime"></span><br>";
echo "<span id=\"ztime\"></span><br>";



echo "Conditions:  $weather->weather <br>";
echo "TEMP: ". (float)$weather->temp_f. "&deg;F <br>";
include 'sunrise-sunset.php'; // www.642weather.com/weather/scripts/sunrise-sunset.zip
echo "WIND from the: <br>";
// return wind data if it is calm. calm<=5
// if "from the" is not present, return wind data and do not return the knots value. knots value shows up in parentheses (i.e (7KT)).
// if "from the" is present strip it out and do not return the knots value. knots value shows up in parentheses.
if(strlen($weather->wind_string)<=5){echo $weather->wind_string; goto end;}
if(!preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo $a;goto end;}
if(preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo substr($a,8,44);}
end:
	
?>



</div>

</div>
        
<div class="promo two">

			  

<a href=https://www.moonconnection.com/moon_module.phtml>

<div class=content>

<img src="../images/moonphase-<?php echo $hash1;?>.gif"  alt="MOON PHASE GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/moonphase-<?php echo $hash1;?>.gif">

</div></a>

</div>



<div class="promo three">

<!-- small screen landscape -->		    

<a href=https://www.fox5krbk.com/weather>

<div class=content>

<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">

</div></a>

</div>



<div class="promo seven">

<!-- big screen landscape -->		

<a href=https://www.fox5krbk.com/weather>

<div class=content>

<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">

</div></a>

</div>

		    

<div class="promo six">

					   

<a href=https://www.fox5krbk.com/weather>

<div class=content>

<img src="../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg" title="THANKS TO FOX5KRBK.COM FOR THIS SEVEN DAY OUTLOOK GRAPHIC" alt="SEVEN DAY FORECAST GRAPHIC.  YOUR CLOCK IS A LITTLE DIFFERENT THAN MINE.  THIS UPDATES AT MIDNIGHT CST/CDT.  REFRESH IN ONE MINUTE OR CLICK ON THIS TO GO TO THE SOURCE.  ../images/weather-seven-day-forecast-graphicl-<?php echo $hash2;?>.jpg">

</div></a>

</div>



<div class="promo four">

<div class=content> <a href="https://www.wunderground.com/weather-radar/united-states-regional/ks/salina"><img src="https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif" alt="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="REGIONAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>

</div>

</div>

    

<div id=radar2 class="promo five">

<div class=content> <a href="https://www.wunderground.com/weather-radar/united-states/mo/springfield/sgf/?region=jef"><img src="https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&showstorms=0&mapx=400&mapy=240&centerx=382&centery=567&transx=-18&transy=327&showlabels=1&severe=0&rainsnow=1&lightning=0&smooth=1&rand=25127676&lat=0&lon=0&label=you" alt="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND" title="LOCAL WEATHER RADAR FROM WEATHER UNDERGROUND"></a>

</div>

</div>

    

</div>

		

<div class=clear-fix></div>

					    

		

<nav>
<a href=../index.html>HOME</a>
<a href=tel:+14173273911>CALL</a>
<a href=info13.html>INFO</a>
<a href=https://www.fox5krbk.com/weather>(local fox weather KRBK)</a>
<a href=https://www.ozarksfirst.com/weather>(local cbs weather KOLR)</a>
<a href=https://www.kspr.com/weather>(local abc weather KSPR)</a>
<a href=https://www.ky3.com/weather/>(local nbc weather KY3)</a>
<a href="https://www.google.com/#q=weather+65613">googleweather</a>
<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">(weather channel)</a>
<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">(forecast weather underground)</a>
<a href=https://www.wunderground.com/weather-radar/>(regional radar weather underground)</a>
<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">(local radar weather underground)</a>
<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
<a href=https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d');?>>(rise and set times of the planets)</a>
<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a>
<a href=https://www.moonconnection.com/moon_module.phtml>moon1</a>
<a href=https://aa.usno.navy.mil/imagery/moon>moon2</a>    
<a href=https://w1.weather.gov/xml/current_obs/KSGF.xml>WEATHER DATA (LEFT CLICK GUI / RIGHT CLICK "SAVE AS" XML DATA) 
    

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

