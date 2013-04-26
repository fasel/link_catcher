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

<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

<div id="container">
 <div id="firstchild">
<?php
require('link_catcher_getmore.php');
?>
 </div>
</div>
<hr />

<div id="header" class="header">
<?php 
$p = $past + 1;
$l = $past - 1;
print('
<a href="link_catcher_display.php?amount='.$amount.'&past='.$p.'&starting='.$starting.'" class="header">&lt;&lt;&lt; past</a>
 | 
<a href="link_catcher_display.php?amount='.$amount.'&past='.$l.'&starting='.$starting.'" class="header">present &gt;&gt;&gt;</a>
');
?>
</div>

<?php
print('
<div id="footer" onclick="callelements(' . $amount . ',' . $until . ',' . $past . ');">
click for more
</div>
');
?>

</body>
</html>
<script type="text/javascript">
console.log('init');
<?php if (isset($amount)) { echo "var amount = $amount;\n"; }; ?>
<?php if (isset($starting)) { echo "var until = $until;\n"; }; ?>
<?php if (isset($past)) { echo "var past = $past;\n"; }; ?>
if(window.addEventListener){
  window.addEventListener('scroll',scroll);
}
else if(window.attachEvent){
  window.attachEvent('onscroll',scroll);
}
function scroll(ev){
  // DETECT UP/DOWN SCROLL
  var delta = 0;
  var scroll = document.documentElement.scrollTop;
  if (window.lastscroll){ delta = scroll - window.lastscroll };
  window.lastscroll = scroll;
  if (delta < 0){ 
    // UP
    console.log("UP"); 
  }else{
    // DOWN
    console.log("DOWN");
  }
  var st = Math.max(document.documentElement.scrollTop,document.body.scrollTop);
  var starter = 500; //px before reaching bottom
  // DETECT SCROLL POSITION
  if(!st){
    // TOP
    console.log('top');
  }else if((st+document.documentElement.clientHeight)>=document.documentElement.scrollHeight ){
    // BOTTOM
    console.log('bottom');
    if (!window.isloading){ 
      console.log('bottom loading: amount '+window.amount+' until '+window.until+' past '+past);
      window.isloading = true; // FIXME: prevent loading on parallel events
      callelements(window.amount, window.until); 
    }
  }else if((st+starter+document.documentElement.clientHeight)>=document.documentElement.scrollHeight ){
    //BUMP
    console.log('bump');
    //if (!window.isloading){ 
    //  console.log('bump loading: amount '+window.amount+' until '+window.until);
    //  window.isloading = true; // prevent loading on parallel events
    //  callelements(window.amount, window.until); 
    //}
  }
}
function callelements(amount,until,past){
  if(window.XMLHttpRequest){
    var xmlhttp = new XMLHttpRequest();
  }
  else{
    if(window.ActiveXObject){
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else{
      alert ("Bummer! Your browser does not support XMLHTTP!");                 
    }
  }
  var url = "link_catcher_getmore.php?amount=" + amount + "&starting=" + until;
  if (past){
    url = url + "&past=" + past;
  }
  xmlhttp.onreadystatechange = function(){
    if(xmlhttp.readyState == 4){
      if (xmlhttp.responseText){
        var starting = until + amount;
        var newdiv = document.createElement("div");
        newdiv.innerHTML = xmlhttp.responseText;
        newdiv.id = "child" + starting;
        document.getElementById("container").appendChild(newdiv);
        var last = document.getElementById("container").lastChild.childNodes;
        // don't add empty div
        if (last.length > 0) { 
          var lastid = "";
          for (i=0; i<last.length; i++){
            if (last[i].nodeName == "A"){
              lastid=last[i].getAttribute("id");
            }
          }
          lastid++;
          if (lastid < starting) { starting = lastid; };
          document.getElementById("footer").setAttribute('onclick','callelements(' + amount + ',' + starting + ',' + past + ');');
          window.amount = amount;
          window.until = starting;
          window.past = past;
          twttr.widgets.load();
          //// determine how many entries are loaded by counting <hr>
          //docCount = document.getElementById("container").getElementsByTagName('hr').length;
          //// determine how many batches are loaded by counting <div>
          //divCount = document.getElementById("container").getElementsByTagName('div').length;
          //if (docCount > 300 && divCount > 3){
          //  first = document.getElementById("container").firstElementChild;
          //  document.getElementById("container").removeChild(first);
          //}
        }
      }
    }
    delete window.isloading;
  }
  xmlhttp.open("GET",url,true);
  xmlhttp.send();
}
</script>
