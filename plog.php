<?php

	/**
	 * PLOG 1.0.0
	 * @author (C)Tomoya Koyanagi - http://www.pxt.jp/
	 */

	#--------------------------------------
	#	ライブラリをロード
	$contentpath = $px->realpath_files();
	if( !include_once( $contentpath.'/libs/plog.php' ) ){
		return false;
	}

	$className = 'cont_plog';
	if( !$className ){
		$px->error()->error_log( '$plogをロードできません。' , __FILE__ , __LINE__ );
		return	false;
	}
	$plog = new $className( &$px );
	#	/ ライブラリをロード
	#--------------------------------------


	#--------------------------------------
	#	コンテンツCSSを登録
	$StyleSheet = '';
	$StyleSheet .= '<link rel="stylesheet" href="'.t::h($px->path_files()).'res/css/contents.css" type="text/css" />'."\n";
	$px->theme()->send_content( $StyleSheet , 'head' );
	#	/ コンテンツCSSを登録
	#--------------------------------------

	#--------------------------------------
	#	設定項目を反映

	#	データベースのテーブル名プレフィックス (必要に応じて変更してください。)
	$plog->table_name = 'plog';

	#	ホームディレクトリ (必要に応じて変更してください。)
	$plog->path_home_dir = $px->get_conf('paths.px_dir').'_sys/ramdata/plog/';
	#	内部キャッシュディレクトリ (必要に応じて変更してください。)
	$plog->path_cache_dir = $px->get_conf('paths.px_dir').'_sys/caches/plog/';
	#	公開キャッシュディレクトリ (必要に応じて変更してください。)
	$plog->path_public_cache_dir = $_SERVER['DOCUMENT_ROOT'].'_caches/plog/';
	#	公開キャッシュディレクトリURL (必要に応じて変更してください。)
	$plog->url_public_cache_dir = 'http'.($this->px->req()->is_ssl()?'s':'').'://'.$_SERVER['HTTP_HOST'].($_SERVER['HTTP_PORT']?':'.$_SERVER['HTTP_PORT']:'').$this->px->get_install_path().'_caches/plog/';

	#	RSSファイルの保存先パス
	#	(必要に応じてコメントを外してください。設定しない場合、RSSは作成されません)
	#$plog->path_rss = '/rss/plog_diary';

	#	RSSファイルに適用するXSLTの保存先パス(共有リソースディレクトリ基点)
	#	(必要に応じてコメントを外してください。設定しない場合、RSSは作成されません)
	$plog->path_rss_xslt = array(
		#'rss1.0' =>'/xslt/plog_diary/rss1.0.xslt' ,
		#'rss2.0' =>'/xslt/plog_diary/rss2.0.xslt' ,
		#'atom1.0'=>'/xslt/plog_diary/atom1.0.xslt' ,
	);

	#	記事URLのテンプレート設定
	$plog->url_article = 'diary.article.{$article_cd}';
	$plog->url_article_rss = 'diary.article.{$article_cd}?rss=1';
	$plog->url_article_admin = 'diary.admin.article.{$article_cd}';

	#	ブログ名
	$plog->blog_name = 'Your BLOG name';

	#	インポート/エクスポート機能の有効/無効 (必要に応じて変更してください。)
	$plog->enable_function_export = true;
	$plog->enable_function_import = true;

	#	コメント入力時に求める値
	$plog->comment_userinfo_name = 'must';		#	コメンタの名前を取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plog->comment_userinfo_email = false;		#	コメンタのメールアドレスを取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plog->comment_userinfo_url = true;			#	コメンタのサイトURLを取得するか否か false=取得しない true=取得する 'must'=必須項目
	$plog->comment_userinfo_passwd = true;		#	コメント編集用パスワードを取得するか否か false=取得しない true=取得する 'must'=必須項目

	#	表示しようとした画像が存在しなかった場合に採用するNoImage画像。
	$plog->no_image_realpath = $px->realpath_files().'res/img/noimage_base.jpg';

	#	レポートメールのあて先
	//$plog->reportmail_to = 'youremail@example.com';

	$plog->article_summary_mode = 'manual';
		#	記事サマリモード
		#		bool false => 使用しない
		#		string 'auto' => 記事本文から自動的に指定
		#		string 'manual' => 手入力

	#	ヘルパーの設定
	#	(必要に応じてコメントアウトを外し、ライブラリを導入してください)
	$plog->helpers = array(
		/*
		'freemind'=>array(
			// FreeMind
			'url_freemind_flash_browser'=>$px->path_files().'res/freemind_flash_browser' ,
		) ,
		*/
		/*
		'captcha'=>array(
			// kcaptcha
			'name'=>'kcaptcha' ,
			'url'=>$px->path_files().'res/kcaptcha-2008-04-06' ,
		) ,
		*/
	);

	#	/ 設定項目を反映
	#--------------------------------------

	//コンテンツを実行して標準出力
	print $plog->execute_content();

?>