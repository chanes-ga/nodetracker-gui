<html>
<script>
        function submitForm(v){
                myform=document.form2;
                myform.action.value=v;
                myform.submit();
        }
</script>

<?php
	include("dbConnect.inc");
	include("linkNode.inc");
	if ($linksingle==""){
		list($mac,$ip)=split("\|",$macip,2);
	}
	print "other: $other $macip m: $mac i: $ip<br>";
	if ($action==1){
		if(strlen($other)>0){
			linkNewNode($other,$mac,$ip);
		}else{
			$sql="insert into Links(nodeID,MAC) values($nodeID,'$mac')";
			$q=mysql_query($sql);
			print "$sql<br>";
		}
		
	}
	if ($action==2){
		$sql="delete from Links where MAC='$mac'";
		$q=mysql_query($sql);
		print $sql;
	}
	if ($action==3){
		$sql="delete from Devices where nodeID=$nodeID";
		$q=mysql_query($sql);
		print $sql;
	}

	print "<form name=form2>";

	if($linksingle==""){

	$sql="select IP.MAC,IP from IP left join Links on Links.MAC=IP.MAC where Links.MAC is null and IP.MAC<>'' union
		select Port.MAC,'' as IP from Port left join IP using (MAC) where IP.MAC is null and uplink=0
		order by IP";
	echo $sql;
	$q=mysql_query($sql);
	if (mysql_num_rows($q)>0){
		$r=mysql_fetch_object($q);
		$list1=$list1."<select name=macip>";
		while($r){
			$list1=$list1."<option value='$r->MAC|$r->IP'>$r->IP ($r->MAC)\n";
			$r=mysql_fetch_object($q);

		}
		$list1=$list1."</select>";
		
	}
        
	}else{
		#single mac was specified
		$list1="<input type=hidden name=macip value=\"$mac|$ip\">$ip ($mac)";
	}

        $sql="select nodeID,description,active from Devices order by description";
        
        $q=mysql_query($sql);
        if (mysql_num_rows($q)>0){
                $r=mysql_fetch_object($q);
                $list2=$list2."<select name=nodeID>";
                while($r){
                        $list2=$list2."<option value=$r->nodeID>$r->description $r->active\n";
                        $r=mysql_fetch_object($q);
                        
                }
                $list2=$list2."</select>";
                
        }       
        

	print "<table border><tr><th>IP</th><th></th><th>Node</th></tr>
		<tr><td>$list1</td><th><-></th><td>$list2<br>New Node <input type=text name=other></td>";
	print "<th><input type=hidden name=action>
	<input type=button value='Link' onClick='submitForm(1);'></th></tr><tr><th>
	<input type=button value='Delete IP' onClick='submitForm(2);'></th><th></th><th>
	<input type=button value='Delete Node' onClick='submitForm(3);'</th></tr>
	</table></form>";


?>

</html>
