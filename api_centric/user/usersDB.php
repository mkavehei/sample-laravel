<?php

////////////////////////////////////////
abstract class dbBase {   
   private    $dbHandler;
   protected  $dbname;
   protected  $username;
   protected  $password;
   protected  $hostname;
   
   ////////////////////////////////////
   public function connect(){	
      error_log(__METHOD__);
	  $this->dbHandler = new mysqli($this->hostname, $this->username, $this->password , $this->dbname);
	  if ($this->dbHandler->connect_error) {
	    error_log(__METHOD__. " ERROR: ".$this->dbHandler->connect_error );
        die('Connection Error (' .  $this->dbHandler->connect_error . ') ');
	  } 
	  return $this->dbHandler;
   }	  
	  	
   ////////////////////////////////////
   public function filter($item=NULL) { 
	  if ( $item ) { 
        $item = $this->dbHandler->real_escape_string(trim($item));
      }
	  return $item;	
   }

	
   ///////////////////////////////////////
   public function insert($tableName=NULL, $colsArray=NULL) {
      error_log(__METHOD__);	
	  if ( !isset($tableName) || !isset($colsArray) || empty($colsArray) || !is_array($colsArray) ) { 
        error_log(__METHOD__ . " ERROR: Something is wrong with $tableName OR colsArray ");		
		return false;
	  }
	  //TODO: check if table exist! 		
	  $col=" ( ";
	  $val=" VALUES ( "; 
	  foreach( $colsArray as $key => $value ) { 
		  $col .= $key . " , ";	
		  if (is_null($value)) {
		    $val .= " NULL , ";
		  } else {
		    $colsArray[$key] = $this->filter($value);
		    $val .= "'".$colsArray[$key] . "' , ";
		  }	  
	  }
	  $col = substr($col, 0, strlen($col)-2) . " ) ";
	  $val = substr($val, 0, strlen($val)-2) . " ) ";
	  $sql = "INSERT INTO " . $tableName . $col . $val;
   
  	  $ret = $this->dbHandler->query($sql);
   	  if ($ret) {  
	    $last_id=$this->dbHandler->insert_id;
        error_log(__METHOD__. " - Last Record ID - ".$last_id);			  
		return $last_id;	
	  } else { 
        error_log(__METHOD__. " - ERROR - ".$sql." - ".print_r($this->dbHandler->error,true) );	
		return false;		  
      }		
    }	
	
    ///////////////////////////////////////
	public function select($tableName=NULL, $where=NULL, $limit=1, $cols="*", $order=NULL ) { 
        error_log(__METHOD__);	
		if ( !isset($tableName) ) { 
          error_log(__METHOD__ . " ERROR in Table Name. ".$tableName );		  
		  return false;
		}  
		$sql = "SELECT ".$cols." FROM " . $tableName;
		if(isset($where)) 
		   $sql .= " WHERE " . $where;
		if(isset($order)) 
		   $sql .= " ORDER BY  " . $order;		   
		if(isset($limit))    
	       $sql .= " LIMIT " . $limit;
		   		   
		$result = $this->dbHandler->query($sql);

   	    if ($result && $result->num_rows >0) {  
		  $rows = array();
          while ($row = $result->fetch_assoc()) {
		    if ( $limit==1 ) $rows=$row; 
            else $rows[]=$row;
          }
          error_log(__METHOD__. " - Number of returned rows - ".$result->num_rows );
		  return $rows;	
		} else { 
		  if ( $this->dbHandler->error ) { 
            error_log(__METHOD__. " - ERROR - ".$sql);
		    error_log(__METHOD__. " - ERROR - ".$this->dbHandler->error );	
		  } else { 
            error_log(__METHOD__. " - NOT ERROR - FOUND ".$result->num_rows);			  	
		  }	  	
		  return false;		  
		}			
    }  
		
    ///////////////////////////////////////
	public function update($tableName=NULL, $colsArray=NULL, $where=NULL, $limit=1 ) { 
        error_log(__METHOD__);	
		if ( !isset($tableName) || !isset($colsArray) || empty($colsArray) || !isset($where) ) { 
		  error_log(__METHOD__ . " something was wrong with where or cols.." );	
		  return false;
		}

		//TODO: check if table exist! 		
		$set=" SET ";
		foreach( $colsArray as $key => $value ) { 
		  if (isset($key) && isset($value) ) { 
		    $colsArray[$key] = $this->filter($value);
		    // check if value is integer or char
		    if ( is_int($colsArray[$key]) )
              $set .= "`".$key . "` = "  . $value ." , ";	  
		    else 
              $set .= "`".$key . "` = '" . $value ."' , ";	  
		   }
		}
		$set = substr($set, 0, strlen($set)-2);
		if ( $limit != NULL ) {  
		  $sql = "UPDATE " . $tableName . $set . " WHERE " . $where . " LIMIT ".$limit;
		} else { 
		  $sql = "UPDATE " . $tableName . $set . " WHERE " . $where;
		}
	  
		error_log(__METHOD__. " sql: " . $sql );		
		$ret = $this->dbHandler->query($sql);

   	    if ($ret) {  
	      $affectedRows=$this->dbHandler->affected_rows;
          error_log(__METHOD__. " - Number of affected rows - ".$affectedRows);			  
		  return $affectedRows;	
		} else { 
          error_log(__METHOD__. " - ERROR - ".$sql);	
		  error_log(__METHOD__. " - ERROR - ".$this->dbHandler->error );
		  return false;		  
		}
    } 

   
} /* end of abstract DB class */
////////////////////////////////





////////////////////////////////////////
class userDB extends dbBase {
   private static $_instanceCore = null;
   private static $_dbname    = 'kixeye';
   private static $_username  = 'xxxxx_secret';
   private static $_password  = 'xxxxx_secret';
   private static $_hostname  = 'localhost';	   
      
   /* Using private clone function (prevents cloning) */
   private function __clone() {}   

   /* Using private constructor (prevents direct instantiation) */
   private function __construct() {
      $this->dbName   = self::$_dbname;
      $this->username = self::$_username;
      $this->password = self::$_password;
      $this->hostname = self::$_hostname;  
	  return $this->connect();
   }    
	    
   //////////////////////////////////////////////	
   public static function &getCore () { 
     if ( !(self::$_instanceCore instanceof self) || is_null(self::$_instanceCore) ) {
       self::$_instanceCore = new self();
     }
	 return self::$_instanceCore;
   }
}
