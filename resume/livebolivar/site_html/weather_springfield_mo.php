<?php
// Require the weather function
require_once( 'weather-Current-NOAA-Weather-Grabber.php' );
// Standard display function
function noaa_weather_grabber_test( $weather ) {
    // Display each item
	if ((isset ( $weather->okay )) && ( $weather->okay == "yes" )) {
        echo "Weather Springfield, MO" . "\n";
        echo date("D, M j, Y ") . "\n";
		echo "Conditions: " . "\n";
        echo $weather->condition . "\n";
		echo "TEMP: " . $weather->temp . "&deg;F" . "\n";
        include 'sunrise-sunset.php';
		echo "\n";
	}
	
	// Dump the entire weather variable
	// This is just for testing -- do not include on your website.
    //	var_dump( $weather );
}

?>

<pre>
<?php
// Get the weather
$weather = noaa_weather_grabber( 'KSGF', 'yes', 'https://forecast.weather.gov/MapClick.php?lat=37.6268&lon=-93.4313&unit=0&lg=english&FcstType=dwml');
// Run the weather test function
noaa_weather_grabber_test( $weather );

?>
</pre>
</body>
</html>