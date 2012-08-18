<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 12:16 2010/08/09

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_trackback ]
class cont_plog_dao_trackback{
	var $plog;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_trackback( &$plog ){
		$this->plog = &$plog;
		$this->conf = &$plog->get_basicobj_conf();
		$this->errors = &$plog->get_basicobj_errors();
		$this->dbh = &$plog->get_basicobj_dbh();
	}


	#--------------------------------------
	#	トラックバックの一覧を取得
	function get_trackback_list( $article_cd , $limit_offset = 0 , $limit_count = 100 ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND status = 1
	AND del_flg = 0
ORDER BY trackback_date
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
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN;

	}


	#--------------------------------------
	#	トラックバックの一覧を全て取得
	function get_trackback_alllist( $article_cd , $limit_offset = 0 , $limit_count = 100 ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND del_flg = 0
ORDER BY trackback_date
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
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN;

	}

	#--------------------------------------
	#	コメントの新着リストを取得
	function get_new_trackbacks( $count ){
		if( !is_int( $count ) ){ $count = 5; }
		$count = intval( $count );
		if( !$count ){ return array(); }

		ob_start();?>
SELECT
	atc.article_cd ,
	atc.article_title ,
	atc.article_summary ,
	atc.release_date ,
	atc.category_cd ,
	tb.trackback_blog_name ,
	tb.trackback_title ,
	tb.trackback_excerpt ,
	tb.trackback_url ,
	tb.keystr ,
	tb.trackback_date
FROM :D:tableName_trackback tb
	LEFT JOIN :D:tableName_article atc ON tb.article_cd = atc.article_cd
WHERE
	tb.del_flg = 0
	AND tb.status = 1
ORDER BY tb.trackback_date DESC
LIMIT :N:limit
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_trackback'=>$this->plog->table_name.'_trackback',
			'tableName_article'=>$this->plog->table_name.'_article',
			'limit'=>$count,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		return	$DATA;

	}


	#--------------------------------------
	#	トラックバックの情報を取得
	function get_trackback_info( $article_cd , $keystr ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND del_flg = 0
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN[0];

	}


	#--------------------------------------
	#	トラックバックのステータスを上書き
	function update_trackback_status( $article_cd , $keystr , $trackback_url , $status ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }

		ob_start();?>
UPDATE :D:tableName
SET
	status = :N:status ,
	update_date = :S:now
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND trackback_url = :S:trackback_url
	AND del_flg = 0
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
			'trackback_url'=>$trackback_url,
			'status'=>$status,
			'now'=>$this->dbh->int2datetime( time() ),
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		return	true;

	}

	#--------------------------------------
	#	トラックバックを追加する
	function insert_trackbackping( $article_cd , $url , $title = '' , $blog_name = '' , $excerpt = '' , $client_ip = '' , $status = 0 ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }

		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$article_info = $dao_visitor->get_article_info( $article_cd );
		if( !is_array( $article_info ) || !count( $article_info ) ){
			#	対象の記事が存在しません。
			return	false;
		}

		#--------------------------------------
		#	エントリの重複チェック
		ob_start();?>
SELECT count(*) AS count FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND trackback_url = :S:trackback_url
	AND del_flg = 0
;
<?php
		$SELECT_SQL = @ob_get_clean();
		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'trackback_url'=>$url,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$count = $this->dbh->getval();
		if( intval( $count[0]['count'] ) ){
			#	すでに登録されている。
			return	false;
		}
		#	/ エントリの重複チェック
		#--------------------------------------

		ob_start();?>
INSERT INTO :D:tableName(
	article_cd ,
	keystr ,
	trackback_blog_name ,
	trackback_title ,
	trackback_url ,
	trackback_excerpt ,
	client_ip ,
	status ,
	trackback_date ,
	create_date ,
	update_date
) VALUES (
	:N:article_cd ,
	:S:keystr ,
	:S:trackback_blog_name ,
	:S:trackback_title ,
	:S:trackback_url ,
	:S:trackback_excerpt ,
	:S:client_ip ,
	:N:status ,
	:S:now ,
	:S:now ,
	:S:now
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback' ,
			'article_cd'=>$article_cd ,
			'keystr'=>md5( time::microtime() ) ,
			'trackback_blog_name'=>$blog_name ,
			'trackback_title'=>$title ,
			'trackback_url'=>$url ,
			'trackback_excerpt'=>$excerpt ,
			'client_ip'=>$client_ip ,
			'status'=>$status ,
			'now'=>$this->dbh->int2datetime( time() ) ,
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		if( strlen( $this->plog->reportmail_to ) ){
			#--------------------------------------
			#	レポートメールを送信する (PLOG 0.1.6 追加)
			$className = $this->dbh->require_lib( '/resources/mail.php' );
			if( !$className ){
				$this->errors->error_log( '/resources/mail.php のロードに失敗しました。レポートメールの送信に失敗しました。' , __FILE__ , __LINE__ );
				return true;
			}
			$mail = new $className( &$this->conf , &$this->errors );
			$mail->setsubject( '【'.$this->plog->blog_name.'】[記事'.$article_cd.']トラックバックが投稿されました。' );
			$mail->setbody(
				 ''.$this->plog->blog_name.' の記事 '.$article_cd.' に、'."\n"
				.'トラックバックが投稿されました。'."\n"
				.$this->plog->get_article_url($article_cd,'admin')."\n"
				."\n"
				.'Blog name: '.$blog_name."\n"
				.'Title: '.$title."\n"
				.'URL: '.$url."\n"
				.'excerpt: '.$excerpt."\n"
			);
			$mail->putto( $this->plog->reportmail_to );
			$mail->setfrom( $this->plog->reportmail_to );

			if( !$mail->send() ){//メール送信
				$this->errors->error_log( 'レポートメールの送信に失敗しました。' , __FILE__ , __LINE__ );
				return true;
			}
			#	/ レポートメールを送信する
			#--------------------------------------
		}

		return	true;

	}





	#--------------------------------------
	#	トラックバックを削除
	function delete_trackback( $article_cd , $keystr , $trackback_url ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }

		ob_start();?>
UPDATE :D:tableName
SET
	del_flg = 1 ,
	update_date = :S:now
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND trackback_url = :S:trackback_url
	AND del_flg = 0
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plog->table_name.'_trackback',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
			'trackback_url'=>$trackback_url,
			'now'=>$this->dbh->int2datetime( time() ),
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		return	true;

	}



	#--------------------------------------
	#	TrackbackPingの送信ログを保存する
	function tbp_sendlog( $tbp_sendlog = array() ){
		if( !is_array( $tbp_sendlog ) || !count( $tbp_sendlog ) ){
			return	false;
		}

		$path_tbp_log_dir = $this->plog->get_send_tbp_log_dir();
		if( $path_tbp_log_dir === false ){
			return	false;
		}
		if( !is_dir( $path_tbp_log_dir ) ){
			return	false;
		}

		$log_file_name = 'tbp_sent_'.date('Ym').'.log';

		$path = $path_tbp_log_dir.'/'.$log_file_name;

		#	ログの記録を開始
		foreach( $tbp_sendlog as $log_line ){
			if( is_array( $log_line['error_messages'] ) ){
				$log_line['error_messages'] = implode( ' / ' , $log_line['error_messages'] );
			}
			$ROW = '';
			$ROW .= date('Y-m-d H:i:s');
			$ROW .= '	'.$log_line['article_cd'];
			$ROW .= '	'.$log_line['tbp_uri'];
			$ROW .= '	'.intval( $log_line['error_count'] );
			$ROW .= '	'.$log_line['error_messages'];
			$result = @error_log( $ROW."\n" , 3 , $path );
			if( !$result ){
				$this->errors->error_log( 'FAILD to save log. - '.preg_replace( '/\t/' , ' ' , $ROW ) , __FILE__ , __LINE__ );
			}
		}

		$this->dbh->chmod( $path );

		return	true;
	}

}

?>