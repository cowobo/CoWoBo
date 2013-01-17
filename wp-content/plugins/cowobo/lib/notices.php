<?php
function have_notices() {
    global $cowobo;
    $loop = &$cowobo->notices_loop;
    $notices = &$cowobo->notices;

    if ( empty ( $notices ) )
        return false;

    if ( ! $loop->count ) {
        // Loop is fresh
        reset ( $notices );
        $loop->index = 0;
        $loop->count = count( $notices );
        return true;
    } elseif ( $loop->index < $loop->count ) {
        // In the loop
        return true;
    } else {
        // Loop is finished
        reset ( $notices );
        $loop->count = 0;
    }

    $loop->in_the_loop = false;
    return false;
}

function the_notice() {
    global $cowobo;
    $loop = &$cowobo->notices_loop;
    $notices = &$cowobo->notices;
    $notice = &$cowobo->notices_loop->the_notice;

    // make sure all the notices haven't already been looped through
    if ( $loop->index >= $loop->count ) {
        return false;
    }

    $loop->in_the_loop = true;

    // retrieve the notice data for the current index
    // and advance
    $notice = current ( each ( $notices ) );

    // increment the index for the next time this method is called
    //next ( $cowobo->notices );
    $loop->index++;

}

function the_notice_content() {
    echo get_the_notice_content();
}

    function get_the_notice_content() {
        global $cowobo;
        $notice = &$cowobo->notices_loop->the_notice;
        return current ( $notice );
    }

function the_notice_type() {
    echo get_the_notice_type();
}

    function get_the_notice_type() {
        global $cowobo;
        $notice = &$cowobo->notices_loop->the_notice;
        return key ( $notice );
    }