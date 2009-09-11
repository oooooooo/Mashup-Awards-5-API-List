<?php // -*- encoding: utf-8 -*-
/**
 * Mashup Awards 5 API List
 *
 * API リストを一覧にする
 * http://mashupaward.jp/
 *
 * @since  2009-09-11
 * @author oooooooo
 */

define('MAX_PAGE', 100);

error_reporting(E_ALL);
set_time_limit(0);

/**
 * MA5 API List からデータを取得
 *
 * @param  int   $page
 * @return array
 */

function data() {
  $data = array();
  $regex = getRegex();

  for ($page = 1; $page <= MAX_PAGE; $page++) {
    $file = f("http://mashupaward.jp/category/3176/$page/");
    if (preg_match_all("|$regex|ms", $file, $match_all, PREG_SET_ORDER)) {
      foreach ($match_all as $match) {
        $data[$page][] = array(
          'title'    => $match['title'],
          'category' => $match['category'],
          'guide'    => $match['guide'],
          );
      }
    }
  }

  return $data;
}

/**
 * 粘り強い file_get_contents()
 * - 取得に失敗しても 3 回試す
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
 * Category を trim
 *
 * @param  string $category
 * @return string
 */

function filterCategory($category) {
  return preg_replace('/\s/', '', $category);
}

/**
 * MA5 API List を解析する正規表現
 *
 * @return string
 */

function getRegex() {
  $open     = '<div class="entry_list_wrap">.+?<p class="thumb">.+?</p>';
  $title    = '<h3>(?P<title>.+?)</h3>';
  $category = '<span class="category">(?P<category>.+?)</span>';
  $guide    = '<p>提供：(?P<guide>.+?)</p>';
  $close    = '</div>';

  $regex = "{$open}.+?{$title}.+?{$category}.+?{$guide}.+?{$close}";

  return $regex;
}

/**
 * View
 *
 * @param  array  $data
 * @return string
 */

function view($data) {
  ob_start();
  foreach ($data as $page=>$match_list) {
    foreach ($match_list as $match) {
      $category = filterCategory($match['category']);
      print <<<_HTML_
{$match['title']}<br>
{$category}<br>
{$match['guide']}
<hr>
_HTML_;
    }
    ob_flush();
    flush();
  }
}

/*
 * 各ページから API を取得して出力
 */

$data = data();
view($data);
