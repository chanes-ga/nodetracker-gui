<head>
<script>
	function isBlank(textBox)
	{
		var temp=textBox.value;
		for (i=0;i<temp.length;i++){
			if ((temp.substring(i,i+1))!=" "){
			 	return false;
			}
		}
		return true;	
	}
	function verify(){
		ip=document.form1.ip.options[document.form1.ip.options.selectedIndex].value;
		if (ip!=""){
			document.form1.submit();
		}

	}
</script>
<?php
include("dbConnect.inc");
$ipLen=strlen($ip);
if ($action==1){
	#need to save an extended description to RouterIFs
	if ($newrecord==0){
		#update statement
		$sql="update RouterIFs set description=\"$extendedDescription\" where ip='$ip' and ifnum=$ifnum";
	}else{
		#insert statement
		$sql="insert into RouterIFs(ip,ifnum,description) values(\"$ip\",$ifnum,\"$extendedDescription\")";
	}
	#print $sql;
	$q=mysql_query($sql);
}
?>
</head>
<body>
<?php
	#echo $sql;
	echo "<center><h2>Interface Description Editor</h2>";

	if ($hide==0){
		echo "Select to edit a routers's extended description<br>
			<form action=configureRouterIF.php method=post name=form1>
			<select name=ip onChange='verify();'><option>";


		$sql="select PrimaryIP as ip,Description as name from Devices where type=3 order by name";
		$q=mysql_query($sql);
		for ($i=0;$i<mysql_num_rows($q);$i++){
        		$row=mysql_fetch_row($q);
	        	echo "<option value=$row[0]>$row[1]\n";
		} 
		echo "</select></form>";
	}

	#check to see if an ip has been selected
	if ($ipLen>0){
		echo "<table border=0 cellpadding=2 cellspacing=2>
			<tr><th colspan=4>Editing $ip</th></tr>
			<tr><th>IF Number</th><th>Description</th><th>Extended Description</th></tr>";
		$sql="select IFDescriptions.ifnum, IFDescriptions.description, RouterIFs.description
			from IFDescriptions 
			left join RouterIFs on IFDescriptions.ip=RouterIFs.ip and IFDescriptions.ifnum=RouterIFs.ifnum
			where IFDescriptions.ip='$ip' order by ifnum";
		$q=mysql_query($sql);
		for ($i=0;$i<mysql_num_rows($q);$i++){
                	$row=mysql_fetch_row($q);
			if (is_null($row[2])){
				#no extended description in RouterIFs table so need to create it
				$newrecord=1;
			}else{
				$newrecord=0;
			}
			echo "<form action=configureRouterIF.php method=post>
				<input type=hidden name=hide value=$hide>
				<input type=hidden name=action value=1>
				<input type=hidden name=newrecord value=$newrecord>
				<input type=hidden name=ip value='$ip'>
				<input type=hidden name=ifnum value=".$row[0].">
                		<tr><td>$row[0]</td><td>$row[1]</td>
				<td><input type=text size=40 max=50 name=extendedDescription value=\"$row[2]\">
				</td><td><input type=submit value=\" Modify \"></td></tr></form>";
			$row[2]="";
        	}
		echo "</table>";
	}

?>

