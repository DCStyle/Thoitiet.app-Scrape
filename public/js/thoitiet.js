$(".showMore").click(function() {
    if($(this).hasClass("showLess")){
        $(this).removeClass("showLess");
        $(this).html("Xem thêm");
        $("ul.weather-city-inner li").not(".shown").hide();
    } else {
        $(this).addClass("showLess");
        $(this).html("Rút gọn");
        $("ul.weather-city-inner li").not(".shown").show();
    }
})
$(".showmore-weather").click(function() {
    if($(this).hasClass("showless-weather")){
        $(this).removeClass("showless-weather");
        $(this).html("Xem thêm");
        $(".hourly-weather .weather-feature-item").not(".shown").hide();
    } else {
        $(this).addClass("showless-weather");
        $(this).html("Rút gọn");
        $(".hourly-weather .weather-feature-item").not(".shown").show();
    }
})
var num_next_day = $(".weather-nextday-content .weather-nextday-chart").length;
var times_click = Array(num_next_day).fill(0);
for (let i = 1; i <= num_next_day; i++) {
    $(".weather-nextday-chart .showdetail_day_" + i).click(function() {
        times_click[i-1]++;
        if($(this).hasClass("showless_day_" + i)){
            $(this).removeClass("showless_day_" + i);
            $(this).html("Xem chi tiết");
            $(".weather-nextday-chart .charts_day_" + i).hide();
        } else {
            $(this).addClass("showless_day_" + i);
            $(this).html("Rút gọn");
            $(".weather-nextday-chart .charts_day_" + i).show();
            if(times_click[i-1] <= 1) {
                charts_config_arr[i-1]();
            }
        }
    })
}
//console.log(times_click);
let items = document.querySelectorAll('.carousel .carousel-item')
items.forEach((el) => {
    const minPerSlide = 4
    let next = el.nextElementSibling

    for (var i=1; i<minPerSlide; i++) {
        if (!next) {
            // wrap carousel by using first child
            next = items[0]
        }
        let cloneChild = next.cloneNode(true)
        el.appendChild(cloneChild.children[0])
        next = next.nextElementSibling
    }

})

function getChartColorsArray(chartId) {
    var colors = $(chartId).attr('data-colors');
    if(colors) {
        var colors = JSON.parse(colors);
        return colors.map(function (value) {
            var newValue = value.replace(' ', '');

            if (newValue.indexOf('--') != -1) {
                var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                if (color) return color;
            } else {
                return newValue;
            }
        });
    }
} //  line chart datalabel
function getData(dataTag) {
    return function(chartId) {
        var data = $(chartId).attr(dataTag);
        if(data) {
            var data = JSON.parse(data);
            return data;
        } else {
            return [];
        }
    }
}

document.addEventListener('scroll', (e) => {
    lastKnownScrollPosition = window.scrollY;
    if(lastKnownScrollPosition >= 130) {
        $(".weather-menu").addClass("top-menu");
    } else {
        $(".weather-menu").removeClass("top-menu");
    }
});
