"use strict";
// Load the Visualization API and the corechart package.
google.charts.load('current', {
    packages: ['corechart', 'line'],
    language: 'ru'
});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(drawChart);
google.charts.setOnLoadCallback(drawChart2);

// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
function drawChart() {
    var jsonData = jQuery.ajax({
        url: "/wp-content/plugins/TopChik/GetDataGraphics.php?graphic=dynamics",
        dataType: "json",
        async: false
    }).responseText;

    // Create the data table.
    var data = new google.visualization.DataTable(jsonData);
    // Set chart options
    var options = {
        'title': 'Динамика изменения ключевых показателей',
        'height': 350,
        'legend': { 'position': 'left' },
    };
    var chart = new google.charts.Line(document.getElementById('chart_dynamic_div'));

    function errorHandler(errorMessage) {
        //curisosity, check out the error in the console
        //console.log(errorMessage);
        //simply remove the error, the user never see it
        google.visualization.errors.removeError(errorMessage.id);
    }
    google.visualization.events.addListener(chart, 'error', errorHandler);
    chart.draw(data, google.charts.Line.convertOptions(options));
}

function drawChart2() {
    var jsonData = jQuery.ajax({
        url: "/wp-content/plugins/TopChik/GetDataGraphics.php?graphic=dynamics_position",
        dataType: "json",
        async: false
    }).responseText;

    // Create the data table.
    var data = new google.visualization.DataTable(jsonData);
    // Set chart options
    var options = {
        'title': 'Позиций улучшилось/ухудшилось',
        'height': 350,
        'legend': { 'position': 'left' },
        'trendlines': {
            0: {
                'color': 'green',
                'lineWidth': 10,
                'opacity': 0.2,
                'type': 'exponential',
                //'type': 'linear',
                //'degree': 3,
                //'pointsVisible': 'true',
                'title': 'Общий тренд'
            }
        }
    };
    var chart = new google.charts.Line(document.getElementById('chart_position_div'));

    function errorHandler(errorMessage) {
        //curisosity, check out the error in the console
        //console.log(errorMessage);
        //simply remove the error, the user never see it
        google.visualization.errors.removeError(errorMessage.id);
    }
    google.visualization.events.addListener(chart, 'error', errorHandler);
    chart.draw(data, google.charts.Line.convertOptions(options));
}