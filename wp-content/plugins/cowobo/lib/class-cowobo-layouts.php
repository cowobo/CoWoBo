<?php
/*
 *      class-cowobo-layouts.php
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

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

 /**
 * This class stores the layouts for the different templates
 *
 *
 * @package cowobo-layouts
 */

class Cowobo_Layouts {

	public $layout;
	public $langnames;

	public function __construct() {

		$this->layout = array();

		$this->layout[get_cat_ID('Wikis')] = array(
			array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'website', 'label' =>'Source', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
			array('type' => 'largetext', 'label' =>'Outline', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('News')] = array(
			array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'website', 'label' =>'Source', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
			array('type' => 'largetext', 'label' =>'In Brief', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('Projects')] = array(
			array('type' => 'title', 'label' =>'Project Name', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => '(Optional) Select the city nearest to the project'),
			array('type' => 'involvement', 'label' =>'Involvement', 'hint' => 'Select your role in the project'),
			array('type' => 'dropdown', 'label' =>'Status', 'hint' => 'Completed,Prototype,Under Construction'),
			array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
			array('type' => 'slogan', 'label' =>'One Liner', 'hint' => 'A phrase that helps sum up project'),
			array('type' => 'largetext', 'label' =>'Description', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('Coders')] = array(
			array('type' => 'title', 'label' =>'Full Name', 'hint' => 'Keep it real'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'email', 'label' =>'Email', 'hint' => 'Required to notify you of responses (will stay hidden)'),
			array('type' => 'location', 'label' =>'Current Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'encpath', 'label' =>'Travel Routes', 'hint' => 'Click here to add a path from google maps'),
			array('type' => 'checkboxes', 'label' =>'Coding Languages', 'hint' => 'C++,C#,Html,Java,jQuery,PHP,Perl,Python,Ruby,Visual Basic'),
			array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.myblog.com'),
			array('type' => 'smalltext', 'label' =>'Looking For', 'hint' => 'ie Collaborators, Funding, etc'),
			array('type' => 'largetext', 'label' =>'Biography', 'hint' => 'Maximum 4000 characters'),
		);

		$this->layout[get_cat_ID('Forums')] = array(
			array('type' => 'title', 'label' =>'Question Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add screenshots/photos to help explain your question'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Current Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'largetext', 'label' =>'Elaborate question', 'hint' => 'Max 3000 characters'),
		);


		$this->layout[get_cat_ID('Events')] = array(
			array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'dates', 'label' =>'Dates', 'hint' => 'Please use the format dd/mm/yyyy'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'smalltext', 'label' =>'Address', 'hint' => 'Street address of event'),
			array('type' => 'smalltext', 'label' =>'Contact Info', 'hint' => 'ie tel, email'),
			array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
			array('type' => 'largetext', 'label' =>'Description', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('Jobs')] = array(
			array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'smalltext', 'label' =>'Contact Info', 'hint' => 'Will only be visible to logged in users'),
			array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
			array('type' => 'largetext', 'label' =>'Description', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('Locations')] = array(
			array('type' => 'title', 'label' =>'Name of location', 'hint' => 'Make sure it does not already exist on our site'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'country', 'label' =>'Country', 'hint' => 'Contact us if yours is not on the list'),
			array('type' => 'largetext', 'label' =>'Description', 'hint' => 'Keep it short and sweet and sweet'),
		);

		$this->layout[get_cat_ID('Blogs')] = array(
			array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'largetext', 'label' =>'Blog Text', 'hint' => 'Max 3000 characters'),
		);

		$this->layout[get_cat_ID('Partners')] = array(
			array('type' => 'title', 'label' =>'Name of Partner', 'hint' => 'Keep it short and sweet'),
			array('type' => 'gallery', 'label' =>'Gallery', 'hint' => 'Add relevant images or youtube videos'),
			array('type' => 'tags', 'label' =>'Tags', 'hint' => 'Separate tags with commas ","'),
			array('type' => 'location', 'label' =>'Location', 'hint' => 'Nearest city, country, and zoom level'),
			array('type' => 'slogan', 'label' =>'Slogan', 'hint' => 'One line that helps describe the project'),
			array('type' => 'largetext', 'label' =>'Description', 'hint' => 'Max 3000 characters'),
		);

	}
}
?>