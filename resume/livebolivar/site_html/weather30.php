<?php
date_default_timezone_set('America/Chicago');
$time_format = 'h:i A T'; // 08:53 PM PDT

      // if (data.weather) {
        // var imgURL = "http://openweathermap.org/img/w/" + data.weather[0].icon + ".png";
        // $("#weatherImg").attr("src", imgURL);
        // $("#weather-text").html(data.weather[0].description);
      // }
	
$json_string = file_get_contents("http://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&mode=JSON&units=imperial&cnt=7&APPID=889cbe42f435f153dd8768a957fd8a82");
$jsonData = json_decode($json_string, true);
$city = $jsonData['city']['name'];
$country = $jsonData['city']['country'];
$time = round ($jsonData['list'][0]['dt']);
$description = ($jsonData['list'][0]['weather']['0']['description']);
$icon = ($jsonData['list'][0]['weather']['0']['icon']);
$min_1 = round ($jsonData['list'][0]['temp']['min'],0,PHP_ROUND_HALF_DOWN);
$max_1 = round ($jsonData['list'][0]['temp']['max'],0,PHP_ROUND_HALF_UP);
$day=date("D");
// $rain=($jsonData['list'][0]['rain']);
echo $city.', '.$country.'<br>';
echo date("l, F jS, Y").'<br>';
echo date("h:i:s A").'<br><br>';
echo date("D").'<br>';
echo date("M j", $time).'<br>';
echo "<img src=http://openweathermap.org/img/w/".$icon.".png><br>";
echo $description.'<br>';
echo 'HIGH: '. $max_1.'&degF'.'<br>';
echo 'LOW: '. $min_1.'&degF'.'<br>';
// echo $rain.'/25.4 in';
?>
<!DOCTYPE HTML>
<!-- https://api.weatherunlocked.com/api/forecast/37.614479,-93.410469?app_id=8a1d2a35&app_key=5a361b954970e0cf3cc7b9f1a8a5e7b3 -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>WEATHER30.PHP</title>

<img src=http://openweathermap.org/img/w/<?php echo $icon; ?>.png>
<script>

</script>
<style>

</style>
</head>

<body>
<p id=display></p>

<script>



</script>

</body>
</html>
