<!DOCTYPE html>
<!-- render openweather json with dom -->
<!--https://jsfiddle.net/sceendy/nea4z7ff/?utm_source=website&utm_medium=embed&utm_campaign=nea4z7ff -->
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>WEATHER27.PHP</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">



<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/1.3.2/css/weather-icons.min.css">
  <style>
body{// background:#dce2e5;font-family:"Verdana",sans-serif;font-weight:100;// font-size:11px}.component__weather-box{margin:0 auto;// width:473px;// border:.5px solid #de6042;// border-radius:10px;overflow:hidden;// box-shadow:2px 5px 20px 1px #444}.component__weather-content{position:relative;// overflow:hidden;color:#fff;background:#e06b4f;// height:120px}.component__weather-content:before{content:"";display:block;position:absolute;left:0;top:0;width:100%;height:100%;z-index:1;opacity:.3;background-image:url("https://nunnz.files.wordpress.com/2013/11/4.jpg");background-repeat:no-repeat;background-position:0 63%;background-size:cover}.weather-content__overview{width:50%;text-align:center;display:inline-block;float:left;z-index:2;position:relative}h1{font-weight:400;font-size:40px;line-height:40px;padding-bottom:0;margin-bottom:0;margin-top:.75em}.weather-content__temp{width:50%;z-index:2;text-align:center;float:left;font-size:50px;text-align:center;margin-top:.5em;position:relative;vertical-align:middle}.weather-content__temp .degrees{line-height:40px}.weather-content__temp .wi-degrees{margin-left:-10px;vertical-align:top!important}.currentTemp .wi{margin-right:20px;font-size:40px;vertical-align:baseline}.component__forecast-box{display:flex;clear:both;border:.5px solid #4249de99}.forecast__item{flex:1;text-align:center;border:.5px solid #425fde80}.forecast-item__heading{background:#403da7;border:0 0 1px 1px solid #4637ad4d;border-left:none;text-transform:uppercase;color:#fff;font-weight:800;// padding:10px}.forecast-item__info{background:#fff;color:#060606;padding-bottom:10px}.forecast-item__info .wi{display:block;margin:0 auto;font-size:24px;padding:15px 0}.forecast-item__info .degrees{font-size:20px;line-height:20px}.forecast-item__info .degrees .wi-degrees{display:inline}small{font-size:14px}
  </style>


  <script type="text/javascript">


    window.onload=function(){
      
const CURRENT_LOCATION = document.getElementsByClassName('weather-content__overview')[0];
const CURRENT_TEMP = document.getElementsByClassName('weather-content__temp')[0];
const FORECAST = document.getElementsByClassName('component__forecast-box')[0];

// Use Fetch API to GET data from OpenWeather API
// return json
function getWeatherData(position) {
  let headers = new Headers();
  const URL = `https://api.openweathermap.org/data/2.5/forecast/daily?${position}&cnt=7&units=imperial&APPID=889cbe42f435f153dd8768a957fd8a82`;
// https://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&cnt=7&units=imperial&APPID=889cbe42f435f153dd8768a957fd8a82
  return fetch(URL, {
    method: 'GET',
    headers: headers
  }).then(data => data.json());
}

// TUTORIAL READERS:
// yeah, using an external resource for the icons and applying them here using a switch block
function applyIcon(icon) {
  let selectedIcon;
  switch (icon) {
    case '01d':
      selectedIcon = "wi-day-sunny"
      break;
    case '01n':
      selectedIcon = "wi-night-clear"
      break;
    case '02d':
    case '02n':
      selectedIcon = "wi-cloudy"
      break;
    case '03d':
    case '03n':
    case '04d':
    case '04n':
      selectedIcon = "wi-night-cloudy"
      break;
    case '09d':
    case '09n':
      selectedIcon = "wi-showers"
      break;
    case '10d':
    case '10n':
      selectedIcon = "wi-rain"
      break;
    case '11d':
    case '11n':
      selectedIcon = "wi-thunderstorm"
      break;
    case '13d':
    case '13n':
      selectedIcon = "wi-snow"
      break;
    case '50d':
    case '50n':
      selectedIcon = "wi-fog"
      break;
    default:
      selectedIcon = "wi-meteor"
  }
  return selectedIcon;
}

// Use returned json from promise to render daily forecast
renderData = (location, forecast) => {
  // render city, current weather description and temp
  // const currentWeather = forecast[0].weather[0];
  // const widgetHeader = `<h1>${location.name}</h1><small>${currentWeather.description}</small>`;
  // CURRENT_TEMP.innerHTML = `<i class="wi ${applyIcon(currentWeather.icon)}"></i> ${Math.round(forecast[0].temp.day)} <i class="wi wi-degrees"></i>`;
  // CURRENT_LOCATION.innerHTML = widgetHeader;

  // render each daily forecast
  forecast.forEach(day => {
    let date = new Date(day.dt * 1000);
    let days = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];
    let name = days[date.getDay()];
    let dayBlock = document.createElement("div");
    dayBlock.className = 'forecast__item';
    dayBlock.innerHTML = `<div class="forecast-item__heading">${name}</div>
      <div class="forecast-item__info"><i class="wi ${applyIcon(day.weather[0].icon)}"></i> <span>${(day.weather[0].main)}</span><br><span>${Math.round(day.rain/25.4)}in</span><br><span>${Math.round(day.speed)}mph</span><br><span class="degrees">${Math.round(day.temp.max)}<i class="wi wi-degrees"></i></span><br> <span class="degrees">${Math.round(day.temp.min)}<i class="wi wi-degrees"></i></span></div>`;
    FORECAST.appendChild(dayBlock);

  });
}


// TUTORIAL reader: I moved the calling of the weather API url
// to be able to get the current browser location
if ("geolocation" in navigator) { var coordinates = `lat=${37.614479}&lon=${-93.410469}`;
	// run/render the widget data
    getWeatherData(coordinates).then(weatherData => {
      let city = weatherData.city;
      let dailyForecast = weatherData.list;

      renderData(city, dailyForecast);
    });
    
} else { 

 coordinates = `lat=${37.614479}&lon=${-93.410469}`;
	// run/render the widget data
    getWeatherData(coordinates).then(weatherData => {
      let city = weatherData.city;
      let dailyForecast = weatherData.list;
      renderData(city, dailyForecast);
    });

  }


    }
  



</script>

</head>
<body>
  <div class="component__weather-box">
  <div class="component__weather-content">
    <div class="weather-content__overview"></div>
    <div class="weather-content__temp"></div>
  </div>
  <div class="component__forecast-box"></div>
</div>

<a class="weatherwidget-io" href="https://forecast7.com/en/37d65n93d40/65613/?unit=us" data-font="Verdana" data-mode="Forecast" data-theme="original" >Bolivar, MO 65613, USA</a>
<script>
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
</script>

<script>
// https://www.webdeveloper.com/forum/d/302549-weather-api-coding/3

</script>







<script>
    function gettingJSON(){
        
        $.getJSON("http://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&mode=JSON&units=imperial&cnt=7&APPID=889cbe42f435f153dd8768a957fd8a82",function(json){
            document.write(JSON.stringify(json));
        });
    }
</script>
  
    <style>

    </style>

<br>
<button id = "getIt" onclick = "gettingJSON()">Get Raw Data</button>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.js"></script>

<?php

date_default_timezone_set('America/Chicago');
$time_format = 'h:i A T'; // 08:53 PM PDT
$json_string = file_get_contents("http://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&mode=JSON&units=imperial&cnt=7&APPID=889cbe42f435f153dd8768a957fd8a82");
$jsonData = json_decode($json_string, true);
$city = $jsonData['city']['name'];
$country = $jsonData['city']['country'];
$time = round ($jsonData['list'][0]['dt']);
$description = ($jsonData['list'][0]['weather']['0']['description']);
$icon = ($jsonData['list'][0]['weather']['0']['icon']);
$min_1 = round ($jsonData['list'][0]['temp']['min'],0,PHP_ROUND_HALF_DOWN);
$max_1 = round ($jsonData['list'][0]['temp']['max'],0,PHP_ROUND_HALF_UP);
// belows converts meters / second to mph
$wind1 = round ((($jsonData['list'][0]['speed'])*2.23694),0,PHP_ROUND_HALF_UP);
$wind1 = round ($jsonData['list'][0]['speed'],0,PHP_ROUND_HALF_UP);
$wind2 = $jsonData['list'][0]['deg'];
// $wind3= round ($wind1*2.23694,0,PHP_ROUND_HALF_UP);
$day=date("D");
// $rain=($jsonData['list'][0]['rain']);
echo "<p>&nbsp;          </p>";
echo $city.', '.$country.'<br>';
echo date("l, F jS, Y").'<br>';
echo date("h:i:s A").'<br>';
echo date("D").'<br>';
echo date("M j", $time).'<br>';
echo "<img src=http://openweathermap.org/img/w/".$icon.".png><br>";
echo $description.'<br>';
?>  

<?php	   
echo 'HIGH: '. $max_1.'&degF'.'<br>';
echo  ' &nbsp;LOW: '. $min_1.'&degF'.'<br>';
// echo $rain.'/25.4 in';

// echo 'WIND: ' . $wind1. ' mps<br>';
// echo 'FROM: ' . $wind2. ' DEGS<br>';
function windRose($item) {
     $winddir[]="NORTH";
     $winddir[]="NNE";
     $winddir[]="NE";
     $winddir[]="ENE";
     $winddir[]="EAST";
     $winddir[]="ESE";
     $winddir[]="SE";
     $winddir[]="SSE";
     $winddir[]="SOUTH";
     $winddir[]="SSW";
     $winddir[]="SW";
     $winddir[]="WSW";
     $winddir[]="WEST";
     $winddir[]="WNW";
     $winddir[]="NW";
     $winddir[]="NNW";
     $winddir[]="NORTH";
     return $winddir[round($item*16/360)];
}

// echo windRose($wind2); // returns SW

echo 'WIND FROM THE ' . windRose($wind2) .' AT ', $wind1.' MPH<br>';
// https://icons8.com/icon/set/east-north-east/all
?> 	   

<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAF+SURBVGhD7dk9L0RBGMXxiaDQeElEJEoaFY0ofAGdlpYS9UaHoBcKFEKn9ClE+AYaoqMUCSHhf/bOTTab2djJzr17J+Ykv+zcJ9mZOd2+mJSUlK5mAkeW1tFFlz7GB34srTWLopCrQLNKF2qnQLNKFcoLfMJ12XbovV0r1I99dFKgmfbaQx9KywVclwnhHKVkBq4LhKQzCs8mXIeHtI7CU4Pr8JAOUHhSEQ+piE9SEQ+piE/+TZFFDGMED3Ymz9Bctu2slUoUWUCeDeTzJw1s/tqjckUG8ArNoy6i7ELzaIv0ogfj0HeNaIsMYilbmjNEXeQmW5ppPGbLeqIrouf5+pMxp/ZVibLIVf3JmCn7qkRZ5BuTGjQkyiJyqEFDoi3yhiHkibaIbCFPFEWucQL9PtU4f4HmcmtnrVSiSAipiE9SEQ+piE9SEQ87KDxrcB0e0ioKzxje4bpACPo4M4pSsoIvuC7SCe25jFIzh0vc4b5D2kN/580iJSUleIz5BfQsXDOwuZMZAAAAAElFTkSuQmCC">
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAHtSURBVGhD7dixS1ZRHMbx11QabMhJhwiKCNqadOhfcAuXGoIsCKxFghqCQBT/AUMcCsHW/oKgKWopHGoIBDcXwUGiUCrt+9T9weHy096r995+ynngA3K4597z4D2+77GTk5PTWEbxEh9qsowRtJqb+Im9mv3ADbSSYXyDt5A66N5DaDx34C2gThNoPI/hPbxO02g8bRSZQ+PJRSrIRaokF6kgF6mSKkW28A5vsVGMdSNMkR1M4jQsvbiN7/DmpMIUeQTLKaSF7sObkwpRZBtnoFyBXil9EbyqATKAr/DmmhBF1mG5BxtfxMXCSjG2nzCv1lkog9ChybvmIGGKPIVFm3wcH+Fd6wlTRCe9h+iDpQd3oT3kzUmFKWK+4Bb6YZmCd20qXBGzigtQ9FvahHedCVFkAdrk8roYkyewvEc6pyxEkTewXMcuNK4Nb/mE8rxUiCJa+DVYLuH83x//5DJ+wZtrwuwRfZqPQX+p0uiT/jO8Oalwm30Nr/AC+mC01+xfwhU5rFykSnKRCnKRKjkxRR7Ae3iddBxuPDqyeg+vkx2LG88SvAXU4Tlai84Xs9C/fbzFHIbuNYP07NJazuEZjlJIc3UP3eu/xwp1c4w1ujZMgXK6KRS6QDleoWNVoBwter5wLAvk5JyMdDq/ARiEcLxeK3skAAAAAElFTkSuQmCC">
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAPqSURBVGhD7dhHqB1VHMfxZw2xxcSGNURs0UUUO4gFBEUlBhUbLsTEhQ2DDXShIFiwoC4UBVEsICi6MAnJQlHBgEYUEVyIDbtYsWLX7zfePxwu/8mcuc+8XMn84LOY887MPffOqW+iT58+ffqsz9kCB2LW6qv/WTbEcXgav+Nv/Dm43gVjH3/1K/A2bHzmU/iGxjKH4EH8jKzxw37ASRiLTMd5WIWssW3+wEVYZ9kTt+FrZA3s6nY4pqYkG+FkrMBfyBo0GU/CN7zWsgOuwYfIGlDrfXw0VDbsJWyP/zRH4DH8iuxDazjdLoOD2q5zIbJ6pXexDyYVF64L8AayD6n1JW7B7ojsDMdVVn+Yb2ak7Iu78R2yB3d1IyJHw/4fi2KNZ1CdTXAGnkP2sFo/4X4sLsq+wKYwD6GsX+MJVOUoOPiyh3TlLGY2QLmanw1zKLz2BzsdrvpRp8l9aM1e8FfMHtDGBewpXI5fBmUO6D1gLkPUfdGCQVx3Igegbfouu2Zj/LbZzTUcR5Fj4TbD8lstIDNRblHmwThjOXM5g/nF4+9N/KFa8yqymzMv4EwsH1x/i80QOQzf4CvEYvYA4v57LSDe471R3sZtT2ueR3Zz+B7+8vshYt+Ovy+0YBBXe3/1z3GuBeQgRF3f2AyYO2GZ65Lr0wI0zWT+rTVXIbtZd2BLRByozjoxHuQbjVwMZz/HQDnTvIyof4kFZG+4Q3CnEPFsEvVKR6I126JsWOlRRJYiqyO/oDkH1rNb7YY4Afp2ou6bcEaL+MVPw7NoGvSua1V5BNkDfO3bwVyPKPcXfr249i0Z1wpXcafWrSwYxC/muIn6LoqeCn2mh6oob7IjqnI4sgfoaphd4cA9ePXVvzvfqOPM5Js1N8GyV7CNBYM4k0X9T+DUHddr4riZhuq8huxBH6A8G9gtjoHrR1nPhc3MRkypdqOdsDXca5X1a3XeZ52P7EGaD+NGz8Zldd5BfOFy0H6GH4vrLhwzJ6JT3Ok2ze3ltuMtRLndoxwrx8Nu4AkvykblafMsjJS7kD203HZcCteJG2A3KgeyeysH+/D9XfjGncZjvRkpc9E0BXp2MDY8Bp9d6QR4+MnuqfUbHodjr5yaJxXn8+zDfNWx7XCGciF9D1ndWh/jWlRPr11yKrIPlVPrw2haQGv4xj0onYKNsdbiw53ns0ZMhhOJ+yu3JlOW65A1ZhSuT4uwOaY89tkuZ+phdj27oNv6dR53r1kj18TBfyViuzIW8RyfNXaYa8wSOA2XW5mxiv+2yRov/zNyM+Zg7OO6cQ/iv4tOnSvhuaPTjnRc4tlif5SnuT59+vTps75lYuIfBJILxYG796AAAAAASUVORK5CYII=">
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAPhSURBVGhD7dlpqFVVGIfxYzRpg83ZRBkNpjSIiWYTFZT4oUApgga0rAgqqMzoSxEFGQ1UNBBFUBQNFoSFSFlBUWQ0SkoYkVEUaGHabOPzhAteNu/Ztwute65w/vBD3e691zrnrLXXsDv99NNPT7IVJuB4nIE5uAo34348hZfwHj7HOxiNYZNRuA+/4u9BegVbo+cZAb/lrJL/1ePwPj3NTGSVG6wb0dO8iqxig/UXzkFPMh5WIKuY3sXXjWNtfsPJGPLcg6xCxRHwSfZZODaQ73E4hizbw0KzyugNmCkox34Kf2/zJfbEkOQSZJUozoZ5FOXYMTgJP4dj3byNbVA9HyKrgL6BY8NuKJW2v5gTUc5bCwfPheFYdB2q5lhkBRc3wWyLC+AI7p/maZTzbvUAmYd4ffERquYJZAUXt2AnxGyB2K/+xFh4/NNNx5pWo1r2gI/JrODoRzyEiYjZFf4C9/77r05nOrLrtRjVYrvNCm3zFs5D1nkXIbtG56JKbAZfICtUNpHzsSwci9ZgAWxSZn/YxLJzfRBUm0yejqzQ4mqUTMLDyB61Vv4FPBeONfmBq2UJskILn0iTEbMzrsQqZNdk/KAHoEoOQrdm0OTj1kXVSJQ4TT8Vz+MPZNcVL6JabkNWaJvvcDv8EmL2Q9tkcgaqxG/2W2SF6n2cAvuFDsaBcL5k04q/jDkS2X3kBNOHSpXMRlZo4f/HWHkfDGfCNcbFuBTXbvI6svtoPqrFNp8VWlwP51UxTsUfwA/Irsn8guZ9/rccjazQJjcenOk6bY/ZEZdhBbLrosdQLY8gK7SNfWYu3F2JcfXnt55do6mokl3QtnZ4GWfhtXAsWoe7cCiMa5HsPPnhq8WROiu0cBOuxPW7e1vr0TzPdf1SOOdq/l9xEarEAazb9LpwQXQCYpyq+4RajuyajL/cdqiS05AVmvkYVn4HxLj6exIDTfttftXiVCIrtM0G2LwcuWP2Qbe+ZrMbhyqxIm3zIT+kfcLRe184ADZ/jZi23Uj7TrUMtENyHGLGwM7qCH4NHL3dgXcq7t7XJ8juo1moFiuTFSpnwDdgb8Qcgjthx82uy3yFLVEtTrezgqON8Knl2BB30h0EL4TbP9l1kV9I1Tj77LZczazEFWi+tHEkz87X79gL1WMh3UbsbpwgPoijYOw32Xl6BkMam46F2pSyCnXzJtqWt963J7Fz26btoFnFBsOZcM/fUPmawEem7/7a3o20uRzDKofhbrS9XmiyHzW3U4dNnPA5GH6ArPLRHdgsMg2+pc1eVfu+sbnYGvbZHa5nnoW79+79Vh3F++mnn2Y6nX8Ayjv78I8oQM0AAAAASUVORK5CYII=">
<p>&nbsp;                      </p>
<!-- weather widget start --><a target="_blank" href="https://www.booked.net/weather/bolivar-5347"><img src="https://w.bookcdn.com/weather/picture/3_5347_0_1_137AE9_430_ffffff_333333_08488D_1_ffffff_333333_0_6.png?scode=124&domid=w209&anc_id=8940"  alt="booked.net"/></a><!-- weather widget end -->
<p>&nbsp;                      </p>


<script>

//necessary global variables for selector caching.
var $tempMode = $("#tempMode");
var $tempText = $("#temp-text");
var $windText = $("#wind-text");
//$(document).ready(function() {
  // this function takes the temperature from the dataHandler and displays the temperature according to the correct temperature unit, and colors the temperature hot or cold.

//function for instruction dialog

  function formatTemperature(kelvin) {
    
    
    var clicked = false;
    var fahr = ((kelvin * 9 / 5) - 459.67).toFixed(0);
    var cels = (kelvin - 273.15).toFixed(1);
    //initial temperature display
    $tempText.html(fahr);

    var firstClick = false;
    //click handler to update the temperature unit of measurement.
    $tempMode.off("click").on("click", function() {
      firstClick = true;
      console.log(clicked);
      clicked === false ? clicked = true : clicked = false;
      clicked === true ? $tempMode.html("C&deg") : $tempMode.html("F&deg");
      if (clicked) {
        $tempText.html(cels);
      } else
        $tempText.html(fahr);
    });

    if (cels > 24) {
      $("#temp-text").css("color", "red");
    } else if (cels < 18) {
      $("#temp-text").css("color", "blue");
    }
  }
  //handles response data and formats it accordingly since it is an asynchronous response object all data handling and formatting must be done within this function.
  function dataHandler(data) {

    formatTemperature(data.main.temp);

    if (data.main.temp && data.name && data.sys) {
      // display location name
      $("#city-text").html(data.name + ", " + data.sys.country);
      // display icon
      if (data.weather) {
        var imgURL = "http://openweathermap.org/img/w/" + data.weather[0].icon + ".png";
        $("#weatherImg").attr("src", imgURL);
        $("#weather-text").html(data.weather[0].description);
      }
      // display wind speed
      if (data.wind) {
        var knots = data.wind.speed;
        $windText.html(knots.toFixed(1) + " mph");
      }
    }
  }
  //This calls the api with the correct coordinates provided by the getLocation function
  function getWeather(lat, lon) {
    var apiURI = "http://api.openweathermap.org/data/2.5/weather?lat=" + lat + "&lon=" + lon + "&appid=06170c100199dbae1e223cc3dfad960b";

    return $.ajax({
      url: apiURI,
      dataType: "json",
      type: "GET",
      async: "true",
    });
  }

  //Passes the browser's geolocation into the getWeather function once its done the response from the getWeather call will be passed to the data handler for formatting.
  var counter = 0;

  function getLocation() {
    console.log("Update# " + counter++);
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(function(position) {
        getWeather(position.coords.latitude, position.coords.longitude).done(dataHandler);
      })
    } else {
      alert("geolocation not available");
    }
  }
  var updateInterval = setInterval(getLocation(), 300000);
//});

</script>





















  

</body>
</html>
