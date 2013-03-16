<?php

require ('link_catcher_globals.php');

$file = $datapath . 'results' . date("Ymd", strtotime("-0 day")) . '.json';
$json = json_decode(file_get_contents($file), true);

// print_r($json);

// traverse tweets. old to new. x at a time
//TODO: sanity check
//security + care about case when remaining is lower than until/amount (return only whats there to not start in the nirvana) mabye in the js in _display
$amount = $_GET["amount"]; 
$amount = isset($amount)?$amount:25;
$starting = $_GET["starting"];
// set to last element if not specified otherwise
if (!$starting) {
  end($json);
  $starting = key($json);
}
$until = $starting - $amount;

function print_via($screen_name, $id, $date) {
    print(" | via <a href=\"https://twitter.com/$screen_name/status/$id\" title=\"$date\">@$screen_name</a><br />\n");
}
for ($i = $starting; $i > $until; $i--) {
  unset($id, $screen_name, $date, $text);
  if (!isset($json[$i]['id_str'])) { continue; }; // skip empty records
  $id = $json[$i]['id_str'];
  $screen_name = $json[$i]['user']['screen_name'];
  $date = $json[$i]['created_at'];
  print("<hr />\n");
  if ($json[$i]['urlhtml']) { 
    print($json[$i]['urlhtml']);
    print_via($screen_name, $id, $date);
  } else {
    $text = $json[$i]['text'];
    print($text);
    print_via($screen_name, $id, $date); 
  }
}
  
?>
