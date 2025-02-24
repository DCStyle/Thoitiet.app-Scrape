(()=> {
    const chartElement = document.querySelector("#bar_chart");
    if (chartElement) {
        var barColors = getChartColorsArray("#bar_chart");
        var rains_hourly = getData('data-rains')("#bar_chart");
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
                categories: next__hours_12
            },
            legend: {
                show: true,
                showForSingleSeries: true,
                position: 'top',
                horizontalAlign: 'center',
            }
        };
        var rains_hourly = new ApexCharts(chartElement, options);
        rains_hourly.render();
    }
})();
