<?php

/**
 * PxFW - Content - [PLOG]
 * @author (C)Tomoya Koyanagi.
 * Last Update : 6:35 2012/08/15
 */

/**
 * コンテンツオブジェクトクラス [ cont_plog ]
 */
class cont_plog{
	var $px;

	var $path_lib = null;//ライブラリディレクトリのパスを記憶(コンストラクタで初期化)

	#--------------------------------------
	#	設定項目
	var $debug_mode = false;
		#	デバッグ/開発中のモード

	var $path_home_dir = null;
		#	プラグインのホームディレクトリ
	var $path_cache_dir = null;
		#	プラグインのキャッシュファイルディレクトリ
	var $path_public_cache_dir = null;
		#	プラグインの公開ファイルディレクトリ
	var $path_rss = null;
		#	RSSファイルの保存先ファイル名称
		#	$conf->path_docroot を基点に指定。
		#	ここで指定したディレクトリが存在する場合は、その中にRSSを生成します。
		#	何も存在しない場合は、それをファイル名として解釈し、その後ろに拡張子などを付加して生成します。
		#	ファイルが存在する場合はエラーとなります。
	var $path_rss_xslt = array( 'rss1.0'=>null , 'rss2.0'=>null , 'atom1.0'=>null );
		#	12:04 2008/01/15 追加
		#	RSSファイルに設定するXSLTのパス。
		#	共有リソースディレクトリ内の相対パスで指定。
	var $url_public_cache_dir = null;
		#	プラグインの公開ファイルディレクトリURL
	var $url_article = null;
	var $url_article_rss = null;
	var $url_article_admin = null;
		#	記事のURL。記事番号は、文字列で {$article_cd} とする。
		#	ページIDを指定しても可。
		#	URL指定の例： http://www.pxt.jp/ja/plog/{$article_cd}/index.html
		#	ページID指定の例： plog.{$article_cd}

	var $enable_trackback = true;
	var $enable_comments = true;
		#	コメント/トラックバック投稿機能の有効/無効
	var $trackback_auto_commit = false;
	var $comment_auto_commit = true;
		#	コメント/の自動承認設定。
		#	これらが true となっている場合、
		#	外部からの要求により、即座に反映されるようになります。
	var $enable_function_export = false;
	var $enable_function_import = false;
		#	インポート/エクスポート機能の有効/無効。

	var $comment_userinfo_name = true;		#	コメンタの名前を取得するか否か false=取得しない true=取得する 'must'=必須項目
	var $comment_userinfo_email = false;	#	コメンタのメールアドレスを取得するか否か false=取得しない true=取得する 'must'=必須項目
	var $comment_userinfo_url = false;		#	コメンタのサイトURLを取得するか否か false=取得しない true=取得する 'must'=必須項目
	var $comment_userinfo_passwd = false;	#	コメント編集用パスワードを取得するか否か false=取得しない true=取得する 'must'=必須項目

	var $article_summary_mode = 'auto';
		#	記事サマリモード
		#		bool false => 使用しない
		#		string 'auto' => 記事本文から自動的に指定
		#		string 'manual' => 手入力

	var $article_image_maxwidth = null;
		#	記事に表示する画像の、最大幅。
		#	この値を超える画像がこの値を超える場合、
		#	自動的にリサイズされる。

	var $srcs = array(
		#	PLOG 0.5.0 追加
		#	HTML中に差し込むオプションソース
		'article_header'=>'<!-- article_header -->' ,//記事本文の前
		'article_footer'=>'<!-- article_footer -->' ,//記事本文の後
	);

	var $helpers = array();
		#	2:40 2008/11/08 追加。
		#	リソースを取り扱うためのヘルパーの設定
		#	$helpers['freemind'] = array(	//	*.mm を扱う FreeMind Flash Browser の設定
		#		'url_freemind_flash_browser' => '～～～', //FreeMind Flash Browser をインストールしたURL
		#	);
		#	$helpers['captcha'] = array(	//	キャプチャ機能呼び出しの設定
		#		'name' => '～～～', //使用するキャプチャ機能の名前(現在 "kcaptcha" のみ可)
		#		'path' => '～～～', //キャプチャ機能のインストール先ディレクトリ(内部パス)
		#		'url' => '～～～', //キャプチャ機能のインストール先ディレクトリ(外部パス)
		#	);

	var $table_name = 'plog';
		#	テーブル名プレフィックス設定

	var $no_image_realpath = null;
		#	存在しない画像を表示しようとした場合に、
		#	代わりに表示する画像のパス。

	var $send_tbp_log_name = 'plog_tbp';
		#	TrackbackPing を送信したログを残す、ログ名。
		#	$conf->path_common_log_dir の後ろに付けられる、ディレクトリ名となる。
		#	スラッシュを含めてはならない。

	var $rss_limit_number = 50;
		#	RSSに書き出す記事の件数

	var $reportmail_to = null;
		#	レポートメールのあて先(PLOG 0.1.6 追加)

	var $blog_name = 'unknown';
		#	ブログ名
	var $blog_description = 'powered by Pickles Framework';
		#	ブログの説明
	var $blog_language = 'ja';
		#	ブログの自然言語
	var $blog_author_name = null;
		#	ブログの編集者名

	#	/ 設定項目
	#--------------------------------------

	/**
	 * コンストラクタ
	 */
	public function cont_plog( &$px ){
		$this->px = &$px;

		$contentpath = $px->get_local_resource_dir_realpath();
		$this->path_lib = $this->px->dbh()->get_realpath($contentpath.'/lib').'/';

//		if( is_null( $this->article_image_maxwidth ) && is_callable( array( $this->px->theme() , 'contents_min_width' ) ) ){
//			$this->article_image_maxwidth = $this->px->theme()->contents_min_width();
//		}
	}

	/**
	 * ホームディレクトリのパスを得る
	 */
	function get_home_dir(){
		if( strlen( $this->path_home_dir ) ){
			return	$this->path_home_dir;
		}
		$path = $this->px->get_conf('paths.px_dir').'_sys/ramdata/plog/';//←デフォルト
		return	$path;
	}

	/**
	 * 内部キャッシュディレクトリのパスを得る
	 */
	function get_cache_dir(){
		if( strlen( $this->path_cache_dir ) ){
			return	$this->path_cache_dir;
		}
		$path = $this->px->get_conf('paths.px_dir').'_sys/caches/plog/';//←デフォルト
		return	$path;
	}

	/**
	 * 公開キャッシュディレクトリのパスを得る
	 */
	function get_public_cache_dir(){
		if( strlen( $this->path_public_cache_dir ) ){
			return	$this->path_public_cache_dir;
		}
		$path = $_SERVER['DOCUMENT_ROOT'].'_caches/plog/';//←デフォルト
		return	$path;
	}

	/**
	 * 公開キャッシュディレクトリのURLを得る
	 */
	function get_url_public_cache_dir(){
		if( strlen( $this->url_public_cache_dir ) ){
			return	$this->url_public_cache_dir;
		}
		$path = 'http'.($this->px->req()->is_ssl()?'s':'').'://'.$_SERVER['HTTP_HOST'].($_SERVER['HTTP_PORT']?':'.$_SERVER['HTTP_PORT']:'').$this->px->get_install_path().'_caches/plog/';//←デフォルト
		return	$path;
	}

	/**
	 * 記事データディレクトリのパスを得る
	 */
	public function get_article_dir( $article_cd ){
		$base_dir = $this->get_home_dir().'/article_datas';

		$ary_path_id = preg_split( '/.{0}/' , $article_cd );
		$path_id = '';
		foreach( $ary_path_id as $dirname ){
			if( !strlen( $dirname ) || $dirname == '.' || $dirname == '..' ){ continue; }
			$path_id .= '/'.urlencode($dirname);
		}

		$RTN = $base_dir.$path_id.'/data/';

		return	$RTN;
	}


	/**
	 * オプションソースの入力
	 */
	public function set_src( $src , $name ){
		if( !strlen( $name ) ){ return false; }
		$this->srcs[$name] = $src;
		return true;
	}
	/**
	 * オプションソースの出力
	 */
	public function get_src( $name ){
		return $this->srcs[$name];
	}

	/**
	 * ライブラリをロードする
	 */
	public function require_lib( $lib_localpath ){
		$lib_localpath = preg_replace( '/^\/'.'*'.'/' , '/' , $lib_localpath );
		$lib_localpath = preg_replace( '/\/+/' , '/' , $lib_localpath );
		$classname_body = str_replace( '/' , '_' , text::trimext( $lib_localpath ) );

		$layer = 'cont';

		$adoptLayer = null;
		if( class_exists( 'cont'.$classname_body ) ){
			#	既にそのクラス名が存在していたら、そこでOK。
			return	'cont'.$classname_body;
		}
		if( isolated::require_once_with_conf( $this->path_lib.$lib_localpath , &$this->conf ) ){
			#	対象のファイルを見つけたら、
			#	パスをセットしてswitchを抜ける。
			if( class_exists( 'cont'.$classname_body ) ){
				#	クラスがちゃんと存在したら。
				$adoptLayer = 'cont';
			}
		}

		if( !class_exists( $adoptLayer.$classname_body ) ){
			return	false;
		}
		return	$adoptLayer.$classname_body;
	}

	/**
	 * 記事オブジェクトを作成
	 */
	public function &factory_article(){
		$className = $this->require_lib( '/plog/contents/article.php' );
		if( !$className ){
			$this->px->error()->error_log( 'FAILD to load library [article.php]' );
			return	false;
		}

		$RTN = new $className( &$this );
		return	$RTN;
	}


	/**
	 * 管理画面オブジェクトを作成
	 */
	public function &factory_admin(){
		$className = $this->require_lib( '/plog/contents/admin.php' );
		if( !$className ){
			$this->px->error()->error_log( 'FAILD to load library [admin.php]' );
			return	false;
		}

		$RTN = new $className( &$this );
		return	$RTN;
	}


	/**
	 * 記事パーサオブジェクトを作成
	 */
	public function &factory_articleparser(){
		$className = $this->require_lib( '/plog/articleParser/operator.php' );
		if( !$className ){
			$this->px->error()->error_log( 'FAILD to load library [articleParser/operator.php]' );
			return	false;
		}

		$RTN = new $className( &$this );
		return	$RTN;
	}


	/**
	 * DAOを作成
	 */
	public function &factory_dao( $dao_name ){
		$className = $this->require_lib( '/plog/dao/'.$dao_name.'.php' );
		if( !$className ){
			$this->px->error()->error_log( 'FAILD to load library ['.$dao_name.'.php]' );
			return	false;
		}

		$RTN = new $className( &$this );
		return	$RTN;
	}


	#--------------------------------------
	#	記事コード番号から、記事のURLを求める
	function get_article_url( $article_cd , $type = null ){
		$url_article = $this->url_article;
		if( $type == 'rss' && strlen( $this->url_article_rss ) ){
			$url_article = $this->url_article_rss;
		}elseif( $type == 'admin' && strlen( $this->url_article_admin ) ){
			$url_article = $this->url_article_admin;
		}
		if( !strlen( $url_article ) ){
			#	記事URL設定は必須
			return	false;
		}

		$RTN = preg_replace( '/'.preg_quote( '{$article_cd}' , '/' ).'/si' , urlencode( $article_cd ) , $url_article );
		$RTN = $this->px->theme()->href(
			$RTN ,
			array(
				'protocol'=>'http',
				'gene_deltemp'=>array(
					'ID',
					'PW',
					'CT',
					'OUTLINE',
					'THEME',
					'LANG',
					'T1',
				),
			)
		);
		return	$RTN;
	}

	#--------------------------------------
	#	記事コード番号から、記事のページIDを求める
	function get_article_pid( $article_cd ){
		$RTN = $this->req->po();
		$RTN .= '.article';
		$RTN .= '.'.intval($article_cd);
		return	$RTN;
	}

	#--------------------------------------
	#	トラックバックピング送信ログの保存先ディレクトリパスを得る
	function get_send_tbp_log_dir(){
		if( !strlen( $this->conf->path_common_log_dir ) ){ return false; }
		if( !is_dir( $this->conf->path_common_log_dir ) ){ return false; }

		if( !strlen( $this->send_tbp_log_name ) ){ return false; }
		if( preg_match( '/\/|\\\\/si' , $this->send_tbp_log_name ) ){ return false; }
			#	スラッシュ/バックスラッシュが含まれていてはならない
		if( $this->send_tbp_log_name == '.' || $this->send_tbp_log_name == '..' ){ return false; }
			#	CurrentDir/ParentDirは指定できない。

		$path_log_dir = $this->conf->path_common_log_dir.'/'.$this->send_tbp_log_name;
		if( !is_dir( $path_log_dir ) ){
			#	保存先ディレクトリが存在しなかったら。
			if( !$this->px->dbh()->mkdirall( $path_log_dir ) ){
				#	作成を試みて、ダメならダメ。
				return	false;
			}
		}

		if( !$this->px->dbh()->is_writable( $path_log_dir ) ){
			#	書き込めなかったらだめ。
			return	false;
		}

		return	realpath( $path_log_dir );

	}

}

?>