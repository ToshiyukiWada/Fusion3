<?php
//------------------------------------------------------------------------------
// template
//------------------------------------------------------------------------------
// html���e���v���[�g�Ƃ��ė��p����
// 
// 
// 
// 
//------------------------------------------------------------------------------
function template($parent, $fileFullpath)
{
	// �e���v���[�g�ł��㑱�����p�ɗp�ӂ��ꂽ�ϐ��𗘗p�ł���悤�ɂ���
	extract($parent->getVariables());
	extract(array('parentAction' => $parent->getParentAction(), 'nowAction' => $parent->getNowAction(), 'realAction' => $parent->getRealAction()));

	// �e���v���[�g�t�@�C���Ǎ�
	$html		= file_get_contents($fileFullpath);																									// �Y���t�@�C���̓Ǎ�
	$html		= "?>".$html;																														// eval�G�X�P�[�v

	// �e���v���[�g�u��
	$html = preg_replace('/%\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars((isset($1)?$1:""))); ?>', $html);					// ��`����Ă���ϐ���HTML�G�X�P�[�v���HTML���ɒʏ�o�͂���
	$html = preg_replace('/#\{(.*?)\}/', '<?php echo isset($1)?$1:""; ?>', $html);																	// ��`����Ă���ϐ���HTML���ɒʏ�o�͂���
	$html = preg_replace('/@\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars($parent->getMessage("$1"))); ?>', $html);			// ��`����Ă��郁�b�Z�[�W��HTML�G�X�P�[�v���HTML���ɒʏ�o�͂���
	$html = preg_replace('/!\{(.*?)\}/', '<?php echo str_replace("\n","<br />", htmlspecialchars_decode($parent->getMessage("$1"))); ?>', $html);	// ��`����Ă��郁�b�Z�[�W��HTML��ʏ�o�͂���
	$html = preg_replace('/&\{(.*?)\}/', '<?php echo $1 ?>', $html);																				// �Œ蕶����HTML���ɒʏ�o�͂���
	$html = preg_replace('/~\{(.*?)\}/', '<?php echo str_replace("\\\\\","", (isset($1)?$1:"")); ?>', $html);										// �Œ蕶����HTML���ɒʏ�o�͂���

	// ���\�[�X�e�L�X�g�Ή�
	$html = preg_replace('/X\{(.*?)\}/', '<?php echo $parent->getResouceText($1); ?>', $html);																// ���\�[�X��HTML���ɒʏ�o�͂���

	// �e���v���[�g�`�揈��
	return eval($html);
}

//------------------------------------------------------------------------------
// chechClient
//------------------------------------------------------------------------------
// �u���E�U��UserAgent����N���C�A���g�𔻕ʂ���
// ��������ԋp�����N���C�A���g��ʂ͈ȉ��̒ʂ�(�d��������e�ɂ��ẮA��ʂ�
// �D�悵�ĕԋp�����)
// �EiPhone
// �EiPad
// �EAndroidMobile
// �EAndroidTablet
// �EMSIE
// �EChrome
// �EFirefox
// �ESafari
//------------------------------------------------------------------------------
function getClient()
{
	$ua				= $_SERVER['HTTP_USER_AGENT'];

	// iPhone����
	if(strpos($ua,'iPhone')			!==false	|| strpos($ua,'iPod')		!==false)	{ return "iPhone"; }

	// iPad����
	if(strpos($ua,'iPad')			!==false)											{ return "iPad"; }

	// Android�g�є���
	if(strpos($ua,'Android')		!==false	&& strpos($ua,'Mobile')	!==false)		{ return "AndroidMobile"; }

	// Android�g�є���
	if(strpos($ua,'Android')		!==false)											{ return "AndroidTablet"; }

	// IE����
	if(strpos($ua,'MSIE')			!==false)											{ return "MSIE"; }

	// Firefox����
	if(strpos($ua,'Firefox')		!==false)											{ return "Firefox"; }

	// Safari����
	if(strpos($ua,'Safari')			!==false)											{ return "Safari"; }

	// Chrome����
	if(strpos($ua,'Chrome')			!==false)											{ return "Chrome"; }

	// ����ȊO
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
// �����񒆂ɑ��݂���URL�E���[���A�h���X�������I�Ƀ����N�����ɕύX����
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
// �t�@�C���p�X����t�@�C�������擾����
// 
// 
// 
// 
//------------------------------------------------------------------------------
function getFilename($fileFullpath)
{
	$result				= "";

	// �p�X��؂�̍ŏI�𔻒f����
	if (strrpos($fileFullpath, "/") !== FALSE)
	{
		// UNIX/Linux�n�̃p�X����
		$result			= substr($fileFullpath, strrpos($fileFullpath, "/") + strlen("/"));
	}
	else if (strrpos($fileFullpath, "\\") !== FALSE)
	{
		// Windows�n�̃p�X����
		$result			= substr($fileFullpath, strrpos($fileFullpath, "\\") + strlen("\\"));
	}
	else
	{
		// ������Ȃ��ꍇ�͂��̂܂܂̒l��ԋp
		$result			= $fileFullpath;
	}

	return $result;
}

//------------------------------------------------------------------------------
// checkExtension
//------------------------------------------------------------------------------
// �g���q���擾����
// 
// 
// 
// 
//------------------------------------------------------------------------------
function checkExtension($filename, $extension)
{
	// �g���q�`�F�b�N
	if (strpos(strtolower($filename), ".".strtolower($extension)) == strlen($filename) - strlen(".".strtolower($extension)))
	{
		// �Ώۂ̊g���q�������ꍇ��true��ԋp
		return true;
	}
	else
	{
		// �Ώۂ̊g���q�ȊO�̏ꍇ��false��ԋp
		return false;
	}
}

//------------------------------------------------------------------------------
// isHash
//------------------------------------------------------------------------------
// �����Ŏw�肵���z�񂪕��ʂ̔z�񂩁A�A�z�z�񂩂��`�F�b�N����
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
// �����Ŏw�肳�ꂽ�ړ���E�ڔ���Ɉ͂܂ꂽ�v���p�e�B�������w�肵�������ɒu����
// ��
// 
// 
// 
//------------------------------------------------------------------------------
function replaceConfig($target, $prefix, $suffix, $values)
{
	// �u������
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

	// ���ʕԋp
	return $target;
}

//------------------------------------------------------------------------------
// getLanguage
//------------------------------------------------------------------------------
// �u���E�U�Őݒ肳��Ă����ԗD�揇�ʂ̍���������擾����
// 
// 
// 
// 
//------------------------------------------------------------------------------
function getLanguage()
{
	$locale					= $_SERVER['HTTP_ACCEPT_LANGUAGE'];						// �u���E�U�ɐݒ肳��Ă��錾��ݒ�ꗗ�̎擾
	$arrayLocale			= explode(",", $locale);								// ����ݒ�͗D�揇�ʏ��ɃJ���}��؂�Ŋi�[����Ă���̂ŁA�J���}�ŕ�����𕪉����ĉ�͂���
	$myLocale				= $arrayLocale[0];										// ��ԍŏ��̎擾�ł�������ݒ肪�A�D�揇�ʂ̈�ԍ�������ݒ�ƂȂ�
	$keyParams				= array();												// �L�[���ɒu������������ꍇ�ɓK�p����p�����[�^

	// ����ݒ�̒�����̌���̎擾
	if (strlen($myLocale) > 2)
	{
		$myLocale			= substr($myLocale, 0, 2);								// ����ݒ�͓�2����-��2�����ō\������A�擪�͍����A��[�͌����\���Ă���B(���{�ł͖������A���ɂ���Ă͍����ŗ��p���錾�ꂪ�قȂ��)
	}

	return $myLocale;
}

?>
