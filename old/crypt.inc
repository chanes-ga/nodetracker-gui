<?php
function verifyKey($key){
	$hash=md5($key);
	$sql="select state from EncryptionStatus where secret='$hash'";
	$q=mysql_query($sql);	
	$c=mysql_num_rows($q);
	$ret=$c;
	if ($c>0){
		$r=mysql_fetch_object($q);
		$ret=$ret+2*$r->state;
	}
	return $ret;
}
function GetNKey($key){
        $nkey=0;
        for ($i=0;$i<4;$i++){
                $t=ord(substr($key,$i,1))*pow(2,$i*8);
                $nkey=$nkey+$t;
                #print "$t\t$nkey<br>";
        }
        return "$nkey";
}
        
function doxor($key,$value){
        return gmp_strval(gmp_xor($key,$value));                
}

function cryptAll($direction,$key,$db,$user){

	$mode=MCRYPT_MODE_ECB;	
	$cipher = MCRYPT_TripleDES;

	$startTime=time();
	#$e=cryptText(1,"i love you",$cipher,$mode,$key);
	#print $e."<br>";
	#$d=cryptText(0,$e,$cipher,$mode,$key);
	#print $d."<br>";

	#return;

	#Devices table
	$table="Devices";
	$fieldList="MAC,PrimaryIP,public,Description,osguess,tcpsequence,ratingcomment";
	$fieldTypeList="1,1,1,1,1,1,1";
	$keyfieldList="MAC";
	$keyfieldTypeList="1";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

        #IFDescriptions
	$table="IFDescriptions";
        $fieldList="ip,description,physAddress";      
	$fieldTypeList="1,1,1";
        $keyfieldList="ip,ifnum";    
        $keyfieldTypeList="1,0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);
                
        #IP
	$table="IP";
        $fieldList="DNS,MAC,IP,address";
	$fieldTypeList="1,1,1,0";
        $keyfieldList="IP";
        $keyfieldTypeList="1";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

	#IPAllocations Table
	$table="IPAllocations";
        $fieldList="description,notes,network";
	$fieldTypeList="1,1,0";
        $keyfieldList="network,mask";
        $keyfieldTypeList="0,0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);


        #IPBlocks
	$table="IPBlocks";
        $fieldList="network";
        $fieldTypeList="0";
        $keyfieldList="network";
        $keyfieldTypeList="0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);


        #OID_Instances
        $table="OID_Instances";
        $fieldList="MAC,lasterror";
        $fieldTypeList="1,1";
        $keyfieldList="refid";
        $keyfieldTypeList="0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

        #Port
        $table="Port";
        $fieldList="MAC,Switch";
        $fieldTypeList="1,1";
        $keyfieldList="MAC,Switch,ifnum";  
        $keyfieldTypeList="1,1,0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

	#RouterIFs
        $table="RouterIFs";
        $fieldList="ip,description";
        $fieldTypeList="1,1";
        $keyfieldList="ip,ifnum";
        $keyfieldTypeList="1,0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

        #RouterIPs
        $table="RouterIPs";
        $fieldList="ip,address,network";   
        $fieldTypeList="1,0,0";
        $keyfieldList="ip,address,network";
        $keyfieldTypeList="1,0,0";
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);


        #nmap
        $table="nmap";
        $fieldList="Port,Service,MAC";
        $fieldTypeList="0,1,1";
        $keyfieldList="Port,MAC";
        $keyfieldTypeList="0,1";
        cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);

        #nmap_changelog
        $table="nmap_changelog";
        $fieldList="MAC,changedescription";
        $fieldTypeList="1,1";
        $keyfieldList="MAC,timestamp";             
        $keyfieldTypeList="1,0";  
        #cryptTable($direction,$table,$keyfieldList, $keyfieldTypeList, $fieldList, $fieldTypeList, $cipher, $mode, $key);



	$totalTime=time()-$startTime;
	print "<br>Action took $totalTime seconds.<br>";

	$sql="update EncryptionStatus set state=$direction";
	$q=mysql_query($sql);
}


function cryptTable($direction,$table,$keyfieldList,$keyfieldTypeList, $fieldList, $fieldTypeList, $cipher,$mode,$key){
	print "<font size=-1>Working on $table<br>";
	#get the numerical key used for ciphering numerical data
	$nkey=GetNKey($key);
	$failures=0;
	$savedsql=Array();
	$keyfields=split(",",$keyfieldList);
	$keyfieldtypes=split(",",$keyfieldTypeList);
	$fields=split(",",$fieldList);
	$fieldtypes=split(",",$fieldTypeList);

	$sql="select $keyfieldList,$fieldList from $table";
	#print $sql;
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	while($r){ 
		#first build update part of sql statement
		$sql="update $table set ";
		$i=0;
		foreach ($fields as $f){
			$delimiter=getDelimiter($fieldtypes[$i]);
			if ($fieldtypes[$i]==0){
				#then need to cipher a numerical value; direction doesn't matter here
				$v=$r->$f;
				$e=doxor($nkey,"$v");
			}else{
				#need to cipher text data here
				$e=cryptText($direction,$r->$f,$cipher,$mode,$key);
				if ($direction==0){
					#when decrypting trim off whitespace
					$e=trim($e);
				}
			}
			$sql=$sql."$f=".$delimiter.$e.$delimiter.",";
			$i++;
		}
		$sql=substr($sql,0,strlen($sql)-1);


		#secondly build the where condition part of the sql statement
		$where=" where ";
		$i=0;
		foreach ($keyfields as $kf){
			$delimiter=getDelimiter($keyfieldtypes[$i]);				
			$keyfield=$r->$kf;
			$where=$where."$kf=".$delimiter.$keyfield.$delimiter." and ";
			$i++;
		}
		$where=substr($where,0,strlen($where)-5);	#trim off last " and "
		$sql=$sql.$where;		
		#print $sql."<br>";
		$q2=mysql_query($sql);

		if (mysql_errno()!=0){
			print "$sql<br>";

		}
	        $r=mysql_fetch_object($q);
	} 	

}
function getDelimiter($type){
	if ($type=="1"){
		return "'";
	}else{
		return "";
	}
}


function cryptText($direction,$text,$cipher,$mode,$key){
	$block_size = mcrypt_get_block_size ($cipher,$mode);
	$text=trim($text);
	#print "$key\n$cipher\n$mode\n";
	#print "Block Size: $block_size\n";

	$iv = mcrypt_create_iv ($block_size, MCRYPT_RAND);
	if ($direction==1){
		#encrypt then encode
		$rtext = mcrypt_encrypt ($cipher, $key, $text, $mode, $iv);
		
		#print "Encrypts to $rtext\n";
		$tmp = base64_encode($rtext);
		$rtext=$tmp;
		#print "Encrypting $text length ".strlen($text)." to $rtext length ".strlen($rtext)."<br>";

	}else{
		#decode then decrypt
		$tmp = base64_decode($text);
		$rtext = mcrypt_decrypt($cipher,$key,$tmp,$mode,$iv);

		#print "Decoded:$tmp<br>Decrypted: $rtext<br>";

		#print "Decrypting $text length ".strlen($text)." to $rtext length ".strlen($rtext)."<br>";

	}
	return $rtext;
}
?>
