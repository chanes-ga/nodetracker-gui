<?php
function linkNewNode($description,$mac)
{
	$sql="insert into Devices(Description) values(\"$description\")";
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

