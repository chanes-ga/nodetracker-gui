<head>
<script>
function ip2dec(ip)
{
      //ip=new String(document.ip2dec.ip_addr.value) 
      a=''
      if(ip.length < 7) write('Too few chars ! [usage] 111.222.111.222')
      else
      {
      numbers = ip.split(".")
      for(i = 0; i < numbers.length; i++) 
      if(numbers[i] >= 0 && numbers[i] <= 255) blah=1
      else
      {
            return false
      }
      a=(Math.pow(256,3) * numbers[0]) + (Math.pow(256,2) * numbers[1]) + (Math.pow(256,1) * numbers[2]) + (Math.pow(256,0) *numbers[3])
      return a;
      }
}
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
		myform=document.form1;
                if (myform.action.value==2){
                        if(confirm("Are you sure you want to delete this block?")==true){
                               myform.submit();
                        }
                }

	}

	function submitForm(){
		myform=document.form2;
		myform.network.value=ip2dec(myform.ip.value);
		myform.netmask.value=ip2dec(myform.mask.value);

		if (myform.action.value==2){
			if(confirm("Are you sure you want to delete this block?")==false){
				return;
			}
		}
		if (!isBlank(myform.network)&&!isBlank(myform.mask)){
			myform.submit();
		}else{
			alert("Please fill in the blank fields!");
		}
	}
</script>
<?php
include("dbConnect.inc");
include("iputils.inc");
# action var tells us how to modify the device - create, edit, delete, test
switch($action){
	case 1:
		#save record
		$sql="insert into IPBlocks(network,mask) values($network,$netmask)";
		if (toMaskBits($netmask)<25){
		#echo $sql;
			$q=mysql_query($sql);
		}else{
			$response="Please specify at least a Class C network";
		}
		break;
	case 2:
		#delete block
		$sql="delete from IPBlocks where network=$network";
		#echo $sql;
		$q=mysql_query($sql);
		break;
}
?>
</head>
<body>
<?php
	echo "<center>$response<h2>IP Address Space Definitions</h2>";

	echo "<br><form action=configureIPSpace.php name=form2>
		New Block
		<input type=hidden name=action value=1>
		<table border=0 cellpadding=2 cellspacing=3><tr>
		<th align=left>Block</th><td>";

		echo "<input type=hidden name=network><input type=text name=ip  size=17 max=15>";
		echo "</td></tr>
			<th align=left>Network Mask</th>
			<td><input type=hidden name=netmask value=0><input type=text name=mask size=17 max=15></td></tr>
			<tr>
			<th colspan=2>
			<input type=button onClick='submitForm();' value=' Save '>
			</th></tr>";
			
	echo "</table></form><br>";

	echo "<form action=configureIPSpace.php method=post name=form1>Existing Blocks<br>
                        <input type=hidden name=action value=2>
                        <select name=network>";
                        
        $sql="select network,mask from IPBlocks order by network";
        $q=mysql_query($sql);
        for ($i=0;$i<mysql_num_rows($q);$i++){
               	$row=mysql_fetch_row($q);
        	echo "<option value=$row[0]>".toIP($row[0])."/".toMaskBits($row[1])."\n";
        }
        echo "</select><input type=button onClick='verify();' value=' Delete Block '></form>";




?>

