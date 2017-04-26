<?php
include("dbConnect.inc");
$mode=MCRYPT_MODE_ECB;
$cipher = MCRYPT_RIJNDAEL_256;
#$cipher = MCRYPT_TripleDES;
#$cipher = MCRYPT_SERPENT;
$key = "iloveyou4ever";

$fieldList="Description";
$keyfieldList="MAC";
$keyfieldTypeList="1";

$direction=0;

test($key);




#cryptTable($direction,"Devices",$keyfieldList, $keyfieldTypeList, $fieldList, $cipher, $mode, $key);
function test($key){
	$nkey=GetNKey($key);

	$sql="select address from IP order by address";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	while($r){
		$e=xorIt($nkey,"$r->address");
		$d=xorIt($nkey,$e);
		print $r->address."  $e $d<br>";
		$r=mysql_fetch_object($q);
	}

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

function xorIt($key,$value){
	return gmp_strval(gmp_xor($key,$value));

}
function cryptTable($direction,$table,$keyfieldList,$keyfieldTypeList, $fieldList,$cipher,$mode,$key){
	$keyfields=split(",",$keyfieldList);
	$keyfieldtypes=split(",",$keyfieldTypeList);
	$fields=split(",",$fieldList);

	$sql="select $keyfieldList,$fieldList from $table";
	print $sql;
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	while($r){ 
		$sql="update $table set ";
		foreach ($fields as $f){
			$e=cryptText($direction,$r->$f,$cipher,$mode,$key);
			$e=addslashes($e);
			$sql=$sql."$f=\"$e\",";
		}
		$sql=substr($sql,0,strlen($sql)-1);
		$where=" where ";
		$i=0;
		foreach ($keyfields as $kf){
			$delimiter=getDelimiter($keyfieldtypes[$i]);
			$where=$where."$kf=".$delimiter.$r->$kf.$delimiter;
			$i++;
		}
		$sql=$sql.$where;
		print "<br>$sql<br>";		
		$q2=mysql_query($sql);
	        $r=mysql_fetch_object($q);
	} 	
}
function getDelimiter($type){
	if ($type=="1"){
		return "\"";
	}else{
		return "";
	}
}


function cryptText($direction,$text,$cipher,$mode,$key){
	$block_size = mcrypt_get_block_size ($cipher,$mode);

	#print "$cipher\n$mode\n";
	#print "Block Size: $block_size\n";

	$iv = mcrypt_create_iv ($block_size, MCRYPT_RAND);
	if ($direction==1){
		#print "Encrypting...";
		$rtext = mcrypt_encrypt ($cipher, $key, $text, $mode, $iv);
	}else{
		#print "Decrypting...";
		$rtext = mcrypt_decrypt($cipher,$key,$text,$mode,$iv);

	}
	#print strlen($crypttext)."\n";
	return $rtext;
}

?>

