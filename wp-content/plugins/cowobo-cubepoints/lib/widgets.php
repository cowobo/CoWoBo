<?php
add_action('widgets_init', 'cowobo_cp_register_widgets' );
function cowobo_cp_register_widgets() {
    register_widget('cowobo_recent_activity_widget');
    register_widget('cowobo_recently_active_widget');
}

class CoWoBo_Recent_Activity_Widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_recent_activity_widget', 'CoWoBo Recent Activity', array('description' => 'Display your recent activity' ) );
    }

        public function cowobo_recent_activity_widget() {
            $this->__construct();
        }

    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='recent_activity'>";
        //echo $before_title . "Your Profile" . $after_title;
        do_action ( 'cowobo_recent_activity_widget' );
        echo "</div>" . $after_widget;
    }



}

class CoWoBo_Recently_Active_Widget extends WP_Widget {

    public function __construct() {
        parent::WP_Widget('cowobo_recently_active_widget', 'CoWoBo Recently Active', array('description' => 'Display the most recently active users' ) );
    }

        public function cowobo_recently_active_widget() {
            $this->__construct();
        }

    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        echo $before_widget . "<div class='recently_active'>";
        //echo $before_title . "Your Profile" . $after_title;
        do_action ( 'cowobo_recently_active_widget' );
        echo "</div>" . $after_widget;
    }



}