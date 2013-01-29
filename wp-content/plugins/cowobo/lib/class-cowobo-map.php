<?php
/*
 *      class-cowobo-map.php
 *
 *      Copyright 2012 Coders Without Borders
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 *
 *
 */

 /**
 * This class handles the custom map interface
 *
 * @package Cowobo
 * @subpackage Plugin
 *
 * @todo Actually turn this into a class
 */

//MAP FUNCTIONS
global $offset, $mapdata, $cowobo;

//offset is the horizontal center in pixels of the custom map at max zoom level (3)

function LonToX($lon) {
	$offset = 2000;
	$radius = $offset / pi();
	$x = round($offset + $radius * $lon * pi() / 180);
	return $x;
}

function LatToY($lat) {
	$offset = 2000;
	$radius = $offset / pi();
	$y = round($offset - $radius * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
	return $y;
}


function XToLon($x) {
	$offset = 268435456;
	$radius = $offset / pi();
	$lon = ((round($x) - $offset) / $radius) * 180/ pi();
	return $lon;
}

function YToLat($y) {
	$offset = 268435456;
	$radius = $offset / pi();
	$y = (pi() / 2 - 2 * atan(exp((round($y) - $offset) / $radius))) * 180 / pi();
	return $y;
}

function adjustLonByPx($lon, $amount, $zoom) {
	$newlon = XToLon(LonToX($lon) + ($amount << (21 - $zoom)));
	if ($newlon < -180) $newlon = 360 + $newlon;
	elseif ($newlon > 180) $newlon = $newlon - 360;
	return $newlon;
}

function adjustLatByPx($lat, $amount, $zoom) {
	$newlat = YToLat(LatToY($lat) + ($amount << (21 - $zoom)));
	if ($newlat < -90) $newlat = 180 + $newlat;
	elseif ($newlat > 90) $newlat = $newlat - 180;
	return $newlat;
}


//return streetview tiles
function cwb_streetview($postid) {
	$coordinates = get_post_meta($postid, 'coordinates', true);
	if($coordinates ){
		$xmlstring = file_get_contents('http://cbk0.google.com/cbk?output=xml&ll='.$coordinates);
		$xml = simplexml_load_string($xmlstring);
		$pano_id = $xml->data_properties['pano_id'];
		$baseurl = 'http://cbk0.google.com/cbk?output=tile&panoid='.$pano_id.'&zoom=1';
		$tiles = '';
		for ($x=0; $x<=1; $x++) {
			$tiles .= '<img src="'.$baseurl.'&x='.$x.'&y=0" alt="" width="50%">';
		}
		$slide = '<div class="slide zoom-1" id="slide-street">'.$tiles.'</div>';
		
		return $slide;
	}
}

//check if location entered exists
function cwb_geocode($address) {
	$string = str_replace (" ", "+", urlencode($address));
	$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $details_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = json_decode(curl_exec($ch), true);
	if ($response['status'] != 'OK') return null;
	$coordinates = $response['results'][0]['geometry']['location'];
	$address = $response['results'][0]['address_components'];
	foreach($address as $array) {
		if(in_array('locality', $array["types"])) $city =  $array["long_name"];
		if(in_array('country', $array["types"])) $country =  $array["long_name"];
	}
	$location = array( 'lat' => $coordinates['lat'], 'lng' => $coordinates['lng'], 'city' => $city, 'country' => $country);
	return $location;
}

function latlng_to_percent($coordinates) {
	$xmid = 1000; $ymid = 500;
	$map_center_lat = 20;
	$map_center_lng = 0;

    if ( empty ( $coordinates ) ) return;
	$latlng = explode(',', $coordinates);
	$delta_x  = (LonToX($latlng[1]) - LonToX($map_center_lng)) >> 1;
	$delta_y  = (LatToY($latlng[0]) - LatToY($map_center_lat)) >> 1;

    $pos['left'] = ($xmid + $delta_x)/($xmid*2);
   	$pos['top'] = ($ymid + $delta_y)/($ymid*2);
	return $pos;
}

function get_map_position($width, $height, $coordinates) {
	$xmapsize = 500;
	$ymapsize = 250;
	$pos = latlng_to_percent($coordinates);
	$x =  $width - ($pos['left'] * $xmapsize) - ($width/2);
	$y = $height - ($pos['top']* $ymapsize) - ($height/2);
	$xmax = $width - $xmapsize;
	$ymax = $height - $ymapsize;
	if($x > 0) $x = 0;
	if($y > 0) $y = 0;
	if($x < $xmax) $x = $xmax;
	if($y < $ymax) $y = $ymax;
	$position = 'position:absolute; top:'.$y.'px; left:'.$x.'px';

	return $position;
}

function cwb_loadmap() {
	global $cowobo, $post;
	$linkedmarkers = array();

	$data = array('lat'=> '20', 'lng'=>'0');
	$zoom1src = get_bloginfo('template_url').'/images/maps/zoom_2.jpg';
	$zoom3src = get_bloginfo('template_url').'/images/maps/zoom_3.jpg';

	//get coordinates if specified in url or post
	if(is_single()):
		if( cowobo()->query->post_ID ) $postid = cowobo()->query->post_ID;
		else $postid = $post->ID;
		$postcoordinates = get_post_meta($postid, 'coordinates', true);
		$pos= latlng_to_percent($postcoordinates);
		$x = (-$pos['left']*200) + 50;
		$y = (-$pos['top']*200) + 40;
		if($x > 0) $x = 0;
		if($y > 0) $y = 0;
		if($x < -100) $x = -100;
		if($y < -100) $y = -100;
		$position = 'style="top:'.$y.'%; left:'.$x.'%"';
		$zoomlevel = 2;
	else:
		$zoomlevel = $x = $y = 0;
        $position = 'style="top:-20%;"';
	endif;

	//construct new maplayer
	$map = '<div class="slide zoom-'.$zoomlevel.'" id="slide-map" '.$position.'>';
	$newlayer = '<img class="slideimg map" src="'.$zoom1src.'" alt="" width="100% height="100%">';
	$newlayer .= '<input type="hidden" class="zoomsrc3" value="'.$zoom3src.'"/>';

	//include large angel on homepage
	if(!is_single()) {
		$newlayer .= '<img class="largeangel" src="'.get_bloginfo('template_url').'/images/largeangel.png" alt="">';
	}

	//sort $posts by related count
	if( is_search() or is_category() && have_posts() ){
		while (have_posts()) : the_post();
			if($coordinates = get_post_meta($post->ID, 'coordinates', true)){
				$linkedids = $cowobo->relations->get_related_ids($post->ID);
				$count = count($linkedids);
				$countarray[$post->ID] = $count;
				$linkedmarkers[] = $post;
			}
		endwhile;
	} elseif( is_single() ) {
		$countarray[$post->ID] = 1;
		$linkedmarkers[] = $post;
	} else {
		$markerposts = get_posts('cat='.get_cat_id('Locations').'&numberposts=-1');
		foreach ($markerposts as $markerpost):
			$linkedids = $cowobo->relations->get_related_ids($markerpost->ID);
			$count = count($linkedids);
			$countarray[$markerpost->ID] = $count;
			$linkedmarkers[] = $markerpost;
		endforeach;
	}

	//store the maximum number of links
	if($countarray) $max = max($countarray);
	if($max == 0) $max = 1;

	//find marker position and add it to map
    $id = 0; $xmid = 1000; $ymid = 500;
	foreach($linkedmarkers as $markerpost): $id++;
		$coordinates = get_post_meta($markerpost->ID, 'coordinates', true);
        if ( empty ( $coordinates ) ) continue;
		$latlng = explode(',', $coordinates);
		$delta_x  = (LonToX($latlng[1]) - LonToX($data['lng'])) >> 1;
		$delta_y  = (LatToY($latlng[0]) - LatToY($data['lat'])) >> 1;
   		$marker_x = ($xmid + $delta_x)/($xmid*2)*100;
   		$marker_y = ($ymid + $delta_y)/($ymid*2)*100;
		if($max == 0) $max = 1;
		$percentage = $countarray[$markerpost->ID]/$max;
		$newsize = 15 + round($percentage * 20);
		$angelsrc = get_bloginfo("template_url").'/images/angel'.rand(1,2).'.png';
		$newmargin = '-'.($newsize/2).'px 0 0 -'.($newsize/2);
		$linkmargin = $newmargin - 10;
		$posstyle = 'top:'.$marker_y.'%; left:'.$marker_x.'%; width:'.$newsize.'px; height:'.$newsize.'px; margin:';
		$markerstyle = $posstyle.$newmargin.'px';
		$linkstyle = $posstyle.$linkmargin.'px';
		$marker = '<img class="marker" style="'.$markerstyle.'" src="'.$angelsrc.'"/>';
		
		$markerlink = '<a class="markerlink" style="'.$linkstyle.'" href="'.get_permalink($markerpost->ID).'">'.$markerpost->post_title.'</a>';
		$newlayer .= $marker;
		$newlayer .= $markerlink;
	endforeach;

	$map .= $newlayer;
	$map .= '</div>';

	return $map;
}