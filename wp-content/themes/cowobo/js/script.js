//VARIABLES//
var rooturl;
var pan = false;
var drag = false;
var previousX;
var previousY;
var offset = 0;


//setup mouselisterner on document load
jQuery(document).ready(function() {

	rooturl = jQuery('meta[name=rooturl]').attr("content");

    // Avatar uploads
    jQuery( ".upload-avatar-link").click( function(e) {
        e.preventDefault();
        jQuery(".upload-avatar, .current-user-after-avatar").slideToggle();
    });

	//center all images in current header height
	center_images(offset);

	//Enable Map Resizing and Panning
	jQuery(".imageviewer").mousedown(function(e) {
		e.preventDefault();
		jQuery('body').addClass('unselectable');
	    previousX = e.clientX;
	    previousY = e.clientY;
	    pan = true;
	});

	jQuery(".dragbar").mousedown(function(e) {
		e.preventDefault();
		get_offset();
		jQuery('body').addClass('unselectable');
	    previousY = e.clientY;
	    drag = true;
	});

	jQuery("body").mousemove(function(e) {
		var viewer = jQuery(".imageviewer");
		var viewheight = viewer.height();

		if (pan) {
	        var slide = jQuery(".slide:last");
			var slidepos = slide.position();
			var xmax = jQuery(window).width() - slide.width();
			var ymax = viewheight - slide.height();
			var newx = slidepos.left - (previousX - e.clientX);
			var newy = slidepos.top - (previousY - e.clientY) ;
			if (jQuery.browser.msie) jQuery('div').attr('unselectable', 'on');
			if(ymax > 0) ymax =0;
			if(newx > 0) newx = 0;
			if(newx < xmax) newx = xmax;
			if(newy > 0) newy = 0;
			if(newy < ymax) newy = ymax;
			slide.css({top: newy, left: newx});
	        previousX = e.clientX;
	        previousY = e.clientY;
	    }

		if (drag) {
			var mousemove = previousY - e.clientY;
			var newy = viewheight - mousemove;
			var ymax = jQuery('.imageholder').height();
			var ymin = jQuery(window).height() - ymax - jQuery('.page').height();
			if(newy > ymax) newy = ymax;
			if(newy < ymin) newy = ymin;
			viewer.height(newy);
	        previousY = e.clientY;
			center_images(offset);
	    }
	});

	//disable panning and dragging on mouse up
	jQuery(document).mouseup(function() {
	    pan = false; drag = false;
		jQuery('body').removeClass('unselectable');
	});


});

//IMAGE VIEWER FUNCTIONS//
jQuery('.zoom, .pan, .labels').live('click', function(event){
	event.preventDefault();
	var action = jQuery(this).attr('class').split(' ')[1];
	var slide = jQuery('.slide:last');
	var slidepos = slide.position();
	var slideimg = slide.children('.slideimg');
	var viewheight = jQuery('.imageviewer').height();
	var curzoom = parseFloat(slide.children('.zoomlevel').val());
	var xmax = jQuery('.planet').width() - slide.width();
	var ymax = viewheight - slide.height();
	if(ymax > 0) ymax =0;
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
	} else if(action == 'zoomin' && curzoom < 2) {
		var newlevel = curzoom + 1;
		var newzoom = newlevel * 200 + '%';
		var newtop = slidepos.top - (slide.height()/2) + 'px';
		var newleft = slidepos.left - (slide.width()/2) + 'px';
		var newsrc = jQuery('.zoomsrc'+newlevel).val();
		newstyle = {width:newzoom, height:newzoom, top:newtop, left:newleft}
		slide.children('.zoomlevel').val(newlevel);

		//load larger image if available
		if(typeof(newsrc) != 'undefined' && newsrc.length > 0) {
			newimg = slideimg.clone();
			newimg.appendTo(slide).attr('src', newsrc);
			var oldimgs = slide.children('.slideimg').not(newimg);
			if(newimg.complete) newimg.load(function() {oldimgs.remove()});
			else newimg.load(function() {oldimgs.remove()});
		}
	} else if(action ==  'zoomout' && curzoom > 0) {
		var newlevel = curzoom - 1
		slide.children('.zoomlevel').val(newlevel);
		newstyle = {width:"100%", height:"100%", top:"0", left:"0"};
	}

	//animate slide
	slide.animate(newstyle, 2000);
});


//expand map to max function
function expand_map() {
    return null;
}

function get_offset() {
	var slide = jQuery('.slide:last');
	var currpos = slide.position();
	var viewheight = jQuery('.imageviewer').height();
	var ycenter = (viewheight - slide.height()) / 2;
	offset = currpos.top - ycenter;
}

function center_images(offset) {
	jQuery('.slide').each(function(){
		var viewheight = jQuery('.imageviewer').height();
		if(jQuery(this).children('.mapimg').length > 0 && offset == 0) {
			//NOTHING
		} else {
			var newy = (viewheight - jQuery(this).height()) / 2;
			var newy = newy + offset;
			var ymax = viewheight - jQuery(this).height();
			if(newy > 0) newy = 0;
			if(newy < ymax) newy = ymax;
			jQuery(this).css({top: newy});
		}
	});
}


//SEARCH

jQuery('.searchbar li').live('click', function(e){
	e.preventDefault();
	var menu = jQuery('.' + jQuery(this).attr('id'))
	menu.slideToggle();
	jQuery('.dropmenu').not(menu).slideUp();
});

jQuery('.searchform span input').live('click', function(event){
	event.stopPropagation();
	jQuery(this).parent().toggleClass('checked');
});

jQuery('.searchform span').live('click', function(event){
	var checkbox = jQuery(this).children('input');
	var type = jQuery(this).parents('.dropmenu').attr('id');
	checkbox.prop("checked", !checkbox.prop("checked"));
	jQuery(this).toggleClass('checked');
});


/// TABS ///
jQuery(document).ready(function($) {
    $(".tab span.close").click( function() {
        $(this).parent('.tab').fadeOut();
    })
});

jQuery(".tab span.close").live('click', function(){
   jQuery(this).parent('.tab').fadeOut();
})

//GALLERY//

//show specific slide in gallery
jQuery('.smallthumbs a').live('click', function(event) {
	var num = jQuery(this).index();
	var slide = jQuery('#slide-'+num);
	event.preventDefault();
	slide.hide().appendTo(jQuery('.imageholder')).fadeIn(1000);
});


jQuery('.resizeicon').live('click', function(event){
	event.stopPropagation();
	var slide = jQuery('.slide:last');
	var viewer = jQuery('.imageviewer');
	var holderheight = jQuery('.imageholder').height();
	var viewheight = viewer.height();
	if(viewheight < holderheight) var amount = holderheight;
	else var amount = holderheight / 2;
	viewer.animate({height: amount}, 1000);
	jQuery('html, body').animate({scrollTop: 0}, 1000);
	jQuery('.slide').each(function(){
		var newtop = (amount - slide.height()) / 2 ;
		jQuery(this).animate({top: newtop}, 1000);
	});
});

//start slideshow
function startslideshow() {
	if(jQuery('.slide').length>1){
		slideshow = setInterval(function(){
			var newnum = jQuery('.slide:visible').length;
			if(newnum == jQuery('.slide').length) newnum = 0;
			var newslide = jQuery('.slide').eq(newnum);
			var newcaption = jQuery('.caption').eq(newnum);

			//fade in new slide
			if(newslide.is(':visible')) newslide.nextAll('.slide').fadeOut(2000);
			else newslide.fadeIn(2000);

			//update caption
			newcaption.fadeIn(2000).siblings().fadeOut(2000);

		}, 4000);
	}
}

//TEXT EDITOR FUNCTIONS

jQuery('.submitform').live('click', function(e){
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