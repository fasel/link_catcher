<?php

/*
//TODO:
bugs: 
- not rendered tweets (protected,RTs?)
- highlight not-rendered tweets with links
- cut off text (>140 chars, RTs)
- Notices

features:
parameters since, how many
print full text from RTs, but still show its a RT
endless scrolling (stops rendering at ~1.5MB)
make it browsable to previous days
make this user friendly:
- autoloop (no need for a cron job)
- manual refresh
- web UI for tokens
parse links (i.e. in protected html)
parse twitter user names
store result properly (some database)
track 200 or more catched tweets (missing tweets)
error logging
dont reuse variables / unclutter */ 

require_once ('link_catcher_globals.php');

// loop existing data to find proper id
// first hit should be latest id
// look in yesterdays file, too, to prevent full query after midnights file rotation
function get_id($json) {
  foreach ( $json as $tweet ) {
    $id = $tweet['id_str'];
    if ($id) { 
      print("found id: " . $id . "<br>\n");
      return $id; 
    }
  }
}
$file = $datapath . 'results' . date("Ymd") . '.json';
$file1 = $datapath . 'results' . date("Ymd", strtotime("-1 day")) . '.json';
$json = (array) json_decode(file_get_contents($file), true);
// print_r($json);
$json1 = (array) json_decode(file_get_contents($file1), true);
($id = get_id($json)) || ($id = get_id($json1));
unset($json1);

// query only what we need
$count = 200; // 200 is max
$since_id = "$id";
$since = "&since_id=$since_id";
$param = "count=$count";
// omit if no id is present 
// should only happen on first run
if ($since_id) { $param .= "$since"; };
print("param: " . $param . "<br>\n");

$reply = (array) $cb->statuses_homeTimeline($param);
// print_r($reply);

// if the tweet contains urls we create and store html to embed later
function get_html($cb,$id,$protected,$text) {
  if ($protected != 1) {
    $reply_embed = (array) $cb->statuses_oembed("id=" . $id . "&omit_script=true");
    return $reply_embed['html'];
  } else {
    $html = " !PROTECTED! <blockquote class=\"twitter-tweet\">";
    $html .= "<p>" . $text . "</p>";
    $html .= "</blockquote>";
    return $html;
  }
}

foreach ( $reply as $key => $tweet ) {
  unset($id, $date, $text, $protected);
  if (!isset($tweet->id_str)) { continue; }; // skip empty records
  $id = $tweet->id_str;
  print("id: " . $id . "<br>\n");
  // retweet
  if (isset($tweet->retweeted_status)) {
    if ($tweet->retweeted_status->entities->urls or isset($tweet->retweeted_status->entities->media)) { 
      $id = $tweet->retweeted_status->id_str;
      $protected = $tweet->retweeted_status->user->protected;
      $text = $tweet->retweeted_status->text;
      $reply[$key]->urlhtml = get_html($cb,$id,$protected,$text);
    }
  // native tweet
  } else {
    if ($tweet->entities->urls or isset($tweet->entities->media)) { 
      $protected = $tweet->user->protected;
      $text = $tweet->text;
      $reply[$key]->urlhtml = get_html($cb,$id,$protected,$text); 
    }
  } 
}

$out = array_merge($reply, $json);
// TODO: implement some error handling 
// we shouldnt overwrite anything if things went wrong
file_put_contents($file, json_encode($out));

$reply_rate = $cb->application_rateLimitStatus();
print(
  "<br>\n"
. $reply_rate->resources->application->{'/application/rate_limit_status'}->remaining
.  " of "
. $reply_rate->resources->application->{'/application/rate_limit_status'}->limit
.  " /application/rate_limit_status "
. "<br>\n"
. $reply_rate->resources->statuses->{'/statuses/oembed'}->remaining
.  " of "
. $reply_rate->resources->statuses->{'/statuses/oembed'}->limit
.  " /statuses/oembed "
. "<br>\n"
. $reply_rate->resources->statuses->{'/statuses/home_timeline'}->remaining
.  " of "
. $reply_rate->resources->statuses->{'/statuses/home_timeline'}->limit
.  " /statuses/home_timeline "
. "<br>\n"
);

?>
