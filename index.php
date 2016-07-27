<?php
include_once('order_number_by_file.php'); // ��ʸ�ֹ��ɤ߹���
/*----------------------------------------------------------------------------------
	�ե�����᡼�� - sformmmail2
	(c)sapphirus.biz  2008-06-08
----------------------------------------------------------------------------------*/

$scriptVersion = '2.20';

include_once('sfm_config.php'); // ����ե������ɤ߹���

if (!$mailTo[0]) {
	Err('������᡼�륢�ɥ쥹�����ꤵ��Ƥޤ���');
}
$mode = $_POST['mode'];
$script_name = preg_replace('/.+\/(.*)/', '$1', $_SERVER['REQUEST_URI']);

switch ($mode) {
case 'SEND': //�᡼������
	session_cache_limiter('nocache');
	session_start();
	if (!$_SESSION['SFM']) {
		Err('���å����ǡ���������ޤ���');
	}
	$mailTo = ($mailTo[$_SESSION['SFM']['mailToNum']]) ? $mailTo[$_SESSION['SFM']['mailToNum']] : $mailTo[0];

	// ������˥᡼������
	$mailFrom = (!$_SESSION['SFM']['email']) ? 'S.B.FormMail' : $_SESSION['SFM']['email'];
	include_once('sfm_mail_tmpl.php'); // �᡼�������ѥƥ�ץ졼��
	SendMail($mailTo, $mailSubject, $mailMessage, $mailFrom, $mailBcc);

	// �᡼�뼫ư�ֿ�
	if (($_POST['autoReply'] || $_SESSION['SFM']['autoReply']) && $_SESSION['SFM']['email'] && is_file('sfm_reply_tmpl.php')) {
		include_once('sfm_reply_tmpl.php'); // ��ư�ֿ��ѥƥ�ץ졼��
		$replyAddress = ($replyAddress) ? $replyAddress : $mailTo;
		if ($replyName) {
			$replyName = EnCode($replyName, 'JIS', 'EUC-JP');
			$replyAddress = '=?iso-2022-jp?B?'.base64_encode($replyName) . '?= <' . $replyAddress . '>';
		}
		SendMail($_SESSION['SFM']['email'], $replySubject, $replyMessage, $replyAddress, $replyBcc);
	}

	// unset($_SESSION['SFM']);
	include_once('sfm_completion.html'); // ������λ����HTML�ƥ�ץ졼��
	break;

case 'CONFIRM': // �ǡ��������ȳ�ǧ
	if ($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] && $refCheck) {
		Err('�����������ѤϽ���ޤ���');
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
	include_once('sfm_confirm.html');	// ��ǧ������HTML�ƥ�ץ졼��
	break;

default: // ���ϥե�����ɽ��
	session_cache_limiter('private_no_expire');
	session_start();
	unset($_SESSION['SFM']);
	$sfm_script = $script_name;
	include_once('sfm_form.html');	// ���ϥե�������HTML�ƥ�ץ졼��
}
exit;


// *** HTML�ǡ�������
function FORM_DATA_H($name) {
	global $baseEnc, $maxText;
	$errArray = array(
		'::INPUT ERROR::'=>'ɬ�ܹ��ܤǤ�',
		'::EMAIL ERROR::'=>'�᡼�륢�ɥ쥹������������ޤ���',
		'::EMAIL CHECK ERROR::'=>'�᡼�륢�ɥ쥹�����פ��ޤ���',
		'::MAXTEXT ERROR::'=>'ʸ������¿�����ޤ���' . number_format($maxText) . '���ޤǡ�'
	);
	$value = $_SESSION['SFM'][$name];
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	$value = str_replace("\t", "\n", $value);	// ɽ���Ѥ�ʣ�����ܤ����
	$value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'EUC-JP'));
	$value = (preg_match('/::.+::/', $value)) ? '<span class="ERR">' . $errArray[$value] . '</span>' : $value;
	$value = ($value != '') ? $value : '&nbsp;';
	return EnCode($value, $baseEnc, 'EUC-JP');
}


// *** MAIL�ǡ�������
function FORM_DATA_M($name) {
	$value = $_SESSION['SFM'][$name];
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	$value = str_replace("\t", ',', $value);	// �᡼���Ѥ�ʣ�����ܤ򥫥�޶��ڤ�
	return $value;
}


// *** �᡼���������󥳡���
function SendMail($to, $sub, $msg, $from, $bcc) {
	global $scriptVersion;
	global $returnPath;
	global $ill_header;
	$sub = EnCode($sub, 'JIS', 'EUC-JP');
	$msg = EnCode($msg, 'JIS', 'EUC-JP');
	$rn = array("\r\n", "\n", "\r");
	$msg = str_replace($rn, "\n", $msg);

	// �إå�����
	$rtf = ($ill_header) ? "\n" : "\r\n";
	$header = 'From: ' . $from . $rtf;
	$header .= 'Bcc: ' . $bcc . $rtf;
	$header .= 'X-Mailer: Sapphirus.Biz Formmail (Ver. ' . $scriptVersion . '/PHP)' . $rtf;
	$header .= 'Mime-Version: 1.0' . $rtf;
	$header .= 'Content-Type: text/plain; charset=ISO-2022-JP' . $rtf;
	$header .= 'Content-Transfer-Encoding: 7bit';

	$sub = '=?iso-2022-jp?B?' . base64_encode($sub) . '?=';	// Base64���󥳡���
	if ($returnPath) {
		mail($to, $sub, $msg, $header, '-f' . $returnPath);
	} else {
		mail($to, $sub, $msg, $header);
	}
	return;
}


/* ::�桼����������� */
function USERINFO() {
	return @gethostbyaddr($_SERVER['']) . ""
	. $_SERVER[''] . ""
	. date("Y/m/d - H:i:s");
}



/* ::ʸ�����󥳡����Ѵ� */
function EnCode($value, $chg, $org) {
	if (!extension_loaded('mbstring')) {
		Err('mbstring�ؿ������ѤǤ��ޤ���');
	}
	return @mb_convert_encoding($value, $chg, $org);
}


/* ::���顼ɽ��HTML */
function Err($err) {
echo <<<EOM
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" />
<title>���顼��$err</title>
<script src="/js/global.js" language="javascript"></script>
</head>
<body style="font-size: 12px; line-height: 1.8em;">
<strong>���顼 : </strong>$err<br>
<a href="javascript:history.back()" style="display:inline-block; vertical-align:middle; margin-left:5px;"><img src="../images/second/btnBack.png" width="150" height="60" alt="���β��̤����" /></a>
</body></html>
EOM;
exit;
}

?>
