
<?php
    header("Content-type: text/css; charset: UTF-8");
	$today2=date("mdy");
?>
<!--https://css-tricks.com/css-variables-with-php/ -->
/* Global Styles */
.cssgif { max-width:66px; float:right; }
.noScreen { display: none; }
span.small  { display: none; }
ul { list-style-type: disk; }
ol { list-style-type: decimal; }
/*DOUG*/ /*CORRECT VERTICAL SPACING BETWEEN EVER CHANGING NAV HEIGHT AND ARTICLE*/



body {
	color: #575c7a;
	line-height: 1.5em;
	font-family: Arial;
	font-size: 14px;
	background: #515673 url(../images/background_gradient.jpg) repeat-x 0px 0px;
	text-transform: uppercase;
	text-decoration: none; 
}
.page {
	max-width: 980px;
	margin: 0px auto;
	position: relative; background-color: #fff;
	padding: .25em;
}

h1 { color: #a6430a; margin: 0em 0em .5em 0em; font-size: 2em; font-weight: normal; }
h2 { font-size: 1.7em; margin: 0em 0em 1em 0em; }
h3 { font-size: 1.5em; margin: 0em 0em 1em 0em; }

p { margin: 0px 0px .75em 0px; }
a { color: #de9000; }
a:hover { color: #009eff; }
a.cta {
	text-transform: uppercase;
	font-size: .9em;
	font-weight: bold;
	text-decoration: none;
	margin: .5em 0 0 0;
	padding: 0px 12px 0px 0px;
	background: url(../images/cta_arrow.png) no-repeat right 0px;
}
a.cta:hover { background-position: right -50px; }
footer { font-size: .85em; color: #000; background-color: #fff; padding: 10px 10px 10px 0px; }

.promo h3 { font-size: 1.1em; margin: 0; }
.promo p { line-height: 1.2em; font-size: .9em; margin-bottom: .5em; }
/*
.promo.two { background-repeat: no-repeat; background-size: 128px 196px; }
.promo.three { background-repeat: no-repeat; background-size: 324px 196px; }
.promo.six { background-repeat: no-repeat; background-size: 324px 196px; }
*/
.promo.two { background-image: url(https://www.moonmodule.com/cs/dm/vn.gif); background-repeat: no-repeat; background-size: 128px 196px; }
/*GODADDY CRON JOB (SEVEN DAY FORECAST GRAPHIC PHP1 and SEVEN DAY FORECAST GRAPHIC PHP2) DOWNLOADS AND PROCESSES GRAPHIC FROM fox5krbr.COM AT MIDNIGHT AND 2AM EVERYDAY*/
.promo.three { background-image: url(https://www.livebolivar.com/publicbr549/seven-day-forecast3l<?php echo $today2; ?>.jpg); background-repeat: no-repeat; background-size: 324px 196px;}
.promo.six { background-image: url(https://www.livebolivar.com/publicbr549/seven-day-forecast3p<?php echo $today2; ?>.jpg); background-repeat: no-repeat; background-size: 324px 196px; }

 
 /*
.promo.three { background-image: url(https://www.livebolivar.com/publicbr549/seven-day-forecast.jpg); }
.promo.three { background-image: url(https://gray.ftp.clickability.com/ksprwebftp/Seven_Day.JPG); }
*/
.promo.four { background-repeat: no-repeat; background-size: 980px 715px; background-image: url(https://icons.wxug.com/data/weather-maps/radar/united-states/salina-kansas-region-current-radar-animation.gif); }
.promo.five { background-repeat: no-repeat; background-size: 980px 715px; background-image: url(https://radblast.wunderground.com/cgi-bin/radar/WUNIDS_map?station=SGF&brand=wui&num=10&delay=15&type=N0R&frame=0&scale=0.125&noclutter=1&lat=0&lon=0&label=you&showstorms=0&map.x=400&map.y=240&centerx=382&centery=567&transx=-18&transy=327&showlabels=1&severe=0&rainsnow=1&lightning=0&smooth=1); }


nav a {
	color: #f5a06e;
	text-transform: uppercase;
	text-decoration: none;
	display: inline-block;
	font-weight: bold;
	font-size: .9em;
}
nav a:hover { color: #fff; }

.clear-fix { clear: both; line-height: 1px; }

.print { display: none; }

/*@media
	only screen and (-webkit-min-device-pixel-ratio: 2),
	only screen and (   min--moz-device-pixel-ratio: 2),
	only screen and (     -o-min-device-pixel-ratio: 2/1),
	only screen and (        min-device-pixel-ratio: 2),
	only screen and (                min-resolution: 192dpi),
	only screen and (                min-resolution: 2dppx)
{
	.promo.one { background-image: url(../images/promo_1_2x.jpg); }
	.promo.two { background-image: url(../images/promo_2_2x.jpg); }
	.promo.three { background-image: url(../images/promo_3_2x.jpg); }
}
*/
