/**
* 2014 Popover jQuery Plugin
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
*  @author    Jordan Kelly
*  @copyright 2014 Jordan Kelly
*  @license   Licensed under the MIT License.
*/

!function ($) {
    
    var methods = {
        _init: function(options, popover) {
            //Theme modifiers
            if(typeof(options.backgroundColor) !== 'undefined'){
                Popover.setBackgroundColor(options.backgroundColor);
            }

            if(typeof(options.fontColor) !== 'undefined'){
                Popover.setFontColor(options.fontColor);
            }

            if(typeof(options.borderColor) !== 'undefined'){
                Popover.setBorderColor(options.borderColor);
            }

            //Functionality modifiers
            //TODO: Rename disableBackButton option.
            if(typeof(options.disableBackButton) !== "undefined"){
                if(options.disableBackButton === true){
                    popover.disableBackButton();
                }else if(options.disableBackButton === false){
                    popover.enableBackButton();
                }
            }

            if(typeof(options.enableBackButton) !== "undefined"){
                if(options.enableBackButton === true){
                    popover.enableBackButton();
                }else if(options.enableBackButton === false){
                    popover.disableBackButton();
                }
            }

            if(typeof(options.disableHeader) !== 'undefined'){
                if(options.disableHeader === true){
                    popover.disableHeader();
                }else if(options.disableHeader === false){
                    popover.enableHeader();
                }
            }

            if(typeof(options.keepData) !== 'undefined'){
                popover.keepData(options.keepData);
            }

            if(typeof(options.childToAppend) !== 'undefined'){
                popover.childToAppend = options.childToAppend;
            }

            //Callbacks
            if(typeof(options.onCreate) !== 'undefined'){
                popover._onCreate = options.onCreate;
            }

            if(typeof(options.onVisible) !== 'undefined'){
                popover._onVisible = options.onVisible;
            }

            Popover.addMenu(options.id, options.title, options.contents);
        },
        _popoverInit: function(options) {
            var popover = new Popover(this.selector);
            methods._init(options, popover);
            return popover;
        },
        _optionsPopoverInit: function (options) {
            var popover = new OptionsPopover(this.selector);
            methods._init(options, popover);
            return popover;
        },
        //Requires instance to be passed.
        disableHeader: function(popover) {
            popover.disableHeader();
        },
        //Requires instance to be passed.
        enableHeader: function(popover) {
            popover.enableHeader();
        },
        //Static functions
        lockPopover: function() {
            Popover.lockPopover();
        },
        unlockPopover: function() {
            Popover.unlockPopover();
        },
        addMenu: function (menu) {
            Popover.addMenu(menu.id, menu.title, menu.contents);
        },
        closePopover: function () {
            Popover.closePopover();
        },
        _getPopoverClass: function() {
            return Popover;
        }
    };

    $.fn.optionsPopover = function (method) {
        // Create some defaults, extending them with any options that were provided
        //var settings = $.extend({}, options);
        // Method calling logic
        if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods._optionsPopoverInit.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.optionsPopover');
        }

        return this.each(function () {});
    };

    $.fn.popover = function (method) {
        // Create some defaults, extending them with any options that were provided
        //var settings = $.extend({}, options);
        // Method calling logic
        if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods._popoverInit.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.popover');
        }

        return this.each(function () {});
    };

////////////////////////////////////////////////////////////
//          Popover Block
////////////////////////////////////////////////////////////
/**     Popover CONSTRUCTOR    **/
function Popover(popoverListener) {
    this.constructor = Popover;

    //Set this popover's number and increment Popover count.
    this.popoverNumber = ++Popover.popoverNum;
    //Class added to detect clicks on primary buttons triggering popovers.
    this.popoverListenerID = "popoverListener"+this.popoverNumber;
    this.isHeaderDisabled = true;
    this.isDataKept = false;
    this.hasBeenOpened = false;

    var thisPopover = this;
    var listenerElements = $(popoverListener);
    listenerElements.addClass(this.popoverListenerID);
    listenerElements.css("cursor", "pointer");
    listenerElements.click(function (e) {
        thisPopover.toggleVisible(e, $(this));
        $(document).trigger("popover.listenerClicked");
    });
}

Popover.prototype.disableHeader = function() {
    this.isHeaderDisabled = true;
};

Popover.prototype.enableHeader = function() {
    this.isHeaderDisabled = false;
};

Popover.prototype.disablePopover = function() {
    this.isDisabled = true;
};

Popover.prototype.enablePopover = function() {
    this.isDisabled = false;
};

Popover.prototype.keepData = function(bool){
    this.isDataKept = bool;
};

Popover.prototype.appendChild = function(){
    var child = this.childToAppend;
    if(!child)return;
    $("#popoverContent")[0].appendChild(child);
};

Popover.prototype.toggleVisible = function (e, clicked) {
    Popover.lastPopoverClicked = this;
    var clickedDiv = $(clicked);
    if (!clickedDiv) {
        console.log("ERROR: No element clicked!");
        return;
    }

    var popoverWrapperDiv = $("#popoverWrapper");
    if (popoverWrapperDiv.length === 0) {
        //console.log("Popover not initialized; initializing.");
        popoverWrapperDiv = this.createPopover();
        if (popoverWrapperDiv.length === 0) {
            console.log("ERROR: Failed to create Popover!");
            return;
        }
    }

    //TODO: In the future, add passed id to selected div's data-* or add specific class.
    var id = clickedDiv.attr("id");
    var identifierList = clickedDiv.attr('class').split(/\s+/);

    //NOTE: identifierList contains the clicked element's id and class names. This is used to find its
    //      associated menu. The next version will have a specialized field to indicate this.
    identifierList.push(id);
    //console.log("List: "+identifierList);

    //TODO: Fix repetition.
    if ($("#popover").is(":visible") && Popover.lastElementClick) {
        if (clickedDiv.is("#" + Popover.lastElementClick)) {
            console.log("Clicked on same element!");
            console.log("Last clicked: " + Popover.lastElementClick);
            Popover.closePopover();
            return;
        }
        console.log("Clicked on different element!");
        Popover.closePopover();
    }

    //Blocking statement that waits until popover closing animation is complete.
    $("#popover").promise().done(function () {});

    //If popover is locked, don't continue actions.
    if(Popover.isLocked||this.isDisabled)return;
    //Update content
    this.populate(identifierList);

    clickedDiv.trigger("popover.action", clickedDiv);

    if(Popover.backgroundColor){
        $("#popoverHeader").css("backgroundColor", Popover.backgroundColor);
        $("#popoverContent").css("backgroundColor", Popover.backgroundColor);
    }

    if(Popover.fontColor){
        $("#popover").css("color", Popover.fontColor);
        //TODO: Trigger color change event and move to OptionsPopover.
        $("#popover a").css("color", Popover.fontColor);
    }

    if(Popover.borderColor){
        $("#popoverHeader").css("border-color", Popover.borderColor);
        $("#popoverContent").css("border-color", Popover.borderColor);
        $(".popoverContentRow").css("border-color", Popover.borderColor);
    }

    //Make popover visible
    $("#popover").stop(false, true).fadeIn('fast');
    $("#popoverWrapper").css("visibility", "visible");
    $("#popover").promise().done(function () {});
    popoverWrapperDiv.trigger("popover.visible");

    if(this._onVisible){
        //console.log("LOG: Executing onVisible callback.");
        this._onVisible();
    }

    if((this.isDataKept && !this.hasBeenOpened) || (!this.isDataKept)){
        var child = this.childToAppend;
        if(child){
            this.appendChild(child);
        }
    }
    this.hasBeenOpened = true;

    //Update left, right and caret positions for popover.
    //NOTE: Must be called after popover.visible event, in order to trigger jspScrollPane update.
    Popover.updatePositions(clickedDiv);

    Popover.lastElementClick = clickedDiv.attr("id");
};

Popover.updatePositions = function(target){
    Popover.updateTopPosition(target);
    Popover.updateLeftPosition(target);
    $(document).trigger("popover.updatePositions");
};

Popover.updateTopPosition = function(target){
    var top = Popover.getTop(target);
    $("#popoverWrapper").css("padding-top", top + "px");
};

Popover.updateLeftPosition = function(target){
    var offset = Popover.getLeft(target);
    $("#popoverWrapper").css("left", offset.popoverLeft);
    Popover.setCaretPosition(offset.targetLeft - offset.popoverLeft + Popover.padding);
};


//Function returns the left offset of the popover and target element.
Popover.getLeft = function (target) {
    var popoverWrapperDiv = $("#popoverWrapper");
    Popover.currentTarget = target;
    var targetLeft = target.offset().left + target.outerWidth() / 2;
    var rightOffset = targetLeft + popoverWrapperDiv.outerWidth() / 2;
    var offset = targetLeft - popoverWrapperDiv.outerWidth() / 2 + Popover.padding + 1;
    var windowWidth = $(window).width();

    Popover.offScreenX = false;
    if (offset < 0) {
        Popover.offScreenX = true;
        offset = Popover.padding;
    } else if (rightOffset > windowWidth) {
        Popover.offScreenX = true;
        offset = windowWidth - popoverWrapperDiv.outerWidth();
    }

    //Returns left offset of popover from window.
    return {targetLeft: targetLeft, popoverLeft: offset};
};

Popover.getTop = function(target){
    var caretHeight =  $("#popoverArrow").height();
    //TODO: Make more readable.
    //If absolute position from mobile css, don't offset from scroll.
    var scrollTop = ($("#popoverWrapper").css("position")==="absolute")?0:$(window).scrollTop();
    var targetTop = target.offset().top - scrollTop;
    var targetBottom = targetTop + target.outerHeight();
    var popoverTop = targetBottom + caretHeight;
    var windowHeight = $(window).height();
    var popoverContentHeight = $("#popoverContent").height();
    var popoverHeight = popoverContentHeight + $("#popoverHeader").outerHeight() + caretHeight;

    Popover.above = false;
    Popover.offScreenY = false;

    //If popover is past the bottom of the screen.
    //else if popover is above the top of the screen.
    if (windowHeight < targetBottom + popoverHeight) {
        Popover.offScreenY = true;
        //If there is room above, move popover above target
        //else keep popover bottom at bottom of screen.
        if(targetTop >= popoverHeight){
            popoverTop = targetTop - popoverHeight;
            Popover.above = true;
        }else{
            popoverTop = windowHeight - popoverHeight;
        }
    } else if (popoverTop < 0) {
        Popover.offScreenY = true;
        popoverTop = Popover.padding + caretHeight;
    }

    /*
     //Debug logs
     console.log("------------------------------------------------------------");
     console.log("Caret Height: " + caretHeight);
     console.log("TargetTop: " + targetTop);
     console.log("Popover Cont Height: " + popoverContentHeight);
     console.log("Cont Height: " + $("#popoverContent").height());
     console.log("Header Height: " + $("#popoverHeader").outerHeight());
     console.log("targetBottom: " + targetBottom);
     console.log("popoverHeight: " + popoverHeight);
     console.log("popoverBottom: " + (targetBottom + popoverHeight));
     console.log("Popover Height: " + $("#popover").height());
     console.log("PopoverWrapper Height: " + $("#popoverWrapper").height());
     console.log("PopoverWrapper2 Height: " + $("#popoverWrapper").height(true));
     console.log("popoverTop: " + popoverTop);
     console.log("windowHeight: " + windowHeight);
     console.log("offScreenY: " + Popover.offScreenY);
     console.log("Popover.above: " + Popover.above);
     console.log("\n");
     */

    return popoverTop;
};

Popover.setCaretPosition = function(offset){
    //console.log("LOG: Setting caret position.");
    var caretPos = "50%";
    var caret = $("#popoverArrow");
    if (Popover.offScreenX) {
        caretPos = offset;
    }
    //Moves carrot on popover div.
    caret.css("left", caretPos);

    //console.log("LOG: Popover.above: "+Popover.above);
    if(Popover.above){
        var popoverHeight = $("#popoverContent").outerHeight() - 4;
        $("#popoverArrow").css("margin-top", popoverHeight+"px")
                          .addClass("flipArrow")
                          .html("▼");
    }else{
        $("#popoverArrow").css("margin-top", "")
                          .removeClass("flipArrow")
                          .html("▲");
    }
    Popover.caretLeftOffset = caretPos;
};

// createPopover: Prepends popover to dom
Popover.prototype.createPopover = function () {
    //Creates popover div that will be populated in the future.
    var popoverWrapperDiv = $(document.createElement("div"));
    popoverWrapperDiv.attr("id", "popoverWrapper");

    var s = "<div id='popover'>" +
                "<div id='popoverArrow'>▲</div>" +
                "<div id='currentPopoverAction' style='display: none;'></div>" +
                "<div id='popoverContentWrapper'>" +
                    "<div id='popoverContent'></div>" +
                "</div>" +
            "</div>";
    popoverWrapperDiv.html(s);
    popoverWrapperDiv.find("#popover").css("display", "none");

    //Appends created div to page.
    $("body").prepend(popoverWrapperDiv);

    //Window resize listener to check if popover is off screen.

    var popoverContentWrapperDiv = $("#popoverContentWrapper");
    $(window).on('resize', function () {
            if ($("#popover").is(":visible")) {
                Popover.updatePositions(Popover.currentTarget);
            }
            var popoverWrapperDiv = $("#popoverWrapper");
            if(popoverWrapperDiv.css("position")==="absolute"){
                popoverWrapperDiv.css("height", $(document).height());
            }else{
                popoverWrapperDiv.css("height", "");
            }
            popoverContentWrapperDiv.trigger("popover.resize");
        }
    );

    //Click listener to detect clicks outside of popover
    $('html')
        .on('click touchend', function (e) {
            var clicked = $(e.target);
            //TODO: Return if not visible.
            var popoverHeaderLen = clicked.parents("#popoverHeader").length + clicked.is("#popoverHeader") ? 1 : 0;
            var popoverContentLen = (clicked.parents("#popoverContentWrapper").length && !clicked.parent().is("#popoverContentWrapper")) ? 1 : 0;
            var isListener = clicked.parents("."+Popover.lastPopoverClicked.popoverListenerID).length + clicked.is("."+Popover.lastPopoverClicked.popoverListenerID) ? 1 : 0;
            if (popoverHeaderLen === 0 && popoverContentLen === 0 && isListener === 0) {
                Popover.closePopover();
            }
        }
    );

    popoverContentWrapperDiv.trigger("popover.created");
    if(this._onCreate)this._onCreate();

    //Function also returns the popover div for ease of use.
    return popoverWrapperDiv;
};

//Closes the popover
Popover.closePopover = function () {
    if(Popover.isLocked)return;
    Popover.lastElementClick = null;

    $(document).trigger("popover.closing");
    Popover.history = [];
    $("#popover").stop(false, true).fadeOut('fast');
    $("#popoverWrapper").css("visibility", "hidden");
};

Popover.getAction = function () {
    return $("#currentPopoverAction").html();
};

Popover.setAction = function (id) {
    $("#currentPopoverAction").html(id);
};

Popover.prototype.disableBackButton = function(){
    this.isBackEnabled = false;
};

Popover.prototype.enableBackButton = function(){
    this.isBackEnabled = true;
};

Popover.prototype.previousPopover = function(){
    Popover.history.pop();
    if (Popover.history.length <= 0) {
        Popover.closePopover();
        return;
    }
    var menu = Popover.history[Popover.history.length - 1];
    this.populateByMenu(menu);
};

//Public setter function for static var title and sets title of the html popover element.
Popover.setTitle = function (t) {
    Popover.title = t;
    $("#popoverTitle").html(t);
};

// Public getter function that returns a popover menu.
// Returns: Popover menu object if found, null if not.
// Arguments:   id - id of menu to lookup
Popover.getMenu = function (id) {
    //Searches for a popover data object by the id passed, returns data object if found.
    var i;
    for (i = 0; i < Popover.menus.length; i += 1) {
        //console.log("LOG: getMenu - Popover.menus["+i+"]: "+Popover.menus[i].id);
        if (Popover.menus[i].id === id) {
            return Popover.menus[i];
        }
    }

    //Null result returned if popover data object is not found.
    //console.log("LOG: getMenu - No data found, returning null.");
    return null;
};

Popover.addMenu = function (id, title, contents) {
    Popover.menus.push({'id': id, 'title': title, 'contents': contents});
};

Popover.prototype.populateByMenu = function(menu){
    $(document).trigger('popover.populating');

    this.lastContentHeight = Popover.getPopoverContentHeight();

    if(!this.isDataKept){
        this.clearData();
    }

    //If data is kept, header and other content will still be in dom, so don't do either.
    if(!this.isHeaderDisabled && !this.isDataKept) {
        this.insertHeader();
    }else{
        this.removeHeader();
    }

    var popoverDisplay = $("#popover").css("display");

    if(!this.isDataKept || !this.hasBeenOpened)this.setData(menu);

    this.currentContentHeight = Popover.getPopoverContentHeight();

    if(Popover.above && popoverDisplay!=="none"){
        var oldPopoverTop = parseInt($("#popoverWrapper").css("padding-top"), 10);
        var contentHeightDelta = this.currentContentHeight - this.lastContentHeight;
        var popoverTop = oldPopoverTop - (contentHeightDelta);
        $("#popoverWrapper").css("padding-top", popoverTop + "px");
        Popover.setCaretPosition(Popover.caretLeftOffset);
    }

    return true;
};

//Public void function that populates setTitle and setContent with data found by id passed.
Popover.prototype.populate = function(identifierList){
    //console.log(identifierList);
    var newMenu = null;
    var i=0;
    for(i; i<identifierList.length; i++){
        newMenu = Popover.getMenu(identifierList[i]);
        if(newMenu){
            //console.log("Found menu! id: "+identifierList[i]);
            break;
        }
    }

    if (!newMenu) {
        console.log("ID not found.");
        return false;
    }

    Popover.history.push(newMenu);
    return this.populateByMenu(newMenu);
};

Popover.getPopoverContentHeight = function(){
    var popoverDisplay = $("#popover").css("display");
    $("#popover").show();
    var popoverHeight = $("#popoverContent").height();
    $("#popover").css("display",popoverDisplay);
    return popoverHeight;
};

Popover.prototype.insertHeader = function (){
    var header = "<div id='popoverHeader'>" +
                    "<div id='popoverTitle'></div>" +
                    "<a id='popoverClose'><span id='popoverCloseIcon'>✕</span></a>" +
                 "</div>";

    $("#popoverContentWrapper").before(header);

    //Create back button
    //Don't create back button or listener if disabled.
    if(this.isBackEnabled){
        //console.log("LOG: Creating back button.");
        var thisPopover = this;
        $("#popoverHeader").prepend("<a id='popoverBack'><span id='popoverBackIcon'>&#x25C0;</span></a>");
        $("#popoverBack").on("click", function () {
            thisPopover.previousPopover();
        });
    }

    //Click listener for popover close button.
    $("#popoverClose").on("click", function () {
        Popover.closePopover();
    });

    $("#popoverContent").css("paddingTop", "47px");
};

Popover.prototype.removeHeader = function() {
    $("#popoverBack").off("click");
    $("#popoverClose").off("click");
    $("#popoverHeader").remove();
    $("#popoverContent").css("paddingTop", "");
};

Popover.prototype.clearData = function (){
    this.removeHeader();

    $("#popoverTitle").html("");
    $("#popoverContent").html("");
};

Popover.prototype.setData = function (data) {
    Popover.setAction(data.id);
    Popover.setTitle(data.title);
    Popover.setContent(data.contents);
};

Popover.prototype.replaceMenu = function (menu, newMenu){
    var property;
    for(property in menu){
        delete menu[property];
    }
    for(property in newMenu){
        menu[property] = newMenu[property];
    }
};

//Public setter function for private var content and sets content of the html popover element.
Popover.setContent = function (cont) {
    Popover.content = cont;
    //$("#popoverContentWrapper").data('jsp').getContentPane().find("#popoverContent").html(cont);
    //Note: Popover content set without using jscrollpane api.
    $("#popoverContent").html(cont);
    //Note: Removed 'this' reference passed.
    $("#popoverContentWrapper").trigger("popover.setContent");
};

/**     STATIC VARIABLES     **/
Popover.popoverNum = 0;
Popover.lastElementClick = null;
Popover.currentTarget = null;
Popover.title = "";
Popover.content = "";
Popover.menus = [];
Popover.history = [];
Popover.backgroundColor = null;
Popover.fontColor = null;
Popover.borderColor = null;
Popover.padding = 3;
Popover.offScreenX = false;
Popover.offScreenY = false;
Popover.isLocked = false;
Popover.above = false;
Popover.caretLeftOffset = "50%";
Popover.lastPopoverClicked = null;

/**     STATIC FUNCTIONS     **/
Popover.setBackgroundColor = function(color){
    Popover.backgroundColor = color;
};

Popover.setFontColor = function(color){
    Popover.fontColor = color;
};

Popover.setBorderColor = function(color){
    Popover.borderColor = color;
};

Popover.lockPopover = function(){
    Popover.isLocked = true;
};

Popover.unlockPopover = function(){
    Popover.isLocked = false;
};

////////////////////////////////////////////////////////////
//          OptionsPopover Block
////////////////////////////////////////////////////////////

/**   OptionsPopover CONSTRUCTOR  **/
function OptionsPopover(popoverListener){
    //Super constructor call.
    Popover.apply(this, [popoverListener]);
    this.constructor = OptionsPopover;
    this.superConstructor = Popover;

    this.isHeaderDisabled = false;
    this.isBackEnabled = true;

    if(!OptionsPopover.hasRun){
        this.init();
        OptionsPopover.hasRun = true;
    }
}
//Inherit Popover
OptionsPopover.prototype = new Popover();
OptionsPopover.constructor = OptionsPopover;

/**     STATIC VARIABLES        **/
OptionsPopover.hasRun = false;

/**     PROTOTYPE FUNCTIONS     **/
//Run-once function for listeners
OptionsPopover.prototype.init = function(){
    $(document)
        .on('touchstart mousedown', '#popover a',
        function () {
            $(this).css({backgroundColor: "#488FCD"});
        })
        .on('touchend mouseup mouseout', '#popover a',
        function () {
            $(this).css({backgroundColor: ""});
        })
        .on('click', '.popoverContentRow',
        function () {
            var newId = [];
            newId.push($(this).attr('id'));

            if ($(this).hasClass("popoverEvent")) {
                $(this).trigger("popover.action", $(this));
            }

            var keepOpen = Popover.lastPopoverClicked.populate(newId);
            if (!keepOpen) Popover.closePopover();
        });
};

OptionsPopover.prototype.setData = function (data) {
    var contArray = data.contents;
    var c = "";
    var i;

    for (i = 0; i < contArray.length; i++) {
        var lastElement = "";
        var popoverEvent = "";
        var menuId = "";
        var menuUrl = "";
        if (i === contArray.length - 1) {
            lastElement = " last";
        }

        //Links are given the popoverEvent class if no url passed. If link has popoverEvent,
        // event is fired based on currentPopoverAction.
        if (typeof(contArray[i].id) !== 'undefined') {
            menuId = " id='" + contArray[i].id + "'";
        }

        if (typeof(contArray[i].url) !== 'undefined') {
            menuUrl = " href='" + contArray[i].url + "'";
        } else {
            popoverEvent = " popoverEvent";
        }

        c += "<a" + menuUrl + menuId + " class='popoverContentRow" + popoverEvent + lastElement + "'>" +
            contArray[i].name +
            "</a>";
    }

    Popover.setAction(data.id);
    Popover.setTitle(data.title);
    Popover.setContent(c);
};
}(window.jQuery);