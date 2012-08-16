<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 21:15 2008/10/06

#	インポート系：anch_import_functions
#	エクスポート系：anch_export_functions
#	その他：anch_other_functions

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_dao_io ]
class cont_plog_dao_io{
	var $plogconf;
	var $conf;
	var $errors;
	var $dbh;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_dao_io( &$plogconf ){
		$this->plogconf = &$plogconf;
		$this->conf = &$plogconf->get_basicobj_conf();
		$this->errors = &$plogconf->get_basicobj_errors();
		$this->dbh = &$plogconf->get_basicobj_dbh();
	}


	###################################################################################################################
	#	エクスポート系機能
	#	Anchor: anch_export_functions

	#--------------------------------------
	#	内部記事データを出力する
	function export( $export_tmp_dir , $option = array() ){
		if( !strlen( $export_tmp_dir ) ){
			return	false;
		}elseif( !is_dir( $export_tmp_dir ) ){
			return	false;
		}
		if( !$this->enable_zip() ){
			return	false;
		}

		if( is_dir( $export_tmp_dir.'/tmp' ) ){
			if( !$this->dbh->rmdir( $export_tmp_dir.'/tmp' ) ){
				#	一時ディレクトリを一旦削除
				return	false;
			}
		}
		if( !$this->dbh->mkdir( $export_tmp_dir.'/tmp' ) ){
			#	一時ディレクトリを作成
			return	false;
		}

		#--------------------------------------
		#	記事テーブルを出力
		$this->export_table( $export_tmp_dir , 'article' );

		#--------------------------------------
		#	カテゴリテーブルを出力
		$this->export_table( $export_tmp_dir , 'category' );

		#--------------------------------------
		#	トラックバックテーブルを出力
		$this->export_table( $export_tmp_dir , 'trackback' );

		#--------------------------------------
		#	コメントテーブルを出力
		$this->export_table( $export_tmp_dir , 'comment' );

		#--------------------------------------
		#	データディレクトリを複製
		$this->dbh->copyall( $this->plogconf->get_home_dir().'/article_datas' , $export_tmp_dir.'/tmp/article_datas' );


		#--------------------------------------
		#	出力設定ファイルを保存
		$export_ini = '';
		$export_ini .= 'blog_name='.$this->plogconf->blog_name."\n";
		$export_ini .= 'export_date='.date( 'Y-m-d H:i:s' )."\n";
		$export_ini .= 'charset='.strtolower( mb_internal_encoding() )."\n";
		$export_ini .= 'plog_version='.$this->get_plog_version()."\n";
		$this->dbh->savefile( $export_tmp_dir.'/tmp/export.ini' , $export_ini );

		#--------------------------------------
		#	圧縮する
		$this->zip( $export_tmp_dir.'/tmp' , $export_tmp_dir.'/export.tgz' );

		#--------------------------------------
		#	一時ファイルを削除する
		$this->dbh->rmdir( $export_tmp_dir.'/tmp' );

		return	$export_tmp_dir.'/export.tgz';
	}

	#--------------------------------------
	#	テーブルを出力する
	function export_table( $export_tmp_dir , $table_key ){
		ob_start();?>
SELECT * FROM :D:tableName :D:orderBy;
<?php
		$SELECT_SQL = @ob_get_clean();

		$orderBy = '';
		if( $table_key == 'article' ){
			$orderBy = ' ORDER BY article_cd';
		}

		$bindData = array(
			'tableName'=>$this->plogconf->table_name[$table_key],
			'orderBy'=>$orderBy,
		);
		$SELECT_SQL = $this->dbh->bind( $SELECT_SQL , $bindData );
		$res = $this->dbh->sendquery( $SELECT_SQL );
		$RTN = $this->dbh->getval();

		foreach( $RTN as $Line ){
			$row = $this->dbh->mk_csv( array( $Line ) , mb_internal_encoding() );
			error_log( $row , 3 , $export_tmp_dir.'/tmp/table_'.urlencode( $table_key ).'_datas.csv' );
		}

		$keys = '';
		foreach( $RTN[0] as $key=>$val ){
			$keys .= $key."\n";
		}
		$this->dbh->savefile( $export_tmp_dir.'/tmp/table_'.urlencode( $table_key ).'_define.dat' , $keys );

		return	true;
	}





	###################################################################################################################
	#	インポート系機能
	#	Anchor: anch_import_functions

	#--------------------------------------
	#	記事データを入力する
	function import( $import_tmp_dir , $UPFILE ){
		if( !strlen( $import_tmp_dir ) ){
			return	false;
		}elseif( !is_dir( $import_tmp_dir ) ){
			return	false;
		}
		if( !$this->enable_zip() ){
			return	false;
		}

		if( is_dir( $import_tmp_dir.'/tmp' ) ){
			if( !$this->dbh->rmdir( $import_tmp_dir.'/tmp' ) ){
				#	一時ディレクトリを一旦削除
				return	false;
			}
		}
		if( !$this->dbh->mkdir( $import_tmp_dir.'/tmp' ) ){
			#	一時ディレクトリを作成
			return	false;
		}

		#--------------------------------------
		#	アップされたファイルを展開
		$this->unzip( $UPFILE['tmp_name'] , $import_tmp_dir.'/tmp' );

		if( !is_file( $import_tmp_dir.'/tmp/export.ini' ) ){
			#	export.ini がなければ、不正なアップロードファイルです。
			$this->dbh->rmdir( $import_tmp_dir.'/tmp' );
			return	false;
		}

		#--------------------------------------
		#	export.iniを読み込み
		$export_ini = $this->dbh->read_ini( $import_tmp_dir.'/tmp/export.ini' );

		#--------------------------------------
		#	出力時のテーブル定義の一覧を得る
		$article_define = $this->dbh->file_get_lines( $import_tmp_dir.'/tmp/table_article_define.dat' );
		foreach( $article_define as $key=>$val ){ $article_define[$key] = trim($val); }
		$comment_define = $this->dbh->file_get_lines( $import_tmp_dir.'/tmp/table_comment_define.dat' );
		foreach( $comment_define as $key=>$val ){ $comment_define[$key] = trim($val); }
		$trackback_define = $this->dbh->file_get_lines( $import_tmp_dir.'/tmp/table_trackback_define.dat' );
		foreach( $trackback_define as $key=>$val ){ $trackback_define[$key] = trim($val); }

		#--------------------------------------
		#	article の一覧を読み込む
		$article_list = $this->dbh->read_csv( $import_tmp_dir.'/tmp/table_article_datas.csv' , null , null , null , $export_ini['common']['charset'] );

		foreach( $article_list as $tmp_article_info ){
			$article_info = array();
			foreach( $article_define as $num=>$key ){ $article_info[$key] = $tmp_article_info[$num]; }
			unset( $tmp_article_info );

			$new_article_cd = $this->insert_article( $article_info );

			#--------------------------------------
			#	コンテンツを移植する
			$this->import_contents_data( $import_tmp_dir.'/tmp' , $article_info['article_cd'] , $new_article_cd );

			#--------------------------------------
			#	コメントを移植する
			$comment_list = $this->dbh->read_csv( $import_tmp_dir.'/tmp/table_comment_datas.csv' , null , null , null , $export_ini['common']['charset'] );
			$this->import_comment_data( $comment_list , $comment_define , $article_info['article_cd'] , $new_article_cd );

			#--------------------------------------
			#	トラックバックを移植する
			$trackback_list = $this->dbh->read_csv( $import_tmp_dir.'/tmp/table_trackback_datas.csv' , null , null , null , $export_ini['common']['charset'] );
			$this->import_trackback_data( $trackback_list , $trackback_define , $article_info['article_cd'] , $new_article_cd );

		}

		#--------------------------------------
		#	一時ディレクトリを削除
		$this->dbh->rmdir( $import_tmp_dir.'/tmp' );
		return	true;
	}



	#--------------------------------------
	#	記事行データをINSERTする
	function insert_article( $article_info ){

		ob_start();?>
INSERT INTO :D:tableName(
	article_title ,
	user_cd ,
	article_summary ,
	status ,
	category_cd ,
	release_date ,
	create_date ,
	update_date ,
	del_flg
) VALUES (
	:S:article_title ,
	:N:user_cd ,
	:S:article_summary ,
	:N:status ,
	:N:category_cd ,
	:S:release_date ,
	:S:create_date ,
	:S:update_date ,
	:N:del_flg
)
;
<?php
		$INSERT_SQL = @ob_get_clean();

		$bindData = array(
			'tableName'=>$this->plogconf->table_name.'_article',
			'article_title'=>$article_info['article_title'],
			'user_cd'=>$article_info['user_cd'],
			'article_summary'=>$article_info['article_summary'],
			'status'=>$article_info['status'],
			'category_cd'=>$article_info['category_cd'],
			'release_date'=>$article_info['release_date'],
			'create_date'=>$article_info['create_date'],
			'update_date'=>$article_info['update_date'],
			'del_flg'=>$article_info['del_flg'],
		);
		$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
		$res = $this->dbh->sendquery( $INSERT_SQL );

		if( !$res ){
			$this->dbh->rollback();
			return	false;
		}
		$this->dbh->commit();

		$article_cd = $this->dbh->get_last_insert_id( null , $this->plogconf->table_name.'_article'.'_article_cd_seq' );//挿入された行のIDを取得
		if( !strlen( $article_cd ) ){
			return	false;
		}

		return	intval( $article_cd );

	}



	#--------------------------------------
	#	コンテンツデータを移植する
	function import_contents_data( $import_data_dir , $old_article_cd , $new_article_cd ){
		$base_dir = $import_data_dir.'/article_datas';

		$ary_path_id = preg_split( '/.{0}/' , $old_article_cd );
		$path_id = '';
		foreach( $ary_path_id as $dirname ){
			if( !strlen( $dirname ) || $dirname == '.' || $dirname == '..' ){ continue; }
			$path_id .= '/'.urlencode($dirname);
		}

		$old_data_dir = $base_dir.$path_id.'/data';
		$new_data_dir = $this->plogconf->get_article_dir( $new_article_cd );

		$this->dbh->mkdirall( $new_data_dir );

		$this->dbh->copyall( $old_data_dir , $new_data_dir );

		return	true;
	}


	#--------------------------------------
	#	コメントデータを移植する
	function import_comment_data( $data_list , $table_define , $old_article_cd , $new_article_cd ){

		foreach( $data_list as $tmp_data_line ){
			$data_line = array();
			foreach( $table_define as $num=>$key ){ $data_line[$key] = $tmp_data_line[$num]; }
			unset( $tmp_data_line );
			if( $data_line['article_cd'] != $old_article_cd ){ continue; }

			ob_start();?>
INSERT INTO :D:tableName(
	article_cd ,
	keystr ,
	comment ,
	commentator_name ,
	commentator_email ,
	commentator_url ,
	comment_date ,
	status ,
	client_ip ,
	create_date ,
	update_date ,
	del_flg
) VALUES (
	:N:article_cd ,
	:S:keystr ,
	:S:comment ,
	:S:commentator_name ,
	:S:commentator_email ,
	:S:commentator_url ,
	:S:comment_date ,
	:N:status ,
	:S:client_ip ,
	:S:create_date ,
	:S:update_date ,
	:N:del_flg
)
;
<?php
			$INSERT_SQL = @ob_get_clean();

			$bindData = array(
				'tableName'=>$this->plogconf->table_name.'_comment',
				'article_cd'=>$new_article_cd ,
				'keystr'=>md5( time::microtime() ) ,
				'comment'=>$data_line['comment'] ,
				'commentator_name'=>$data_line['commentator_name'] ,
				'commentator_email'=>$data_line['commentator_email'] ,
				'commentator_url'=>$data_line['commentator_url'] ,
				'comment_date'=>$data_line['comment_date'] ,
				'status'=>$data_line['status'] ,
				'client_ip'=>$data_line['client_ip'] ,
				'create_date'=>$data_line['create_date'] ,
				'update_date'=>$data_line['update_date'] ,
				'del_flg'=>$data_line['del_flg'] ,
			);
			$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
			$res = $this->dbh->sendquery( $INSERT_SQL );

			if( !$res ){
				$this->dbh->rollback();
				return	false;
			}
			$this->dbh->commit();

		}

		return	true;
	}


	#--------------------------------------
	#	トラックバックデータを移植する
	function import_trackback_data( $data_list , $table_define , $old_article_cd , $new_article_cd ){

		foreach( $data_list as $tmp_data_line ){
			$data_line = array();
			foreach( $table_define as $num=>$key ){ $data_line[$key] = $tmp_data_line[$num]; }
			unset( $tmp_data_line );
			if( $data_line['article_cd'] != $old_article_cd ){ continue; }

			ob_start();?>
INSERT INTO :D:tableName(
	article_cd ,
	keystr ,
	trackback_blog_name ,
	trackback_title ,
	trackback_url ,
	trackback_excerpt ,
	trackback_date ,
	status ,
	client_ip ,
	create_date ,
	update_date ,
	del_flg
) VALUES (
	:N:article_cd ,
	:S:keystr ,
	:S:trackback_blog_name ,
	:S:trackback_title ,
	:S:trackback_url ,
	:S:trackback_excerpt ,
	:S:trackback_date ,
	:N:status ,
	:S:client_ip ,
	:S:create_date ,
	:S:update_date ,
	:N:del_flg
)
;
<?php
			$INSERT_SQL = @ob_get_clean();

			$bindData = array(
				'tableName'=>$this->plogconf->table_name.'_trackback',
				'article_cd'=>$new_article_cd ,
				'keystr'=>md5( time::microtime() ) ,
				'trackback_blog_name'=>$data_line['trackback_blog_name'] ,
				'trackback_title'=>$data_line['trackback_title'] ,
				'trackback_url'=>$data_line['trackback_url'] ,
				'trackback_excerpt'=>$data_line['trackback_excerpt'] ,
				'trackback_date'=>$data_line['trackback_date'] ,
				'status'=>$data_line['status'] ,
				'client_ip'=>$data_line['client_ip'] ,
				'create_date'=>$data_line['create_date'] ,
				'update_date'=>$data_line['update_date'] ,
				'del_flg'=>$data_line['del_flg'] ,
			);
			$INSERT_SQL = $this->dbh->bind( $INSERT_SQL , $bindData );
			$res = $this->dbh->sendquery( $INSERT_SQL );

			if( !$res ){
				$this->dbh->rollback();
				return	false;
			}
			$this->dbh->commit();

		}

		return	true;
	}




	###################################################################################################################
	#	その他機能
	#	Anchor: anch_other_functions


	#--------------------------------------
	#	ZIPメソッドを利用可能か否か確認する
	function enable_zip(){
		if( !strlen( $this->conf->path_commands['tar'] ) ){ return false; }
		if( !is_callable( 'exec' ) ){ return false; }
		return	true;
	}

	#--------------------------------------
	#	ファイルまたはディレクトリをZIP圧縮する
	function zip( $path_target , $path_zipto ){
		#	$path_target => 圧縮する元ファイル/ディレクトリ
		#	$path_zipto => 作成したzipファイルの保存先パス
		$path_target = $this->dbh->get_realpath($path_target);
		$path_zipto = $this->dbh->get_realpath($path_zipto);

		if( !$this->enable_zip() ){ return false; }

		if( !is_dir( $path_target ) && !is_file( $path_target ) ){
			#	ファイルでもディレクトリでもなければ、ダメ。
			$this->errors->error_log( 'ZIP対象['.$path_target.']は、ファイルでもディレクトリでもありません。' );
			return	false;
		}

		#	現在のディレクトリを記憶
		$MEMORY_CDIR = realpath('.');

		$cdto = $path_target;
		if( is_file( $path_target ) ){
			$cdto = dirname( $path_target );
		}

		if( !@chdir( $cdto ) ){
			return	false;
		}

		if( strlen( $this->conf->path_commands['tar'] ) && strlen( $this->conf->path_commands['gzip'] ) ){
			#--------------------------------------
			#	tar+gzipコマンドを実行する
			$tmp_filename = dirname( $path_zipto ).'/tmp'.time();
			$command = escapeshellcmd( $this->conf->path_commands['tar'] ).' cvf '.escapeshellarg( $tmp_filename.'.tar' ).' ';
			if( is_dir( $path_target ) ){
				$command .= ' '.'./*';
			}else{
				$command .= ' '.escapeshellarg( './'.basename( $path_target ).'*' );
			}
			$result = @exec( $command );

			clearstatcache();
			if( is_file( $tmp_filename.'.tar' ) ){
				$result = @exec( escapeshellcmd( $this->conf->path_commands['gzip'] ).' '.escapeshellarg( $tmp_filename.'.tar' ) );
				clearstatcache();
				if( is_file( $tmp_filename.'.tar.gz' ) ){
					$result = rename( $tmp_filename.'.tar.gz' , $path_zipto );
				}elseif( is_file( $tmp_filename.'.tgz' ) ){
					$result = rename( $tmp_filename.'.tgz' , $path_zipto );
				}
			}

			#	/ tar+gzipコマンドを実行する
			#--------------------------------------
		}else{
			#--------------------------------------
			#	tarコマンドを実行する
			$command = escapeshellcmd( $this->conf->path_commands['tar'] ).' cvfz '.escapeshellarg( $path_zipto ).' ';
			if( is_dir( $path_target ) ){
				$command .= ' '.'./*';
			}else{
				$command .= ' '.escapeshellarg( './'.basename( $path_target ) );
			}
			$result = @exec( $command );
			#	/ tarコマンドを実行する
			#--------------------------------------
		}

		#	元のディレクトリに戻す
		@chdir( $MEMORY_CDIR );

		if( $result === false ){
			return	false;
		}

		return true;

	}

	#--------------------------------------
	#	ZIPファイルを展開する
	function unzip( $path_target , $path_unzipto ){
		#	$path_target => 圧縮する元ファイル/ディレクトリ
		#	$path_unzipto => 作成したzipファイルの保存先パス
		$path_target = $this->dbh->get_realpath($path_target);
		$path_unzipto = $this->dbh->get_realpath($path_unzipto);

		if( !$this->enable_zip() ){ return false; }

		if( !is_file( $path_target ) ){
			#	ファイルじゃなければ、ダメ。
			$this->errors->error_log( 'UNZIP対象['.$path_target.']は、ファイルでありません。' );
			return	false;
		}

		if( is_file( $path_unzipto ) ){
			#	展開先がファイルだったらダメ。
			$this->errors->error_log( 'UNZIP先['.$path_unzipto.']は、ファイルです。' );
			return	false;
		}

		if( !is_dir( $path_unzipto ) ){
			#	展開先ディレクトリがなかったらダメ。
			$this->errors->error_log( 'UNZIP先ディレクトリ['.$path_unzipto.']は、存在しません。' );
			return	false;
		}

		if( !$this->dbh->is_writable( $path_unzipto ) ){
			#	展開先ディレクトリが書き込めなかったらダメ。
			$this->errors->error_log( 'UNZIP先ディレクトリ['.$path_unzipto.']は、書き込めません。' );
			return	false;
		}

		#	現在のディレクトリを記憶
		$MEMORY_CDIR = realpath('.');
	
		if( !@chdir( $path_unzipto ) ){
			return	false;
		}

		if( strlen( $this->conf->path_commands['tar'] ) && strlen( $this->conf->path_commands['gzip'] ) ){
			#--------------------------------------
			#	tar+gzipコマンドを実行する
			$command = escapeshellcmd( $this->conf->path_commands['gzip'] ).' -d '.escapeshellarg( $path_target );
			$result = @exec( $command );

			#	解凍してできた *.tar ファイルをリネーム
			$tmp_tar_filename = preg_replace( '/(?:\.tar\.gz|\.tgz)$/i' , '' , $path_target );
			rename( $tmp_tar_filename.'.tar' , $path_target.'.tar' );

			$command = escapeshellcmd( $this->conf->path_commands['tar'] ).' xvf '.escapeshellarg( $path_target.'.tar' );
			$result = @exec( $command );
			if( is_file( $path_target.'.tar' ) ){
				$result = @exec( escapeshellcmd( $this->conf->path_commands['gzip'] ).' '.escapeshellarg( $path_target ).'.tar' );
				if( is_file( $path_target.'.tar.gz' ) ){
					$result = rename( $path_target.'.tar.gz' , $path_target );
				}
			}
			#	/ tar+gzipコマンドを実行する
			#--------------------------------------
		}else{
			#--------------------------------------
			#	tarコマンドを実行する
			$command = escapeshellcmd( $this->conf->path_commands['tar'] ).' xvfz '.escapeshellarg( $path_target );
			$result = @exec( $command );
			#	/ tarコマンドを実行する
			#--------------------------------------
		}

		#	元のディレクトリに戻す
		@chdir( $MEMORY_CDIR );

		if( $result === false ){
			return	false;
		}

		return true;

	}






	#--------------------------------------
	#	PLOGプラグインのバージョン番号を得る
	function get_plog_version(){
		$filepath = $this->conf->path_lib_base.'/plugins/PLOG/_UPDATELOG_/setupHistory.log';
		if( !is_file( $filepath ) ){
			return	false;
		}elseif( !is_readable( $filepath ) ){
			return	false;
		}
		$rows = $this->dbh->file_get_lines( $filepath );
		$final = array(
			'inst_datetime'=>0 ,
			'version'=>'0.0.0' ,
			'plugin_name'=>'PLOG' ,
		);
		foreach( $rows as $line ){
			$line = preg_replace( '/(?:\r\n|\r|\n)?$/si' , '' , $line );
			list( $date , $version , $plugin_name ) = explode( '	' , $line );
			$inst_datetime = time::datetime2int( $date );
			if( $final['inst_datetime'] <= $inst_datetime ){
				$final = array(
					'inst_datetime'=>$inst_datetime ,
					'version'=>trim( $version ) ,
					'plugin_name'=>trim( $plugin_name ) ,
				);
			}
		}

		return	$final['version'];
	}


}


?>