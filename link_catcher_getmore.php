<?php

require ('link_catcher_globals.php');

//TODO: sanity check
$past = $_GET["past"];
$past = isset($past)?$past:"0";
$file = $datapath . 'results' . date("Ymd", strtotime("-$past days")) . '.json';
$json = json_decode(file_get_contents($file), true);

// print_r($json);

// traverse tweets. old to new. x at a time
//TODO: sanity check
$amount = $_GET["amount"]; 
$amount = isset($amount)?$amount:25;
$starting = $_GET["starting"];
$starting = isset($starting)?$starting:0;
$until = $starting + $amount;
$last = get_last($json);
$last++;
if ($last < $until) { $until = $last; };
function get_last($json) {
  end($json);
  return key($json);
}
function print_via($screen_name, $id, $date, $i) {
    print(" | via <a href=\"https://twitter.com/$screen_name/status/$id\" id=\"$i\" title=\"$date\">@$screen_name</a>\n");
}
for ($i = $starting; $i < $until; $i++) {
  unset($id, $screen_name, $date, $text);
  if (!isset($json[$i]['id_str'])) { continue; }; // skip empty records
  $id = $json[$i]['id_str'];
  $screen_name = $json[$i]['user']['screen_name'];
  $date = strtotime($json[$i]['created_at']);
  $date = date('D M d H:i:s O Y', $date); // convert to local tz
  print("<hr />\n");
  if ($json[$i]['urlhtml']) { 
    // this is fetched in link_catcher.php if it contains a link
    print($json[$i]['urlhtml']);
    print_via($screen_name, $id, $date, $i);
  } elseif ($json[$i]['retweeted_status']['text']) {
    // text-only retweet
    $text = $json[$i]['retweeted_status']['text'];
    $screen_name_rt = $json[$i]['retweeted_status']['user']['screen_name'];
    print("<p class=\"tweet\">");
    print("RT @$screen_name_rt: $text");
    print_via($screen_name, $id, $date, $i); 
    print("</p>");
  } else {
    // text-only native tweet
    $text = $json[$i]['text'];
    print("<p class=\"tweet\">");
    print($text);
    print_via($screen_name, $id, $date, $i); 
    print("<a href=\"?amount=$amount&past=$past&starting=$i\" class=\"layover\">xxx</a></p>");
  }
}
  
?>
