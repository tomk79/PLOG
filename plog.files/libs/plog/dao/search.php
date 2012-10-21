<?php

/**
 * 記事検索オブジェクトクラス [ cont_plog_dao_search ]
 * PxFW - Content - [PLOG]
 * (C)Tomoya Koyanagi
 */
class cont_plog_dao_search{
	private $plog;
	private $px;

	/**
	 * コンストラクタ
	 */
	public function __construct( $plog ){
		$this->plog = $plog;
		$this->px = $plog->px;
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
			'tableName'=>$this->plog->table_name.'_article' ,
		);
		$SELECT_SQL = $this->px->dbh()->bind( $SELECT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $SELECT_SQL );
		$DATA = $this->px->dbh()->get_results();
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
		$operator = $this->plog->factory_articleparser();
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
			'tableName'=>$this->plog->table_name.'_search' ,
			'article_cd'=>$article_cd ,
		);
		$DELETE_SQL = $this->px->dbh()->bind( $DELETE_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $DELETE_SQL );
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
			'tableName'=>$this->plog->table_name.'_search' ,
			'article_cd'=>$article_cd ,
			'article_bodytext'=>$this->mk_bodytext4search_by_html( $ARTICLE_BODY_SRC ) ,
			'now'=>date( 'Y-m-d H:i:s' ) ,
		);
		$INSERT_SQL = $this->px->dbh()->bind( $INSERT_SQL , $bindData );
		$res = $this->px->dbh()->send_query( $INSERT_SQL );

		#	/更新
		#--------------------------------------

		if( !$res ){
			$this->px->dbh()->rollback();
			return	false;
		}

		$this->px->dbh()->commit();

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