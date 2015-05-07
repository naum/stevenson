#!/usr/bin/env php
<?php

  // BEGIN MAIN

  $argcount = count($argv) - 1;
  if ($argcount < 2) {
    $scriptname = basename($argv[0]);
    die("Usage: {$scriptname} fromtumblr tolocal\n");
  }

  $tumblrsite = $argv[1];
  if (! starts_with('http://', $tumblrsite)) {
    $tumblrsite = 'http://' . $tumblrsite;
  }
  $tumblrsuffix = '/api/read/json/?type=text&num=50&start=50&callback=?';
  $tumblrapi = $tumblrsite . $tumblrsuffix;
  $tumblrdata = crop_tumblr_out(file_get_contents($tumblrapi));
  $tumblrmap = json_decode($tumblrdata, true);
  generate_tumblr_report($tumblrmap);

  // END MAIN

  function crop_tumblr_out ($tumblrdata) {
    return substr($tumblrdata, 22, -2);
  }

  function generate_tumblr_report ($tmap) {
    $sitetitle = $tmap['tumblelog']['title'];
    $postcount = $tmap['posts-total'];
    echo $sitetitle . "\n";
    echo $postcount . "\n\n";
    foreach ($tmap['posts'] as $post) {
      echo $post['regular-title'] . "\n";
    }
  }

  function starts_with ($patt, $str) {
    $patt = preg_quote($patt, '/');
    return preg_match('/^' . $patt . '/', $str);
  }

?>
