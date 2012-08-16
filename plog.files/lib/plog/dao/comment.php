<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 12:10 2010/08/09

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_comment ]
class cont_plog_dao_comment{
	var $plogconf;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_comment( &$plogconf ){
		$this->plogconf = &$plogconf;
		$this->conf = &$plogconf->get_basicobj_conf();
		$this->errors = &$plogconf->get_basicobj_errors();
		$this->dbh = &$plogconf->get_basicobj_dbh();
	}





	#--------------------------------------
	#	コメントの一覧を取得
	function get_comment_list( $article_cd , $limit_offset = 0 , $limit_count = 100 ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND status = 1
	AND del_flg = 0
ORDER BY comment_date
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
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'limit_string'=>$limit_string,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN;

	}

	#--------------------------------------
	#	コメントの一覧を全て取得
	function get_comment_alllist( $article_cd , $limit_offset = 0 , $limit_count = 100 ){
		ob_start();?>
SELECT *
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND del_flg = 0
ORDER BY comment_date
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
			'tableName'=>$this->plogconf->table_name.'_comment',
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
	function get_new_comments( $count ){
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
	cmt.commentator_name ,
	cmt.commentator_email ,
	cmt.commentator_url ,
	cmt.keystr ,
	cmt.comment ,
	cmt.comment_date
FROM :D:tableName_comment cmt
	LEFT JOIN :D:tableName_article atc ON cmt.article_cd = atc.article_cd
WHERE
	cmt.del_flg = 0
	AND cmt.status = 1
ORDER BY cmt.comment_date DESC
LIMIT :N:limit
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName_comment'=>$this->plogconf->table_name.'_comment',
			'tableName_article'=>$this->plogconf->table_name.'_article',
			'limit'=>$count,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();

		return	$DATA;

	}

	#--------------------------------------
	#	コメントの情報を取得
	function get_comment_info( $article_cd , $keystr ){
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
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		return	$RTN[0];

	}

	#--------------------------------------
	#	コメントのステータスを上書き
	function update_comment_status( $article_cd , $keystr , $create_date , $client_ip , $status ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }

		ob_start();?>
UPDATE :D:tableName
SET
	status = :N:status ,
	update_date = :S:now
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND create_date = :S:create_date
	AND client_ip = :S:client_ip
	AND del_flg = 0
;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
			'create_date'=>$create_date,
			'client_ip'=>$client_ip,
			'status'=>$status,
			'now'=>$this->dbh->int2datetime( time() ),
		);
		$UPDATE_SQL = $this->dbh->bind( $UPDATE_SQL , $bindData );
		$res = $this->dbh->sendquery( $UPDATE_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		return	true;

	}

	#--------------------------------------
	#	コメントを追加する
	function add_comment( $article_cd , $comment , $commentator_name = '' , $commentator_email = '' , $commentator_url = '' , $password = '' , $client_ip = '' , $status = 0 ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }
		if( !$this->is_client_contributable( $client_ip , $article_cd ) ){
			#	投稿してもよいクライアントかどうかチェックしてもらう。
			#	チェックしてダメなら、この先の工程には進ませない。
			return false;
		}

		ob_start();?>
INSERT INTO :D:tableName(
	article_cd ,
	keystr ,
	comment ,
	commentator_name ,
	commentator_email ,
	commentator_url ,
	client_ip ,
	status ,
	password ,
	comment_date ,
	create_date ,
	update_date
) VALUES (
	:N:article_cd ,
	:S:keystr ,
	:S:comment ,
	:S:commentator_name ,
	:S:commentator_email ,
	:S:commentator_url ,
	:S:client_ip ,
	:N:status ,
	:S:password ,
	:S:now ,
	:S:now ,
	:S:now
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'keystr'=>md5( time::microtime() ),
			'comment'=>$comment,
			'commentator_name'=>$commentator_name,
			'commentator_email'=>$commentator_email,
			'commentator_url'=>$commentator_url,
			'client_ip'=>$client_ip,
			'status'=>$status,
			'password'=>$password,
			'now'=>$this->dbh->int2datetime( time() ),
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		if( strlen( $this->plogconf->reportmail_to ) ){
			#--------------------------------------
			#	レポートメールを送信する (PLOG 0.1.6 追加)
			$className = $this->dbh->require_lib( '/resources/mail.php' );
			if( !$className ){
				$this->errors->error_log( '/resources/mail.php のロードに失敗しました。レポートメールの送信に失敗しました。' , __FILE__ , __LINE__ );
				return true;
			}
			$mail = new $className( &$this->conf , &$this->errors );
			$mail->setsubject( '【'.$this->plogconf->blog_name.'】[記事'.$article_cd.']投稿が送信されました。' );
			$mail->setbody(
				 ''.$this->plogconf->blog_name.' の記事 '.$article_cd.' に、'."\n"
				.'コメントが投稿されました。'."\n"
				.$this->plogconf->get_article_url($article_cd,'admin')."\n"
				."\n"
				.'From: '.$commentator_name."\n"
				.'Email: '.$commentator_email."\n"
				.'URL: '.$commentator_url."\n"
				.'┏---------------------------------------┓'."\n"
				.$comment."\n"
				.'┗---------------------------------------┛'."\n"
				."\n"
				.'Client IP: '.$client_ip."\n"
			);
			$mail->putto( $this->plogconf->reportmail_to );
			$mail->setfrom( $this->plogconf->reportmail_to );

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
	#	重複投稿をチェックする
	function is_client_contributable( $client_ip , $article_cd ){
		#	機械的な連続投稿(嫌がらせ)をはじくためのチェック。
		if( !strlen( $article_cd ) || $article_cd != intval( $article_cd ) ){ return false; }
		if( !strlen( $client_ip ) ){ return false; }

		$locked_error_message = 'コメントの投稿をロックにより拒否しました。client_ip = ['.$client_ip.'] article_cd = ['.$article_cd.']';

		$path_lockfile_dir = $this->plogconf->path_home_dir.'/lockfiles/comment_contribute_lock';
		if( !is_dir( $path_lockfile_dir ) ){
			if( !$this->dbh->mkdirall( $path_lockfile_dir ) ){
				#	ディレクトリを作れません。
				$this->errors->error_log( 'FAILED to create directory ['.$path_lockfile_dir.'].' , __FILE__ , __LINE__ );
				return	false;
			}
		}
		$path_lockfile = $path_lockfile_dir.'/'.$client_ip.'.lock';

		#--------------------------------------
		#	ロックファイルの確認
		if( is_file( $path_lockfile ) ){
			#	既にロックされていた場合
			if( filemtime( $path_lockfile ) >= time() - (60*60*6) ){
				#	現在時刻よりも 6時間 以内であれば、NGとする
				$this->errors->error_log( $locked_error_message , __FILE__ , __LINE__ );
				return	false;
			}
		}
		#	/ ロックファイルの確認
		#--------------------------------------

		ob_start();?>
SELECT count(*) as count
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND client_ip = :S:client_ip
	AND comment_date > :S:timelimit
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'client_ip'=>$client_ip,
			'timelimit'=>$this->dbh->int2datetime( time()-60 ),//60秒以内で計測(仮)
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		if( !$res ){
			return	false;
		}
		$RTN = $this->dbh->getval();

		if( intval( $RTN[0]['count'] ) >= 6 ){
			#	もし抵触するようならば、
			#	ロックファイルを作成して終了
			touch( $path_lockfile );
			$this->errors->error_log( $locked_error_message , __FILE__ , __LINE__ );
			return	false;
		}

		#	ここまででチェック項目は終了です。
		#	もしOKなら、ロックファイルは消しましょう。
		if( is_file( $path_lockfile ) ){
			unlink( $path_lockfile );
		}

		return	true;

	}


	#--------------------------------------
	#	コメントを削除する
	function delete_comment( $article_cd , $keystr ){
		if( !strlen($article_cd) || $article_cd != intval($article_cd) ){ return false; }

		ob_start();?>
UPDATE :D:tableName
SET
	del_flg = 1 ,
	update_date = :S:now
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND del_flg = 0
;
<?php
		$UPDATE_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
			'now'=>$this->dbh->int2datetime( time() ),
		);
		$UPDATE_SQL = $this->dbh->bind( $UPDATE_SQL , $bindData );
		$res = $this->dbh->sendquery( $UPDATE_SQL );
		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		return	true;

	}


	#--------------------------------------
	#	コメントの削除用パスワードをチェックする
	function check_password( $article_cd , $keystr , $password ){
		ob_start();?>
SELECT password
FROM :D:tableName
WHERE
	article_cd = :N:article_cd
	AND keystr = :S:keystr
	AND del_flg = 0
;
<?php
		$SELECT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_comment',
			'article_cd'=>$article_cd,
			'keystr'=>$keystr,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );

		$res = $this->dbh->sendquery( $SELECT_SQL );
		$gotValues = $this->dbh->getval();

		if( $gotValues[0]['password'] == $password ){
			#	照合の結果、OKだったら true を返す。
			return	true;
		}

		return	false;

	}



	#--------------------------------------
	#	コメント文字列を出力可能なHTMLに変換する
	function view_comment2html( $txt_comment ){
		$html_comment_src = $txt_comment;
		$html_comment_src = htmlspecialchars( $html_comment_src );
		$html_comment_src = preg_replace( '/\r\n|\r|\n/' , '<br />' , $html_comment_src );
		$html_comment_src = preg_replace( '/(https?:\/\/[a-zA-Z0-9\_\-\/\:\;\?\=#\.@\+\~\%\&]+)/is' , '<a href="$1" onclick="window.open(this.href,\'_blank\');return false;">$1</a>' , $html_comment_src );
		return	$html_comment_src;
	}


}


?>