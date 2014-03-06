//******************************************************************************
// FUSIONDialog
//******************************************************************************
// ダイアログ制御
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
var FUSIONDialog = function(parentObj)
{
	this._parentObj			= parentObj;		// 親となるボディオブジェクト
	this._eventIdResize		= null;				// このクラス内でのウインドウリサイズ時のイベントID
	this._eventIdScroll		= null;				// このクラス内でのウインドウスクロール時のイベントID
	this._dialogFreeObjs	= new Object();		// 自由移動のダイアログ一覧
	this._dialogCenterObjs	= new Object();		// 中央表示のダイアログ一覧

	this._maxZIndex			= 100;				// 初期Zインデックス
	
	// イベントに対応する処理の追加
	this._eventIdResize		= appendEventListener(window, "resize", this.onResize, false);
	this._eventIdScroll		= appendEventListener(window, "scroll", this.onScroll, false);

	// イベントの削除の方法は以下の通り(例)
	// deleteEventListener(window, "resize", this._eventIdResize, false);
	// deleteEventListener(window, "scroll", this._eventIdScroll, false);
}

//------------------------------------------------------------------------------
// イベント対象処理(ウインドウリサイズ)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.onResize = function()
{
	// オブジェクトの再配置を実施する
	dialog.reallocation();
}

//------------------------------------------------------------------------------
// イベント対象処理(スクロール)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.onScroll = function()
{
	// オブジェクトの再配置を実施する
	dialog.reallocation();
}

//------------------------------------------------------------------------------
// オブジェクトの位置を再配置
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.reallocation = function()
{
	for (var dialogObj in this._dialogCenterObjs)
	{
		dialogDivObj			= this._dialogCenterObjs[dialogObj]["obj"];

		var dialogWidth			= $(dialogDivObj).width();
		var dialogHeight		= $(dialogDivObj).height();

		// センターに表示する
		var clientWidth			= $('html')[0].clientWidth;
		var clientHeight		= $('html')[0].clientHeight;

		var positionLeft		= Math.floor((clientWidth / 2) - (dialogWidth / 2));
		var positionTop			= Math.floor((clientHeight / 2) - (dialogHeight / 2));

		dialogDivObj.style.top	= positionTop + "px";
		dialogDivObj.style.left	= positionLeft + "px";
	}
}

//------------------------------------------------------------------------------
// 最大Z-Indexの取得
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.getZIndex = function()
{
	this._maxZIndex++;
	return this._maxZIndex;
}

//------------------------------------------------------------------------------
// 背景作成
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.openBackground = function(colorCode, opacity)
{
	// IDの作成
	var objId		= getUUID();

	// 背景のDIVタグを動的に作成
	var backgroundDivObj						= document.createElement("div");
	backgroundDivObj.id							= objId;
	backgroundDivObj.style.position				= "absolute";
	backgroundDivObj.style.top					= 0;
	backgroundDivObj.style.left					= 0;
	backgroundDivObj.style.width				= "100%";
	backgroundDivObj.style.height				= "100%";
	backgroundDivObj.style.zIndex				= this.getZIndex();
	backgroundDivObj.style.backgroundColor		= colorCode;
	backgroundDivObj.style.MozOpacity 			= opacity / 100;
	backgroundDivObj.style.opacity	 			= opacity / 100;
	backgroundDivObj.style.filter				= "alpha(opacity = " + opacity + ")";
	this._parentObj.appendChild(backgroundDivObj);

	// ここで作成したオブジェクトのIDを返却する
	return objId;
}

//------------------------------------------------------------------------------
// ダイアログ作成(自由移動)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.openFreeDialog = function(id, params, callbackFunction, dialogName)
{
	this.openDialog(id, "FREE", params, callbackFunction, dialogName);
}

//------------------------------------------------------------------------------
// ダイアログ作成(中央)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.openCenterDialog = function(id, params, callbackFunction, dialogName)
{
	this.openDialog(id, "CENTER", params, callbackFunction, dialogName);
}

//------------------------------------------------------------------------------
// ダイアログ作成(共通)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.openDialog = function(id, type, params, callbackFunction, dialogName)
{
	// ダイアログIDが指定されていない場合は起動しない
	if (id == undefined){ return ""; }
	if (id == ""){ return ""; }

	if (dialogName == undefined){ dialogName = ""; }
	
	// 引数が定義されていなかった場合を考慮
	var param		= arrayParams2getParam(params);

	// 背景を描画する
	var backgroundObjId		= this.openBackground("#000000", 70);

	// 要素を追加するHTMLオブジェクトを先に取得しておく
	var tempParentObj		= this._parentObj;
	var dialogFreeObjs		= this._dialogFreeObjs;
	var dialogCenterObjs	= this._dialogCenterObjs;
	var parent				= this;

	// IDの作成
	var objId			= getUUID();

	// Ajaxによるダイアログ内容の取得
	$.get("./dialog/" + id + "?objId=" + objId + "&dialogName=" + dialogName + "&" + (+new Date()) + "&" + param, function(data)
	{

		// ダイアログオブジェクトを作成する
		var dialogDivObj							= document.createElement("div");		// DIVタグの動的生成
		dialogDivObj.id								= objId;								// IDの付与
		dialogDivObj.style.position					= "absolute";							// 描画位置
		dialogDivObj.style.zIndex					= parent.getZIndex();					// 奥行き
		dialogDivObj.innerHTML						= data;									// 内容
		dialogDivObj.style.display					= "none";								// 最初は非表示(フェードインで描画する為)

		// 作成したオブジェクトをHTML要素に追加する
		tempParentObj.appendChild(dialogDivObj);

		// 中央配置か自動移動か
		if      (type == "FREE")  { dialogFreeObjs[objId]   = {obj:dialogDivObj, id:objId, backgroundObjId:backgroundObjId}; }
		else if (type == "CENTER"){ dialogCenterObjs[objId] = {obj:dialogDivObj, id:objId, backgroundObjId:backgroundObjId}; }

		// 位置の調整
		parent.reallocation();

		// __FUSION_ACTにダイアログ情報を追加する
		if (document.getElementById("__FUSION_ACTION") != null)
		{
			document.getElementById("__FUSION_ACTION").value		= document.getElementById("__FUSION_ACTION").value + "@->@" + id + "(dialog:" + objId + ")";
		}

		// フェードイン表示
		$(dialogDivObj).fadeIn("slow", function()
		{
			// フェードイン後にコールバック関数の指定があればコールバック関数を実行する
			if (callbackFunction != undefined)
			{
				callbackFunction();
			}
		});
	});

	// ここで作成したオブジェクトのIDを返却する
	return objId;
}

//------------------------------------------------------------------------------
// 背景消去
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.closeBackground = function(objId)
{
	$("#"+objId).fadeOut("fast", function()
	{
		$("#"+objId).remove();
	});
}

//------------------------------------------------------------------------------
// ダイアログの内容変更
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.updateDialog = function(objId, id, params, callbackFunction)
{
	var dialogDivObj		= document.getElementById(objId);
	if (dialogDivObj != null)
	{
		// 引数が定義されていなかった場合を考慮
		var param		= "";
		if (params != undefined)
		{
			for (var key in params)
			{
				param += "&" + key + "=" + encodeURIComponent(params[key]);
			}
		}

		// 親オブジェクトの保持
		var parent				= this;

		// Ajaxによるダイアログ内容の取得
		$.get("./dialog/" + id + "?objId=" + objId + "&" + (+new Date()) + param, function(data)
		{
			// 内容の入れ替え
			dialogDivObj.innerHTML						= data;									// 内容

			// 位置の調整
			parent.reallocation();

			if (callbackFunction != undefined)
			{
				callbackFunction();
			}
		});
	}
}

//------------------------------------------------------------------------------
// ダイアログ消去
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONDialog.prototype.closeDialog = function(objId)
{
	var dialogFreeObjs		= this._dialogFreeObjs;
	var dialogCenterObjs	= this._dialogCenterObjs;
	var parent				= this;

	// 経路情報の削除
	if (document.getElementById("__FUSION_ACTION") != null)
	{
		document.getElementById("__FUSION_ACTION").value		= document.getElementById("__FUSION_ACTION").value.substr(0, document.getElementById("__FUSION_ACTION").value.lastIndexOf("@->@"));
	}

	$("#"+objId).fadeOut("fast", function()
	{
		$("#"+objId).remove();

		// 背景も削除する
		if (dialogFreeObjs[objId]   != null){ parent.closeBackground(dialogFreeObjs[objId]["backgroundObjId"]);   }
		if (dialogCenterObjs[objId] != null){ parent.closeBackground(dialogCenterObjs[objId]["backgroundObjId"]); }
	});
}

//------------------------------------------------------------------------------
// 直近で開いたダイアログの内容を更新する
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function updateDialog(id, param, callbackFunction)
{
	// 現在の経路情報を保持しているか確認する
	if (document.getElementById("__FUSION_ACTION") != null)
	{
		// 経路情報を取得する
		var fusionAct		= document.getElementById("__FUSION_ACTION").value;

		// 経路情報を分断する
		var fusionActRoutes	= fusionAct.split("@->@");

		// 最後の経路を取得
		var lastAct			= fusionActRoutes[fusionActRoutes.length - 1];

		// 最後の経路がダイアログの場合のみ処理を実施する
		if (lastAct.indexOf("(dialog:") != -1)
		{
			var dialogObjId		= lastAct.substr(lastAct.indexOf("(dialog:") + "(dialog:".length);
			dialogObjId			= dialogObjId.replace(")", "");

			dialog.updateDialog(dialogObjId, id, param, callbackFunction)
		}
	}
}

//------------------------------------------------------------------------------
// 直近で開いたダイアログを閉じる
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function closeDialog()
{
	// 現在の経路情報を保持しているか確認する
	if (document.getElementById("__FUSION_ACTION") != null)
	{
		// 経路情報を取得する
		var fusionAct		= document.getElementById("__FUSION_ACTION").value;

		// 経路情報を分断する
		var fusionActRoutes	= fusionAct.split("@->@");

		// 最後の経路を取得
		var lastAct			= fusionActRoutes[fusionActRoutes.length - 1];

		// 最後の経路がダイアログの場合のみ処理を実施する
		if (lastAct.indexOf("(dialog:") != -1)
		{
			var dialogObjId		= lastAct.substr(lastAct.indexOf("(dialog:") + "(dialog:".length);
			dialogObjId			= dialogObjId.replace(")", "");

			dialog.closeDialog(dialogObjId);
		}
	}
}

// フレームワーク中で利用する為に予約語"dialog"を定義しておく
var dialog = null;
appendEventListener(window, "load", function(){ dialog = new FUSIONDialog(document.getElementById("inputform")); }, false);
