<!DOCTYPE html>
<html>
<head>
<title>Weather</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
    function gettingJSON(){
        
        $.getJSON("http://api.openweathermap.org/data/2.5/forecast/daily?id=4377835&mode=JSON&units=imperial&cnt=7&APPID=889cbe42f435f153dd8768a957fd8a82",function(json){
            document.write(JSON.stringify(json));
        });
    }
</script>
  
    <style>

    </style>
</head>
<body>
<button id = "getIt" onclick = "gettingJSON()">Get Raw Data</button>

<div class="container-fluid all">

  <div class="row">
    <div class="col-md-12 well" id="city">
      <div class="row">
        <div class="col-md-4" id="weather">
          <img src="" id="weatherImg" class="center-block">
	    <br>
          <span class="text-center data-item" id="weather-text"></span>
        </div>
        <div class="col-md-4" id="temp">
          <span>Temp: <span id="temp-text"></span> F&deg;</span>
         </div>
        <div class="col-md-4" id="wind">
          Wind: <span id="wind-text"></span>
        </div>
      </div>
    </div>
  </div>

</div>


<script>

//necessary global variables for selector caching.
var $tempMode = $("#tempMode");
var $tempText = $("#temp-text");
var $windText = $("#wind-text");
//$(document).ready(function() {
  // this function takes the temperature from the dataHandler and displays the temperature according to the correct temperature unit, and colors the temperature hot or cold.

//function for instruction dialog
  $(function() {
    $( "#dialog" ).dialog();
  });

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
    var apiURI = "http://api.openweathermap.org/data/2.5/weather?lat=" + lat + "&lon=" + lon + "&APPID=889cbe42f435f153dd8768a957fd8a82";

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