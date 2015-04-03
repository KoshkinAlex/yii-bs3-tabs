
$( document ).ready( function() {
    var url = document.location.toString();
    if (url.match('#')) {
        //$('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
        var tab = url.split('#')[1];

        $('.nav-tabs a[data-toggle="tab"]').each( function(index, element) {
            var params = $(element).attr("href").split('#');
            if (params.length == 2 && params[1] == tab) {
                $(element).tab('show');
            }
        });
    }
    $('.nav-pills, .nav-tabs').tabdrop();
});

$( document ).on('show.bs.tab', '.nav-tabs a[data-toggle="tab"]', function( event ) {
    var params = $(event.target).attr("href").split('#');
    if (params.length == 2) {
        var url = params[0];
        var container = '#'+params[1];
    } else {
        return true;
    }

    if(history.pushState) {
        history.pushState(null, null, container);
    } else {
        location.hash = container;
    }

    if ($(container).is(':empty')) {
        $.ajax({
            type: "GET",
            url: url,
            error: function(data){
                window.location = url;
            },
            success: function(data){
                $(container).html(data);
            }
        })
    }
});