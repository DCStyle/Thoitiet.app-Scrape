var next_day_8 = ['01:00','04:00','07:00','10:00','13:00','16:00','19:00','22:00'];
var charts_config = function(chartId1, chartId2) {
    return function () {
        var barColors = getChartColorsArray(chartId1);
        var rains_hourly = getData('data-rains')(chartId1);
        var options = {
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
        var rains_hourly_chart = new ApexCharts(document.querySelector(chartId1), options);
        rains_hourly_chart.render();

        var lineDatalabelColors = getChartColorsArray(chartId2);
        var temp_hourly = getData('data-temps')(chartId2);
        var options = {
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
                formatter: function(val, opt) {
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
                // align: 'right',
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
                    formatter: function formatter(y, opt) {
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
        var temps_hourly_chart = new ApexCharts(document.querySelector(chartId2), options);
        temps_hourly_chart.render();
    }
};
