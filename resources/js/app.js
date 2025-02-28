import './bootstrap';

// Add class "is-sticky" to the header when the page is scrolled
window.addEventListener('scroll', function() {
    var header = document.querySelector('.navbar');
    header.classList.toggle('is-sticky', window.scrollY > 0);
});

// Show more/less for weather city
document.addEventListener('DOMContentLoaded', function() {
    // Show more/less for weather city
    const showMoreBtn = document.querySelector('.showMore');
    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', function() {
            if (this.classList.contains('showLess')) {
                this.classList.remove('showLess');
                this.textContent = 'Xem thêm';
                document.querySelectorAll('ul.weather-city-inner li:not(.shown)').forEach(item => {
                    item.style.display = 'none';
                });
            } else {
                this.classList.add('showLess');
                this.textContent = 'Rút gọn';
                document.querySelectorAll('ul.weather-city-inner li:not(.shown)').forEach(item => {
                    item.style.display = '';
                });
            }
        });
    }

    // Show more/less for weather features
    const showMoreWeatherBtn = document.querySelector('.showmore-weather');
    if (showMoreWeatherBtn) {
        showMoreWeatherBtn.addEventListener('click', function() {
            if (this.classList.contains('showless-weather')) {
                this.classList.remove('showless-weather');
                this.textContent = 'Xem thêm';
                document.querySelectorAll('.hourly-weather .weather-feature-item:not(.shown)').forEach(item => {
                    item.style.display = 'none';
                });
            } else {
                this.classList.add('showless-weather');
                this.textContent = 'Rút gọn';
                document.querySelectorAll('.hourly-weather .weather-feature-item:not(.shown)').forEach(item => {
                    item.style.display = '';
                });
            }
        });
    }

    // Weather next day charts handling
    const nextDayCharts = document.querySelectorAll('.weather-nextday-content .weather-nextday-chart');
    const numNextDay = nextDayCharts.length;
    const timesClick = Array(numNextDay).fill(0);

    for (let i = 1; i <= numNextDay; i++) {
        const showDetailBtn = document.querySelector(`.weather-nextday-chart .showdetail_day_${i}`);
        if (showDetailBtn) {
            showDetailBtn.addEventListener('click', function() {
                timesClick[i-1]++;
                const lessDayClass = `showless_day_${i}`;

                if (this.classList.contains(lessDayClass)) {
                    this.classList.remove(lessDayClass);
                    this.textContent = 'Xem chi tiết';
                    document.querySelectorAll(`.weather-nextday-chart .charts_day_${i}`).forEach(chart => {
                        chart.style.display = 'none';
                    });
                } else {
                    this.classList.add(lessDayClass);
                    this.textContent = 'Rút gọn';
                    document.querySelectorAll(`.weather-nextday-chart .charts_day_${i}`).forEach(chart => {
                        chart.style.display = '';
                    });

                    if (timesClick[i-1] <= 1 && typeof charts_config_arr !== 'undefined' && charts_config_arr[i-1]) {
                        charts_config_arr[i-1]();
                    }
                }
            });
        }
    }

    // Carousel handling
    const carouselItems = document.querySelectorAll('.carousel .carousel-item');
    carouselItems.forEach(el => {
        const minPerSlide = 4;
        let next = el.nextElementSibling;

        for (let i = 1; i < minPerSlide; i++) {
            if (!next) {
                // Wrap carousel by using first child
                next = carouselItems[0];
            }
            const cloneChild = next.cloneNode(true);
            el.appendChild(cloneChild.children[0]);
            next = next.nextElementSibling;
        }
    });

    // Get chart colors function
    function getChartColorsArray(chartId) {
        const chartElement = document.querySelector(chartId);
        if (!chartElement) return [];

        const colors = chartElement.getAttribute('data-colors');
        if (colors) {
            const colorArray = JSON.parse(colors);
            return colorArray.map(function(value) {
                const newValue = value.replace(' ', '');

                if (newValue.indexOf('--') !== -1) {
                    const color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                    if (color) return color;
                } else {
                    return newValue;
                }
            });
        }
        return [];
    }

    // Get chart data function
    function getData(dataTag) {
        return function(chartId) {
            const chartElement = document.querySelector(chartId);
            if (!chartElement) return [];

            const data = chartElement.getAttribute(dataTag);
            if (data) {
                return JSON.parse(data);
            } else {
                return [];
            }
        };
    }

    // Make the functions available globally if needed
    window.getChartColorsArray = getChartColorsArray;
    window.getData = getData;

    // Scroll event for sticky menu
    window.addEventListener('scroll', function() {
        const lastKnownScrollPosition = window.scrollY;
        const weatherMenu = document.querySelector('.weather-menu');

        if (weatherMenu) {
            if (lastKnownScrollPosition >= 130) {
                weatherMenu.classList.add('top-menu');
            } else {
                weatherMenu.classList.remove('top-menu');
            }
        }
    });
});

// Search Header
function delay(callback, ms) {
    let timer = 0;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timer);
        timer = setTimeout(() => {
            callback.apply(context, args);
        }, ms || 0);
    };
}

// Handle desktop search
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('thoi-tiet-search-header');
    const mobileSearchInput = document.getElementById('m-thoi-tiet-search-header');
    const searchResultsContainer = document.querySelector('.thoi-tiet-search-header-result');
    const mobileSearchResultsContainer = document.querySelector('.m-thoi-tiet-search-header-result');

    if (searchInput) {
        searchInput.addEventListener('keyup', delay(function(e) {
            const searchText = this.value;
            if (searchText != "") {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/api/search-header?key=${searchText}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(response => response.text())
                    .then(data => {
                        searchResultsContainer.innerHTML = data;
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                searchResultsContainer.innerHTML = "";
            }
            console.log('S:', this.value);
        }, 800));
    }

    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('keyup', delay(function(e) {
            const searchText = this.value;
            if (searchText != "") {
                fetch(`/api/search-header?key=${searchText}`, {
                    method: 'POST'
                })
                    .then(response => response.text())
                    .then(data => {
                        mobileSearchResultsContainer.innerHTML = data;
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                mobileSearchResultsContainer.innerHTML = "";
            }
            console.log('S:', this.value);
        }, 800));
    }
});

// Clock function
function Dong_ho() {
    const gio = document.getElementById("gio");
    const phut = document.getElementById("phut");
    const giay = document.getElementById("giay");

    if (!gio || !phut || !giay) return;

    const now = new Date();
    const Gio_hien_tai = now.getHours();
    const Phut_hien_tai = now.getMinutes();
    const Giay_hien_tai = now.getSeconds();

    gio.innerHTML = ('0' + Gio_hien_tai).slice(-2);
    phut.innerHTML = ('0' + Phut_hien_tai).slice(-2);
    giay.innerHTML = ('0' + Giay_hien_tai).slice(-2);
}

// Calendar functionality
document.addEventListener('DOMContentLoaded', () => {
    // Initialize variables
    const today = new Date();
    let year = today.getFullYear();
    let month = today.getMonth();

    const getAdjacentMonth = (curr_month, curr_year, direction) => {
        let theNextMonth;
        let theNextYear;
        if (direction == "next") {
            theNextMonth = (curr_month + 1) % 12;
            theNextYear = (curr_month == 11) ? curr_year + 1 : curr_year;
        } else {
            theNextMonth = (curr_month == 0) ? 11 : curr_month - 1;
            theNextYear = (curr_month == 0) ? curr_year - 1 : curr_year;
        }
        return [theNextMonth, theNextYear];
    };

    const nextDates = getAdjacentMonth(month, year, "next");
    let nextMonth = nextDates[0];
    let nextYear = nextDates[1];

    const monthNames = [
        "Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6",
        "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"
    ];

    const daysArray = ["CN", "Th2", "Th3", "Th4", "Th5", "Th6", "Th7"];

    const cal1 = document.getElementById("calendar_first");
    const cal2 = document.getElementById("calendar_second");

    if (!cal1 || !cal2) return;

    const calHeader1 = cal1.querySelector(".calendar_header");
    const weekline1 = cal1.querySelector(".calendar_weekdays");
    const datesBody1 = cal1.querySelector(".calendar_content");

    const calHeader2 = cal2.querySelector(".calendar_header");
    const weekline2 = cal2.querySelector(".calendar_weekdays");
    const datesBody2 = cal2.querySelector(".calendar_content");

    const bothCals = document.querySelectorAll(".calendar");
    const switchButtons = document.querySelectorAll(".calendar_header .switch-month");

    const calendars = {
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
    };

    let firstClicked, secondClicked, thirdClicked;
    let firstClick = false;
    let secondClick = false;
    let selected = {};

    // Helper functions
    function getDaysInMonth(currentYear, currentMonth) {
        return new Date(currentYear, currentMonth + 1, 0).getDate();
    }

    function getWeekdayNum(e, t, n) {
        return new Date(e, t, n).getDay();
    }

    function checkToday(e) {
        const todayDate = `${today.getFullYear()}/${today.getMonth() + 1}/${today.getDate()}`;
        const checkingDate = `${e.getFullYear()}/${e.getMonth() + 1}/${e.getDate()}`;
        return todayDate == checkingDate;
    }

    function makeWeek(weekElement) {
        weekElement.innerHTML = '';
        for (let e = 0; e < 7; e++) {
            const dayDiv = document.createElement('div');
            dayDiv.textContent = daysArray[e].substring(0, 3);
            weekElement.appendChild(dayDiv);
        }
    }

    function makeMonthArray(passed_month, passed_year) {
        const e = [];
        for (let r = 1; r < getDaysInMonth(passed_year, passed_month) + 1; r++) {
            e.push({
                day: r,
                weekday: daysArray[getWeekdayNum(passed_year, passed_month, r)]
            });
        }
        return e;
    }

    function getClickedInfo(element, calendar) {
        const clickedCalendar = calendar.name;
        const clickedMonth = clickedCalendar == "first" ? month : nextMonth;
        const clickedYear = clickedCalendar == "first" ? year : nextYear;

        return {
            "calNum": clickedCalendar,
            "date": parseInt(element.textContent),
            "month": clickedMonth,
            "year": clickedYear
        };
    }

    function selectDates(selected) {
        if (Object.keys(selected).length === 0) return;

        const dateElements1 = datesBody1.querySelectorAll('div');
        const dateElements2 = datesBody2.querySelectorAll('div');

        function highlightDates(passed_year, passed_month, dateElements) {
            if (passed_year in selected && passed_month in selected[passed_year]) {
                const daysToCompare = selected[passed_year][passed_month];
                dateElements.forEach(element => {
                    if (daysToCompare.includes(parseInt(element.textContent))) {
                        element.classList.add('selected');
                    }
                });
            }
        }

        highlightDates(year, month, dateElements1);
        highlightDates(nextYear, nextMonth, dateElements2);
    }

    function addChosenDates(firstClicked, secondClicked, selected) {
        if (secondClicked.date > firstClicked.date ||
            secondClicked.month > firstClicked.month ||
            secondClicked.year > firstClicked.year) {

            let added_year = secondClicked.year;
            let added_month = secondClicked.month;
            let added_date = secondClicked.date;

            if (added_year > firstClicked.year) {
                // First add all dates from all months of Second-Clicked-Year
                selected[added_year] = {};
                selected[added_year][added_month] = [];

                for (let i = 1; i <= secondClicked.date; i++) {
                    selected[added_year][added_month].push(i);
                }

                added_month = added_month - 1;
                while (added_month >= 0) {
                    selected[added_year][added_month] = [];
                    for (let i = 1; i <= getDaysInMonth(added_year, added_month); i++) {
                        selected[added_year][added_month].push(i);
                    }
                    added_month = added_month - 1;
                }

                added_year = added_year - 1;
                added_month = 11; // Reset month to Dec because we decreased year
                added_date = getDaysInMonth(added_year, added_month); // Reset date as well

                // Now add all dates from all months of in-between years
                while (added_year > firstClicked.year) {
                    selected[added_year] = {};
                    for (let i = 0; i < 12; i++) {
                        selected[added_year][i] = [];
                        for (let d = 1; d <= getDaysInMonth(added_year, i); d++) {
                            selected[added_year][i].push(d);
                        }
                    }
                    added_year = added_year - 1;
                }
            }

            if (added_month > firstClicked.month) {
                if (firstClicked.year == secondClicked.year) {
                    if (!selected[added_year]) {
                        selected[added_year] = {};
                    }
                    selected[added_year][added_month] = [];
                    for (let i = 1; i <= secondClicked.date; i++) {
                        selected[added_year][added_month].push(i);
                    }
                    added_month = added_month - 1;
                }

                while (added_month > firstClicked.month) {
                    if (!selected[added_year]) {
                        selected[added_year] = {};
                    }
                    selected[added_year][added_month] = [];
                    for (let i = 1; i <= getDaysInMonth(added_year, added_month); i++) {
                        selected[added_year][added_month].push(i);
                    }
                    added_month = added_month - 1;
                }
                added_date = getDaysInMonth(added_year, added_month);
            }

            if (!selected[added_year]) {
                selected[added_year] = {};
            }
            if (!selected[added_year][added_month]) {
                selected[added_year][added_month] = [];
            }

            for (let i = firstClicked.date + 1; i <= added_date; i++) {
                selected[added_year][added_month].push(i);
            }
        }
        return selected;
    }

    // Calendar creation function
    function createCalendar(passed_month, passed_year, calNum) {
        const calendar = calNum == 0 ? calendars.cal1 : calendars.cal2;
        makeWeek(calendar.weekline);
        calendar.datesBody.innerHTML = '';

        const calMonthArray = makeMonthArray(passed_month, passed_year);
        let r = 0;
        let u = false;

        while (!u) {
            if (daysArray[r] == calMonthArray[0].weekday) {
                u = true;
            } else {
                const blankDiv = document.createElement('div');
                blankDiv.className = 'blank';
                calendar.datesBody.appendChild(blankDiv);
                r++;
            }
        }

        for (let cell = 0; cell < 42 - r; cell++) {
            if (cell >= calMonthArray.length) {
                const blankDiv = document.createElement('div');
                blankDiv.className = 'blank';
                calendar.datesBody.appendChild(blankDiv);
            } else {
                const shownDate = calMonthArray[cell].day;
                const iter_date = new Date(passed_year, passed_month, shownDate);

                const dateDiv = document.createElement('div');

                if ((shownDate != today.getDate() && passed_month == today.getMonth()) ||
                    passed_month != today.getMonth() && iter_date < today) {
                    dateDiv.className = 'past-date';
                } else if (checkToday(iter_date)) {
                    dateDiv.className = 'today';
                }

                dateDiv.textContent = shownDate;
                calendar.datesBody.appendChild(dateDiv);
            }
        }

        const color = "#444444";
        const headerText = calendar.calHeader.querySelector("p");
        if (headerText) {
            headerText.textContent = `${monthNames[passed_month]} ${passed_year}`;
        }

        const weekDivs = calendar.weekline.querySelectorAll("div");
        weekDivs.forEach(div => {
            div.style.color = color;
        });

        const todayElements = calendar.datesBody.querySelectorAll(".today");
        todayElements.forEach(el => {
            el.style.color = "#00bdaa";
        });

        // Add click events to date elements
        const dateElements = calendar.datesBody.querySelectorAll('div:not(.blank)');
        dateElements.forEach(el => {
            el.addEventListener('click', function() {
                const whichCalendar = calendar.name;

                if (firstClick && secondClick) {
                    thirdClicked = getClickedInfo(this, calendar);
                    const firstClickDateObj = new Date(firstClicked.year, firstClicked.month, firstClicked.date);
                    const secondClickDateObj = new Date(secondClicked.year, secondClicked.month, secondClicked.date);
                    const thirdClickDateObj = new Date(thirdClicked.year, thirdClicked.month, thirdClicked.date);

                    if (secondClickDateObj > thirdClickDateObj && thirdClickDateObj > firstClickDateObj) {
                        secondClicked = thirdClicked;
                        // Reset selections
                        bothCals.forEach(cal => {
                            const divs = cal.querySelectorAll(".calendar_content div");
                            divs.forEach(div => div.classList.remove("selected"));
                        });

                        selected = {};
                        selected[firstClicked.year] = {};
                        selected[firstClicked.year][firstClicked.month] = [firstClicked.date];
                        selected = addChosenDates(firstClicked, secondClicked, selected);
                    } else {
                        // Reset clicks
                        selected = {};
                        firstClicked = null;
                        secondClicked = null;
                        firstClick = false;
                        secondClick = false;

                        bothCals.forEach(cal => {
                            const divs = cal.querySelectorAll(".calendar_content div");
                            divs.forEach(div => div.classList.remove("selected"));
                        });
                    }
                }

                if (!firstClick) {
                    firstClick = true;
                    firstClicked = getClickedInfo(this, calendar);
                    selected[firstClicked.year] = {};
                    selected[firstClicked.year][firstClicked.month] = [firstClicked.date];
                } else {
                    secondClick = true;
                    secondClicked = getClickedInfo(this, calendar);

                    // What if second clicked date is before the first clicked?
                    const firstClickDateObj = new Date(firstClicked.year, firstClicked.month, firstClicked.date);
                    const secondClickDateObj = new Date(secondClicked.year, secondClicked.month, secondClicked.date);

                    if (firstClickDateObj > secondClickDateObj) {
                        const cachedClickedInfo = secondClicked;
                        secondClicked = firstClicked;
                        firstClicked = cachedClickedInfo;
                        selected = {};
                        selected[firstClicked.year] = {};
                        selected[firstClicked.year][firstClicked.month] = [firstClicked.date];
                    } else if (firstClickDateObj.getTime() == secondClickDateObj.getTime()) {
                        selected = {};
                        firstClicked = null;
                        secondClicked = null;
                        firstClick = false;
                        secondClick = false;
                        this.classList.remove("selected");
                    }

                    // Add between dates to [selected]
                    selected = addChosenDates(firstClicked, secondClicked, selected);
                }

                selectDates(selected);
            });
        });
    }

    // Initialize calendars
    createCalendar(month, year, 0);
    createCalendar(nextMonth, nextYear, 1);

    // Add event listeners to switch buttons
    switchButtons.forEach(button => {
        button.addEventListener('click', function() {
            const generateCalendars = (direction) => {
                const nextDatesFirst = getAdjacentMonth(month, year, direction);
                const nextDatesSecond = getAdjacentMonth(nextMonth, nextYear, direction);
                month = nextDatesFirst[0];
                year = nextDatesFirst[1];
                nextMonth = nextDatesSecond[0];
                nextYear = nextDatesSecond[1];

                createCalendar(month, year, 0);
                createCalendar(nextMonth, nextYear, 1);
            };

            if (this.classList.contains('left')) {
                generateCalendars("previous");
            } else {
                generateCalendars("next");
            }
        });
    });
});

// Hours generation
const d = new Date();
const cur_hour = d.getHours();
const next__hours = [];
const next__hours_12 = [];

function gen_next_hours() {
    for (let i = 1; i <= 24; i++) {
        let next_hour = cur_hour + i * 3;
        if (next_hour >= 24) {
            next_hour = next_hour % 24;
        }
        next__hours.push(next_hour.toString().padStart(2, '0') + ":00");
    }
}

function gen_next_hours_12() {
    for (let i = 1; i <= 12; i++) {
        let next_hour = cur_hour + i * 3;
        if (next_hour >= 24) {
            next_hour = next_hour % 24;
        }
        next__hours_12.push(next_hour.toString().padStart(2, '0') + ":00");
    }
}

gen_next_hours();
gen_next_hours_12();

// Initialize clock if elements exist
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById("gio")) {
        Dong_ho();
        setInterval(Dong_ho, 1000);
    }
});

// Get local
function getCookie(cname) {
    const name = `${cname}=`;
    const cookies = document.cookie.split(';');

    for (let cookie of cookies) {
        cookie = cookie.trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length);
        }
    }
    return "";
}

// Main initialization function
document.addEventListener('DOMContentLoaded', function() {
    const localElement = document.getElementById("local-hien-tai");

    // Check if we need to get location
    if (getCookie("cTinhThanh") === "") {
        getDefaultLocation();
    }

    // Set up location button
    document.getElementById("id-btn-change-local").addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                getCurrentLocation,
                error => console.error("Geolocation error:", error),
                { maximumAge: 60000, timeout: 5000 }
            );
        } else {
            alert("Trình duyệt không hỗ trợ.");
        }
    });
});

// Function to get location using coordinates
function getCurrentLocation(position) {
    const baseUrl = window.location.origin;
    const { latitude, longitude } = position.coords;

    fetchLocation(`${baseUrl}/api/find-local?lat=${latitude}&lon=${longitude}`)
        .then(() => window.location.reload());
}

// Function to get default location
function getDefaultLocation() {
    const baseUrl = window.location.origin;
    fetchLocation(`${baseUrl}/api/find-local`);
}

// Shared location fetching logic
function fetchLocation(url) {
    return fetch(url, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) throw new Error(`Network response error: ${response.status}`);
            return response.json();
        })
        .then(result => {
            const localElement = document.getElementById("local-hien-tai");
            if (localElement) {
                localElement.setAttribute("href", result.slug);
                localElement.textContent = result.name;
            }
            return result;
        })
        .catch(error => console.error("Error fetching location:", error));
}
