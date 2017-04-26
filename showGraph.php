<head>
<META HTTP-EQUIV="Refresh" CONTENT="600">
</head>
<body bgcolor=white>
<center>
<?
	include ("dbConnect.inc");
	include ("graphIF.inc");
	$url="showGraph.php?ifnum=$ifnum&inrefid=$inrefid&outrefid=$outrefid&speed=$speed";
	$urltail="&ifdesc=".urlencode($ifdesc);
	graphIF($db,$ifnum,$interval,$inrefid,$outrefid,"550","250",$ifdesc,0,$speed,"");
	echo  "<tr>
                <th><a href=$url&interval=0$urltail>Last 24 Hours</a></th>
                <th><a href=$url&interval=1$urltail>Last Week</a></th>
                <th><a href=$url&interval=2$urltail>Last Month</a></th>
                <th><a href=$url&interval=3$urltail>Last Year</a></th>
		</table>";

?>
