<?php
class userController {

  public function userByIDAction ($user_id=NULL) { 
    if ( is_null($user_id) || !(int)$user_id ) return NULL;
    $userArr = userDB::getCore()->select('kixeye.users', "user_id=".$user_id);
    return json_encode($userArr, true);     
  }

  public function addUserAction ($userArr) { 
    if ( is_null($userArr) || !is_array($userArr) ) return NULL;  
    $last_user_id = userDB::getCore()->insert('kixeye.users', $userArr);
    return $last_user_id;     
  }
  
  public function editUserAction ($user_id, $userArr) { 
    if ( is_null($userArr) || !is_array($userArr) ) return NULL;  
    $isUpdated = userDB::getCore()->update('kixeye.users', $userArr, "user_id=".$user_id);
    return $isUpdated;     
  }
    
  public function saveScoreAction ( $params ){
    if ( is_null($params) || !is_array($params) ) return NULL;  
    $last_score_id = userDB::getCore()->insert('kixeye.users', $params);
    return $last_score_id;       
  }
  
  public function searchUsersForAction ( $key ){
    
  }
    
}
