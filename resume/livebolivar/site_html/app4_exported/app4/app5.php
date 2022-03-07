<?php
$timestamp=date("mdyhis");
copy ("application.jpg", "application$timestamp.jpg");
// echo $_SERVER['DOCUMENT_ROOT'];
?>
<!DOCTYPE HTML>
<!-- saved from url=(0014)about:internet -->
<html lang="en">
<head>
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<title>APP SCREENSHOT</title>
<style>
.container {
	width:752px;
	margin:0 auto;
	}
button {float:right}
img {
  display: block;
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: 90%;
  margin: 20px auto;
@media only screen and ( max-width:600px ) { 
.container {
	width:600px;
}
img {
  width: 300px;
  
}}
</style>
</head>

<body class=container>
<img class="doug" src="../../../images/_banner4.jpg" alt="" usemap="#Map">
<map name="Map"><area shape="rect" coords="313,122,652,141" href="MAILTO:AJELLIS@LIVEBOLIVAR.COM" title="CLICK HERE TO SEND ME AN EMAIL" alt="EMAIL." data-tooltip="email"><area shape="rect" coords="-10,122,305,136" href="TEL:+14177775049" title="CLICK TO CALL LORI AT 417-777-5049" alt="  PHONE." data-tooltip="skype2"><area shape="rect" coords="173,1,650,105" href="#" onClick="history.go(-1);return false;" title="PROPERTY AVAILABLE NOW OR IN 30 DAYS" alt="  GO BACK." data-tooltip="banner"><area shape="rect" coords="654,4,748,132" href="../../site_html/map_office13.html" title="CLICK HERE FOR A MAP TO THE OFFICE" alt="  MAP TO THE OFFICE." data-tooltip="office"><area shape="rect" coords="1,1,170,105" href="../about-us13.php" title="CLICK HERE TO LEARN MORE ABOUT US" alt="  ABOUT US." data-tooltip="aj">
<area shape="rect" coords="0,107,653,120" href="../../site_html/map_office13.html" alt="  MAP TO THE OFFICE." title="CLICK HERE FOR A MAP TO THE OFFICE" data-tooltip="map_address">
</map>
<!-- <p><button onclick="history.go(-1);">Back </button></p> -->
<img src="application<?php echo "$timestamp"?>.jpg" alt="application screenshot">
<!-- <img src="application.jpg" alt="application screenshot"> -->
<?php
// echo "<img src=application.jpg?" . time() . ">";
?>
<!-- <p><button onclick="history.go(-1);">Back </button></p> -->
</body>
</html>





