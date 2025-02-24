// Searh Header
function delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}
$('#thoi-tiet-search-header').keyup(delay(function (e) {
    var _text = $(this).val();
    if (_text!="") {
        $.ajax({
            url: '/api/search-header?key=' + _text,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res){
                $('.thoi-tiet-search-header-result').html(res);
            }
        });
    } else {
        $('.thoi-tiet-search-header-result').html("");
    }
    console.log('S:', this.value);
}, 800));
$('#m-thoi-tiet-search-header').keyup(delay(function (e) {
    var _text = $(this).val();
    if (_text!="") {
        $.ajax({
            url: '/api/search-header?key=' + _text,
            type: 'POST',
            success: function(res){
                $('.m-thoi-tiet-search-header-result').html(res);
            }
        });
    } else {
        $('.m-thoi-tiet-search-header-result').html("");
    }
    console.log('S:', this.value);
}, 800));
function Dong_ho() {
    var gio = document.getElementById("gio");
    var phut = document.getElementById("phut");
    var giay = document.getElementById("giay");
    var Gio_hien_tai = new Date().getHours();
    var Phut_hien_tai = new Date().getMinutes();
    var Giay_hien_tai = new Date().getSeconds();
    Giay_hien_tai = '0'+Giay_hien_tai;
    Phut_hien_tai = '0'+Phut_hien_tai;
    Gio_hien_tai = '0'+Gio_hien_tai;
    gio.innerHTML = Gio_hien_tai.slice(-2);
    phut.innerHTML = Phut_hien_tai.slice(-2);
    giay.innerHTML = Giay_hien_tai.slice(-2);
}

$(document).ready(function() {
    var today = new Date();
    var date = '<span id="ngay_thang">'+today.getDate()+'/'+(today.getMonth()+1)+'/'+today.getFullYear()+'</span>';
    var time = '<span id="gio">00</span><span>:</span><span id="phut">00</span><span>:</span><span id="giay">00</span><span> </span>';
    var dateTime = time+' '+date;
    document.getElementById("s-date-time").innerHTML = dateTime;
    var Dem_gio = setInterval(Dong_ho, 1000);
});
(function($) {
    "use strict";
    $(document).ready(function () {
        function c(passed_month, passed_year, calNum) {
            var calendar = calNum == 0 ? calendars.cal1 : calendars.cal2;
            makeWeek(calendar.weekline);
            calendar.datesBody.empty();
            var calMonthArray = makeMonthArray(passed_month, passed_year);
            var r = 0;
            var u = false;
            while (!u) {
                if (daysArray[r] == calMonthArray[0].weekday) {
                    u = true
                } else {
                    calendar.datesBody.append('<div class="blank"></div>');
                    r++;
                }
            }
            for (var cell = 0; cell < 42 - r; cell++) { // 42 date-cells in calendar
                if (cell >= calMonthArray.length) {
                    calendar.datesBody.append('<div class="blank"></div>');
                } else {
                    var shownDate = calMonthArray[cell].day;
                    var iter_date = new Date(passed_year, passed_month, shownDate);
                    if (
                        (
                            (shownDate != today.getDate() && passed_month == today.getMonth()) || passed_month != today.getMonth()) && iter_date < today) {
                        var m = '<div class="past-date">';
                    } else {
                        var m = checkToday(iter_date) ? '<div class="today">' : "<div>";
                    }
                    calendar.datesBody.append(m + shownDate + "</div>");
                }
            }

            var color = "#444444";
            calendar.calHeader.find("p").text(i[passed_month] + " " + passed_year);
            calendar.weekline.find("div").css("color", color);
            calendar.datesBody.find(".today").css("color", "#00bdaa");

            // find elements (dates) to be clicked on each time
            // the calendar is generated
            var clicked = false;
            selectDates(selected);

            clickedElement = calendar.datesBody.find('div');
            clickedElement.on("click", function () {
                clicked = $(this);
                var whichCalendar = calendar.name;

                if (firstClick && secondClick) {
                    thirdClicked = getClickedInfo(clicked, calendar);
                    var firstClickDateObj = new Date(firstClicked.year,
                        firstClicked.month,
                        firstClicked.date);
                    var secondClickDateObj = new Date(secondClicked.year,
                        secondClicked.month,
                        secondClicked.date);
                    var thirdClickDateObj = new Date(thirdClicked.year,
                        thirdClicked.month,
                        thirdClicked.date);
                    if (secondClickDateObj > thirdClickDateObj && thirdClickDateObj > firstClickDateObj) {
                        secondClicked = thirdClicked;
                        // then choose dates again from the start :)
                        bothCals.find(".calendar_content").find("div").each(function () {
                            $(this).removeClass("selected");
                        });
                        selected = {};
                        selected[firstClicked.year] = {};
                        selected[firstClicked.year][firstClicked.month] = [firstClicked.date];
                        selected = addChosenDates(firstClicked, secondClicked, selected);
                    } else { // reset clicks
                        selected = {};
                        firstClicked = [];
                        secondClicked = [];
                        firstClick = false;
                        secondClick = false;
                        bothCals.find(".calendar_content").find("div").each(function () {
                            $(this).removeClass("selected");
                        });
                    }
                }
                if (!firstClick) {
                    firstClick = true;
                    firstClicked = getClickedInfo(clicked, calendar);
                    selected[firstClicked.year] = {};
                    selected[firstClicked.year][firstClicked.month] = [firstClicked.date];
                } else {
                    secondClick = true;
                    secondClicked = getClickedInfo(clicked, calendar);

                    // what if second clicked date is before the first clicked?
                    var firstClickDateObj = new Date(firstClicked.year,
                        firstClicked.month,
                        firstClicked.date);
                    var secondClickDateObj = new Date(secondClicked.year,
                        secondClicked.month,
                        secondClicked.date);

                    if (firstClickDateObj > secondClickDateObj) {

                        var cachedClickedInfo = secondClicked;
                        secondClicked = firstClicked;
                        firstClicked = cachedClickedInfo;
                        selected = {};
                        selected[firstClicked.year] = {};
                        selected[firstClicked.year][firstClicked.month] = [firstClicked.date];

                    } else if (firstClickDateObj.getTime() == secondClickDateObj.getTime()) {
                        selected = {};
                        firstClicked = [];
                        secondClicked = [];
                        firstClick = false;
                        secondClick = false;
                        $(this).removeClass("selected");
                    }


                    // add between dates to [selected]
                    selected = addChosenDates(firstClicked, secondClicked, selected);
                }
                selectDates(selected);
            });

        }

        function selectDates(selected) {
            if (!$.isEmptyObject(selected)) {
                var dateElements1 = datesBody1.find('div');
                var dateElements2 = datesBody2.find('div');

                function highlightDates(passed_year, passed_month, dateElements) {
                    if (passed_year in selected && passed_month in selected[passed_year]) {
                        var daysToCompare = selected[passed_year][passed_month];
                        for (var d in daysToCompare) {
                            dateElements.each(function (index) {
                                if (parseInt($(this).text()) == daysToCompare[d]) {
                                    $(this).addClass('selected');
                                }
                            });
                        }

                    }
                }

                highlightDates(year, month, dateElements1);
                highlightDates(nextYear, nextMonth, dateElements2);
            }
        }

        function makeMonthArray(passed_month, passed_year) { // creates Array specifying dates and weekdays
            var e = [];
            for (var r = 1; r < getDaysInMonth(passed_year, passed_month) + 1; r++) {
                e.push({
                    day: r,
                    // Later refactor -- weekday needed only for first week
                    weekday: daysArray[getWeekdayNum(passed_year, passed_month, r)]
                });
            }
            return e;
        }

        function makeWeek(week) {
            week.empty();
            for (var e = 0; e < 7; e++) {
                week.append("<div>" + daysArray[e].substring(0, 3) + "</div>")
            }
        }

        function getDaysInMonth(currentYear, currentMon) {
            return (new Date(currentYear, currentMon + 1, 0)).getDate();
        }

        function getWeekdayNum(e, t, n) {
            return (new Date(e, t, n)).getDay();
        }

        function checkToday(e) {
            var todayDate = today.getFullYear() + '/' + (today.getMonth() + 1) + '/' + today.getDate();
            var checkingDate = e.getFullYear() + '/' + (e.getMonth() + 1) + '/' + e.getDate();
            return todayDate == checkingDate;

        }

        function getAdjacentMonth(curr_month, curr_year, direction) {
            var theNextMonth;
            var theNextYear;
            if (direction == "next") {
                theNextMonth = (curr_month + 1) % 12;
                theNextYear = (curr_month == 11) ? curr_year + 1 : curr_year;
            } else {
                theNextMonth = (curr_month == 0) ? 11 : curr_month - 1;
                theNextYear = (curr_month == 0) ? curr_year - 1 : curr_year;
            }
            return [theNextMonth, theNextYear];
        }

        function b() {
            today = new Date;
            year = today.getFullYear();
            month = today.getMonth();
            var nextDates = getAdjacentMonth(month, year, "next");
            nextMonth = nextDates[0];
            nextYear = nextDates[1];
        }

        var e = 480;

        var today;
        var year,
            month,
            nextMonth,
            nextYear;

        var r = [];
        var i = [
            "Tháng 1",
            "Tháng 2",
            "Tháng 3",
            "Tháng 4",
            "Tháng 5",
            "Tháng 6",
            "Tháng 7",
            "Tháng 8",
            "Tháng 9",
            "Tháng 10",
            "Tháng 11",
            "Tháng 12"];
        var daysArray = [
            "CN",
            "Th2",
            "Th3",
            "Th4",
            "Th5",
            "Th6",
            "Th7"];

        var cal1 = $("#calendar_first");
        var calHeader1 = cal1.find(".calendar_header");
        var weekline1 = cal1.find(".calendar_weekdays");
        var datesBody1 = cal1.find(".calendar_content");

        var cal2 = $("#calendar_second");
        var calHeader2 = cal2.find(".calendar_header");
        var weekline2 = cal2.find(".calendar_weekdays");
        var datesBody2 = cal2.find(".calendar_content");

        var bothCals = $(".calendar");

        var switchButton = bothCals.find(".calendar_header").find('.switch-month');

        var calendars = {
            "cal1": {
                "name": "first",
                "calHeader": calHeader1,
                "weekline": weekline1,
                "datesBody": datesBody1
            },
            "cal2": {
                "name": "second",
                "calHeader": calHeader2,
                "weekline": weekline2,
                "datesBody": datesBody2
            }
        }


        var clickedElement;
        var firstClicked,
            secondClicked,
            thirdClicked;
        var firstClick = false;
        var secondClick = false;
        var selected = {};

        b();
        c(month, year, 0);
        c(nextMonth, nextYear, 1);
        switchButton.on("click", function () {
            var clicked = $(this);
            var generateCalendars = function (e) {
                var nextDatesFirst = getAdjacentMonth(month, year, e);
                var nextDatesSecond = getAdjacentMonth(nextMonth, nextYear, e);
                month = nextDatesFirst[0];
                year = nextDatesFirst[1];
                nextMonth = nextDatesSecond[0];
                nextYear = nextDatesSecond[1];

                c(month, year, 0);
                c(nextMonth, nextYear, 1);
            };
            if (clicked.attr("class").indexOf("left") != -1) {
                generateCalendars("previous");
            } else {
                generateCalendars("next");
            }
            clickedElement = bothCals.find(".calendar_content").find("div");
        });


        //  Click picking stuff
        function getClickedInfo(element, calendar) {
            var clickedInfo = {};
            var clickedCalendar,
                clickedMonth,
                clickedYear;
            clickedCalendar = calendar.name;
            clickedMonth = clickedCalendar == "first" ? month : nextMonth;
            clickedYear = clickedCalendar == "first" ? year : nextYear;
            clickedInfo = {
                "calNum": clickedCalendar,
                "date": parseInt(element.text()),
                "month": clickedMonth,
                "year": clickedYear
            }
            return clickedInfo;
        }


        // Finding between dates MADNESS. Needs refactoring and smartening up :)
        function addChosenDates(firstClicked, secondClicked, selected) {
            if (secondClicked.date > firstClicked.date || secondClicked.month > firstClicked.month || secondClicked.year > firstClicked.year) {

                var added_year = secondClicked.year;
                var added_month = secondClicked.month;
                var added_date = secondClicked.date;

                if (added_year > firstClicked.year) {
                    // first add all dates from all months of Second-Clicked-Year
                    selected[added_year] = {};
                    selected[added_year][added_month] = [];
                    for (var i = 1;
                         i <= secondClicked.date;
                         i++) {
                        selected[added_year][added_month].push(i);
                    }

                    added_month = added_month - 1;
                    while (added_month >= 0) {
                        selected[added_year][added_month] = [];
                        for (var i = 1;
                             i <= getDaysInMonth(added_year, added_month);
                             i++) {
                            selected[added_year][added_month].push(i);
                        }
                        added_month = added_month - 1;
                    }

                    added_year = added_year - 1;
                    added_month = 11; // reset month to Dec because we decreased year
                    added_date = getDaysInMonth(added_year, added_month); // reset date as well

                    // Now add all dates from all months of inbetween years
                    while (added_year > firstClicked.year) {
                        selected[added_year] = {};
                        for (var i = 0; i < 12; i++) {
                            selected[added_year][i] = [];
                            for (var d = 1; d <= getDaysInMonth(added_year, i); d++) {
                                selected[added_year][i].push(d);
                            }
                        }
                        added_year = added_year - 1;
                    }
                }

                if (added_month > firstClicked.month) {
                    if (firstClicked.year == secondClicked.year) {
                        selected[added_year][added_month] = [];
                        for (var i = 1;
                             i <= secondClicked.date;
                             i++) {
                            selected[added_year][added_month].push(i);
                        }
                        added_month = added_month - 1;
                    }
                    while (added_month > firstClicked.month) {
                        selected[added_year][added_month] = [];
                        for (var i = 1;
                             i <= getDaysInMonth(added_year, added_month);
                             i++) {
                            selected[added_year][added_month].push(i);
                        }
                        added_month = added_month - 1;
                    }
                    added_date = getDaysInMonth(added_year, added_month);
                }

                for (var i = firstClicked.date + 1;
                     i <= added_date;
                     i++) {
                    selected[added_year][added_month].push(i);
                }
            }
            return selected;
        }
    });


})(jQuery);
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
$(document).ready(function() {
    try {
        var ckTinhThanh = getCookie("cTinhThanh");
        //var ckSlug = getCookie("cSlug");

        if(ckTinhThanh == ""){
            getDiaDiemHienTai2();
        }
    } catch {}

    $( "#id-btn-change-local" ).click(function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(getDiaDiemHienTai);
        } else {
            alert("Trình duyệt hiện tại không hỗ trợ");
        }
    });
    var x = document.getElementById("local-hien-tai");
});
function getDiaDiemHienTai(position) {
    //alert("Latitude: " + position.coords.latitude +" Longitude: " + position.coords.longitude);
    $.ajax({
        url: '/api/find-local?lat=' + position.coords.latitude +'&lon='+ position.coords.longitude,
        type: 'GET',
        dataType: 'JSON',
        xhrFields: { withCredentials: true },
        crossDomain: true,
        success: function(result){
            //var datalc = JSON.parse(result);
            try {
                $("#local-hien-tai").attr("href", result['slug']);
                $("#local-hien-tai").text(result['name'])
            } catch {}
        }
    });
    location.reload();
}
function getDiaDiemHienTai2() {
    $.ajax({
        url: '/api/find-local',
        type: 'GET',
        dataType: 'JSON',
        xhrFields: { withCredentials: true },
        crossDomain: true,
        success: function(result){
            //var datalc = JSON.parse(result);
            try {
                $("#local-hien-tai").attr("href", result['slug']);
                $("#local-hien-tai").text(result['name'])
            } catch {}
        }
    });
}
//
const d = new Date();
var cur_hour = d.getHours();
var next__hours = [];
var next__hours_12 = [];
function gen_next_hours() {
    for (var i = 1; i <= 24; i++) {
        var next_hour = cur_hour + i*3;
        if (next_hour >= 24) {
            next_hour = next_hour % 24;
        }
        next__hours.push(next_hour.toString().padStart(2, 0) + ":00");
    }
}
function gen_next_hours_12() {
    for (var i = 1; i <= 12; i++) {
        var next_hour = cur_hour + i*3;
        if (next_hour >= 24) {
            next_hour = next_hour % 24;
        }
        next__hours_12.push(next_hour.toString().padStart(2, 0) + ":00");
    }
}
gen_next_hours();
gen_next_hours_12();
