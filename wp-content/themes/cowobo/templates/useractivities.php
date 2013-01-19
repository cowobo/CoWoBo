<?php
global $cowobo;

echo "<div id='buddypress'>";

    if ( $cowobo->users->is_current_user_profile() ) {
        echo "<div class='tab'>";
            echo "<h3>Quick Update</h3>";
            $_GET['r'] = null;
            bp_get_template_part( 'activity/post-form' );
        echo "</div>";
    }

    echo "<div class='tab activity'>";
        echo "<h3>Activities</h3>";
        $cowobo->buddypress->query_filter = 'user';
        bp_get_template_part( 'activity/activity-loop' );
    echo "</div>";

    if ( ! $cowobo->users->is_current_user_profile() ) {
        echo "<div class='tab'>";
            echo "<h3>Leave a message</h3>";
            $_GET['r'] = $cowobo->users->displayed_user->user_nicename;
            bp_get_template_part( 'activity/post-form' );
        echo "</div>";
    }
    echo "<div class='tab activity'>";
        echo "<h3>Mentions</h3>";
        $cowobo->buddypress->query_filter = 'mentions';
        bp_get_template_part( 'activity/activity-loop' );
    echo "</div>";


echo "</div>";