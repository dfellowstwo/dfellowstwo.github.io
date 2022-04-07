<!DOCTYPE HTML><html lang=en><head><meta http-equiv=Content-Type content="text/html; charset=UTF-8"><meta name=viewport content="width=device-width, initial-scale=1.0"><title>FIX</title><head>
    <script type="text/javascript">

        // Wait for the page to load first
        window.onload = function() {

          //Get a reference to the link on the page
          // with an id of "mylink"
          var a = document.getElementById("link1");

          //Set code to run when the link is clicked
          // by assigning a function to "onclick"
          a.onclick = function() {
	      var img = new Image(); 
		var div = document.getElementById('link1'); 
		img.onload = function() { 
		div.innerHTML += '<br /><img src="'+img.src+'" />';  
		}; 
		img.src = 'http://dfellows.rf.gd/site_html/weather-seven-day-forecast-graphic.jpg'; 
		}; 
		//window.location.reload(1);
           //return false;
          }

    </script>
</head><body>

<span id="zdate"></span><br />
<a id="link1" href=#>Click/Tap for the seven-day-forecast-graphic.</a><br>

<span id="zdate2"></span><br>
SUN: &nbsp; &nbsp; &nbsp; <span id="zSunrise"></span> &nbsp; &nbsp; <span id="zSunset"></span><br>
MOON: &nbsp; <span id="zMoonrise"></span> &nbsp; &nbsp; <span id="zMoonset"></span><br>

<script src=jquery-1.5.1.js></script>
<script src=moment.min.js></script>

<script>
var date = new Date();
document.getElementById("zdate").innerHTML = date.toLocaleString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' , hour12: true });
</script>

<script>
// var date2 = moment(date).format("MM/DD/YYYY");
// https://aa.usno.navy.mil/api/rstt/oneday?date=4/5/2022&coords=37.653690,-93.399376&tz=-5&dst=false


function readTextFile(file, callback) {
    var rawFile = new XMLHttpRequest();
    rawFile.overrideMimeType("application/json");
    rawFile.open("GET", file, true);
    rawFile.onreadystatechange = function() {
        if (rawFile.readyState === 4 && rawFile.status == "200") {
            callback(rawFile.responseText);
        }
    }
    rawFile.send(null);
}

readTextFile("usno.json", function(text){
var data2 = JSON.parse(text); // formatted. WORKING!

document.getElementById("zSunrise").innerHTML= (moment(data2.properties.data.sundata[1].time,"h:mma")).format("h:mma");
document.getElementById("zSunset").innerHTML= (moment(data2.properties.data.sundata[3].time,"h:mma")).format("h:mma");


}); // END readTextFile




</script>