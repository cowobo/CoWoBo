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
		echo "<div class='profilemenu'>";
	        echo $before_title . "Update Your Profile &raquo;" . $after_title;
	        do_action ( 'cowobo_profile_widget' );
			echo "<a href='" . wp_logout_url( "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ) . "'>Logout</a>";
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

        include COWOBO_PLUGIN_DIR . '/widgets/share.php';

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

        include COWOBO_PLUGIN_DIR . '/widgets/tour.php';

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

        include COWOBO_PLUGIN_DIR . '/widgets/contactus.php';

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
	    echo $before_title . "Translate our site &raquo;" . $after_title;
		
		//todo improve edit translation button
		//$args = 
     	if(function_exists("transposh_widget")) transposh_widget(array(), array('title' => '', 'widget_file' => ''));
		else echo 'Please activate the transposh plugin.';

        echo "</div>" . $after_widget;
    }
	
}
?>