// BEGIN SCIENCE FRIDAY. Science Friday is a radio program that streams starting at 2pm EasternTime on Friday and is distributed by WNYC-FM, New York City to public radio stations across the nation. This code supplies part of the url of the stream. The url is in the format of: https://s3.amazonaws.com/scifri-episodes/scifri20220211-episode.mp3

// BEGIN WORKS. IN PRODUCTION. PART OF https://stackoverflow.com/a/47889259/8826818
// if you check for last Friday's date on Friday it returns todays date
function getDateOfLast(refday){
    var days = {
        monday: 1,
        tuesday: 2,
        wednesday: 3,
        thursday: 4,
        friday: 5,
        saturday: 6,
        sunday: 0
    };
    if(!days.hasOwnProperty(refday))throw new Error(refday+" is not listed in "+JSON.stringify(days));
    var currDate = new Date();
    var currTimestamp = currDate.getTime();
    var triggerDay = days[refday];
    var dayMillDiff=0;
    var dayInMill = 1000*60*60*24;
    // add a day to dayMillDiff as long as the desired refday (sunday for instance) is not reached
    while(currDate.getDay()!=triggerDay){
        dayMillDiff -= dayInMill;
        currDate = new Date(currDate.getTime()-dayInMill);
    }
    return new Date(currTimestamp + dayMillDiff);
}
// END. WORKS. IN PRODUCTION. PART OF https://stackoverflow.com/a/47889259/8826818



// JavaScript objects
// https://javascript.info/object


// JavaScript date time
//https://www.toptal.com/software/definitive-guide-to-datetime-manipulation



var date = new Date(); // Used for the rest of the script 


// document.write("<br/>The date is "+date.toLocaleDateString('en-US')); // WORKS


var now_utc =  Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(),
 date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());

// document.write("<br/>The date is "+now_utc); // WORKS

//document.write("<br/>The date is "+now_utc.toLocaleDateString('en-US')); // NOT WORK





var date1 = (date.getFullYear() + ("0" + (date.getMonth() + 1)).slice(-2) + ("0" + date.getDate()).slice(-2)); // WORKS. 
var date2 = (date.getFullYear() + ("0" + (date.getMonth() + 1)).slice(-2) + ("0" + date.getDate()).slice(-2)+" " + ("0" + date.getHours() ).slice(-2) +":"+ ("0" + date.getMinutes()).slice(-2)+":" + ("0" + date.getSeconds()).slice(-2) );
//document.write("<br/>Today's date is "+date1);

// BEGIN https://stackoverflow.com/a/44118057/8826818 
// Every Friday at 2pm ET a url is created that contains a date string i.e. 20220204. YYYYMMDD. I am in CentralTime.
// If ( Friday before 2pm EasternTime ){ last Friday's date } else { today's date till Saturday then last Friday's date }
var hour = date.getHours(); // Hour w/o leading zero
var day = date.getDay(); // 0 = Sun
var combined = day+'_'+hour;
var run_on = ['5_0','5_1','5_2','5_3','5_4','5_5','5_6','5_7','5_8','5_9','5_10','5_11','5_12']; // This week's stream starts at 2pm EasternTime. I am in CST.
for (i = 0; i < run_on.length; i++){
    if (combined == run_on[i]){
    // begin https://stackoverflow.com/a/30323659/8826818
    const lastFriday = new Date();
    lastFriday.setDate((new Date().getDate() + (6 - new Date().getDay() - 1) - 7));
    // end https://stackoverflow.com/a/30323659/8826818
    
    var date3 = lastFriday.toLocaleDateString('en-GB').split('/').reverse().join(''); // '20220204' yyyymmdd
    var date3 = (date3.toString());
    // var date3=(lastFriday.getFullYear()+("0" + (lastFriday.getMonth() + 1)).slice(-2)+("0" + lastFriday.getDate()).slice(-2)); //yyyymmdd
    
    // document.write("<br/>It is not after 2pm EasternTime on Friday.");
    // document.write("<br/>The day is "+date.getDay()); // THIS CAN NOT BE day. It breaks if day is used.
    // document.write("<br/>The var hour is "+hour);
    // document.write("<br/>The hour is "+("0" + date.getHours() ).slice(-2) );
    // document.write("<br/>Combined is "+combined+" and run_on is "+run_on[i]);
    // document.write("<br/>The date is "+date3);
    break;
    
    } else {
	    
    var DateOfLastFriday = getDateOfLast("friday"); // if checked on Friday returns today's date
    var date3 = DateOfLastFriday.toLocaleDateString('en-GB').split('/').reverse().join(''); // '20220204'
    var date3 = (date3.toString());
    // var date3=(DateOfLastFriday.getFullYear()+("0" + (DateOfLastFriday.getMonth() + 1)).slice(-2)+("0" + DateOfLastFriday.getDate()).slice(-2)); //yyyymmdd. WORKS

    // document.write("<br/>It is after 2pm EasternTime on Friday.");
    // document.write("<br/>The var date.getDay is "+date.getDay()); // THIS CAN NOT BE day. It breaks if day is used.
    // document.write("<br/>The var hour is "+hour);
    // document.write("<br/>The hour is "+("0" + date.getHours() ).slice(-2) );
    // document.write("<br/>Combined is "+combined+" and run_on is "+run_on[i]);
    // document.write("<br/>Last Friday's date is "+date3);

    }
} 

// END https://stackoverflow.com/a/44118057/8826818 

// END SCIENCE FRIDAY.

//SunCalc.getTimes(/*Date*/ date, /*Number*/ latitude, /*Number*/ longitude, /*Number (default=0)*/ height)
/*
 (c) 2011-2015, Vladimir Agafonkin
 SunCalc is a JavaScript library for calculating sun/moon position and light phases.
 https://github.com/mourner/suncalc
*/

(function () { 'use strict';

// shortcuts for easier to read formulas

var PI   = Math.PI,
    sin  = Math.sin,
    cos  = Math.cos,
    tan  = Math.tan,
    asin = Math.asin,
    atan = Math.atan2,
    acos = Math.acos,
    rad  = PI / 180;

// sun calculations are based on http://aa.quae.nl/en/reken/zonpositie.html formulas


// date/time constants and conversions

var dayMs = 1000 * 60 * 60 * 24,
    J1970 = 2440588,
    J2000 = 2451545;

function toJulian(date) { return date.valueOf() / dayMs - 0.5 + J1970; }
function fromJulian(j)  { return new Date((j + 0.5 - J1970) * dayMs); }
function toDays(date)   { return toJulian(date) - J2000; }


// general calculations for position

var e = rad * 23.4397; // obliquity of the Earth

function rightAscension(l, b) { return atan(sin(l) * cos(e) - tan(b) * sin(e), cos(l)); }
function declination(l, b)    { return asin(sin(b) * cos(e) + cos(b) * sin(e) * sin(l)); }

function azimuth(H, phi, dec)  { return atan(sin(H), cos(H) * sin(phi) - tan(dec) * cos(phi)); }
function altitude(H, phi, dec) { return asin(sin(phi) * sin(dec) + cos(phi) * cos(dec) * cos(H)); }

function siderealTime(d, lw) { return rad * (280.16 + 360.9856235 * d) - lw; }

function astroRefraction(h) {
    if (h < 0) // the following formula works for positive altitudes only.
        h = 0; // if h = -0.08901179 a div/0 would occur.

    // formula 16.4 of "Astronomical Algorithms" 2nd edition by Jean Meeus (Willmann-Bell, Richmond) 1998.
    // 1.02 / tan(h + 10.26 / (h + 5.10)) h in degrees, result in arc minutes -> converted to rad:
    return 0.0002967 / Math.tan(h + 0.00312536 / (h + 0.08901179));
}

// general sun calculations

function solarMeanAnomaly(d) { return rad * (357.5291 + 0.98560028 * d); }

function eclipticLongitude(M) {

    var C = rad * (1.9148 * sin(M) + 0.02 * sin(2 * M) + 0.0003 * sin(3 * M)), // equation of center
        P = rad * 102.9372; // perihelion of the Earth

    return M + C + P + PI;
}

function sunCoords(d) {

    var M = solarMeanAnomaly(d),
        L = eclipticLongitude(M);

    return {
        dec: declination(L, 0),
        ra: rightAscension(L, 0)
    };
}


var SunCalc = {};


// calculates sun position for a given date and latitude/longitude

SunCalc.getPosition = function (date, lat, lng) {

    var lw  = rad * -lng,
        phi = rad * lat,
        d   = toDays(date),

        c  = sunCoords(d),
        H  = siderealTime(d, lw) - c.ra;

    return {
        azimuth: azimuth(H, phi, c.dec),
        altitude: altitude(H, phi, c.dec)
    };
};


// sun times configuration (angle, morning name, evening name)

var times = SunCalc.times = [
    [-0.833, 'sunrise',       'sunset'      ],
    [  -0.3, 'sunriseEnd',    'sunsetStart' ],
    [    -6, 'dawn',          'dusk'        ],
    [   -12, 'nauticalDawn',  'nauticalDusk'],
    [   -18, 'nightEnd',      'night'       ],
    [     6, 'goldenHourEnd', 'goldenHour'  ]
];

// adds a custom time to the times config

SunCalc.addTime = function (angle, riseName, setName) {
    times.push([angle, riseName, setName]);
};


// calculations for sun times

var J0 = 0.0009;

function julianCycle(d, lw) { return Math.round(d - J0 - lw / (2 * PI)); }

function approxTransit(Ht, lw, n) { return J0 + (Ht + lw) / (2 * PI) + n; }
function solarTransitJ(ds, M, L)  { return J2000 + ds + 0.0053 * sin(M) - 0.0069 * sin(2 * L); }

function hourAngle(h, phi, d) { return acos((sin(h) - sin(phi) * sin(d)) / (cos(phi) * cos(d))); }
function observerAngle(height) { return -2.076 * Math.sqrt(height) / 60; }

// returns set time for the given sun altitude
function getSetJ(h, lw, phi, dec, n, M, L) {

    var w = hourAngle(h, phi, dec),
        a = approxTransit(w, lw, n);
    return solarTransitJ(a, M, L);
}


// calculates sun times for a given date, latitude/longitude, and, optionally,
// the observer height (in meters) relative to the horizon

SunCalc.getTimes = function (date, lat, lng, height) {

    height = height || 0;

    var lw = rad * -lng,
        phi = rad * lat,

        dh = observerAngle(height),

        d = toDays(date),
        n = julianCycle(d, lw),
        ds = approxTransit(0, lw, n),

        M = solarMeanAnomaly(ds),
        L = eclipticLongitude(M),
        dec = declination(L, 0),

        Jnoon = solarTransitJ(ds, M, L),

        i, len, time, h0, Jset, Jrise;


    var result = {
        solarNoon: fromJulian(Jnoon),
        nadir: fromJulian(Jnoon - 0.5)
    };

    for (i = 0, len = times.length; i < len; i += 1) {
        time = times[i];
        h0 = (time[0] + dh) * rad;

        Jset = getSetJ(h0, lw, phi, dec, n, M, L);
        Jrise = Jnoon - (Jset - Jnoon);

        result[time[1]] = fromJulian(Jrise);
        result[time[2]] = fromJulian(Jset);
    }

    return result;
};


// moon calculations, based on http://aa.quae.nl/en/reken/hemelpositie.html formulas

function moonCoords(d) { // geocentric ecliptic coordinates of the moon

    var L = rad * (218.316 + 13.176396 * d), // ecliptic longitude
        M = rad * (134.963 + 13.064993 * d), // mean anomaly
        F = rad * (93.272 + 13.229350 * d),  // mean distance

        l  = L + rad * 6.289 * sin(M), // longitude
        b  = rad * 5.128 * sin(F),     // latitude
        dt = 385001 - 20905 * cos(M);  // distance to the moon in km

    return {
        ra: rightAscension(l, b),
        dec: declination(l, b),
        dist: dt
    };
}

SunCalc.getMoonPosition = function (date, lat, lng) {

    var lw  = rad * -lng,
        phi = rad * lat,
        d   = toDays(date),

        c = moonCoords(d),
        H = siderealTime(d, lw) - c.ra,
        h = altitude(H, phi, c.dec),
        // formula 14.1 of "Astronomical Algorithms" 2nd edition by Jean Meeus (Willmann-Bell, Richmond) 1998.
        pa = atan(sin(H), tan(phi) * cos(c.dec) - sin(c.dec) * cos(H));

    h = h + astroRefraction(h); // altitude correction for refraction

    return {
        azimuth: azimuth(H, phi, c.dec),
        altitude: h,
        distance: c.dist,
        parallacticAngle: pa
    };
};


// calculations for illumination parameters of the moon,
// based on http://idlastro.gsfc.nasa.gov/ftp/pro/astro/mphase.pro formulas and
// Chapter 48 of "Astronomical Algorithms" 2nd edition by Jean Meeus (Willmann-Bell, Richmond) 1998.

SunCalc.getMoonIllumination = function (date) {

    var d = toDays(date || new Date()),
        s = sunCoords(d),
        m = moonCoords(d),

        sdist = 149598000, // distance from Earth to Sun in km

        phi = acos(sin(s.dec) * sin(m.dec) + cos(s.dec) * cos(m.dec) * cos(s.ra - m.ra)),
        inc = atan(sdist * sin(phi), m.dist - sdist * cos(phi)),
        angle = atan(cos(s.dec) * sin(s.ra - m.ra), sin(s.dec) * cos(m.dec) -
                cos(s.dec) * sin(m.dec) * cos(s.ra - m.ra));

    return {
        fraction: (1 + cos(inc)) / 2,
        phase: 0.5 + 0.5 * inc * (angle < 0 ? -1 : 1) / Math.PI,
        angle: angle
    };
};


function hoursLater(date, h) {
    return new Date(date.valueOf() + h * dayMs / 24);
}

// calculations for moon rise/set times are based on http://www.stargazing.net/kepler/moonrise.html article

SunCalc.getMoonTimes = function (date, lat, lng, inUTC) {
    var t = new Date(date);
    if (inUTC) t.setUTCHours(0, 0, 0, 0);
    else t.setHours(0, 0, 0, 0);

    var hc = 0.133 * rad,
        h0 = SunCalc.getMoonPosition(t, lat, lng).altitude - hc,
        h1, h2, rise, set, a, b, xe, ye, d, roots, x1, x2, dx;

    // go in 2-hour chunks, each time seeing if a 3-point quadratic curve crosses zero (which means rise or set)
    for (var i = 1; i <= 24; i += 2) {
        h1 = SunCalc.getMoonPosition(hoursLater(t, i), lat, lng).altitude - hc;
        h2 = SunCalc.getMoonPosition(hoursLater(t, i + 1), lat, lng).altitude - hc;

        a = (h0 + h2) / 2 - h1;
        b = (h2 - h0) / 2;
        xe = -b / (2 * a);
        ye = (a * xe + b) * xe + h1;
        d = b * b - 4 * a * h1;
        roots = 0;

        if (d >= 0) {
            dx = Math.sqrt(d) / (Math.abs(a) * 2);
            x1 = xe - dx;
            x2 = xe + dx;
            if (Math.abs(x1) <= 1) roots++;
            if (Math.abs(x2) <= 1) roots++;
            if (x1 < -1) x1 = x2;
        }

        if (roots === 1) {
            if (h0 < 0) rise = i + x1;
            else set = i + x1;

        } else if (roots === 2) {
            rise = i + (ye < 0 ? x2 : x1);
            set = i + (ye < 0 ? x1 : x2);
        }

        if (rise && set) break;

        h0 = h2;
    }

    var result = {};

    if (rise) result.rise = hoursLater(t, rise);
    if (set) result.set = hoursLater(t, set);

    if (!rise && !set) result[ye > 0 ? 'alwaysUp' : 'alwaysDown'] = true;

    return result;
};


// export as Node module / AMD module / browser variable
if (typeof exports === 'object' && typeof module !== 'undefined') module.exports = SunCalc;
else if (typeof define === 'function' && define.amd) define(SunCalc);
else window.SunCalc = SunCalc;

}());
var times = SunCalc.getTimes(new Date(), 37.606697, -93.416709, -0.1);
var sunriseStr = times.sunrise.getHours() + ':' + times.sunrise.getMinutes();
// get position of the sun (azimuth and altitude) at today's sunrise
var sunrisePos = SunCalc.getPosition(times.sunrise, 51.5, -0.1);
// get sunrise azimuth in degrees
var sunriseAzimuth = sunrisePos.azimuth * 180 / Math.PI;

//document.write("<br/>Today's date is "+date1);
//document.write("<br/>Sunrise is "+sunriseStr);