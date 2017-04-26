<script>

        function verify(myform){
                if(confirm("Are you sure you want to delete this?")==true){
                        myform.action.value=3;
                        myform.submit();
                }

        }


</script>
<body>
<center>
<?
include ("index.css");
include "../dbConnect.inc";
include ("misc.inc");


function getStatsForm($id,$rrdid,$routerip,$description){
	#print "classid: $rrdid<br>";
	$fillfactor=10;
	#first check to see if this rrdid has children; if not then just return
	$sql="select count(*) as c from CanopyQOSClasses where pid=$id";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	#print "Found $r->c $sql<br>";
	
	if($r->c==0){
		return "";
	}



	$sql="select * from CanopyQOSRouters where ip='$routerip'";
	
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);

	$frm="<form method=post action=https://".$routerip."/cgi-bin/tcGraph.pl target=BLANK$rrdid><td>
			<input type=submit value='Stats'>
			<input type=hidden name=description value=\"$description\">
			<input type=hidden name=rrdid value=$rrdid>
			<input type=hidden name=ulIF value=".$r->upIF.">
			<input type=hidden name=dlIF value=".$r->downIF."></td>
			</form>";
	#print "RouterIP: $routerip rrd:$rrdid $sql<br>";

	return $frm;

}


#Create Forms for toplevel QOS statistics for each linux router
$sql="select * from CanopyQOSRouters";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<table border cellpadding=5><tr><th>QOS Router</th><th></th></tr>";
$routerlist="";
while($r)
{
	$routerlist=$routerlist."<option value='$r->ip'>$r->name\n";
	print "<form method=post action=https://".$r->ip."/cgi-bin/tcGraph.pl target=BLANK>";
	print "<tr><td>$r->name</td><td>
			<input type=submit value='Stats'>
			<input type=hidden name=rrdid value=1>
			<input type=hidden name=description value=\"$r->name\">
			<input type=hidden name=ulIF value=".$r->upIF.">
			<input type=hidden name=dlIF value=".$r->downIF."></td></form>\n";

	if($r->remoteadmin==1){
		print "<form method=post action=https://".$r->ip."/qosadmin/ target=BLANK>\n<td>
			<input type=submit value='Admin'></td></form>\n";
}
	print		"</tr>\n";

	$r=mysql_fetch_object($q);
}

print "</table>
	<form method=post>
	<table border>
	<tr><th>Gateway</th><th>ClassID</th><th>Customer</th><th>IP Block/Mask</th><th>DL Speed</th><th>UL Speed</th></tr>
	<tr><td><select name=linuxgw>$routerlist
	</select>
	</td>
	<td><input type=text name=classid size=3></td>
	<td><input type=text name=customer></td>
	<td><input type=text name=ipblock size=15><br>
	<input type=text name=mask size=15></td>
	<td><input type=text name=dlspeed size=4></td>
	<td><input type=text name=ulspeed size=4></td>
	<td>
	<input type=hidden name=action value=1>
	<input type=submit value='Add New'>
	</td>
	</tr>
	</form>
	<tr><td colspan=7><br></td></tr>";
?>
<?
function saveRecord($action,$id,$linuxgw,$customer,$ipblock,$mask,$dlspeed,$ulspeed,$classid)
{
	$ip=toAddress($ipblock);
	$ip=gmp_strval($ip);
	
	$mask=toAddress($mask);
	$mask=gmp_strval($mask);
	
	switch($action){
		case 1:
		#new record
		$sql="insert into CanopyCustomers(linuxgw,classID,Customer,ipblock,mask,dlspeed,ulspeed) 
			values('$linuxgw',$classid,\"$customer\",$ip,$mask,$dlspeed,$ulspeed)";
		$q=mysql_query($sql);
		$sql="select LAST_INSERT_ID() as id";
		$q=mysql_query($sql);
		$r=mysql_fetch_object($q);
		$id=$r->id;

		pushChanges($id,0,1,$linuxgw);
		break;

		case 2:
		#update record
		$sql="update CanopyCustomers	
			set classID=$classid,Customer=\"$customer\", ipblock=$ip,mask=$mask,dlspeed=$dlspeed,ulspeed=$ulspeed
			where id=$id";
		#print "$sql";
		$q=mysql_query($sql);
		pushChanges($id,0,2,$linuxgw);
		break;

		case 3:
		#delete record
		print "deleting record $classid\n";
		$sql="delete from CanopyCustomers where id=$id";
		$q=mysql_query($sql);
		$sql="delete from CanopyQOSClasses where pid=$id";
		$q=mysql_query($sql);
		$sql="delete from CanopyQOSFilters where pid=$id";
		$q=mysql_query($sql);

		pushChanges($classid,0,3,$linuxgw);

		break;

	}


}
include "../ipconvert.inc";


if($action){
	#print "Classid: $classid";
	saveRecord($action,$id,$linuxgw,$customer,$ipblock,$mask,$dlspeed,$ulspeed,$classid);

}




$sql="select * from CanopyCustomers order by linuxgw,classID";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);

while($r)
{

$ipblock=toIP($r->ipblock);
$mask=toIP($r->mask);
$frmName="frm".$r->id;
print "<form name=$frmName><tr><td><input type=hidden name=linuxgw value=$r->linuxgw>$r->linuxgw</td></td>
	<input type=hidden name=id value=$r->id></td>
	<td><input type=text name=classid value=$r->classID size=3></td>
	<td><input type=text name=customer value=\"$r->customer\"></td>
	<td><input type=text name=ipblock size=15 value=$ipblock><br>
	<input type=text name=mask size=15 value=$mask></td>
	<td><input type=text name=dlspeed size=4 value=$r->dlspeed></td>
	<td><input type=text name=ulspeed size=4 value=$r->ulspeed></td>
	<td>
	<input type=hidden name=action value=2>
	
	<input type=submit value=' Update'>
	<input type=button value=' Delete ' onClick='verify($frmName);'>
	</td></form><td>";
	$stats=getStatsForm($r->id,$r->classID,$r->linuxgw,$r->customer);
	print "<form action=advanced.php target=BLANK>
		<input type=hidden name=linuxgw value=$r->linuxgw>
		<input type=hidden name=pid value=$r->id>
		<input type=hidden name=dlspeed value=$r->dlspeed>
		<input type=hidden name=ulspeed value=$r->ulspeed>
		<input type=submit value='Advanced'></td></form>\n
		$stats";
	
	$r=mysql_fetch_object($q);
}


?>

</table>

