<body bgcolor=white>
<center>
<br><br><br>

<?
include("dbConnect.inc");

$sql="select * from EncryptionStatus"; 
$q=mysql_query($sql); 
$r=mysql_fetch_object($q);
print "<table border=0 width=450><tr><td>Network topology data from your routers and switches was last collected <font 
color=blue><b>".strftime("%D 
%T",$r->lastWalk)."</b></font><br><br>While this information is collected twice daily, if you have made significant network changes, you 
may 
want to update NodeTracker now.<br><br></td></tr>
<form method=post action=schedule.php>
<tr>
<th>
<input type=hidden name=f value=scheduleWalk>
<input type=submit value='  Schedule Walk of Switches and Routers  '>
</th></tr>
</form>
</table>";


print "<br><br><br><table border=0 width=450><tr><td>SNMP Auto Discovery of Network Devices was last run <font 
color=blue><b>".strftime("%D 
%T",$r->lastDiscovery)."</b></font>.<br><br>You will want to run discovery periodically to allow Nodetracker to identify new routers and 
switches and to check for changed community strings.<br><br></td></tr>";

if ($r->scheduleDiscovery==2){
	print "<tr><th><font color=red>Autodiscovery is in progress...</font></th></tr>";
}else{
	print "<form method=post action=schedule.php><tr><th>
		<input type=hidden name=f value=scheduleDiscovery>
	<input type=submit value='Schedule Autodiscover of SNMP Devices'>
	</th></tr>
	</form>";
}

print "</table>";
