<?php
//------------------------------------------------------------------------------
// template
//------------------------------------------------------------------------------
// htmlをテンプレートとして利用する
// 
// 
// 
// 
//------------------------------------------------------------------------------
function template($parent, $fileFullpath)
{
	// テンプレートでも後続処理用に用意された変数を利用できるようにする
	extract($parent->getVariables());
	extract(array('parentAction' => $parent->getParentAction(), 'nowAction' => $parent->getNowAction(), 'realAction' => $parent->getRealAction()));

	// テンプレートファイル読込
	$html		= file_get_contents($fileFullpath);																									// 該当ファイルの読込
	$html		= "?>".$html;																														// evalエスケープ

	// テンプレート置換
	$html = preg_replace('/%\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars((isset($1)?$1:""))); ?>', $html);					// 定義されている変数をHTMLエスケープ後にHTML内に通常出力する
	$html = preg_replace('/#\{(.*?)\}/', '<?php echo isset($1)?$1:""; ?>', $html);																	// 定義されている変数をHTML内に通常出力する
	$html = preg_replace('/@\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars($parent->getMessage("$1"))); ?>', $html);			// 定義されているメッセージをHTMLエスケープ後にHTML内に通常出力する
	$html = preg_replace('/!\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars_decode($parent->getMessage("$1"))); ?>', $html);	// 定義されているメッセージをHTMLを通常出力する
	$html = preg_replace('/&\{(.*?)\}/', '<?php echo $1 ?>', $html);																				// 固定文字をHTML内に通常出力する
	$html = preg_replace('/~\{(.*?)\}/', '<?php echo str_replace("\\\\\","", (isset($1)?$1:"")); ?>', $html);										// 固定文字をHTML内に通常出力する

	// リソーステキスト対応
	$html = preg_replace('/X\{(.*?)\}/', '<?php echo $parent->getResouceText($1); ?>', $html);																// リソースをHTML内に通常出力する

	// テンプレート描画処理
	return eval($html);
}

//------------------------------------------------------------------------------
// chechClient
//------------------------------------------------------------------------------
// ブラウザのUserAgentからクライアントを判別する
// ここから返却されるクライアント種別は以下の通り(重複する内容については、上位が
// 優先して返却される)
// ・iPhone
// ・iPad
// ・AndroidMobile
// ・AndroidTablet
// ・MSIE
// ・Chrome
// ・Firefox
// ・Safari
//------------------------------------------------------------------------------
function getClient()
{
	$ua				= $_SERVER['HTTP_USER_AGENT'];

	// iPhone判定
	if(strpos($ua,'iPhone')			!==false	|| strpos($ua,'iPod')		!==false)	{ return "iPhone"; }

	// iPad判定
	if(strpos($ua,'iPad')			!==false)											{ return "iPad"; }

	// Android携帯判定
	if(strpos($ua,'Android')		!==false	&& strpos($ua,'Mobile')	!==false)		{ return "AndroidMobile"; }

	// Android携帯判定
	if(strpos($ua,'Android')		!==false)											{ return "AndroidTablet"; }

	// IE判定
	if(strpos($ua,'MSIE')			!==false)											{ return "MSIE"; }

	// Firefox判定
	if(strpos($ua,'Firefox')		!==false)											{ return "Firefox"; }

	// Safari判定
	if(strpos($ua,'Safari')			!==false)											{ return "Safari"; }

	// Chrome判定
	if(strpos($ua,'Chrome')			!==false)											{ return "Chrome"; }

	// それ以外
	return "";
}

//------------------------------------------------------------------------------
// isPC
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
// 
// 
//------------------------------------------------------------------------------
function isPC()
{
	$client			= getClient();

	if ($client == "MSIE")				{ return true; }
	if ($client == "Firefox")			{ return true; }
	if ($client == "Safari")			{ return true; }
	if ($client == "Chrome")			{ return true; }

	return false;
}

//------------------------------------------------------------------------------
// isSmartphone
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
// 
// 
//------------------------------------------------------------------------------
function isSmartphone()
{
	$client			= getClient();

	if ($client == "iPhone")			{ return true; }
	if ($client == "AndroidMobile")		{ return true; }

	return false;
}

//------------------------------------------------------------------------------
// isTablet
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
// 
// 
//------------------------------------------------------------------------------
function isTablet()
{
	$client			= getClient();

	if ($client == "iPad")				{ return true; }
	if ($client == "AndroidTablet")		{ return true; }

	return false;
}

//------------------------------------------------------------------------------
// makeClickable
//------------------------------------------------------------------------------
// 文字列中に存在するURL・メールアドレスを自動的にリンク文字に変更する
// 
// 
// 
// 
//------------------------------------------------------------------------------
function makeClickable($text)
{
	$ret = ' ' . $text;
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a href=\"\\2\" >\\2</a>'", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://\\2\" >\\2</a>'", $ret);
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
	$ret = substr($ret, 1);

	return($ret);
}

//------------------------------------------------------------------------------
// getFilename
//------------------------------------------------------------------------------
// ファイルパスからファイル名を取得する
// 
// 
// 
// 
//------------------------------------------------------------------------------
function getFilename($fileFullpath)
{
	$result				= "";

	// パス区切りの最終を判断する
	if (strrpos($fileFullpath, "/") !== FALSE)
	{
		// UNIX/Linux系のパス分割
		$result			= substr($fileFullpath, strrpos($fileFullpath, "/") + strlen("/"));
	}
	else if (strrpos($fileFullpath, "\\") !== FALSE)
	{
		// Windows系のパス分割
		$result			= substr($fileFullpath, strrpos($fileFullpath, "\\") + strlen("\\"));
	}
	else
	{
		// 見つからない場合はそのままの値を返却
		$result			= $fileFullpath;
	}

	return $result;
}

//------------------------------------------------------------------------------
// checkExtension
//------------------------------------------------------------------------------
// 拡張子を取得する
// 
// 
// 
// 
//------------------------------------------------------------------------------
function checkExtension($filename, $extension)
{
	// 拡張子チェック
	if (strpos(strtolower($filename), ".".strtolower($extension)) == strlen($filename) - strlen(".".strtolower($extension)))
	{
		// 対象の拡張子だった場合はtrueを返却
		return true;
	}
	else
	{
		// 対象の拡張子以外の場合はfalseを返却
		return false;
	}
}

//------------------------------------------------------------------------------
// isHash
//------------------------------------------------------------------------------
// 引数で指定した配列が普通の配列か、連想配列かをチェックする
// 
// 
// 
// 
//------------------------------------------------------------------------------
function isHash(&$array)
{
    return array_keys($array) !== range(0, count($array) - 1);
}

//------------------------------------------------------------------------------
// replaceConfig
//------------------------------------------------------------------------------
// 引数で指定された接頭語・接尾語に囲まれたプロパティ文字を指定した文字に置換す
// る
// 
// 
// 
//------------------------------------------------------------------------------
function replaceConfig($target, $prefix, $suffix, $values)
{
	// 置換処理
	while(($replaceFromIndex = strpos($target, $prefix)) !== FALSE)
	{
		$replaceToIndex		= strpos($target, $suffix, $replaceFromIndex);
		if ($replaceToIndex !== FALSE)
		{
			$replaceKey	= substr($target, $replaceFromIndex + strlen($prefix) ,$replaceToIndex - $replaceFromIndex - strlen($suffix));
			$replaceString	= "";
			if (array_key_exists($replaceKey, $values)){ $replaceString = $values[$replaceKey]; }
			$target	= str_replace($prefix.$replaceKey.$suffix, $replaceString, $target);
		}
	}

	// 結果返却
	return $target;
}

//------------------------------------------------------------------------------
// getLanguage
//------------------------------------------------------------------------------
// ブラウザで設定されている一番優先順位の高い言語を取得する
// 
// 
// 
// 
//------------------------------------------------------------------------------
function getLanguage()
{
	$locale					= $_SERVER['HTTP_ACCEPT_LANGUAGE'];						// ブラウザに設定されている言語設定一覧の取得
	$arrayLocale			= explode(",", $locale);								// 言語設定は優先順位順にカンマ区切りで格納されているので、カンマで文字列を分解して解析する
	$myLocale				= $arrayLocale[0];										// 一番最初の取得できた言語設定が、優先順位の一番高い言語設定となる
	$keyParams				= array();												// キー内に置換文字がある場合に適用するパラメータ

	// 言語設定の中からの言語の取得
	if (strlen($myLocale) > 2)
	{
		$myLocale			= substr($myLocale, 0, 2);								// 言語設定は頭2文字-後2文字で構成され、先頭は国を、後端は言語を表している。(日本では無いが、国によっては国内で利用する言語が異なる為)
	}

	return $myLocale;
}

?>
