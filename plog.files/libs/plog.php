<?php

/**
 * PLOGメインオブジェクトクラス
 * PxFW - Content - [PLOG]
 * @author Tomoya Koyanagi
 */
class cont_plog{
	public $px;

	private $path_lib = null;//ライブラリディレクトリのパスを記憶(コンストラクタで初期化)

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

	var $blog_name = 'Your BLOG name';
		#	ブログ名

	var $blog_description = '';
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
	public function __construct( $px ){
		$this->px = $px;

		$content_path = $px->realpath_files();
		$this->path_lib = $this->px->dbh()->get_realpath($content_path.'/libs').'/';

		/*
		//↓UTODO: PxFWから削除された機能を使用するため、一時的にコメントアウト。
		if( is_null( $this->article_image_maxwidth ) && is_callable( array( $this->px->theme() , 'contents_min_width' ) ) ){
			$this->article_image_maxwidth = $this->px->theme()->contents_min_width();
		}
		*/
	}

	/**
	 * コンテンツの処理を実行する。
	 */
	public function execute_content(){
		$current_page_info = $this->px->site()->get_current_page_info();
		$path_original = $this->px->site()->bind_dynamic_path_param($current_page_info['path'],array(''=>''));

		#--------------------------------------
		#	コンテンツの描画
		if( preg_match( '/admin\/(?:index\.html)?/s' , $path_original ) ){
			#	管理画面
			$plog_article = &$this->factory_admin();
			$SRC = $plog_article->start();
		}else{
			#	ブログ
			$plog_article = &$this->factory_article();
			$SRC = $plog_article->start();
		}
		#	/ コンテンツの描画
		#--------------------------------------

		return	$SRC;
	}//execute_content()

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
		$classname_body = str_replace( '/' , '_' , t::trimext( $lib_localpath ) );

		$layer = 'cont';

		$adoptLayer = null;
		if( class_exists( 'cont'.$classname_body ) ){
			#	既にそのクラス名が存在していたら、そこでOK。
			return	'cont'.$classname_body;
		}
		if( is_file( $this->path_lib.$lib_localpath ) ){
			#	対象のファイルを見つけたら、
			#	パスをセットしてswitchを抜ける。
			include_once($this->path_lib.$lib_localpath);
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
	}//get_article_url()

	#--------------------------------------
	#	記事コード番号から、記事のページIDを求める
	function get_article_pid( $article_cd ){
		$RTN = $this->px->req()->po();
		$RTN .= '.article';
		$RTN .= '.'.intval($article_cd);
		return	$RTN;
	}//get_article_pid()

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

	}//get_send_tbp_log_dir()


	/**
	 * 日付選択インターフェイスを作る
	 * 旧PxFW0.x系から移植
	 */
	public function mk_form_select_date( $mode = 'input' , $option = array() ){
		#	18:32 2007/09/28 Pickles Framework 0.1.9 追加

		#	カラム名を決定
		$name = array();
		if( !strlen($option['prefix']) ){
			$option['prefix'] = 'selectdate';
		}
		$name['y'] = $option['prefix'].'_date_y';
		$name['m'] = $option['prefix'].'_date_m';
		$name['d'] = $option['prefix'].'_date_d';
		$name['h'] = $option['prefix'].'_date_h';
		$name['i'] = $option['prefix'].'_date_i';
		$name['s'] = $option['prefix'].'_date_s';

		#	デフォルト値を決定
		$int_now = time();
		if( strlen( $option['default'] ) ){
			if( preg_match( '/^[0-9]+$/is' , $option['default'] ) ){
				$int_now = intval( $option['default'] );
			}else{
				$int_now = $this->px->dbh()->datetime2int( $option['default'] );
			}
		}

		if( strlen( $this->px->req()->get_param( $name['y'] ) ) || strlen( $this->px->req()->get_param( $name['m'] ) ) || strlen( $this->px->req()->get_param( $name['d'] ) ) || strlen( $this->px->req()->get_param( $name['h'] ) ) || strlen( $this->px->req()->get_param( $name['i'] ) ) || strlen( $this->px->req()->get_param( $name['s'] ) ) ){
			#	実際に選択された値で、デフォルト値を上書き
			$int_now = mktime(
				intval( $this->px->req()->get_param( $name['h'] ) ) ,
				intval( $this->px->req()->get_param( $name['i'] ) ) ,
				intval( $this->px->req()->get_param( $name['s'] ) ) ,
				intval( $this->px->req()->get_param( $name['m'] ) ) ,
				intval( $this->px->req()->get_param( $name['d'] ) ) ,
				intval( $this->px->req()->get_param( $name['y'] ) )
			);
		}
		if( $mode == 'get_int' ){
			#	INT値を返すモード
			return	$int_now;
		}elseif( $mode == 'get_datetime' ){
			#	datetime値を返すモード
			return	$this->px->dbh()->int2datetime( $int_now );
		}

		$dateinfo = getdate( $int_now );

		$selected = array();
		$selected['y'] = intval( $dateinfo['year'] );
		$selected['m'] = intval( $dateinfo['mon'] );
		$selected['d'] = intval( $dateinfo['mday'] );
		$selected['h'] = intval( $dateinfo['hours'] );
		$selected['i'] = intval( $dateinfo['minutes'] );
		$selected['s'] = intval( $dateinfo['seconds'] );
		unset($dateinfo);

		#--------------------------------------
		#	レイアウトを決定
		$layout = $option['layout'];
		if( !strlen( $layout ) ){
			$layout = '[Y]/[M]/[D] [H]:[I]:[S]';
		}

		if( $mode == 'confirm' ){
			#	確認用出力モード
			$RTN = $layout;
			$RTN = preg_replace( '/'.preg_quote('[Y]','/').'/i' , intval( $this->px->req()->get_param( $name['y'] ) ) , $RTN );
			$RTN = preg_replace( '/'.preg_quote('[M]','/').'/i' , intval( $this->px->req()->get_param( $name['m'] ) ) , $RTN );
			$RTN = preg_replace( '/'.preg_quote('[D]','/').'/i' , intval( $this->px->req()->get_param( $name['d'] ) ) , $RTN );
			$RTN = preg_replace( '/'.preg_quote('[H]','/').'/i' , intval( $this->px->req()->get_param( $name['h'] ) ) , $RTN );
			$RTN = preg_replace( '/'.preg_quote('[I]','/').'/i' , intval( $this->px->req()->get_param( $name['i'] ) ) , $RTN );
			$RTN = preg_replace( '/'.preg_quote('[S]','/').'/i' , intval( $this->px->req()->get_param( $name['s'] ) ) , $RTN );
			return	$RTN;
		}elseif( $mode == 'hidden' ){
			#	hiddenタグ出力モード
			$RTN = '';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['y'] ).'" value="'.intval( $this->px->req()->get_param( $name['y'] ) ).'" />';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['m'] ).'" value="'.intval( $this->px->req()->get_param( $name['m'] ) ).'" />';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['d'] ).'" value="'.intval( $this->px->req()->get_param( $name['d'] ) ).'" />';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['h'] ).'" value="'.intval( $this->px->req()->get_param( $name['h'] ) ).'" />';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['i'] ).'" value="'.intval( $this->px->req()->get_param( $name['i'] ) ).'" />';
			$RTN .= '<input type="hidden" name="'.htmlspecialchars( $name['s'] ).'" value="'.intval( $this->px->req()->get_param( $name['s'] ) ).'" />';
			return	$RTN;
		}


		#	年
		$SRC['y'] = '';
		$SRC['y'] .= '<select name="'.htmlspecialchars($name['y']).'">';
		$c = array( intval( $selected['y'] ) =>' selected="selected"' );
		$max_year = date('Y');
		if( strlen( $option['max_year'] ) ){
			#	Pickles Framework 0.5.5 : $option['max_year'] を追加。
			$max_year = intval($option['max_year']);
		}
		$min_year = 1970;
		if( strlen( $option['min_year'] ) ){
			#	Pickles Framework 0.5.5 : $option['min_year'] を追加。
			$min_year = intval($option['min_year']);
		}
		for( $i = $min_year; $i <= $max_year; $i ++ ){
			$i = intval( $i );
			$SRC['y'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval( $i ).'</option>';
		}
		$SRC['y'] .= '</select>';

		#	月
		$SRC['m'] = '';
		$SRC['m'] .= '<select name="'.htmlspecialchars($name['m']).'">';
		$c = array( intval( $selected['m'] ) =>' selected="selected"' );
		for( $i = 1; $i <= 12; $i ++ ){
			$i = intval( $i );
			$SRC['m'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval($i).'</option>';
		}
		$SRC['m'] .= '</select>';

		#	日
		$SRC['d'] = '';
		$SRC['d'] .= '<select name="'.htmlspecialchars($name['d']).'">';
		$c = array( intval( $selected['d'] ) =>' selected="selected"' );
		for( $i = 1; $i <= 31; $i ++ ){
			$i = intval( $i );
			$SRC['d'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval($i).'</option>';
		}
		$SRC['d'] .= '</select>';

		#	時
		$SRC['h'] = '';
		$SRC['h'] .= '<select name="'.htmlspecialchars($name['h']).'">';
		$c = array( intval( $selected['h'] ) =>' selected="selected"' );
		for( $i = 0; $i <= 23; $i ++ ){
			$i = intval( $i );
			$SRC['h'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval($i).'</option>';
		}
		$SRC['h'] .= '</select>';

		#	分
		$SRC['i'] = '';
		$SRC['i'] .= '<select name="'.htmlspecialchars($name['i']).'">';
		$c = array( intval( $selected['i'] ) =>' selected="selected"' );
		for( $i = 0; $i <= 59; $i ++ ){
			$i = intval( $i );
			$SRC['i'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval($i).'</option>';
		}
		$SRC['i'] .= '</select>';

		#	秒
		$SRC['s'] = '';
		$SRC['s'] .= '<select name="'.htmlspecialchars($name['s']).'">';
		$c = array( intval( $selected['s'] ) =>' selected="selected"' );
		for( $i = 0; $i <= 59; $i ++ ){
			$i = intval( $i );
			$SRC['s'] .= '<option value="'.intval( $i ).'"'.$c[$i].'>'.intval($i).'</option>';
		}
		$SRC['s'] .= '</select>';

		$RTN = $layout;
		$RTN = preg_replace( '/'.preg_quote('[Y]','/').'/i' , $SRC['y'] , $RTN );
		$RTN = preg_replace( '/'.preg_quote('[M]','/').'/i' , $SRC['m'] , $RTN );
		$RTN = preg_replace( '/'.preg_quote('[D]','/').'/i' , $SRC['d'] , $RTN );
		$RTN = preg_replace( '/'.preg_quote('[H]','/').'/i' , $SRC['h'] , $RTN );
		$RTN = preg_replace( '/'.preg_quote('[I]','/').'/i' , $SRC['i'] , $RTN );
		$RTN = preg_replace( '/'.preg_quote('[S]','/').'/i' , $SRC['s'] , $RTN );
		return	$RTN;
	}//mk_form_select_date()

}

?>