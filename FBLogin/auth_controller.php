<?php   
require_once(WEBCORE     . "register.inc");
require_once(WEBCORE     . "act.inc");
require_once(WEBCORE     . "user.inc");
require_once(WEBCORE 	 . "email.inc");
require_once(WEBCORE     . "logSession.inc");
require_once(LIBS        . "validation/validation.php");

class authController extends siteController {  
  private $invalid_hint = '*';
  private $img_holder   = 'v3.jpg';
  private $msg_error = "Required OR Invalid Field(s)";  
  private $msg_error_login_failed = "Invalid Login";
  private $luckyNumber = 1345;
  
  public  $doGrids = true; 

  private $fb_client_id = "client-id-444444";	
  private $fb_client_secret = "secret-1010101010101010101010101";
  private $fb_call_back = "http://www.DomainName.com/auth/efblogin";
  	
  private $domainCookie = " , '.DomainName.com'";
  ///////////////////////////////////////	
  function __construct($request) {
     parent::__construct($request);
	 $this->title = "Authentication";
	 $this->pageDescription ="Member and User Login";
  }
  
  ///////////////////////////////////////
  function indexAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
   	 // Invalid access: redirect to home page
 	 header('Location: '.SITE_URL );
	 exit;		 
  }
	   
	
  // FB Login Flow - OAuth2.0 Protocol	
  // https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/	
  // Register your app. on FB and get ID and Secret code	   
  // Invoking the login dialog
  
  // Confirming identity: 
  // - Exchanging code for an access token
  // - Inspecting access tokens  
  
  // Storing access tokens and login status:
  // - Storing access tokens
  // the app should store the token in a database along with the user_id to identify it.

  // Get User Data 
     
  ///////////////////////////////////////
  function fbloginAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if ( $this->isPost() ) {
	 
	   // Invoking the login dialog
	   $this->logger(__METHOD__ . ":line(".__LINE__ .") ". " FB Login: step1 "); 
   	   $fb_url        = "https://www.facebook.com/dialog/oauth?client_id=".$this->fb_client_id;
	   $call_back     = "&redirect_uri=".$this->fb_call_back;
	   
	   //$response_type = "&response_type=code%20token";
	   $response_type = "&response_type=code";
	   $scope         = "";
	   //$scope         = "&scope=email";
	   $state         = "&state=".sha1("hello123"."brando!");
	 
	   $fbUrl = $fb_url.$call_back.$response_type.$scope. $state;
	 
	   $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " fbURL= ". $fbUrl);
  	   Header("Location: ". $fbUrl);
	} 	 

  }
  
  ///////////////////////////////////////
  function efbloginAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 
	 $code  = $_REQUEST['code'];
	 $state = $_REQUEST['state'];
	 
	 
	 // TODO:
	 // check if state is the same 
	 
	 // Exchanging code for an access token
	 $access_token = $this->getAccessToken($code);
	 if ( !is_null($access_token) ) {
	   $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " getting app token ");
	   // get app Token 
	   $app_token = $this->getAppToken();
	   
	   // Inspecting access tokens	    
	   $finalResult = $this->inspectAccessToken( $access_token, $app_token );	
	   if ( isset($finalResult['data']) ) { 
	      // :)
	      $fb_user_arr = array (
	        'app_id'      => $finalResult['data']['app_id'],  
	        'is_valid'    => $finalResult['data']['is_valid'],
	        'application' => $finalResult['data']['application'],
			'user_id'     => $finalResult['data']['user_id'],
			'issued_at'   => $finalResult['data']['issued_at'],
			'expires_at'  => $finalResult['data']['expires_at'],
			'scopes'      => $finalResult['data']['scopes']
  	      );
          $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " data= ".print_r($fb_user_arr, true ));
		  $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " scopes= ".print_r($fb_user_arr['scopes'][0], true ));	
		  
		  // 100004099783534?fields=id,accounts,picture,link,username
		  // https://graph.facebook.com/
		  // id, name, first_name, last_name,link, username, gender, locale, age_range
		  $fbUserData = array();
		  $fbUserData = $this->getUserData($finalResult['data']['user_id'], "fields=id,link,name,picture" );
		  $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " fbUserData1=" . print_r($fbUserData, true) );
		  
		  if ( $fbUserData['id'] == $finalResult['data']['user_id']  ) { 
		    // store all user information which needs to be saved!
		    $final_user_arr = array();
		    $final_user_arr = array ( 
		      "id"            => $fbUserData['id'],
			  "name"          => $fbUserData['name'],
			  "link"          => (isset($fbUserData['link']) ? $fbUserData['link'] : ''),
			  "picture"       => $fbUserData['picture']['data']['url'],
			  "is_silhouette" => $fbUserData['picture']['data']['is_silhouette'],
			  
		    );
		    $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " Final Hura :) data= ".print_r($final_user_arr, true ));
			
			// TODO: 
			// update backend - DB
			
			// check if this user exist by comparing fb_user_id and user_id
			// if it didnot exist , it is a new FB user
			$FoundUser = user::getCore('users')->getUserByFacebookId($final_user_arr['id']);
			$this->logger(__METHOD__.  ":line(".__LINE__ .") ". " FB found=" . $FoundUser);
			if ( !$FoundUser ) { 
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " a new FB user.. :) " );
			  $userArr = array();
			  $userArr['ip_address']         = $_SERVER['REMOTE_ADDR'];
		      $userArr['status']             = 100;		
              $userArr['created_at']         = time();
              $userArr['fb_user_id']         = $final_user_arr['id'];
              $userArr['fb_user_picture']    = trim($final_user_arr['picture']);
              $userArr['fb_user_silhouette'] = $final_user_arr['is_silhouette']; 
              $userArr['fb_user_name']       = trim($final_user_arr['name']);
			  $userArr['uname']              = trim($final_user_arr['name']);	  
              $userArr['fb_user_email']      = ''; 
              $userArr['fb_user_link']       = $final_user_arr['link']; 
              $userArr['fb_exist']           = 1;	
			  $userArr['seoUrl']             = MakeSeoUrl(strtolower(trim($final_user_arr['name'])));
			  
			  $new_fb_id = user::getCore('users')->addUser("user_main" , $userArr);	
			  $bpca_user_id = $new_fb_id;
			  
			  $FoundUser = user::getCore('users')->userById($new_fb_id);


              // adding FB user image 
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") Adding user photo to DB.." );
			  $imgArr = array();
		      $imgArr['user_id'] = $new_fb_id;
		      $imgArr['ip_address'] = $_SERVER['REMOTE_ADDR'];	
		      $imgArr['user_image'] = trim($final_user_arr['picture']);;
		      $imgArr['user_caption'] = '';
		      $imgArr['user_album'] = 'main';
			  $ret=user::getCore('users')->addImage("uimage", $imgArr);
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") is photo added? ". $ret );
              unset($imgArr);
			  
			} else {
			  $bpca_user_id = $FoundUser['user_id'];
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " a returning FB user.." );
			  $userArr = array();
			  $userArr['ip_address']         = $_SERVER['REMOTE_ADDR'];
		      $userArr['status']             = 100;		
              $userArr['updated_at']         = time();
              $userArr['fb_user_picture']    = trim($final_user_arr['picture']);
              $userArr['fb_user_silhouette'] = $final_user_arr['is_silhouette']; 
              $userArr['fb_user_name']       = trim($final_user_arr['name']);
			  $userArr['uname']              = trim($final_user_arr['name']);
              $userArr['fb_user_email']      = ''; 
              $userArr['fb_user_link']       = trim($final_user_arr['link']); 
              $userArr['fb_exist']           = 1;	
			  $userArr['seoUrl']             = MakeSeoUrl(strtolower(trim($final_user_arr['name'])));
			  
			  $updated_fb_id = user::getCore('users')->update("user_main" , $userArr , "fb_user_id=".$final_user_arr['id']);				  
			}
			$bpca_seoUrl  = trim($userArr['seoUrl']);
			unset($userArr);
			unset($final_user_arr);   
			unset($fbUserData);
			unset($finalResult);	
			
					
			// redirect to user profile 
			if ( $bpca_user_id ) {
			  // TODO: do login before redirecting
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") ".  " user url to be redirected ".SITE_URL."user/" . $bpca_user_id . "/" . $bpca_seoUrl ."/" );	
			  $user_page = trim(SITE_URL."user/" . $bpca_user_id . "/" . $bpca_seoUrl ."/");
			  
			  
			  // before redirect- has to be logged in, if not already
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") ".  " Start with Login FB User.." );
			  $this->fbDoUserLogin($FoundUser);
			  $this->logger(__METHOD__.  ":line(".__LINE__ .") ".  " End with Login FB User.." );
			  ///////////////////////////////////////////////////////
			  
			  $this->Redirect($user_page);
		    } else {
              Header("Location: ". SITE_URL);
			  exit;			
			}	
			
		  } else {
		    $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " Last Error: user ids are not matched!" );
		  }	
		  	  
	   }	  
	    
	 } else{
	   $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " access denied or possible error..");
	 }

  }  	   
	     
  //////////////////////////////
  private function getAppToken(){
    $this->logger(__METHOD__.  ":line(".__LINE__ .")" );  
  
    $appTokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=".
	   $this->fb_client_id."&client_secret=".
	   $this->fb_client_secret."&grant_type=client_credentials";
	   
	$this->logger(__METHOD__ . " app token url=". $appTokenUrl);
	
    $body = $this->doCurl($appTokenUrl, true);	
	$this->logger(__METHOD__ . " app token body =". print_r($body, true ) );	
	  
	$appToken = false;  
	// output is access_token=443346762352206|Imqfg2BS3eHqtucGFPMlKOLJHjM
	if ( $body && !empty($body) && strlen($body) > 13 ) {
	  $body = trim($body );  
      $appToken = substr ( $body , 13 );
	  $this->logger(__METHOD__ . " appToken to be returned is=".$appToken );
	}  
	return $appToken;
  }
	
	
  ///////////////////////////////////////
  private function getAccessToken($code=NULL) {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if ( is_null($code) ) return false;  

     // set URL and other appropriate options
	 $fb_url = "https://graph.facebook.com/oauth/access_token?client_id=".$this->fb_client_id;
     $redirect_uri = "&redirect_uri=".$this->fb_call_back;
     $client_secret= "&client_secret=".$this->fb_client_secret;
     $fb_code = "&code=".$code;

     $fbUrl = $fb_url.	$redirect_uri. $client_secret. $fb_code;  	 
     $this->logger(__METHOD__ . " making GET call to ". $fbUrl );
	 $access_token = null;
	 $body = $this->doCurl($fbUrl);
	 
	 
	 // access_token={access-token}&expires={seconds-til-expiration}
	 $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " return from doCurl=".$body  );
	 
	 if ($body && strlen($body)>1 ) {
	   $pos1 = stripos($body, "access_token");
       if ($pos1 === false) {
         // something is wrong!!
	     $this->logger(__METHOD__ . " Something is wrong(1).." );
       } else {
	     $pos2 = stripos($body, "expires=" );
	     if ($pos2 === false) {
	       $this->logger(__METHOD__ . " Something is wrong(2).." );
	     } else {
	       $access_token = substr ( $body, strlen("access_token="),  $pos2-strlen("access_token=")-1 );
	     }
	   }
	   $this->logger(__METHOD__ . " Access_Token=". $access_token);	
	 } else {
	   // if user decline access, then, we get this msg
	   // error_reason=user_denied
       // &error=access_denied
       // &error_description=The+user+denied+your+request.
	 }   
	 return $access_token;	 
  }	
	

  // Inspecting access tokens
  ////////////////////////////////////
  private function inspectAccessToken( $access_token = NULL , $app_token=NULL ) { 
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if ( is_null($access_token) || is_null($app_token) ) return false; 

	 $fb_url      = "https://graph.facebook.com/debug_token?";
     $input_token = "input_token=".$access_token;
     $app_token   = "&access_token=".$app_token;
	 
	 $inspectUrl = $fb_url .$input_token. $app_token ;
	 $this->logger(__METHOD__ . " inspect url=". $inspectUrl );
	 
	 $body = $this->doCurl($inspectUrl, true);
     $this->logger(__METHOD__ . " inspect body=". print_r( $body, true)  );
	 return json_decode( $body, true );
  }  
  
  	
	
  //////////////////////////////////////////
  private function Redirect($url, $permanent = false) {
    $this->logger(__METHOD__.  ":line(".__LINE__ .")" );  
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }
    exit();
  }

  
  ////////////////////////////////////////////////////////////
  private function getUserData($user_id, $user_fields) { 
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if ( is_null($user_id) || is_null($user_fields) ) return false;  
	 
	 $fb_url = "https://graph.facebook.com/";
	 $items = $user_id."?".$user_fields;
	 $fbUrl = $fb_url.$items."&redirect_uri=".$this->fb_call_back;
	 
	 $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " final FB url= ".$fbUrl );
	  
     $body = $this->doCurl($fbUrl , true);
	 return json_decode( $body, true );
     
  }
    

  
  //////////////////////////////////////////  
  function hashMemberPassword($passwd=NULL) {
    $this->logger(__METHOD__.  ":line(".__LINE__ .") ");
	$salt = register::getCore('main')->getSalt();
    return sha1($passwd.$salt);  
  }
   
  //////////////////////////////////////////  
  function hashUserPassword($passwd=NULL) {
    $this->logger(__METHOD__.  ":line(".__LINE__ .") " );  
	$salt = user::getCore('users')->getSalt();
    return sha1($passwd.$salt);  
  }    
  
 
  
  /////////////////////////////////
  function doCurl( $url=NULL, $https=false) {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
     $ch = curl_init();	 
     curl_setopt($ch, CURLOPT_URL, $url);
	 curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
     curl_setopt($ch, CURLOPT_HEADER, true);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_POST, FALSE);	 
	 
	 if ( $https ) { 
	   $this->logger(__METHOD__ . " do https " );
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	   curl_setopt($ch, CURLOPT_SSLVERSION, 3);
	 }
	 
     $a = curl_exec($ch);
	 $body = "";
	 list($header, $body) = explode("\r\n\r\n", $a, 2);
     curl_close($ch);
	 return $body;

  }	
	
	
  ////////////////////////////////
  private function fbDoUserLogin($user){
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );  
	 if ( isset($user) && is_array($user) && count($user) > 0) { 
	   $this->logger(__METHOD__ ." user is a FB user.. ");
	 } else {
	   $this->logger(__METHOD__ ." user is not set! ");	
	   return false;
	 }  		
	 				
	 $hash = sha1( $user['user_id'].time().logSession::getCore('main')->getSalt() );	
	 $user['secure_id'] = $hash; 
	 $session = logSession::getCore('main')->addUserSession($user, 'u');
	 $expire=time()+3600; //in one hour
	 $expire=0; // when session expires
     setcookie('u_l_u', 'u', $expire , '/');
     setcookie('u_l_h', $hash, $expire , '/');		
	 $actSaved = act::getCore('act')->insert("online", array('action'=>'User authed.', 'ip_address' =>$_SERVER['REMOTE_ADDR'], 'created_at' => time(), 'user_id' => (int)$user['user_id']) );

     // add it to user log table
	 $logArr=array();
	 $logArr['action']="logged";	
	 $logArr['user_id']= $user['user_id'];				   
	 $logArr['ip_address']=$_SERVER['REMOTE_ADDR'];
	 $logArr['created_at']=time();
	 $logSaved = user::getCore('users')->insert("user_log", $logArr );
     $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " User logged saved or not ". $logSaved );			   
	 unset($logArr);  
	 /////////////////////////////////
  
  }	 // end of function
  //////////////////////////	
	
  ///////////////////////////////////////	
  function logoutAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 $this->signout();
  } 	
  	
  ///////////////////////////////////////	
  function forgotpwdAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
     if (!$this->isPost()) { 
		$tempId=user::getCore('users')->getTempId();
		$tempHash=user::getCore('users')->getHash($tempId);
		$this->tempId = $tempId;
		$this->tempHash = $tempHash;
		$this->view['tempId']=$tempId;
		$this->view['tempHash']=$tempHash;	
	    $actSaved = act::getCore('act')->insert("online", array('action'=>'pwd resetting started..', 'ip_address' =>$_SERVER['REMOTE_ADDR'], 'created_at' => time()) );
		$this->logger(__METHOD__ . " - Pwd resetting started - customer Temp ID is $tempId" );	
 
	 } else { 
	    $tempId=$this->getPost('tempId');
		$tempHash=$this->getPost('tempHash');
        // this tempHash from step1 should be equal to this tempHash here
		// if not, page has to be redirected to the main page.
		$salt = user::getCore('users')->getSalt();
	    if( $tempHash != sha1($tempId.$salt) ) {
 	      header('Location: '.SITE_URL );
	      exit;		
		}	
		$authArr = array(); 	
        $authArr['uemail'] = $this->getPost('uemail');
		if ( !is_valid_email($authArr['uemail']) ){ 
		  $this->setErrMsg('uemail', $this->invalid_hint);
		}

        if ( !$this->isErrMsg() ){
		  $user = user::getCore('users')->getUserByEmail($authArr['uemail']);
	      if (isset($user) && isset($user['user_id']) ) { 	
			   $hash = sha1( $user['user_id'].logSession::getCore('main')->getSalt() );	
			   $user['secure_id'] = $hash; 	
			   $code=$this->luckyNumber + (int)$user['user_id'];
			   $link = SITE_URL.  "auth/resetpwd?hash&#61;" . urlencode($hash)."&amp;code&#61;".urlencode($code).'u';			 
               // create a reset link and email it to user's email address
		       $ret = email::getCore()->resetPwd( array('email'=>$authArr['uemail'], 'link' => $link));
		       $sentOR =  ($ret? " has " : " has NOT ");
		       $this->logger(__METHOD__ . " Reset Link to user " . $sentOR . "been emailed!" ); 

			   // add it to user member table
			   $logArr=array();
			   $logArr['action']="User ". $user['uname']." ask to reset pwd.";
   			   $logArr['act_type']= 'RPASSW';		
			   $logArr['user_id']= $user['user_id'];				   
			   $logArr['ip_address']=$_SERVER['REMOTE_ADDR'];
			   $logArr['created_at']=time();
			   $logSaved = user::getCore('users')->insert("user_log", $logArr );
               $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " User logged saved or not ". $logSaved );			   
			   unset($logArr);  
			   /////////////////////////////////			   
		       $this->view['tempId']= $tempId;
	           $this->view['tempHash']=$tempHash;
			   $authArr['msg'] = 'Reset instruction sent to your email address. ';
		       $this->view['auth']=$authArr;			   
		  }	 

		  $biz = register::getCore('main')->getBizByEmail($authArr['uemail']);
	      if (isset($biz) && isset($biz['biz_id']) ) { 
			   $hash = sha1( $biz['biz_id'].logSession::getCore('main')->getSalt() );	
			   $code=$this->luckyNumber + (int)$biz['biz_id'];			   
			   $link = SITE_URL.  "auth/resetpwd?hash&#61;" . urlencode($hash)."&amp;code&#61;".urlencode($code).'m';					   
			   $biz['secure_id'] = $hash;
               // create a reset link and email it to member's email address
			   $ret = email::getCore()->resetPwd(array('email'=>$authArr['uemail'], 'link' => $link) );
		       $sentOR =  ($ret? " has " : " has NOT ");
		       $this->logger(__METHOD__ . " Reset Link to user " . $sentOR . "been emailed!" ); 

			   // add it to member log table
     		   $dbName = getDB($biz['category_id']);
			   $logArr=array();
			   $logArr['action']="Member " . $biz['title'] . " ask to reset pwd.";
			   $logArr['biz_id']= $biz['biz_id'];				   	
			   $logArr['ip_address']=$_SERVER['REMOTE_ADDR'];
			   $logArr['created_at']=time();
			   $logSaved =  member::getCore($dbName)->insert("log", $logArr );
			   $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " Member logged saved or not ". $logSaved );
			   unset($logArr);  
			   /////////////////////////////////  
		       $this->view['tempId']= $tempId;
	           $this->view['tempHash']=$tempHash;
			   $authArr['msg'] = 'Reset instruction sent to your email address. ';
		       $this->view['auth']=$authArr;				   
		  } 

		  // if not user nor not biz, the that email doe snot exist!!
		  if ( !$biz   &&   !$user   &&   !$biz['biz_id']   &&   !$user['user_id']) { 
	         $this->view['tempId']= $tempId;
	         $this->view['tempHash']=$tempHash;
		     $this->setErrMsg('msg', $this->invalid_hint);
		     $authArr['msg'] = 'This email does not exist in our database.';
		     $this->view['auth']=$authArr;			
		  }	
		} else { 
	       $this->logger(__METHOD__ ." There are input errors!");
	       $this->view['tempId']= $tempId;
           $this->view['tempHash']=$tempHash;
	       $this->setErrMsg('msg', $this->invalid_hint);
	       $authArr['msg'] = $this->msg_error;
	       $this->view['auth']=$authArr;		
		} // end-if-no-error
	 } // end-if-post 	 
  }
  	
  ///////////////////////////////////////	
  function resetpwdAction() {
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if (!$this->isPost()) {
	    $tmpCode = substr($this->getParam('code'),0,strlen($this->getParam('code'))-1);
		$tmpType = substr($this->getParam('code'),strlen($this->getParam('code'))-1); 
	   	$tmpCode = (int)$tmpCode - $this->luckyNumber;
		$tmpHash = $this->getParam('hash');
		$this->logger(__METHOD__ . " hash: ". $tmpHash . " , code:  ". $tmpCode. " type: ". $tmpType );
		 
		$salt = logSession::getCore('main')->getSalt();
	    if( $tmpHash != sha1($tmpCode.$salt) ) { 
		  $this->logger(__METHOD__ . " - Invalid Access For Resetting Pwd (1).");	
	   	   // redirect to invalid access page
  	       header('Location: '.SITE_URL );
	       exit;	
		} elseif ( $tmpType != 'u' && $tmpType != 'm') {
		   $this->logger(__METHOD__ . " - Invalid Access For Resetting Pwd (2).");
	   	   // redirect to invalid access page
  	       header('Location: '.SITE_URL );
	       exit;		
		}
	    $this->view['tempId']= $this->getParam('code');
        $this->view['tempHash']=$tmpHash;
		if ( $tmpType == 'm' ) { 
		  $bizArr = member::getCore('main')->memberById($tmpCode);
		  $this->view['name'] = $bizArr['fname'];
		}
		if ( $tmpType == 'u' ) { 
		  $userArr = user::getCore('users')->userById($tmpCode);
		  $this->view['name'] = $userArr['uname'];
		}
			
	 } else { 
	    // new password has been posted. 
	    $this->logger(__METHOD__ . '  Posting new password.' );
	    $tmpCode = substr($this->getPost('tempId'),0,strlen($this->getPost('tempId'))-1);
		$tmpType = substr($this->getPost('tempId'),strlen($this->getPost('tempId'))-1); 
	   	$tmpCode = (int)$tmpCode - $this->luckyNumber;
		$tmpHash = $this->getPost('tempHash');
		$this->logger(__METHOD__ . " hash: ". $tmpHash . " , code:  ". $tmpCode. " type: ". $tmpType );
			    
		$this->view['tempId']= $this->getPost('tempId');
        $this->view['tempHash']=$tmpHash;
				 
		$salt = logSession::getCore('main')->getSalt();
	    if( $tmpHash != sha1($tmpCode.$salt) ) { 
		  $this->logger(__METHOD__ . " - Invalid Access For posting Pwd (1).");	
	   	   // redirect to invalid access page
  	       header('Location: '.SITE_URL );
	       exit;	
		} elseif ( $tmpType != 'u' && $tmpType != 'm') {
		   $this->logger(__METHOD__ . " - Invalid Access For posting Pwd (2).");
	   	   // redirect to invalid access page
  	       header('Location: '.SITE_URL );
	       exit;		
		}
		$pass1 = $this->getPost('passwdone');
		$pass2 = $this->getPost('passwdtwo');	
		
		
		$validPwdArr = array();
		$validPwdArr = is_valid_pwd($pass1);
		if ( !$validPwdArr['valid'] ){ 
		   $this->setErrMsg('passwdone', $validPwdArr['msg']);		
		   $this->logger(__METHOD__ . " pwd(I)" . $validPwdArr['msg']);
		} else { 
		   $this->logger(__METHOD__ . " pwd(I) OK");
		}	
		
		$validPwdArr2 = array();
		$validPwdArr2 = is_valid_pwd($pass2);
		if ( !$validPwdArr2['valid'] ){ 
		   $this->setErrMsg('passwdtwo', $validPwdArr2['msg']);		
		   $this->logger(__METHOD__ . " pwd(I)" . $validPwdArr2['msg']);
		} else { 
		   $this->logger(__METHOD__ . " pwd(I) OK");
		}		
		
        if ( ($validPwdArr['valid'] && $validPwdArr2['valid']) && ( $pass1 != $pass2 ) )  { 
		  $this->setErrMsg('passwdone', "Passwords are not the same.");
		} else { 
		  $this->logger(__METHOD__ . " valid passwd " );
		}
					
		if ( $tmpType == 'm' ) { 
		  $bizArr = array(); 
		  $bizArr = member::getCore('main')->memberById($tmpCode);
		  $this->view['name'] = $bizArr['fname'];
		  $forNotifyEmail = $bizArr['email'];
		  $forNotifyName = $bizArr['title'];
		}
		if ( $tmpType == 'u' ) {
		  $userArr = array();		 
		  $userArr = user::getCore('users')->userById($tmpCode);
		  $this->view['name'] = $userArr['uname'];
		  $forNotifyEmail = $userArr['email'];
		  $forNotifyName = $userArr['uname'];
		}
		$this->logger(__METHOD__  . " cool so far." );
		// ready to update DB for new passwd
		
        if ( !$this->isErrMsg() ){
		  $this->logger(__METHOD__ . " no error 1");
		  if ( $tmpType == 'm' ) { 
		    $this->logger(__METHOD__ . " no error 11");		  
		    $updateArr=array();
			// TODO
			$updateArr['passwd']=$this->hashMemberPassword($pass1);
			$updateArr['updated_at']=time();
		    $ret = register::getCore('main')->update("biz",$updateArr, "biz_id=".$bizArr['biz_id']  );
			if ( $ret ) { 
			  $this->logger(__METHOD__ . " - member reset their password successfully." );
			  // send notofication to member to this email 
			  $ret2 = email::getCore()->pwdIsReset($forNotifyEmail, $forNotifyName);
			} else { 
			  $this->logger(__METHOD__ . " - member coul not reset password, some thing might be wrong." );
			}
	        $dbName = getDB($bizArr['category_id']);
	        $logArr=array();
		    $logArr['action']="Member " . $bizArr['title'] . " password has been reset.";
		    $logArr['biz_id']= $bizArr['biz_id'];				   	
		    $logArr['ip_address']=$_SERVER['REMOTE_ADDR'];
		    $logArr['created_at']=time();
		    $logSaved =  member::getCore($dbName)->insert("log", $logArr );
		    $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " Member logged saved or not ". $logSaved );
		    unset($logArr); 			
		    $this->view['msg'] = 'Your password has been reset successfully.';			
          }
		  if ( $tmpType == 'u' ) { 
		    $this->logger(__METHOD__ . " no error 22");		  
		    $updateArr=array();
			// TODO
			$updateArr['passwd']=$this->hashUserPassword($pass1);			
			$updateArr['updated_at']=time();
		    $ret = user::getCore('users')->update("user_main", $updateArr, "user_id=".$userArr['user_id'] );
			if ( $ret ) { 
			  $this->logger(__METHOD__ . " - user reset their password successfully." );
			  // send notofication to member to this email 
			  $ret2 = email::getCore()->pwdIsReset($forNotifyEmail, $forNotifyName);
			} else { 
			  $this->logger(__METHOD__ . " - user could not reset password, some thing might be wrong." );
			}			
			
	        // add it to user member table
	        $logArr=array();
		    $logArr['action']="User ". $userArr['uname']." password has been reset.";	
		    $logArr['user_id']= $userArr['user_id'];	
	        $logArr['act_type']= 'PRESET';				   
		    $logArr['ip_address']=$_SERVER['REMOTE_ADDR'];
		    $logArr['created_at']=time();
		    $logSaved = user::getCore('users')->insert("user_log", $logArr );
            $this->logger(__METHOD__.  ":line(".__LINE__ .") ". " User logged saved or not ". $logSaved );			   
		    unset($logArr);  
		    /////////////////////////////////	
		 			
		    $this->view['msg'] = 'Your password has been reset successfully.';			
          }		  
	    } else {
		  $this->setErrMsg('errmsg', $this->invalid_hint. ' ' .$this->msg_error);				
		}
				
	 }	 
	 
  }


  ////////////////////////////////////////////////
  private function signout() { 
     $this->logger(__METHOD__.  ":line(".__LINE__ .")" );
	 if ( isset($_COOKIE['u_l_u'])  && isset($_COOKIE['u_l_h'])  ) { 
       $ret = logSession::getCore('main')->deleteFromLogSession($_COOKIE['u_l_h'], $_COOKIE['u_l_u']);	
       $actSaved = act::getCore('act')->insert("online", array('action'=>'logout.', 'ip_address' =>$_SERVER['REMOTE_ADDR'], 'updated_at' => time()) );
		 
	   $expire=time()-60*60*60*60*60;
	   setcookie('u_l_u', false, $expire , '/');
	   setcookie('u_l_h', false, $expire , '/');
	   $this->logger(__METHOD__.  ":line(".__LINE__ .") ". 'user cookie is deleted. ' );	  
	 } else { 
   	   // redirect to home page/member page
 	   header("Location: /home" );
	   exit;		 
	 }

  }
  
}  // end of user controller class
