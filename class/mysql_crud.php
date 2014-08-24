<?php
/*
 * @Author Rory Standley <rorystandley@gmail.com>
 * @Version 1.3
 * @Package Database
 */
 class Database{
	/* 
	 * Create variables for credentials to MySQL database
	 * The variables have been declared as private. This
	 * means that they will only be available with the 
	 * Database class
	 */
	private $db_host = "localhost";  // Change as required
	private $db_user = "root";  // Change as required
	private $db_pass = "";  // Change as required
	private $db_name = "cdcol";	// Change as required
	
	/*
	 * Extra variables that are required by other function such as boolean con variable
	 */
	private $con = false; // Check to see if the connection is active
	private $result = array(); // Any results from a query will be stored here
    private $myQuery = "";// used for debugging process with SQL return
    private $numResults = "";// used for returning the number of rows
	private $myconn;
	// Function to make connection to database
	public function connect(){
		if(!$this->con){
			$this->myconn = mysqli_connect($this->db_host,$this->db_user,$this->db_pass);  // mysql_connect() with variables defined at the start of Database class
            if($this->myconn){
            	$seldb = mysqli_select_db($this->myconn,$this->db_name); // Credentials have been pass through mysql_connect() now select the database
                if($seldb){
                	$this->con = true;
                    return true;  // Connection has been made return TRUE
                }
                else{
                	array_push($this->result,mysqli_errno($this->myconn)); 
                    return false;  // Problem selecting database return FALSE
                }  
            }
            else{
            	array_push($this->result,mysqli_errno($this->myconn));
                return false; // Problem connecting return FALSE
            }  
        }else{  
            return true; // Connection has already been made return TRUE 
        }  	
	}
	
	// Function to disconnect from the database
    public function disconnect(){
    	// If there is a connection to the database
    	if($this->con){
    		// We have found a connection, try to close it
    		if(@mysqli_close()){
    			// We have successfully closed the connection, set the connection variable to false
    			$this->con = false;
				// Return true tjat we have closed the connection
				return true;
			}else{
				// We could not close the connection, return false
				return false;
			}
		}
    }
	
	public function sql($sql){
		$query = @mysqli_query($this->myconn,$sql);
        $this->myQuery = $sql; // Pass back the SQL
		if($query){
			// If the query returns >= 1 assign the number of rows to numResults
			$this->numResults = $this->myconn->affected_rows;
			// Loop through the query results by the number of rows returned
			for($i = 0; $i < $this->numResults; $i++){
				$r = mysqli_fetch_array($query);
               	$key = array_keys($r);
               	for($x = 0; $x < count($key); $x++){
               		// Sanitizes keys so only alphavalues are allowed
                   	if(!is_int($key[$x])){
                   		if($this->myconn->affected_rows>= 1){
                   			$this->result[$i][$key[$x]] = utf8_encode($r[$key[$x]]);
						}else{
							$this->result = null;
						}
					}
				}
			}
				$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='OK';
				$this->result['Records']=$a;
			return true; // Query was successful
		}else{
			array_push($this->result,(mysqli_errno($this->myconn)));
				$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
			return false; // No rows where returned
		}
	}
	
	// Function to SELECT from the database
	public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null){
		// Create query from the variables passed to the function
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null){
			$q .= ' JOIN '.$join;
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        if($limit != null){
            $q .= ' LIMIT '.$limit;
        }
        
        $this->myQuery = $q; // Pass back the SQL
        
		// Check to see if the table exists
        if($this->tableExists($table)){
        	// The table exists, run the query
        	
        	
        	$query = @mysqli_query($this->myconn,$q);
			if($query){
				// If the query returns >= 1 assign the number of rows to numResults
				$this->numResults =$this->myconn->affected_rows;
				// Loop through the query results by the number of rows returned
				for($i = 0; $i < $this->numResults; $i++){
					$r = mysqli_fetch_array($query);
                	$key = array_keys($r);
                	for($x = 0; $x < count($key); $x++){
                		// Sanitizes keys so only alphavalues are allowed
                    	if(!is_int($key[$x])){
                    		if(mysqli_num_rows($query) >= 1){
                    			$this->result[$i][$key[$x]] =utf8_encode($r[$key[$x]]);
							}else{
								$this->result = null;
							}
						}
					}
				}
				
				$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='OK';
				$this->result['Records']=$a;				
				return true; // Query was successful
			}else{
				array_push($this->result,mysqli_errno($this->myconn));
				$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
				return false; // No rows where returned
			}
      	}else{
      		$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
      		return false; // Table does not exist
    	}
    }
	
	// Function to insert into the database
    public function insert($table,$params=array()){
    	// Check to see if the table exists
    	 if($this->tableExists($table)){
    	 	$sql='INSERT INTO `'.$table.'` (`'.implode('`, `',array_keys($params)).'`) VALUES ("' . implode('", "', $params) . '")';
    	 	
            $this->myQuery = $sql; // Pass back the SQL
            // Make the query to insert to the database
            if($ins =mysqli_query($this->myconn,$sql)){
            	
            	array_push($this->result,$this->myconn->affected_rows);
            	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='OK';
				$this->result['Records']=$a;
            	 return false;               
            }else{
            	//array_push($this->result,mysqli_errno($this->myconn));
            	array_push($this->result,$this->checkError(mysqli_errno($this->myconn)));
            	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
				
                return false; // The data has not been inserted
            }
        }else{
        	
        	return false; // Table does not exist
        }
    }
	
private function checkError($errorCode){
	
switch ($errorCode) {
    case 1062:
        return "El registro ya existe";
        break;
    case 1136:
        return "Las columnas no coinciden";
        break;
    case 1054:
        return "Columna Desconocida";
        break;
        default:
        return $errorCode;	
		}
}	
	
	//Function to delete table or row(s) from database
    public function delete($table,$where = null){
    	// Check to see if table exists
    
    	 if($this->tableExists($table)){
    	 	// The table exists check to see if we are deleting rows or table
    	 	if($where == null){
                $delete = 'DELETE from '.$table; // Create query to delete table
            }else{
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; // Create query to delete rows
            }
          
            // Submit query to database
            if($del =mysqli_query($this->myconn,$delete)){
          		//array_push($this->result,"ok");
            	array_push($this->result,$this->myconn->affected_rows);
            	
                $this->myQuery = $delete; // Pass back the SQL
            	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='OK';
				$this->result['Records']=$a;    
                return true; // The query exectued correctly
            }else{
            	array_push($this->result,$this->checkError(mysqli_error($this->myconn)));
	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;             
               	return false; // The query did not execute correctly
            }
        }else{
        	array_push($this->result,$this->checkError(mysqli_error($this->myconn)));
        	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
            return false; // The table does not exist
        }
    }
	
	// Function to update row in database
    public function update($table,$params=array(),$where){
    	// Check to see if table exists
    	if($this->tableExists($table)){
    		// Create Array to hold all the columns to update
            $args=array();
			foreach($params as $field=>$value){
				// Seperate each column out with it's corresponding value
				$args[]=$field.'="'.$value.'"';
			}
			// Create the query
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;
			
			// Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            if($query = @mysqli_query($this->myconn,$sql)){
            	//array_push($this->result,"update:ok");
            	array_push($this->result,$this->myconn->affected_rows);
            		$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='OK';
				$this->result['Records']=$a;
            	return true; // Update has been successful
            }else{
            	array_push($this->result,$this->checkError(mysqli_errno($this->myconn)));
            	$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
                return false; // Update has not been successful
            }
        }else{
        		$a=array();
				$a=$this->result;
				$this->result=array();
				$this->result['Result']='ERROR';
				$this->result['Message']=$a;
            return false; // The table does not exist
        }
    }
	
	// Private function to check if table exists for use with queries
	private function tableExists($table){
	
		$tablesInDb = mysqli_query($this->myconn,'SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        if($tablesInDb){
        	if(mysqli_num_rows($tablesInDb)==1){
                return true; // The table exists
            }else{
            	//array_push($this->result,"900811");
            	//array_push($this->result,mysqli_errno($this->myconn));
            	array_push($this->result,"Table is loose");
            	
            	
                return false; // The table does not exist
            }
        }
        
    }
	
	// Public function to return the data to the user
    public function getResult($json=false){
		if($json) {
       $val = json_encode($this->result);
       }else{
       	$val=$this->result;
       	}
       return $val; 
    }

    //Pass the SQL back for debugging
    public function getSql(){
        $val = $this->myQuery;
        $this->myQuery = array();
        echo $val;
    }

    //Pass the number of rows back
    public function numRows(){
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }

    // Escape your string
    public function escapeString($data){
        return mysqli_real_escape_string($data);
    }
} 
?>
