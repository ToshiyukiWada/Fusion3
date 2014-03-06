//******************************************************************************
// FUSIONValidate
//******************************************************************************
// 入力値チェック制御
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
//******************************************************************************
//------------------------------------------------------------------------------
// コンストラクタ
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var FUSIONValidate = function()
{
	
}
FUSIONValidate.prototype = new FUSIONAjaxBase();

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONValidate.prototype.validate = function(processName, params, callbackFunction, errorOutputFunction)
{
	// Ajaxリクエスト送信
	this.sendServer(this
	,"./validate/" + processName
	,false
	,params
	,function(variables, contents, result)
	{
		// 入力値チェックで使用する変数
		var isError			= false;		// この中にエラーがあったか否か
		var isWarning		= false;		// この中に警告があったか否か
		var errorMessage	= "";			// エラーメッセージ
		var warningMessage	= "";			// 警告メッセージ

		// 入力値チェック解析処理
		var action					= contents["_action"];						// エラーチェックを実施したAction
		var process					= contents["_process"];						// エラーチェックプロセス

		for (var countVariable = 0 ; countVariable < contents["_variables"].length ; countVariable++)
		{
			var variable			= contents["_variables"][countVariable];	// 変数情報の取得
			var name				= variable["_name"];						// 変数名
			var displayname			= variable["_displayname"];					// 変数表示名
			var type				= variable["_type"];						// 変数の型
			var value				= variable["_value"];						// 変数の値
			var isMultiMessage		= variable["_isMultiMessage"];				// エラー・警告を複数表示するか否か

			var isRiseError			= false;			// この変数で一度でもエラーが発生したか否か
			var isRiseWarning		= false;			// この変数で一度でも警告が発生したか否か

			for (var countValidate = 0 ; countValidate < variable["_validate"].length ; countValidate++)
			{
				var validate		= variable["_validate"][countValidate];		// 入力値チェックの取得
				var validateClass	= validate["_validateClass"];				// 入力値チェッククラス
				var validateType	= validate["_validateType"];				// エラーか警告か
				var result			= validate["_result"];						// チェック結果
				var message			= validate["_message"];						// チェック結果メッセージ

				if (validateType == "ERROR" && result == false)
				{
					// エラーが発生
					if (isMultiMessage == false && isRiseError == true){ continue; }		// 同じ変数内で同じエラー種別のものを複数出力するか否か判定し、複数出力しない場合は次へ
					isRiseError		= true;												// 一度でもエラーが発生
					isError			= true;												// エラーが発生していることを知らせる

					errorMessage	+= message + "\n";
				}
				if (validateType == "WARNING" && result == false)
				{
					// 警告が発生
					if (isMultiMessage == false && isRiseWarning == true){ continue; }	// 同じ変数内で同じエラー種別のものを複数出力するか否か判定し、複数出力しない場合は次へ
					isRiseWarning	= true;												// 一度でも警告が発生
					isWarning		= true;												// 警告が発生していることを知らせる

					warningMessage	+= message + "\n";
				}
			}
		}

		// エラーの出力
		var result = true;			// 結果格納
		if (errorOutputFunction == undefined)
		{
			// エラー関数が定義されていない場合は通常のAlert/Confirmを使って実装する
			if (isError == true)
			{
				// エラー発生
				alert(errorMessage);
				result = false;
			}
			else if (isWarning == true)
			{
				// 警告発生
				result = confirm(warningMessage);
			}
		}
		else
		{
			if (isError == true || isWarning == true)
			{
				// エラー関数が定義されている場合は全ての処理をその関数に譲渡する
				// 後続処理についても、そちらの関数に処理を委譲する為、ここでの処理はここで終了
				errorOutputFunction(				// エラー出力関数の引数は以下の通り
					 isError						// エラーが発生したか否か
					,isWarning						// 警告が発生したか否か
					,errorMessage					// エラーメッセージ
					,warningMessage					// 警告メッセージ
					,callbackFunction				// 正常終了時に呼び出して欲しい関数
					,variables						// (必須ではない)
					,contents						// (必須ではない)
					,result							// (必須ではない)
				);
				result		= false;				// ここで処理終了(後続処理の呼び出しもエラー関数に任せる(その為にコールバック関数を渡している))
			}
		}

		// 最終的な判断
		return result;
	}
	,function(variables, contents, result)			// variables:サーバーから返却された変数 contents:Ajax処理それそれの返却値 result:サーバーから返却された全ての情報
	{
		if (callbackFunction != undefined)
		{
			callbackFunction();
		}
	});
}

// フレームワーク中で利用する為に予約語"validate"を定義しておく
var validate = null;
appendEventListener(window, "load", function(){ validate = new FUSIONValidate(); }, false);
