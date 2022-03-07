<?php

//units=For temperature in Celsius use units=metric
//5128638 is new york ID

$jsonfile =  file_get_contents("http://api.openweathermap.org/data/2.5/weather?id=4377835&mode=json&lang=en&units=imperial&APPID=889cbe42f435f153dd8768a957fd8a82");
date_default_timezone_set('America/Chicago');
$jsondata = json_decode($jsonfile);
$temp = $jsondata->main->temp;
$pressure = $jsondata->main->pressure;
$mintemp = $jsondata->main->temp_min;
$maxtemp = $jsondata->main->temp_max;
$wind = $jsondata->wind->speed;
$humidity = $jsondata->main->humidity;
$desc = $jsondata->weather[0]->description;
$maind = $jsondata->weather[0]->main;


$jsonfile = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Bolivar,MO&units=imperial&appid=889cbe42f435f153dd8768a957fd8a82");

$jsondata = json_decode($jsonfile);
$temp = $jsondata->main->temp;
$pressure = $jsondata->main->pressure;
$mintemp = $jsondata->main->temp_min;
$maxtemp = $jsondata->main->temp_max;
$wind = $jsondata->wind->speed;
$humidity = $jsondata->main->humidity;
$desc = $jsondata->weather[0]->description;
$maind = $jsondata->weather[0]->main;

?>

<?php
	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/forecasts/:auto?&format=json&filter=day&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		echo "https://api.aerisapi.com/forecasts/:auto?&format=json&filter=day&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA <br><br>";
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}
	
	
echo "<br><br><br>";

	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/forecasts/:auto?&format=json&filter=mdnt2mdnt&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windSpeedMinMPH,periods.windDirMin,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		echo "https://api.aerisapi.com/forecasts/:auto?&format=json&filter=mdnt2mdnt&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windSpeedMinMPH,periods.windDirMin,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA <br><br>";
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}
echo "<br><br><br><br><br><br>";

// https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=1&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA


	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=7&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		echo "https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=7&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA <br><br>";
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}
echo "<br><br><br><br><br><br>";

https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=7&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA


	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=7&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		echo "https://api.aerisapi.com/observations/bolivar,mo?&format=json&filter=allstations&limit=7&fields=id,loc,ob.dateTimeISO,ob.tempF,ob.dewpointF,ob.humidity,ob.windSpeedMPH,ob.windDir,ob.weather,ob.heatindexF,ob.windchillF,ob.feelslikeF&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA <br><br>";
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}
echo "<br><br><br><br><br><br>";
?>
<!DOCTYPE html>
<html>
<head>
	<title>WEATHER21.PHP</title>
	<link href="weather18-global_menus.min.css" rel="stylesheet" type="text/css">
	<link href="weather18-hw-min-141012a.css" rel="stylesheet" type="text/css">
	<link href="weather18-calendar_161103.min.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
body,td,th {
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
}
body {
	margin: 20px;

}
</style>
<script>


var http = require("http");

// Request your own Key at: http://openweathermap.com/
var API_Key = "889cbe42f435f153dd8768a957fd8a82";
var Location = "4377835";




/*
 if you like to see more details just dump the entire data!
*/
function DumpWeather( OWMData ) {

  /* remove comment below to see full data */
  console.log(OWMData);
  console.log("Temperature: " +  (OWMData.main.temp-273.15));
  console.log("Pressure:    " +  (OWMData.main.pressure) + "hPa");
  console.log("Humidity:    " +  (OWMData.main.humidity) +"%");
  console.log("Wind speed:  " +  OWMData.wind.speed + "m/s");
  console.log("Weather:     " +  OWMData.weather[0].description);
  var SRD = new Date(OWMData.sys.sunrise*1000);
  console.log("Sunrise:     " +  SRD.getHours() + ":" +SRD.getMinutes());
  var SSD = new Date(OWMData.sys.sunset*1000);
  console.log("Sunset:      " +  SSD.getHours() + ":" +SSD.getMinutes());
  var SAM = new Date(OWMData.dt*1000);
  console.log("Sample:      " +  SAM.getHours() + ":" +SAM.getMinutes());

}

/*
 This function dump the data which is received when forecast was requested.
*/
function DumpForecast( OWMData ) {
  //console.log(OWMData);    
  for (i=0; i < OWMData.cnt; i++ )
  {
    console.log(OWMData.list[i].dt_txt + " - " + (OWMData.list[i].main.temp-273.15) );
  }
}

/* Generate the OWM Request 
This function generates a string which can be used to request the weather data.
*/
function GetOWM_Request ( RequestType, LocationID, APIKey )
{
  var OWM = "https://api.openweathermap.org/data/2.5/";
  var Result = "error";
  if ( ( RequestType == "weather") || (( RequestType == "forecast")) )
  {
    Result = OWM + RequestType+ "?id=" + Location + "&appid=" + APIKey;
  } else {
    console.log ( "unsupported OWM Request" );
  }
  return Result;
}

function DoInit() {

  var Req = "";
  // Request weather data
  Req = GetOWM_Request("weather", Location, API_Key);
  print ("Request: "+ Req);
  http.get(Req, function(res) {
    var contents = "";
    res.on('data', function(data) { contents += data; });
    res.on('close', function() { DumpWeather(JSON.parse(contents)); });
  }); 

  /* 
  // Reqeust forecast data
  Req = GetOWM_Request("forecast", Location, API_Key);
  print ("Request: "+ Req);
  http.get( Req, function(res) {
    var contents = "";
    res.on('data', function(data) { contents += data; });
    res.on('close', function() { DumpForecast(JSON.parse(contents)); });
  });
  */


}

DoInit();


</script>
</head>
<body>
<p>i would like to take:
</p>
<blockquote>
http://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&mode=xml&units=imperial&cnt=7&APPID=889cbe42f435f153dd8768a957fd8a82 <br>
  <p>{"city":{"id":4377835,"name":"Bolivar","coord":{"lon":-93.4105,"lat":37.6145},"country":"US","population":0},"cod":"200","message":0.0440695,"cnt":7,"list":[{"dt":1539108000,"temp":{"day":73.94,"min":65.91,"max":75.33,"night":65.91,"eve":73.53,"morn":73.94},"pressure":989.02,"humidity":100,"weather":[{"id":502,"main":"Rain","description":"heavy intensity rain","icon":"10d"}],"speed":16.37,"deg":152,"clouds":88,"rain":17.59},{"dt":1539194400,"temp":{"day":58.87,"min":46.69,"max":61.3,"night":46.69,"eve":55.08,"morn":61.3},"pressure":989.78,"humidity":83,"weather":[{"id":500,"main":"Rain","description":"light rain","icon":"10d"}],"speed":12.55,"deg":282,"clouds":0,"rain":0.65},{"dt":1539277200,"temp":{"day":50.4,"min":38.39,"max":53.56,"night":38.39,"eve":48.27,"morn":39.56},"pressure":1001,"humidity":75,"weather":[{"id":800,"main":"Clear","description":"sky is clear","icon":"01d"}],"speed":9.08,"deg":338,"clouds":0},{"dt":1539363600,"temp":{"day":60.49,"min":47.16,"max":60.49,"night":54.19,"eve":56.64,"morn":47.16},"pressure":991.9,"humidity":0,"weather":[{"id":501,"main":"Rain","description":"moderate rain","icon":"10d"}],"speed":7.36,"deg":203,"clouds":97,"rain":6.53},{"dt":1539450000,"temp":{"day":54.91,"min":52.09,"max":57.33,"night":52.09,"eve":57.33,"morn":53.17},"pressure":981.87,"humidity":0,"weather":[{"id":503,"main":"Rain","description":"very heavy rain","icon":"10d"}],"speed":7.85,"deg":99,"clouds":100,"rain":73.57},{"dt":1539536400,"temp":{"day":51.33,"min":45.93,"max":51.33,"night":45.93,"eve":49.89,"morn":48.81},"pressure":995.22,"humidity":0,"weather":[{"id":502,"main":"Rain","description":"heavy intensity rain","icon":"10d"}],"speed":11.59,"deg":333,"clouds":82,"rain":22.66},{"dt":1539622800,"temp":{"day":54.55,"min":44.46,"max":54.55,"night":44.46,"eve":52.21,"morn":45.07},"pressure":1005.14,"humidity":0,"weather":[{"id":500,"main":"Rain","description":"light rain","icon":"10d"}],"speed":4.09,"deg":61,"clouds":10,"rain":0.32}]}</p>
</blockquote>
<p> and turn it into </p>
    <div class="cal_wrap">
<div class="cal_colwrap cal_dayiconhi" style="background-color:#efefef;">
			<span class="cal_day">Sun</span><br>
			<span class="cal_date">Oct 7</span><br>
			<img alt="Mostly Cloudy with Numerous Storms" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/mcloudyt.png" style="width:55px; height:55px; border:0;" title="Mostly Cloudy with Numerous Storms"><br>
			<span class="cal_hi">78&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#e3e3e3;">
			<span class="cal_day">Mon</span><br>
			<span class="cal_date">Oct 8</span><br>
			<img alt="Partly Cloudy with Isolated Storms" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/pcloudyt.png" style="width:55px; height:55px; border:0;" title="Partly Cloudy with Isolated Storms"><br>
			<span class="cal_hi">83&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#efefef;">
			<span class="cal_day">Tue</span><br>
			<span class="cal_date">Oct 9</span><br>
			<img alt="Mostly Cloudy with Scattered Storms" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/mcloudyt.png" style="width:55px; height:55px; border:0;" title="Mostly Cloudy with Scattered Storms"><br>
			<span class="cal_hi">80&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#e3e3e3;">
			<span class="cal_day">Wed</span><br>
			<span class="cal_date">Oct 10</span><br>
			<img alt="Partly Cloudy with Scattered Storms" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/pcloudyt.png" style="width:55px; height:55px; border:0;" title="Partly Cloudy with Scattered Storms"><br>
			<span class="cal_hi">68&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#efefef;">
			<span class="cal_day">Thu</span><br>
			<span class="cal_date">Oct 11</span><br>
			<img alt="Sunny" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/sunny.png" style="width:55px; height:55px; border:0;" title="Sunny"><br>
			<span class="cal_hi">62&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#e3e3e3;">
			<span class="cal_day">Fri</span><br>
			<span class="cal_date">Oct 12</span><br>
			<img alt="Partly Cloudy with Light Showers Likely" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/showers.png" style="width:55px; height:55px; border:0;" title="Partly Cloudy with Light Showers Likely"><br>
			<span class="cal_hi">60&deg;F</span>
		</div>
		<div class="cal_colwrap cal_dayiconhi" style="background-color:#efefef;">
			<span class="cal_day">Sat</span><br>
			<span class="cal_date">Oct 13</span><br>
			<img alt="Partly Cloudy with Scattered Showers" class="Tips1" src="https://d2hhjsu0v3gh4o.cloudfront.net/reports/images/aeris1410/pcloudyr.png" style="width:55px; height:55px; border:0;" title="Partly Cloudy with Scattered Showers"><br>
			<span class="cal_hi">60&deg;F</span>
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#efefef;">
			68&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#e3e3e3;">
			66&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#efefef;">
			63&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#e3e3e3;">
			43&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#efefef;">
			44&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#e3e3e3;">
			42&deg;F
		</div>
		<div class="cal_colwrap cal_low" style="background-color:#efefef;">
			43&deg;F
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#efefef;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">SE&nbsp;9 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 70%</span></a>
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#e3e3e3;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">SSE&nbsp;14 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 20%</span></a>
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#efefef;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">SSE&nbsp;15 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 50%</span></a>
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#e3e3e3;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">SSW&nbsp;10 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 50%</span></a>
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#efefef;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">NNW&nbsp;8 MPH</span></a><br>
			&nbsp;
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#e3e3e3;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">ESE&nbsp;8 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 60%</span></a>
		</div>
		<div class="cal_colwrap" style="height: 42px; background-color:#efefef;">
			<a class="Tips3" href="" onclick="return false;" title="Wind speed and direction"><span class="cal_wind">NNE&nbsp;7 MPH</span></a><br>
			<a class="Tips3" href="" onclick="return false;" title="Probability of Precipitation"><span class="cal_pop">Precip 40%</span></a>
		</div>
	</div>
	
	
	
</body>
</html>