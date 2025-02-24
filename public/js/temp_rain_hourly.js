(() => {
    const chartElement = document.querySelector("#mixed_chart");
    if (chartElement) {
        var mixedColors = getChartColorsArray("#mixed_chart");
        var temp_hourly = getData('data-temps')("#mixed_chart");
        var cloud_hourly = getData('data-clouds')("#mixed_chart");
        var options = {
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
                    if (opt.seriesIndex == 0)
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

            labels: next__hours,
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
                    formatter: function formatter(y, opt) {
                        if (opt.seriesIndex == 0)
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

        var chart = new ApexCharts(chartElement, options);
        chart.render(); //  Radial chart
    }
})();
