<html>
<body bgcolor=#dddddd><center>
<?php
include("icmpControl.css");
include("dbConnect.inc");
$endtime=floor(time()/60)*60;
$starttime=$endtime-3600*6;
echo "<form name=myform target=graph action=\"/cgi-bin/icmpChart.pl\" method=post><table border=0 cellpadding=3><tr>";
echo "<td valign=top>Start Time<br><input type=text name=starttime size=14 value=\"".strftime("%D %T",$starttime)."\"><br>End 
Time<br><input type=text name=endtime size=14 value=\"".strftime("%D %T",$endtime)."\"></td>
	";

switch($graphtype){
	case "1":
		$lselected=" selected ";
		break;
	case "2":
		$plselected=" selected ";
		break;
	default:
		$lselected=" selected ";
}

echo "<td valign=top>Graph Type<br>
	<select name=graphtype size=3>
		<option value=1 $lselected>Latency<option value=2 $plselected>Packet Loss<option value=3>Packet Jitter
	</select></td>
	<td valign=top>IP<br><input type=text size=10 name=ip value=$ip><center>
			<input type=button value='Clear' onClick=\"document.myform.ip.value='';\"></td>";


echo "<th>
	<input type=submit value=\"Graph\"><br><br>
	<input type=reset></th></tr>";
echo "</table></form>";


?>
</html>

