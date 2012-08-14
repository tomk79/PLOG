<?php

	/**
	 * PLOG 1.0.0
	 * @author (C)Tomoya Koyanagi - http://www.pxt.jp/
	 */

	#--------------------------------------
	#	コンテンツCSSを登録
	$StyleSheet = '';
	$StyleSheet .= '<link rel="stylesheet" href="'.htmlspecialchars($theme->resource('css/contents.css')).'" type="text/css" />'."\n";
	$theme->setsrc( $StyleSheet , 'additional_header' );
	#	/ コンテンツCSSを登録
	#--------------------------------------


	#--------------------------------------
	#	ライブラリをロード
	$contentpath = $theme->parse_contentspath();
	if( !require_once( $contentpath['path_workdir'].'/lib/PLOG/config.php' ) ){
		return false;
	}

	$className = 'cont_PLOG_config';
	if( !$className ){
		$errors->error_log( '$plogconfをロードできません。' , __FILE__ , __LINE__ );
		return	false;
	}
	$plogconf = new $className( &$conf , &$errors , &$dbh , &$req , &$user , &$site , &$theme , &$custom );
	#	/ ライブラリをロード
	#--------------------------------------

	#--------------------------------------
	#	設定項目を反映

	#	データベースのテーブル名 (必要に応じて変更してください。)
	$plogconf->table_name = array(
		'article'  =>'pxt255_plog_diary_article',
		'category' =>'pxt255_plog_diary_category',
		'tbp_send' =>'pxt255_plog_diary_tbp_send',
		'trackback'=>'pxt255_plog_diary_trackback',
		'comment'  =>'pxt255_plog_diary_comment',
		'search'   =>'pxt255_plog_diary_search',
	);

	#	プラグインのホームディレクトリ (必要に応じて変更してください。)
	$plogconf->path_home_dir = $conf->path_ramdata_dir.'/plugins/PLOG/diary/';
	#	プラグインのキャッシュファイルディレクトリ (必要に応じて変更してください。)
	$plogconf->path_cache_dir = $contentpath['path_workdir_cache'];//←PxFW 0.6.10以降で利用可能。
#	$plogconf->path_cache_dir = $conf->path_cache_dir.'/plog';//←PxFW 0.6.9以前はこちら
	#	プラグインの公開ファイルディレクトリ (必要に応じて変更してください。)
	$plogconf->path_public_dir = $conf->path_docroot.$conf->url_localresource.$theme->get_contresource_cache_localpath().'/resources/c';
	#	プラグインの公開ファイルディレクトリURL (必要に応じて変更してください。)
	$plogconf->url_public_dir = $conf->url_root.$conf->url_localresource.$theme->get_contresource_cache_localpath().'/resources/c';

	#	RSSファイルの保存先パス
	#	(必要に応じてコメントを外してください。設定しない場合、RSSは作成されません)
	$plogconf->path_rss = '/rss/plog_diary';

	#	RSSファイルに適用するXSLTの保存先パス(共有リソースディレクトリ基点)
	#	(必要に応じてコメントを外してください。設定しない場合、RSSは作成されません)
	$plogconf->path_rss_xslt = array(
		'rss1.0' =>'/xslt/plog_diary/rss1.0.xslt' ,
		'rss2.0' =>'/xslt/plog_diary/rss2.0.xslt' ,
		'atom1.0'=>'/xslt/plog_diary/atom1.0.xslt' ,
	);

	#	記事URLのテンプレート設定
	$plogconf->url_article = 'diary.article.{$article_cd}';
	$plogconf->url_article_rss = 'diary.article.{$article_cd}?rss=1';
	$plogconf->url_article_admin = 'diary.admin.article.{$article_cd}';

	#	ブログ名
	$plogconf->blog_name = $site->gettitle().' - '.$site->getpageinfo( 'diary' , 'title' );

	#	インポート/エクスポート機能の有効/無効 (必要に応じて変更してください。)
	$plogconf->enable_function_export = true;
	$plogconf->enable_function_import = true;

	#	コメント入力時に求める値
	$plogconf->comment_userinfo_name = 'must';		#	コメンタの名前を取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plogconf->comment_userinfo_email = false;		#	コメンタのメールアドレスを取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plogconf->comment_userinfo_url = true;			#	コメンタのサイトURLを取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plogconf->comment_userinfo_passwd = true;		#	コメント編集用パスワードを取得するか否か false=取得しない true=取得する 'must'=必須項目

	#	表示しようとした画像が存在しなかった場合に採用するNoImage画像。
	$plogconf->no_image_realpath = $theme->resource('img/noimage_base.jpg');

	#	レポートメールのあて先
	$plogconf->reportmail_to = $conf->email['info'];

	$plogconf->article_summary_mode = 'manual';
		#	記事サマリモード
		#		bool false => 使用しない
		#		string 'auto' => 記事本文から自動的に指定
		#		string 'manual' => 手入力

	#	ヘルパーの設定
	#	(必要に応じてコメントアウトを外し、ライブラリを導入してください)
	$plogconf->helpers = array(
		'freemind'=>array(
			// FreeMind
			'url_freemind_flash_browser'=>$theme->resource( 'freemind_flash_browser' ) ,
		) ,
		'captcha'=>array(
			// kcaptcha
			'name'=>'kcaptcha' ,
			'url'=>$theme->resource('kcaptcha-2008-04-06') ,
		) ,
	);

	#	/ 設定項目を反映
	#--------------------------------------

	#--------------------------------------
	#	コンテンツの描画
	if( $req->poelm(-1) == 'admin' ){
		#	管理画面
		$plog_article = &$plogconf->factory_admin();
		$SRC = $plog_article->start();
	}else{
		#	ブログ
		$plog_article = &$plogconf->factory_article();
		$SRC = $plog_article->start();
	}
	#	/ コンテンツの描画
	#--------------------------------------

	return	$SRC;

?>