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
        echo $before_title . "<a href='" . cowobo()->users->get_current_user_profile_link() . "'>Your Activity</a>" . $after_title;

        cowobo()->points->do_points_log_box (get_current_user_id(), 3 );

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
        echo $before_title . "Recently Active" . $after_title;

        $recently_active_ids = cowobo()->points->get_recently_active_profile_ids();

        $previous_time = '';
        foreach ( $recently_active_ids as $user ) {
            $user_rank = cowobo()->points->get_user_rank( $user['uid'] );
            $time_since = cp_relativeTime( $user['maxtimestamp'] );
            if ( $previous_time != $time_since ) {
                echo "<span class='time-since'>" . $time_since . "</span>";
                $previous_time = $time_since;
            }

            echo "<a href='" . get_permalink( $user['profile_id'] ) . "'>";
            echo "<div class='recently_active_user'>";
            echo get_avatar( $user['uid'], 40 );
            echo "<h3>" . get_the_title( $user['profile_id'] ) . "<br>";
            echo "<small>$user_rank</small></h3>";
            echo "</div>";
            echo "</a>";
        }


        do_action ( 'cowobo_recently_active_widget' );
        echo "</div>" . $after_widget;
    }



}