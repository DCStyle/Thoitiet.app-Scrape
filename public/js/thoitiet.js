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
