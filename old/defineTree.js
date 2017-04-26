// You can find instructions for this file here:
// http://www.mmartins.com

// Decide if the names are links or just the icons
USETEXTLINKS = 1  //replace 0 with 1 for hyperlinks

// Decide if the tree is to start all open or just showing the root folders
STARTALLOPEN = 0 //replace 0 with 1 to show the whole tree

foldersTree = gFld("<i>Been There</i>", "startPage.html")
	aux1 = insFld(foldersTree, gFld("Europe", "http://www.mmartins.com/ft/example/pictures/beenthere_europe.gif"))
	      aux2 = insFld(aux1, gFld("United Kingdom", "http://www.mmartins.com/ft/example/pictures/beenthere_unitedkingdom.gif"))
		      aux3 = insFld(aux2, gFld("Scotland", "http://www.mmartins.com/ft/example/pictures/beenthere_scotland.jpg"))
				insDoc(aux3, gLnk(2, "Edinburgh", "www.mmartins.com/ft/example/pictures/beenthere_edinburgh.gif"))
 			insDoc(aux2, gLnk(2, "London", "www.mmartins.com/ft/example/pictures/beenthere_london.jpg"))
	      aux2 = insFld(aux1, gFld("Germany", "http://www.mmartins.com/ft/example/pictures/beenthere_germany.gif"))
 			insDoc(aux2, gLnk(2, "Munich", "www.mmartins.com/ft/example/pictures/beenthere_munich.jpg"))
	      aux2 = insFld(aux1, gFld("Greece", "http://www.mmartins.com/ft/example/pictures/beenthere_greece.gif"))
 			insDoc(aux2, gLnk(2, "Athens", "www.mmartins.com/ft/example/pictures/beenthere_athens.jpg"))
	      aux2 = insFld(aux1, gFld("Italy", "http://www.mmartins.com/ft/example/pictures/beenthere_italy.gif"))
		      aux3 = insFld(aux2, gFld("Tuscany", "http://www.mmartins.com/ft/example/pictures/beenthere_tuscany.gif"))
				insDoc(aux3, gLnk(2, "Florence", "www.mmartins.com/ft/example/pictures/beenthere_florence.jpg"))
				insDoc(aux3, gLnk(2, "Pisa", "www.mmartins.com/ft/example/pictures/beenthere_pisa.jpg"))
			insDoc(aux2, gLnk(2, "Rome", "www.mmartins.com/ft/example/pictures/beenthere_rome.jpg"))
	      aux2 = insFld(aux1, gFld("Portugal", "http://www.mmartins.com/ft/example/pictures/beenthere_portugal.gif"))
 			insDoc(aux2, gLnk(2, "Lisboa", "www.mmartins.com/ft/example/pictures/beenthere_lisbon.jpg"))
     aux1 = insFld(foldersTree, gFld("North America", "http://www.mmartins.com/ft/example/pictures/beenthere_america.gif"))
	      aux2 = insFld(aux1, gFld("Canada", "http://www.mmartins.com/ft/example/pictures/beenthere_canada.gif"))
 			insDoc(aux2, gLnk(2, "Montreal", "www.mmartins.com/ft/example/pictures/beenthere_montreal.jpg"))
	      aux2 = insFld(aux1, gFld("United States", "http://www.mmartins.com/ft/example/pictures/beenthere_unitedstates.gif"))
 			insDoc(aux2, gLnk(2, "Boston", "www.mmartins.com/ft/example/pictures/beenthere_boston.jpg"))
 			insDoc(aux2, gLnk(2, "New York", "www.mmartins.com/ft/example/pictures/beenthere_newyork.jpg"))
 			insDoc(aux2, gLnk(2, "Washington", "www.mmartins.com/ft/example/pictures/beenthere_washington.jpg"))
     aux1 = insFld(foldersTree, gFld("Test A", "javascript:parent.op()"))
		      aux2 = insFld(aux1, gFld("Test B", "http://www.mmartins.com/ft/example/pictures/beenthere_athens.jpg"))
              insDoc(aux1, gLnk(1, "Test C", "www.mmartins.com/panoramas"))
