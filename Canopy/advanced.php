<script>

        function verify(myform){
                if(confirm("Are you sure you want to delete this?")==true){
                        myform.action.value=3;
                        myform.submit();
                }

        }
        function verify2(myform){
                if(confirm("Are you sure you want to delete this?")==true){
                        myform.action.value=6;
                        myform.submit();
                }

        }


</script>
<center>

<?
include ("index.css");
include "../dbConnect.inc";
include ("misc.inc");
function 
saveRecord($action,$id,$pid,$classid,$parentclassid,$protocol,$portlist,$bwpercentage,$bwceiling,$bwpercentageu,$bwceilingu,$description,$direction,$layer7,$isdefault)
{
        switch($action){
                case 1:
                #new class record
                $sql="insert into CanopyQOSClasses(classID,pid,BWPercentage,BWPercentageU) 
                        values($classid,$pid,$bwpercentage,$bwpercentage)";
		print $sql;
                $q=mysql_query($sql);
		pushChanges($pid,$classid,1,"");
                break;

                case 2:
                #update class record
                $sql="update CanopyQOSClasses set BWPercentage=$bwpercentage,BWCeiling=$bwceiling,
			BWPercentageU=$bwpercentageu,BWCeilingU=$bwceilingu,
			description=\"$description\",isdefault=$isdefault 
			where pid=$pid and ClassID=$classid";
                print "$sql";
                $q=mysql_query($sql);

		pushChanges($pid,$classid,2,"");

                break;

                case 3:
                #delete class record
                $sql="delete from CanopyQOSClasses where ClassID=$classid and pid=$pid";
                print "$sql\n";
		$q=mysql_query($sql);

		$sql="delete from CanopyQOSFilters where realClassID=$classid";
		print "$sql\n";
		$q=mysql_query($sql);

		pushChanges($pid,$classid,3,"");

                break;

		case 4:
		#add filter record	

		$sql="insert into CanopyQOSFilters(pid,classID,protocol,PortList,direction,layer7) 
				values($pid,$classid,'$protocol','$portlist',$direction,'$layer7')";
		print "$sql\n";
		$q=mysql_query($sql);

		# iptable rules get cleared and then reapplied on changes so action = 2 is correct here and below
		pushChanges($pid,$classid,2,"");
		break;

		case 5:
		#update a filter
		$sql="update CanopyQOSFilters set protocol='$protocol',PortList='$portlist',layer7='$layer7',
			direction=$direction where id=$id";
		print "$sql\n";
		$q=mysql_query($sql);

		pushChanges($pid,$classid,2,"");
		break;

		case 6:
		#delete a filter
		$sql="delete from CanopyQOSFilters where id=$id";
		print "$sql\n";
		$q=mysql_query($sql);
		pushChanges($pid,$classid,2,"");

		break;
        }
}
function showFilters($classid,$pid)
{	
	$sql="select * from CanopyQOSFilters where classid=$classid and pid=$pid order by id";	

	print "<table border><tr><th>ID</th><th>Proto</th><th>Direction</th><th>Ports</th><th>Layer 7</th></tr>";
        $q=mysql_query($sql);
        $r=mysql_fetch_object($q);
        while($r)
        {
                $frmName="filterfrm".$r->id;
		if ($r->direction==1){
			$direction="<select name=direction><option value=1 selected>remote<option value=0>local</select>";
		}else{
			$direction="<select name=direction><option value=1>remote<option selected value=0>local</select>";
		}
                print "\n<form name=$frmName>\n<tr>
                        <input type=hidden name=action value=5>
			<input type=hidden name=classid value=$classid size=2>
			<input type=hidden name=pid value=$pid>
                        <td><input type=hidden name=id value=$r->id>$r->id</td>\n
		        <td><select name=protocol><option selected>".$r->protocol."<option>ip<option>tcp<option>udp<option>icmp</td>\n
			<td>$direction</td>\n
              	 	<td><input type=text size=40 name=portlist value=$r->PortList></td>\n
			<td><input type=text size=10 name=layer7   value=$r->layer7></td>\n
			<td>
                        <input type=submit value=' Update Filter'>
                        <input type=button value=' Delete Filter' onClick='verify2($frmName);'>
                        </td></tr>\n
                        </form>\n\n";
                $r=mysql_fetch_object($q);


        }  
	print "</table>";
}

	if($action){
	    
		saveRecord($action,$id,$pid,$classid,$parentclassid,$protocol,$portlist,$bwpercentage,$bwceiling,$bwpercentageu,$bwceilingu,$description,$direction,$layer7,$isdefault);

	}


	$sql="select * from CanopyCustomers where id=$pid";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$minKbps=120;		# MTU=1500 bytes * 8 bits/byte = 12000 bits * 10 r2q = 120000 bits
	$minupPercentage=($minKbps/$r->ulspeed)*100;
	$mindownPercentage=($minKbps/$r->dlspeed)*100;
	print "<h2>$r->customer</h2><br>";
	print "Min Upload % is $minupPercentage<br>Min Down % is $mindownPercentage<br>";


	print "<font color=red>Remember to Specify Default Class!!</font><br><form>
		<table border><tr><th>ParentClassID</th><th>ClassID</th><th>BW Percentage</th></tr>
		<tr><td><input type=hidden name=pid value=$pid>$pid</td>
		<td><input type=text size=2 name=classid></td>
		<td><input type=text name=bwpercentage size=2></td>
		<td><input type=hidden name=action value=1><input type=submit value='Add New Class'</td></tr></form>
		<tr></tr>\n\n
		</table>
		</form>
		<form>
		<input type=hidden name=pid value=$pid>
                <table border><tr><th>ClassID</th><th>Protocol</th><th>Port Direction</th>
			<th>PortList</th><th>Layer 7</th></tr>       
                <tr><td><input type=text size=3 name=classid></td>
                <td><select name=protocol><option>ip<option>tcp<option>udp<option>icmp</td>\n
		<td><select name=direction><option value=1>remote<option value=0>local</select></td>\n
                <td><input type=text name=portlist size=50></td><td><input type=text name=layer7 size=30></td>
                <td><input type=hidden name=action value=4><input type=submit value='Add New Filter'</td></tr></form>
                <tr></tr></table>\n\n<br><br>
		<table border><th>Class ID</th><th>Default?</th>
			<th>BW Down%</th><th>BWC Down%</th><th>BW Up%</th><th>BWC Up%</th><th>Description</th></tr>";


	$sql="select * from CanopyQOSClasses where pid=$pid order by ClassID";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$total=0;
	$totalU=0;
	while($r)
	{
		$frmName="frm".$r->id;
		$total=$total+$r->BWPercentage;
		$totalU=$totalU+$r->BWPercentageU;
		print "<form name=$frmName>\n<tr>
			<input type=hidden name=action value=2>
			<input type=hidden name=pid value=$pid>\n
			<td><input type=text size=1 name=classid value=$r->ClassID></td>\n
			<td><input type=text size=1 name=isdefault value=$r->isdefault></td>\n
			<td><input type=text name=bwpercentage size=1 value=$r->BWPercentage></td>\n
			<td><input type=text name=bwceiling size=1 value=$r->BWCeiling></td>\n
			<td><input type=text name=bwpercentageu size=1 value=$r->BWPercentageU></td>\n
			<td><input type=text name=bwceilingu size=1 value=$r->BWCeilingU></td>\n
			
			<td align=left>
			<input type=text name=description size=50 value=\"$r->description\">
			</td><td align=center>
			<input type=submit value=' Update Class'>
			<input type=button value=' Delete Class' onClick='verify($frmName);'>
			</td></tr>\n
			</form>\n\n<tr><td colspan=2><br></td><td colspan=5>";
		showFilters($r->ClassID,$pid);
		print "</td></tr>";
		$r=mysql_fetch_object($q);
	}
	print "</table>";
	if ($total!=100){
		print "<font color=red>Your download percentages add up to $total but should add up to 100<br>Fix!!</font>";
	}
	if ($totalU!=100){
		print "<font color=red>Your upload percentages add up to $totalU but should add up to 100<br>Fix!!</font>";
	}
?>
