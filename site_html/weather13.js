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
document.write("<br/>Today's date is "+date1);

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

var times = SunCalc.getTimes(new Date(), 37.606697, -93.416709, -0.1);
var sunriseStr = times.sunrise.getHours() + ':' + times.sunrise.getMinutes();
// get position of the sun (azimuth and altitude) at today's sunrise
var sunrisePos = SunCalc.getPosition(times.sunrise, 51.5, -0.1);
// get sunrise azimuth in degrees
var sunriseAzimuth = sunrisePos.azimuth * 180 / Math.PI;

document.write("<br/>Today's date is "+date1);
document.write("<br/>Sunrise is "+sunriseStr);