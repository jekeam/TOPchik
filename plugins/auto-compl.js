"use strict";
jQuery(document).ready(function(jQuery) {
    var cache = {};
    jQuery( "#birds" ).autocomplete({
      minLength: 2,
      source: function( request, response ) {
        var term = request.term;
        if ( term in cache ) {
          response( cache[ term ] );
          return;
        }
 
        jQuery.getJSON( "/wp-content/plugins/TopChik/search.php", request, function( data, status, xhr ) {
          cache[ term ] = data;
          response( data );
        });
      }
    });
});    