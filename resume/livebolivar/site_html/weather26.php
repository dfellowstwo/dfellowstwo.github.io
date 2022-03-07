<!DOCTYPE html>
<html>
<!--https://jsfiddle.net/sceendy/nea4z7ff/?utm_source=website&utm_medium=embed&utm_campaign=nea4z7ff -->
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>WEATHER26.PHP</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">


  <script type="text/javascript" src="../scripts/jquery-1.12.0.min.js"> </script>

    <link rel="stylesheet" type="text/css" href="/css/result-light.css">

      <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/1.3.2/css/weather-icons.min.css">
  <style type="text/css">
    /* Styles */

body {
  background: #dce2e5;
  font-family: "Roboto", sans-serif;
  font-weight: 100;
  font-size: 11px;
}

.component__weather-box {
  margin: 30px auto;
  width: 420px;
  border: 1px solid #de6042;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 2px 5px 20px 1px #444;
}

.component__weather-content {
  position: relative;
  overflow: hidden;
  color: #fff;
  background: #E06B4F;
  height: 120px;
}

.component__weather-content:before {
  content: "";
  display: block;
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  opacity: 0.3;
  background-image: url("https://nunnz.files.wordpress.com/2013/11/4.jpg");
  background-repeat: no-repeat;
  background-position: 0 63%;
  background-size: cover;
}

.weather-content__overview {
  width: 50%;
  text-align: center;
  display: inline-block;
  float: left;
  z-index: 2;
  position: relative;
}

h1 {
  font-weight: 400;
  font-size: 40px;
  line-height: 40px;
  padding-bottom: 0;
  margin-bottom: 0;
  margin-top: 0.75em;
}

.weather-content__temp {
  width: 50%;
  z-index: 2;
  text-align: center;
  float: left;
  font-size: 50px;
  text-align: center;
  margin-top: 0.5em;
  position: relative;
  vertical-align: middle;
}

.weather-content__temp .degrees {
  line-height: 40px;
}

.weather-content__temp .wi-degrees {
  margin-left: -10px;
  vertical-align: top !important;
}

.currentTemp .wi {
  margin-right: 20px;
  font-size: 40px;
  vertical-align: baseline;
}

.component__forecast-box {
  display: flex;
  clear: both;
}

.forecast__item {
  flex: 1;
  text-align: center;
}

.forecast-item__heading {
  background: #e68872;
  border: 1px solid #d64826;
  border-left: none;
  text-transform: uppercase;
  color: #fff;
  font-weight: 800;
  padding: 10px;
}

.forecast-item__info {
  background: #fff;
  color: #E06B4F;
  padding-bottom: 10px;
  border-right: 1px solid #d64826;
}

.forecast-item__info .wi {
  display: block;
  margin: 0 auto;
  font-size: 24px;
  padding: 15px 0;
}

.forecast-item__info .degrees {
  font-size: 20px;
  line-height: 20px;
}

.forecast-item__info .degrees .wi-degrees {
  display: inline;
}

small {
  font-size: 12px;
}

  </style>
  <!-- TODO: Missing CoffeeScript 2 -->

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

  return fetch(URL, {
    method: 'GET',
    headers: headers
  }).then(data => data.json());
}


 // fetch("http://api.openweathermap.org/data/2.5/weather?id=4377835&cnt=7&units=imperial&APPID=889cbe42f435f153dd8768a957fd8a82").then(data => data.json());

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
  const currentWeather = forecast[0].weather[0];
  const widgetHeader = `<h1>${location.name}</h1><small>${currentWeather.description}</small>`;
  CURRENT_TEMP.innerHTML = `<i class="wi ${applyIcon(currentWeather.icon)}"></i> ${Math.round(forecast[0].temp.day)} <i class="wi wi-degrees"></i>`;
  CURRENT_LOCATION.innerHTML = widgetHeader;

  // render each daily forecast
  forecast.forEach(day => {
    let date = new Date(day.dt * 1000);
    let days = ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'];
    let name = days[date.getDay()];
    let dayBlock = document.createElement("div");
    dayBlock.className = 'forecast__item';
    dayBlock.innerHTML = `<div class="forecast-item__heading">${name}</div>
      <div class="forecast-item__info"><i class="wi ${applyIcon(day.weather[0].icon)}"></i> <span class="degrees">${Math.round(day.temp.day)}<i class="wi wi-degrees"></i></span></div>`;
    FORECAST.appendChild(dayBlock);
  });
}

// TUTORIAL reader: I moved the calling of the weather API url
// to be able to get the current browser location
if ("geolocation" in navigator) {
  navigator.geolocation.getCurrentPosition((position) => {
  	// coordinates = `lat={37.614479}&lon={-93.410469}`;
    const coordinates = `lat=${position.coords.latitude}&lon=${position.coords.longitude}`;
    // run/render the widget data
    getWeatherData(coordinates).then(weatherData => {
      let city = weatherData.city;
      let dailyForecast = weatherData.list;

      renderData(city, dailyForecast);
    });
  });
} else {

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

  
  <script>
    // tell the embed parent frame the height of the content
    if (window.parent && window.parent.parent){
      window.parent.parent.postMessage(["resultsFrame", {
        height: document.body.getBoundingClientRect().height,
        slug: "nea4z7ff"
      }], "*")
    }
  </script>
</body>
</html>
