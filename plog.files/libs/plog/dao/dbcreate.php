<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 23:51 2008/04/27

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_dbcreate ]
class cont_plog_dao_dbcreate{
	var $plog;
	var $px;

	/**
	 * コンストラクタ
	 */
	function __construct( &$plog ){
		$this->plog = &$plog;
		$this->px = &$plog->px;
	}//__construct()


	/**
	 * PLOGのテーブルを作成する
	 */
	public function create_tables( $exec_mode = null ){

		#--------------------------------------
		#	article: 記事マスタテーブル
		ob_start();?>
<?php if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){ ?>
CREATE TABLE :D:tableName(
    article_cd    SERIAL NOT NULL,
    category_cd    INT NOT NULL,
    user_id    VARCHAR NOT NULL,
    article_title    VARCHAR NOT NULL,
    article_summary    TEXT,
    status    INT2 NOT NULL DEFAULT '0',
    release_date    TIMESTAMP DEFAULT 'NOW',
    create_date    TIMESTAMP DEFAULT 'NOW',
    update_date    TIMESTAMP DEFAULT 'NOW',
    del_flg    INT2 NOT NULL DEFAULT '0'
);
<?php }elseif( $this->plog->px->get_conf('dbms.dbms') == 'sqlite' ){ ?>
CREATE TABLE :D:tableName(
    article_cd    INTEGER NOT NULL PRIMARY KEY,
    category_cd    INT(11) NOT NULL,
    user_id    VARCHAR(64) NOT NULL,
    article_title    VARCHAR(64) NOT NULL,
    article_summary    TEXT,
    status    INT(1) NOT NULL DEFAULT '0',
    release_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    create_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php }else{ ?>
CREATE TABLE :D:tableName(
    article_cd    INT(11) NOT NULL,
    category_cd    INT(11) NOT NULL,
    user_id    VARCHAR(64) NOT NULL,
    article_title    VARCHAR(64) NOT NULL,
    article_summary    TEXT,
    status    INT(1) NOT NULL DEFAULT '0',
    release_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    create_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php } ?>
<?php
		$sql['article'] = array();
		array_push( $sql['article'] , @ob_get_clean() );
		if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){
			#	PostgreSQL
			array_push( $sql['article'] , 'ALTER TABLE :D:tableName ADD PRIMARY KEY ( article_cd );' );
		}elseif( $this->plog->px->get_conf('dbms.dbms') == 'mysql' ){
			#	MySQL
			array_push( $sql['article'] , 'ALTER TABLE :D:tableName ADD PRIMARY KEY ( article_cd );' );
			array_push( $sql['article'] , 'ALTER TABLE :D:tableName CHANGE article_cd article_cd INT(11) NOT NULL AUTO_INCREMENT;' );
		}

		#--------------------------------------
		#	category: カテゴリマスタテーブル
		#	Updated : 17:08 2008/01/17
		#		user_cd を追加。ユーザ別の記事管理を想定。
		#			(ユーザに紐付けない場合、ユーザがファイル管理の場合は、ゼロを入れる)
		ob_start();?>
<?php if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){ ?>
CREATE TABLE :D:tableName(
    category_cd    SERIAL NOT NULL,
    user_id    VARCHAR NOT NULL,
    parent_category_cd    INT NOT NULL,
    category_title    VARCHAR NOT NULL,
    category_subtitle    TEXT,
    category_summary    TEXT,
    create_date    TIMESTAMP DEFAULT 'NOW',
    update_date    TIMESTAMP DEFAULT 'NOW',
    del_flg    INT2 NOT NULL DEFAULT '0'
);
<?php }elseif( $this->plog->px->get_conf('dbms.dbms') == 'sqlite' ){ ?>
CREATE TABLE :D:tableName(
    category_cd    INTEGER NOT NULL PRIMARY KEY,
    user_id    VARCHAR(64) NOT NULL,
    parent_category_cd    INT(11) NOT NULL,
    category_title    VARCHAR(64) NOT NULL,
    category_subtitle    TEXT,
    category_summary    TEXT,
    create_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php }else{ ?>
CREATE TABLE :D:tableName(
    category_cd    INT(11) NOT NULL,
    user_id    VARCHAR(64) NOT NULL,
    parent_category_cd    INT(11) NOT NULL,
    category_title    VARCHAR(64) NOT NULL,
    category_subtitle    TEXT,
    category_summary    TEXT,
    create_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php } ?>
<?php
		$sql['category'] = array();
		array_push( $sql['category'] , @ob_get_clean() );
		if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){
			#	PostgreSQL
			array_push( $sql['category'] , 'ALTER TABLE :D:tableName ADD PRIMARY KEY ( category_cd );' );
		}elseif( $this->plog->px->get_conf('dbms.dbms') == 'mysql' ){
			#	MySQL
			array_push( $sql['category'] , 'ALTER TABLE :D:tableName ADD PRIMARY KEY ( category_cd );' );
			array_push( $sql['category'] , 'ALTER TABLE :D:tableName CHANGE category_cd category_cd INT(11) NOT NULL AUTO_INCREMENT;' );
		}


		#--------------------------------------
		#	trackback: トラックバック受信テーブル
		ob_start();?>
<?php if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){ ?>
CREATE TABLE :D:tableName(
    article_cd    INT NOT NULL,
    keystr    VARCHAR NOT NULL,
    trackback_blog_name    VARCHAR NOT NULL,
    trackback_title    VARCHAR NOT NULL,
    trackback_url    TEXT,
    trackback_excerpt    TEXT,
    trackback_date    TIMESTAMP DEFAULT 'NOW',
    status    INT2 NOT NULL DEFAULT '0',
    client_ip    VARCHAR,
    create_date    TIMESTAMP DEFAULT 'NOW',
    update_date    TIMESTAMP DEFAULT 'NOW',
    del_flg    INT2 NOT NULL DEFAULT '0'
);
<?php }else{ ?>
CREATE TABLE :D:tableName(
    article_cd    INT(11) NOT NULL,
    keystr    VARCHAR(64) NOT NULL,
    trackback_blog_name    VARCHAR(64) NOT NULL,
    trackback_title    VARCHAR(64) NOT NULL,
    trackback_url    TEXT,
    trackback_excerpt    TEXT,
    trackback_date    DATETIME DEFAULT NULL,
    status    INT(1) NOT NULL DEFAULT '0',
    client_ip    VARCHAR(64),
    create_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php } ?>
<?php
		$sql['trackback'] = array();
		array_push( $sql['trackback'] , @ob_get_clean() );


		#--------------------------------------
		#	comment: 記事コメントテーブル
		#	Updated : 17:17 2007/12/06
		#		password を追加。投稿者が自分で削除できるようにするため。
		ob_start();?>
<?php if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){ ?>
CREATE TABLE :D:tableName(
    article_cd    INT NOT NULL,
    keystr    VARCHAR NOT NULL,
    comment    TEXT,
    commentator_name    VARCHAR NOT NULL,
    commentator_email    VARCHAR NOT NULL,
    commentator_url    VARCHAR NOT NULL,
    comment_date    TIMESTAMP DEFAULT 'NOW',
    status    INT2 NOT NULL DEFAULT '0',
    password    VARCHAR,
    client_ip    VARCHAR,
    create_date    TIMESTAMP DEFAULT 'NOW',
    update_date    TIMESTAMP DEFAULT 'NOW',
    del_flg    INT2 NOT NULL DEFAULT '0'
);
<?php }else{ ?>
CREATE TABLE :D:tableName(
    article_cd    INT(11) NOT NULL,
    keystr    VARCHAR(64) NOT NULL,
    comment    TEXT,
    commentator_name    VARCHAR(64) NOT NULL,
    commentator_email    VARCHAR(64) NOT NULL,
    commentator_url    VARCHAR(255) NOT NULL,
    comment_date    DATETIME DEFAULT NULL,
    status    INT(1) NOT NULL DEFAULT '0',
    password    VARCHAR(64),
    client_ip    VARCHAR(64),
    create_date    DATETIME DEFAULT NULL,
    update_date    DATETIME DEFAULT NULL,
    del_flg    INT(1) NOT NULL DEFAULT '0'
);
<?php } ?>
<?php
		$sql['comment'] = array();
		array_push( $sql['comment'] , @ob_get_clean() );

		#--------------------------------------
		#	search: 記事検索テーブル
		#	Created : 18:27 2008/03/30
		#	Updated : 18:27 2008/03/30
		ob_start();?>
<?php if( $this->plog->px->get_conf('dbms.dbms') == 'postgresql' ){ ?>
CREATE TABLE :D:tableName(
    article_cd    INT NOT NULL,
    article_bodytext    TEXT,
    update_date    TIMESTAMP DEFAULT 'NOW'
);
<?php }else{ ?>
CREATE TABLE :D:tableName(
    article_cd    INT(11) NOT NULL,
    article_bodytext    TEXT,
    update_date    DATETIME DEFAULT NULL
);
<?php } ?>
<?php
		$sql['search'] = array();
		array_push( $sql['search'] , @ob_get_clean() );



		#----------------------------------------------------------------------------
		#	テーブル別SQL一覧
		$targetTableNames = array();
		array_push( $targetTableNames , 'article' );
		array_push( $targetTableNames , 'category' );
		array_push( $targetTableNames , 'trackback' );
		array_push( $targetTableNames , 'comment' );
		array_push( $targetTableNames , 'search' );



		#	ダウンロード用に作成するソース
		$SQL4DOWNLOAD = $sqlComment['headerinfo']."\n";

		$this->px->dbh()->start_transaction();

		foreach( $targetTableNames as $tableName ){
			foreach( $sql[$tableName] as $sql_content ){
				$bindData = array(
					'tableName'=>$this->plog->table_name.'_'.$tableName,
				);
				$sqlFinal = $this->px->dbh()->bind( $sql_content , $bindData );
				if( !strlen( $sqlFinal ) ){ continue; }
				$SQL4DOWNLOAD .= $sqlFinal."\n";

				if( !strlen( $exec_mode ) ){
					#	$exec_modeが空白ならば、
					#	SQLを流すモード
					$this->px->dbh()->send_query( $sqlFinal );
				}
			}
		}

		if( !strlen( $exec_mode ) ){
			#	$exec_modeが空白ならば、
			#	SQLを流すモード
			$this->px->dbh()->commit();
		}elseif( $exec_mode == 'GET_SQL_SOURCE' ){
			return	$SQL4DOWNLOAD;
		}
		return	true;

	}

}

?>