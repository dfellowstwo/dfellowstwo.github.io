<?php
$feed = file_get_contents("https://xml.weather.yahoo.com/forecastrss?p=65613&u=f");
$xml = simplexml_load_string($feed);
 
// Display the city name and time
echo $xml->channel->item->title;
?>
<br>
<?php
// Display the conditions
echo $xml->channel->item->description;

    /* This is a multi line comment
       yet another line of comment */
?>