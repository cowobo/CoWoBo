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
$offset = 268435456;

function LonToX($lon) {
	$offset = 268435456;
	$radius = $offset / pi();
	return round($offset + $radius * $lon * pi() / 180);
}

function LatToY($lat) {
	$offset = 268435456;
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
    $postid = 0;

	//setup default map and tile size
	$data = array('lat'=> '0', 'lng'=>'40', 'zoom'=>3, 'type'=>'sat', 'path' => '', 'type' => 'sat' );
	$xmid = 500; $ymid = 250;
	if( $cowobo->query->post_ID ) $postid = $cowobo->query->post_ID;
	elseif(is_single()) $postid = $post->ID;

	//get coordinates if specified in url or post
	$postcoordinates = get_post_meta($postid, 'coordinates', true);

	if($cowobo->query->center) $newcenter = $cowobo->query->center;
	else $newcenter = $postcoordinates;
	if($cowobo->query->keywords):
		$geocode = cwb_geocode($cowobo->query->keywords);
		$data['lat'] = $geocode['lat'];
		$data['lng'] = $geocode['lng'];
	elseif($newcenter):
		$postcoordinates = explode(',',$newcenter);
		$data['lat'] = $postcoordinates['0'];
		$data['lng'] = $postcoordinates['1'];
	endif;

	//get zoomlevel if specified in url or post
	if($cowobo->query->zoom) $newzoom = $cowobo->query->zoom;
	else $newzoom = get_post_meta($postid, 'zoom', true);
	if($newzoom) $data['zoom'] = $newzoom;
	elseif($postcoordinates && $cowobo->query->action != 'editpost' && ! $cowobo->query->new ) $data['zoom'] = 9;

	//check if post has path
	//if($postpath = get_post_meta($postid, 'encpath', true)) $path = '&shapeformat=cmp&shape='.$postpath;

	//get map type if specified and adjust for different tile providers
	if( $cowobo->query->maptype ) $data['type'] = $cowobo->query->maptype;

	if($data['zoom']<10): //mapquest
		$maptype = $data['type'];
	else: //google
		if($data['type'] == 'sat') $maptype = 'satellite';
		elseif($data['type'] == 'hyb') $maptype = 'hybrid';
	endif;

	//update global mapdata
	$mapdata = $data;

	if(is_home() or is_category()):
		$bufferurl =  get_bloginfo('template_url').'/images/buffer.jpg';
		$tileurl =  get_bloginfo('template_url').'/images/tile.jpg';
	else:
		if($data['zoom']<5) $mappath = 'http://platform.beta.mapquest.com/staticmap/v4/getmap?key=Kmjtd|luua2qu7n9,7a=o5-lzbgq&type='.$maptype.'&scalebar=false&size=1000,500';
		else $mappath = 'http://maps.googleapis.com/maps/api/staticmap?maptype='.$maptype.'&sensor=false&size=640x390&format=jpg&size=1000x500'; //.$path;
		$bufferurl =  $mappath.'&zoom='.($data['zoom']-1).'&center='.$data['lat'].','.$data['lng'];
		$tileurl =  $mappath.'&zoom='.$data['zoom'].'&center='.$data['lat'].','.$data['lng']; //.$path;
	endif;

	//add the navigation controls
	$panleft = $data['lat'].','.adjustLonByPx($data['lng'], -$xmid, $data['zoom']);
	$panright = $data['lat'].','.adjustLonByPx($data['lng'], $xmid, $data['zoom']-1);
	$panup = adjustLatByPx($data['lat'], $ymid/2*-1, $data['zoom']).','.$data['lng'];
	$pandown = adjustLatByPx($data['lat'], $ymid/2*1, $data['zoom']).','.$data['lng'];
	if($data['zoom']<15) $zoomin = $data['zoom']+2; else $zoomin = 17;
	if($data['zoom']>3) $zoomout = $data['zoom']-2; else $zoomout = 3;

	echo '<div class="navcontrols">';
		echo '<a class="pan panleft" href="?rotate=left">&#60;</a>';
		echo '<a class="pan panright" href="?rotate=right">&#62;</a>';
		echo '<a class="pan panup" href="?rotate=up">&#8743;</a>';
		echo '<a class="pan pandown" href="?rotate=down">&#8744;</a>';
		echo '<a class="zoom zoomin" href="?zoom=in">+</a>';
		echo '<a class="zoom zoomout" href="?zoom=out">-</a>';
	echo '</div>';

	//construct new maplayer
	$map = '<div class="slide">';
	$map .= '<input type="hidden" class="mapdata" value="'.implode('*',$data).'"/>';
	$map .= '<div class="mapholder">';

	$newlayer = '<div class="maplayer">';
	$newlayer .= '<img class="buffer" src="'.$bufferurl.'" alt="">';
	$newlayer .= '<img class="tile" src="'.$tileurl.'" alt="">';

	//sort $posts by related count
	if(!$postcoordinates) $markerposts = get_posts(array('cat'=>get_cat_id('Locations'), 'numberposts'=>-1));
	else $markerposts = array($post);

	foreach ($markerposts as $markerpost):
		$linkedids = $cowobo->relations->get_related_ids($markerpost->ID);
		$count = count($linkedids);
		//if($count>0): //only show linked locations
			$countarray[$markerpost->ID] = $count;
			$linkedmarkers[] = $markerpost;
		//endif;
	endforeach;

	if($countarray) $max = max($countarray);

	//find marker position and add it to map
    $id = 0;
	foreach($linkedmarkers as $markerpost): $id++;
		$coordinates = get_post_meta($markerpost->ID, 'coordinates', true);
		$latlng = explode(',', $coordinates);
		$delta_x  = (LonToX($latlng[1]) - LonToX($data['lng'])) >> (21 - $data['zoom']);
		$delta_y  = (LatToY($latlng[0]) - LatToY($data['lat'])) >> (21 - $data['zoom']);
   		$marker_x = ($xmid*2 + $delta_x)/($xmid*4)*100;
   		$marker_y = ($ymid*2 + $delta_y)/($ymid*4)*100;
		if($max == 0) $max = 1;
		$percentage = $countarray[$markerpost->ID]/$max;
		$newsize = 15 + round($percentage * 20);
		$newmargin = '-'.($newsize/2).'px 0 0 -'.($newsize/2).'px';
		$markerstyle = 'top:'.$marker_y.'%; left:'.$marker_x.'%; width:'.$newsize.'px; height:'.$newsize.'px; margin:'.$newmargin;
		$marker = '<img class="marker" id="marker-'.$id.'" style="'.$markerstyle.'" src="'.get_bloginfo("template_url").'/images/mapnav.png" title="'.$coordinates.'"/>';
		$markerlinks[] = '<a class="markerlink" id="link-'.$id.'" style="'.$markerstyle.'" href="'.get_permalink($markerpost->ID).'">'.$markerpost->post_title.'</a>';
		$newlayer .= $marker;
	endforeach;

	$newlayer .= '</div>';
	$map .= $newlayer;
	$map .= '</div>';
	$map .= '</div>';

	//now add the links to each marker in a div above the map
	if(!$postcoordinates && $markerlinks):
		$map .= '<div class="markerlinks"><div class="mapholder">';
		foreach($markerlinks as $markerlink):
			$map .= $markerlink;
		endforeach;
		$map .= '</div></div>';
	endif;

	return $map;
}