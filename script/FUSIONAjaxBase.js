//******************************************************************************
// FUSIONProcess
//******************************************************************************
// プロセス制御
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
var FUSIONAjaxBase = function()
{
	this._results				= new Object();					// 各Process処理の結果が格納される(キーはID)
}

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// FUSIONAjax系の処理の戻り値を解析し、インスタンス内に格納する
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.sendServer = function(myObj, url, isAsync, params, processFunction, callbackFunction)
{
	// IDの生成
	id			= getUUID();

	// 入力フォームの値を集約
	var param	= $("#inputform").serializeArray();

	// 型情報を自動付与
	var formObjects		= $("#inputform, #inputform, *").children();
	for(var count = 0; count < formObjects.length; count++)
	{
		var childObject	= formObjects.eq(count);
		if (childObject[0].tagName == "INPUT" || childObject[0].tagName == "TEXTAREA")
		{
			var name		= childObject[0].name;
			var type		= childObject[0].type;
			var isReadOnly	= childObject[0].readOnly;

			if (type == "text" || type == "password" || type == "checkbox" || type == "hidden"|| type == "radio" || type == "select-one" || type == "select-multiple" || type == "textarea")
			{
				name = name.replace("[]","");
				param.push({name: name + "__type", value: type + "::" + (isReadOnly?"t":"f")});
			}
		}
	}

	// 引数で与えられたパラメータを追加する(重複している場合は引数パラメータを優先する)
	if (params != undefined)
	{
		param		= addHash(param, params);
	}

	// IDの付与
	param.push({name: "__FUSION_AJAXID", value: id});

	// 呼び出し元オブジェクトの保持
	var invoker		= this;

	$.ajax({
		 url		: url									// AjaxのリクエストURL
		,async		: isAsync								// 同期
		,cache		: false									// キャッシュはしない
		,type		: "post"								// POST送信
		,dataType	: "xml"									// XMLで結果を受け取る(フレームワークの仕様)
		,data		: (param)								// パラメータ
		,success	: function(request)						// 正常終了時の処理
		 {
			// 戻り値解析
			invoker.parse(request);

			// 致命的なエラーが発生したか否か
			invoker.checkError(id);

			// 解析処理
			var processResult		= true;
			if (processFunction != undefined)
			{
				var result			= invoker.getResult(id);
				var variables		= result["variables"];
				var contents		= result["contents"];
				processResult		= processFunction(variables, contents, result);
			}

			// コールバック関数の実行
		 	if (callbackFunction != undefined && processResult == true)
			{
				callbackFunction(variables, contents, result);
			}
		 }
		,error		: function(request)						// 異常終了時の処理
		 {
			invoker.sendError("jQuery Ajax Error","Ajax Error","HTTP ServerError","unknown");
		 }
	});
}

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// FUSIONAjax系の処理の戻り値を解析し、インスタンス内に格納する
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.parse = function(xmlObj, argId)
{
	// 受け取り変数定義
	var id					= "";			// 
	var mode				= "";			// 
	var processingMode		= "";			// 
	var requestUrl			= "";			// 
	var nowAction			= "";			// 
	var parentAction		= "";			// 
	var realAction			= "";			// 
	var className			= "";			// 
	var startTime			= "";			// 
	var endTime				= "";			// 
	var errors				= null;			// 
	var variables			= null;			// 
	var contents			= null;			// 

	// 結果XMLから結果を受け取る
	if (xmlObj.getElementsByTagName("id").length == 1)				{ id				= getTextContent(xmlObj.getElementsByTagName("id")); }				// 
	if (xmlObj.getElementsByTagName("mode").length == 1)			{ mode				= getTextContent(xmlObj.getElementsByTagName("mode")); }			// 
	if (xmlObj.getElementsByTagName("processingMode").length == 1)	{ processingMode	= getTextContent(xmlObj.getElementsByTagName("processingMode")); }	// 
	if (xmlObj.getElementsByTagName("requestUrl").length == 1)		{ requestUrl		= getTextContent(xmlObj.getElementsByTagName("requestUrl")); }		// 
	if (xmlObj.getElementsByTagName("nowAction").length == 1)		{ nowAction			= getTextContent(xmlObj.getElementsByTagName("nowAction")); }		// 
	if (xmlObj.getElementsByTagName("parentAction").length == 1)	{ parentAction		= getTextContent(xmlObj.getElementsByTagName("parentAction")); }	// 
	if (xmlObj.getElementsByTagName("realAction").length == 1)		{ realAction		= getTextContent(xmlObj.getElementsByTagName("realAction")); }		// 
	if (xmlObj.getElementsByTagName("className").length == 1)		{ className			= getTextContent(xmlObj.getElementsByTagName("className")); }		// 
	if (xmlObj.getElementsByTagName("startTime").length == 1)		{ startTime			= getTextContent(xmlObj.getElementsByTagName("startTime")); }		// 
	if (xmlObj.getElementsByTagName("endTime").length == 1)			{ endTime			= getTextContent(xmlObj.getElementsByTagName("endTime")); }			// 
	errors		= JSON.parse(getTextContent(xmlObj.getElementsByTagName("errors")));																		// 
	variables	= JSON.parse(getTextContent(xmlObj.getElementsByTagName("variables")));																		// 
	contents	= JSON.parse(getTextContent(xmlObj.getElementsByTagName("contents")));																		// 

	if (argId != undefined){ id = argId; }

	// 結果メンバ変数に格納する
	this._results[id]	= {  id				: id								// 
							,mode			: mode								// 
							,processingMode	: processingMode					// 
							,requestUrl		: requestUrl						// 
							,nowAction		: nowAction							// 
							,parentAction	: parentAction						// 
							,realAction		: realAction						// 
							,className		: className							// 
							,startTime		: startTime							// 
							,endTime		: endTime							// 
							,errors			: errors							// 
							,variables		: variables							// 
							,contents		: contents };						// 
}

//------------------------------------------------------------------------------
// checkError
//------------------------------------------------------------------------------
// Ajax系処理でエラーが発生したかどうかをチェックする
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.checkError = function(id)
{
	// 致命的なエラーが発生したか否か
	if (this._results[id]["errors"].length > 0)
	{
		this.sendError(this._results[id]["errors"][0]["message"], this._results[id]["errors"][0]["errstr"], this._results[id]["errors"][0]["errfile"], this._results[id]["errors"][0]["errline"]);
	}
}

//------------------------------------------------------------------------------
// sendError
//------------------------------------------------------------------------------
// エラー発生時にエラーを送信する
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.sendError = function(message, errstr, errfile, errline)
{
		var exceptionFormObj			= document.createElement("form");					// エラー情報を送信する為のFORMタグを作成する
		exceptionFormObj.action			= "./exception/exception";							// FORMタグの情報送信先はフレームワークのエラー表示用画面とする
		exceptionFormObj.method			= "post";											// フレームワークにエラー情報を渡す方法はPOSTメソッドで渡す

		var exceptionMessageObj			= document.createElement("input");					// エラーメッセージを送信する為の隠し項目タグを作成する
		exceptionMessageObj.name		= "exceptionMessage";								// エラーメッセージタグの名前を決める
		exceptionMessageObj.id			= "exceptionMessage";								// エラーメッセージタグのIDを決める
		exceptionMessageObj.type		= "hidden";											// エラーメッセージタグを隠しタグにする
		exceptionMessageObj.value		= message;											// エラーメッセージタグにエラー内容を格納する

		var exceptionErrStrObj			= document.createElement("input");					// エラー詳細を送信する為の隠し項目タグを作成する
		exceptionErrStrObj.name			= "exceptionErrStr";								// エラー詳細タグの名前を決める
		exceptionErrStrObj.id			= "exceptionErrStr";								// エラー詳細タグのIDを決める
		exceptionErrStrObj.type			= "hidden";											// エラー詳細タグを隠しタグにする
		exceptionErrStrObj.value		= errstr;											// エラー詳細タグにエラー内容を格納する

		var exceptionErrFileObj			= document.createElement("input");					// エラーファイル名を送信する為の隠し項目タグを作成する
		exceptionErrFileObj.name		= "exceptionErrFile";								// エラーファイル名タグの名前を決める
		exceptionErrFileObj.id			= "exceptionErrFile";								// エラーファイル名タグのIDを決める
		exceptionErrFileObj.type		= "hidden";											// エラーファイル名タグを隠しタグにする
		exceptionErrFileObj.value		= errfile;											// エラーファイル名タグにエラー内容を格納する

		var exceptionErrLineObj			= document.createElement("input");					// エラー行数を送信する為の隠し項目タグを作成する
		exceptionErrLineObj.name		= "exceptionErrLine";								// エラー行数タグの名前を決める
		exceptionErrLineObj.id			= "exceptionErrLine";								// エラー行数タグのIDを決める
		exceptionErrLineObj.type		= "hidden";											// エラー行数タグを隠しタグにする
		exceptionErrLineObj.value		= errline;											// エラー行数タグにエラー内容を格納する

		exceptionFormObj.appendChild(exceptionMessageObj);									// エラーメッセージをフォームに追加する
		exceptionFormObj.appendChild(exceptionErrStrObj);									// をフォームに追加する
		exceptionFormObj.appendChild(exceptionErrFileObj);									// をフォームに追加する
		exceptionFormObj.appendChild(exceptionErrLineObj);									// をフォームに追加する
		exceptionFormObj.submit();															// エラー画面に遷移するエラーメッセージをフォームに追加する
}

//------------------------------------------------------------------------------
// getResult
//------------------------------------------------------------------------------
// 同一画面上でのAjaxの結果はIDをキーに保持されていくので、いつでも結果を再取得
// できる
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getResult = function(id)
{
	return this._results[id];
}

//------------------------------------------------------------------------------
// getMode
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getMode = function(id)
{
	return this.getResult(id)["mode"];
}

//------------------------------------------------------------------------------
// getProcessingMode
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getProcessingMode = function(id)
{
	return this.getResult(id)["processingMode"];
}

//------------------------------------------------------------------------------
// getRequestUrl
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getRequestUrl = function(id)
{
	return this.getResult(id)["requestUrl"];
}

//------------------------------------------------------------------------------
// getNowAction
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getNowAction = function(id)
{
	return this.getResult(id)["nowAction"];
}

//------------------------------------------------------------------------------
// getParentAction
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getParentAction = function(id)
{
	return this.getResult(id)["parentAction"];
}

//------------------------------------------------------------------------------
// getRealAction
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getRealAction = function(id)
{
	return this.getResult(id)["realAction"];
}

//------------------------------------------------------------------------------
// getClassName
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getClassName = function(id)
{
	return this.getResult(id)["className"];
}

//------------------------------------------------------------------------------
// getStartTime
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getStartTime = function(id)
{
	return this.getResult(id)["startTime"];
}

//------------------------------------------------------------------------------
// getEndTime
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getEndTime = function(id)
{
	return this.getResult(id)["endTime"];
}

//------------------------------------------------------------------------------
// getErrors
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getErrors = function(id)
{
	return this.getResult(id)["errors"];
}

//------------------------------------------------------------------------------
// getVariables
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONAjaxBase.prototype.getVariables = function(id)
{
	return this.getResult(id)["variables"];
}
