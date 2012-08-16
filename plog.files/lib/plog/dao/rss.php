<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 13:19 2010/11/07

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_rss ]
class cont_plog_dao_rss{
	var $plogconf;
	var $conf;
	var $errors;
	var $dbh;
	var $theme;

	var $error_list = array();

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_rss( &$plogconf ){
		$this->plogconf = &$plogconf;
		$this->conf = &$plogconf->get_basicobj_conf();
		$this->errors = &$plogconf->get_basicobj_errors();
		$this->dbh = &$plogconf->get_basicobj_dbh();
		$this->theme = &$plogconf->get_basicobj_theme();
	}





	#--------------------------------------
	#	記事一覧を取得
	function get_article_list(){
		ob_start();?>
SELECT
	a.article_cd AS article_cd,
	a.article_title AS article_title,
	a.user_cd AS user_cd,
	a.article_summary AS article_summary,
	a.status AS status,
	a.category_cd AS category_cd,
	c.category_title AS category_title,
	c.category_subtitle AS category_subtitle,
	c.category_summary AS category_summary,
	a.release_date AS release_date,
	a.update_date AS update_date,
	a.create_date AS create_date
FROM :D:tableName_article a
	LEFT JOIN :D:tableName_category c ON a.category_cd = c.category_cd
WHERE
	a.del_flg = 0
	AND a.status = 1
	AND a.release_date <= :S:now -- >
ORDER BY release_date DESC
:D:limit_string
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$limit_number = 50;
		if( is_int( $this->plogconf->rss_limit_number ) ){
			$limit_number = $this->plogconf->rss_limit_number;
		}

		$limit_string = '';
		if( $this->conf->rdb['type'] == 'PostgreSQL' ){
			#	【 PostgreSQL 】
			$limit_string .= ' OFFSET '.intval(0).' LIMIT '.intval($limit_number);
		}else{
			#	【 MySQL/SQLite 】
			$limit_string .= ' LIMIT';
			$limit_string .= ' '.intval(0).',';
			$limit_string .= ' '.intval($limit_number);
		}

		$bindData = array(
			'tableName_article'=>$this->plogconf->table_name.'_article',
			'tableName_category'=>$this->plogconf->table_name.'_category',
			'limit_string'=>$limit_string,
			'now'=>$this->dbh->int2datetime(time()),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN;

	}




	#--------------------------------------
	#	RSSファイルを更新する。
	function update_rss_file(){
		if( !is_dir( $this->conf->path_docroot ) ){
			$this->internal_error( 'ドキュメントルート['.$this->conf->path_docroot.']が存在しません。' , __FILE__ , __LINE__ );
			return	false;
		}

		$path_rss = $this->plogconf->path_rss;
		if( !strlen( $path_rss ) ){
			$this->internal_error( 'RSSファイルの保存先が指定されていません。' , __FILE__ , __LINE__ );
			return	false;
		}

		$path_rss = $this->get_rss_realpath();
		if( $path_rss === false ){
			$this->internal_error( 'RSSファイルの保存先が不正です。' , __FILE__ , __LINE__ );
			return	false;
		}

		#	対象記事の一覧を得る。
		$target_article_list = $this->get_article_list();

		#--------------------------------------
		#	RSSを生成して保存する。
		$path_rss = $this->get_rss_realpath( 'rss1.0' );
		$SRC_RSS = $this->generate_rss_0100( $target_article_list );//RSS 1.0
		if( !$this->dbh->savefile( $path_rss , $SRC_RSS ) ){
			$this->internal_error( 'FAILD to save feed RSS 1.0 ['.$path_rss.']' , __FILE__ , __LINE__ );
		}
		unset( $path_rss , $SRC_RSS );

		$path_rss = $this->get_rss_realpath( 'rss2.0' );
		$SRC_RSS = $this->generate_rss_0200( $target_article_list );//RSS 2.0
		if( !$this->dbh->savefile( $path_rss , $SRC_RSS ) ){
			$this->internal_error( 'FAILD to save feed RSS 2.0 ['.$path_rss.']' , __FILE__ , __LINE__ );
		}
		unset( $path_rss , $SRC_RSS );

		$path_rss = $this->get_rss_realpath( 'atom1.0' );
		$SRC_RSS = $this->generate_rss_atom( $target_article_list );//ATOM
		if( !$this->dbh->savefile( $path_rss , $SRC_RSS ) ){
			$this->internal_error( 'FAILD to save feed ATOM ['.$path_rss.']' , __FILE__ , __LINE__ );
		}
		unset( $path_rss , $SRC_RSS );
		#	/ RSSを生成して保存する。
		#--------------------------------------

#		$this->internal_error( '開発中です。' , __FILE__ , __LINE__ );
#		return	false;

		if( $this->get_error_count() ){
			return	false;
		}

		return	true;
	}

	#--------------------------------------
	#	RSS1.0ソースを生成する
	function generate_rss_0100( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		if( strlen( $this->plogconf->path_rss_xslt['rss1.0'] ) && $this->theme->resource_exists( $this->plogconf->path_rss_xslt['rss1.0'] ) ){
			$RTN .= '<'.'?xml-stylesheet type="text/xsl" href="'.htmlspecialchars( $this->theme->resource( $this->plogconf->path_rss_xslt['rss1.0'] ) ).'" ?'.'>'."\n";
		}
		$RTN .= '<rdf:RDF';
		$RTN .= ' xmlns="http://purl.org/rss/1.0/"';
		$RTN .= ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"';
		$RTN .= ' xmlns:dc="http://purl.org/dc/elements/1.1/"';
		$RTN .= '>'."\n";
		$RTN .= '	<channel rdf:about="'.htmlspecialchars( $this->conf->url_sitetop ).'">'."\n";
		$RTN .= '		<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '		<link>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</link>'."\n";
		$RTN .= '		<description>'.htmlspecialchars( $this->get_blog_info('blog_description') ).'</description>'."\n";
		$RTN .= '		<items>'."\n";
		$RTN .= '			<rdf:Seq>'."\n";
		foreach( $article_array as $Line ){
			$RTN .= '				<rdf:li rdf:resource="'.htmlspecialchars( $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' ) ).'" />'."\n";
		}
		$RTN .= '			</rdf:Seq>'."\n";
		$RTN .= '		</items>'."\n";
		$RTN .= '	</channel>'."\n";
		foreach( $article_array as $Line ){
			$article_url = $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' );
			$RTN .= '		<item rdf:about="'.htmlspecialchars( $article_url ).'">'."\n";
			$RTN .= '			<title>'.htmlspecialchars( $Line['article_title'] ).'</title>'."\n";
			$RTN .= '			<link>'.htmlspecialchars( $article_url ).'</link>'."\n";
			$RTN .= '			<description><![CDATA['.htmlspecialchars( $Line['article_summary'] ).']]></description>'."\n";//descriptionはHTMLとして解釈されるのか？
			$RTN .= '			<dc:date>'.$this->mk_releasedate_string( time::datetime2int( $Line['release_date'] ) ).'</dc:date>'."\n";
			$RTN .= '		</item>'."\n";
			unset($article_url);
		}
		$RTN .= '</rdf:RDF>'."\n";
		return	$RTN;
	}

	#--------------------------------------
	#	RSS2.0ソースを生成する
	function generate_rss_0200( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		if( strlen( $this->plogconf->path_rss_xslt['rss2.0'] ) && $this->theme->resource_exists( $this->plogconf->path_rss_xslt['rss2.0'] ) ){
			$RTN .= '<'.'?xml-stylesheet type="text/xsl" href="'.htmlspecialchars( $this->theme->resource( $this->plogconf->path_rss_xslt['rss2.0'] ) ).'" ?'.'>'."\n";
		}
		$RTN .= '<rss version="2.0">'."\n";
		$RTN .= '	<channel>'."\n";
		$RTN .= '		<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '		<link>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</link>'."\n";
		$RTN .= '		<language>'.htmlspecialchars( $this->get_blog_info('language') ).'</language>'."\n";
		$RTN .= '		<description>'.htmlspecialchars( $this->get_blog_info('blog_description') ).'</description>'."\n";
		$RTN .= '		<pubDate>'.$this->mk_releasedate_string( time() ).'</pubDate>'."\n";
#		$RTN .= '		<guid>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</guid>'."\n";
		foreach( $article_array as $Line ){
			$RTN .= '		<item>'."\n";
			$RTN .= '			<title>'.htmlspecialchars( $Line['article_title'] ).'</title>'."\n";
			$RTN .= '			<link>'.htmlspecialchars( $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' ) ).'</link>'."\n";
			$RTN .= '			<description><![CDATA['.htmlspecialchars( $Line['article_summary'] ).']]></description>'."\n";//descriptionはHTMLとして解釈されるのか？
			$RTN .= '			<pubDate>'.$this->mk_releasedate_string( time::datetime2int( $Line['release_date'] ) ).'</pubDate>'."\n";
			$RTN .= '			<guid isPermaLink="true">'.htmlspecialchars( $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' ) ).'</guid>'."\n";
			$RTN .= '		</item>'."\n";
		}
		$RTN .= '	</channel>'."\n";
		$RTN .= '</rss>'."\n";
		return	$RTN;
	}

	#--------------------------------------
	#	ATOMソースを生成する
	function generate_rss_atom( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		if( strlen( $this->plogconf->path_rss_xslt['atom1.0'] ) && $this->theme->resource_exists( $this->plogconf->path_rss_xslt['atom1.0'] ) ){
			$RTN .= '<'.'?xml-stylesheet type="text/xsl" href="'.htmlspecialchars( $this->theme->resource( $this->plogconf->path_rss_xslt['atom1.0'] ) ).'" ?'.'>'."\n";
		}
		$RTN .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		$RTN .= '	<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '	<link rel="alternate" href="'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'" type="text/html" />'."\n";
		$RTN .= '	<updated>'.$this->mk_releasedate_string( time() ).'</updated>'."\n";
		$RTN .= '	<author>'."\n";
		$RTN .= '		<name>'.htmlspecialchars( $this->get_blog_info('blog_author_name') ).'</name>'."\n";
		$RTN .= '	</author>'."\n";
		$RTN .= '	<id>'.htmlspecialchars( md5( $this->get_blog_info('blog_index_url') ) ).'</id>'."\n";
		foreach( $article_array as $Line ){
			$RTN .= '	<entry>'."\n";
			$RTN .= '		<title>'.htmlspecialchars( $Line['article_title'] ).'</title>'."\n";
			$RTN .= '		<link rel="alternate" href="'.htmlspecialchars( $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' ) ).'" type="text/html" />'."\n";
			$RTN .= '		<id>'.htmlspecialchars( md5( $this->plogconf->get_article_url( $Line['article_cd'] , 'rss' ) ) ).'</id>'."\n";
			$RTN .= '		<updated>'.$this->mk_releasedate_string( time::datetime2int( $Line['release_date'] ) ).'</updated>'."\n";
			$RTN .= '		<summary>'.htmlspecialchars( $Line['article_summary'] ).'</summary>'."\n";//summary要素はHTMLとして解釈されない。type="html"をつけるとHTMLになるのかも。
			$RTN .= '		<content>'.htmlspecialchars( $Line['article_summary'] ).'</content>'."\n";//content要素はHTMLとして解釈されない。type="html"をつけるとHTMLになるのかも。
			$RTN .= '	</entry>'."\n";
		}
		$RTN .= '</feed>'."\n";
		return	$RTN;
	}



	#--------------------------------------
	#	リリース日を表す文字列を生成する
	function mk_releasedate_string( $releaseDate ){
		$Ymd = date( 'Y-m-d' , $releaseDate );
		$His = date( 'H:i:s' , $releaseDate );
		$dit = date( 'O' , $releaseDate );
		$dit = preg_replace( '/^(.*)([0-9]{2})$/' , '$1:$2' , $dit );
		return	$Ymd.'T'.$His.$dit;
	}



	#--------------------------------------
	#	ブログに関する諸情報を得る
	function get_blog_info( $name ){
		switch( strtolower( $name ) ){
			case 'blog_title':
				return	$this->plogconf->blog_name; break;
			case 'blog_index_url':
				return	$this->conf->url_sitetop; break;
			case 'blog_description':
				return	$this->plogconf->blog_description; break;
			case 'language':
				return	$this->plogconf->blog_language; break;
			case 'blog_author_name':
				return	$this->plogconf->blog_author_name; break;
			default:
				return	false;
				break;
		}
		return	false;
	}



	#--------------------------------------
	#	RSSファイルのURLを得る
	function get_rss_url( $version_name ){
		$path_rss = $this->plogconf->path_rss;
		if( !strlen( $path_rss ) ){
			#	RSSファイルの保存先が指定されていなければ。
			return	false;
		}elseif( !is_string( $path_rss ) ){
			#	RSSファイルの保存先が文字列型でなければ。
			return	false;
		}

		preg_replace( '/^\/*/' , '/' , $path_rss );
		$realpath_rss = $this->conf->path_docroot.$path_rss;

		$RTN = 'http://'.$this->conf->url_domain.$this->conf->url_root.$path_rss;
		if( is_dir( $realpath_rss ) ){
			#	ディレクトリ指定だったら。
			$RTN .= '/'.basename( $this->get_rss_realpath( $version_name ) );
		}else{
			#	ディレクトリが存在しなかったら。
			$RTN = dirname( $RTN ).'/'.basename( $this->get_rss_realpath( $version_name ) );
		}
		return	$RTN;
	}
	#--------------------------------------
	#	RSSファイルの絶対パスを得る
	function get_rss_realpath( $version_name = null ){
		$path_rss = $this->plogconf->path_rss;
		if( !strlen( $path_rss ) ){
			#	RSSファイルの保存先が指定されていなければ。
			return	false;
		}elseif( !is_string( $path_rss ) ){
			#	RSSファイルの保存先が文字列型でなければ。
			return	false;
		}

		preg_replace( '/^\/*/' , '/' , $path_rss );
		$realpath_rss = $this->conf->path_docroot.$path_rss;

		if( is_dir( $realpath_rss ) ){
			#	ディレクトリ指定だったら、その中に作成する。
			$prefix = 'rss';
			$dirname = $realpath_rss;
		}else{
			#	ディレクトリが存在しなかったら、
			#	それをプリフィックスとして扱う。
			$prefix = basename( $realpath_rss );
			$dirname = dirname( $realpath_rss );
			if( !$this->dbh->mkdirall( $dirname ) ){
				$this->internal_error( '書き出し先ディレクトリの作成に失敗しました。' , __FILE__ , __LINE__ );
				return	false;
			}
		}

		if( is_null( $version_name ) ){
			#	名前を受け取らなかったら、
			#	そのまま返しちゃう、
			return	$realpath_rss;
		}

		if( is_dir( $realpath_rss ) ){
			#	ディレクトリ指定の場合
			$sep = '/';
		}else{
			#	ファイルプレフィックス指定の場合
			$sep = '_';
		}

		$RTN = $dirname.'/'.$prefix;
		switch( strtolower( $version_name ) ){
			case 'rss1.0':
				$RTN .= $sep.'rss0100.rdf';break;
			case 'rss2.0':
				$RTN .= $sep.'rss0200.xml';break;
			case 'atom1.0':
				$RTN .= $sep.'atom0100.xml';break;
			default:
				return false;break;
		}

		return	$RTN;
	}

	#--------------------------------------
	#	オブジェクト内部エラーを記憶
	function internal_error( $error_msg , $FILE = '' , $LINE = 0 ){
		if( !is_array( $this->error_list ) ){ $this->error_list = array(); }
		array_push( $this->error_list , array( 'message'=>$error_msg , 'file'=>$FILE , 'line'=>$LINE ) );
		return	true;
	}
	#--------------------------------------
	#	オブジェクト内部エラーを取得
	function get_error_list(){
		return	$this->error_list;
	}
	#--------------------------------------
	#	エラー数を調べる
	function get_error_count(){
		return	count( $this->error_list );
	}

}

?>