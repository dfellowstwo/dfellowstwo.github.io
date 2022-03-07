<?php
// copy file to filename.ext-mmddyy-hhmmss.ext IN SAME DIRECTORY
// mmddyy-hhmmss IS LOCAL TIME IN 12HR FORMAT
date_default_timezone_set('America/Chicago');$file=$_SERVER["SCRIPT_FILENAME"];$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$f=pathinfo(basename($_SERVER['SCRIPT_NAME']), PATHINFO_FILENAME);$ext=pathinfo(basename($_SERVER['SCRIPT_NAME']), PATHINFO_EXTENSION);
$stamp=date("mdy-his");$f2='weather13.php-'.$stamp.$ext;$f2=$f . '.' . $ext.'-'.$stamp.'.'.$ext;
// copy($f.'.'.$ext, $f.'.'.$ext.'-'.$stamp.'.'.$ext);
// echo "copy ",$f.'.'.$ext," to ", $f.'.'.$ext.'-'.$stamp.'.'.$ext."<br>";
$HOME="/home/content/86/9256686";
$today=date("mdy");
echo $today."<br>";



// if (file_exists("$HOME/html/map")) {
    // echo "$HOME/html/map already exists <br>";
// } else {
	// mkdir ("$HOME/html/map");
	// }






// exec ("cp $HOME/html/private/2BACKUP\ DOUG/*.php $HOME/html/BACKUP/PHP/$today");
// exec("cp $HOME/html/Css/*.css $HOME/html/BACKUP",$out,$err);
// echo "BACKUP THE CSS FOLDER <br>";
// echo "RESULTS IS : ".$err."<br>";
// https://www.linuxquestions.org/questions/linux-software-2/copying-folders-with-spaces-between-157180/
// exec("cp -f $HOME/html/private/2BACKUP\ DOUG/*.php $HOME/html/BACKUP/PHP/$today",$out,$err);
// echo "BACKUP FILES THAT END WITH PHP TO PHP FOLDER/DAILY FOLDER <br>";
// echo "RESULTS IS : ".$err."<br>";



$url = "https://maps.google.com/maps/api/geocode/json?address=1325+S+LILLIAN+AVE+BOLIVAR+MO+65613&sensor=false&region=US";
$response = file_get_contents($url);
$response = json_decode($response, true);
 
//print_r($response);
 
$lat = $response['results'][0]['geometry']['location']['lat'];
$long = $response['results'][0]['geometry']['location']['lng'];
 
echo "latitude: " . $lat . " longitude: " . $long."<br>";


?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=320" />
<title>SITE_HTML-1-PHP</title>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCUZCL6Msvi1MSDXkXy-MJ1GjZmCHDzOzA&callback=initMap" type="text/javascript"></script>
		
</head>
<body>


<script type="text/javascript">

</script>


<a href="https://www.google.com/maps/dir/?api=1&destination=37.6019214,-93.4168756&travelmode=driving&zoom=13">link1</a>
 <p>&nbsp;</p>
    
    37.601848,-93.415522
    37.6019214,-93.4168756
    
    YOUR API KEY  
AIzaSyCUZCL6Msvi1MSDXkXy-MJ1GjZmCHDzOzA 
restrictions - http referrrers - *.livebolivar.com/* and livebolivar.com/*
    
    handleNavigation(la, lo) {
  const rla = this.region.latitude;
  const rlo = this.region.longitude;
  const url = `https://maps.apple.com/?saddr=${rla},${rlo}&daddr=${la},${lo}&dirflg=d`;
  return Linking.openURL(url);
}

   
</body>

</html>