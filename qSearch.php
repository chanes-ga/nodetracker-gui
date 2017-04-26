<head>
<?php
include ("qSearch.css");
include("dbConnect.inc");

?>
</head>
<body>

<table border=0>
<tr><th>Customer Filter</th><th>Group Filter</th><th>Output</th><th></th></tr>
<?php
$selectSize=10;

#print "groupid: $groupid ownerid: $ownerid<br>";

if ($groupid<>''){
	$filtersql=" where groupid=$groupid";
	$groupSelectAll="";
}else{
	$filtersql="";
	$groupSelectAll=" selected ";
}
if($ownerid<>''){
	$ownerSelectAll="";
	if ($filtersql==""){
		$connector="where ";
	}else{
		$connector=" and ";
	}
	$filtersql=$filtersql.$connector." ownerid=$ownerid ";
}else{
	$ownerSelectAll=" selected";
}


print "<form name=qSearchFrm><tr><th valign=top><select name=ownerid size=$selectSize onClick='document.qSearchFrm.submit();'>";

$sql="select id, name from Owners order by name";
$q=mysql_query($sql);
print "<option value='' $ownerSelectAll>All\n";
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_row($q);
        if($ownerid==$row[0]){    
                $selected=" selected ";
        }else{
                $selected="";
        }

        echo "\t<option value=$row[0] $selected>$row[1]\n";
}


print "</select></th>\n\n";




print "<th><select name=groupid size=$selectSize onClick='document.qSearchFrm.submit();'>";

$sql="select id, name from Groups order by name";
$q=mysql_query($sql);
print "<option value='' $groupSelectAll>All\n";
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_row($q);
	if($groupid==$row[0]){
		$selected=" selected ";
	}else{
		$selected="";
	}
	
        echo "\t<option value=$row[0] $selected>$row[1]\n";
}
print "</select></form></th>\n\n";
print "<th valign=top><form target=output action=showDevice.php>\n";


$sql="select nodeID, type, Description from Devices $filtersql order by Description";
#print "$sql\n<br>";

print "<select name=nodeID size=$selectSize>";

$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_row($q);
        echo "<option value=\"$row[0]|$row[1]\">$row[2]\n";
}
?>

</select>
</th>

<th align=left>
<input type=text name=address size=20><select name=addresstype><option value=ip>IP<option value=mac>MAC</select>
<br><br>
<input type=submit value="Find Device"><input type=reset value=Clear></th></form><form><th><input type=submit value='Refresh List'>
</th>
</tr>
</table>
</form>
