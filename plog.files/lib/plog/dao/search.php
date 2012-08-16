<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 1:17 2008/03/20

#------------------------------------------------------------------------------------------------------------------
#	記事検索オブジェクトクラス [ cont_plog_dao_search ]
class cont_plog_dao_search{
	var $plogconf;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_search( &$plogconf ){
		$this->plogconf = &$plogconf;
		$this->conf = &$plogconf->get_basicobj_conf();
		$this->errors = &$plogconf->get_basicobj_errors();
		$this->dbh = &$plogconf->get_basicobj_dbh();
	}


	#--------------------------------------
	#	全記事のインデックスを更新する
	function update_all_index(){

		ob_start();?>
SELECT article_cd FROM :D:tableName
;
<?php
		$SELECT_SQL = @ob_get_clean();
		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_article' ,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$DATA = $this->dbh->getval();
		if( !is_array( $DATA ) ){
			return	false;
		}

		foreach( $DATA as $article_info ){
			$this->update_article_index( $article_info['article_cd'] );
		}

		return	true;
	}



	#--------------------------------------
	#	記事コンテンツのインデックスを更新する
	function update_article_index( $article_cd ){

		#	HTMLを取得
		$operator = $this->plogconf->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $article_cd );

		#--------------------------------------
		#	一旦削除
		ob_start();?>
DELETE FROM :D:tableName
WHERE article_cd = :N:article_cd
;
<?php
		$DELETE_SQL = @ob_get_clean();
		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_search' ,
			'article_cd'=>$article_cd ,
		);
		$DELETE_SQL = $this->dbh->bind( $DELETE_SQL , $bindData );
		$res = $this->dbh->sendquery( $DELETE_SQL );
		#	/ 一旦削除
		#--------------------------------------


		#--------------------------------------
		#	更新
		ob_start();?>
INSERT INTO :D:tableName (
	article_cd ,
	article_bodytext ,
	update_date
) VALUES (
	:N:article_cd ,
	:S:article_bodytext ,
	:S:now
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_search' ,
			'article_cd'=>$article_cd ,
			'article_bodytext'=>$this->mk_bodytext4search_by_html( $ARTICLE_BODY_SRC ) ,
			'now'=>date( 'Y-m-d H:i:s' ) ,
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );

		#	/更新
		#--------------------------------------

		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}

		$this->dbh->commit();

		return	true;
	}


	#--------------------------------------
	#	記事コンテンツのHTMLから、検索用文字列を生成する
	function mk_bodytext4search_by_html( $SRC_HTML ){
		$SRC_HTML = strip_tags( $SRC_HTML );
		$SRC_HTML = trim( $SRC_HTML );
		$SRC_HTML = preg_replace( '/(\r\n|\r|\n)\t+/is' , '$1' , $SRC_HTML );
		$SRC_HTML = preg_replace( '/(?:\r\n|\r|\n)+/is' , " " , $SRC_HTML );
		return	$SRC_HTML;
	}



}


?>