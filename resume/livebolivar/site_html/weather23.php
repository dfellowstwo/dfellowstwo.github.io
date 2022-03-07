<!DOCTYPE HTML>
<html lang=en>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>WEATHER23.PHP</title>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
 <!-- url:"http://api.wunderground.com/api/c3d8a6a640832fd0/conditions/forecast/alert/q/37.614,-93.41.json" -->
<!-- https://stackoverflow.com/questions/44964205/using-a-json-script-in-html-and-show-data?rq=1 -->
<!-- $('#forecast').text('Forecast : ' + data["current_observation"]["forecast"]["txt_forecast"]["date"]["forecastday"]);-->
<script>



    window.onload=function(){ 
      
$.ajax({
  dataType: "json",
  url:"https://api.wunderground.com/api/c3d8a6a640832fd0/conditions/forecast/alert/q/37.614,-93.41.json",
  success: function(data){
		$('#city').text("City : " + data["current_observation"]["display_location"]["full"]);
		$('#high').html("High: " + data["forecast"]["simpleforecast"]["forecastday"][0]["high"]["fahrenheit"]);
		$('#low').html("Low: " + data["forecast"]["simpleforecast"]["forecastday"][0]["low"]["fahrenheit"]);
		$('#temp').text("Temp: " + data["current_observation"]["temp_f"]);
		$('#conditions').text("Conditions : " + data["current_observation"]["icon"]);
		$('<img />').attr('src',data["current_observation"]["icon_url"]).appendTo($("#iconorimage"));
		$('#wind').text('Wind : ' + data["current_observation"]["wind_string"]);
		$('#time').text('Time : ' + data["current_observation"]["observation_time"]);
		// $('#time').text('Time : ' + data["forecast"]["simpleforecast"]["forecastday"]["pretty"]);
		// $('#forecast').text('Forecast : ' + data["forecast"]);
	}
});
}

</script>
</head>

<body>
<div class="wrapper">
<span id="city"></span><br/>
<span id="time"></span><br/>
<span id="conditions"></span><br/>
<span id="temp"></span><br/>
<span id="high"></span><br/>
<span id="low"></span><br/>
<span id="wind"></span><br/>
<span id="iconorimage"></span><br/>
<span id="forecast"></span><br/>
</div>
</body>
</html>

