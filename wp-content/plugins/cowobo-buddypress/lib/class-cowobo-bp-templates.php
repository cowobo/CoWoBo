<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class CoWoBo_BuddyPress_Templates
{
    public function profile_activities() {
			include( COWOBO_BP_TEMPLATEPATH . '/useractivities.php');
    }
}