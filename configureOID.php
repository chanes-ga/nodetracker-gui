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
		//oid=document.form1.oid.options[document.form1.oid.options.selectedIndex].value;
		//if (oid!=""){
			document.form1.submit();
		//}

	}
	function submitForm(v){
		myform=document.form2;
		myform.action.value=v;
                if ((myform.newrecord.value==1)&&(myform.action.value!=1)){
			//can't delete something not yet created
			return;
                }
		if (myform.action.value==3){
			if(confirm("Are you sure you want to delete this device?")==false){
				return;
			}
		}
		if (!isBlank(myform.oid)&&!isBlank(myform.description)){
			myform.submit();
		}else{
			alert("Please fill in the blank fields!");
		}
	}
</script>
<?php
include("dbConnect.inc");

# action var tells us how to modify the OID - create, edit, delete
switch($action){
	case 1:
		if ($monitorSTD=="on"){
			$monitor=1;
		}else{
			$monitor=0;
		}

		#save record
		if ($newrecord==1){
			$sql="select count(*) as c,description from SNMP_OID where oid='$oid' group by description";
			$q=mysql_query($sql);
			$r=mysql_fetch_object($q);
			if ($r->c==0){
				#this oid is not in the table so we can add it.
				$sql="insert into 
SNMP_OID(oid,description,rrd_graphType,rrd_stacked,rrd_dataType,plotType,descriptionoid,monitorSTD) 
					values
					
(\"$oid\",\"$description\",'$graphType',$stacked,'$dataType',$plotType,'$oiddescription',$monitor)";
				$q=mysql_query($sql);
				$shortoid=mysql_insert_id();
			}else{	
				echo "<center><b>$oid has already been added as $r->description</b><br></center>";
			}
		}else{
			if (strlen($ignoredvalue)>0){
				$moresql=",ignoredvalue=$ignoredvalue";
			}else{
				$moresql=",ignoredvalue=null";
			}
			$sql="update SNMP_OID set oid=\"$oid\", description=\"$description\",
				rrd_graphType='$graphType',
				rrd_stacked=$stacked,
				rrd_dataType='$dataType',
				plotType=$plotType,
				descriptionoid='$oiddescription',	
				monitorSTD=$monitor $moresql
				where shortoid=$shortoid";
			$q=mysql_query($sql);
		}
		break;
	case 3:
		#delete device
		$sql="delete from SNMP_OID where shortoid=$shortoid";
		$q=mysql_query($sql);
		$shortoid="";
		$oid="";
		$description="";
		break;
}
?>
</head>
<body>
<?php
	echo "<center><h2>SNMP OID Configurations</h2>$sql";

	echo  "Select to edit an existing OID or complete the form to add a new OID<br>
			<form action=configureOID.php method=post name=form1>
			<input type=hidden name=mode value=1>
			<select name=shortoid onChange='verify();'><option><option value=\"\">New OID";
	$sql="select shortoid,description from SNMP_OID order by description";
	$q=mysql_query($sql);
	for ($i=0;$i<mysql_num_rows($q);$i++){
        	$row=mysql_fetch_row($q);
	       	echo "<option value=$row[0]>$row[1]\n";
	} 
	echo "</select></form>";


	if ($shortoid!=""){
		$newrecord=0;
		$sql="select * from SNMP_OID where shortoid=$shortoid";
		$q=mysql_query($sql);
		$row=mysql_fetch_object($q);
		$oid=$row->oid;
		$description=$row->description;
		$oiddescription=$row->descriptionoid;
		$rrdDataType="<option>".$row->rrd_dataType;
		$rrdGraphType="<option selected>$row->rrd_graphType";
		if (!is_null($row->IgnoredValue)){
			$ignoredvalue="value=$row->IgnoredValue";
		}
		if ($row->monitorSTD==0){
			$monitorSTD="";
		}else{
			$monitorSTD="checked";
		}
		if ($row->rrd_stacked==1){
			$rrdStacked="<option value=1 selected>Stacked<option value=0>Not Stacked";
		}else{
			$rrdStacked="<option selected value=0>Not Stacked<option value=1>Stacked";
		}
		switch ($row->plotType){
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

		echo "Editing OID<br>";
	}else{
		echo "New OID<br>";
		$newrecord=1;
		$rrdStacked="<option selected value=0>Not Stacked<option value=1>Stacked";

	}
	
	echo "<br><form action=configureOID.php name=form2>
		<input type=hidden name=action value=0>
		<input type=hidden name=newrecord value=".$newrecord.">
		<input type=hidden name=shortoid value=$shortoid>
		<table border=0 cellpadding=2 cellspacing=3><tr>
		<th align=left>OID</th><td>
		<input type=text name=oid value=\"".$oid."\" size=50 max=200></td></tr>
		<tr><th align=left>Description</th><td><input type=text name=description size=50 max=50 value=\"$description\"></td></tr>
		<tr><th align=left>OID Description Location</th>
		<td><input type=text name=oiddescription size=50 max=200 value=\"$oiddescription\"></tr>
		
		<tr><th align=left>Treat SNMP values as</th><td><select name=dataType>$rrdDataType
		<option>GAUGE<option>COUNTER<option>ABSOLUTE</select></td></tr>
		<tr><th align=left>Default Graph Options</th><td><select name=graphType>$rrdGraphType<option>Area<option>Line</select>
		<select name=stacked>$rrdStacked</select><select name=plotType>$plotTypeOption
		<option value=0>Value Histogram VS Time
		<option value=1>Values VS Time
		<option value=2>Interface Histogram VS Time
		</select>
		</td></tr>
		<tr><th align=left>Monitor STD of Values?</th>
		<td><input name=monitorSTD type=checkbox $monitorSTD></td></tr>
		<tr><th align=left>Ignore Value</th><td><input type=text name=ignoredvalue $ignoredvalue size=5></td></tr>
		";

		echo "</td></tr>
			<th colspan=2><br>
			<input type=button onClick='submitForm(1);' value=' Save '>
			<input type=button  onClick='submitForm(3);' value=' Delete '>
			</th></tr>";
			
	echo "</table></form>";


?>

