<head>
<script>
	function verify(myform,action){
		if(action==3){
	                if(confirm("Are you sure you want to delete this customer?")==true){
				myform.action.value=action;
				myform.submit();
			}
			
                }else{
			myform.action.value=action;
                	myform.submit();
		}
	}
</script>
</head>
<body>
<?php
include("dbConnect.inc");
include("owners.css");
$now=time();
switch($action)
{
	case 1:
		$sql="update Owners set name=\"$name\", contact=\"$contact\",phone=\"$phone\",email=\"$email\",notes=\"$notes\",lastupdated=$now
			where id=$ownerid";
		#print $sql;
		$q=mysql_query($sql);
		break;
	case 2:
		$sql="insert into Owners(name,contact,phone,email,notes,lastupdated)values(\"$name\",\"$contact\",\"$phone\",\"$email\",\"$notes\",$now)";
		#print "$sql";
		$q=mysql_query($sql);
		$sql="select last_insert_id() as id;";
		$q=mysql_query($sql);
		$r=mysql_fetch_object($q);
		$ownerid=$r->id;	
		$ownerid=0;
		break;
	case 3:
		if($ownerid!=1){
			#default owner is 1; don't delete
			$sql="delete from Owners where id=$ownerid";
			$q=mysql_query($sql);
			$ownerid=0;
		}
}

$sql="select * from Owners order by name";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<form name=loadFrm><select name=ownerid size=10 onClick='document.loadFrm.submit();'><option value=0>New\n";
while ($r){
	if ($ownerid==$r->id){
        	$selected="selected";
	}else{
        	$selected="";
	}
	print "<option value=$r->id $selected>$r->name\n";
        $r=mysql_fetch_object($q);
}
print "</select></form>";

if($ownerid){
	
	$sql="select * from Owners where id=$ownerid";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$name=$r->name;
	$contact=$r->contact;
	$phone=$r->phone;
	$email=$r->email;
	$notes=$r->notes;
	$time=strftime("%D %T",$r->lastupdated);
}else{
	$name="";
	$contact="";
	$phone="";
	$email="";
	$notes="";
}



print "<form name=editFrm><input type=hidden name=ownerid value=$ownerid><input type=hidden name=action>";
print "<table border=0>
	<tr><th>Name</th>   <td><input type=text name=name size=40 value=\"$name\"></td></tr>
	<tr><th>Contact</th>  <td><input type=text name=contact size=40 value=\"$contact\"></td></tr>
	<tr><th>Phone</th>  <td><input type=text name=phone size=40 value=\"$phone\"></td></tr>
	<tr><th>Email</th>  <td><input type=text name=email size=40 value=\"$email\"></td></t>
	<tr><th>Notes</th>  <td><textarea name=notes cols=60 rows=10>$notes</textarea></td></tr>
	</table>";
if($ownerid==0){
	print "<input type=button value='Add New' onClick='verify(document.editFrm,2);'>";
}else{
	print "<input type=button value='Update' onClick='verify(document.editFrm,1);'>";
	print "<input type=button value='Delete' onClick='verify(document.editFrm,3);'>";
}
print "</form>";
print "<font size=-1><i>Last Updated: $time</i></font>"

?>
</body>
