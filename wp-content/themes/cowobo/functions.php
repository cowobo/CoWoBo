<?php

//DEFINITIONS
global $social;
global $layouts;
global $langnames;
global $lang;
global $primecats;// Unused?

define ( 'SITEURL', get_bloginfo('url') );
define ( 'PERSONALFEEDSLUG', 'personal-feed' );
define ( 'PERSONALFEEDURL', SITEURL . '/' . PERSONALFEEDSLUG );

//LIBRARIES


//SESSION
if (!session_id()) session_start();
//$map = new Cowobo_Map;

//ACTIONS/FILTERS
add_action('comment_post', 'cwb_comment_notice');
add_action('wp', 'activate_daily_events');
add_action('comment_post', 'cowobo_add_comment_meta', 1);
//add_filter('show_admin_bar', 'my_function_admin_bar');


//ADMIN FUNCTIONS

//Remove admin bar
// Not needed?
function my_function_admin_bar(){
    return false;
}

//Add console log for easier debugging
function console_log ( $content ) {
	echo "<script>console.log('$content')</script>";
}

//GLOBAL FUNCTIONS

//Update language session
if($_GET['lang']) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'];

//POST FUNCTIONS

//return translated content if available
function cwb_the_content($postid) {
	global $lang;
    if ($translated = get_post_meta($postid, 'content-'.$lang, true)) {
    	return $translated;
    }
   	return get_the_content($postid);
}

//return translated title if available
function cwb_the_title($postid) {
	global $lang;
    if ($translated = get_post_meta($postid, 'title-'.$lang, true)) {
    	return $translated;
    }
   	return get_the_title($postid);
}

//add translated versions of post as custom fields
function cwb_correct_translation() {
	global $lang; global $post;
	update_post_meta($post->ID, 'title-'.$lang, $_POST['title-'.$lang]);
	update_post_meta($post->ID, 'content-'.$lang, $_POST['content-'.$lang]);
   	return $notices;
}

//Return gallery with captions
function cwb_loadgallery($postid){

	$slidenum = 1; //to limit the download burden

	for ($x=0; $x<$slidenum; $x++):

		//check if the slide has an image
		if($imgid = get_post_meta($postid, 'imgid'.$x, true)):
			if($imgsrc = wp_get_attachment_image_src($imgid, $size ='large')):
				$slides[$x] = '<div class="slide '.$state.'"><img src="'.$imgsrc[0].'" width="100%" alt=""/></div>';
			endif;
		endif;

		foreach(get_children('post_parent='.$postid.'&numberposts=4&post_mime_type=image') as $image):
			$imgsrc = wp_get_attachment_image_src($image->ID, $size = 'large');
			$slides[$x] = '<div class="slide '.$state.'"><img src="'.$imgsrc[0].'" width="100%" alt=""/></div>';
		endforeach;

		//check if the slide has a video
		if($caption = get_post_meta($postid, 'caption'.$x, true)):
			$videocheck = explode("?v=", $caption);
			if($url = $videocheck[1]):
				$slides[$x] = '<div class="slide '.$state.'"><object>';
					$slides[$x] .= '<param name="movie" value="http://www.youtube.com/v/'.$url.'">';
					$slides[$x] .= '<param NAME="wmode" VALUE="transparent">';
					$slides[$x] .= '<param name="allowFullScreen" value="true"><param name="allowScriptAccess" value="always">';
					$slides[$x] .= '<embed src="http://www.youtube.com/v/'.$url.'" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" wmode="opaque" width="100%" height="100%"/>';
				$slides[$x] .= '</object></div>';
				$captions .= '<div class="caption '.$state.'"></div>';
			else:
				$captions .= '<div class="caption '.$state.'">'.$caption.'</div>';
				unset($caption);
			endif;
		else:
			$captions .= '<div class="caption '.$state.'"></div>';
		endif;
	endfor;

	if($slides):
		return '<div class="gallery">'.implode('', $slides).'</div>';
	endif;
}

//Return thumbnail of post
function cwb_the_thumbnail($postid, $catslug){
	if($catslug == 'location'):
		if($coordinates = get_post_meta($postid, 'coordinates', true)):
			$zoom = '11';
		else:
			$coordinates = '0,40'; $zoom = '1';
		endif;
		$mapurl = 'http://platform.beta.mapquest.com/staticmap/v4/getmap?key=Kmjtd|luua2qu7n9,7a=o5-lzbgq&type=sat&scalebar=false&size=140,130&zoom='.$zoom.'&center='.$coordinates;
		echo '<img src="'.$mapurl.'" width="100%" alt=""/>';
	else:
		foreach(get_children('post_parent='.$postid.'&numberposts=1&post_mime_type=image') as $image):
			$imgsrc = wp_get_attachment_image_src($image->ID, $size = 'thumbnail');
			echo '<img src="'.$imgsrc[0].'" width="100%" alt=""/>';
		endforeach;
	endif;
}

//Handle requests to edit posts
function cwb_edit_request(){
	global $post; global $social;
	$rqtype = $_POST["requesttype"];
	$rquser = $_POST["requestuser"];
	$rqpost = $_POST["requestpost"];
	$rqmsg = $_POST["requestmsg"];

	//if request is coming from a post use that data instead
	if(!$rquser) $rquser = $social->profile_id;
	if(!$rqpost) $rqpost = $post->ID;

	//if we are dealing with an existing request get its meta
	if($rqtype != 'add'):
		$requests = get_post_meta($rqpost, 'request', false);
		foreach($requests as $request):
			$rqdata = explode('|', $request);
			if($rqdata[0] == $rquser) $toedit = $request;
		endforeach;
	endif;

	//handle the request
	if($rqtype == 'add'):
		add_post_meta($rqpost, 'request', $rquser.'|'.$rqmsg);
		$notices = 'Thank you, your request has been sent.';
	elseif($rqtype == 'accept'):
		delete_post_meta($rqpost, 'request', $toedit);
		add_post_meta($rqpost, 'author', $rquser);
		$notices = 'Thank you, the request has been accepted.';
	elseif($rqtype == 'deny'):
		delete_post_meta($rqpost, 'request', $toedit);
		add_post_meta($rqpost, 'request', $requestuser.'|deny');
		$notices = 'Thank you, the request has been denied.';
	elseif($rqtype == 'cancel'):
		delete_post_meta($rqpost, 'request', $toedit);
		$notices = 'Thank you, the request has been cancelled.';
	endif;

	return $notices;
}

//Get list of all published IDs
function get_published_ids() {
	global $wpdb;
	$postobjs = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish'");
	$postids = array();
	foreach ( $postobjs as $post ) {
		$postids[] = $post->ID;
	}
	return $postids;
}

// Send notification to author when comment is posted
function cwb_comment_notice($comment_id) {
	global $wpdb;
	//to do: check if user has email
	$comment = get_comment($comment_id);
	$post = get_post($comment->comment_post_ID);
	$siteurl = get_option('siteurl');
	$user = get_userdata($post->post_author);
	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
	$notify_message .= sprintf( __('Author : %1$s'), $comment->comment_author) . "\r\n";
	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
	$notify_message .= sprintf( __('Please visit the moderation panel:')) . "\r\n";
	$notify_message .= "$siteurl/wp-admin/moderation.php\r\n";
	$subject = sprintf( __('[%1$s] New Comment requires moderation'), get_option('blogname') );
	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);
	@wp_mail($user->user_email, $subject, $notify_message);
	return true;
}

// Add private tag to corresponding comment
function cwb_add_comment_meta($comment_id) {
	if(isset($_POST['privatemsg'])){
		add_comment_meta($comment_id, 'privatemsg', $_POST['privatemsg'], false);
	} else if(isset($_POST['requestmsg'])){
		add_comment_meta($comment_id, 'privatemsg', $_POST['requestmsg'], false);
	}
}

// Send contact email
function cwb_send_email() {
	global $social, $cowobo;
	$profile = get_post($social->profile_id);
	$firstname = $_POST['user_firstname'];
	$header  = 'MIME-Version: 1.0'."\r\n";
	$header .= 'Content-type: text/html; charset=utf8'."\r\n";
	$header .= 'From: Coders Without Borders <'.get_bloginfo('admin_email').'>' . "\r\n";

	if($from = $_POST['user_email']):
		$subject = 'New message from a visitor';
		$message = $_POST['emailtext'].'<br/><br/>'.$firstname.'<br/><br/>';
		$message .= '<a href="mailto:'.$from.'">Click here to reply</a>';
		mail('balthazar@cowobo.org', $subject, $message, $header);
	elseif($to = $_POST['user_friends']):
		$subject = $firstname.' sent you this message via our site';
		$message = $_POST['emailtext'].'<br/><br/>'.$firstname.'<br/><br/>';
		$message .= '<a href="'.$_SERVER['REQUEST_URI'].'">'. $cowobo->feed->feed_title() .'</a><br/>';
		if(is_single()) $message .= get_the_excerpt(); else $message .= get_bloginfo('description');
		mail('balthazar@cowobo.org,'.$to, $subject, $message, $header);
	else:
		$emailnotice = 'Please enter at least one email address';
	endif;
	$emailnotice = 'Your email has been sent successfully';

	return $emailnotice;
	//to do: handle and return errors
}

// Set up daily cron jobs
function activate_daily_events() {
	if ( !wp_next_scheduled( 'daily_events' ) ) {
		wp_schedule_event(time(), 'daily', 'daily_events' );
	}
}

//Check if feed is a user feed
function is_userfeed() {
    global $wp_query;
    if ( isset ( $wp_query->query_vars['userfeed'] ) && $userfeed = get_user_by ( 'slug', $wp_query->query_vars['userfeed'] ) )
            return $userfeed;
    else return false;
}


// Returns pagination for a feed
function cwb_pagination($pages = '', $range = 2){
     $showitems = ($range * 2)+1;
     global $paged;
     if(empty($paged)) $paged = 1;
     if($pages == ''){
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages) $pages = 1;
     }
	 $pagination = '<span class="horspans">';
     if(1 != $pages){
         for ($i=1; $i <= $pages; $i++){
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
                 $pagination .= ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
         }
         $pagination .= '<a href="'.get_pagenum_link($paged + 1).'">Next</a>';
     }
	 return $pagination;
}

/**
 * Returns an array with the current category (obj) and the category id (str)
 *
 * @return arr  current category (obj) and category id (str)
 */
function cowobo_get_current_category() {
    if (is_home()) {
        $catid = 0;
        $currentcat = false;
    } elseif ($catid = get_query_var('cat')) {
        $currentcat = get_category($catid);
    } else {
        $cat = get_the_category($post->ID);
        $currentcat = $cat[0];
        $catid = $currentcat->term_id;
    }
    return array ('currentcat' => $currentcat, 'catid' => $catid );
}

//include custom feed template
remove_all_actions( 'do_feed_rss2' );
add_action( 'do_feed_rss2', 'cwb_feed_rss2');

function cwb_feed_rss2() {
    $rss_template = get_template_directory() . '/feeds.php';
    load_template( $rss_template );
}

// Sort objects stored in array based on object property
function array_object_sort($array,$property,$dir = 'ASC') {
	foreach($array as $a_key => $a_value) {
		$sortable[$a_key] = strtolower($a_value->$property);
	}
	if ( $dir == 'DESC' ) arsort($sortable);
	else asort($sortable);
	foreach($sortable as $s_key=>$s_val) {
		$sorted[] = $array[$s_key];
	}
	return $sorted;
}

// Removes doubles. from an array containing post objects
function remove_doubles($postlist) {
	$postid_list = array();
	foreach ($postlist as $key => $post) {
		foreach ($postid_list as $postid) {
			if ($post->ID == $postid) {
				unset($postlist[$key]);
				$removed = true;
				break;
			}
		}
		if (!$removed) $postid_list[] = $post->ID;
	}
	return $postlist;
}

//store different translating messages
$langnames = array(
	'en' => array('English', 'Welcome to Coders Without Borders', 'Translating page..'),
	'ar' => array('دي', 'مرحبا بكم في المبرمجون بلا حدود', 'متابعة باللغة العربية', '..ترجمة الصفحة'),
	'ca' => array('Català', 'Benvingut als Codificadors Sense Fronteres', ' Traduint pàgina..'),
	'cs' => array('Ceské', 'Vítejte na programátory bez hranic', "Překlady stránku .."),
	'da' => array('Dansk', 'Velkommen til Programmører Uden Grænser', 'Oversætter siden ..'),
	'de' => array('Deutsch', 'Welkom bei Programmierer ohne Grenzen', 'Seite Verarbeitung ..'),
	'el' => array('Ελληνική', 'Καλώς ήλθατε στο Coders Χωρίς Σύνορα', 'Μεταφράζοντας σελίδα ..'),
	'es' => array('Españoles', 'Bienvenido a Codificadores Sin Fronteras', ' Traduciendo página .. '),
	'fa' => array('فارسی', 'به برنامه نویسان بدون مرز خوش آمدید', 'صفحه ترجمه ..'),
	'fi' => array('Suomalainen', 'Tervetuloa Ohjelmoijat Ilman Rajoja', "Käännetään sivua .."),
	'fr' => array('Français', 'Bienvenue à Codeurs Sans Frontières', ' Page traduire .. '),
	'id' => array('Indonesia', 'Selamat Datang programmer tanpa batas', 'Menerjemahkan halaman ..'),
	'it' => array('Italiano', 'Benvenuto a Coders Senza Frontiere', 'Tradurre la pagina'),
	'iw' => array('Hebrew', 'ברוכים באים למקודדים ללא גבולות', 'דף תרגום ..'),
	'ja' => array('日本', '国境なきコーダーへようこそ', '日本語で継続', 'ページを翻訳する..'),
	'hu' => array('Magyar', 'Üdvözöljük a programozóknak Határok Nélkül', 'Fordítás oldal ..'),
	'hr' => array('Hrvatskom', 'Dobrodošli Programera Bez Granica', ' Prevođenje stranica .. '),
	'lt' => array('Lietuvos', 'Sveiki atvykę į Programuotojams be Sienų ', 'ulkojot lapu ..'),
	'no' => array('Norsk', 'Velkommen til Programmerere Uten Grenser', ' Oversett siden ..'),
	'pl' => array('Polish', 'Witamy Programistów Bez Granic', 'Przełożenie stronę ..'),
	'pt' => array('Português', 'Bem-vindo ao Coders Sem Fronteiras', ' Página Traduzindo ..'),
	'nl' => array('Nederlands', 'Welkom bij Codeurs Zonder Grenzen', 'Pagina wordt vertaald..'),
	'ro' => array('Român', 'Bine ați venit la Programatori Fără Frontiere ', 'Traducerea pagina ..'),
	'ru' => array('Русский', 'Добро пожаловать в Coders без границ', "Перевод страницы .."),
	'sk' => array('Slovak', 'Welcome to Coders Without Borders', 'Prevod stránky ..'),
	'sl' => array('Slovenskega', 'Dobrodošli na Kodiranje Brez Meja', 'Translating page..'),
	'sr' => array('Српске', 'Добродошли у Цодерса без граница', ' Превођење страна .. '),
	'sv' => array('Svenska', 'Välkommen till Coders Utan Gränser', "Översättning sida .."),
	'th' => array('ไทย', 'ยินดีต้อนรับสู่โปรแกรมเมอร์ไร้พรมแดน', 'หน้าแปล .. '),
	'tr' => array('Türk', 'Sınır Tanımayan Coders hoşgeldiniz', 'Tercüme sayfası .. '),
	'uk' => array('Український', 'Ласкаво просимо в кодери без кордонів', 'Переклад сторінці ..'),
	'vi' => array('Việt Nam', 'Chào mừng bạn đến với các lập trình viên không biên giới', 'trang Dịch ..'),
	'vko' => array('한국어', '국경을 초월한 코더에 오신 것을 환영합니다', '한국어로 계속', '번역 페이지를 ..'),
	'zh-CN' => array('中国', '欢迎到编码器无国界', '继续在中国', '网页翻译。'),
);

// Utility functions

/**
 * Return time passed since publish date
 */
function cwb_time_passed($timestamp){
    $timestamp = (int) $timestamp;
    $current_time = time();
    $diff = $current_time - $timestamp;
    $intervals = array ('day' => 86400, 'hour' => 3600, 'minute'=> 60);
    //now we just find the difference
    if ($diff == 0) return 'just now &nbsp;';
    if ($diff < $intervals['hour']){
        $diff = floor($diff/$intervals['minute']);
        return $diff == 1 ? $diff . ' min ago' : $diff . ' mins ago';
    }
    if ($diff >= $intervals['hour'] && $diff < $intervals['day']){
        $diff = floor($diff/$intervals['hour']);
        return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
    }
    if ($diff >= $intervals['day']){
        $diff = floor($diff/$intervals['day']);
        return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
    }
}