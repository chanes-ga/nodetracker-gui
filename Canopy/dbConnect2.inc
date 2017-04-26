<?php
$host="localhost";
$sock="/tmp/mysql.sock";
$port="3306";
$user='tech';
$password='alef314';
$db='nt_icnet';
$fullhost=$host.":".$port.":".$sock;

mysql_connect($fullhost,$user,$password);
mysql_select_db($db);

?>
<?
function getRefID($nodeID,$shortoid){
	$sql="select refid from OID_Instances where nodeID=$nodeID and shortoid=$shortoid";
	#echo $sql;
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	return $r->refid;
}

?>
