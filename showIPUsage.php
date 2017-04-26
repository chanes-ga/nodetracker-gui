<?
	include("dbConnect.inc");
	include("iputils.inc");
	include("showIPUsage.css");

	if ($keyword){
		$sql="select * from IPAllocations where description like '%$keyword%' order by description,network";
	}else{
		$sql="select * from IPAllocations where description='$description' order by description,network";
	}
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	print "<table border=0 width=450>";
	while($r){	
		$size=computeNetSize($r->mask);
		print "<tr><td>$r->description</td><td width=5>
			</td><td align=right>".toIP($r->network)."</td><td>/".toMaskBits($r->mask)."</td>
			<td><a href=showClassC.php?network=$r->network&size=$size target=details>More Info</a></td></tr>";
		$r=mysql_fetch_object($q);

	}
	print "</table>";
?>
