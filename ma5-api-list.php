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
 * バッファ出力
 *
 * @return void
 */

function flushBuffers(){
  @ob_end_flush();
  @ob_flush();
  flush();
  ob_start();
}

/**
 * MA5 API List からデータを取得
 *
 * @param  int   $page
 * @return array
 */

function getData($page) {
  $data = array();
  $regex = getRegex();

  $file = f("http://mashupaward.jp/category/3176/$page/");
  if (preg_match_all("|$regex|ms", $file, $match_all, PREG_SET_ORDER)) {
    foreach ($match_all as $match) {
      $data[] = array(
        'title'    => $match['title'],
        'category' => $match['category'],
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
 * API 紹介
 *
 * @param  array  $data
 * @return void
 */

function printBody($data) {
  foreach ($data as $match) {
    print <<<_HTML_
<h3>{$match['title']}</h3>
<span class="category">{$match['category']}</span>
<p>{$match['guide']}</p>
<div class="line"><hr /></div>

_HTML_;
    flushBuffers();
  }
}

/**
 * フッタ
 *
 * @return void
 */

function printFooter() {
  print <<<_HTML_
    <p>done.</p>

  </body>
</html>

_HTML_;
  flushBuffers();
}

/**
 * ヘッダ
 *
 * @return void
 */

function printHeader() {
  print <<<_HTML_
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="common.css" rel="stylesheet" type="text/css" />
    <title>Mashup Awards 5 API List</title>
  </head>
  <body>
    <h1>Mashup Awards 5 API List</h1>
    <h2>ページ送りが面倒なあなたに</h2>
_HTML_;
  flushBuffers();
}

/*
 * 各ページから API を取得して出力
 */

printHeader();

for ($page = 1; $page <= MAX_PAGE; $page++) {
  if ($data = getData($page)) {
    printBody($data);
  }
  else {
    break;
  }
}

printFooter();
