<?php

//%%%%%%	File Name index.php 	%%%%%
define('_MD_SEARCH', '検索');
define('_MD_PLZENTER', '必要なデータを全て入力してください。');
define('_MD_SEARCHRESULTS', '検索結果');
define('_MD_NOMATCH', '該当データが見つかりませんでした。');
define('_MD_FOUND', '<strong>%s</strong>件のデータが見つかりました。');
define('_MD_SHOWING', '（%d ～ %d 件目を表示）');
define('_MD_ANY', 'いずれか（OR検索）');
define('_MD_ALL', 'すべて（AND検索）');
define('_MD_EXACT', 'フレーズ');
define('_MD_SHOWALLR', 'すべて表示');
define('_MD_NEXT', '次のページ >>');
define('_MD_PREVIOUS', '<< 前のページ');
define('_MD_KEYWORDS', 'キーワード');
define('_MD_TYPE', '検索の種類');
define('_MD_SEARCHIN', '検索対象のページ');
define('_MD_KEYTOOSHORT', 'キーワードは半角 %s 字、全角 %s 字以上で指定してください。');
define('_MD_KEYIGNORE', '文字数が半角<strong>%s</strong>字、全角<strong>%s</strong>字未満のキーワードは無視されます。');
define('_MD_SEARCHRULE', '検索上の注意');
define('_MD_IGNOREDWORDS', '次の語句は短すぎる（%u 文字以下）ため検索に使用されていません。');
define('_MD_UNABLE_TO_SEARCH', '検索できるページがありません。');
define('_MD_KEY_WORD_SUG', '上のキーワードに類似して下記のキーワードもお試し下さい。');
define('_MD_KEY_SPACE', '複数のキーワードで検索する場合は<strong>スペース</strong>で区切って下さい。');
define('_MD_ZENKAKU_EISU', '(全角文字)');
define('_MD_HANKAKU_EISU', '(半角文字)');
define('_MD_SHOW_CONTEXT', '本文を表示する');
//下記は必要がない限り変更しないこと。
define('_MD_NBSP', '　'); //←全角スペース(キーワードを区切るためのもの)
define('_MD_PREG_ZESU', '/\xA3[\xC1-\xFA]/');
define('_MD_PREG_HESU', '/[A-Za-z0-9]/');
define('_MD_PREG_ZKANA', '/\xA5[\xA1-\xF6]/');
define('_MD_PREG_HKANA', '/\x8E[\xA6-\xDF]/');
