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

//define the center in pixels of google maps

function LonToX($lon) {
	$offset = 2000;
	$radius = $offset / pi();
	return round($offset + $radius * $lon * pi() / 180);
}

function LatToY($lat) {
	$offset = 2000;
	$radius = $offset / pi();
	return round($offset - $radius * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
}


function XToLon($x) {
	$offset = 268435456;
	$radius = $offset / pi();
	return ((round($x) - $offset) / $radius) * 180/ pi();
}

function YToLat($y) {
	$offset = 268435456;
	$radius = $offset / pi();
	return (pi() / 2 - 2 * atan(exp((round($y) - $offset) / $radius))) * 180 / pi();
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


//check if location entered exists
function cwb_geocode($address) {
	$string = str_replace (" ", "+", urlencode($address));
	$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $details_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = json_decode(curl_exec($ch), true);
	if ($response['status'] != 'OK') return null;
	$geometry = $response['results'][0]['geometry'];
	$lng = $geometry['location']['lng'];
	$lat = $geometry['location']['lat'];
	//cityname = $geometry['location']['lat'];
	$latlng = array('lat' => $lat, 'lng' => $lng);
	return $latlng;
}



function cwb_loadmap() {
	global $cowobo, $post;
	$linkedmarkers = array();

	$data = array('lat'=> '20', 'lng'=>'0', 'zoom'=>1);

	//get coordinates if specified in url or post
	if(is_single()):
		if( cowobo()->query->post_ID ) $postid = cowobo()->query->post_ID;
		else $postid = $post->ID;
		$postcoordinates = get_post_meta($postid, 'coordinates', true);
		$tileurl = get_bloginfo('template_url').'/images/maps/zoom_1.jpg';
	endif;

	$tileurl = get_bloginfo('template_url').'/images/maps/zoom_1.jpg';

	//construct new maplayer
	$map = '<div class="slide zoom1" id="slide-0" style="width:110%">';
	$newlayer = '<img class="mapimg" src="'.$tileurl.'" alt="" width="100%">';

	//sort $posts by related count
	$markerposts = get_posts('cat='.get_cat_id('Locations').'&numberposts=-1');

	//find marker position and add it to map
    $id = 0; $xmid = 500; $ymid = 250; $max = 0;
	foreach($markerposts as $markerpost): $id++;
		$coordinates = get_post_meta($markerpost->ID, 'coordinates', true);
        if ( empty ( $coordinates ) ) continue;
		$latlng = explode(',', $coordinates);
		$delta_x  = (LonToX($latlng[1]) - LonToX($data['lng'])) >> (3 - $data['zoom']);
		$delta_y  = (LatToY($latlng[0]) - LatToY($data['lat'])) >> (3 - $data['zoom']);
   		$marker_x = ($xmid + $delta_x)/($xmid*2)*100;
   		$marker_y = ($ymid + $delta_y)/($ymid*2)*100;
		if($max == 0) $max = 1;
		//$percentage = $countarray[$markerpost->ID]/$max;
		//$newsize = 15 + round($percentage * 20);
		//$newmargin = '-'.($newsize/2).'px 0 0 -'.($newsize/2).'px';
        $newsize = $newmargin = 0;
		$markerstyle = 'top:'.$marker_y.'%; left:'.$marker_x.'%; width:'.$newsize.'px; height:'.$newsize.'px; margin:'.$newmargin;
		$marker = '<img class="marker" style="'.$markerstyle.'" src="'.get_bloginfo("template_url").'/images/mapnav.png"/>';
		$markerlinks[] = '<a class="markerlink" style="'.$markerstyle.'" href="'.get_permalink($markerpost->ID).'">'.$markerpost->post_title.'</a>';
		$newlayer .= $marker;
	endforeach;

	$map .= $newlayer;
	$map .= '</div>';

	//now add the links to a layer above the cloud mask
	if($markerlinks):
		$map .= '<div class="markerlinks zoom1">';
		foreach($markerlinks as $markerlink):
			$map .= $markerlink;
		endforeach;
		$map .= '</div>';
	endif;

	echo $map;
}