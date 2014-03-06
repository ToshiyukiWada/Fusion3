<?php
//==============================================================================
// include
//------------------------------------------------------------------------------
// フレームワークで利用する各ファイルを一括で読込む
// 
// 
// 
// 
//==============================================================================
// FUSIONで利用する処理・クラスを全てここで読込する
require_once dirname(__FILE__) . "/../config.php";						// グローバル設定ファイル
require_once dirname(__FILE__) . "/../controller.php";					// コントローラー設定ファイル
require_once dirname(__FILE__) . "/common/FusionCommon.php";			// FUSION共通処理
require_once dirname(__FILE__) . "/info/FusionConfig.php";				// FUSION設定ファイル構造体
require_once dirname(__FILE__) . "/info/FusionController.php";			// FUSIONコントローラーファイル構造体
require_once dirname(__FILE__) . "/info/FusionDatabase.php";			// FUSIONデータベース構造体
require_once dirname(__FILE__) . "/core/FusionMain.php";				// 処理メインクラス読込

?>
