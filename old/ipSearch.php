<head>
<?php
include ("qSearch.css");
include("dbConnect.inc");

?>
</head>
<body>

<table border=0>
<tr><th colspan=2>Select from the drop down list or specify a keyword</th></tr>
<form target=output action=showIPUsage.php>

<tr>
<th>

<select name=description size=6>
<!option value=0>
<?php
$sql="select distinct description from IPAllocations order by description";
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_row($q);
        echo "<option>$row[0]\n";
}
?>

</select>
</th>

<th align=left>
<input type=text name=keyword size=20>
<br><br>
<input type=submit value="Get IP Info"><input type=reset value=Clear></th></form><form><th><input type=submit value='Refresh List'>

</th>
</tr>
</table>
</form>
