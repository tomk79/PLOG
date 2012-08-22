<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 23:21 2010/07/04

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_admin ]
class cont_plog_dao_admin{
	var $plog;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_admin( &$plog ){
		$this->plog = &$plog;
		$this->conf = &$plog->get_basicobj_conf();
		$this->errors = &$plog->get_basicobj_errors();
		$this->dbh = &$plog->get_basicobj_dbh();
	}



	#--------------------------------------
	#	記事一覧を取得
	function get_article_list( $category_cd = null , $limit_offset = 0 , $limit_count = 10 ){
		ob_start();?>
SELECT * FROM :D:tableName
WHERE del_flg = 0
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

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_article',
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$RTN = $this->px->dbh()->get_results();

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
			'now'=>$this->px->dbh()->int2datetime(time()),
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$RTN = $this->px->dbh()->get_results();

		return	intval( $RTN[0]['count'] );

	}


	#--------------------------------------
	#	記事情報を取得
	function get_article_info( $article_cd ){
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
	a.article_cd = :N:article_cd
	AND a.del_flg = 0
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article',
			'tableName_category'=>$this->plog->table_name.'_category',
			'article_cd'=>$article_cd,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$RTN = $this->px->dbh()->get_results();

		return	$RTN[0];

	}



	#--------------------------------------
	#	新規記事を作成する
	function create_article( $article_title , $status , $contents , $ary_options = array() ){
		if( !strlen( $ary_options['release_date'] ) ){
			$ary_options['release_date'] = date( 'Y-m-d H:i:s' );
		}

		ob_start();?>
INSERT INTO :D:tableName(
	article_title ,
	user_cd ,
	article_summary ,
	status ,
	category_cd ,
	release_date ,
	create_date ,
	update_date
) VALUES (
	:S:article_title ,
	:N:user_cd ,
	:S:article_summary ,
	:N:status ,
	:N:category_cd ,
	:S:release_date ,
	:S:now ,
	:S:now
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_article',
			'article_title'=>$article_title,
			'article_summary'=>$ary_options['article_summary'],
			'user_cd'=>intval( $ary_options['user_cd'] ),
			'status'=>$status,
			'category_cd'=>$ary_options['category_cd'],
			'release_date'=>$ary_options['release_date'],
			'now'=>date( 'Y-m-d H:i:s' ),
		);
		$INSERT_SQL = $this->px->dbh()->bind( $INSERT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $INSERT_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();

		$article_cd = $this->px->dbh()->get_last_insert_id( null , $this->plog->table_name.'_article'.'_article_cd_seq' );//挿入された行のIDを取得
		if( !strlen( $article_cd ) ){
			return	false;
		}

		#--------------------------------------
		#	コンテンツファイルの保存
		$base_path = $this->plog->get_article_dir( $article_cd );
		if( !is_dir( $base_path ) ){
			if( !$this->px->dbh()->mkdirall( $base_path ) ){
				return	false;
			}
		}

		if( !$this->px->dbh()->file_overwrite( $base_path.'/contents.txt' , $contents ) ){//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
			#	コンテンツファイルの保存に失敗
			return	false;
		}
		#	/ コンテンツファイルの保存
		#--------------------------------------

		return	intval( $article_cd );

	}


	#--------------------------------------
	#	既存記事を更新する
	function update_article( $article_cd , $article_title , $status , $contents , $ary_options = array() ){
		if( !strlen( $ary_options['release_date'] ) ){
			$ary_options['release_date'] = date( 'Y-m-d H:i:s' );
		}

		ob_start();?>
UPDATE :D:tableName SET
	article_title = :S:article_title ,
	article_summary = :S:article_summary ,
	status = :N:status ,
	category_cd = :N:category_cd ,
	release_date = :S:release_date ,
	update_date = :S:now
WHERE article_cd = :N:article_cd
;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_article',
			'article_cd'=>$article_cd,
			'article_title'=>$article_title,
			'article_summary'=>$ary_options['article_summary'],
			'status'=>$status,
			'category_cd'=>$ary_options['category_cd'],
			'release_date'=>$ary_options['release_date'],
			'now'=>date( 'Y-m-d H:i:s' ),
		);
		$UPDATE_SQL = $this->px->dbh()->bind( $UPDATE_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $UPDATE_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();

		#--------------------------------------
		#	コンテンツファイルの保存
		$base_path = $this->plog->get_article_dir( $article_cd );
		if( !is_dir( $base_path ) ){
			if( !$this->px->dbh()->mkdirall( $base_path ) ){
				return	false;
			}
		}

		if( !$this->px->dbh()->file_overwrite( $base_path.'/contents.txt' , $contents ) ){//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
			#	コンテンツファイルの保存に失敗
			return	false;
		}
		#	/ コンテンツファイルの保存
		#--------------------------------------

		return	intval( $article_cd );

	}


	#--------------------------------------
	#	記事コンテンツのインデックスを更新する
	function update_article_index( $article_cd ){

		#	HTMLを取得
		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $article_cd );

		if( $this->plog->article_summary_mode == 'auto' ){
			#	記事サマリを自動的に作成するモードだった場合。
			$article_summary = $operator->mk_summary_by_html( $ARTICLE_BODY_SRC );
			$SQLPARTS_SUMMARY = $this->px->dbh()->bind( '	article_summary = :S:article_summary ,' , array('article_summary'=>$article_summary) );

			ob_start();?>
UPDATE :D:tableName SET
:D:article_summary
	update_date = :S:now
WHERE article_cd = :N:article_cd
;
<?php
			$UPDATE_SQL = @ob_get_clean();

			$bindData = array(
				'tableName'=>$this->plog->table_name.'_article',
				'article_cd'=>$article_cd,
				'article_summary'=>$SQLPARTS_SUMMARY,
				'now'=>date( 'Y-m-d H:i:s' ),
			);
			$UPDATE_SQL = $this->px->dbh()->bind( $UPDATE_SQL , $bindData );
			$res = $this->px->dbh()->send_query( $UPDATE_SQL );

			if( !$res ){
				$this->px->dbh()->rollback();
				return	false;
			}
			$this->px->dbh()->commit();

		}

		#--------------------------------------
		#	記事検索テーブルの値を更新
		$dao_search = $this->plog->factory_dao( 'search' );
		$dao_search->update_article_index( $article_cd );

		return	true;
	}


	#--------------------------------------
	#	記事コンテンツのソースを得る
	function get_contents_src( $article_cd ){
		$base_path = $this->plog->get_article_dir( $article_cd );
		if( !is_dir( $base_path ) ){
			return	false;
		}

		return	$this->px->dbh()->file_get_contents( $base_path.'/contents.txt' );
	}

	#--------------------------------------
	#	記事コンテンツに紐付く画像一覧を得る
	function get_contents_image_list( $article_cd ){
		$base_path = $this->plog->get_article_dir( $article_cd ).'/images';
		if( !is_dir( $base_path ) ){
			return	false;
		}

		return	$this->px->dbh()->getfilelist( $base_path );
	}

	#--------------------------------------
	#	記事コンテンツの画像を開く
	function load_contents_image( $article_cd , $image_name ){
		$base_path = $this->plog->get_article_dir( $article_cd ).'/images/'.$image_name;
		if( !is_file( $base_path ) ){
			return	false;
		}

		return	$this->px->dbh()->file_get_contents( $base_path );
	}

	#--------------------------------------
	#	記事コンテンツの画像を保存する
	function save_contents_image( $article_cd , $image_name , $bin ){
		$base_path = $this->plog->get_article_dir( $article_cd ).'/images';
		if( !is_dir( $base_path ) ){
			if( !$this->px->dbh()->mkdirall( $base_path ) ){
				return	false;
			}
		}

		return	$this->px->dbh()->file_overwrite( $base_path.'/'.$image_name , $bin );//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
	}

	#--------------------------------------
	#	記事コンテンツの画像を削除する
	function delete_contents_image( $article_cd , $image_name ){
		$base_path = $this->plog->get_article_dir( $article_cd ).'/images';
		if( !is_file( $base_path.'/'.$image_name ) ){
			return	true;
		}
		return	$this->px->dbh()->rmdir( $base_path.'/'.$image_name );
	}




	#--------------------------------------
	#	カテゴリの一覧を得る
	function get_category_list(){
		ob_start();?>
SELECT *
FROM :D:tableName
ORDER BY category_title
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		return	$DATA;
	}

	#--------------------------------------
	#	カテゴリの階層構造を捨て、全てフラットに並べる
	function make_all_categories_flat(){
		ob_start();?>
UPDATE :D:tableName SET parent_category_cd = :N:parent_category_cd;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
			'parent_category_cd'=>0,
		);
		$UPDATE_SQL = $this->px->dbh()->bind( $UPDATE_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $UPDATE_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();

		return	true;
	}

	#--------------------------------------
	#	カテゴリ情報を得る
	function get_category_info( $category_cd ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE category_cd = :N:category_cd
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category' ,
			'category_cd'=>$category_cd ,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		return	$DATA[0];
	}


	#--------------------------------------
	#	新規カテゴリを作成する
	function create_category( $category_title , $category_subtitle , $category_summary , $parent_category_cd = 0 , $ary_options = array() ){
		$parent_category_cd = intval($parent_category_cd);
		if( !strlen( $ary_options['release_date'] ) ){
			$ary_options['release_date'] = date( 'Y-m-d H:i:s' );
		}

		ob_start();?>
INSERT INTO :D:tableName(
	category_title ,
	user_cd ,
	category_subtitle ,
	category_summary ,
	parent_category_cd ,
	create_date ,
	update_date
) VALUES (
	:S:category_title ,
	:N:user_cd ,
	:S:category_subtitle ,
	:S:category_summary ,
	:N:parent_category_cd ,
	:S:now ,
	:S:now
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
			'category_title'=>$category_title,
			'user_cd'=>intval($ary_options['user_cd']),
			'category_subtitle'=>$category_subtitle,
			'category_summary'=>$category_summary,
			'parent_category_cd'=>$parent_category_cd,
			'now'=>date( 'Y-m-d H:i:s' ),
		);
		$INSERT_SQL = $this->px->dbh()->bind( $INSERT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $INSERT_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();

		$category_cd = $this->px->dbh()->get_last_insert_id( null , $this->plog->table_name.'_category'.'_category_cd_seq' );//挿入された行のIDを取得
		if( !strlen( $category_cd ) ){
			return	false;
		}

		return	intval( $category_cd );

	}



	#--------------------------------------
	#	カテゴリを更新する
	function update_category( $category_cd , $category_title , $category_subtitle , $category_summary , $parent_category_cd = 0 , $ary_options = array() ){
		$parent_category_cd = intval($parent_category_cd);
		if( !strlen( $ary_options['release_date'] ) ){
			$ary_options['release_date'] = date( 'Y-m-d H:i:s' );
		}

		ob_start();?>
UPDATE :D:tableName SET
	category_title = :S:category_title ,
	category_subtitle = :S:category_subtitle ,
	category_summary = :S:category_summary ,
	parent_category_cd = :N:parent_category_cd ,
	update_date = :S:now
WHERE category_cd = :N:category_cd
;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_category',
			'category_cd'=>$category_cd,
			'category_title'=>$category_title,
			'category_subtitle'=>$category_subtitle,
			'category_summary'=>$category_summary,
			'parent_category_cd'=>$parent_category_cd,
			'now'=>date( 'Y-m-d H:i:s' ),
		);
		$UPDATE_SQL = $this->px->dbh()->bind( $UPDATE_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $UPDATE_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();


		return	intval( $category_cd );

	}







	#--------------------------------------
	#	承認待ちのコメントの一覧を得る
	function get_req_comment_list( $limit_offset = 0 , $limit_count = 100 ){

		ob_start();?>
SELECT
	a.article_cd,
	a.article_title,
	a.user_cd,
	c.keystr,
	c.comment,
	c.commentator_name,
	c.commentator_email,
	c.commentator_url,
	c.comment_date,
	c.status,
	c.create_date
FROM :D:tableName_article a, :D:tableName_comment c
WHERE
	a.article_cd = c.article_cd
	AND c.status = 0
	AND c.del_flg = 0
	AND a.del_flg = 0
ORDER BY c.comment_date
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
			'tableName_comment'=>$this->plog->table_name.'_comment' ,
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		return	$DATA;
	}



	#--------------------------------------
	#	承認待ちのトラックバックの一覧を得る
	function get_req_trackback_list( $limit_offset = 0 , $limit_count = 100 ){

		ob_start();?>
SELECT
	a.article_cd,
	a.article_title,
	a.user_cd,
	t.keystr,
	t.trackback_title,
	t.trackback_url,
	t.trackback_excerpt,
	t.trackback_date,
	t.status,
	t.create_date
FROM :D:tableName_article a, :D:tableName_trackback t
WHERE
	a.article_cd = t.article_cd
	AND t.status = 0
	AND t.del_flg = 0
	AND a.del_flg = 0
ORDER BY t.trackback_date
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
			'tableName_trackback'=>$this->plog->table_name.'_trackback' ,
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		return	$DATA;
	}


	#--------------------------------------
	#	記事を削除する(論理削除)
	function delete_article( $article_cd ){

		ob_start();?>
UPDATE :D:tableName SET
	del_flg = 1 ,
	update_date = :S:now
WHERE article_cd = :N:article_cd
;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_article',
			'article_cd'=>$article_cd,
			'now'=>$this->px->dbh()->int2datetime( time() ),
		);
		$UPDATE_SQL = $this->px->dbh()->bind( $UPDATE_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $UPDATE_SQL );

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}
		$this->px->dbh()->commit();


		return	true;
	}





	#--------------------------------------
	#	記事を検索する(管理画面専用)
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
			array_push( $sql_wheres , $this->px->dbh()->bind( $SQL_WHERE_TPL , $bindData ) );
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
			'keyword'=>'%'.$keyword.'%' ,
			'limit_string'=>$limit_string,
			'sql_where'=>$SQL_WHERE,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		return	$DATA;
	}



	#--------------------------------------
	#	記事検索のヒット数を取得する(管理画面専用)
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
			array_push( $sql_wheres , $this->px->dbh()->bind( $SQL_WHERE_TPL , $bindData ) );
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
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_article'=>$this->plog->table_name.'_article' ,
			'tableName_search'=>$this->plog->table_name.'_search' ,
			'keyword'=>'%'.$keyword.'%' ,
			'sql_where'=>$SQL_WHERE,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();

		$RTN = $DATA[0]['count'];
		return	intval($RTN);
	}

}

?>