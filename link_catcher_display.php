<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>link grabber</title>
 <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
 <meta name="robots" content="noindex">
 <link rel="stylesheet" type="text/css" href="link_catcher.css" />
</head>

<body>

<script async src="http://platform.twitter.com/widgets.js" charset="utf-8"></script>

<div id="container">
<div id="firstchild">

<?php
require('link_catcher_getmore.php');
?>

</div>
</div>

<div id="footer">
<?php
print("<hr /><a href=\"?amount=$amount&starting=$until\">get newer tweets</a>");
?>
</div>

<?php
print('<div id="f00ter" onclick="callelements(' . $amount . ',' . $until . ');" style="font:20px bold; text-align: center;">
click for more
</div>');
?>

</body>
</html>
<script type="text/javascript">
function callelements(amount,until){
  if(window.XMLHttpRequest)
    var xmlhttp = new XMLHttpRequest();
  else
    if(window.ActiveXObject)
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    else
      alert ("Bummer! Your browser does not support XMLHTTP!");                 
  var url="link_catcher_getmore.php?amount=" + amount + "&starting=" + until;
  xmlhttp.onreadystatechange = function(){
    if(xmlhttp.readyState == 4){
      var newuntil = until - amount;
      var newdiv=document.createElement("div");
      newdiv.innerHTML = xmlhttp.responseText;
      newdiv.id = "child" + newuntil;
      document.getElementById("container").appendChild(newdiv);
      document.getElementById("f00ter").setAttribute('onclick','callelements(' + amount + ',' + newuntil + ');');
      twttr.widgets.load();
    }
  }
  xmlhttp.open("GET",url,true);
  xmlhttp.send();
}
</script>
