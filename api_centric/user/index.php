<?php

// model
require_once 'usersDB.php';

// controller
require_once 'userController.php';

$actionsArr = array (
  'new'   => 'addUser',
  'edit'  => 'editUser',
  'score' => 'saveScore',
  'get'   => 'searchUsersFor', 
);
   
try {
   $params = $_REQUEST;
		 	
   $controller = new userController();
   if ( (int)$params['action_id'] ) { 
      $action = 'userByIDAction';
   } else { 	  
      $action = $actionsArr[$params['action_id']].'Action';
   }	  

   $controller = new userControoler();
   $result['data'] = $controller->$action($params);
   $result['success'] = true;
    
} catch( Exception $e ) {
   $result = array();
   $result['success'] = false;
   $result['errormsg'] = $e->getMessage();
}

return $result;
