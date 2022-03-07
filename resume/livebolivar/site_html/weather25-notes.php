

// TUTORIAL reader: I moved the calling of the weather API url
// to be able to get the current browser location
if ("geolocation" in navigator) {
  navigator.geolocation.getCurrentPosition((position) => {
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