
<?php

	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/forecasts/:auto?&format=json&filter=day&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}
	
	
echo "<br><br><br><br><br><br>";

	// fetch Aeris API output as a string and decode into an object
	$response = file_get_contents("https://api.aerisapi.com/forecasts/:auto?&format=json&filter=mdnt2mdnt&limit=7&fields=periods.dateTimeISO,loc,periods.maxTempF,periods.minTempF,periods.pop,periods.windSpeedMaxMPH,periods.windSpeedMinMPH,periods.windDirMin,periods.windDirMax,periods.weather&client_id=IqSvnd3dtK8uE2s2J19Dp&client_secret=BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA");
	$json = json_decode($response);
	if ($json->success == true) {
		// create reference to our returned observation object
		print_r($json);
	}
	else {
		echo sprintf("An error occurred: %s", $json->error->description);
	}

echo "<br><br><br><br><br><br>";	


?>

<!-- https://idratherbewriting.com/learnapidoc/docapis_aerisweather_example.html#4-pull-out-the-values-from-the-response -->
<html>
<TITLE>WEATHER22.PHP</TITLE>
   <body>
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
      <script>

         jQuery.ajax({
             url: "http://api.aerisapi.com/observations/BOLIVAR,MO",
             type: "GET",
             data: {
                 "client_id": "IqSvnd3dtK8uE2s2J19Dp",
                 "client_secret": "BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA",
             },
         })
         .done(function(data, textStatus, jqXHR) {
             console.log("HTTP Request Succeeded: " + jqXHR.status);
             console.log(data);
		 
             if (data.response.ob.windSpeedMPH > 1) {
         	var windAnswer = "Yes, it's too windy.";
		
         }
         	else  {
			
         	 var windAnswer = "No, it's not that windy.";
         	}
             $("#windAnswer").append(windAnswer);
         })
         .fail(function(jqXHR, textStatus, errorThrown) {
             console.log("HTTP Request Failed");
         })
         .always(function() {
             /* ... */
         });


      </script>
      <p>Is it too windy to go on a bike ride?</p>
      <div id="windAnswer" style="font-size:76px"></div>

	
	
	
<!--https://www.aerisweather.com/wizard/wxblox/output?view=forecast&options.loc.value=BOLIVAR%2CMO&options.request=&options.type=detailed&type=view -->
 <link rel="stylesheet" href="https://cdn.aerisapi.com/wxblox/aeris-wxblox.css">
<script src="https://cdn.aerisapi.com/wxblox/aeris-wxblox.min.js"></script>

<!--// target DOM element where WeatherBlox will be rendered //-->
<div id="wxblox" class="aeris-wrapper"></div>

<script> 

// set Aeris account access keys
Aeris.wxblox.setAccess('IqSvnd3dtK8uE2s2J19Dp', 'BEZBKOzxH058R4sd5XAfcpiMCCU31xtTGDQ9DkqA');

// create desired WeatherBlox instance
var view = new Aeris.wxblox.views.Forecast('#wxblox', {
    type: "detailed"
});

// load data and render the view for a specific location
view.load({
    p: "BOLIVAR,MO"
});



</script>
   </body>
</html>