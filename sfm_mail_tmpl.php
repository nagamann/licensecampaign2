<?php
//送信メッセージ
$mailMessage=
'「原付免許取得支援キャンペーン」にエントリーがありました。
送信された内容は以下の通りです。

---------------------------------------------------------------------
エントリー内容
---------------------------------------------------------------------

エントリー番号
  AJ'.number().'

氏名
　'.FORM_DATA_M('name').'
   '.FORM_DATA_M('kana').'

メールアドレス
　'.FORM_DATA_M('email').'

生年月日
　'.FORM_DATA_M('year').'年'.FORM_DATA_M('month').'月'.FORM_DATA_M('day').'日

お住まい
　'.FORM_DATA_M('zip').'
　'.FORM_DATA_M('address').'

連絡先TEL 
　'.FORM_DATA_M('tel').'

未成年者の親の同意について
　'.FORM_DATA_M('trigger').'

確認事項
　'.FORM_DATA_M('agree').'

エントリー日時
'
.USERINFO();
?>