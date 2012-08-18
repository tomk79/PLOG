<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 16:12 2009/12/29

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_visitor ]
class cont_plog_dao_visitor{
	var $plog;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_visitor( &$plog ){
		$this->plog = &$plog;
		$this->conf = &$plog->get_basicobj_conf();
		$this->errors = &$plog->get_basicobj_errors();
		$this->dbh = &$plog->get_basicobj_dbh();
	}


	#--------------------------------------
	#	記事一覧を取得
	function get_article_list( $category_cd = null , $limit_offset = 0 , $limit_count = 10 ){
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
:D:category_cd_string
ORDER BY release_date DESC
:D:limit_string
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$limit_string = '';
		if( $this->conf->rdb['type'] == 'PostgreSQL' ){
			#	【 PostgreSQL 】
			$limit_string .= ' OFFSET '.intval($limit_offset).' LIMIT '.intval($limit_count);
		}else{
			#	【 MySQL/SQLite 】
			$limit_string .= ' LIMIT';
			$limit_string .= ' '.intval($limit_offset).',';
			$limit_string .= ' '.intval($limit_count);
		}

		$category_cd_string = '';
		if( strlen( $category_cd ) ){
			$category_cd_string = '	AND a.category_cd = '.intval( $category_cd );
		}


		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article',
			'tableName_category'=>$this->plog->table_name.'_category',
			'limit_string'=>$limit_string,
			'category_cd_string'=>$category_cd_string,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN;

	}

	#--------------------------------------
	#	記事の総数を取得
	function get_article_count( $category_cd = null ){
		ob_start();?>
SELECT count(*) AS count
FROM :D:tableName
WHERE
	del_flg = 0
	AND status = 1
	AND release_date < :S:now -- >
:D:category_cd_string
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$category_cd_string = '';
		if( strlen( $category_cd ) ){
			$category_cd_string = '	AND category_cd = '.intval( $category_cd );
		}


		$bindData = array(
			'tableName'=>$this->plog->table_name.'_article',
			'category_cd_string'=>$category_cd_string,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	intval( $RTN[0]['count'] );

	}

	#--------------------------------------
	#	記事情報を取得
	function get_article_info( $article_cd ){
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
	AND a.release_date < :S:now -- >
	AND a.article_cd = :N:article_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article',
			'tableName_category'=>$this->plog->table_name.'_category',
			'article_cd'=>$article_cd,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN[0];

	}




	#--------------------------------------
	#	コメントの数を取得
	function get_comment_count( $target_article_cd ){
		if( is_array( $target_article_cd ) ){
			$target_article_cd = implode( ',' , $target_article_cd );
		}else{
			$target_article_cd = intval( $target_article_cd );
		}


		ob_start();?>
SELECT article_cd,count(*) AS count
FROM :D:tableName
WHERE
	del_flg = 0
	AND article_cd IN (:D:target_article_cd)
	AND status = 1
GROUP BY article_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_comment',
			'target_article_cd'=>$target_article_cd,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();
		$RTN = array();
		foreach( $DATA as $line ){
			$RTN[$line['article_cd']] = intval($line['count']);
		}

		return	$RTN;

	}

	#--------------------------------------
	#	全コメントの数を取得
	function get_comment_allcount( $target_article_cd ){
		if( is_array( $target_article_cd ) ){
			$target_article_cd = implode( ',' , $target_article_cd );
		}else{
			$target_article_cd = intval( $target_article_cd );
		}


		ob_start();?>
SELECT article_cd,count(*) AS count
FROM :D:tableName
WHERE
	del_flg = 0
	AND article_cd IN (:D:target_article_cd)
	AND status = 1
GROUP BY article_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_comment',
			'target_article_cd'=>$target_article_cd,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();
		$RTN = array();
		foreach( $DATA as $line ){
			$RTN[$line['article_cd']] = intval($line['count']);
		}

		return	$RTN;

	}

	#--------------------------------------
	#	トラックバックの数を取得
	function get_trackback_count( $target_article_cd ){
		if( is_array( $target_article_cd ) ){
			$target_article_cd = implode( ',' , $target_article_cd );
		}else{
			$target_article_cd = intval( $target_article_cd );
		}


		ob_start();?>
SELECT article_cd,count(*) AS count
FROM :D:tableName
WHERE
	del_flg = 0
	AND article_cd IN (:D:target_article_cd)
	AND status = 1
GROUP BY article_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'target_article_cd'=>$target_article_cd,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();
		$RTN = array();
		foreach( $DATA as $line ){
			$RTN[$line['article_cd']] = intval($line['count']);
		}

		return	$RTN;

	}

	#--------------------------------------
	#	全トラックバックの数を取得
	function get_trackback_allcount( $target_article_cd ){
		if( is_array( $target_article_cd ) ){
			$target_article_cd = implode( ',' , $target_article_cd );
		}else{
			$target_article_cd = intval( $target_article_cd );
		}


		ob_start();?>
SELECT article_cd,count(*) AS count
FROM :D:tableName
WHERE
	del_flg = 0
	AND article_cd IN (:D:target_article_cd)
GROUP BY article_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'target_article_cd'=>$target_article_cd,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();
		$RTN = array();
		foreach( $DATA as $line ){
			$RTN[$line['article_cd']] = intval($line['count']);
		}

		return	$RTN;

	}


	#--------------------------------------
	#	画像をキャッシュする
	function create_image_cache( $article_cd , $filename , $option = array() ){
		if( preg_match( '/^https?\:\/\//si' , $filename ) ){
			#	ネットワークリソースならそのまま返す。
			return $filename;
		}

		#--------------------------------------
		#	オリジナルのパス
		$path_original_file = $this->plog->get_article_dir( $article_cd ).'/images/'.$filename;
		if( !is_file( $path_original_file ) && is_file( $this->plog->no_image_realpath ) ){
			#	No Image 画像が指定されていたら、
			#	それを採用する。
			$path_original_file = realpath( $this->plog->no_image_realpath );
		}
		if( !is_file( $path_original_file ) ){ return false; }

		#--------------------------------------
		#	キャッシュのパス
		$path_cache_basedir = $this->plog->get_public_cache_dir();
		if( !is_dir( $path_cache_basedir ) ){
			if( !$this->dbh->mkdirall( $path_cache_basedir ) ){
				return	false;
			}
		}

		$is_image = true;
		switch( strtolower( $this->dbh->get_extension($filename) ) ){
			case 'gif':
			case 'png':
			case 'jpg':
			case 'jpe':
			case 'jpeg':
			case 'bmp':
				$real_image_name = $filename.'@m'.intval($this->plog->article_image_maxwidth).'w'.intval($option['width']).'h'.intval($option['height']).'.'.$this->dbh->get_extension($filename);
				$is_image = true;
				break;
			default:
				#	画像ファイルと認識できない拡張子がついていたら
				#	リサイズとかはしないで、ダウンロードファイルとして取り扱う。
				$real_image_name = $filename;
				$is_image = false;
				break;
		}

		$ary_path_id = preg_split( '/.{0}/' , $article_cd );
		$path_id = '';
		foreach( $ary_path_id as $dirname ){
			if( !strlen( $dirname ) || $dirname == '.' || $dirname == '..' ){ continue; }
			$path_id .= '/'.urlencode($dirname);
		}

		$path_cache_file = $path_cache_basedir.$path_id.'/'.$real_image_name;
		if( !is_dir( dirname( $path_cache_file ) ) ){
			$this->dbh->mkdirall( dirname( $path_cache_file ) );
		}
		$RTN = $this->plog->get_url_public_cache_dir().$path_id.'/'.$real_image_name;

		#--------------------------------------
		#	更新日比較
		if( $this->dbh->comp_filemtime( $path_cache_file , $path_original_file ) ){
			#	キャッシュが新しかったら、おしまい。
			return	$RTN;
		}

		#######################################
		#	キャッシュ作成
		if( !$this->dbh->copy( $path_original_file , $path_cache_file ) ){
			#	仮に、単にコピーするだけ。
			return	false;
		}
		if( !$is_image ){
			#	画像として取り扱わなければ、ここで終わり。
			return	$RTN;
		}

		$className = $this->dbh->require_lib( '/resources/image.php' );
		if( !$className ){
			#	画像オブジェクトの生成に失敗したら。エラーです。
			$this->errors->error_log( 'FAILD to create object [/resources/image.php].' , __FILE__ , __LINE__ );
			return	$RTN;
		}
		$obj_image = new $className( &$this->conf , &$this->req , &$this->dbh , &$this->errors );
		if( !$obj_image->enable() ){
			#	画像オブジェクトの生成に失敗したら。エラーです。
			$this->errors->error_log( 'image object is not active.' , __FILE__ , __LINE__ );
			return	$RTN;
		}

		#	画像オブジェクトに画像をセットする。
		$obj_image->set_image( $path_cache_file );

		#	画像の大きさを調べる
		$imgSize = $obj_image->getimagesize();

		if( $option['width'] && $option['height'] ){
			#	画像サイズを指定されていたらリサイズ
			$new_width = intval( $option['width'] );
			$new_height = intval( $option['height'] );
			if( intval( $new_width ) > $this->plog->article_image_maxwidth ){
				#	最大画像幅を超えていたらそれを超えられない
				$rate = intval( $this->plog->article_image_maxwidth )/intval( $new_width );
				$new_width = intval( $this->plog->article_image_maxwidth );
				$new_height = intval( $new_height*$rate );
			}
			$obj_image->resize( $new_width , $new_height , array() );

		}elseif( $option['width'] ){
			#	画像幅を指定されていたらリサイズ
			$new_width = intval( $option['width'] );
			if( intval( $new_width ) > $this->plog->article_image_maxwidth ){
				#	最大画像幅を超えていたらそれを超えられない
				$new_width = intval( $this->plog->article_image_maxwidth );
			}
			$obj_image->fit2width( $new_width , array() );

		}elseif( $option['height'] ){
			#	画像高を指定されていたらリサイズ
			$new_height = intval( $option['height'] );
			$obj_image->fit2height( $new_height , array() );

		}elseif( $imgSize[0] > $this->plog->article_image_maxwidth ){
			#	最大画像幅を超えていたらリサイズ
			$obj_image->fit2width( $this->plog->article_image_maxwidth , array() );
		}

		#	MIMEタイプをセットする
		$obj_image->set_mime( $this->dbh->get_extension( $path_cache_file ) );

		#	編集した画像を保存する
		$obj_image->saveimage();

		#	/ キャッシュ作成
		#######################################

		return	$RTN;
	}




	#--------------------------------------
	#	カテゴリの一覧を得る
	function get_category_list(){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	del_flg = 0
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		return	$DATA;
	}


	#--------------------------------------
	#	カテゴリの情報を得る
	function get_category_info( $category_cd ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	del_flg = 0
	AND category_cd = :N:category_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
			'category_cd'=>$category_cd,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		return	$DATA[0];
	}






	#--------------------------------------
	#	記事を検索する
	function search_article( $keyword , $limit_offset = 0 , $limit_count = 10 , $option = array() ){
		$keyword = trim( $keyword );
		if( !strlen( $keyword ) ){ return array(); }

		#--------------------------------------
		#	マッチング用のWHERE句を作成
		$keywords = preg_split( '/(?: |　|\t|\r\n|\r|\n)+/' , $keyword );
		if( !count( $keywords ) ){ return 0; }
		$sql_wheres = array();
		$SQL_WHERE_TPL = '( lower( atc.article_title ) LIKE :S:keyword OR lower( atc.article_summary ) LIKE :S:keyword OR lower( sch.article_bodytext ) LIKE :S:keyword )';
		foreach( $keywords as $a_word ){
			$bindData = array( 'keyword'=>'%'.strtolower( $a_word ).'%' );
			array_push( $sql_wheres , $this->dbh->bind( $SQL_WHERE_TPL , $bindData ) );
		}
		$SQL_WHERE = implode( ' AND ' , $sql_wheres );
		#	/ マッチング用のWHERE句を作成
		#--------------------------------------

		ob_start();?>
SELECT 
    atc.article_cd       AS article_cd       ,
    atc.category_cd      AS category_cd      ,
    atc.user_cd          AS user_cd          ,
    atc.article_title    AS article_title    ,
    atc.article_summary  AS article_summary  ,
    atc.status           AS status           ,
    atc.release_date     AS release_date     ,
    atc.update_date      AS update_date      ,
    atc.create_date      AS create_date      ,
    atc.del_flg          AS del_flg          ,
    sch.article_bodytext AS article_bodytext 
FROM :D:tableName_article atc
    LEFT JOIN :D:tableName_search sch ON atc.article_cd = sch.article_cd
WHERE
    :D:sql_where
    AND del_flg = 0
    AND status = 1
    AND release_date < :S:now -- >
ORDER BY atc.release_date DESC
:D:limit_string
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$limit_string = '';
		if( $this->conf->rdb['type'] == 'PostgreSQL' ){
			#	【 PostgreSQL 】
			$limit_string .= ' OFFSET '.intval($limit_offset).' LIMIT '.intval($limit_count);
		}else{
			#	【 MySQL/SQLite 】
			$limit_string .= ' LIMIT';
			$limit_string .= ' '.intval($limit_offset).',';
			$limit_string .= ' '.intval($limit_count);
		}

		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article' ,
			'tableName_search'=>$this->plog->table_name.'_search' ,
			'limit_string'=>$limit_string,
			'sql_where'=>$SQL_WHERE,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		return	$DATA;
	}

	#--------------------------------------
	#	記事検索のヒット数を取得する
	function search_article_count( $keyword , $option = array() ){
		$keyword = trim( $keyword );
		if( !strlen( $keyword ) ){ return 0; }

		#--------------------------------------
		#	マッチング用のWHERE句を作成
		$keywords = preg_split( '/(?: |　|\t|\r\n|\r|\n)+/' , $keyword );
		if( !count( $keywords ) ){ return 0; }
		$sql_wheres = array();
		$SQL_WHERE_TPL = '( lower( atc.article_title ) LIKE :S:keyword OR lower( atc.article_summary ) LIKE :S:keyword OR lower( sch.article_bodytext ) LIKE :S:keyword )';
		foreach( $keywords as $a_word ){
			$bindData = array( 'keyword'=>'%'.strtolower( $a_word ).'%' );
			array_push( $sql_wheres , $this->dbh->bind( $SQL_WHERE_TPL , $bindData ) );
		}
		$SQL_WHERE = implode( ' AND ' , $sql_wheres );
		#	/ マッチング用のWHERE句を作成
		#--------------------------------------

		ob_start();?>
SELECT count(*) AS count
FROM :D:tableName_article atc
	LEFT JOIN :D:tableName_search sch ON atc.article_cd = sch.article_cd
WHERE
	:D:sql_where
	AND atc.del_flg = 0
	AND atc.status = 1
	AND atc.release_date < :S:now -- >
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article' ,
			'tableName_search'=>$this->plog->table_name.'_search' ,
			'sql_where'=>$SQL_WHERE,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		$RTN = $DATA[0]['count'];
		return	intval($RTN);
	}


	#--------------------------------------
	#	次の記事を取得
	#	Updated : 20:23 2008/01/15 追加
	function get_next_article_info( $current_article_release_date = null , $is_next = true , $category_cd = null ){
		#	$current_article_release_date	->	基点とする記事の公開日(これを元に前(または後)の記事を取り出します)
		#	$is_next						->	欲しいのは次の記事(falseを渡すと前の記事になる)
		#	$category_cd					->	カテゴリを考慮すべき場合に、カテゴリCDを指定
		ob_start();?>
SELECT
	a.article_cd AS article_cd,
	a.user_cd AS user_cd,
	a.article_title AS article_title,
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

:D:next_or_prev
:D:category_cd_string
:D:orderby
:D:limit_string
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$limit_offset = 0;
		$limit_count = 1;
			#	↑1件だけ取ってくる。

		$limit_string = '';
		if( $this->conf->rdb['type'] == 'PostgreSQL' ){
			#	【 PostgreSQL 】
			$limit_string .= ' OFFSET '.intval($limit_offset).' LIMIT '.intval($limit_count);
		}else{
			#	【 MySQL/SQLite 】
			$limit_string .= ' LIMIT';
			$limit_string .= ' '.intval($limit_offset).',';
			$limit_string .= ' '.intval($limit_count);
		}

		#	カテゴリの指定がある場合は考慮する。
		$category_cd_string = '';
		if( strlen( $category_cd ) ){
			$category_cd_string = '	AND a.category_cd = '.intval( $category_cd );
		}

		#	基点とする記事の前と後
		$next_or_prev_string = '';
		$orderby_string = '';
		if( $is_next ){
			$next_or_prev_string = '	AND a.release_date > '.text::data2text( $current_article_release_date );
			$orderby_string = 'ORDER BY release_date'."\n";
		}else{
			$next_or_prev_string = '	AND a.release_date < '.text::data2text( $current_article_release_date );
			$orderby_string = 'ORDER BY release_date DESC'."\n";
		}


		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article',
			'tableName_category'=>$this->plog->table_name.'_category',
			'limit_string'=>$limit_string,
			'next_or_prev'=>$next_or_prev_string,
			'orderby'=>$orderby_string,
			'category_cd_string'=>$category_cd_string,
			'now'=>$this->dbh->int2datetime( $this->conf->time ),
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN[0];

	}

}

?>