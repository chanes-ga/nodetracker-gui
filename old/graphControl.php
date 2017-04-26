<html>
<body bgcolor=#dddddd><center>
<?php
include("graphControl.css");
include("dbConnect.inc");
$rrd_graphType=strtoupper($rrd_graphType);
$rrd_dataType=strtoupper($rrd_dataType);
echo "<form target=graph action=\"graphSNMP.php\" method=post><table border=0 cellpadding=3><tr>";
echo "<td valign=top>Start Time<br><input type=text name=starttime size=14 value=\"".strftime("%D %T",$starttime)."\"><br>End 
Time<br><input 
type=text name=endtime size=14 value=\"".strftime("%D %T",$endtime)."\"></td>
	";
getRRDOptions();
echo "<td valign=top>Filter<br><select name=ffield><option value=0>None<option value=ifnum>Interface<option value=value>Value</select>=
	<input type=text size=7 name=fvalue><br><br>Show Interface Details<input type=checkbox name=groupbyif><br>(Text results only)	
	</td>";


getRefIDOptions();
echo "<th>
	<input type=hidden name=ignoredval value=$ignoredval>
	<input type=hidden name=timeunit value=$timeunit>
	<input type=hidden name=refid value=$refid>
	<input type=hidden name=usetime value=1>
	<input type=hidden name=refidarray value=1>
	<input type=submit value=\"Graph\"><br><br>
	<input type=reset></th></tr>";
echo "</table></form>";

Function getRefIDOptions(){
	global $shortoid,$refids;
	$sql="select refid,Devices.Description as device, SNMP_OID.ignoredvalue,SNMP_OID.description as oid from OID_Instances
		left join Devices on Devices.nodeID=OID_Instances.nodeID 
		left join SNMP_OID on SNMP_OID.shortoid=OID_Instances.shortoid 
		where OID_Instances.shortoid=$shortoid order by device";
	$q=mysql_query($sql);
 	$r=mysql_fetch_object($q);
	$selectsize=mysql_num_rows($q);
	if ($selectsize>6){
		$selectsize=6;
	}
	echo "<th>$r->oid on<br><select name=refids[] multiple size=$selectsize>";

	while($r){
		if ($r->refid==$refids){
			$selected="selected";
		}else{
			$selected="";
		}
		print "<option value=$r->refid $selected>$r->device\n";
		$r=mysql_fetch_object($q);
	}
	echo "</select></th>";
}

Function getRRDOptions(){
	global $rrd_graphType,$rrd_stacked,$plotType,$rrd_dataType;
	echo "<td valign=top><table><tr><td>Graph Format</td><td><select 
name=rrd_graphType><option>$rrd_graphType<option>AREA<option>LINE";
	echo "</select><select name=rrd_stacked>";
	if($rrd_stacked==0){
		$tmp="<option value=0>Not Stacked<option value=1>Stacked";
	}else{
		$tmp="<option value=1>Stacked<option value=0>Not Stacked";

	}
	echo "$tmp</select></td></tr><tr><td>Plot Type</td><td><select name=plotType>";
                switch ($plotType){
                        case 0:
                        $plotTypeOption="<option selected value=0>Value Histogram VS Time";
                        break;
                        case 1:
                        $plotTypeOption="<option selected value=1>Values VS Time";
                        break;
                        case 2:
                        $plotTypeOption="<option selected value=2>Interface Histogram VS Time";
                        break;
                }


	echo "$plotTypeOption
                <option value=0>Value Histogram VS Time
                <option value=1>Values VS Time
                <option value=2>Interface Histogram VS Time
		</select></td></tr><tr><td>Data Type</td><td><select name=rrd_dataType><option>$rrd_dataType
		<option>COUNTER<option>GAUGE<option>ABSOLUTE";

	echo "</select></td></tr></table></td>";
}

?>
</html>

