<?php

/*---------------------------------------------------------------------------------------------
	フォームメール - sformmmail2
	(c)sapphirus.biz

	■ファイル構成
	sformmail.php - 本体
	sformmail.css - 共通スタイルシート(CSS)
	sfm_config.php - 設定ファイル(このファイル)
	sfm_form.html - 入力フォーム用(HTML)
	sfm_confirm.html - 確認画面用(HTML)
	sfm_completion.html - 送信完了画面(HTML)
	sfm_mail_tmpl.php - メール送信用テンプレート
	sfm_reply_tmpl.php - 自動返信用テンプレート
	
	■フォームのnameに「_s」オプションをつけると必須項目扱いになります。
	　例) <input type="text" name="age_s" />
	
	■name=emailを指定するとメールアドレスとして扱われます。
	　例）<input type="text" name="email" />
	
	■name=emailcheckを指定するとメールアドレスの再入力の確認をすることができます。
	　※emailを使わない場合、emailcheckも利用しないようにして下さい。
	　例)<input type="text" name="emailcheck" />
	
	■入力フォーム用(sfm_form.html)には非表示フィールドで
	　「mode=CONFIRM」を必ず渡して下さい。これが無いと確認画面になりません。
	　例）<input name="mode" type="hidden" value="CONFIRM" />
	
	■確認画面用(sfm_confirm.html)には非表示フィールドで
	　「mode=SEND」を必ず渡して下さい。これが無いと送信処理ができません。
	　例）<input name="mode" type="hidden" value="SEND" />
	
	■入力されたメールアドレスに自動返信することができます。
	　入力フォームもしくは確認画面で「name=autoReply」に対して「value=1」を渡してください。
	　※emailまたはautoReply項目またはreply.phpファイルのどれかが無い場合は無効になります。
	　例）<input name="autoReply" type="hidden" value="1" />
	　or　<input name="autoReply" type="checkbox" value="1" />等
	　
	■ページエンコードの設定に関してデフォルト設定及び付属のHTMLファイルはUTF-8です。
	　エンコードを変更する場合、
	　sfm_form.html
	　sfm_confirm.html
	　sfm_completion.html
	　の3種類のHTMLと下記の「フォームHTMLのエンコード」を合わせてください。
	　※内部はEUC-JPとして処理しているので他のファイルは変更しないでください。
	　
	■受け取るメールアドレス先を選択することが出来ます。
	　入力画面で「name=mailToNum」に対し下記「フォームデータを受け取るメールアドレス」に
	　該当する数字を渡すとそのアドレス宛にフォームデータが届きます。
	　例）
	　<select name="mailToNum">
	　<option value="0">共通</option>
	　<option value="1">技術</option>
	　<option value="2">営業</option>
	　</select>
	
	注）バージョン1.xx とは互換性がありません
---------------------------------------------------------------------------------------------*/


/* +-+-+-+ 設定 +-+-+-+ */


/* 基本 */

// フォームデータを受け取るメールアドレス（$mailTo[0]は必須）
$mailTo[0] = 'licensecampaign@aj-tokyo.or.jp';
//$mailTo[0] = 'nagamatsu@artist-union.com';
$mailTo[1] = '';
$mailTo[2] = '';

// 受け取る時のSubject（件名）
$mailSubject = '【東京オートバイ協同組合】原付免許取得支援キャンペーンへエントリーがありました';

// 自動返信のSubject（件名）
$replySubject = '【東京オートバイ協同組合】原付免許取得支援キャンペーン　エントリー受付完了';

// フォームHTMLのエンコード（SJIS／EUC-JP／UTF-8）
$baseEnc = 'UTF-8';

// テキストの最大入力文字数
$maxText = 2000;



/* オプション（必要時のみ設定） */

// BCCで受け取りが必要な場合は設定（フォームからのメールアドレス）
$mailBcc = '';

// BCCで受け取りが必要な場合は設定（自動返信メールアドレス）
$replyBcc = '';

// 自動返信時の送信元メールアドレス
$replyAddress = 'licensecampaign@aj-tokyo.or.jp';

// 自動返信時の送信元メールアドレスに付加する名前
$replyName = '原付免許取得支援キャンペーン';

// 送信エラー等で戻ってくるメールの受け取りを変更する場合のメールアドレス
$returnPath = '';

// リファラによる外部使用の制限（する:1/しない:0）
$refCheck = 0;

// 文字化けする場合は1にしてみて下さい
$ill_char = 0;

// 「表」などの文字のあとに「\」が付いてしまう場合は1にしてみて下さい
$ill_slash = 0;

// ヘッダが本文に入ってきてしまう場合は1にしてみて下さい
$ill_header = 0;

?>
