<head>
<?php
include ("qSearch.css");
include("dbConnect.inc");

?>
</head>
<body>

<table border=0>
<tr><th colspan=3>Select from the drop down list or specify an IP address/DNS Name</th></tr>

<?php
$selectSize=10;

print "<tr><th><form name=qSearchFrm><select name=groupid size=$selectSize onClick='document.qSearchFrm.submit();'>";

$sql="select id, name from Groups order by name";
$q=mysql_query($sql);
print "<option value=''>All\n";
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_row($q);
        echo "\t<option value=$row[0]>$row[1]\n";
}
print "</select></form></th>\n\n";
print "<th valign=top><form target=output action=showDevice.php>\n";

if ($groupid<>''){
	$filtersql=" where groupid=$groupid";
}else{
	$filtersql="";
}

print "<select name=nodeID size=$selectSize>";
$sql="select nodeID, type, Description from Devices $filtersql order by Description";
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
