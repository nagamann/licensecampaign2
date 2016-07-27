<?php
include_once('order_number_by_file.php'); // 注文番号読み込み
/*----------------------------------------------------------------------------------
	フォームメール - sformmmail2
	(c)sapphirus.biz  2008-06-08
----------------------------------------------------------------------------------*/

$scriptVersion = '2.20';

include_once('sfm_config.php'); // 設定ファイル読み込み

if (!$mailTo[0]) {
	Err('受取先メールアドレスが設定されてません');
}
$mode = $_POST['mode'];
$script_name = preg_replace('/.+\/(.*)/', '$1', $_SERVER['REQUEST_URI']);

switch ($mode) {
case 'SEND': //メール送信
	session_cache_limiter('nocache');
	session_start();
	if (!$_SESSION['SFM']) {
		Err('セッションデータがありません');
	}
	$mailTo = ($mailTo[$_SESSION['SFM']['mailToNum']]) ? $mailTo[$_SESSION['SFM']['mailToNum']] : $mailTo[0];

	// 指定先にメール送信
	$mailFrom = (!$_SESSION['SFM']['email']) ? 'S.B.FormMail' : $_SESSION['SFM']['email'];
	include_once('sfm_mail_tmpl.php'); // メール送信用テンプレート
	SendMail($mailTo, $mailSubject, $mailMessage, $mailFrom, $mailBcc);

	// メール自動返信
	if (($_POST['autoReply'] || $_SESSION['SFM']['autoReply']) && $_SESSION['SFM']['email'] && is_file('sfm_reply_tmpl.php')) {
		include_once('sfm_reply_tmpl.php'); // 自動返信用テンプレート
		$replyAddress = ($replyAddress) ? $replyAddress : $mailTo;
		if ($replyName) {
			$replyName = EnCode($replyName, 'JIS', 'EUC-JP');
			$replyAddress = '=?iso-2022-jp?B?'.base64_encode($replyName) . '?= <' . $replyAddress . '>';
		}
		SendMail($_SESSION['SFM']['email'], $replySubject, $replyMessage, $replyAddress, $replyBcc);
	}

	// unset($_SESSION['SFM']);
	include_once('sfm_completion.html'); // 送信完了画面HTMLテンプレート
	break;

case 'CONFIRM': // データ処理と確認
	if ($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] && $refCheck) {
		Err('外部から利用は出来ません');
	}
	session_cache_limiter('none');
	session_start();
	unset($_SESSION['SFM']);

	foreach ($_POST as $key => $value) {
		if (is_array($value)) {
			$value = implode("\t", $value);
		}
		if (!$ill_slash) {
			$value = (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
		}
		if (!$ill_char) {
			$value = EnCode($value, 'EUC-JP', $baseEnc);
		}
		$value = mb_convert_kana($value, 'KV', 'EUC-JP');
		$name = preg_replace('/(.+)_s$/', "$1", $key);
		if (preg_match('/_s$/', $key) && $value == '') {
			$_SESSION['SFM'][$name] = '::INPUT ERROR::';
			$error = 1;
		} elseif ($name == 'email' && $value) {
			if (!preg_match("/^[\w\-\.]+\@[\w\-\.]+\.([a-z]+)$/", $value)) {
				$_SESSION['SFM']['email'] = '::EMAIL ERROR::';
				$error = $email = 1;
			} else {
				$_SESSION['SFM']['email'] = $email = $value;
			}
		} elseif ($name == 'emailcheck') {
			if ($email != 1 && $email != $value) {
				$_SESSION['SFM']['email'] = '::EMAIL CHECK ERROR::';
				$error = 1;
			}
		} elseif ($maxText && strlen($value) > $maxText) {
			$_SESSION['SFM'][$name] = '::MAXTEXT ERROR::';
			$error = 1;
		} else {
			$_SESSION['SFM'][$name] = $value;
		}
	}
	$_SESSION['SFM']['InputErr'] = $error;
	$sfm_script = $script_name . ((SID) ? '?'.strip_tags(SID) : '');
	include_once('sfm_confirm.html');	// 確認画面用HTMLテンプレート
	break;

default: // 入力フォーム表示
	session_cache_limiter('private_no_expire');
	session_start();
	unset($_SESSION['SFM']);
	$sfm_script = $script_name;
	include_once('sfm_form.html');	// 入力フォーム用HTMLテンプレート
}
exit;


// *** HTMLデータ整形
function FORM_DATA_H($name) {
	global $baseEnc, $maxText;
	$errArray = array(
		'::INPUT ERROR::'=>'必須項目です',
		'::EMAIL ERROR::'=>'メールアドレスが正しくありません',
		'::EMAIL CHECK ERROR::'=>'メールアドレスが一致しません',
		'::MAXTEXT ERROR::'=>'文字数が多すぎます（' . number_format($maxText) . '字まで）'
	);
	$value = $_SESSION['SFM'][$name];
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	$value = str_replace("\t", "\n", $value);	// 表示用に複数項目を改行
	$value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'EUC-JP'));
	$value = (preg_match('/::.+::/', $value)) ? '<span class="ERR">' . $errArray[$value] . '</span>' : $value;
	$value = ($value != '') ? $value : '&nbsp;';
	return EnCode($value, $baseEnc, 'EUC-JP');
}


// *** MAILデータ整形
function FORM_DATA_M($name) {
	$value = $_SESSION['SFM'][$name];
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	$value = str_replace("\t", ',', $value);	// メール用に複数項目をカンマ区切り
	return $value;
}


// *** メール送信エンコード
function SendMail($to, $sub, $msg, $from, $bcc) {
	global $scriptVersion;
	global $returnPath;
	global $ill_header;
	$sub = EnCode($sub, 'JIS', 'EUC-JP');
	$msg = EnCode($msg, 'JIS', 'EUC-JP');
	$rn = array("\r\n", "\n", "\r");
	$msg = str_replace($rn, "\n", $msg);

	// ヘッダ生成
	$rtf = ($ill_header) ? "\n" : "\r\n";
	$header = 'From: ' . $from . $rtf;
	$header .= 'Bcc: ' . $bcc . $rtf;
	$header .= 'X-Mailer: Sapphirus.Biz Formmail (Ver. ' . $scriptVersion . '/PHP)' . $rtf;
	$header .= 'Mime-Version: 1.0' . $rtf;
	$header .= 'Content-Type: text/plain; charset=ISO-2022-JP' . $rtf;
	$header .= 'Content-Transfer-Encoding: 7bit';

	$sub = '=?iso-2022-jp?B?' . base64_encode($sub) . '?=';	// Base64エンコード
	if ($returnPath) {
		mail($to, $sub, $msg, $header, '-f' . $returnPath);
	} else {
		mail($to, $sub, $msg, $header);
	}
	return;
}


/* ::ユーザー情報取得 */
function USERINFO() {
	return @gethostbyaddr($_SERVER['']) . ""
	. $_SERVER[''] . ""
	. date("Y/m/d - H:i:s");
}



/* ::文字エンコード変換 */
function EnCode($value, $chg, $org) {
	if (!extension_loaded('mbstring')) {
		Err('mbstring関数が利用できません');
	}
	return @mb_convert_encoding($value, $chg, $org);
}


/* ::エラー表示HTML */
function Err($err) {
echo <<<EOM
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" />
<title>エラー：$err</title>
<script src="/js/global.js" language="javascript"></script>
</head>
<body style="font-size: 12px; line-height: 1.8em;">
<strong>エラー : </strong>$err<br>
<a href="javascript:history.back()" style="display:inline-block; vertical-align:middle; margin-left:5px;"><img src="../images/second/btnBack.png" width="150" height="60" alt="前の画面に戻る" /></a>
</body></html>
EOM;
exit;
}

?>
