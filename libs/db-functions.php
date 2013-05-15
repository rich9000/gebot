<?php

// depending on db type change this stuff
// isn't really fully implemented



function insert($keyvaluearray,$table,$debug = false){
	
	$column = '';
	$values = '';

	foreach($keyvaluearray as $key => $val){
    	if($key != '' ){
	
	    	$column .= ", ".$key;
	    	$values .= ", '".trim(addslashes($val))."'";

    	}
    }
    $column = trim($column, ',');
    $values= trim($values, ',');
    
    $query = "insert into $table ($column) values ($values)";   
    
	$sqli = mysql_query($query);
	
	
	if($debug){
		
		echo "<textarea rows='25' cols='60'>".$query."</textarea>";
		
		
	}
	
	
	
    /*	 
    
	if($table == "dbmail.dbmail_aliases"){
    	echo "<textarea rows='25' cols='60'>".$query."</textarea>";
    	exit;
    }
    
   
     
       
    if($table == "users_privs"){
    	echo "<textarea rows='25' cols='60'>users_privs query:".$query."</textarea>";
    //  exit;
    }
    if($table == "users_domains_xref"){
    	echo "<textarea rows='25' cols='60'>users_privs query:".$query."</textarea>";
    //  exit;
    }
    */
    
    $insertid = mysql_insert_id();
    
    return $insertid;

}




function update($updatearray,$wherearray,$table,$debug = false){
	
	
		$pairs = '';
			foreach($wherearray as $key => $val){
        	if($key == ''){
        	} else {
        	$pairs .= $key." = '".$val."' and ";
        	}
        }
        $wherepairs = trim($pairs, " and ");
        foreach($updatearray as $key => $val){
        	if($key == ''){
        		
        	} else {
        		
        		$val = addslashes(trim($val));
	        	
        		$query = "update $table set $key = '$val' where $wherepairs";
        		
        		if($debug) echo $query."\n";
        			        	
				$sql_update = mysql_query($query);
        	}
        }
}

?>