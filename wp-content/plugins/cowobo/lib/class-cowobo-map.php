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
global $mapcenter, $mapdata, $cowobo;

//offset is the horizontal center in pixels of the map at max zoom level

//custom map functions
function LonToX($lon, $mapcenter) {
	$radius = $mapcenter / pi();
	$x = round($mapcenter + $radius * $lon * pi() / 180);
	return $x;
}

function LatToY($lat, $mapcenter) {
	$radius = $mapcenter / pi();
	$y = round($mapcenter - $radius * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
	return $y;
}


//google map functions
function XToLon($x) {
	$mapcenter = 268435456;
	$radius = $mapcenter / pi();
	$lon = ((round($x) - $mapcenter) / $radius) * 180/ pi();
	return $lon;
}

function adjustLonByPx($lon, $amount, $zoom) {
	$mapcenter = 268435456;
	$newlon = XToLon(LonToX($lon, $mapcenter) + ($amount << (21 - $zoom)));
	if ($newlon < -180) $newlon = 360 + $newlon;
	elseif ($newlon > 180) $newlon = $newlon - 360;
	return $newlon;
}

//return streetview tiles
function cwb_streetview($postid) {
	$coordinates = get_post_meta($postid, 'cwb_coordinates', true);
	if($coordinates ){
		$xmlstring = file_get_contents('http://cbk0.google.com/cbk?output=xml&&ll='.$coordinates);
		$xml = simplexml_load_string($xmlstring);
		if($pano_id = $xml->data_properties['pano_id']) {
			$baseurl = 'http://cbk0.google.com/cbk?output=tile&panoid='.$pano_id.'&zoom=1';
			$tiles = '';
			for ($x=0; $x<=1; $x++) {
				$tiles .= '<img class="slideimg" src="'.$baseurl.'&x='.$x.'&y=0" alt="" width="50%">';
			}
			$slide = '<div class="slide hide" id="slide-street" style="width:150%; top:-10%;">';
			$slide .= '<img class="proportion" src="'.get_bloginfo('template_url').'/images/ratio-streetview.png" width="100%" alt=""/>';
			$slide .= '<div style="position:absolute; width:100%; top:0;"/>'.$tiles.'</div>';
			$slide .= '</div>';
			return $slide;
		} else {
			return false;
		}
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


function cwb_loadmap() {
	global $cowobo, $post;
	$linkedmarkers = array(); $postid = 0;
	
	//check if its a single post with coordinates
	if ( $queryid = cowobo()->query->post_ID ) $postid = $queryid;
	elseif ( is_single() ) $postid = $post->ID;
	$coordinates = get_post_meta($postid, 'cwb_coordinates', true);
	
	//construct new maplayer
	$map = '<div class="slide hide zoom-0" id="slide-map">';
	
	//include map image
	if( is_single() && $coordinates) {
		$latlng = explode(',', $coordinates);
		$zoom = get_post_meta($postid, 'cwb_zoom', true);
		if(empty($zoom)) $zoom = 16;
		$mappath = 'http://maps.googleapis.com/maps/api/staticmap?maptype=hybrid&sensor=false';
		$mappath .= '&size=640x640&format=jpg&zoom='.$zoom.'&center='.$latlng[0].',';
		$newlayer = '<img class="slideimg map" src="'.$mappath.adjustLonByPx($latlng[1], -320, $zoom).'" alt="" width="50%">';
		$newlayer .= '<img class="slideimg map" src="'.$mappath.adjustLonByPx($latlng[1], 320, $zoom).'" alt="" width="50%">';
		$newlayer .= '<img class="smallangel" src="'.get_bloginfo('template_url').'/images/largeangel.png" alt="">';	
	}else {
		$zoom1src = get_bloginfo('template_url').'/images/maps/zoom_2.jpg';
		$zoom3src = get_bloginfo('template_url').'/images/maps/zoom_3.jpg';
		$newlayer = '<img class="slideimg map" src="'.$zoom1src.'" alt="" width="100%">';
		$newlayer .= '<input type="hidden" class="zoomsrc3" value="'.$zoom3src.'"/>';
		$newlayer .= '<img class="largeangel" src="'.get_bloginfo('template_url').'/images/largeangel.png" alt="">';	
	}	
		
	//get marker posts
	if( is_search() or is_category() && have_posts() ){
		$originalpost = $post;
		while (have_posts()) : the_post();
			if($coordinates = get_post_meta($post->ID, 'cwb_coordinates', true)){
				$linkedids = $cowobo->relations->get_related_ids($post->ID);
				$count = count($linkedids);
				$countarray[$post->ID] = $count;
				$linkedmarkers[] = $post;
			}
		endwhile;	
		$post = $originalpost;
	} elseif( ! $coordinates ) {
		$markerposts = get_posts('cat='.get_cat_id('Locations').'&numberposts=-1');
		foreach ($markerposts as $markerpost):
			$linkedids = $cowobo->relations->get_related_ids($markerpost->ID);
			$count = count($linkedids);
			$countarray[$markerpost->ID] = $count;
			$linkedmarkers[] = $markerpost;
		endforeach;
	}

	$max = 1;
	
	//store the maximum number of links
	if( isset( $countarray ) ) $max = max($countarray);

	//find marker position and add it to map
    $id = 0; $xmid = 1000; $ymid = 500;
	foreach($linkedmarkers as $markerpost): $id++;
		$coordinates = get_post_meta($markerpost->ID, 'cwb_coordinates', true);
        if ( empty ( $coordinates ) ) continue;
		$latlng = explode(',', $coordinates);
		$mapcenter = 2000; //pixels to center of map
		$data = array('lat'=> '20', 'lng'=>'0'); //coordinates of center
		$delta_x  = (LonToX($latlng[1], $mapcenter) - LonToX($data['lng'], $mapcenter)) >> 1;
		$delta_y  = (LatToY($latlng[0], $mapcenter) - LatToY($data['lat'], $mapcenter)) >> 1;
   		$marker_x = ($xmid + $delta_x)/($xmid*2)*100;
   		$marker_y = ($ymid + $delta_y)/($ymid*2)*100;
		if($max == 0) $max = 1;
		$percentage = $countarray[$markerpost->ID]/$max;
		$newsize = 15 + round($percentage * 20);
		$angelsrc = get_bloginfo("template_url").'/images/angel'.rand(1,2).'.png';
		$newmargin = '-'.($newsize/2).'px 0 0 -'.($newsize/2);
		$style = 'top:'.$marker_y.'%; left:'.$marker_x.'%; width:'.$newsize.'px; height:'.$newsize.'px; margin:'.$newmargin.'px';
		$marker = '<img class="marker" style="'.$style.'" src="'.$angelsrc.'"/>';
		$markerlink = '<a class="markerlink" style="'.$style.'" href="'.get_permalink($markerpost->ID).'">'.$markerpost->post_title.'</a>';
		$newlayer .= $marker;
		$newlayer .= $markerlink;
	endforeach;

	$map .= $newlayer;
	$map .= '</div>';

	return $map;
}