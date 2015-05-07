#!/usr/bin/env php
<?php

  define('JEKYLL_POST_DIR', $_ENV['HOME'] . '/Sites/%s' . '/_posts');
  define('POST_DIVIDER', '---');

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
  $tumblrsuffix = '/api/read/json/?type=text&num=50&start=0&callback=?';
  $tumblrapi = $tumblrsite . $tumblrsuffix;
  $tumblrdata = crop_tumblr_out(file_get_contents($tumblrapi));
  $tumblrmap = json_decode($tumblrdata, true);
  generate_tumblr_report($tumblrmap);
  $jekylldir = sprintf(JEKYLL_POST_DIR, $argv[2]);
  clean_jekyll_post_dir($jekylldir);
  generate_jekyll_posts($tumblrmap, $jekylldir);

  // END MAIN

  function clean_jekyll_post_dir ($jekylldir) {
    echo "jekylldir=$jekylldir\n";
    chdir($jekylldir);
    echo `ls -l`;
  }

  function crop_tumblr_out ($tumblrdata) {
    return substr($tumblrdata, 22, -2);
  }

  function generate_jekyll_posts ($tmap, $jdir) {
    foreach ($tmap['posts'] as $post) {
      $outfn = $jdir 
        . '/'
        . substr($post['date-gmt'], 0, 10) 
        . '-'
        . $post['slug']
        . '.html';
      $outfp = fopen($outfn, 'w');
      fwrite($outfp, POST_DIVIDER . "\n");
      fwrite($outfp, "layout: post\n");
      fwrite(
        $outfp, 
        "title: " 
          . '"' 
          . addcslashes($post['regular-title'], '"') 
          . '"'
          . "\n"
      );
      fwrite($outfp, POST_DIVIDER . "\n");
      fwrite($outfp, $post['regular-body']);
      fclose($outfp);
    }
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
