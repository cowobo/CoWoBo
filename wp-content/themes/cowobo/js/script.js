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

    jQuery(".tab span.close").click( function() {
        jQuery(this).parent('.tab').fadeOut();
    })

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
    var pagesource = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' + document.documentElement.outerHTML;
    jQuery('div.pagesource div.code').html(
        pagesource.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/(\r\n|\n|\r)/gm," ")
    );
    jQuery('div.pagesource div.code').litelighter();


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

jQuery('.searchform').live('click', function(e){
	e.preventDefault();
	jQuery('.dropmenu').slideDown();
});

jQuery('.closebutton').live('click', function(event){
	event.stopPropagation();
	jQuery('.dropmenu').slideUp();
});


jQuery('.resizeicon').live('click', function(event){
	event.stopPropagation();
	//to do add function so map doesnt fall of page
	jQuery('.planet').animate({marginTop: 0}, 1000);
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

/*
 * jQuery Litelighter (modified) // Please leave it in, it's only 3.1KB!
 * By: Trent Richardson [http://trentrichardson.com]
 *
 * Copyright 2012 Trent Richardson
 * Dual licensed under the MIT or GPL licenses.
 * http://trentrichardson.com/Impromptu/GPL-LICENSE.txt
 * http://trentrichardson.com/Impromptu/MIT-LICENSE.txt
 */
(function(e){e.litelighter=function(t,n){this.settings=e.extend({},{clone:false,style:"light",language:"html",tab:"    "},n);this.code=t;this.enable()};e.extend(e.litelighter.prototype,{enable:function(){this.codelite=this.code.data("llcode",this.code.text());if(this.settings.clone==true)this.codelite=e("<div />").text(this.code.text()).addClass("litelighter").insertAfter(this.code.css("display","none"));var t=e.litelighter.styles[this.settings.style],n=e.litelighter.languages[this.settings.language],r=e.litelighter.highlight(this.codelite.html(),t,n).replace(/\t/g,this.settings.tab);this.codelite.attr("style",t.code).html(r);return this.code},disable:function(){if(this.settings.clone){this.codelite.remove();return this.code.css("display","block")}return this.code.html("").text(this.code.data("llcode"))},destroy:function(){this.disable();return this.code.removeData("litelighter")},option:function(e,t){if(t!==undefined){this.code.data("ll"+e,t);this.settings[e]=t;this.disable();return this.enable()}return this[e]}});e.fn.extend({litelighter:function(t){t=t||{};var n=Array.prototype.slice.call(arguments);if(typeof t=="string")return this.each(function(){var r=e(this).data("litelighter");r[t].apply(r,n.slice(1))});else return this.each(function(){var n=e(this);n.data("litelighter",new e.litelighter(n,t))})}});e.litelighter.highlight=function(t,n,r){var i=0,s=[];for(var o in r){if(r.hasOwnProperty(o)&&r[o].language!==undefined&&e.litelighter.languages[r[o].language]!==undefined){t=t.replace(r[o].re,function(t,u,a){s[i++]=e.litelighter.highlight(u,n,e.litelighter.languages[r[o].language]);return t.replace(u,"___subtmpl"+(i-1)+"___")})}}for(var o in r){if(r.hasOwnProperty(o)&&r[o].language===undefined){t=t.replace(r[o].re,"___"+o+"___$1___end"+o+"___")}}var u=[];t=t.replace(/___(?!subtmpl)\w+?___/g,function(t){var n=t.substr(3,3)=="end"?true:false,i=(!n?t.substr(3):t.substr(6)).replace(/_/g,""),s=u.length>0?u[u.length-1]:null;if(!n&&(s==null||i==s||s!=null&&r[s].embed!=undefined&&e.inArray(i,r[s].embed)>=0)){u.push(i);return t}else if(n&&i==s){u.pop();return t}return""});for(var o in r){if(r.hasOwnProperty(o)){t=t.replace(new RegExp("___end"+o+"___","g"),"</span>").replace(new RegExp("___"+o+"___","g"),"<span class='litelighterstyle' style='"+n[r[o].style]+"'>")}}for(var o in r){if(r.hasOwnProperty(o)&&r[o].language!==undefined&&e.litelighter.languages[r[o].language]!==undefined){t=t.replace(/___subtmpl\d+___/g,function(e){var t=parseInt(e.replace(/___subtmpl(\d+)___/,"$1"),10);return s[t]})}}return t};e.litelighter.styles={light:{code:"background-color:#ffffff;color:#555;",comment:"color:#999",string:"color:#8F9657",number:"color:#CF6745;",keyword:"color:#6F87A8;",operators:"color:#9e771e;"}};e.litelighter.languages={html:{comment:{re:/(\&lt\;\!\-\-([\s\S]*?)\-\-\&gt\;)/g,style:"comment"},tag:{re:/(\&lt\;\/?\w(.|\n)*?\/?\&gt\;)/g,style:"keyword",embed:["string"]},string:{re:/((\'.*?\')|(\".*?\"))/g,style:"string"},css:{re:/(?:\<style.*?\>)([\s\S]+?)(?:\<\/style\>)/gi,language:"css"},script:{re:/(?:\<script.*?\>)([\s\S]+?)(?:\<\/script\>)/gi,language:"js"}}}})(jQuery);