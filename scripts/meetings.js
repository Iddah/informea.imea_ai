$(document).ready(function() {
    $('#results').infinitescroll({
        navSelector: "#results .paginator",
        nextSelector: "#results .paginator a:last",
        itemSelector: "#results .items",

        finishedMsg: '<em>No more meetings found</em>'
    });
});