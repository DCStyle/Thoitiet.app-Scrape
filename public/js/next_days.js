const next_day_8 = ['01:00','04:00','07:00','10:00','13:00','16:00','19:00','22:00'];

// Function to get data from data attributes
const getData = (attributeName) => {
    return (selector) => {
        const element = document.querySelector(selector);
        if (!element) return [];

        const dataValue = element.getAttribute(attributeName);
        if (!dataValue) return [];

        try {
            return JSON.parse(dataValue);
        } catch (e) {
            console.error(`Error parsing ${attributeName} data:`, e);
            return [];
        }
    };
};

// Function to get chart colors
const getChartColorsArray = (selector) => {
    const element = document.querySelector(selector);
    if (!element) return [];

    const colors = element.getAttribute('data-colors');
    if (!colors) return ['#3498db']; // Default color if none specified

    try {
        return JSON.parse(colors);
    } catch (e) {
        console.error("Error parsing chart colors:", e);
        return ['#3498db']; // Default color on error
    }
};

// Main chart configuration function
const charts_config = function(chartId1, chartId2) {
    return function() {
        // Rain chart configuration
        const barColors = getChartColorsArray(chartId1);
        const rains_hourly = getData('data-rains')(chartId1);

        const rainOptions = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false
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
            dataLabels: {
                enabled: true,
                offsetY: -20,
                formatter: function(val) {
                    return val + "";
                },
            },
            series: [{
                name: 'Lượng mưa (mm)',
                type: 'column',
                data: rains_hourly
            }],
            colors: barColors,
            grid: {
                borderColor: '#f1f1f1'
            },
            xaxis: {
                categories: next_day_8
            },
            legend: {
                show: true,
                showForSingleSeries: true,
                position: 'top',
                horizontalAlign: 'center',
            }
        };

        // Create and render rain chart
        const rainChartElement = document.querySelector(chartId1);
        if (rainChartElement) {
            const rains_hourly_chart = new ApexCharts(rainChartElement, rainOptions);
            rains_hourly_chart.render();
        } else {
            console.error(`Element not found: ${chartId1}`);
        }

        // Temperature chart configuration
        const lineDatalabelColors = getChartColorsArray(chartId2);
        const temp_hourly = getData('data-temps')(chartId2);

        const tempOptions = {
            chart: {
                height: 380,
                type: 'line',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: true
                }
            },
            colors: lineDatalabelColors,
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val + "°";
                },
                offsetY: -5,
                offsetX: 5,
                background: {
                    enabled: false
                }
            },
            stroke: {
                width: [3, 3],
                curve: 'straight'
            },
            series: [{
                name: 'Nhiệt độ (°C)',
                type: 'line',
                data: temp_hourly
            }],
            title: {
                style: {
                    fontWeight: '400'
                }
            },
            grid: {
                borderColor: '#dd2020'
            },
            markers: {
                size: 0
            },
            xaxis: {
                categories: next_day_8,
            },
            yaxis: {
                min: 5,
                max: 40,
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
                    formatter: function(y) {
                        return y + "°C";
                    }
                },
            },
            legend: {
                show: true,
                showForSingleSeries: true,
                position: 'top',
                horizontalAlign: 'center'
            },
            responsive: [{
                breakpoint: 600,
                options: {
                    chart: {
                        toolbar: {
                            show: false
                        }
                    },
                    legend: {
                        show: true,
                        showForSingleSeries: true,
                        position: 'top',
                        horizontalAlign: 'center'
                    }
                }
            }]
        };

        // Create and render temperature chart
        const tempChartElement = document.querySelector(chartId2);
        if (tempChartElement) {
            const temps_hourly_chart = new ApexCharts(tempChartElement, tempOptions);
            temps_hourly_chart.render();
        } else {
            console.error(`Element not found: ${chartId2}`);
        }
    };
};
