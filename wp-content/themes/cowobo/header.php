<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/1">
<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
<meta name="generator" content="Dev-PHP 2.4.0" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta name="rooturl" content="<?php bloginfo('url'); ?>/"/>
<link rel="shortcut icon" href="<?php bloginfo('template_url');?>/images/favicon.ico" />
<link rel="icon" type="image/gif" href="<?php bloginfo('template_url');?>/images/animated_favicon1.gif" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/style.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/print.css" media="print"/>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" /><?php

// LOAD TEMPLATE SCRIPTS
wp_enqueue_script("jquery");
wp_enqueue_script('mainscript', get_bloginfo('template_url').'/js/script.js');
if(is_single())	wp_enqueue_script('comment-reply');

//get currentcat
$current_category = cowobo()->get_current_category();
extract ($current_category);

//$feed_query = ($catid = get_query_var('cat')) ? "'c',$catid" : "'p',".$post->ID;
$userid = wp_get_current_user()->ID;

//include rtl styles if required
//$rtlarray = array('ar', 'zh-CN', 'ja', 'iw');
//if(in_array($lang, $rtlarray)):?>
	<link rel="stylesheet" type="text/css" href="//<?php bloginfo('template_url') ?>/rtl.css"/><?php
//endif;

wp_head();?>

</head>

<body class="no-js">
    <script type="text/javascript">
        document.body.className = document.body.className.replace('no-js','js');
    </script>
