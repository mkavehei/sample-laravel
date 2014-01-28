<?php
abstract class RESTful {
    
    protected $uri;
    protected $curl;
    protected $timeout = 30;
    protected $params = NULL;
	protected $isPost = false;
	
    public function __construct ($uri = null){
        $this->uri = $uri;
    }
	
    public function getUri(){
        return $this->uri;
    }
	
    public function getTimeout(){
        return $this->timeout;
    }	
	
    public function getCurl(){
        if (!$this->curl){
            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_URL, $this->getUri());
            curl_setopt($this->curl, CURLOPT_HEADER, 0);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->getTimeout());
            curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->getTimeout());
            if ($this->isPost) { 
              curl_setopt($this->curl,CURLOPT_POST,true); 
			}  
            curl_setopt($this->curl,CURLOPT_POSTFIELDS,$this->params); 
        }
        return $this->curl;
    }
    
    public function execCurl(){
      return curl_exec($this->getCurl());
    }
		
}	

/////////////////////////////
class apiServer extends RESTful {

  public static function sendRequest( $api_controller, $api_action_id,  $params=NULL ){
     switch ($api_controller) {
	 
        case 'user':
           $this->uri = 'http://localhost/user/'.$api_action_id;
		   
		   // this below line is for front end controller to identify action or id
		   // if it was integer, it is an id, otherwise, it is an action 
		   $params['action_id'] =  $api_action_id;
		   
		   $this->params = $params; 
		   
		   if ( $api_action_id == 'new' || $api_action_id == 'edit' || $api_action_id == 'score') { 
		     $this->isPost = true;	
		   }
		   if ( (int)$api_action_id   )  {
		     $this->params = array('user_id' => $api_action_id );
		   }	
		   return $this->execCurl(); 	
		   break;
		   
        default:
           echo "TODO later"; 
	 }
  }
  
}

