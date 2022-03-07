
$(document).ready(function() {
	var urlIconWeatherRunLocked = 'http://www.weatherunlocked.com/Images/icons/2/';

	$(document).on('change', '[data-toggle^="buttons"]', function(e) {
		if ('celsius' === e.target.id) {
			$('#degree').html(app.getDegree('C'));
		} else {
			$('#degree').html(app.getDegree('F'));
		}
	});

	$('#getLocation').modal({
		backdrop: 'static',
		keyboard: false
	});

	$('#cityName').on('keypress', function(e) {
		if ("Enter" === e.key) {
			getBySearch()
		}
	});
	$('#citySearch').on('click', function() {
		getBySearch()
	});
	$('#geoNavigator').on('click', function() {
		getByNavigator()
	});

	var apiIconToSkycons = {
		'Sunny.gif': 'clearDay',
		'HeavyRainSwrsDay.gif': 'partlyCloudyDay',
		'HeavySleetSwrsDay.gif': 'partlyCloudyDay',
		'HeavySnowSwrsDay.gif': 'partlyCloudyDay',
		'IsoRainSwrsDay.gif': 'partlyCloudyDay',
		'IsoSleetSwrsDay.gif': 'partlyCloudyDay',
		'IsoSnowSwrsDay.gif': 'partlyCloudyDay',
		'ModRainSwrsDay.gif': 'partlyCloudyDay',
		'ModSleetSwrsDay.gif': 'partlyCloudyDay',
		'ModSnowSwrsDay.gif': 'partlyCloudyDay',
		'PartlyCloudyDay.gif': 'partlyCloudyDay',
		'PartCloudRainThunderDay.gif': 'partlyCloudyDay',
		'PartCloudSleetSnowThunderDay.gif': 'partlyCloudyDay',

		'Clear.gif': 'clearNight',
		'HeavyRainSwrsNight.gif': 'partlyCloudyNight',
		'HeavySleetSwrsNight.gif': 'partlyCloudyNight',
		'HeavySnowSwrsNight.gif': 'partlyCloudyNight',
		'IsoRainSwrsNight.gif': 'partlyCloudyNight',
		'IsoSleetSwrsNight.gif': 'partlyCloudyNight',
		'IsoSnowSwrsNight.gif': 'partlyCloudyNight',
		'ModRainSwrsNight.gif': 'partlyCloudyNight',
		'ModSleetSwrsNight.gif': 'partlyCloudyNight',
		'ModSnowSwrsNight.gif': 'partlyCloudyNight',
		'PartlyCloudyNight.gif': 'partlyCloudyNight',
		'PartCloudRainThunderNight.gif': 'partlyCloudyNight',
		'PartCloudSleetSnowThunderNight.gif': 'partlyCloudyNight',

		'OccLightRain.gif': 'snow',
		'OccLightSnow.gif': 'snow',
		'HeavySnow.gif': 'snow',
		'FreezingDrizzle.gif': 'snow',
		'Blizzard.gif': 'snow',
		'ModSnow.gif': 'snow',

		'Overcast.gif': 'cloudy',
		'Cloudy.gif': 'cloudy',

		'FreezingRain.gif': 'rain',
		'HeavyRain.gif': 'rain',
		'CloudRainThunder.gif': 'rain',
		'ModRain.gif': 'rain',

		'OccLightSleet.gif': 'sleet',
		'CloudSleetSnowThunder.gif': 'sleet',
		'ModSleet.gif': 'sleet',

		'Fog.gif': 'fog',
		'FreezingFog.gif': 'fog'
	};
	var initSkycons = function() {
		var icons = new Skycons({
			"color": "white"
		});

		icons.set("clearDay", Skycons.CLEAR_DAY);
		icons.set("clearNight", Skycons.CLEAR_NIGHT);
		icons.set("partlyCloudyDay", Skycons.PARTLY_CLOUDY_DAY);
		icons.set("partlyCloudyNight", Skycons.PARTLY_CLOUDY_NIGHT);
		icons.set("cloudy", Skycons.CLOUDY);
		icons.set("rain", Skycons.RAIN);
		icons.set("sleet", Skycons.SLEET);
		icons.set("snow", Skycons.SNOW);
		icons.set("wind", Skycons.WIND);
		icons.set("fog", Skycons.FOG);

		icons.play();
	};
	initSkycons();

	var getBySearch = function() {
		var cityName = $('#cityName').val();
		if (cityName) {
			$.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=' + cityName, function(res) {
				if ('OK' !== res.status) {
					console.log(res.status);
					return false;
				};
				console.log(res);
				$('#cityName').val(getAddress(res.results));
				getWeather(res.results[0].geometry.location.lat, res.results[0].geometry.location.lng);
				return true;
			});
		};
	};

	var getByNavigator = function() {
		navigator.geolocation.getCurrentPosition(function(res) {
			getWeather(res.coords.latitude, res.coords.longitude);

			$.getJSON('https://maps.googleapis.com/maps/api/geocode/json?latlng=' + res.coords.latitude.toFixed(2) + ',' + res.coords.longitude.toFixed(2), function(res) {
				if ('OK' !== res.status) {
					console.log(res.status);
					return false;
				};
				$('#cityName').val(getAddress(res.results));
				return true;
			});
		}, function(res) {
			console.log(res);
		});
	};

	var getWeather = function(lat, lng) {
		var urlMyWeather2 = 'http://www.myweather2.com/developer/forecast.ashx?uac=mR9rVt3eKr&output=json&query=' + lat + ',' + lng,
			urlWunderground = 'http://api.wunderground.com/api/Your_Key/geolookup/q/' + lat + ',' + lng + '.json',
			urlOpenWeatherMap = 'http://api.openweathermap.org/data/2.5/weather?lat=' + lat + '&lon=' + lng + '&appid=e144707097a4f86da75f46f2dd83c3c3',
			urlWeatherRunLocked = 'https://api.weatherunlocked.com/api/current/' + lat + ',' + lng + '?app_id=8209d1dc&app_key=306293331a8b4d2ffff84c8a3f8c0013';

		$.ajax({
			headers: {
				Accept: 'application/json'
			},
			url: urlWeatherRunLocked,
			type: 'GET',
			dataType: 'JSON',
			success: function(parsedResponse, statusText, jqXhr) {
				if ('success' === statusText) {

					if (parsedResponse.wx_icon.toLowerCase().indexOf('night') !== -1 || apiIconToSkycons[parsedResponse.wx_icon].toLowerCase().indexOf('night') !== -1) {
						$('body').addClass('night').removeClass('day');
					} else {
						$('body').addClass('day').removeClass('night');
					};

					$('#getLocation').modal('hide');

					console.log(parsedResponse);

					setInfos(parsedResponse);
				}

			},
			error: function(error) {
				console.log(error);
			}
		});
	};

	var getAddress = function(results) {
		var address = 'Address not found';
		results.forEach(function(result) {
			var types = result.types;
			if (2 === types.length && 'locality' === types[0] && 'political' === types[1]) {
				address = result.formatted_address;
				app.cityName = address;
				$('#locationInfo').html(app.cityName);
			}
		});
		return address;
	};

	var setInfos = function(res) {

		console.log(res);

		app.F = res.temp_f;
		app.C = res.temp_c;

		$('#degree.active').removeClass('active');
		$('#' + apiIconToSkycons[res.wx_icon]).addClass('active');
		$('#degree').html(app.getDegree('F'));

		$(".splashscreen").remove();
	};

	var toggleTemp = function() {
		var html = $('#degree').html();
		console.log(html);
	};
});

var app = {
	F: 0,
	C: 0,
	getDegree: function(t) {
		return 'F' === t ? this.F + '°' : this.C + '°'
	}
};
