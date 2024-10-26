$(document).ready(function() {
    $('#generate_oqrs').on('click', function() {
        $('iframe[name="iframe"]').attr('src', function(i, val) { return val; });
    });
});