<?php
add_action('widgets_init', 'cowobo_register_widgets' );
function cowobo_register_widgets() {
    register_widget('cowobo_profile_widget');
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

        echo $before_widget . "<div class='profilemenu'>";
        //echo $before_title . "Your Profile" . $after_title;
        do_action ( 'cowobo_profile_widget' );
        echo "</div>" . $after_widget;
    }



}