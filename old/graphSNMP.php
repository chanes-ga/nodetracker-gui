<?php
header ("Pragma: no-cache"); 
#header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
#header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header ("Cache-Control: no-cache, no-store, must-revalidate, max_age=0"); 
header ("Expires: 0"); 
?>
<head>
<script>

function placebo() {
	document.rrdgraph.src="";
} 

function reload(rrd) {
        document.rrdgraph.src="";
        document.rrdgraph.src=rrd;
}  

</script>
</head>


<?php
include("dbConnect.inc");
include("graphSNMP.css");

if ($groupbyif=="on"){
	$groupbyif=1;
}else{
	$groupbyif=0;
}
$vartotals=array();
$vars=array();
if ($usetime==1){
	$endtime=strtotime($endtime);
	$starttime=strtotime($starttime);
}else{
	$endtime=time();
	$starttime=$endtime-3600*24;
}
$endtime=(floor($endtime/$timeunit)+1)*$timeunit;
$starttime=floor($starttime/$timeunit)*$timeunit;
print $starttime;

$plotpoints=($endtime-$starttime)/$timeunit + 1;
$uri="/$db/data/tmp";
$basepath="/var/www/html$uri";
$timeclause="floor(timestamp/$timeunit)";

echo "<body bgcolor=#dddddd onUnload=\"placebo();\"><center>";

switch($plotType){
	case 0:
		#for a histogram of values vs time
		$key="value";
		$value="count(*)";
		$orderbysql="v desc";
		break;
	case 1:
		#for a normal graph
		$key="ifnum";
		$value="value";
		$orderbysql="k";
		break;
	case 2:
		#for a histogram of ifnum vs time
		$key="ifnum";
		$value="sum(value)";
		$orderbysql="v desc";
		break;
}
$refidsql="refid in(";
if ($refidarray){
	#we have an array of values
	$totalrefids=count($refids)-1;
	for($i=0;$i<$totalrefids;$i++){
		$refidsql=$refidsql."$refids[$i],";
	}
	$refidsql=$refidsql.$refids[$totalrefids].")";
	$refid=$refids[$totalrefids];
}else{
	#we have a single value
	$refidsql=$refidsql.$refids.")";
	$refid=$refids;
}

$rrd=$basepath."/$refid.rrd";
$rrdgraph="$uri/$refid.rrd.png";


if ($ffield!="0"&&$ffield!=""){
	#need to filter
	$fieldfilter=" and $ffield=$fvalue";
	#echo $fieldfilter;
}

if($ignoredval!="null"){
	$ignoredvalFilter=" and value!=$ignoredval";
}
$whereclause="where $refidsql $fieldfilter $ignoredvalFilter and timestamp>=$starttime and timestamp<=$endtime";
#print $totalrefids."<br>".$whereclause."<br>Refid(s):$refid<hr>";

$sql="select Devices.description as Device,PrimaryIP,refid,lasttime,OID_Instances.shortoid,SNMP_OID.description as monitor,SNMP_OID.oid 
		from
                OID_Instances
                left join SNMP_OID on SNMP_OID.shortoid=OID_Instances.shortoid
                left join Devices on Devices.nodeID=OID_Instances.nodeID
                where $refidsql";

$q=mysql_query($sql);
$r=mysql_fetch_object($q);
$title="<table><tr><th>$r->monitor</th></tr>";
while ($r){
	$title=$title."<tr><td align=left>$r->Device ($r->PrimaryIP)</td></tr>\n";
	$oid=$r->oid;
	$r=mysql_fetch_object($q);	
}
$title=$title."</table>";
echo "<br>$title<br><div id=dates><b>".strftime("%D %T",$starttime)." - ".strftime("%D %T",$endtime)."</div>";
#echo "|$rrd_dataType|$rrd_stacked|$rrd_graphType\n";



getVariables();

if ($groupbyif==0){
	#results stored in vars; copy into vartotals
	$vartotals=$vars;
	createRRD($rrd);
	updateRRD($rrd);
	$results=graphRRD($rrd,"");
	echo "<img name=rrdgraph src=$rrdgraph onClick=\"reload('$rrdgraph');\">";
	echo "<table border=0><tr><th></th><th>".strtoupper($key)."</th><th>DESCRIPTION</th><th>".strtoupper($value)."</th>
		<th>Current</th><th>Minumum</th><th>Average</th><th>Maximum</th></tr>";
	$i=0;
	foreach($vartotals as $k=>$v){
                echo "<tr>
			<th width=10 bgcolor=#".getColor($k),"></th>
			<td>$k</td><td>".getDescription($refid,$key,$k,$oid)."</td><td align=right>$v 
 (".sprintf("%1.1f",$v/$totalrecords*100)."%)</td>
			<td align=right>".$results[calcpr][$i]."</td>
			<td align=right>".$results[calcpr][$i+1]."</td><td align=right>".$results[calcpr][$i+2]."</td>
			<td align=right>".$results[calcpr][$i+3]."</td>
			</tr>";
		$i=$i+4;
	}
	echo "</table>";
}


Function getDescription($refid,$key,$k,$oid){
	global $refidsql;
	if ($key=="ifnum"){
   		$sql="select ifdescr from OID_Instance_D where $refidsql and ifnum=$k";
	        $q=mysql_query($sql);
	        $r=mysql_fetch_object($q);		
		return $r->ifdescr;
	}else{
		$sql="select description from SNMP_Value_D where oid='$oid' and value=$k";
		$q=mysql_query($sql);
		$r=mysql_fetch_object($q);
		return $r->description;	
	}

}

Function perl_chop ($string) { 
	return substr($string, 0, strlen($string) - 1); 
}
Function resetVars(){
	global $vars;
	foreach($vars as $k=>$v){
		$vars[$k]=0;
	}
}
Function graphRRD($rrd,$title){
	global $vartotals,$vars,$starttime,$endtime,$refid,$rrd_stacked,$rrd_graphType,$rrd_dataType;
	$rrd_graphType=strtoupper($rrd_graphType);
	if ($rrd_graphType!="AREA"){
		$rrd_graphType=$rrd_graphType."2";
	}

	if ($rrd_stacked==0){
		$nextGraphType=$rrd_graphType;
	}else{
		$nextGraphType="STACK";
	}
        #print "\nGraphing RRD file\n";
	$rrdgraph="$rrd.png";
	$rrdopts=array();
	array_push($rrdopts,"--start",$starttime,"--end",$endtime,"--imgformat","PNG","-l",0,"--width",600,"--height",250);

	#array_push($rrdopts,"--units-exponent",1);
	$i=1;
	foreach($vars as $k=>$v){
		array_push($rrdopts,"DEF:v$i=$rrd:$k:AVERAGE ");
		$fields[$i]=$k;
		$i=$i+1;
	}
	$totalFields=$i-1;
	array_push($rrdopts, "$rrd_graphType:v1#".getColor($fields[1]).":$fields[1]");
        array_push($rrdopts,"PRINT:v1:LAST: %4.0lf %s");
        array_push($rrdopts,"PRINT:v1:MIN: %4.0lf %s");
        array_push($rrdopts,"PRINT:v1:AVERAGE: %4.0lf %s");
        array_push($rrdopts,"PRINT:v1:MAX: %4.0lf %s");

        for ($i=2;$i<=$totalFields;$i++){
                array_push ($rrdopts,"$nextGraphType:v$i#".getColor($fields[$i]).":$fields[$i]");
        	array_push($rrdopts,"PRINT:v$i:LAST: %4.0lf %s");
        	array_push($rrdopts,"PRINT:v$i:MIN: %4.0lf %s");
        	array_push($rrdopts,"PRINT:v$i:AVERAGE: %4.0lf %s");
        	array_push($rrdopts,"PRINT:v$i:MAX: %4.0lf %s");
        }


	array_push($rrdopts,"--title",$title);

	#echo join("\n",$rrdopts);
	$ret=rrd_graph($rrdgraph,$rrdopts,count($rrdopts));
	checkForError($ret);
	#echo join("<br>",$rrdopts);
	return $ret;
}
Function getColor($port){
	# Converts a 16 bit number into a 24 bit color returning the hex string representation
	# The 16 bits are assigned as follows: r6b5g5b4 g4r4b3 g3r3b2g2 r2b1g1r1
	$port=$port+0x222222;
        $r=array();
        $g=array();
        $b=array();
                
        $rbit=1;
        $gbit=2;
        $bbit=4;
        $shift=0;
        for ($i=1;$i<=6;$i++){
                $r[$i]=($port&($rbit<<$shift))>>$shift;
                $g[$i]=($port&($gbit<<$shift))>>($shift+1);
                $b[$i]=($port&($bbit<<$shift))>>($shift+2);
                $shift+=3;
        }
        # only 5 bits for blue and green
        $b[6]=0;
        $g[6]=0;
        
        $red=0;
        $green=0;
        $blue=0;
        for ($i=1;$i<=6;$i++){
                $red=$red+($r[$i]<<(8-$i));
                $green=$green+($g[$i]<<(8-$i));
                $blue=$blue+($b[$i]<<(8-$i));
        }
        $hexcolor=sprintf("%02X%02X%02X",$red,$green,$blue);
	#echo "$port:$red $green $blue $hexcolor\n";

        return $hexcolor;



}
Function updateRRD($rrd){
	#echo "In updateRRD\n";
	global $timeclause, $whereclause, $vars, $timeunit,$orderbysql,$key,$value;

	$sql="select $value as v,$key as k,$timeclause as basetime from RawSNMP $whereclause group by basetime, k order by 
basetime, $orderbysql";
	#echo $sql."\n";
	$q=mysql_query($sql);
	$rows=mysql_num_rows($q);

	$r=mysql_fetch_object($q);
	$previousTime=$r->basetime;
	resetVars();
	while($r){
		if($previousTime!=$r->basetime){
			$timestamp=$previousTime*$timeunit;
			$template="--template ";
			$vals="$timestamp:";
			foreach($vars as $k=>$v){
				$template=$template."$k:";
				$vals=$vals."$v:";
			}
			resetVars();
			$vals=perl_chop($vals);
			$template=perl_chop($template);
			$params=$template." ".$vals;
			#echo "$rrd\t$params<br>";
			#--template broken here; have to go with values only...
			$ret=rrd_update($rrd,$vals);
			checkForError($ret);
			#echo strftime("%D %T",$timestamp)."\t$vals\n<Br>";
		}
		#$vars[$r->value]=$r->c;
		$vars[$r->k]=$r->v;
		#echo "$r->k\t$r->v\t\t".strftime("%D %T",$r->basetime)."\n<br>";

		$previousTime=$r->basetime;
		$r=mysql_fetch_object($q);
	}

}
Function checkForError($ret){
        if ( $ret == 0 )
        {
                $err = rrd_error();
                echo "RRD Error: $err\n";
        }
}
Function createRRD($rrd){
	global $vars,$starttime,$timeunit,$plotpoints,$rrd_dataType;
	$rrdopts=array();
	array_push($rrdopts,"--start",($starttime-$timeunit),"--step",$timeunit);
	foreach($vars as $k=>$v){
        	#echo "$k\t$v<Br>";
        	array_push($rrdopts,"DS:$k:$rrd_dataType:6000:U:U");
	}       
	array_push($rrdopts, "RRA:AVERAGE:.5:1:$plotpoints");
	#echo join(" ",$rrdopts)."\n";
	$ret = rrd_create($rrd, $rrdopts, count($rrdopts));
	if ( $ret == 0 )
	{
        	$err = rrd_error();
	        echo "Create error: $err\n";
	}
}
Function getValueDescriptions(){
	global $oid;
	$sql="select value,description from SNMP_Value_D where oid='$oid'";
	#echo $sql;
        $q=mysql_query($sql);
        $r=mysql_fetch_object($q);
	while($r){
		$v[$r->value]=$r->description;
	        $r=mysql_fetch_object($q);
	}
        return $v;
}
Function getVariables(){
	#purpose here is to calculate the # variables
	global $groupbyif,$timeunit,$whereclause,$groupbysql,$key,$value,$orderbysql,$totalrecords;
	global $vars;
	if($groupbyif==1){
		$orderbysql="ifnum,$orderbysql";
		$ifsql=",ifnum";
		$groupbysql="group by ifnum,k";
	}else{
		$ifsql="";
		$groupbysql="group by k";
	}
	$totalrecords=0;

	$sql="select $value as v,$key as k $ifsql from RawSNMP $whereclause $groupbysql order by $orderbysql";
	#echo "$sql\n";
	$q2=mysql_query($sql);
	$rows=mysql_num_rows($q2);
	if ($groupbyif==0){
		for ($i=0;$i<$rows;$i++){
			$r=mysql_fetch_object($q2);
			$vars[$r->k]=$r->v;
			$totalrecords=$totalrecords+$r->v;
		}
	}else{
		$valueHash=getValueDescriptions();
		echo "<table><tr><th>Ifnum</th><th>Value</th><th>Frequency</th></tr>";
        	for ($i=0;$i<$rows;$i++){
                        $r=mysql_fetch_object($q2);
			echo "<tr><td>$r->ifnum</td><td>".$valueHash[$r->k]."($r->k)</td><td align=right>$r->v</td></tr>";
                }
		echo "</table>";
	}
}

?>
