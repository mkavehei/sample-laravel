<?php
require_once 'user/usersDB.php';

/////////////////////////////
for ( $i=1; $i<=100; $i++ ) {
  
  $tmpUserArr = array ( 
    'user_fname' => "user_".$i,
    'user_lname' => "none",
    'user_name'  => "uname_".$i,
    'user_email' => "none".$i."@none.com",   
    'user_passwd'    => "none",         
    'fb_user_id'     => 1000032+$i, 
    'fb_user_country' => "us",
    'fb_user_locale'  => "en_US",   
    'fb_user_min_age' => 20,
  );
  
  $ret = userDB::getCore()->insert('kixeye.users', $tmpUserArr);
  echo $ret;
}  

$retArr = userDB::getCore()->select('kixeye.users', NULL, 100  );

var_dump($retArr);