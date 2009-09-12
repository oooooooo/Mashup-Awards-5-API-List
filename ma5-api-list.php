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

define('MAX_PAGE', 10);

error_reporting(E_ALL);
set_time_limit(0);

/**
 * 粘り強い file_get_contents()
 * - file_get_contents() はたまに取得に失敗するので 3 回試す
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

function getAPI($url, $page) {
  $data = array();
  foreach (getAPIData($url, $page) as $api_data) {
    $data[] = array(
      'title'    => $api_data['title'],
      'category' => $api_data['category'],
      'guide'    => $api_data['guide'],
      );
  }

  return $data;
}

/**
 * MA5 API List を解析する正規表現
 *
 * @return string
 */

function getAPIData($url, $page) {
  $file = f("$url$page");
  
  $regex_open     = '<div class="entry_list_wrap">.+?<p class="thumb">.+?</p>';
  $regex_title    = '<h3>(?P<title>.+?)</h3>';
  $regex_category = '<span class="category">(?P<category>.+?)</span>';
  $regex_guide    = '<p>提供：(?P<guide>.+?)</p>';
  $regex_close    = '</div>';

  $regex = "{$regex_open}.+?{$regex_title}.+?{$regex_category}.+?{$regex_guide}.+?{$regex_close}";

  preg_match_all("|$regex|ms", $file, $match_all, PREG_SET_ORDER);  

  return $match_all;
}

/**
 * カテゴリ ( url と category_name の配列 ) を返す
 *
 * @return array
 */

function getCategory() {
  $category_raw_data     = getCategoryRawData();
  $category_url_and_name = getCategoryUrlAndName($category_raw_data);
  return $category_url_and_name;
}

/**
 * ページからカテゴリの生データ取得
 *
 * @return string
 */

function getCategoryRawData() {
  $file = f('http://mashupaward.jp/');
  
  $regex_open1 = '<a class="plugin_show_category_side_ajax_first_category" href="javascript:void\(0\)">API一覧';
  $regex_open2 = '<ul class="plugin_show_category_side_ajax_second_categories">';
  $regex_open  = "{$regex_open1}.+?{$regex_open2}";
  $regex_close = '</ul>';

  $data = '';
  if (preg_match("|{$regex_open}.+?(?<url_and_category_name>.+?){$regex_close}|ms", $file, $match)) {
    $data = $match['url_and_category_name'];
  }

  return $data;
}

/**
 * カテゴリの生データからカテゴリ名と URL 取得
 *
 * @param  string $category_raw_data
 * @return array
 */

function getCategoryUrlAndName($category_raw_data) {
  $regex_url_and_name = '<a href="(?<url>.+?)">(?<category_name>.+?)</a>';

  $data = array();
  if (preg_match_all("|$regex_url_and_name|ms", $category_raw_data, $match_all, PREG_SET_ORDER)) {
    foreach ($match_all as $match) {
      $data[$match['url']] = $match['category_name'];
    }
  }
  
  return $data;
}

/**
 * フラグメント
 *
 * @param  string  $name
 * @return string
 */

function getFragment($name) {
  return urlencode($name);
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
 * カテゴリ名
 *
 * @param  string $category_name
 * @return void
 */

function printCategoryName($category_name) {
  $fragment = getFragment($category_name);
  print <<<_HTML_
<h2><a name="$fragment">$category_name</a></h2>

<p><a href="#top">↑ top</a></p>

_HTML_;
  flushBuffers();
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
  $title = 'Mashup Awards 5 API List';
  print <<<_HTML_
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="common.css" rel="stylesheet" type="text/css" />
    <title>$title</title>
  </head>
  <body>
    <h1><a name="top">$title</a></h1>

_HTML_;
  flushBuffers();
}

/**
 * メニュー
 *
 * @return void
 */

function printMenu($category) {
  $data = array();
  foreach ($category as $url=>$category_name) {
    $fragment = getFragment($category_name);
    $data[] = <<<_HTML_
<a href="#$fragment">$category_name</a>

_HTML_;

  }

  print implode(' | ', $data);
  flushBuffers();
}

/*
 * 各ページから API を取得して出力
 */

$category = getCategory();

printHeader();
printMenu($category);

foreach ($category as $url=>$category_name) {
  printCategoryName($category_name);
  
  for ($page = 1; $page <= MAX_PAGE; $page++) {
    if ($data = getAPI($url, $page)) {
      printBody($data);
    }
    else {
      break;
    }
  }
}

printFooter();
