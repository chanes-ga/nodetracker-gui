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
function saveRecord($action,$id,$classid,$parentclassid,$protocol,$portlist,$bwpercentage,$bwceiling,$bwpercentageu,$bwceilingu,$description)
{
	$sql="select linuxgw from CanopyCustomers where classID=$parentclassid";
	$q=mysql_query($sql);
        $r=mysql_fetch_object($q);
	$linuxgw=$r->linuxgw;
        switch($action){
                case 1:
                #new class record
                $sql="insert into CanopyQOSClasses(classID,ParentClassID,BWPercentage,BWPercentageU) 
                        values($classid,$parentclassid,$bwpercentage,$bwpercentage)";
		print $sql;
                $q=mysql_query($sql);
		pushChanges($linuxgw,$parentclassid,$classid,1);
                break;

                case 2:
                #update class record
                $sql="update CanopyQOSClasses set BWPercentage=$bwpercentage,BWCeiling=$bwceiling,
			BWPercentageU=$bwpercentageu,BWCeilingU=$bwceilingu,
			description=\"$description\" 
			where ParentClassID=$parentclassid and ClassID=$classid";
                print "$sql";
                $q=mysql_query($sql);

		pushChanges($linuxgw,$parentclassid,$classid,2);

                break;

                case 3:
                #delete class record
                $sql="delete from CanopyQOSClasses where ClassID=$classid and ParentClassID=$parentclassid";
                print "$sql\n";
		$q=mysql_query($sql);

		$sql="delete from CanopyQOSFilters where realClassID=$classid";
		print "$sql\n";
		$q=mysql_query($sql);

		pushChanges($linuxgw,$parentclassid,$classid,3);

                break;

		case 4:
		#add filter record	

		$sql="insert into CanopyQOSFilters(ParentClassID,classID,protocol,PortList) values($parentclassid,$classid,'$protocol','$portlist')";
		print "$sql\n";
		$q=mysql_query($sql);

		# iptable rules get cleared and then reapplied on changes so action = 2 is correct here and below
		pushChanges($linuxgw,$parentclassid,$classid,2);
		break;

		case 5:
		#update a filter
		$sql="update CanopyQOSFilters set protocol='$protocol',PortList='$portlist' where id=$id";
		print "$sql\n";
		$q=mysql_query($sql);

		pushChanges($linuxgw,$parentclassid,$classid,2);
		break;

		case 6:
		#delete a filter
		$sql="delete from CanopyQOSFilters where id=$id";
		print "$sql\n";
		$q=mysql_query($sql);
		pushChanges($linuxgw,$parentclassid,$classid,2);

		break;
        }
}
function showFilters($classid,$parentclassid)
{	
	$sql="select * from CanopyQOSFilters where classid=$classid and ParentClassID=$parentclassid order by id";	

	print "<table border><tr><th>ID</th><th>Proto</th><th>Ports</th></tr>";
        $q=mysql_query($sql);
        $r=mysql_fetch_object($q);
        while($r)
        {
                $frmName="frm$classID_".$r->id;
                print "<form name=$frmName>\n<tr>
                        <input type=hidden name=action value=5>
			<input type=hidden name=classid value=$classid>
			<input type=hidden name=parentclassid value=$parentclassid>
                        <td><input type=hidden name=id value=$r->id>$r->id</td>\n
		        <td><select name=protocol><option selected>".$r->protocol."<option>ip<option>tcp<option>udp<option>icmp</td>\n
                        <td><input type=text size=50 name=portlist value=$r->PortList></td>\n
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
	        saveRecord($action,$id,$classid,$parentclassid,$protocol,$portlist,$bwpercentage,$bwceiling,$bwpercentageu,$bwceilingu,$description);

	}


	print "<font color=red>Always Create Default Class First!!</font><br><form>
		<table border><tr><th>ParentClassID</th><th>ClassID</th><th>BW Percentage</th></tr>
		<tr><td><input type=hidden name=parentclassid value=$parentclassid>$parentclassid</td>
		<td><input type=text size=3 name=classid></td>
		<td><input type=text name=bwpercentage size=2></td>
		<td><input type=hidden name=action value=1><input type=submit value='Add New Class'</td></tr></form>
		<tr></tr>\n\n
		</table>
		</form>
		<form>
		<input type=hidden name=parentclassid value=$parentclassid>
                <table border><tr><th>ClassID</th><th>Protocol</th><th>PortList</th></tr>       
                <tr><td><input type=text size=3 name=classid></td>
                <td><select name=protocol><option>ip<option>tcp<option>udp<option>icmp</td>\n
                <td><input type=text name=portlist size=50></td>
                <td><input type=hidden name=action value=4><input type=submit value='Add New Filter'</td></tr></form>
                <tr></tr></table>\n\n<br><br><table border><th>Class ID</th><th>BW Down%</th><th>BWC Down%</th><th>BW Up%</th><th>BWC Up%</th><th>Description</th></tr>";

	

	$sql="select * from CanopyQOSClasses where ParentClassID=$parentclassid order by ClassID";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$total=0;
	while($r)
	{
		$frmName="frm".$r->id;
		$total=$total+$r->BWPercentage;
		print "<form name=$frmName>\n<tr>
			<input type=hidden name=action value=2>
			<input type=hidden name=parentclassid value=$parentclassid>\n
			<td><input type=text size=3 name=classid value=$r->ClassID></td>\n
			<td><input type=text name=bwpercentage size=2 value=$r->BWPercentage></td>\n
			<td><input type=text name=bwceiling size=2 value=$r->BWCeiling></td>\n
			<td><input type=text name=bwpercentageu size=2 value=$r->BWPercentageU></td>\n
			<td><input type=text name=bwceilingu size=2 value=$r->BWCeilingU></td>\n
			
			<td align=left>
			<input type=text name=description size=50 value=\"$r->description\">
			</td><td align=center>
			<input type=submit value=' Update Class'>
			<input type=button value=' Delete Class' onClick='verify($frmName);'>
			</td></tr>\n
			</form>\n\n<tr><td colspan=2><br></td><td colspan=5>";
		showFilters($r->ClassID,$parentclassid);
		print "</td></tr>";
		$r=mysql_fetch_object($q);
	}
	print "</table>";
	if ($total!=100){
		print "<font color=red>Your percentages add up to $total but should add up to 100<br>Fix!!</font>";
	}
?>
