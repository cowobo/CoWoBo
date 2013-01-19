<?php


$allowed_notice_types = array(
    "message",
    "error",
    "NOEMAIL",
    "WRONGPASSWORD",
    "editrequest_sent",
    "editrequest_accepted",
    "editrequest_denied",
    "editrequest_cancelled",
    'editrequest',
    'email_sent'
);
cowobo()->print_notices( $allowed_notice_types );

//TODO
//check if user has setup an email and explain why its needed
//check for unread comments
//check if its the user's first time
//check for any pending job applications