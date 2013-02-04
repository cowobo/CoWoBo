<?php
add_action('widgets_init', 'cowobo_register_widgets' );

function cowobo_register_widgets() {
    register_widget('cowobo_profile_widget');
    register_widget('cowobo_share_widget');
    register_widget('cowobo_tour_widget');
	register_widget('cowobo_rss_widget');
	register_widget('cowobo_contact_widget');
	register_widget('cowobo_translate_widget');
}

class CoWoBo_Profile_Widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_profile_widget', 'CoWoBo Profile', array('description' => 'Display the CoWoBo Profile Badge' ) );
    }

        public function cowobo_profile_widget() {
            $this->__construct();
        }

    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        echo $before_widget;
		echo $before_title . "Update Your Profile &raquo;" . $after_title;
		
		echo "<div class='profilemenu'>";
			if (is_user_logged_in() ) {
				do_action ( 'cowobo_profile_widget' );
				echo "<a href='" . wp_logout_url( "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ) . "'>Logout</a>";
			} else {
				
				echo '<img class="mysteryman" src="'.get_bloginfo('template_url').'/images/mysteryman.png" alt="" />';
				echo '<a href="?action=login">Click to go to your profile</a><br/>';
				echo '<b>Location:</b> Unknown<br/>';
				echo '<b>Coding level:</b> Unknown<br/>';
				echo '<b>Looking for:</b> Unknown<br/>';
			}
        echo "</div>";
		echo $after_widget;
    }

}

class CoWoBo_Share_widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_share_widget', 'CoWoBo Share Widget', array('description' => 'Display the CoWoBo Share Widget' ) );
    }

        public function cowobo_share_widget() {
            $this->__construct();
        }

    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='share-widget'>";
			echo $before_title . "Spread the word &raquo;" . $after_title;
			
			//if(is_single()) $description .= get_the_excerpt(); else $description .= get_bloginfo('description');
			$pagelink = get_bloginfo('url').$_SERVER['REQUEST_URI'];
			$fburl = 'https://www.facebook.com/dialog/feed?app_id=296002893747852';
			$fburl .= '&link='. urlencode ( get_bloginfo( 'url' ) );
			$fburl .= '&name='.urlencode("Coders Without Borders");
			//$fburl .= '&caption='.urlencode('on Coders Without Borders');
			$fburl .= '&description='.urlencode( get_bloginfo( 'description' ) );
			$fburl .= '&redirect_uri='.$pagelink;
			$tweeturl = 'http://twitter.com/home?status='.urlencode('Check this out "http://www.cowobo.org/"');
			
			if( isset ( $_POST['user_firstname'] ) ) echo 'Your message has been sent.';
			
			echo '<form method="post" action="">';
				echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
				echo '<input type="input" name="user_firstname" placeholder="Your name"/>';
				echo '<input type="input" name="user_friends" placeholder="Friend\'s addresses separate by commas" />';
			   	echo '<input type="submit" class="button sendemail" value="Send Email">';
				echo '<a class="fbbutton" href="'.$fburl.'">Facebook</a>';
			    echo '<a class="tweetbutton" target="_blank" href="'.$tweeturl.'">Twitter</a>';
			echo '</form>';
				
			echo '<div class="fb-like" data-layout="button_count" data-width="250" data-show-faces="false" data-font="trebuchet ms"></div>';?>
			
			<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk')); </script><?php

		echo "</div>" . $after_widget;
    }
}


class CoWoBo_Tour_widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_tour_widget', 'CoWoBo Tour Widget', array('description' => 'Take the tour!' ) );
    }

        public function cowobo_tour_widget() {
            $this->__construct();
        }

    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='tour-widget'>";
		    echo '<h2>Learn more &raquo;</h2>';
			echo '<div class="introline">We instruct technology to make the world a better place</div>';
			echo '<a href="'.get_bloginfo('url').'/category/wiki">Who &raquo;</a> ';
			echo '<a href="'.get_bloginfo('url').'/category/wiki">What &raquo;</a> ';
			echo '<a href="'.get_bloginfo('url').'/category/wiki">Why &raquo;</a> ';
			echo '<a href="'.get_bloginfo('url').'/category/wiki">Where &raquo;</a> ';
			echo '<a href="'.get_bloginfo('url').'/category/wiki">How &raquo;</a> ';
        echo "</div>" . $after_widget;
		
    }
}


class CoWoBo_RSS_widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_rss_widget', 'CoWoBo RSS Widget', array('description' => 'RSS Links!' ) );
    }

        public function cowobo_rss_widget() {
            $this->__construct();
        }

    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='rss-widget'>";

	    echo $before_title . "Stay in touch &raquo;" . $after_title;

        include COWOBO_PLUGIN_DIR . '/widgets/rss.php';

        echo "</div>" . $after_widget;
    }
}


class CoWoBo_Contact_widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_contact_widget', 'CoWoBo Contact Widget', array('description' => 'Contact Us Form' ) );
    }

        public function cowobo_contact_widget() {
            $this->__construct();
        }

    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='contact-widget'>";
		    echo $before_title . "Tell us what you think &raquo;" . $after_title;
			if( cowobo()->query->user_email ) echo 'Your message has been sent.';
			echo '<form method="post" action="">';
				echo '<textarea name="emailtext" rows="5"></textarea>';		
				echo '<input type="text" name="user" class="hide" value=""/>'; //spammer trap
				echo '<input type="input" class="lefthalf" name="user_firstname" placeholder="Your Name" />';
				echo '<input type="input" class="righthalf" name="user_email" placeholder="Email Address" />';
		        wp_nonce_field( 'sendemail', 'sendemail' );
		   		echo '<input type="submit" class="button" value="Send Email">';
			echo '</form>';
        echo "</div>" . $after_widget;
    }
}


class CoWoBo_Translate_widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_translate_widget', 'CoWoBo Translate Widget', array('description' => 'Translate Drop Down Menu' ) );
    }

        public function cowobo_translate_widget() {
            $this->__construct();
        }

    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='translate-widget'>";
	    echo $before_title . "Help translate our site &raquo;" . $after_title;
		
		//todo improve edit translation button
		//$args = 
     	if(function_exists("transposh_widget")) transposh_widget(array(), array('title' => '', 'widget_file' => ''));
		else echo 'Please activate the transposh plugin.';

        echo "</div>" . $after_widget;
    }
	
}
?>