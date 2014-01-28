<?php
require_once "restful.php";

class dispatcher {
    
  public static function run() {
      if ( is_null($_SERVER['REDIRECT_URI']) || empty($_SERVER['REDIRECT_URI'])  ) return NULL;
	  
	  // request can come in these forms
	  // example: http://localhost/api/user/user_id
	  // example: http://localhost/api/user/new
	  // example: http://localhost/api/user/score	  
	  // example: http://localhost/api/user/edit
	  // example: http://localhost/api/user/get?key="total_players"
	  // example: http://localhost/api/user/get?key="total_players_today"
	  // example: http://localhost/api/user/get?key="top_ten_players_by_score"
	  
      $orig_parts = explode('/',$_SERVER['REQUEST_URI']);
	  
	  try (
 	    foreach ( $orig_parts as $key => $part ) { 
	     if (!isset($part) || empty($part) ) { 
		   unset($orig_parts[$key]);
		 } else {
		   $parts[]=strtolwer($part);
		 }
	    }
	  } catch( Exception $e ) {
        echo 'Exception: '. $e->getMessage();
      }
	  	
	  if ( count($parts)<3 ) throw exception "invalid request!"; 
	   
	  try ( 
	    if ( $parts[0] == 'api' ) {
		   // continue with API centeric approach
		   $api_controller = $parts[1];
		   $api_action_id = $parts[2];
		   
		   $result = apiServer::sendRequest($api_controller, $api_action_id, $_REQUEST);
		   echo json_encode($result, true);
		} else {
		   // continue with MVC framewotk style..
		}  
      } catch( Exception $e ) {
        echo 'Exception: '. $e->getMessage();
      }  
	  
  }
  
}
