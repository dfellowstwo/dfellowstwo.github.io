<?php
    /* https://www.phptoys.com/tutorial/get-actual-weather.html */
       
function getWeatherRSS($weatherLink){
   
   if ($fp = fopen($weatherLink, 'r')) {
      $content = '';
        
      while ($line = fread($fp, 1024)) {
         $content .= $line;
      }
   }

   return $content;  
}

function processWeather($wurl){
    
    $wrss = getWeatherRSS($wurl);
    $temp  = '-';
    $tempu = '';
    if (strlen($wrss)>100){
        // Get temperature unit C or F
        $spos = strpos($wrss,'yweather:units temperature="')+strlen('yweather:units temperature="');
        $epos = strpos($wrss,'"',$spos);
        if ($epos>$spos){
            $tempu = substr($wrss,$spos,$epos-$spos);
        } 

        $spos = strpos($wrss,'yweather:wind chill="')+strlen('yweather:wind chill="');
        $epos = strpos($wrss,'"',$spos);
        if ($epos>$spos){
            $temp += substr($wrss,$spos,$epos-$spos);
        } else {
            $temp = '-';
        }
        
    
    }
    
    return $temp.' &deg;'.$tempu;
        
}

function sunrise($wurl){
    
    $wrss = getWeatherRSS($wurl);
    $sunrise  = '';

        
        // Get sunrise
        $spos = strpos($wrss,'yweather:astronomy sunrise="')+strlen('yweather:astronomy sunrise="');
        $epos = strpos($wrss,'"',$spos);
        if ($epos>$spos){
            $sunrise = substr($wrss,$spos,$epos-$spos);
        } 

    return $sunrise;
    
}

function sunset($wurl){
    
    $wrss = getWeatherRSS($wurl);
    $sunset  = '';

        
        // Get sunset
        $spos = strpos($wrss,'yweather:astronomy sunrise="')+strlen('yweather:astronomy sunrise="');
        $epos = strpos($wrss,'"',$spos);
        if ($epos>$spos){
            $sunset = substr($wrss,$spos,$epos-$spos);
        } 

    return $sunset;
        
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
   <title>Micro Weather</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="main">
      <div id="caption">CURRENT WEATHER</div>
      <div id="icon2">&nbsp;</div>
      <div id="result">TEMP:&nbsp;<?php echo processWeather('https://xml.weather.yahoo.com/forecastrss?p=65613&u=f'); ?>
      <br>
      Sunrise:&nbsp;<?php echo sunrise('https://xml.weather.yahoo.com/forecastrss?p=65613&u=f'); ?>
      <br>
      Sunset:&nbsp;<?php echo sunset('https://xml.weather.yahoo.com/forecastrss?p=65613&u=f'); ?>
      
      </div>
      <div id="source">Micro Weather 1.0</div>
    </div>
</body>