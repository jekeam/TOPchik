"use strict";
// Load the Visualization API and the corechart package.
google.charts.load('current', {
    packages: ['corechart', 'line'],
    language: 'ru'
});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(drawChart);

// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
function drawChart() {
    var v_post_id = jQuery('#post_ID').val();
    var jsonData = jQuery.ajax({
        url: "/wp-content/plugins/ТопЧик - анализ поисковых запросов/GetDataGraphics.php?post_id="+v_post_id,
        dataType: "json",
        async: false
    }).responseText;
    
    // Create the data table.
    var data = new google.visualization.DataTable(jsonData);

    // Set chart options
    var options = {
        //'interpolateNulls':'true',
        'title': 'Анализ поисковых запросов',
        //'curveType': 'function',
        'width': 900,
        'height': 300,
        'legend': {
            'position': 'right'
        },
         'hAxis': {
          'title': 'Дата',
          'format': 'M.d',
          //'gridlines': {'count': 30}
        },
        'vAxis': {
          'title': 'Позиция',
          'direction':'-1',
          'maxValue':100,
          'minValue':0,
          'viewWindowMode': 'explicit',
           'gridlines': {'count': '10',}
        },
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

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);
        
    //var chart = new google.charts.Line(document.getElementById('chart_div'));
    //chart.draw(data, google.charts.Line.convertOptions(options));
    //to-do trendlines https://github.com/google/google-visualization-issues/issues/2143
    
    function errorHandler(errorMessage) {
        //curisosity, check out the error in the console
        console.log(errorMessage);
    
        //simply remove the error, the user never see it
        google.visualization.errors.removeError(errorMessage.id);
    }
    google.visualization.events.addListener(chart, 'error', errorHandler);
}