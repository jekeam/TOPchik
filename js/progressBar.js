"use strict";

function hslToRgb(h, s, l){
    var r, g, b;

    if(s == 0){
        r = g = b = l; // achromatic
    }else{
        function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.floor(r * 255), Math.floor(g * 255), Math.floor(b * 255)];
}


// convert a number to a color using hsl
function numberToColorHsl(i) {
    // as the function expects a value between 0 and 1, and red = 0° and green = 120°
    // we convert the input to the appropriate hue value
    var hue = i * 1.2 / 360;
    // we convert hsl to rgb (saturation 100%, lightness 50%)
    var rgb = hslToRgb(hue, 1, .5);
    // we format to css value and return
    return 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ')'; 
}


jQuery(document).ready(function ($) {
    var pie1 = $('.pie-1'),
        pie2 = $('.pie-2'),
        pie3 = $('.pie-3'),
        pie4 = $('.pie-4'),
        pie5 = $('.pie-5'),
        pie6 = $('.pie-6');
    
    var all = 0,
        top3 = 0,
        top10 = 0,
        top30 = 0,
        visibility_serp = 0,
        pos_improved = 0,
        pos_deteriorated = 0,
        pos_available = 0;
        
        $.ajax({  
         url: "/wp-content/plugins/TopChik/tch-db-progress-bar.php",  
         cache: false,
         async: false,
         method: "POST",
         dataType: "json",
         //data: ({top: 3}),
         //beforeSend: function()  { $("#content").html("").hide(); },
         success:  function(data){
             //console.log(data);
             top3               = data[0].top3;
             top10              = data[0].top10; 
             top30              = data[0].top30;
             visibility_serp    = Math.round(data[0].visibility_serp);
             all                = data[0].count_all;
             pos_improved       = data[0].pos_improved;
             pos_deteriorated   = data[0].pos_deteriorated;
             pos_available      = data[0].pos_available;
         }
       });  
        
    document.getElementById('cnt_keys').innerHTML = all;
    progressBarUpdate(visibility_serp, 100, pie1,'');
    progressBarUpdate(top3, all, pie2,"<div class='pb_small_text' title='Всего фраз: "+all+"'>"+Math.round(Number(top3/all*100))+"%</div>");
    progressBarUpdate(top10, all, pie3,"<div class='pb_small_text' title='Всего фраз: "+all+"'>"+Math.round(Number(top10/all*100))+"%</div>");
    progressBarUpdate(top30, all, pie4,"<div class='pb_small_text' title='Всего фраз: "+all+"'>"+Math.round(Number(top30/all*100))+"%</div>");
    progressBarUpdate(pos_improved, pos_available, pie5, "<div class='pb_small_text' title='Всего фраз: "+all+"'>"+Math.round(Number(pos_improved/pos_available*100))+"%</div>");
    progressBarUpdate(pos_deteriorated, pos_available, pie6, "<div class='pb_small_text' title='Всего фраз: "+all+"'>"+Math.round(Number(pos_deteriorated/pos_available*100))+"%</div>");
    
});

function rotate(element, degree) {
    element.css({
        '-webkit-transform': 'rotate(' + degree + 'deg)',
        '-moz-transform': 'rotate(' + degree + 'deg)',
        '-ms-transform': 'rotate(' + degree + 'deg)',
        '-o-transform': 'rotate(' + degree + 'deg)',
        'transform': 'rotate(' + degree + 'deg)',
        'zoom': 1
    });
}

function progressBarUpdate(x, outOf, elem, type) {
    var firstHalfAngle = 180;
    var secondHalfAngle = 0;

    // caluclate the angle
    var drawAngle = x / outOf * 360;

    // calculate the angle to be displayed if each half
    if (drawAngle <= 180) {
        firstHalfAngle = drawAngle;
    } else {
        secondHalfAngle = drawAngle - 180;
    }

    // set the transition
    rotate(elem.find(".slice1"), firstHalfAngle);
    rotate(elem.find(".slice2"), secondHalfAngle);

    // set the values on the text
    elem.find(".status").html(x + "<span>" + type + "</span>");
    
     // Раскрасим нужным цветом элементы, в зависисмости от значения
    var $hsl1 = elem.find(".slice1"), $hsl2 = elem.find(".slice2");
    if (elem.find(".red").length == 0 && elem.find(".green").length == 0)
    {
        $hsl1.css({'background-color': numberToColorHsl(drawAngle/3.6)});
        $hsl2.css({'background-color': numberToColorHsl(drawAngle/3.6)});
    }
}