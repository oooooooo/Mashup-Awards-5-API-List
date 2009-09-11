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

function html_footer() {
  return <<<_HTML_
  </body>
</html>

_HTML_;
}

function html_header() {
  return <<<_HTML_
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="common.css" rel="stylesheet" type="text/css" />
    <title>Mashup Awards 5 API List</title>
  </head>
  <body>
_HTML_;
}

/**
 * View
 *
 * @param  array  $data
 * @return string
 */

function view($data) {
  ob_start();
  print html_header();

  foreach ($data as $page=>$match_list) {
    foreach ($match_list as $match) {
      print <<<_HTML_
<h3>{$match['title']}</h3>
<span class="category">{$match['category']}</span>
<p>{$match['guide']}</p>

_HTML_;
    }
    ob_flush();
    flush();
  }

  print html_footer();
  ob_flush();
  flush();
}

/*
 * 各ページから API を取得して出力
 */

$data = data();
view($data);
