<!DOCTYPE HTML>
<html lang="en">
<head>
<!-- BEGIN prevent the browser from displaying the ISO-8859-1 encoded data incorrectly -->
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<!-- END prevent the browser from displaying the ISO-8859-1 encoded data incorrectly -->
<title>WEATHER14.PHP</title>
<style>
html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}html{font-family:Arial, Helvetica, sans-serif;}body{line-height:1.5em;font-size:14px;}.container{width:400px;margin:0 auto;}article{padding-top:2em;}

</style>
</head>
<body class="container">

<!-- END prevent the browser from displaying the ISO-8859-1 encoded data incorrectly -->
<?php
exec("wget -O ksgf.xml https://w1.weather.gov/xml/current_obs/KSGF.xml"); // GET THE WEATHER DATA
$weather = simplexml_load_file('ksgf.xml');
	echo '<article>'; // ALLOWS ME TO PAD THE TOP
	{list($a,$b,$c)=explode(',',$weather->location);
	echo $b.'<br>';echo $a.",",$c.'<br>';}
	echo "Conditions:  $weather->weather <br >";
	echo "TEMP: ". (float)$weather->temp_f. "&deg;F <br>";
	// echo "TEMP: ".$weather->temp."&deg;F"."<br />\n";
	include 'sunrise-sunset.php'; // www.642weather.com/weather/scripts/sunrise-sunset.zip
	echo "WIND from the: ".'<br>';
	// return the wind data without the knots value
	if(strlen($weather->wind_string)<=5){echo $weather->wind_string.'<br>';goto end;}if(!preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo $a;goto end;}if(preg_match('/^[f]/i',$weather->wind_string)){list($a,$b)=explode('(',$weather->wind_string);echo substr($a,8,44);}end:
	echo '</article>';

?>
</body>
</html>
	
