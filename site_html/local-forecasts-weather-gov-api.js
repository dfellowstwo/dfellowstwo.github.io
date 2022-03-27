       
	 // GITHUB DOES NOT LIKE UNDERSCORE _
	  
	  var a = new Date();
        var days = new Array(7);
        days[0] = "Sunday";
        days[1] = "Monday";
        days[2] = "Tuesday";
        days[3] = "Wednesday";
        days[4] = "Thursday";
        days[5] = "Friday";
        days[6] = "Saturday";
        let dayName = days[a.getDay()];

class ForecastFetcher {
  defaults = {
    timeout: 3000,
    maxDays: 3,
    host: "https://api.weather.gov/",
  };

  dayRenderer = (forecastDay) => {
    const { day, night } = forecastDay;

    if (night && !day) {
      return `

          `;
    }

    return `
<div class="forecast-day">

  <div class="forecast-section forecast-section--day">
    <strong class="forecast-section__name">${day.name.substring(0,3)}</strong>
    <img class="forecast-section__icon" src="${day.icon}" alt="${day.shortForecast}">
    <div class="forecast-section__temp">${day.temperature}&deg; ${day.temperatureUnit}</div>
    <div class="forecast-section__temp">${night.temperature}&deg; ${night.temperatureUnit}</div>
    <div class="forecast-section__short">${day.shortForecast}</div>
    <div class="forecast-section__wind">${day.windSpeed} from the ${day.windDirection}</div>
  </div> 
</div>



    `;
  };

  wrapRenderer = (forecast, forecastMarkup) => {
    return ` 
<div class="forecast-wrapper">
  ${forecastMarkup}
</div>
<p class="forecast-credits"><a href="https://www.weather.gov/documentation/services-web-api">Data provided by the National Weather Service API</a></p>
<p class="forecast-credits"><a href='https://daltonrowe.com/local-forecasts-weather-gov-api.html#'>How to by Dalton Rowe</a></p>
    `;
  };

  constructor(config = {}) {
    // merge our defaults with user config
    this.config = { ...this.defaults, ...config };
  }

  // fetch data via the nws api
  useNWS = async (route) => {
    const response = await fetch(`${this.config.host}${route}`, {
      method: "GET",
    });

    return response.json();
  };

  // lookup and provide point information
  lookupPoint = async (lat, lng) => {
    return this.useNWS(`points/${lat},${lng}`);
  };

  // lookup and provide forecast info
  lookupForecast = async (office, gridX, gridY) => {
    return this.useNWS(`gridpoints/${office}/${gridX},${gridY}/forecast`);
  };

  // combine point and forecast lookups
  lookupForecastForLatLng = async (lat, lng) => {
    const point = await this.lookupPoint(lat, lng);
    const { cwa, gridX, gridY } = point.properties;

    return await this.lookupForecast(cwa, gridX, gridY);

  };

  // manipulate html strings, or let user do it
  markupForecast = (forecast) => {
    let forecastMarkup = "";
    const { periods } = forecast.properties;
    periods[0].name=dayName;
    let offset = 0;
    let maxDays = this.config.maxDays;
    //periods[0].isDaytime='true';
if (!periods[0].isDaytime) { 
     maxDays = 8;
     offset = 1;
     maxDays -= 1;

     forecastMarkup += this.dayRenderer({ night: periods[0] });
    }
if (periods[0].isDaytime) { 
    for (let i = offset; i < maxDays * 2; i += 2) {
      const forecastDay = {
        day: periods[i],
        night: periods[i + 1],
      };

      forecastMarkup += this.dayRenderer(forecastDay);
    }
    } else {
    for (let i = offset; i < maxDays * 2; i += 2) {
      const forecastDay = {
        night: periods[i + 1],
        day: periods[i],
      };

      forecastMarkup += this.dayRenderer(forecastDay);
    }
    }


    const forecastWrapper = document.createElement("DIV");
    forecastWrapper.innerHTML = this.wrapRenderer(forecast, forecastMarkup);
    return forecastWrapper;
  };
} // END class ForecastFetcher

(async () => {


  async function runDemo() {


    const fetcher = new ForecastFetcher({
      maxDays: 7,
    });
    const forecast = await fetcher.lookupForecastForLatLng(37.6537,-93.3994);
    const forecastNode = fetcher.markupForecast(forecast);
    
	let z = forecast ;
	// let z=z.replace('/This Afternoon/g', 'Wednesday');
	//console.log(z);
    // TypeError: z.replace is not a function
    
    demo.appendChild(forecastNode);
  }

  window.onload=function(){runDemo();};
})();
