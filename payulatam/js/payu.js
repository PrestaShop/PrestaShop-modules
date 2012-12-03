$(".menuTabButton").click(function ()
{
    $(".menuTabButton.selected").removeClass("selected");
    $(this).addClass("selected");
    $(".tabItem.selected").removeClass("selected");
    $("#" + this.id + "Sheet").addClass("selected");
});
