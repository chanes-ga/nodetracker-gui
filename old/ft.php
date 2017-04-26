<?php
include("dbConnect.inc");
include("iputils.inc");
?>

<!--
     (Please keep all copyright notices.)
     This frameset document includes the FolderTree script.
     Script found in: http://www.geocities.com/marcelino_martins/foldertree.html
     Author: Marcelino Alves Martins (http://www.mmartins.com) 

     Instructions:
     - Do not make any changes to this file outside the style tag.
	 - Through the style tag you can change the colors and types
	   of fonts to the particular needs of your site. 
	 - A predefined block has been made for stylish people with
	   black backgrounds.
-->


<html>

<head>

<style>
   BODY {background-color: white; font-size: 9pt; font-family: verdana,helvetica
           text-decoration: none;
	}

   TD {font-size: 8pt; 
       font-family: MS Sans Serif,Arial,Helvetica; 
	   text-decoration: none;
	   white-space:nowrap;}
   A  {text-decoration: none;
       color: black}
</style>



<!-- NO CHANGES PAST THIS LINE -->


<!-- Code for browser detection -->
<script src="ua.js"></script>

<!-- Infrastructure code for the tree -->
<script src="ftiens4.js"></script>

<!-- Execution of the code that actually builds the specific tree.
     The variable foldersTree creates its structure with calls to
	 gFld, insFld, and insDoc -->

<!script src="defineTree.js"></script> 

<script> 
<?php
	include("defineTree.php");
?> 
</script>



</head>

<body topmargin=16 marginheight=16>
Welcome to NodeTracker<br><br>
<?php

if ($key){
	include("crypt.inc");
	$r=verifyKey($key);
	if ($r!=0){
	        $keyvalid=1;
	        session_register("key");
	        session_register("keyvalid");
	}
}
if (($HTTP_SESSION_VARS["keyvalid"]==1)||($keyvalid==1)){
	print "<font size=-1 color=blue>Encryption key established</font><br><br>";
}else{
	print "<font size=-1 color=red>Please provide your encryption key</font>
		<form method=post><input type=password name=key></form>
	<br>";
}

?>


<div style="position:absolute; top:0; left:0; "><table border=0><tr><td><font size=-2><a style="font-size:6pt;text-decoration:none;color:gray" href=http://www.mmartins.com target=_top></a></font></td></table></div>

<!-- Build the browser's objects and display default view of the 
     tree. -->
<script>initializeDocument()</script>

</html>
