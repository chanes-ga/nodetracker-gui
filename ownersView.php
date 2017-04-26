<head>
<script>
	function verify(myform,action){
		if(action==3){
	                if(confirm("Are you sure you want to delete this customer?")==true){
				myform.action.value=action;
				myform.submit();
			}
			
                }else{
			myform.action.value=action;
                	myform.submit();
		}
	}
</script>
</head>
<body>
<?php
include("iputils.inc");
function getNodeHTML($ownerid){
	$sql="select nodeID, PrimaryIP,Description,name from Devices left join Groups on Groups.id=Devices.groupid where ownerid=$ownerid";
	$q=mysql_query($sql);
	$html="";
	if(mysql_num_rows($q)>0){

	$r=mysql_fetch_object($q);
	$html="<br><table border=1><tr><th colspan=4>Nodes</th></tr><tr><th>Group</th><th>Description</th><th colspan=2>IP Info</th></tr>";
	while ($r){
		$ipinfo=getIPBlock($r->PrimaryIP);

	        $html=$html."<tr><td>$r->name</td><td><a href=showDevice.php?nodeID=$r->nodeID>$r->Description</a></td>
			<td>$ipinfo</td></tr>\n";
	        $r=mysql_fetch_object($q);
	}
	$html=$html."<tr><td colspan=2></td><td>By convention, the first IP of an IP Block is a default gateway</td></tr></table>";
	}else{
		$html="<br>No Node Assignments found<br><br>";
	}
	return $html;
}
function getIPBlock($ip){
	$html="";
	if($ip){
		$address=toAddressString($ip);
        	$sql="select IPAllocations.*,Owners.name from IPAllocations left join Owners on Owners.id=IPAllocations.ownerid where network<$address order by network desc limit 1";
	        $q=mysql_query($sql);

        	$r=mysql_fetch_object($q);
                $size=computeNetSize($r->mask);
		$html="<table border=0>";
	        $html=$html."<tr><td align=right width=80>$ip</td><td width=90>/".toIP($r->mask)."</td><td>belongs to block <a 
				href=showClassC.php?network=$r->network&size=$size target=details>$r->description</a> assigned to $r->name</td></tr>";

	        $html=$html."</table>";

	}
        return $html;

}

function getIPBlocks($ownerid){

        $sql="select * from IPAllocations where ownerid=$ownerid";
        $q=mysql_query($sql);

	if(mysql_num_rows($q)>0){

	$r=mysql_fetch_object($q);
       	$html="<br><table border=1><tr><th colspan=5>Assigned IP Blocks</th></tr>
		<tr><th>Description</th><th></th><th>IP Network</th><th>IP Mask</th><th>Notes</th></tr>";
        while($r){
                $size=computeNetSize($r->mask);
                $html=$html."<tr><td>$r->description</td><td width=5>
                        </td><td align=right>".toIP($r->network)."</td><td>".toIP($r->mask)."</td>
			<td>$r->notes</td>
                        <td><a href=showClassC.php?network=$r->network&size=$size target=details>More Info</a></td></tr>";
                $r=mysql_fetch_object($q);

        }

        $html=$html."</table>";

}else{
		$html="No IP Block Assignments found";
	}
        return $html;



}
include("dbConnect.inc");
include("owners.css");
$now=time();
switch($action)
{
	case 1:
		break;
	case 2:
		break;
	case 3:
}

$sql="select * from Owners order by name";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<form name=loadFrm><select name=ownerid size=10 onClick='document.loadFrm.submit();'>";
while ($r){
	if ($ownerid==$r->id){
        	$selected="selected";
	}else{
        	$selected="";
	}
	print "<option value=$r->id $selected>$r->name\n";
        $r=mysql_fetch_object($q);
}
print "</select></form>";

if($ownerid){
	
	$sql="select * from Owners where id=$ownerid";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$name=$r->name;
	$contact=$r->contact;
	$phone=$r->phone;
	$email=$r->email;
	$notes=$r->notes;
	$time=strftime("%D %T",$r->lastupdated);

	print "";
	print "<table border=0>
		<tr><th align=left>Name</th><td>$name</td></tr>
		<tr><th align=left>Contact</th><td>$contact</td></tr>
		<tr><th align=left>Phone</th><td>$phone</td></tr>
		<tr><th align=left>Email</th><td>$email</td></t>
		<tr><th align=left valign=top>Notes</th><td valign=top><pre>$notes</pre></td></tr>
		</table>";
	print "<font size=-2><i>Above Last Updated: $time</i></font><br>";

	$nodeHTML=getNodeHTML($ownerid);	
	print $nodeHTML;
	$ipblockHTML=getIPBlocks($ownerid);
	print $ipblockHTML;


}
?>
</body>
