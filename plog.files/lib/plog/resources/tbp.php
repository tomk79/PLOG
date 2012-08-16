<?php

#	Copyright (C)Tomoya Koyanagi.
#	LastUpdate : 18:49 2010/07/22

###################################################################################################################
#	トラックバックピングを司るクラス：発行、受付、両用。
#		受信系：anch_receive_tbp
#		発信系：anch_send_tbp
#		その他：anch_other_methods
class cont_plog_resources_tbp{
	var $plogconf;
	var $conf;
	var $dbh;
	var $theme;

	var $internal_error_list = array();
		#	内部エラーの溜まり場
	var $tbp_user_agent = 'PLOG_TrackbackPing_Agent';
		#	TrackbackPing送信時のユーザエージェント
	var $response_header;
	var $response_body;

	var $tbp_sendlog = array();
		#	トラックバックPING送信ログのメモ

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_resources_tbp( &$plogconf , &$conf , &$dbh , &$theme ){
		$this->plogconf = &$plogconf;
		$this->conf = &$conf;
		$this->dbh = &$dbh;
		$this->theme = &$theme;
	}




	###################################################################################################################
	#	TrackBackPing受信系メソッド集(Anchor: anch_receive_tbp)


	#--------------------------------------
	#	TBPを受ける
	function receive_trackback_ping( $POST , $article_cd ){

		#--------------------------------------
		#	環境変数系のチェック
		if( !$this->plogconf->enable_trackback ){
			#	トラックバック機能が無効に設定されている場合
			return	$this->response_ng( 'Trackback Ping is Not Allowed.' );
		}

		if( strtolower( $_SERVER['REQUEST_METHOD'] ) != 'post' ){
			#	メソッドがPOSTではなかった場合
			return	$this->response_ng( 'Trackback pings must use HTTP POST' );
		}

		if( !is_array( $POST ) ){
			#	$POST に不正な値が渡された場合
			$this->internal_error( '$POST is NOT an array.' , __FILE__ , __LINE__ );
			return	$this->response_ng( 'Application Error.' );
		}

		if( intval( $article_cd ) != $article_cd || intval( $article_cd ) < 1 ){
			#	$article_cd に不正な値が渡された場合
			$this->internal_error( '$article_cd is an Invalid value.' , __FILE__ , __LINE__ );
			return	$this->response_ng( 'Application Error.' );
		}
		$article_cd = intval( $article_cd );//念のため、INT型にキャスト


		#--------------------------------------
		#	$POST 入力値のチェック/調整

		#	【サイト(ブログ)名】
		$POST['blog_name'] = preg_replace( '/(?:\r\n|\r|\n|\t)+/si' , ' ' , $POST['blog_name'] );//改行とタブは禁止
		$POST['blog_name'] = trim( $POST['blog_name'] );//前後の余計な要素は切り詰めます
		if( !strlen( $POST['blog_name'] ) ){
			$POST['blog_name'] = 'Unknown';
		}elseif( strlen( $POST['blog_name'] ) > 127 ){
			#	丸める
			$POST['blog_name'] = mb_strimwidth( $POST['blog_name'] , 0 , 127 , '...' );
		}

		#	【記事タイトル】
		$POST['title'] = preg_replace( '/(?:\r\n|\r|\n|\t)+/si' , ' ' , $POST['title'] );//改行とタブは禁止
		$POST['title'] = trim( $POST['title'] );//前後の余計な要素は切り詰めます
		if( !strlen( $POST['title'] ) ){
			$POST['title'] = 'Unknown';
		}elseif( strlen( $POST['title'] ) > 128 ){
			#	丸める
			$POST['title'] = mb_strimwidth( $POST['title'] , 0 , 128 , '...' );
		}

		#	【URL】
		$POST['url'] = preg_replace( '/\r\n|\r|\n|\t| /si' , '' , $POST['url'] );//改行とタブとスペースは禁止
		$POST['url'] = trim( $POST['url'] );//前後の余計な要素は切り詰めます
		if( !strlen( $POST['url'] ) ){
			$errorMsg = 'URL is NOT given.';
			$this->internal_error( $errorMsg , __FILE__ , __LINE__ );
			return	$this->response_ng( $errorMsg );
		}elseif( !preg_match( '/^https?\:\/\//si' , $POST['url'] ) ){
			#	URLの形式エラー
			$errorMsg = 'URL is Invalid value.';
			$this->internal_error( $errorMsg , __FILE__ , __LINE__ );
			return	$this->response_ng( $errorMsg );
		}elseif( strlen( $POST['url'] ) > 256 ){
			#	URLの文字数過多エラー
			$errorMsg = 'URL is Invalid value.';
			$this->internal_error( $errorMsg , __FILE__ , __LINE__ );
			return	$this->response_ng( $errorMsg );
		}

		#	【サマリ？】
		$POST['excerpt'] = preg_replace( '/(?:\r\n|\r|\n)+/si' , "\n" , $POST['excerpt'] );//余計な改行はトルツメます
		$POST['excerpt'] = preg_replace( '/\t| /si' , ' ' , $POST['excerpt'] );//タブとスペースは半角スペースに統一
		$POST['excerpt'] = trim( $POST['excerpt'] );//前後の余計な要素は切り詰めます
		if( !strlen( $POST['excerpt'] ) ){
			$POST['excerpt'] = 'no message';
		}elseif( strlen( $POST['excerpt'] ) > 256 ){
			#	丸める
			$POST['excerpt'] = mb_strimwidth( $POST['excerpt'] , 0 , 256 , '...' );
		}


		#--------------------------------------
		#	トラックバック登録の処理開始
		$dao = &$this->plogconf->factory_dao( 'trackback' );
		if( !is_object( $dao ) ){
			$this->internal_error( 'FAILD to load DAO.' , __FILE__ , __LINE__ );
			return	$this->response_ng( 'Application Error.' );
		}

		$status = 0;
		if( $this->plogconf->trackback_auto_commit ){
			#	トラックバックの即座反映設定の反映
			$status = 1;
		}

		$result = $dao->insert_trackbackping( $article_cd , $POST['url'] , $POST['title'] , $POST['blog_name'] , $POST['excerpt'] , $_SERVER['REMOTE_ADDR'] , $status );
		if( $result === false ){
			$this->internal_error( 'FAILD to Insert TrackbackPing.' , __FILE__ , __LINE__ );
			return	$this->response_ng( 'FAILD to accept.' );
		}

		return	$this->response_ok();
	}





	#--------------------------------------
	#	TBP元へ返答を返す
	function response_ok( $do_download = true ){
		if( !strlen( $error_msg ) ){
			$error_msg = 'Unknown error.';
		}
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="iso-8859-1"?'.'>'."\n";
		$RTN .= '<response>'."\n";
		$RTN .= '<error>0</error>'."\n";
		$RTN .= '</response>'."\n";

		if( $do_download ){
			return	$this->theme->download( $RTN , array( 'content-type'=>'application/xml' ) );
		}

		return	$RTN;
	}
	function response_ng( $error_msg = 'Unknown error.' , $do_download = true ){
		if( !strlen( $error_msg ) ){
			$error_msg = 'Unknown error.';
		}
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="iso-8859-1"?'.'>'."\n";
		$RTN .= '<response>'."\n";
		$RTN .= '<error>1</error>'."\n";
		$RTN .= '<message>'.htmlspecialchars( $error_msg ).'</message>'."\n";
		$RTN .= '</response>'."\n";

		if( $do_download ){
			return	$this->theme->download( $RTN , array( 'content-type'=>'application/xml' ) );
		}

		return	$RTN;
	}





	###################################################################################################################
	#	TrackBackPing発信系メソッド集(Anchor: anch_send_tbp)

	#--------------------------------------
	#	TBPを送る
	function send_trackback_ping( $article_cd , $tbp_uri , $article_url , $article_title , $article_summary , $blog_name ){
		if( $this->is_tbp_sent( $article_cd , $tbp_uri ) && !$this->is_tbp_error( $article_cd , $tbp_uri ) ){
			#	このセッション内で、
			#	同一記事から同一URIに向けたTBPを既に送信しており、
			#	かつ、エラーがなかったら、重複送信となるので、
			#	ここで終わり。(成功したことにする)
			return	true;
		}

#		#	↓PLOG 0.1.3 : もう使ってないのに確認してた不具合を修正。
#		if( !strlen( $this->conf->path_commands['wget'] ) ){
#			#	wget のパスが設定されていない場合。
#			$this->internal_error( 'path of command [wget] is NOT set.' , __FILE__ , __LINE__ );
#			return	false;
#		}

		$saveTo = realpath( $this->plogconf->get_home_dir() );
		if( !is_dir( $saveTo ) ){
			#	ホームディレクトリが設定されていない場合
			$this->internal_error( 'HOME directory is NOT exists.' , __FILE__ , __LINE__ );
			return	false;
		}
		if( !$this->dbh->is_writable( $saveTo ) ){
			#	ホームディレクトリが書き込みできない場合
			$this->internal_error( 'HOME directory is NOT writable.' , __FILE__ , __LINE__ );
			return	false;
		}

		$saveTo = $saveTo.'/tmp_tbprequestfile.tmp';
		if( file_exists( $saveTo ) && !$this->dbh->is_writable( $saveTo ) ){
			#	一時ファイルが存在していて、書き込みできない場合
			$this->internal_error( 'temp file is exists and NOT writable.' , __FILE__ , __LINE__ );
			return	false;
		}
		if( is_dir( $saveTo ) ){
			#	一時ファイルがディレクトリの場合
			$this->internal_error( 'temp file is exists BUT as a directory.' , __FILE__ , __LINE__ );
			return	false;
		}

		$post_data = '';
		$post_data .= 'url='.urlencode( $article_url );
		$post_data .= '&blog_name='.urlencode( $blog_name );
		$post_data .= '&title='.urlencode( $article_title );
		$post_data .= '&excerpt='.urlencode( $article_summary );
		$request_url = $tbp_uri;
		if( preg_match( '/\?/' , $request_url ) ){
			$request_url .= '&'.$post_data;
		}else{
			$request_url .= '?'.$post_data;
		}

		#--------------------------------------
		#	トラックバックリクエストを送信
		$className = $this->plogconf->require_lib( '/PLOG/resources/httpaccess.php' );
		$httpaccess = new $className();
		$httpaccess->set_user_agent( $this->tbp_user_agent );
		$httpaccess->set_max_redirect_number( 5 );
		$httpaccess->set_auto_redirect_flg( true );
		$httpaccess->set_url( $tbp_uri );
		$httpaccess->set_method( 'POST' );
		$httpaccess->set_post_data( $post_data );
		$httpaccess->save_http_contents( $saveTo );
		#	/ トラックバックリクエストを送信
		#--------------------------------------


		#--------------------------------------
		#	取れた文字列を解析
		if( is_file( $saveTo ) ){
			$GOT_CONTENT = $this->dbh->file_get_contents( $saveTo );
			$response_body = $GOT_CONTENT;

		}
		#	/ 取れた文字列を解析
		#--------------------------------------

		#	一時ファイルを削除
		$this->dbh->rmdir( $saveTo );

		if( !strlen( $response_body ) ){
			#	取れた値が空白なら失敗
			$this->internal_error( 'No responce.' , __FILE__ , __LINE__ );
			return	false;
		}

		#--------------------------------------
		#	エラーの数を調べる
		$is_tbp_error = false;
		preg_match( '/<error>(.*?)<\/error>/is' , $response_body , $preg_result );
		$error_count = intval( $preg_result[1] );

		$error_messages = array();//エラーメッセージ
		if( $error_count >= 1 ){
			#	エラーが1件以上存在したら失敗。
			$is_tbp_error = true;
			preg_match_all( '/<message>(.*?)<\/message>/is' , $response_body , $preg_result );
			for( $i = 0; !is_null( $preg_result[0][$i] ); $i ++ ){
				$this->internal_error( $preg_result[1][$i] , __FILE__ , __LINE__ );
				array_push( $error_messages , $preg_result[1][$i] );
			}
		}

		$response_code = intval( $httpaccess->get_status_cd() );
		if( $response_code >= 400 && $response_code < 600 ){
			#	HTTPステータスコードが400番台または500番台なら、エラー
			$is_tbp_error = true;
			$error_string = $response_code.' '.$httpaccess->get_status_msg();
			$this->internal_error( $error_string , __FILE__ , __LINE__ );
			array_push( $error_messages , $error_string );
		}
		#	/ エラーの数を調べる
		#--------------------------------------

		#	トラックバックPINGの送信ログをメモる。
		$this->add_tbp_sendlog( $article_cd , $tbp_uri , $error_count , $error_messages );

		if( $is_tbp_error ){
			#	エラーが1件以上存在したら、
			#	falseを返す。
			return	false;
		}
		return	true;
	}


	#--------------------------------------
	#	送信ログをメモる
	function add_tbp_sendlog( $article_cd , $tbp_uri , $error_count , $error_messages = array() ){
		$this->tbp_sendlog[$article_cd][$tbp_uri] = array(
			'article_cd'=>$article_cd ,
			'tbp_uri'=>$tbp_uri ,
			'error_count'=>intval( $error_count ) ,
			'error_messages'=>$error_messages ,
		);
		return	true;
	}

	#--------------------------------------
	#	送信ログを取得する
	function get_tbp_sendlog(){
		$RTN = array();
		foreach( $this->tbp_sendlog as $article_line ){
			foreach( $article_line as $log_line ){
				array_push( $RTN , $log_line );
			}
		}
		return	$RTN;
	}

	#--------------------------------------
	#	このセッションで、指定URIに対してPINGを送ったか調べる
	function is_tbp_sent( $article_cd , $tbp_uri ){
		if( !is_array( $this->tbp_sendlog[$article_cd][$tbp_uri] ) ){
			return	false;
		}
		return	true;
	}

	#--------------------------------------
	#	このセッションで、指定URIに対してPINGに、エラーがあったか調べる
	function is_tbp_error( $article_cd , $tbp_uri ){
		if( !$this->is_tbp_sent( $article_cd , $tbp_uri ) ){
			#	送信されていなければ、エラーはない。
			return	false;
		}
		if( $this->tbp_sendlog[$article_cd][$tbp_uri]['error_num'] >= 1 ){
			#	エラーの数が1以上なら、エラーが存在する。
			return	true;
		}
		return	false;
	}






	###################################################################################################################
	#	その他の処理(Anchor: anch_other_methods)

	#--------------------------------------
	#	オブジェクト内部エラーを記憶
	function internal_error( $errormessage , $file , $line ){
		array_push(
			$this->internal_error_list ,
			array(
				'message'=>$errormessage ,
				'file'=>$file ,
				'line'=>$line ,
			)
		);
		return	true;
	}
	function get_internal_error_list(){
		return	$this->internal_error_list;
	}
	function is_internal_error(){
		return	$this->internal_error_list;
	}




}



?>