<?php // -*- encoding: utf-8 -*-
/**
 * Mashup Awards 5 API List
 *
 * API リストを一覧にする
 *
 *
 * @since      2009-09-11
 * @author     oooooooo
 */

define('MAX_PAGE', 100);

error_reporting(E_ALL);

/**
 * MA5 API List からデータを取得
 *
 * @param  int   $page
 * @return array
 */

function data($page) {
  $file = f("http://mashupaward.jp/category/3176/$page/");
  $data = array();
  $regex = regex();
  if (preg_match_all("|$regex|ms", $file, $match_all, PREG_SET_ORDER)) {
    foreach ($match_all as $match) {
      $data[] = array(
        'title'    => $match['title'],
        'category' => trim_category($match['category']),
        'guide'    => $match['guide'],
        );
    }
  }
  else {
    return false;
  }

  return $data;
}

/**
 * 粘り強い file_get_contents()
 *
 * @param  string $url
 * @return string
 */

function f($url) {
  for ($i = 0; $i < 3; $i++) {
    $file = @file_get_contents($url);
    if ($file) {
      return $file;
    }
  }
  return false;
}

/**
 * MA5 API List を解析する正規表現
 *
 * @return string
 */

function regex() {
  $open     = '<div class="entry_list_wrap">.+?<p class="thumb">.+?</p>';
  $title    = '<h3>(?P<title>.+?)</h3>';
  $category = '<span class="category">(?P<category>.+?)</span>';
  $guide    = '<p>提供：(?P<guide>.+?)</p>';
  $close    = '</div>';

  $regex = "{$open}.+?{$title}.+?{$category}.+?{$guide}.+?{$close}";

  return $regex;
}

function trim_category($category) {
  return preg_replace('/\s/', '', $category);
}

/**
 * View
 *
 * @param  array  $data
 * @return string
 */

function view($data) {
  foreach ($data as $match) {
    print <<<_HTML_
{$match['title']}<br>
{$match['category']}<br>
{$match['guide']}
<hr>
_HTML_;
  }
}

for ($page = 1; $page <= MAX_PAGE; $page++) {
  if ($data = data($page)) {
    view($data);
  }
  else {
    break;
  }
}
