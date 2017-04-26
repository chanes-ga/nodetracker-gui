<?php
function linkNewNode($description,$mac,$ip)
{
	$sql="insert into Devices(Description,PrimaryIP) values(\"$description\",\"$ip\")";
	print "$sql\n";
        $q=mysql_query($sql);
        $sql="select last_insert_id() as id";
        $q=mysql_query($sql);
        $r=mysql_fetch_object($q);
        $nodeID=$r->id;
        $sql="insert into Links(nodeID,MAC) values($nodeID,'$mac')";
        $q=mysql_query($sql);
        print "$sql";

}
?>

