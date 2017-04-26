<body bgcolor=#cccccc fgcolor=white>
<?
	$down="eth1";
	$up="eth0";
	print "Hello $linuxgw $rrdid\n";
	$urlbase="https://$linuxgw/tcStats/"; 
	print "<table border><tr><th>Download</th><th>Upload</th></tr>";

	print "<tr><th><img src=$urlbase$rrdid.1hrs.$down.png></th><th><img src=$urlbase$rrdid.1hrs.$up.png></th></tr>";
	print "<tr><th><img src=$urlbase$rrdid.24hrs.$down.png></th><th><img src=$urlbase$rrdid.24hrs.$up.png></th></tr>";


	print "</table>";
?>
</body>
