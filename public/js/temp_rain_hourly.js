(() => {
    // Helper function to get chart colors array
    const getChartColorsArray = (chartId) => {
        const chartElement = document.querySelector(chartId);
        if (!chartElement) return [];

        const colors = chartElement.getAttribute('data-colors');
        if (colors) {
            return JSON.parse(colors);
        }

        // Fallback colors if not specified
        return ['#2ab57d', '#5156be', '#fd625e'];
    };

    // Helper function to get data from element attribute
    const getData = (attribute) => (selector) => {
        const element = document.querySelector(selector);
        if (!element) return [];

        const data = element.getAttribute(attribute);
        return data ? JSON.parse(data) : [];
    };

    const chartElement = document.querySelector("#mixed_chart");
    if (chartElement) {
        const mixedColors = getChartColorsArray("#mixed_chart");
        const temp_hourly = getData('data-temps')("#mixed_chart");
        const cloud_hourly = getData('data-clouds')("#mixed_chart");

        // Get next_hours data from a global variable or data attribute
        const next_hours = window.next__hours || getData('data-hours')("#mixed_chart");

        const options = {
            chart: {
                height: 350,
                type: 'line',
                stacked: false,
                toolbar: {
                    show: true
                },
                animations: {
                    enabled: true,
                },
            },
            stroke: {
                width: [0, 2, 4],
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    if (opt.seriesIndex === 0)
                        return val + "%";
                    else
                        return val + "°";
                },
                offsetY: -9,
                style: {
                    fontSize: '12px',
                    fontWeight: '400',
                },
                background: {
                    enabled: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    columnWidth: '50%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            colors: mixedColors,
            series: [{
                name: 'Khả năng có mưa',
                type: 'column',
                data: cloud_hourly
            }, {
                name: 'Nhiệt độ',
                type: 'line',
                data: temp_hourly
            }],

            labels: next_hours,
            markers: {
                size: 0
            },
            xaxis: {
                type: 'time',
            },
            yaxis: {
                axisBorder: {
                    show: true
                },
                labels: {
                    show: true,
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(y, opt) {
                        if (opt.seriesIndex === 0)
                            return y + "%";
                        else
                            return y + "°";
                    }
                },
            },
            grid: {
                borderColor: '#dd2020'
            }
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();
    }
})();
