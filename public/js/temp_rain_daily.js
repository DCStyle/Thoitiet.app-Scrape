(() => {
    const chartElement = document.querySelector("#line_chart_datalabel");

    if (chartElement) {
        // Function to get chart colors array
        const getChartColorsArray = (chartId) => {
            const colors = document.querySelector(chartId).getAttribute('data-colors');
            return colors ? JSON.parse(colors) : ['#3980c0', '#f1734f'];
        };

        // Function to get data from data attributes
        const getDataAttribute = (attribute, selector) => {
            const dataString = document.querySelector(selector).getAttribute(attribute);
            return dataString ? JSON.parse(dataString) : [];
        };

        const lineDatalabelColors = getChartColorsArray("#line_chart_datalabel");
        const days_label = getDataAttribute('data-dailys', "#line_chart_datalabel");
        const temp_daily = getDataAttribute('data-temps', "#line_chart_datalabel");
        const cloud_daily = getDataAttribute('data-clouds', "#line_chart_datalabel");

        const options = {
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
                    if (opt.seriesIndex == 1)
                        return val + "%";
                    else
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
                name: 'Nhiệt độ',
                type: 'line',
                data: temp_daily
            }, {
                name: 'Khả năng có mưa',
                type: 'line',
                data: cloud_daily
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
                categories: days_label,
            },
            yaxis: {
                min: 5,
                max: 120,
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
                        if (opt.seriesIndex === 0) {
                            return y + "°";
                        } else {
                            return y + "%";
                        }
                    }
                },
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
                        show: false
                    }
                }
            }]
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();
    }
})();
