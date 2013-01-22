//VARIABLES//
var overscroller;
var rooturl;
var data = {'lat':0, 'lng':40, 'zoom':3, 'type':'sat'}
var animate = false;
var slideshow;

//setup mouselisterner on document load
jQuery(document).ready(function() {
	rooturl = jQuery('meta[name=rooturl]').attr("content");

	//update global vars of map
	var newdata = jQuery('.mapdata').val();
	if(typeof(newdata) !='undefined'){
		newdata = newdata.split('*');
		data = {'lat':newdata[0], 'lng':newdata[1], 'zoom':newdata[2], 'type': newdata[3]};

	}

	//cross browser check if page has finished translating
	if(jQuery('.translating').length>0){
		var title = jQuery(".description");
		title.data('original', title.text());
		var checktitle = setInterval(function(){
			if(title.data('original') != title.text()) {
				jQuery('.feeds').fadeIn();
				jQuery('.translating').fadeOut();
				clearInterval(checktitle);
			}
		}, 500);
	} else {
		jQuery('.feeds').fadeIn();
	}

	if(jQuery('.translating').length>0){
		var title = jQuery(".description");
		title.data('original', title.text());
		var checktitle = setInterval(function(){
			if(title.data('original') != title.text()) {
				jQuery('.feeds').fadeIn();
				jQuery('.translating').fadeOut();
				clearInterval(checktitle);
			}
		}, 500);
	} else {
		jQuery('.feeds').fadeIn();
	}

    // Avatar uploads
    jQuery( ".upload-avatar-link").click( function(e) {
        e.preventDefault();
        jQuery(".upload-avatar").slideToggle();
    });

	//Enable Map Resizing and Panning
	var pan = false; var drag = false; var previousX; var previousY;

	jQuery(".planet").mousedown(function(e) {
		jQuery('body').addClass('unselectable');
	    previousX = e.clientX;
	    previousY = e.clientY;
	    pan = true;
	});

	jQuery(".dragbar").mousedown(function(e) {
		jQuery('body').addClass('unselectable');
	    previousY = e.clientY;
	    drag = true;
	});

	jQuery("body").mousemove(function(e) {

		if (pan) {
	        var slide = jQuery(".slide:last");
			var slidepos = slide.position();
			var xmax = jQuery('.planet').width()-slide.width();
			var ymax = jQuery('.planet').height()-slide.height();
			var ymin = -parseFloat(jQuery('.planet').css('margin-top'));
			var newx = slidepos.left - (previousX - e.clientX);
			var newy = slidepos.top - (previousY - e.clientY) ;
			if (jQuery.browser.msie) jQuery('div').attr('unselectable', 'on');
			if(slide.find('.marker').length > 0) slide = jQuery(".slide:last, .markerlinks");
			if(newx > 0) newx = 0;
			if(newx < xmax) newx = xmax;
			if(newy > ymin) newy = ymin;
			if(newy < ymax) newy = ymax;
			slide.css({top: newy, left: newx});
	        previousX = e.clientX;
	        previousY = e.clientY;
	    }

		if (drag) {
	        var planet = jQuery(".planet");
			var planetpos = parseFloat(planet.css('margin-top'));
			var slide = jQuery('.slide:last');
			var slidepos = slide.position();
			var mousemove = previousY - e.clientY;
			var newy = planetpos - mousemove;
			var ymax = planet.height() - slide.height();
			var overlap = slidepos.top - ymax;
			var newtop = slidepos.top + mousemove;
			if(overlap < 0) newtop = ymax;
			if(newy > 0) newy = 0;
			if(newy < -300) {
				newy = -300;
				newtop = slidepos.top;
			}
			planet.css('margin-top', newy);
			slide.css('top', newtop);
	        previousY = e.clientY;
			
			
			
			
			
	    }
	});

	//disable panning and dragging on mouse up
	jQuery(document).mouseup(function() {
	    pan = false; drag = false;
		jQuery('body').removeClass('unselectable');
	});

    // Is So Meta (Even The Acronym)!
    jQuery('div.pagesource pre').html(
        document.documentElement.outerHTML.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    );

});

//IMAGE VIEWER FUNCTIONS//
jQuery('.zoom, .pan, .labels').live('click', function(event){
	event.preventDefault();
	var action = jQuery(this).attr('class').split(' ')[1];
	var slide = jQuery('.slide:last');
	var slideimg = jQuery('.slide .mapimg');
	var slidepos = slide.position();
	var xmax = jQuery('.planet').width()-slide.width();
	var ymax = jQuery('.planet').height()-slide.height();
	var amount; var newstyle;

	if(action == 'labels') {
		//to do: change day/night maptype
	} else if(action == 'panleft') {
		if(slidepos.left < -300) amount = slidepos.left + 300; else amount = 0;
		newstyle = {left: amount}
	} else if(action == 'panright') {
		if(slidepos.left > xmax + 300) amount = slidepos.left - 300; else amount = xmax;
		newstyle = {left: amount}
	} else if(action == 'panup') {
		if(slidepos.top < -200) amount = slidepos.top + 200; else amount = 0;
		newstyle = {top: amount}
	} else if(action == 'pandown') {
		if(slidepos.top > ymax + 200) amount = slidepos.top - 200; else amount = ymax;
		newstyle = {top: amount}
	} else if(action == 'zoomin') {
		var curzoom = parseFloat(slide[0].style.width.split('%')[0]);
		if(curzoom < 400) {
			var newzoom = curzoom * 2 + '%';
			var newtop = slidepos.top - (slide.height()/2) + 'px';
			var newleft = slidepos.left - (slide.width()/2) + 'px';
			var imgname = slideimg.attr('src').split('_')[0];
			newstyle = {width:newzoom, height:newzoom, top:newtop, left:newleft};
			slideimg.clone().appendTo(slide).attr('src', imgname + '_3.jpg');
		}
	} else if(action ==  'zoomout') {
		newstyle = {width:"100%", height:"100%", top:"0", left:"0"};
	}

	//if slide contains markers than animate that too
	if(slide.find('.marker').length > 0) slide = jQuery(".slide:last, .markerlinks");
	slide.animate(newstyle, 2000);
});


//expand map to max function
function expand_map() {
    return null;
}

//Search form listerners
jQuery('.dropmenu input').live('click', function(event){
	event.stopPropagation();
	jQuery(this).parent().toggleClass('checked');
});

jQuery('.dropmenu span').live('click', function(event){
	var checkbox = jQuery(this).children('input');
	var type = jQuery(this).parents('.dropmenu').attr('id');
	checkbox.prop("checked", !checkbox.prop("checked"));
	jQuery(this).toggleClass('checked');
});

jQuery('.searchform').live('click', function(){
	jQuery('.dropmenu').slideDown();
});

jQuery('.closebutton').live('click', function(event){
	event.stopPropagation();
	jQuery('.dropmenu').slideUp();
});


/// TAB FUNCTIONS ///
jQuery(document).ready(function($) {
    $(".tab span.close").click( function() {
        $(this).parent('.tab').fadeOut();
    })
});

//FEED FUNCTIONS//

//show map
jQuery('.showmap').live('click', function(event) {
	jQuery('.feedlinks').animate({marginBottom: jQuery('.planet').height()-100}, 1000);
	jQuery(this).attr('class', 'hidemap').html('Hide Map');
	event.preventDefault();
});

//hidemap
jQuery('.hidemap').live('click', function(event) {
	jQuery('.feedlinks').animate({marginBottom: 30}, 1000);
	jQuery(this).attr('class', 'showmap').html('Show Map');;
	event.preventDefault();
});

//show specific slide in gallery
jQuery('.fourths a').live('click', function(event) {
	var num = jQuery(this).index();
	var slide = jQuery('#slide-'+num);
	event.preventDefault();
	if(num == 0) jQuery('.markerlinks').show();
	else jQuery('.markerlinks').hide();
	slide.hide().appendTo(jQuery('.planet')).fadeIn(1000);
});


//TEXT EDITOR FUNCTIONS

jQuery('#formsubmit').live('click', function(e){
	jQuery('.htmlbox').val(jQuery('#rte').html());
});

jQuery('.htmlmode').live('click', function(e){
	e.preventDefault();
	jQuery('.htmlbox').val(jQuery('#rte').html());
	jQuery('#rte, .htmlbox, .htmlmode, .richmode').toggle();
});

jQuery('.richmode').live('click', function(e){
	e.preventDefault();
 	jQuery('#rte').html(jQuery(".htmlbox").val());
	jQuery('#rte, .htmlbox, .htmlmode, .richmode').toggle();
});

jQuery('.makebold').live('click', function(e){
	document.execCommand('bold', false, null);
	jQuery('#rte').focus();return false;
});

jQuery('.makeitalic').live('click', function(e){
 	document.execCommand('italic', false, null);
	jQuery('#rte').focus();return false;
});

jQuery('.makeunderline').live('click', function(e){
	document.execCommand('underline', false, null);
	jQuery('#rte').focus();return false;
});

jQuery('.makelink').live('click', function(e){
	e.preventDefault();
	var selection = getInputSelection();
	var selectval = String(selection);
	if(selectval.length > 0) {
		var value = prompt("Enter a url:", "");
    	if (value != null) document.execCommand("CreateLink", false, value);
    } else {
		alert('Select the text you wish to turn into a link');
	}
});

//cross browser check for selection
function getInputSelection() {
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            return sel.getRangeAt(0);
        }
    } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
    }
    return null;
}