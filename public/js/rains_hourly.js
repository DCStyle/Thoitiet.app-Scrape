(() => {
    // Function to get chart colors array using native JS
    const getChartColorsArray = (chartId) => {
        const chartElement = document.querySelector(chartId);
        if (!chartElement) return [];

        const colors = chartElement.getAttribute('data-colors');
        if (colors) {
            return JSON.parse(colors);
        }

        // Default colors if none specified
        return ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'];
    };

    // Function to get data attribute using native JS
    const getData = (attributeName) => {
        return (chartId) => {
            const element = document.querySelector(chartId);
            if (!element) return [];

            const dataAttribute = element.getAttribute(attributeName);
            return dataAttribute ? JSON.parse(dataAttribute) : [];
        };
    };

    // Chart initialization
    const chartElement = document.querySelector("#bar_chart");
    if (chartElement) {
        const barColors = getChartColorsArray("#bar_chart");
        const rains_hourly = getData('data-rains')("#bar_chart");

        // We need to ensure next__hours_12 is defined somewhere
        // If it was a global variable in the original code, we need to access it correctly
        // If it wasn't defined elsewhere, we need to define it
        const next__hours_12 = window.next__hours_12 || Array.from({length: 12}, (_, i) => `Hour ${i+1}`);

        const options = {
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

        // Create and render the chart (renamed to avoid variable name collision)
        const rainsHourlyChart = new ApexCharts(chartElement, options);
        rainsHourlyChart.render();
    }
})();
