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
	jQuery('.feed').animate({marginTop: jQuery('.planet').height()-60}, 1000);
	jQuery(this).attr('class', 'hidemap').html('Hide Map');
	event.preventDefault();
});

//hidemap
jQuery('.hidemap').live('click', function(event) {
	jQuery('.feed').animate({marginTop: 20}, 1000);
	jQuery(this).attr('class', 'showmap').html('Show Map');;
	event.preventDefault();
});

//show specific slide in gallery
jQuery('.galthumbs a').live('click', function(event) {
	var oldnum = jQuery('.gallery .slide:last').attr('id').split('-')[1];
	var oldthumb = jQuery('.galthumbs a').eq(oldnum);
	var newnum = jQuery(this).index();
	event.preventDefault();
	if(jQuery(this).children('img').length>0){
		jQuery('#slide-'+newnum).hide().appendTo(jQuery('.gallery')).fadeIn(1000);
		jQuery(this).hide();
		oldthumb.show();
	}
});


//MAP FUNCTIONS//

var offset = 268435456; // center of google map in pixels at max zoom level
function LonToX(lon) {
	return Math.round(offset + (offset / Math.PI) * lon * Math.PI / 180);
}
function LatToY(lat) {
	return Math.round(offset - (offset / Math.PI) * Math.log((1 + Math.sin(lat * Math.PI / 180)) / (1 - Math.sin(lat * Math.PI / 180))) / 2);
}
function XToLon(x) {
	return ((Math.round(x) - offset) / (offset / Math.PI)) * 180/ Math.PI;
}
function YToLat(y) {
	return (Math.PI / 2 - 2 * Math.atan(Math.exp((Math.round(y) - offset) / (offset/ Math.PI)))) * 180 / Math.PI;
}
function adjustLonByPx(lon, amount, zoom) {
	var newlon = XToLon(LonToX(lon) + (amount << (21 - zoom)));
	if (newlon < -180) newlon = 360 + newlon;
	else if (newlon > 180) newlon = newlon - 360;
	return newlon;
}
function adjustLatByPx(lat, amount, zoom) {
	var newlat = YToLat(LatToY(lat) + (amount << (21 - zoom)));
	if (newlat < -90) newlat = 180 + newlat;
	else if (newlat > 90) newlat = newlat - 180;
	return newlat;
}

jQuery('.zoom, .pan, .labels').live('click', function(event){
	event.preventDefault();
	clearInterval(slideshow);
	jQuery('.pauseshow').hide().next().show();

	var xmid = 500; var ymid = 250;
	var action = jQuery(this).attr('class').split(' ')[1];

	//only start animating new map if the old map has finished loading
	if(animate == false){
		//update global parameters
		if(action == 'labels') {
			var newstyle = 'labels';
			if(data.type == 'hyb'){
				data.type = 'sat';
				jQuery(this).removeClass('grey');
			} else {
				data.type = 'hyb';
				jQuery(this).addClass('grey');
			}
		} else if(action == 'panleft') {
			data.lng = adjustLonByPx(data.lng, -xmid, data.zoom);
			var newstyle = {left:'25%'}
		} else if(action == 'panright') {
			data.lng = adjustLonByPx(data.lng, xmid, data.zoom);
			var newstyle = {left:"-25%"};
		} else if(action == 'panup') {
			data.lat = adjustLatByPx(data.lat, -ymid, data.zoom);
			var newstyle = {top:'25%'};
		} else if(action == 'pandown') {
			data.lat = adjustLatByPx(data.lat, ymid, data.zoom);
			var newstyle = {top:"-25%"};
		} else if(action == 'zoomin') {
			if(data.zoom < 15) {
				data.zoom = parseFloat(data.zoom)+1;
				var newstyle = {width:"200%", height:"200%", top:"-50%", left:"-50%"};
			} else {
				var newstyle = '';
			}
		} else if(action ==  'zoomout') {
			if(data.zoom > 3) {
				data.zoom = parseFloat(data.zoom)-1;
				var newstyle = {width:"50%", height:"50%", top:"25%", left:"25%"};
			} else {
				var newstyle = '';
			}
		}

		if(newstyle != '') loadnewmap(newstyle);
	}
});

function loadnewmap(newstyle) {
	var xmid = 500; var ymid = 250;
	var currlayer = jQuery('.maplayer:first');
	var path = '';
	animate = true;
	if(typeof(data.path)!= 'undefined') path ='&path=weight:2%7Ccolor:0xffffffff%7Cenc:'+data.path;

	if(data.zoom<10){
		var mappath = 'http://platform.beta.mapquest.com/staticmap/v4/getmap?key=Kmjtd|luua2qu7n9,7a=o5-lzbgq&type='+data.type+'&scalebar=false&size=1000,500';
	} else {
		if(data.type = 'sat') var maptype = 'satellite'; else if(data.type = 'hyb') var maptype = 'hybrid';
		var mappath = 'http://maps.googleapis.com/maps/api/staticmap?maptype='+maptype+'&sensor=false&format=jpg&size=1000x500';
	}
	var bufferurl = mappath+'&zoom='+(data.zoom-1)+'&center='+data.lat+','+data.lng;
	var tileurl = mappath+'&zoom='+data.zoom+'&center='+data.lat+','+data.lng;

	var newlayer = jQuery('<div class="maplayer"><img class="buffer" src="'+bufferurl+'" alt=""><img class="tile" src="'+tileurl+'" alt=""></div>');

	jQuery('.maploading').show();

	//animage globe
	if(newstyle == 'labels') {
		newlayer.hide().children('.tile').load(function() { animate=false;
			newlayer.insertAfter(currlayer).fadeIn(2000, function(){
				currlayer.hide().appendTo(newlayer).children('.marker').appendTo(newlayer);
			});
			jQuery('.maploading').hide();
		});
	} else {
		currlayer.animate(newstyle, 2000, function() { animate=false;
			//add newlayer and append current so they can be animated together
			newlayer.insertAfter(currlayer).append(currlayer);

			//hide old layer when buffer has finished loading
			newlayer.children('.buffer').load(function() {
				jQuery('.maploading').hide();
				currlayer.hide();
			});

			//move markers to positions on newlayer
			currlayer.children('.marker').each(function(){
				var marker = jQuery(this);
				var markerid = jQuery(this).attr('id').split('-')[1];
				var markerpos = jQuery(this).attr('title').split(',');
				var markerlink = jQuery('#link-'+markerid);
				var delta_x  = (LonToX(markerpos[1]) - LonToX(data.lng)) >> (21 - data.zoom);
				var delta_y  = (LatToY(markerpos[0]) - LatToY(data.lat)) >> (21 - data.zoom);
		   		var marker_x = ((xmid*2 + delta_x)/(xmid*4)*100)+'%';
		   		var marker_y = ((ymid*2 + delta_y)/(ymid*4)*100)+'%';
				marker.hide().css({top:marker_y, left: marker_x});
				markerlink.css({top:marker_y, left: marker_x});
				marker.appendTo(newlayer).show();
			});
		});
	}

}

//animate zooming of map to location of mouse
jQuery('.planet').live('click', function(e){
	var currslide = jQuery('.slide:visible').last();
	if(currslide.children('.mapholder').length>0){
		if(animate == false && data.zoom < 16){
			var mousex = e.clientX-jQuery(this).offset().left;
			var mousey = e.clientY;
			var xpercent = 	mousex/1000;
			var ypercent = mousey/500;
			data.lat = adjustLatByPx(data.lat, mousey - 250, data.zoom);
			data.lng = adjustLonByPx(data.lng, mousex - 500, data.zoom);
			data.zoom = parseFloat(data.zoom)+1;
			var newstyle = {width:"200%", height:"200%", top: (-ypercent*100) + "%", left: (-xpercent*100) + "%"};
			loadnewmap(newstyle);
		}
	} else if (currslide.children('object').length>0) {
		//play youtube video
		var player = currslide.find('embed');
		player.attr("src", player.attr("src")+'?autoplay=1');
	}
});

jQuery('.planet a').live('click', function(e){
	e.stopPropagation();
});

//TEXT EDITOR FUNCTIONS

//wrap text with bold tags
jQuery('.makebold').live('click', function(){
	var textarea = jQuery(this).parent().siblings('textarea');
	var selected= getInputSelection(textarea[0]);
	var selectlength = selected.end - selected.start;
	if(selectlength > 0) wrapText(textarea, '<b>', '</b>');
	else alert('Select the text you wish to highlight');
});

//wrap text with link tags
jQuery('.makelink').live('click', function(){
	var textarea = jQuery(this).parent().siblings('textarea');
	var selected= getInputSelection(textarea[0]);
	var selectlength = selected.end - selected.start;
	if(selectlength > 0) {
		var value = prompt("Enter a url:", "");
    	if (value != null) wrapText(textarea, '<a  href="'+value+'">', '</a>');
    } else {
		alert('Select the text you wish to turn into a link');
	}
});

//wrap text in textarea with specified tag
function wrapText(textarea, openTag, closeTag) {
    var len = textarea.val().length;
	var selected = getInputSelection(textarea[0]);
    var start = selected.start;
    var end = selected.end;
    var selectedText = textarea.val().substring(start, end);
    var replacement = openTag + selectedText + closeTag;
    textarea.val(textarea.val().substring(0, start) + replacement + textarea.val().substring(end, len));
}

//crossbrowser get selection for textareas
function getInputSelection(el) {
    var start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
        start = el.selectionStart;
        end = el.selectionEnd;
    } else {
        range = document.selection.createRange();

        if (range && range.parentElement() == el) {
            len = el.value.length;
            normalizedValue = el.value.replace(/\r\n/g, "\n");

            // Create a working TextRange that lives only in the input
            textInputRange = el.createTextRange();
            textInputRange.moveToBookmark(range.getBookmark());
            endRange = el.createTextRange();
            endRange.collapse(false);

            if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                start = end = len;
            } else {
                start = -textInputRange.moveStart("character", -len);
                start += normalizedValue.slice(0, start).split("\n").length - 1;

                if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                    end = len;
                } else {
                    end = -textInputRange.moveEnd("character", -len);
                    end += normalizedValue.slice(0, end).split("\n").length - 1;
                }
            }
        }
    }
    return { start: start, end: end };
}