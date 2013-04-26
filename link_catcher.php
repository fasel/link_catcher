<?php

/*
//TODO:
bugs: 
- endless scroll in past doesn't work 
- new elements overlap in past mode
- emoji is a box

features:
remove older elements (stops rendering at ~1.5MB)
reload newer again when scrolling upwards
colorize users
maybe improve performance with php flush()
web UI for tokens / multiuser
parse links/hashtags/usernames
store result properly (some database)
track 200 or more caught tweets (missing tweets)
error logging (implement debug flag)
implement (post)privacy flag (choose to store/print protected)
implement link-only flag / view
dont reuse variables / unclutter */ 

require_once ('link_catcher_globals.php');

// feature switches
$feature_stats = 1;
$feature_rt = 0;
$feature_unfollow = 0;

// loop existing data to find proper id
// look in yesterdays file, too, to prevent full query after midnights file rotation
function get_id($json) {
  $json = array_reverse($json);
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
$reply = array_reverse($reply);
//print_r($reply);

// if the tweet contains urls we create and store html to embed later
function get_html($cb,$id,$protected,$text) {
  if ($protected != 1) {
    $reply_embed = (array) $cb->statuses_oembed("id=" . $id . "&omit_script=true");
    if ($reply_embed['httpstatus'] == 200 && $reply_embed['html']) {
      return $reply_embed['html'];
    } else {
      $html = " <blockquote class=\"twitter-tweet\">";
      $html .= "<p class=\"error\">" . $text . "</p>";
      $html .= "</blockquote>";
      return $html;
    }  
  } else {
    $html = " <blockquote class=\"twitter-tweet\">";
    $html .= "<p class=\"protected\">" . $text . "</p>";
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

$out = array_merge($json, $reply);
// TODO: implement some error handling 
// we shouldnt overwrite anything if things went wrong
file_put_contents($file, json_encode($out));

if ($feature_rt) {
// look up whose RTs you turned off
$reply_rt = (array) $cb->friendships_noRetweets_ids();
unset($reply_rt['httpstatus']);
$last_rt = end($reply_rt);
$out_rt = '';

foreach ($reply_rt as $key => $tweet) {
  $out_rt .= $tweet;
  if ($tweet != $last_rt) {
    $out_rt .= ",";
  }
}

$richout = (array) $cb->users_lookup("user_id=$out");

foreach ($richout as $key => $user) {
  if (!isset($user->screen_name)) { continue; }; // skip empty records
  print("https://twitter.com/".$user->screen_name."/\n");
}
} //

if ($feature_stats) {
function print_stats($source, $resource) {
  print($source->remaining." of ".$source->limit." $resource \n");
}
$reply_rate = $cb->application_rateLimitStatus();
print_stats($reply_rate->resources->application->{'/application/rate_limit_status'}, "/application/rate_limit_status");
print_stats($reply_rate->resources->statuses->{'/statuses/oembed'}, "/statuses/oembed");
print_stats($reply_rate->resources->users->{'/users/lookup'}, "/users/lookup");
print_stats($reply_rate->resources->statuses->{'/statuses/home_timeline'}, "/statuses/home_timeline");
print_stats($reply_rate->resources->friendships->{'/friendships/no_retweets/ids'}, "/friendships/no_retweets/ids");
print_stats($reply_rate->resources->followers->{'/followers/ids'}, "/followers/ids");
print("\n");
} //

?>
