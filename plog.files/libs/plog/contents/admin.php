<?php

#	PxFW - Content - [PLOG]
#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 19:44 2010/08/09

#------------------------------------------------------------------------------------------------------------------
#	コンテンツオブジェクトクラス [ cont_plog_contents_admin ]
class cont_plog_contents_admin{
	var $plog;
	var $conf;
	var $errors;
	var $dbh;
	var $req;
	var $user;
	var $site;
	var $theme;
	var $custom;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_contents_admin( &$plog ){
		$this->plog = &$plog;
		$this->conf = &$plog->get_basicobj_conf();
		$this->errors = &$plog->get_basicobj_errors();
		$this->dbh = &$plog->get_basicobj_dbh();
		$this->req = &$plog->get_basicobj_req();
		$this->user = &$plog->get_basicobj_user();
		$this->site = &$plog->get_basicobj_site();
		$this->theme = &$plog->get_basicobj_theme();
		$this->custom = &$plog->get_basicobj_custom();

		$this->additional_setup();
	}

	#--------------------------------------
	#	コンストラクタの追加処理
	function additional_setup(){
	}


	#--------------------------------------
	#	開始
	function start(){
		$article_info = array();
		if( strlen( $this->req->pvelm(1) ) ){
			$dao_admin = &$this->plog->factory_dao( 'admin' );
			$article_info = $dao_admin->get_article_info( $this->req->pvelm(1) );
		}

		#--------------------------------------
		#	サイトマップ定義
		$cattitleby = $this->site->getpageinfo( $this->req->po() , 'cattitleby' );
		$sitemap_path = $this->site->getpageinfo( $this->req->po() , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.io' , array( 'title'=>'インポート/エクスポート' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.article_list' , array( 'title'=>'記事一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.create_db' , array( 'title'=>'テーブル作成' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.category' , array( 'title'=>'カテゴリ一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.update_rss' , array( 'title'=>'RSSの更新' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.configcheck' , array( 'title'=>'関連設定の確認' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.search' , array( 'title'=>'記事検索' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.update_search_index' , array( 'title'=>'検索インデックス更新' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.req_comments' , array( 'title'=>'承認待ちコメント一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.new_comments' , array( 'title'=>'新着コメント一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.req_trackbacks' , array( 'title'=>'承認待ちトラックバック一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.new_trackbacks' , array( 'title'=>'新着トラックバック一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );

		$sitemap_path = $this->site->getpageinfo( $this->req->po().'.article_list' , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.create_article' , array( 'title'=>'新規記事作成' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.article.'.$this->req->pvelm(1) , array( 'title'=>'記事詳細: '.$article_info['article_title'] , 'title_breadcrumb'=>mb_strimwidth( $article_info['article_title'] , 0 , strlen('あ')*10 , '...' ) , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );

		$sitemap_path = $this->site->getpageinfo( $this->req->po().'.article.'.$this->req->pvelm(1) , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.edit_article.'.$this->req->pvelm(1) , array( 'title'=>'記事編集' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.delete_article.'.$this->req->pvelm(1) , array( 'title'=>'記事削除' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.send_tbp.'.$this->req->pvelm(1) , array( 'title'=>'トラックバック送信' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.tb_list.'.$this->req->pvelm(1) , array( 'title'=>'トラックバック一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.comment_list.'.$this->req->pvelm(1) , array( 'title'=>'コメント一覧' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );

		$sitemap_path = $this->site->getpageinfo( $this->req->po().'.tb_list.'.$this->req->pvelm(1) , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.tb_cst.'.$this->req->pvelm(1) , array( 'title'=>'トラックバックステータス変更' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.tb_delete.'.$this->req->pvelm(1) , array( 'title'=>'トラックバック削除' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );

		$sitemap_path = $this->site->getpageinfo( $this->req->po().'.comment_list.'.$this->req->pvelm(1) , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.comment_cst.'.$this->req->pvelm(1) , array( 'title'=>'コメントステータス変更' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.comment_delete.'.$this->req->pvelm(1) , array( 'title'=>'コメント削除' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );

		$sitemap_path = $this->site->getpageinfo( $this->req->po().'.category' , 'path' );
		$this->site->setpageinfoall( $this->req->po().'.create_category' , array( 'title'=>'新規カテゴリ作成' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby , 'list_flg'=>true ) );
		$this->site->setpageinfoall( $this->req->po().'.edit_category.'.$this->req->pvelm(1) , array( 'title'=>'カテゴリ編集' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		$this->site->setpageinfoall( $this->req->po().'.make_categories_flat.'.$this->req->pvelm(1) , array( 'title'=>'カテゴリ階層構造のリセット' , 'path'=>$sitemap_path , 'cattitleby'=>$cattitleby ) );
		#	/ サイトマップ定義
		#--------------------------------------

		if( $this->req->pvelm() == 'article_list' ){
			return	$this->page_article_list();
		}elseif( $this->req->pvelm() == 'create_article' || $this->req->pvelm() == 'edit_article' ){
			return	$this->start_edit_article();
		}elseif( $this->req->pvelm() == 'delete_article' ){
			return	$this->start_delete_article();
		}elseif( $this->req->pvelm() == 'article' ){
			return	$this->page_article();
		}elseif( $this->req->pvelm() == 'create_db' ){
			return	$this->start_create_db();
		}elseif( $this->req->pvelm() == 'category' ){
			return	$this->page_category();
		}elseif( $this->req->pvelm() == 'update_rss' ){
			return	$this->start_update_rss();
		}elseif( $this->req->pvelm() == 'create_category' || $this->req->pvelm() == 'edit_category' ){
			return	$this->start_edit_category();
		}elseif( $this->req->pvelm() == 'make_categories_flat' ){
			return	$this->start_make_categories_flat();
		}elseif( $this->req->pvelm() == 'create_db_sql_download' ){
			return	$this->start_create_db_sql_download();
		}elseif( $this->req->pvelm() == 'send_tbp' ){
			return	$this->start_send_tbp();
		}elseif( $this->req->pvelm() == 'tb_list' ){
			return	$this->page_tb_list();
		}elseif( $this->req->pvelm() == 'tb_cst' ){
			return	$this->start_tb_cst();
		}elseif( $this->req->pvelm() == 'tb_delete' ){
			return	$this->start_tb_delete();
		}elseif( $this->req->pvelm() == 'comment_cst' ){
			return	$this->start_comment_cst();
		}elseif( $this->req->pvelm() == 'comment_delete' ){
			return	$this->start_comment_delete();
		}elseif( $this->req->pvelm() == 'comment_list' ){
			return	$this->page_comment_list();
		}elseif( $this->req->pvelm() == 'req_comments' ){
			return	$this->page_req_comments();
		}elseif( $this->req->pvelm() == 'new_comments' ){
			return	$this->page_new_comments();
		}elseif( $this->req->pvelm() == 'req_trackbacks' ){
			return	$this->page_req_trackbacks();
		}elseif( $this->req->pvelm() == 'new_trackbacks' ){
			return	$this->page_new_trackbacks();
		}elseif( $this->req->pvelm() == 'search' ){
			return	$this->start_search();
		}elseif( $this->req->pvelm() == 'configcheck' ){
			return	$this->page_configcheck();
		}elseif( $this->req->pvelm() == 'io' ){
			return	$this->start_io();
		}elseif( $this->req->pvelm() == 'update_search_index' ){
			return	$this->start_update_search_index();
		}
		return	$this->page_start();
	}



	###################################################################################################################
	#	スタートページ
	function page_start(){

		$MENU = '';
		$MENU .= $this->theme->mk_hx('記事管理')."\n";
		$MENU .= '<ul>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':article_list' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':create_article' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':category' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':update_rss' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':search' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '</ul>'."\n";
		$MENU .= '<div class="unit_pane2">'."\n";
		$MENU .= '	<div class="pane2L">'."\n";
		$MENU .= '		'.$this->theme->mk_hx('コメント管理')."\n";
		$MENU .= '		<ul>'."\n";
		$MENU .= '			<li>'.$this->theme->mk_link( ':req_comments' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '			<li>'.$this->theme->mk_link( ':new_comments' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '		</ul>'."\n";
		$MENU .= '	</div>'."\n";
		$MENU .= '	<div class="pane2R">'."\n";
		$MENU .= '		'.$this->theme->mk_hx('トラックバック管理')."\n";
		$MENU .= '		<ul>'."\n";
		$MENU .= '			<li>'.$this->theme->mk_link( ':req_trackbacks' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '			<li>'.$this->theme->mk_link( ':new_trackbacks' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '		</ul>'."\n";
		$MENU .= '	</div>'."\n";
		$MENU .= '</div>'."\n";
		$MENU .= $this->theme->mk_hx('その他')."\n";
		$MENU .= '<ul>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':update_search_index' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':io' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':configcheck' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':create_db' , array('style'=>null) ).'</li>'."\n";
		$MENU .= '</ul>'."\n";

		$RTN = '';
		$RTN .= $MENU;

		return	$RTN;
	}


	###################################################################################################################
	#	記事一覧ページ
	function page_article_list(){

		#	イベントハンドラを一時的に無効にする
		$METHOD_MEMO = $this->dbh->method_eventhdl_query_error;
		$this->dbh->method_eventhdl_query_error = null;

		$page_number = intval( $this->req->pvelm(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$dao = &$this->plog->factory_dao( 'admin' );
		$pager_info = $this->dbh->get_pager_info( $dao->get_article_count() , $page_number , 20 );
		$article_list = $dao->get_article_list( null , $pager_info['offset'] , $pager_info['dpp'] );

		#	イベントハンドラを元に戻す
		$this->dbh->method_eventhdl_query_error = $METHOD_MEMO;
		unset($METHOD_MEMO);

		$SRCMEMO = '';
		foreach( $article_list as $Line ){
			$status_label = array( 0=>'執筆中' , 1=>'公開中' );
			if( time() < time::datetime2int( $Line['release_date'] ) ){
				$status_label[1] = '公開待ち';
			}
			$status_class = array( 0=>'progress' , 1=>'public' );
			$SRCMEMO .= '	<tr class="'.htmlspecialchars($status_class[$Line['status']]).'">'."\n";
			$SRCMEMO .= '		<th>'.htmlspecialchars($Line['article_cd']).'</th>';
			$SRCMEMO .= '		<td>'."\n";
			$SRCMEMO .= '			<div>'.$this->theme->mk_link( ':article.'.$Line['article_cd'] , array('label'=>$Line['article_title'],'style'=>'inside') ).'</div>'."\n";
			$SRCMEMO .= '			<div class="ttrs">公開日時：'.htmlspecialchars( $this->theme->dateformat( 'YmdHi' , time::datetime2int( $Line['release_date'] ) ) ).'</div>'."\n";
			$SRCMEMO .= '		</td>'."\n";
			$SRCMEMO .= '		<td>'.htmlspecialchars($status_label[$Line['status']]).'</td>';
			$SRCMEMO .= '		<td class="ttr nowrap">'.$this->theme->mk_link( ':edit_article.'.$Line['article_cd'] , array('label'=>'編集','style'=>'inside') ).'</td>';
			$SRCMEMO .= '	</tr>'."\n";
		}

		$MENU = '';
		$MENU .= '<ul class="horizontal">'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':create_article' , array('label'=>'新しい記事を作成する','style'=>'inside') ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':category' , array('label'=>'カテゴリ一覧','style'=>'inside') ).'</li>'."\n";
		$MENU .= '</ul>'."\n";

		#--------------------------------------
		#	ページャ生成
		$PID_BASE = 'article_list.';

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->theme->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->theme->mk_link( ':'.$PID_BASE.$pager_info['prev'] , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->theme->mk_link( ':'.$PID_BASE.$i , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->theme->mk_link( ':'.$PID_BASE.$pager_info['next'] , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
				}
				$PAGER = '';
				if( $pager_info['total_page_count'] > 1 ){
					$PAGER .= '<p class="ttr alignC cont_pager">'."\n";
					$PAGER .= implode( ' | ' , $PAGER_ARY )."\n";
					$PAGER .= '</p>'."\n";
				}
			}
		}
		#	/ページャ生成
		#--------------------------------------

		$RTN = '';
		$RTN .= $MENU;
		$RTN .= $PAGER;
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<table class="deftable" width="100%">'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</table>'."\n";
		}else{
			$RTN .= '<p>'."\n";
			$RTN .= '	現在記事は登録されていません。<br />'."\n";
			$RTN .= '</p>'."\n";
		}
		$RTN .= $PAGER;
		$RTN .= $MENU;

		return	$RTN;
	}


	###################################################################################################################
	#	記事の詳細画面
	function page_article(){
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$article_info = $dao_admin->get_article_info( $this->req->pvelm(1) );
		if( !is_array($article_info) ){
			return	$this->theme->printnotfound();
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':edit_article.'.$this->req->pvelm(1) , array('label'=>'この記事を編集する','style'=>'inside') ).'</li>'."\n";
		if( intval( $article_info['status'] ) && time::datetime2int( $article_info['release_date'] ) <= time() ){
			$RTN .= '	<li>'.$this->theme->mk_link( ':send_tbp.'.$this->req->pvelm(1) , array('label'=>'TrackbackPingを送信する','style'=>'inside') ).'</li>'."\n";
		}
		$RTN .= '</ul>'."\n";

		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>記事番号</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->req->pvelm(1) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>タイトル</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $article_info['article_title'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>カテゴリ</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $article_info['category_title'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>公開日</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->theme->dateformat( 'YmdHis' , time::datetime2int($article_info['release_date']) ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ステータス</th>'."\n";
		$status_view = array( 0=>'執筆中' , 1=>'公開中' );
		if( time() < time::datetime2int( $article_info['release_date'] ) ){
			$status_view[1] = '公開待ち';
		}
		$RTN .= '		<td>'.htmlspecialchars( $status_view[$article_info['status']] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$tb_count = $dao_visitor->get_trackback_count( $this->req->pvelm(1) );
		$RTN .= '		<th>トラックバック</th>'."\n";
		$RTN .= '		<td>有効件数：'.intval( $tb_count[$this->req->pvelm(1)] ).'件 '.$this->theme->mk_link( ':tb_list.'.$this->req->pvelm(1) , array('label'=>'トラックバック一覧','style'=>'inside') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$comment_count = $dao_visitor->get_comment_count( $this->req->pvelm(1) );
		$RTN .= '		<th>コメント</th>'."\n";
		$RTN .= '		<td>有効件数：'.intval( $comment_count[$this->req->pvelm(1)] ).'件 '.$this->theme->mk_link( ':comment_list.'.$this->req->pvelm(1) , array('label'=>'コメント一覧','style'=>'inside') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $this->req->pvelm(1) );

		$RTN .= $this->theme->mk_hx('記事プレビュー')."\n";
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}else{
			$RTN .= '<p class="ttr error">記事は作成されていません。</p>'."\n";
		}

		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':edit_article.'.$this->req->pvelm(1) , array('label'=>'この記事を編集する','style'=>'inside') ).'</li>'."\n";
		if( intval( $article_info['status'] ) && time::datetime2int( $article_info['release_date'] ) <= time() ){
			$RTN .= '	<li>'.$this->theme->mk_link( ':send_tbp.'.$this->req->pvelm(1) , array('label'=>'TrackbackPingを送信する','style'=>'inside') ).'</li>'."\n";
		}
		$RTN .= '</ul>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";

		$RTN .= '<p class="ttrs alignR">'."\n";
		$RTN .= '	作成日:'.htmlspecialchars( $this->theme->dateformat( 'YmdHis' , time::datetime2int($article_info['create_date']) ) ).''."\n";
		$RTN .= '	更新日:'.htmlspecialchars( $this->theme->dateformat( 'YmdHis' , time::datetime2int($article_info['update_date']) ) ).''."\n";
		$RTN .= '</p>'."\n";

		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<div class="unit p">'."\n";
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':delete_article.'.$this->req->pvelm(1) , array('label'=>'この記事を削除する','style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '</div>'."\n";

		return	$RTN;

	}

	###################################################################################################################
	#	記事のトラックバック一覧
	function page_tb_list(){
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$trackback_count = $dao_visitor->get_trackback_allcount( $this->req->pvelm(1) );
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_list = $dao_trackback->get_trackback_alllist( $this->req->pvelm(1) );

		$SRCMEMO = '';
		foreach( $trackback_list as $Line ){
			$SRCMEMO .= $this->theme->mk_hr()."\n";
			$SRCMEMO .= $this->theme->mk_hx( $this->theme->mk_link( $Line['trackback_url'] , array( 'label'=>$Line['trackback_date'].' - '.$Line['trackback_title'] ) ) , null , array( 'allow_html'=>true ) )."\n";
			$SRCMEMO .= '<p>'.htmlspecialchars( $Line['trackback_blog_name'] ).'</p>'."\n";
			$SRCMEMO .= '<p>'.text::text2html( $Line['trackback_excerpt'] ).'</p>'."\n";
			$pid = ':tb_cst.'.$this->req->pvelm(1);
			$query = '';
			$query .= 'keystr='.urlencode( $Line['keystr'] );
			$query .= '&trackback_url='.urlencode( $Line['trackback_url'] );
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			if( $Line['status'] ){
				$query .= '&status=0';
				$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( $pid , array('additionalquery'=>$query,'label'=>'承認を取り消す') ).'</li>'."\n";
			}else{
				$query .= '&status=1';
				$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( $pid , array('additionalquery'=>$query,'label'=>'承認する') ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( ':tb_delete.'.$this->req->pvelm(1) , array('additionalquery'=>$query,'label'=>'削除する') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<p>'.intval($trackback_count[$this->req->pvelm(1)]).'件のトラックバックが登録されています。</p>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= $this->theme->mk_hr()."\n";
		}else{
			$RTN .= '<p class="ttr error">表示できるトラックバックありません。</p>'."\n";
		}

		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";

		return	$RTN;
	}

	###################################################################################################################
	#	トラックバックのステータス変更
	function start_tb_cst(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_tb_cst_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_tb_cst_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_tb_cst_confirm();
	}
	#--------------------------------------
	#	トラックバックのステータス変更：確認
	function page_tb_cst_confirm(){
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_info = $dao_trackback->get_trackback_info( $this->req->pvelm(1) , $this->req->in('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->req->in('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->req->in('trackback_url') ).'" />';
		$HIDDEN .= '<input type="hidden" name="status" value="'.htmlspecialchars( $this->req->in('status') ).'" />';

		$RTN .= '<p>'."\n";
		$RTN .= '	次のトラックバックのステータスを変更します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->mk_link( ':article.'.$this->req->pvelm(1) , array('label'=>$this->req->pvelm(1)) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>タイトル</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_title'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>リンク先URL</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_url'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ブログ名</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_blog_name'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>記事抜粋</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_excerpt'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿日時</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->dateformat( 'YmdHis' , $comment_info['trackback_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$btnlabel = 'ステータスを変更する';
		switch( intval( $this->req->in('status') ) ){
			case '0':
				$btnlabel = 'このトラックバックの承認を取り消す';
				break;
			case '1':
				$btnlabel = 'このトラックバックを承認する';
				break;
			default:
				return	$this->theme->errorend( '不明なステータスへの変更指示です。' );
				break;
		}
		$RTN .= '<p>'."\n";
		$RTN .= '	内容を確認し、間違いなければ、「'.htmlspecialchars( $btnlabel ).'」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="'.htmlspecialchars( $btnlabel ).'" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':tb_list.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':tb_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバックのステータス変更：実行
	function execute_tb_cst_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$obj_trackback = &$this->plog->factory_dao( 'trackback' );
		$result = $obj_trackback->update_trackback_status( $this->req->pvelm(1) , $this->req->in('keystr') , $this->req->in('trackback_url') , $this->req->in('status') );
		if( !$result ){
			return	'<p class="ttr error">トラックバック['.htmlspecialchars( $this->req->in('keystr') ).']のステータスの更新に失敗しました。</p>';
		}


		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	トラックバックのステータス変更：完了
	function page_tb_cst_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバックのステータス変更処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':tb_list.'.$this->req->pvelm(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':tb_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

	###################################################################################################################
	#	トラックバックの削除
	function start_tb_delete(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_tb_delete_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_tb_delete_execute();
		}
		return	$this->page_tb_delete_confirm();
	}
	#--------------------------------------
	#	トラックバックの削除：確認
	function page_tb_delete_confirm(){
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_info = $dao_trackback->get_trackback_info( $this->req->pvelm(1) , $this->req->in('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->req->in('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->req->in('trackback_url') ).'" />';

		$RTN .= '<p>次のトラックバックを削除します。</p>'."\n";

		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->mk_link( ':article.'.$this->req->pvelm(1) , array('label'=>$this->req->pvelm(1)) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>タイトル</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_title'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>リンク先URL</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_url'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ブログ名</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_blog_name'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>記事抜粋</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $trackback_info['trackback_excerpt'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿日時</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->dateformat( 'YmdHis' , $comment_info['trackback_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<p>このトラックバックを削除してよろしければ、「削除する」ボタンをクリックしてください。</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post" onsubmit="if( !confirm(\'この作業は取り消せません。本当によろしいですか？\') ){return false;}">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="削除する" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':tb_list.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':tb_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバックの削除：実行
	function execute_tb_delete_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		#	トラックバックDAOの生成
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$result = $dao_trackback->delete_trackback( $this->req->pvelm(1) , $this->req->in('keystr') , $this->req->in('trackback_url') );
		if( !$result ){
			return	'<p class="ttr error">トラックバックの削除に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	トラックバックの削除：完了
	function page_tb_delete_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバックの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':tb_list.'.$this->req->pvelm(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':tb_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	記事のコメント一覧
	function page_comment_list(){
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$comment_count = $dao_visitor->get_comment_allcount( $this->req->pvelm(1) );
		$obj_comment = &$this->plog->factory_dao( 'comment' );
		$comment_list = $obj_comment->get_comment_alllist( $this->req->pvelm(1) );

		$SRCMEMO = '';
		foreach( $comment_list as $Line ){
			if( !strlen( $Line['commentator_name'] ) ){
				$Line['commentator_name'] = 'No Name';
			}
			$SRCMEMO .= $this->theme->mk_hx( '<span class="date">'.htmlspecialchars( $this->theme->dateformat( 'YmdHis' , time::datetime2int( $Line['comment_date'] ) ) ).'</span> '.htmlspecialchars( $Line['commentator_name'] ) , null , array('allow_html'=>true) )."\n";
			$SRCMEMO .= '<p>'.preg_replace( '/\r\n|\r|\n/' , '<br />' , htmlspecialchars( $Line['comment'] ) ).'</p>'."\n";
			$pid = ':comment_cst.'.$this->req->pvelm(1);
			$query = '';
			$query .= 'keystr='.urlencode( $Line['keystr'] );
			$query .= '&create_date='.urlencode( $Line['create_date'] );
			$query .= '&client_ip='.urlencode( $Line['client_ip'] );
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			if( $Line['status'] ){
				$query .= '&status=0';
				$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( $pid , array('additionalquery'=>$query,'label'=>'承認を取り消す') ).'</li>'."\n";
			}else{
				$query .= '&status=1';
				$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( $pid , array('additionalquery'=>$query,'label'=>'承認する') ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li class="ttrs">'.$this->theme->mk_link( ':comment_delete.'.$this->req->pvelm(1) , array('additionalquery'=>$query,'label'=>'削除する') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= $this->theme->mk_hr()."\n";
		}

		$RTN = '';
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<p>'.intval($comment_count[$this->req->pvelm(1)]).'件のコメントが登録されています。</p>'."\n";
			$RTN .= $this->theme->mk_hr()."\n";
			$RTN .= $SRCMEMO;
		}else{
			$RTN .= '<p class="ttr error">表示できるコメントありません。</p>'."\n";
		}

		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";

		return	$RTN;
	}

	###################################################################################################################
	#	コメントのステータス変更
	function start_comment_cst(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_comment_cst_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_comment_cst_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_comment_cst_confirm();
	}
	#--------------------------------------
	#	コメントのステータス変更：確認
	function page_comment_cst_confirm(){
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$comment_info = $dao_comment->get_comment_info( $this->req->pvelm(1) , $this->req->in('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->req->in('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="create_date" value="'.htmlspecialchars( $this->req->in('create_date') ).'" />';
		$HIDDEN .= '<input type="hidden" name="client_ip" value="'.htmlspecialchars( $this->req->in('client_ip') ).'" />';
		$HIDDEN .= '<input type="hidden" name="status" value="'.intval( $this->req->in('status') ).'" />';

		$RTN .= '<p>'."\n";
		$RTN .= '	次のコメントのステータスを変更します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->mk_link( ':article.'.$this->req->pvelm(1) , array('label'=>$this->req->pvelm(1)) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		if( $this->plog->comment_userinfo_name ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>お名前</div></th>'."\n";
			$RTN .= '		<td style="width:70%;">'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $comment_info['commentator_name'] ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_url ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>URL</div></th>'."\n";
			$RTN .= '		<td style="width:70%;">'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $comment_info['commentator_url'] ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>コメント</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$dao_comment->view_comment2html( $comment_info['comment'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿日時</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->dateformat( 'YmdHis' , $comment_info['comment_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$btnlabel = 'ステータスを変更する';
		switch( intval( $this->req->in('status') ) ){
			case '0':
				$btnlabel = 'このコメントの承認を取り消す';
				break;
			case '1':
				$btnlabel = 'このコメントを承認する';
				break;
			default:
				return	$this->theme->errorend( '不明なステータスへの変更指示です。' );
				break;
		}
		$RTN .= '<p>'."\n";
		$RTN .= '	内容を確認し、間違いなければ、「'.htmlspecialchars( $btnlabel ).'」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="'.htmlspecialchars( $btnlabel ).'" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':comment_list.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':comment_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントのステータス変更：実行
	function execute_comment_cst_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$obj_comment = &$this->plog->factory_dao( 'comment' );
		$result = $obj_comment->update_comment_status( $this->req->pvelm(1) , $this->req->in('keystr') , $this->req->in('create_date') , $this->req->in('client_ip') , $this->req->in('status') );
		if( !$result ){
			return	'<p class="ttr error">コメント['.htmlspecialchars( $this->req->in('keystr') ).']のステータスの更新に失敗しました。</p>';
		}


		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	コメントのステータス変更：完了
	function page_comment_cst_thanks(){
		$RTN = '';
		$RTN .= '<p>コメントのステータス変更処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':comment_list.'.$this->req->pvelm(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':comment_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

	###################################################################################################################
	#	コメントの削除
	function start_comment_delete(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_comment_delete_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_comment_delete_execute();
		}
		return	$this->page_comment_delete_confirm();
	}
	#--------------------------------------
	#	コメントの削除：確認
	function page_comment_delete_confirm(){
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$comment_info = $dao_comment->get_comment_info( $this->req->pvelm(1) , $this->req->in('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->req->in('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="create_date" value="'.htmlspecialchars( $this->req->in('create_date') ).'" />';
		$HIDDEN .= '<input type="hidden" name="client_ip" value="'.htmlspecialchars( $this->req->in('client_ip') ).'" />';

		$RTN .= '<p>次のコメントを削除します。</p>'."\n";

		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->mk_link( ':article.'.$this->req->pvelm(1) , array('label'=>$this->req->pvelm(1)) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		if( $this->plog->comment_userinfo_name ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>お名前</div></th>'."\n";
			$RTN .= '		<td style="width:70%;">'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $comment_info['commentator_name'] ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_url ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>URL</div></th>'."\n";
			$RTN .= '		<td style="width:70%;">'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $comment_info['commentator_url'] ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>コメント</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$dao_comment->view_comment2html( $comment_info['comment'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿日時</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->theme->dateformat( 'YmdHis' , $comment_info['comment_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<p>このコメントを削除してよろしければ、「削除する」ボタンをクリックしてください。</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post" onsubmit="if( !confirm(\'この作業は取り消せません。本当によろしいですか？\') ){return false;}">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="削除する" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':comment_list.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':comment_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの削除：実行
	function execute_comment_delete_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		#	コメントDAOの生成
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$result = $dao_comment->delete_comment( $this->req->pvelm(1) , $this->req->in('keystr') );
		if( !$result ){
			return	'<p class="ttr error">コメントの削除に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	コメントの削除：完了
	function page_comment_delete_thanks(){
		$RTN = '';
		$RTN .= '<p>コメントの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':comment_list.'.$this->req->pvelm(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':comment_list.'.$this->req->pvelm(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	記事作成・編集
	function start_edit_article(){
		$error = $this->check_edit_article_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_edit_article_thanks();
		}elseif( $this->req->in('mode') == 'imagepreview' ){
			return	$this->page_edit_article_imagepreview();
		}elseif( $this->req->in('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_article_confirm();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_article_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
			$this->req->delete_uploadfile_all();
			if( $this->req->pvelm() == 'edit_article' ){
				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$article_info = $dao_admin->get_article_info( $this->req->pvelm(1) );
				if( !is_array( $article_info ) || !count( $article_info ) ){
					#	記事が存在しません。
					return	$this->theme->printnotfound();
				}
				$this->req->setin( 'article_title' , $article_info['article_title'] );
				$this->req->setin( 'article_summary' , $article_info['article_summary'] );
				$this->req->setin( 'status' , $article_info['status'] );
				$this->req->setin( 'category_cd' , $article_info['category_cd'] );
				$this->req->setin( 'release_date' , $article_info['release_date'] );

//				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$this->req->setin( 'contents' , $dao_admin->get_contents_src( $this->req->pvelm(1) ) );

				$file_name_list = $dao_admin->get_contents_image_list( $this->req->pvelm(1) );
				if( is_array( $file_name_list ) ){
					foreach( $file_name_list as $filename ){
						if( $filename == '.' || $filename == '..' ){ continue; }
						$this->req->save_uploadfile(
							$filename ,
							array(
								'name'=>$filename ,
								'type'=>'image/jpeg' ,
								'content'=>$dao_admin->load_contents_image( $this->req->pvelm(1) , $filename ) ,
							)
						);
					}
				}

			}
		}
		return	$this->page_edit_article_input( $error );
	}
	#--------------------------------------
	#	記事作成・編集：画像プレビュー
	function page_edit_article_imagepreview(){
		$filename = $this->req->in('preview_image_name');
		$image_info = $this->req->get_uploadfile( $filename );
		if( !strlen( $image_info['name'] ) ){
			return	$this->theme->printnotfound();
		}

		$mime = null;
		switch( strtolower( $this->dbh->get_extension( $filename ) ) ){
			case 'jpg':
			case 'jpeg':
			case 'jpe':
				$mime = 'image/jpeg';
				break;
			case 'gif':
				$mime = 'image/gif';
				break;
			case 'png':
				$mime = 'image/png';
				break;
			case 'bmp':
				$mime = 'image/bmp';
				break;
			case 'mm':
			case 'xml':
			case 'rdf':
			case 'rss':
				$mime = 'application/xml';
				break;
			default:
				$mime = 'text/plain';
				break;
		}

		return	$this->theme->download( $image_info['content'] , array('filename'=>$image_info['name'],'content-type'=>$mime) );
	}
	#--------------------------------------
	#	記事作成・編集：入力
	function page_edit_article_input( $error ){
		$RTN = '';

		if( count( $error ) ){
			$RTN .= '<p class="ttr error">'.count( $error ).'件の入力エラーがあります。もう一度ご確認ください。</p>'."\n";
		}else{
			$RTN .= '<p>'."\n";
			if( $this->req->pvelm() == 'edit_article' ){
				$RTN .= '	記事を編集して、「確認する」ボタンをクリックしてください。<br />'."\n";
			}else{
				$RTN .= '	新しい記事を作成します。記事の内容を編集したら、「確認する」ボタンをクリックしてください。<br />'."\n";
			}
			$RTN .= '</p>'."\n";
		}


		$form_enctype = '';
		if( !$this->user->is_mp() ){ $form_enctype = ' enctype="multipart/form-data"'; }
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post"'.$form_enctype.' name="editForm">'."\n";

		$RTN .= $this->theme->mk_hx('基本情報')."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		if( $this->req->pvelm() == 'edit_article' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>記事番号</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->req->pvelm(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>タイトル <span class="must">*</span></div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="article_title" value="'.htmlspecialchars( $this->req->in('article_title') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['article_title'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['article_title'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$c = array($this->req->in('category_cd')=>' selected="selected"');
		$RTN .= '				<select name="category_cd">'."\n";
		$RTN .= '					<option value="0"'.$c['0'].'>(選択しない)</option>'."\n";
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$category_list = $dao_visitor->get_category_list();
		foreach( $category_list as $line ){
			$RTN .= '					<option value="'.intval($line['category_cd']).'"'.$c[intval($line['category_cd'])].'>'.htmlspecialchars($line['category_title']).'</option>'."\n";
		}
		$RTN .= '				</select>'."\n";
		$RTN .= '			</div>'."\n";
		if( strlen( $error['category_cd'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['category_cd'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>公開日</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.$this->theme->mk_form_select_date( 'input' , array( 'default'=>$this->req->in('release_date'),'max_year'=>date('Y')+3 ) ).'</div>'."\n";
		if( strlen( $error['release_date'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['release_date'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ステータス</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$c = array( intval( $this->req->in('status') )=>' checked="checked"' );
		$RTN .= '				<input type="radio" name="status" id="status_0" value="0"'.$c[0].' /><label for="status_0">執筆中</label>'."\n";
		$RTN .= '				<input type="radio" name="status" id="status_1" value="1"'.$c[1].' /><label for="status_1">公開中</label>'."\n";
		$RTN .= '			</div>'."\n";
		if( strlen( $error['status'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['status'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		if( $this->plog->article_summary_mode == 'manual' ){
			$RTN .= $this->theme->mk_hx('サマリ')."\n";
			if( strlen( $error['article_summary'] ) ){
				$RTN .= '<div class="ttr error">'.$error['article_summary'].'</div>'."\n";
			}
			$RTN .= '<p><textarea name="article_summary" rows="7" cols="20" class="inputitems">'.htmlspecialchars( $this->req->in('article_summary') ).'</textarea></p>'."\n";
		}

		$RTN .= $this->theme->mk_hx('内容 <span class="must">*</span>',null,array('allow_html'=>true))."\n";
		if( strlen( $error['contents'] ) ){
			$RTN .= '<div class="ttr error">'.$error['contents'].'</div>'."\n";
		}
		$RTN .= '<p><textarea name="contents" rows="11" cols="20" class="inputitems">'.htmlspecialchars( $this->req->in('contents') ).'</textarea></p>'."\n";

		$RTN .= $this->theme->mk_hx('画像')."\n";

		$tmp_image_list = $this->req->get_uploadfile_list();
		$IMAGE_SRC = '';
		#	既にアップロードされた画像の一覧
		foreach( $tmp_image_list as $image_key ){
			$image_info = $this->req->get_uploadfile( $image_key );
			$IMAGE_SRC .= '	<tr>'."\n";
			$IMAGE_SRC .= '		<td width="30%" class="alignC">'."\n";
			$tmp_ext = strtolower( $this->dbh->get_extension( $image_key ) );
			switch( $tmp_ext ){
				case 'gif':
				case 'png':
				case 'jpg':
				case 'jpe':
				case 'jpeg':
				case 'bmp':
					$IMAGE_SRC .= '			<div><img src="'.htmlspecialchars( $this->theme->href( $this->req->p() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.urlencode($image_key) ) ) ).'" width="100" alt="" /></div>'."\n";
					break;
				default:
					#	画像ファイルと認識できない拡張子がついていたら
					#	リサイズとかはしないで、ダウンロードファイルとして取り扱う。
					$IMAGE_SRC .= '			<div>'.htmlspecialchars( $tmp_ext ).'形式の添付ファイル</div>'."\n";
					break;
			}
			$IMAGE_SRC .= '		</td>'."\n";
			$IMAGE_SRC .= '		<td>'."\n";
			$IMAGE_SRC .= '			<div>'.htmlspecialchars($image_key).'</div>'."\n";
			if( !$this->user->is_mp() ){
				$IMAGE_SRC .= '			<input type="file" name="'.htmlspecialchars($this->exchange_edit_article_key('imagefile:'.$image_key)).'" value="" /><br />'."\n";
				$IMAGE_SRC .= '			<input type="checkbox" name="'.htmlspecialchars($this->exchange_edit_article_key('deleteimage:'.$image_key)).'" id="deleteimage:'.htmlspecialchars($this->exchange_edit_article_key($image_key)).'" value="1" /><label for="deleteimage:'.htmlspecialchars($this->exchange_edit_article_key($image_key)).'">削除する</label><br />'."\n";
			}
			$IMAGE_SRC .= '		</td>'."\n";
			$IMAGE_SRC .= '	</tr>'."\n";
		}

		if( strlen( $IMAGE_SRC ) ){
			$RTN .= '<table width="100%" class="deftable">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th class="ttr alignC">サムネイル</th>'."\n";
			$RTN .= '			<th class="ttr alignC">画像名</th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			$RTN .= $IMAGE_SRC;
			$RTN .= '</table>'."\n";
		}
		if( !$this->user->is_mp() ){
			#	新しい画像をアップロードする
			$RTN .= $this->theme->mk_hx( '新しい画像をアップロード' , -1 )."\n";
			$RTN .= '<p>'."\n";
			$RTN .= '	<input type="file" name="new_image" value="" /><br />'."\n";
			$RTN .= '	ファイル名：<input type="text" name="new_imagename" value="" /><br />'."\n";
			if( strlen( $error['new_image'] ) ){
				$RTN .= '	<span class="error">'.$error['new_image'].'</span><br />'."\n";
			}
			$RTN .= '</p>'."\n";
		}

		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /><input type="submit" name="s" value="画像を反映" onclick="document.editForm.mode.value=\'input\';return true;" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	記事作成・編集：確認
	function page_edit_article_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	編集した内容を確認してください。これでよろしければ、「保存する」ボタンをクリックして、作業を完了してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= $this->theme->mk_hx('基本情報')."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		if( $this->req->pvelm() == 'edit_article' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>記事番号</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->req->pvelm(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>タイトル <span class="must">*</span></div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('article_title') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="article_title" value="'.htmlspecialchars( $this->req->in('article_title') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$category_list = $dao_visitor->get_category_list();
		$category_title = '(選択しない)';
		foreach( $category_list as $line ){
			if( $line['category_cd'] == $this->req->in('category_cd') ){
				$category_title = $line['category_title'];
				break;
			}
		}
		$RTN .= '			<div>'.htmlspecialchars( $category_title ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_cd" value="'.intval( $this->req->in('category_cd') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>公開日</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.$this->theme->mk_form_select_date( 'confirm' , array( 'default'=>$this->req->in('release_date'),'max_year'=>date('Y')+3 ) ).'</div>'."\n";
		$HIDDEN .= $this->theme->mk_form_select_date( 'hidden' , array( 'default'=>$this->req->in('release_date'),'max_year'=>date('Y')+3 ) );
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ステータス</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$status = array( '執筆中' , '公開中' );
		$RTN .= '			<div>'.$status[intval($this->req->in('status'))].'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="status" value="'.htmlspecialchars( $this->req->in('status') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		if( $this->plog->article_summary_mode == 'manual' ){
			$RTN .= $this->theme->mk_hx('サマリ')."\n";
			$RTN .= '<blockquote class="sourcecode"><div>'.htmlspecialchars( $this->req->in('article_summary') ).'</div></blockquote>'."\n";
			$HIDDEN .= '<input type="hidden" name="article_summary" value="'.htmlspecialchars( $this->req->in('article_summary') ).'" />';
		}

		$RTN .= $this->theme->mk_hx('内容プレビュー',null,array('allow_html'=>true))."\n";

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content_preview( $this->req->in('contents') );
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}else{
			$RTN .= '<p class="ttr error">記事は作成されていません。</p>'."\n";
		}

		$RTN .= $this->theme->mk_hx('ソース',null,array('allow_html'=>true))."\n";
		$RTN .= '<blockquote class="sourcecode"><div>'.htmlspecialchars( $this->req->in('contents') ).'</div></blockquote>'."\n";
		$HIDDEN .= '<input type="hidden" name="contents" value="'.htmlspecialchars( $this->req->in('contents') ).'" />';

		$RTN .= $this->theme->mk_hx('画像')."\n";
		$tmp_image_list = $this->req->get_uploadfile_list();
		$IMAGE_SRC = '';
		foreach( $tmp_image_list as $image_key ){
			$image_info = $this->req->get_uploadfile( $image_key );
			$IMAGE_SRC .= '	<tr>'."\n";
			$IMAGE_SRC .= '		<td width="30%" class="alignC">'."\n";
			$tmp_ext = strtolower( $this->dbh->get_extension( $image_key ) );
			switch( $tmp_ext ){
				case 'gif':
				case 'png':
				case 'jpg':
				case 'jpe':
				case 'jpeg':
				case 'bmp':
					$IMAGE_SRC .= '			<div><img src="'.htmlspecialchars( $this->theme->href( $this->req->p() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.urlencode($image_key) ) ) ).'" width="100" alt="" /></div>'."\n";
					break;
				default:
					#	画像ファイルと認識できない拡張子がついていたら
					#	リサイズとかはしないで、ダウンロードファイルとして取り扱う。
					$IMAGE_SRC .= '			<div>'.htmlspecialchars( $tmp_ext ).'形式の添付ファイル</div>'."\n";
					break;
			}
			$IMAGE_SRC .= '		</td>'."\n";
			$IMAGE_SRC .= '		<td>'."\n";
			$IMAGE_SRC .= '			<div>'.htmlspecialchars( $image_info['name'] ).'</div>'."\n";
			$IMAGE_SRC .= '		</td>'."\n";
			$IMAGE_SRC .= '	</tr>'."\n";
		}
		if( strlen( $IMAGE_SRC ) ){
			$RTN .= '<table width="100%" class="deftable">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th class="ttr alignC">サムネイル</th>'."\n";
			$RTN .= '			<th class="ttr alignC">画像名</th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			$RTN .= $IMAGE_SRC;
			$RTN .= '</table>'."\n";
		}else{
			$RTN .= '<p>画像は登録されていません。</p>'."\n";
		}

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post" onsubmit="document.getElementById(\'cont_saveform\').disabled=true;return true;">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="保存する" id="cont_saveform" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		if( $this->req->pvelm() == 'edit_article' ){
			$cancel_pid = ':article.'.$this->req->pvelm(1);
		}else{
			$cancel_pid = ':article_list';
		}
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( $cancel_pid ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( $cancel_pid )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	記事作成・編集：チェック
	function check_edit_article_check(){
		$RTN = array();

		#--------------------------------------
		#	新しい画像の登録
		$new_image_file_info = $this->req->in('new_image');
		if( strlen( $new_image_file_info['name'] ) ){
			$imagename = $this->req->in('new_imagename');
			if( !strlen( $imagename ) ){
				$imagename = $new_image_file_info['name'];
			}
			$new_image_file_info['name'] = $imagename;

			if( !strlen( $imagename ) ){
				#	ファイル名エラー
				$RTN['new_image'] = 'ファイル名が取得できません。';
			}elseif( !preg_match( '/^[a-zA-Z0-9\.\_\-]+\.(?:[a-zA-Z0-9\-\_]+)$/is' , $imagename ) ){
				#	ファイル名エラー
				$RTN['new_image'] = 'ファイル名が不正です。';
			}else{
				$this->req->save_uploadfile( $imagename , $new_image_file_info );
			}
		}
		unset($new_image_file_info);

		#--------------------------------------
		#	既存画像の処理
		$tmp_image_list = $this->req->get_uploadfile_list();
		foreach( $tmp_image_list as $image_key ){
			if( $this->req->in($this->exchange_edit_article_key( 'deleteimage:'.$image_key )) ){
				#	画像削除
				$this->req->delete_uploadfile( $image_key );
				continue;
			}
			$uploaded_image = $this->req->in($this->exchange_edit_article_key('imagefile:'.$image_key));
			if( !strlen( $uploaded_image['name'] ) ){
				continue;
			}
			$uploaded_image['name'] = $image_key;
			$this->req->save_uploadfile( $uploaded_image['name'] , $uploaded_image );
			unset( $uploaded_image );
		}
		unset( $tmp_image_list );

		if( !strlen( $this->req->in('article_title') ) ){
			$RTN['article_title'] = 'タイトルを入力してください。';
		}elseif( preg_match( '/\r\n|\r|\n/si' ,  $this->req->in('article_title') ) ){
			$RTN['article_title'] = 'タイトルに改行を含めることはできません。';
		}elseif( strlen( $this->req->in('article_title') ) > 124 ){
			$RTN['article_title'] = 'タイトルが長すぎます。('.strlen( $this->req->in('article_title') ).'/124)';
		}

		if( !strlen( $this->req->in('category_cd') ) ){
			$RTN['category_cd'] = 'カテゴリを選択してください。';
		}elseif( !preg_match( '/^[0-9]+$/si' ,  $this->req->in('category_cd') ) ){
			$RTN['category_cd'] = 'カテゴリの形式が不正です。';
		}

		if( !strlen( $this->req->in('contents') ) ){
			$RTN['contents'] = '内容を入力してください。';
		}

		return	$RTN;
	}
	#--------------------------------------
	#	記事作成・編集：実行
	function execute_edit_article_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$this->req->setin( 'release_date' , $this->theme->mk_form_select_date( 'get_datetime' , array('max_year'=>date('Y')+3) ) );//公開日を反映

		$dao_admin = &$this->plog->factory_dao( 'admin' );

		if( $this->req->pvelm() == 'create_article' ){
			#--------------------------------------
			#	新規作成の処理

			$result = $dao_admin->create_article(
				$this->req->in('article_title') ,
				$this->req->in('status') ,
				$this->req->in('contents') ,
				array(
					'article_summary'=>$this->req->in('article_summary') ,
					'user_cd'=>intval( $this->user->getusercd() ) ,
					'release_date'=>$this->req->in('release_date') ,
					'category_cd'=>intval( $this->req->in('category_cd') ) ,
				)
			);
			if( $result === false ){
				return	'<p class="ttr error">記事の作成に失敗しました。</p>';
			}
			$article_cd = $result;


		}elseif( $this->req->pvelm() == 'edit_article' && strlen( $this->req->pvelm(1) ) ){
			#--------------------------------------
			#	既存編集の処理

			$result = $dao_admin->update_article(
				$this->req->pvelm(1) ,
				$this->req->in('article_title') ,
				$this->req->in('status') ,
				$this->req->in('contents') ,
				array(
					'article_summary'=>$this->req->in('article_summary') ,
					'release_date'=>$this->req->in('release_date') ,
					'category_cd'=>intval( $this->req->in('category_cd') ) ,
				)
			);
			if( $result === false ){
				return	'<p class="ttr error">記事の保存に失敗しました。</p>';
			}
			$article_cd = $this->req->pvelm(1);


		}else{
			#--------------------------------------
			#	どちらでもなければエラー
			$errorMsg = '不明な命令です。['.$this->req->pvelm().']';
			$this->errors->error_log( $errorMsg , __FILE__ , __LINE__ );
			return	'<p class="ttr error">'.htmlspecialchars( $errorMsg ).'</p>';
		}


		#--------------------------------------
		#	画像を一旦削除
		$file_name_list = $dao_admin->get_contents_image_list( $article_cd );
		if( is_array( $file_name_list ) ){
			foreach( $file_name_list as $filename ){
				if( $filename == '.' || $filename == '..' ){ continue; }
				$dao_admin->delete_contents_image( $article_cd , $filename );
			}
		}


		#--------------------------------------
		#	画像を保存
		$tmp_image_list = $this->req->get_uploadfile_list();
		if( is_array( $tmp_image_list ) ){
			foreach( $tmp_image_list as $filename ){
				$fileinfo = $this->req->get_uploadfile( $filename );
				$dao_admin->save_contents_image(
					$article_cd ,
					$fileinfo['name'] ,
					$fileinfo['content']
				);
			}
		}

		#	アップロード画像のメモをクリア
		$this->req->delete_uploadfile_all();

		#	記事インデックスの更新
		$result = $dao_admin->update_article_index( $article_cd );
		if( !$result ){
			return	'<p class="ttr error">記事インデックスの更新に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks&article_cd='.$article_cd );
	}
	#--------------------------------------
	#	キーに使える文字列に変換
	function exchange_edit_article_key( $key ){
		$key = preg_replace( '/\./' , '_' , $key );
		return	$key;
	}
	#--------------------------------------
	#	記事作成・編集：完了
	function page_edit_article_thanks(){
		$RTN = '';
		if( $this->req->pvelm() == 'edit_article' ){
			$next_message = '記事の編集を保存しました。';
			$next_pid = ':article.'.$this->req->pvelm(1);
		}else{
			$next_message = '新しい記事を作成しました。';
			$next_pid = ':article.'.$this->req->in('article_cd');
		}
		$RTN .= '<p>'.htmlspecialchars($next_message).'</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( $next_pid ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( $next_pid )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}




	###################################################################################################################
	#	トラックバック送信
	function start_send_tbp(){
		if( !strlen( $this->req->pvelm(1) ) ){
			#	記事が指定されていません。
			return	$this->theme->printnotfound();
		}
		$dao_admin = &$this->plog->factory_dao('admin');
		$article_info = $dao_admin->get_article_info( $this->req->pvelm(1) );
		if( !is_array( $article_info ) && !count( $article_info ) ){
			#	記事が存在しません。
			return	$this->theme->printnotfound();
		}

		$error = $this->check_send_tbp_check( $article_info );
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_send_tbp_thanks();
		}elseif( $this->req->in('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_send_tbp_confirm( $article_info );
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_send_tbp_execute( $article_info );
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
			$this->req->setin( 'article_summary' , $article_info['article_summary'] );
		}
		return	$this->page_send_tbp_input( $article_info , $error );
	}
	#--------------------------------------
	#	トラックバック送信：入力
	function page_send_tbp_input( $article_info , $error ){
		$RTN = '';

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '<div>'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ブログ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->blog_name ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>記事タイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $article_info['article_title'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>記事URL</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->get_article_url( $this->req->pvelm(1) ) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('トラックバック先URI')."\n";
		$RTN .= '<p><textarea name="trackback_url" class="inputitems" rows="5" cols="20">'.htmlspecialchars( $this->req->in('trackback_url') ).'</textarea></p>'."\n";
		if( strlen( $error['trackback_url'] ) ){
			$RTN .= '<p class="ttr error">'.$error['trackback_url'].'</p>'."\n";
		}

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $this->req->pvelm(1) );

		$RTN .= $this->theme->mk_hx('サマリ')."\n";
		$article_summary = $this->req->in('article_summary');
		if( !strlen( $article_summary ) ){
			$article_summary = strip_tags( $ARTICLE_BODY_SRC );
			$article_summary = trim( $article_summary );
			$article_summary = preg_replace( '/(\r\n|\r|\n)\t+/is' , '$1' , $article_summary );
			$article_summary = preg_replace( '/(?:\r\n|\r|\n)+/is' , "\n" , $article_summary );
			$article_summary = mb_strimwidth( $article_summary , 0 , 256 , '...');
		}
		$RTN .= '			<div class="p">'."\n";
		$RTN .= '			<div><textarea name="article_summary" class="inputitems" rows="5" cols="20">'.htmlspecialchars( $article_summary ).'</textarea></div>'."\n";
		if( strlen( $error['article_summary'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['article_summary'].'</div>'."\n";
		}
		$RTN .= '			</div>'."\n";

		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /></p>'."\n";

		$RTN .= $this->theme->mk_hx('記事プレビュー')."\n";
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}else{
			$RTN .= '<p class="ttr error">記事は作成されていません。</p>'."\n";
		}


		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバック送信：確認
	function page_send_tbp_confirm( $article_info ){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	送信するトラックバックピングの内容が間違いないかご確認ください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ブログ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->blog_name ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>記事タイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $article_info['article_title'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>記事URL</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->get_article_url( $this->req->pvelm(1) ) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('トラックバック先URI')."\n";
		$tburl_list = preg_split( '/\r\n|\r|\n/' , $this->req->in('trackback_url') );
		$SRCMEMO = '';
		foreach( $tburl_list as $tburl ){
			if( !strlen( trim( $tburl ) ) ){ continue; }
			$SRCMEMO .= '	<li>'.htmlspecialchars( trim( $tburl ) ).'</li>'."\n";
		}
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<ul>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</ul>'."\n";
		}
//		$RTN .= '<p>'.text::text2html( $this->req->in('trackback_url') ).'</p>'."\n";
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->req->in('trackback_url') ).'" />';

		$RTN .= $this->theme->mk_hx('サマリ')."\n";
		$RTN .= '<p>'.text::text2html( $this->req->in('article_summary') ).'</p>'."\n";
		$HIDDEN .= '<input type="hidden" name="article_summary" value="'.htmlspecialchars( $this->req->in('article_summary') ).'" />';

		$RTN .= $this->theme->mk_hr()."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	この操作は、相手方のブログへ通知されます。一度送信したトラックバックピングの処理は、<strong>取り消すことができません</strong>。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	もう一度よく確認し、問題がなければ、「トラックバックピングを送信する」をクリックして、トラックバックピング送信を完了してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="トラックバックピングを送信する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':article.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':article.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバック送信：チェック
	function check_send_tbp_check( $article_info ){
		$RTN = array();
		if( !strlen( $this->req->in('trackback_url') ) ){
			$RTN['trackback_url'] = 'トラックバック先URIが設定されていません。';
		}
		if( !strlen( $this->req->in('article_summary') ) ){
			$RTN['article_summary'] = 'サマリは必ず指定してください。';
		}
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバック送信：実行
	function execute_send_tbp_execute( $article_info ){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		if( !$this->plog->enable_trackback ){
			return	'<p class="ttr error">トラックバック機能が無効になっています。</p>';
		}

		#--------------------------------------
		#	TrackbackPing送信先の一覧を作成する
		$trackback_url_list = preg_split( '/\r\n|\r|\n/' , $this->req->in('trackback_url') );
		foreach( $trackback_url_list as $key=>$val ){
			$trackback_url_list[$key] = trim($val);
			if( !strlen($val) ){
				unset( $trackback_url_list[$key] );
			}
		}
		#	/ TrackbackPing送信先の一覧を作成する
		#--------------------------------------

		$className = $this->plog->require_lib('/PLOG/resources/tbp.php');
		if( !$className ){
			return	'<p class="ttr error">TrackbackPingクラスのロードに失敗しました。</p>';
		}
		$tbp = new $className( &$this->plog , &$this->conf , &$this->dbh , &$this->theme );

		#--------------------------------------
		#	TrackbackPingを送信する
		$done = array();
		$done_ok = array();
		$done_ng = array();
		$done_errors = array();
		foreach( $trackback_url_list as $tbp_url ){
			if( $done[$tbp_url] === true ){ continue; }
			$done[$tbp_url] = true;

			#	TrackbackPingを送信する。
			$result = $tbp->send_trackback_ping(
				$this->req->pvelm(1) ,
				$tbp_url ,
				$this->plog->get_article_url( $this->req->pvelm(1) ) ,
				$article_info['article_title'] ,
				$this->req->in('article_summary') ,
				$this->plog->blog_name
			);

			if( !$result ){
				#	失敗
				array_push( $done_ng , $tbp_url );
				array_push( $done_errors , $tbp->get_internal_error_list() );
			}else{
				#	成功
				array_push( $done_ok , $tbp_url );
			}

		}


		#--------------------------------------
		#	今回のトラックバックPING送信のログを、ファイルに保存する。
		$dao_tb = $this->plog->factory_dao( 'trackback' );
		$result = $dao_tb->tbp_sendlog( $tbp->get_tbp_sendlog() );
#		if( !$result ){
#			return	'<p class="ttr error">トラックバックPING送信ログを保存できませんでした。</p>';
#		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks&count='.count($done).'&ok_list='.urlencode(implode("\n",$done_ok)).'&ng_list='.urlencode(implode("\n",$done_ng)) );
	}
	#--------------------------------------
	#	トラックバック送信：完了
	function page_send_tbp_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバック送信処理を完了しました。</p>'."\n";
		$RTN .= '<p>総数：'.$this->req->in('count').'</p>'."\n";

		$RTN .= $this->theme->mk_hx('OK')."\n";
		$LIST_VIEW = preg_split( '/\r\n|\r|\n/' , $this->req->in('ok_list') );
		$SRC_MEMO = '';
		foreach( $LIST_VIEW as $Line ){
			$Line = trim( $Line );
			if( !strlen( $Line ) ){ continue; }
			$SRC_MEMO .= '	<li>'.htmlspecialchars( $Line ).'</li>'."\n";
		}
		if( strlen( $SRC_MEMO ) ){
			$RTN .= '<ul>'."\n";
			$RTN .= $SRC_MEMO;
			$RTN .= '</ul>'."\n";
		}else{
			$RTN .= '<p>該当する項目はありません</p>'."\n";
		}

		$RTN .= $this->theme->mk_hx('NG')."\n";
		$LIST_VIEW = preg_split( '/\r\n|\r|\n/' , $this->req->in('ng_list') );
		$SRC_MEMO = '';
		foreach( $LIST_VIEW as $Line ){
			$Line = trim( $Line );
			if( !strlen( $Line ) ){ continue; }
			$SRC_MEMO .= '	<li>'.htmlspecialchars( $Line ).'</li>'."\n";
		}
		if( strlen( $SRC_MEMO ) ){
			$RTN .= '<ul>'."\n";
			$RTN .= $SRC_MEMO;
			$RTN .= '</ul>'."\n";
		}else{
			$RTN .= '<p>該当する項目はありません</p>'."\n";
		}

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':article.'.$this->req->pvelm(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':article.'.$this->req->pvelm(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	###################################################################################################################
	#	記事を削除する(論理削除)
	function start_delete_article(){
		$error = $this->check_delete_article_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_delete_article_thanks();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_delete_article_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_delete_article_confirm();
	}
	#--------------------------------------
	#	記事を削除する(論理削除)：確認
	function page_delete_article_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>記事['.htmlspecialchars( $this->req->pvelm(1) ).']を削除します。</p>'."\n";
		$RTN .= '<p>よろしいですか？</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':article.'.$this->req->pvelm(1) ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':article.'.$this->req->pvelm(1) )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	記事を削除する(論理削除)：チェック
	function check_delete_article_check(){
		$RTN = array();
		return	$RTN;
	}
	#--------------------------------------
	#	記事を削除する(論理削除)：実行
	function execute_delete_article_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_admin = &$this->plog->factory_dao( 'admin' );
		if( !$dao_admin->delete_article( $this->req->pvelm(1) ) ){
			return	'<p class="ttr error">記事の削除に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	記事を削除する(論理削除)：完了
	function page_delete_article_thanks(){
		$RTN = '';
		$RTN .= '<p>記事['.htmlspecialchars( $this->req->pvelm(1) ).']を削除しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':article_list' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':article_list' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}





	###################################################################################################################
	#	カテゴリ一覧
	function page_category(){
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$category_list = $dao_admin->get_category_list();
		$SRC_LIST = '';
		if( is_array( $category_list ) && count( $category_list ) ){
			$SRC_LIST .= '<!-- category_list -->'."\n";
			$SRC_LIST .= $this->page_category_get_pagelist( $category_list )."\n";
			$SRC_LIST .= '<!-- /category_list -->'."\n";
		}

		$MENU = '';
		$MENU .= '<ul class="horizontal">'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':create_category' , array( 'label'=>'新規カテゴリ作成','style'=>'inside' ) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->theme->mk_link( ':make_categories_flat' , array( 'label'=>'階層構造のリセット','style'=>'inside' ) ).'</li>'."\n";
		$MENU .= '</ul>'."\n";

		$RTN = '';
		if( strlen( $SRC_LIST ) ){
			$RTN .= $SRC_LIST;
		}else{
			$RTN .= '<p>カテゴリは登録されていません。</p>'."\n";
		}
		$RTN .= $MENU;
		return	$RTN;
	}
	function page_category_get_pagelist( $category_list , $parent_category = 0 , $depth = 0 ){
		$RTN = '';
		$MEMO = '';
		if( $depth > 20 ){ return ''; }
		if( is_array( $category_list ) && count( $category_list ) ){
			foreach( $category_list as $category_info ){
				if( $parent_category != $category_info['parent_category_cd'] ){ continue; }
				$MEMO .= '<li>'.$this->theme->mk_link( ':edit_category.'.$category_info['category_cd'] , array( 'label'=>$category_info['category_title'] ) );
				$MEMO .= $this->page_category_get_pagelist( $category_list , $category_info['category_cd'] , $depth + 1 );
				$MEMO .= '</li>';
			}
		}
		if( strlen( $MEMO ) ){
			$RTN .= '<ul>';
			$RTN .= $MEMO;
			$RTN .= '</ul>';
		}
		return	$RTN;
	}





	###################################################################################################################
	#	カテゴリの作成・編集
	function start_edit_category(){
		$error = $this->check_edit_category_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_edit_category_thanks();
		}elseif( $this->req->in('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_category_confirm();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_category_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
			if( $this->req->pvelm() == 'edit_category' ){
				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$category_info = $dao_admin->get_category_info( $this->req->pvelm(1) );
				if( !is_array($category_info) ){
					return	'<p class="ttr error">指定されたカテゴリは存在しません。</p>';
				}
				$this->req->setin( 'category_title' , $category_info['category_title'] );
				$this->req->setin( 'category_subtitle' , $category_info['category_subtitle'] );
				$this->req->setin( 'category_summary' , $category_info['category_summary'] );
				$this->req->setin( 'parent_category_cd' , $category_info['parent_category_cd'] );

			}
		}
		return	$this->page_edit_category_input( $error );
	}
	#--------------------------------------
	#	カテゴリの作成・編集：入力
	function page_edit_category_input( $error ){
		$RTN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	登録するカテゴリの内容を編集してください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		if( $this->req->pvelm() == 'edit_category' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th width="30%"><div>カテゴリコード</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->req->pvelm(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>カテゴリ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="category_title" value="'.htmlspecialchars( $this->req->in('category_title') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['category_title'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['category_title'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>カテゴリサブタイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="category_subtitle" value="'.htmlspecialchars( $this->req->in('category_subtitle') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['category_subtitle'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['category_subtitle'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>サマリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><textarea name="category_summary" class="inputitems" cols="24" rows="5">'.htmlspecialchars( $this->req->in('category_summary') ).'</textarea></div>'."\n";
		if( strlen( $error['category_summary'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['category_summary'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>親カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$RTN .= '				<select name="parent_category_cd">'."\n";
		$c = array( $this->req->in('parent_category_cd')=>' selected="selected"' );
		$RTN .= '					<option value="0"'.$c['0'].'>(最上位階層)</option>'."\n";
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$category_list = $dao_admin->get_category_list();
		if( !is_array( $category_list ) ){ $category_list = array(); }
		foreach( $category_list as $category_info ){
			$RTN .= '					<option value="'.$category_info['category_cd'].'"'.$c[$category_info['category_cd']].'>'.htmlspecialchars( $category_info['category_cd'] ).'	'.htmlspecialchars( $category_info['category_title'] ).'</option>'."\n";
		}
		$RTN .= '				</select>'."\n";
		$RTN .= '			</div>'."\n";
		if( strlen( $error['parent_category_cd'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['parent_category_cd'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリの作成・編集：確認
	function page_edit_category_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	この内容でよろしければ、「保存する」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		if( $this->req->pvelm() == 'edit_category' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th width="30%"><div>カテゴリコード</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->req->pvelm(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>カテゴリ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('category_title') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_title" value="'.htmlspecialchars( $this->req->in('category_title') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>カテゴリサブタイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->req->in('category_subtitle') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_subtitle" value="'.htmlspecialchars( $this->req->in('category_subtitle') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>サマリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.text::text2html( $this->req->in('category_summary') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_summary" value="'.htmlspecialchars( $this->req->in('category_summary') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>親カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		if( !intval( $this->req->in('parent_category_cd') ) ){
			$RTN .= '			<div>(最上位階層)</div>'."\n";
		}else{
			$dao_admin = &$this->plog->factory_dao( 'admin' );
			$category_list = $dao_admin->get_category_list();
			foreach( $category_list as $category_info ){
				if( $category_info['category_cd'] == $this->req->in('parent_category_cd') ){
					$RTN .= '			<div>'.htmlspecialchars( $this->req->in('parent_category_cd') ).'	'.$category_info['category_title'].'</div>'."\n";
					break;
				}
			}
		}
		$HIDDEN .= '<input type="hidden" name="parent_category_cd" value="'.htmlspecialchars( $this->req->in('parent_category_cd') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="保存する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':category' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':category' )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリの作成・編集：チェック
	function check_edit_category_check(){
		$RTN = array();
		if( !strlen( $this->req->in('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名を入力してください。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->req->in('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名に改行を含めることはできません。';
		}elseif( text::mdc_exists( $this->req->in('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名に機種依存文字が含まれています。';
		}elseif( strlen( $this->req->in('category_title') ) > 64 ){
			$RTN['category_title'] = 'カテゴリ名が長すぎます。('.strlen( $this->req->in('category_title') ).'/64)';
		}

		if( strlen( $this->req->in('category_subtitle') ) > 255 ){
			$RTN['category_subtitle'] = 'カテゴリサブタイトルが長すぎます。('.strlen( $this->req->in('category_subtitle') ).'/255)';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->req->in('category_subtitle') ) ){
			$RTN['category_subtitle'] = 'カテゴリサブタイトルに改行を含めることはできません。';
		}

		if( strlen( $this->req->in('category_summary') ) > 1024 ){
			$RTN['category_summary'] = 'サマリが長すぎます。('.strlen( $this->req->in('category_summary') ).'/1024)';
		}

		if( !strlen( $this->req->in('parent_category_cd') ) ){
			$RTN['parent_category_cd'] = '親カテゴリを選択してください。';
		}elseif( !preg_match( '/^[0-9]+$/is' , $this->req->in('parent_category_cd') ) ){
			$RTN['parent_category_cd'] = '親カテゴリの指定値が不正です。';
		}elseif( $this->req->pvelm() == 'edit_category' ){
			if( intval( $this->req->in('parent_category_cd') ) == intval( $this->req->pvelm(1) ) ){
				$RTN['parent_category_cd'] = '自分自身を親カテゴリには指定できません。';
			}
		}

		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリの作成・編集：実行
	function execute_edit_category_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_admin = &$this->plog->factory_dao( 'admin' );

		if( $this->req->pvelm() == 'create_category' ){
			#--------------------------------------
			#	カテゴリ作成
			$result = $dao_admin->create_category(
				$this->req->in('category_title') ,
				$this->req->in('category_subtitle') ,
				$this->req->in('category_summary')
			);
		}elseif( $this->req->pvelm() == 'edit_category' ){
			#--------------------------------------
			#	カテゴリ編集
			$result = $dao_admin->update_category(
				$this->req->pvelm(1),
				$this->req->in('category_title') ,
				$this->req->in('category_subtitle') ,
				$this->req->in('category_summary') ,
				intval( $this->req->in('parent_category_cd') )
			);
		}else{
			return	'<p class="ttr error">不明な指示です。</p>';
		}

		if( !$result ){
			return	'<p class="ttr error">失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	カテゴリの作成・編集：完了
	function page_edit_category_thanks(){
		$RTN = '';
		$RTN .= '<p>カテゴリの作成・編集処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':category' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( 'category:' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	カテゴリ階層構造のクリア
	function start_make_categories_flat(){
		$error = $this->check_make_categories_flat_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_make_categories_flat_thanks();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_make_categories_flat_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_make_categories_flat_confirm();
	}
	#--------------------------------------
	#	カテゴリ階層構造のクリア：確認
	function page_make_categories_flat_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	カテゴリの階層構造をリセットしようとしています。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	このメニューは、階層構造の指定を間違って復帰できなくなった場合などに使用してください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	リセットしてもよろしいですか？<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="リセットを実行する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':category' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':category' )."\n";
		$RTN .= '	<input type="submit" name="s" value="キャンセル" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリ階層構造のクリア：チェック
	function check_make_categories_flat_check(){
		$RTN = array();
		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリ階層構造のクリア：実行
	function execute_make_categories_flat_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_admin = &$this->plog->factory_dao( 'admin' );
		if( !$dao_admin->make_all_categories_flat() ){
			$errorMsg = 'カテゴリ階層構造のリセットに失敗しました。';
			$this->errors->error_log( $errorMsg , __FILE__ , __LINE__ );
			return	'<p class="ttr error">'.htmlspecialchars( $errorMsg ).'</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	カテゴリ階層構造のクリア：完了
	function page_make_categories_flat_thanks(){
		$RTN = '';
		$RTN .= '<p>カテゴリ階層構造のリセット処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':category' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':category' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}








	###################################################################################################################
	#	データのインポート/エクスポート
	function start_io(){
		if( $this->req->pvelm(1) == 'import' ){
			return	$this->start_io_article_import();
		}elseif( $this->req->pvelm(1) == 'export' ){
			return	$this->start_io_article_export();
		}
		return	$this->page_io();
	}
	function page_io(){
		$RTN = '';
		$RTN .= '<p>この機能は、サーバに登録された記事情報をファイルとしてエクスポート、エクスポートされたデータの登録が行えます。</p>'."\n";
		if( !strlen( $this->conf->path_commands['tar'] ) ){
			$RTN .= '<p class="ttr error">この機能は、UNIXの tar コマンドを使用します。<code>$conf->path_commands[\'tar\']</code> に、適切な tarコマンド のパスを設定してください。</p>'."\n";
		}

		$RTN .= '<div class="unit_pane2">'."\n";
		$RTN .= '	<div class="pane2L">'."\n";
		$RTN .= $this->theme->mk_hx('記事情報のエクスポート')."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':'.$this->req->pvelm().'.export' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" value="エクスポートする" /><input type="hidden" name="mode" value="execute" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':'.$this->req->pvelm().'.export' )."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '	</div>'."\n";

		$RTN .= '	<div class="pane2R">'."\n";
		$RTN .= $this->theme->mk_hx('記事情報のインポート')."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':'.$this->req->pvelm().'.import' ) ).'" method="post" enctype="multipart/form-data">'."\n";
		$RTN .= '	<p class="ttr alignC">'."\n";
		$RTN .= '		<input type="file" name="exported_data" value="" />'."\n";
		$RTN .= '		<input type="submit" value="インポートする" />'."\n";
		$RTN .= '	</p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'.$this->theme->mk_form_defvalues( ':'.$this->req->pvelm().'.import' )."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '	</div>'."\n";
		$RTN .= '</div>'."\n";

		return	$RTN;
	}

	###################################################################################################################
	#	全記事データのエクスポート
	function start_io_article_export(){
		if( $this->req->in('mode') == 'execute' ){
			return	$this->download_io_article_export();
		}
		return	$this->page_io_article_export_input();
	}


	#--------------------------------------
	#	全記事データのエクスポート
	function page_io_article_export_input(){
		$RTN = '';
		$RTN .= '<p>この機能は、サーバに登録された記事情報をファイルとしてエクスポートします。</p>'."\n";
		$RTN .= '<p>次のボタンをクリックしてください。</p>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" value="エクスポートする" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	#--------------------------------------
	#	全記事データのエクスポート
	function download_io_article_export(){
		if( !$this->plog->enable_function_export ){
			return	'<p class="ttr error">エクスポート機能が無効に設定されています。</p>';
		}

		$dao_io = &$this->plog->factory_dao( 'io' );

		$export_tmp_dir = $this->plog->get_home_dir().'/tmp_io/export';
		if( !is_dir( $export_tmp_dir ) ){
			$this->dbh->mkdirall( $export_tmp_dir );
		}
		$result = $dao_io->export( $export_tmp_dir );
		if( $result === false ){
			$RTN = '';
			$RTN .= '	<p class="ttr error">出力ファイルの生成に失敗しました。</p>'."\n";
			return	$RTN;
		}

		if( !is_file( $result ) ){
			$RTN = '';
			$RTN .= '	<p class="ttr error">出力ファイルが存在しません。</p>'."\n";
			return	$RTN;
		}

		return	$this->theme->flush_file( $result , array( 'content-type'=>'x-download/download' , 'filename'=>'plog_articles_'.date('Ymd_Hi').'.tgz' , 'delete'=>true ) );
	}

	###################################################################################################################
	#	全記事データのインポート
	function start_io_article_import(){
		$error = $this->check_io_article_import_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_io_article_import_thanks();
		}elseif( $this->req->in('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_io_article_import_confirm();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_io_article_import_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_io_article_import_input( $error );
	}
	#--------------------------------------
	#	全記事データのインポート：入力
	function page_io_article_import_input( $error ){
		$RTN = '';

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post" enctype="multipart/form-data">'."\n";
		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>インポートファイル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="file" name="exported_data" value="" /></div>'."\n";
		if( strlen( $error['exported_data'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['exported_data'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：確認
	function page_io_article_import_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>インポートファイル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$UPFILE_INFO = $this->req->get_uploadfile('exported_data');
		$RTN .= '			<div>'.htmlspecialchars( $UPFILE_INFO['name'] ).'</div>'."\n";
#		$HIDDEN .= '<input type="hidden" name="exported_data" value="'.htmlspecialchars( $this->req->in('exported_data') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="インポートする" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':'.$this->req->pvelm() ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':'.$this->req->pvelm() )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：チェック
	function check_io_article_import_check(){
		$RTN = array();

		$UPFILE_INFO = $this->req->in('exported_data');
		if( strlen( $UPFILE_INFO['tmp_name'] ) && is_file( $UPFILE_INFO['tmp_name'] ) ){
			#	ファイルがアップロードされていたら。
			if( !preg_match( '/\.tgz/si' , $UPFILE_INFO['name'] ) ){
				$RTN['exported_data'] = 'TGZファイルのみアップロード可能です。';
			}else{
				$this->req->save_uploadfile( 'exported_data' , $UPFILE_INFO );
			}
		}
		$UPFILE_INFO = $this->req->get_uploadfile('exported_data');

		if( !strlen( $UPFILE_INFO['content'] ) ){
			$RTN['exported_data'] = 'TGZファイルをアップロードしてください。';
		}
		if( !$this->plog->enable_function_import ){
			$RTN['exported_data'] = 'インポート機能が無効に設定されています。';
		}

		if( count( $RTN ) ){
			$this->req->delete_uploadfile_all();
		}

		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：実行
	function execute_io_article_import_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_io = &$this->plog->factory_dao( 'io' );

		$import_tmp_dir = $this->plog->get_home_dir().'/tmp_io/import';

		if( !is_dir( $import_tmp_dir ) ){
			#	作業ディレクトリを作成
			$this->dbh->mkdirall( $import_tmp_dir );
		}

#		$UPFILE_INFO = $this->req->in('exported_data');
		$UPFILE_INFO = $this->req->get_uploadfile('exported_data');
		if( !strlen( $UPFILE_INFO['content'] ) ){
			$RTN = '';
			$RTN .= '<p class="ttr error">アップロードファイルがゼロバイトです。</p>'."\n";
			return	$RTN;
		}
		if( !$this->dbh->file_overwrite( $this->plog->get_home_dir().'/tmp_io/import/export.tgz' , $UPFILE_INFO['content'] ) ){//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
			$RTN = '';
			$RTN .= '<p class="ttr error">アップロードファイルの一時領域への保存に失敗しました。</p>'."\n";
			return	$RTN;
		}
		$UPFILE_INFO['tmp_name'] = $this->plog->get_home_dir().'/tmp_io/import/export.tgz';

		$result = $dao_io->import( $import_tmp_dir , $UPFILE_INFO );
		if( $result === false ){
			$RTN = '';
			$RTN .= '<p class="ttr error">記事データの入力に失敗しました。</p>'."\n";
			return	$RTN;
		}

		#	アップロード一時ファイルの削除
		$this->dbh->rmdir( $this->plog->get_home_dir().'/tmp_io/import/export.tgz' );
		$this->req->delete_uploadfile_all();

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	全記事データのインポート：完了
	function page_io_article_import_thanks(){
		$RTN = '';
		$RTN .= '<p>全記事データのインポート処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':io' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':io' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート
	function download_io_article_import(){
		$this->page_io_article_import_thanks();
		return	$this->execute_io_article_import_execute();
	}





	###################################################################################################################
	#	RSSの更新
	function start_update_rss(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_update_rss_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_update_rss_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_update_rss_confirm();
	}
	#--------------------------------------
	#	RSSの更新：確認
	function page_update_rss_confirm(){
		$dao_rss = &$this->plog->factory_dao( 'rss' );
		$article_list = $dao_rss->get_article_list();

		foreach( $article_list as $line ){
			$SRC_MEMO .= '	<dt>'.htmlspecialchars( $line['article_title'] ).'</dt>'."\n";
			$SRC_MEMO .= '		<dd>'."\n";
			if( strlen( $line['category_title'] ) ){
				$SRC_MEMO .= '			'.htmlspecialchars( $line['category_title'] ).'<br />'."\n";
			}else{
				$SRC_MEMO .= '			(未分類)<br />'."\n";
			}
			$SRC_MEMO .= '			'.htmlspecialchars( $this->plog->get_article_url( $line['article_cd'] , 'rss' ) ).'<br />'."\n";
			$SRC_MEMO .= '			'.htmlspecialchars( $line['release_date'] ).'<br />'."\n";
			$SRC_MEMO .= '		</dd>'."\n";
		}

		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>次の内容で、RSSを更新します。</p>'."\n";

		$RTN .= $this->theme->mk_hx('記事の一覧')."\n";
		$RTN .= '<dl>'."\n";
		$RTN .= $SRC_MEMO;
		$RTN .= '</dl>'."\n";

		$RTN .= '<table width="100%" class="deftable">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>URL</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('rss1.0') ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('rss2.0') ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('atom1.0') ).'<br /></div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>XSLT</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->theme->resource( $this->plog->path_rss_xslt['rss1.0'] ) ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->theme->resource( $this->plog->path_rss_xslt['rss2.0'] ) ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->theme->resource( $this->plog->path_rss_xslt['atom1.0'] ) ).'<br /></div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>保存先</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('rss1.0') ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('rss2.0') ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('atom1.0') ).'<br /></div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	よろしければ、「更新する」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="更新する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	RSSの更新：実行
	function execute_update_rss_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_rss = &$this->plog->factory_dao( 'rss' );
		$result = $dao_rss->update_rss_file();
		if( $result === false ){
			$ERROR = '';
			$error_list = $dao_rss->get_error_list();
			foreach( $error_list as $error_cont ){
				$ERROR .= '	<li class="ttr error">'.htmlspecialchars($error_cont['message']).'</li>'."\n";
			}
			$RTN = '';
			$RTN .= '<p class="ttr error">RSSファイルの書き出し中にエラーが発生しました。</p>'."\n";
			$RTN .= '<ul>'."\n";
			$RTN .= $ERROR;
			$RTN .= '</ul>'."\n";
			return	$RTN;
		}


		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	RSSの更新：完了
	function page_update_rss_thanks(){
		$RTN = '';
		$RTN .= '<p>RSSの更新処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}




	###################################################################################################################
	#	承認待ちのコメント一覧
	function page_req_comments(){
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$result = $dao_admin->get_req_comment_list();

		$SRCMEMO = '';
		foreach( $result as $line ){
			$SRCMEMO .= '	<dt>From: '.htmlspecialchars( $line['commentator_name'] ).' To: '.$this->theme->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ).'</dt>'."\n";
			$SRCMEMO .= '		<dd>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['comment'] ).'</div>'."\n";
			$SRCMEMO .= '			<ul class="horizontal">'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">投稿日: '.htmlspecialchars($line['comment_date']).'</li>'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">'.$this->theme->mk_link( ':comment_list.'.$line['article_cd'] , array('label'=>'この記事のコメント一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '			</ul>'."\n";
			$SRCMEMO .= '		</dd>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<dl>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</dl>'."\n";
		}else{
			$RTN .= '<p>承認待ちのコメントはありません。</p>'."\n";
		}
		return	$RTN;
	}

	###################################################################################################################
	#	新着コメント一覧
	function page_new_comments(){
		$dao = &$this->plog->factory_dao( 'comment' );
		$result = $dao->get_new_comments( 25 );

		$SRCMEMO = '';
		foreach( $result as $line ){
			$SRCMEMO .= $this->theme->mk_hx( 'To: '.$this->theme->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ) , null , array('allow_html'=>true) )."\n";
			$SRCMEMO .= '<ul>'."\n";
			$SRCMEMO .= '	<li>From: '.htmlspecialchars( $line['commentator_name'] ).'</li>'."\n";
			if( strlen( $line['commentator_url'] ) ){
				$SRCMEMO .= '	<li>URL: '.$this->theme->mk_link( $line['commentator_url'] ).'</li>'."\n";
			}
			if( strlen( $line['commentator_email'] ) ){
				$SRCMEMO .= '	<li>メールアドレス: '.htmlspecialchars( $line['commentator_email'] ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li>投稿日: '.htmlspecialchars($line['comment_date']).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			$SRCMEMO .= '	<li class="ttrs alignR">'.$this->theme->mk_link( ':comment_list.'.$line['article_cd'] , array('label'=>'この記事のコメント一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_comments' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= $SRCMEMO;
		}else{
			$RTN .= '<p>承認待ちのコメントはありません。</p>'."\n";
		}
		return	$RTN;
	}

	###################################################################################################################
	#	承認待ちのトラックバック一覧
	function page_req_trackbacks(){
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$result = $dao_admin->get_req_trackback_list();

		$SRCMEMO = '';
		foreach( $result as $line ){
			$SRCMEMO .= '	<dt>To: '.$this->theme->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ).'</dt>'."\n";
			$SRCMEMO .= '		<dd>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['trackback_blog_name'] ).'</div>'."\n";
			$SRCMEMO .= '			<div>'.$this->theme->mk_link( $line['trackback_url'] , array( 'label'=>$line['trackback_title'] ) ).'</div>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['trackback_excerpt'] ).'</div>'."\n";
			$SRCMEMO .= '			<ul class="horizontal">'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">投稿日: '.htmlspecialchars($line['trackback_date']).'</li>'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">'.$this->theme->mk_link( ':tb_list.'.$line['article_cd'] , array('label'=>'この記事のトラックバック一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '			</ul>'."\n";
			$SRCMEMO .= '		</dd>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<dl>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</dl>'."\n";
		}else{
			$RTN .= '<p>承認待ちのトラックバックはありません。</p>'."\n";
		}
		return	$RTN;
	}

	###################################################################################################################
	#	新着トラックバック一覧
	function page_new_trackbacks(){
		$dao = &$this->plog->factory_dao( 'trackback' );
		$result = $dao->get_new_trackbacks( 25 );

		$SRCMEMO = '';
		foreach( $result as $line ){
			$SRCMEMO .= $this->theme->mk_hx( 'To: '.$this->theme->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ) , null , array('allow_html'=>true) )."\n";
			$SRCMEMO .= '<ul>'."\n";
			$SRCMEMO .= '	<li>ブログ名: '.htmlspecialchars( $line['trackback_blog_name'] ).'</li>'."\n";
			$SRCMEMO .= '	<li>記事名: '.$this->theme->mk_link( $line['trackback_url'] , array( 'label'=>$line['trackback_title'],'target'=>'_blank' ) ).'</li>'."\n";
			$SRCMEMO .= '	<li>投稿日: '.htmlspecialchars($line['trackback_date']).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			$SRCMEMO .= '	<li class="ttrs alignR">'.$this->theme->mk_link( ':tb_list.'.$line['article_cd'] , array('label'=>'この記事のトラックバック一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':req_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->theme->mk_link( ':new_trackbacks' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= $SRCMEMO;
		}else{
			$RTN .= '<p>承認待ちのトラックバックはありません。</p>'."\n";
		}
		return	$RTN;
	}



	###################################################################################################################
	#	記事検索
	function start_search(){
		if( strlen( $this->req->in('keyword') ) ){
			return	$this->page_search_result();
		}
		return	$this->page_search();
	}
	function page_search(){
		$RTN = '';
		$RTN .= '<p>探したいキーワードを入力して、「検索する」ボタンをクリックしてください。</p>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':search' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="text" name="keyword" value="'.htmlspecialchars( $this->req->in('keyword') ).'" /><input type="submit" name="s" value="検索する" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':search' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	function page_search_result(){
		$page_number = intval( $this->req->pvelm(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$option = array();
		$dao_admin = &$this->plog->factory_dao('admin');
		$hit_count = $dao_admin->search_article_count( $this->req->in('keyword') , $option );
		$pager_info = $this->dbh->get_pager_info( $hit_count , $page_number , 20 );
		$search_result = $dao_admin->search_article( $this->req->in('keyword') , $pager_info['offset'] , $pager_info['dpp'] , $option );

		$SRCMEMO = '';
		foreach( $search_result as $result_line ){
			#	仮に結果を出力してみる。
			$SRCMEMO .= '	<tr>'."\n";
			$SRCMEMO .= '		<th><div>'.htmlspecialchars($result_line['article_cd']).'</div></th>'."\n";
			$SRCMEMO .= '		<td><div>'.$this->theme->mk_link( ':article.'.intval($result_line['article_cd']) , array('label'=>$result_line['article_title']) ).'</div></td>'."\n";
			$SRCMEMO .= '	</tr>'."\n";
		}
		
		#--------------------------------------
		#	ページャ生成
		$PID_BASE = '';

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->theme->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->theme->mk_link( ':search.'.$pager_info['prev'].'?keyword='.urlencode($this->req->in('keyword')) , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->theme->mk_link( ':search.'.$i.'?keyword='.urlencode($this->req->in('keyword')) , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->theme->mk_link( ':search.'.$pager_info['next'].'?keyword='.urlencode($this->req->in('keyword')) , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
				}
				$PAGER = '';
				if( $pager_info['total_page_count'] > 1 ){
					$PAGER .= '<p class="ttr alignC cont_pager">'."\n";
					$PAGER .= implode( ' | ' , $PAGER_ARY )."\n";
					$PAGER .= '</p>'."\n";
				}
			}
		}
		#	/ページャ生成
		#--------------------------------------

		$RTN = '';
		$RTN .= '<p>『<strong>'.htmlspecialchars( $this->req->in('keyword') ).'</strong>』で検索した結果、 '.intval( $hit_count ).' 件の記事がヒットしました。</p>'."\n";
		$this->site->setpageinfo( $this->req->po().'.'.$this->req->pvelm() , 'title' , '『'.htmlspecialchars( $this->req->in('keyword') ).'』による '.intval( $hit_count ).' 件の検索結果' );

		if( strlen($SRCMEMO) ){
			$RTN .= $PAGER;
			$RTN .= '<table width="100%" class="deftable">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th>記事コード</th>'."\n";
			$RTN .= '		<th>記事名</th>'."\n";
			$RTN .= '	</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</table>'."\n";
			$RTN .= $PAGER;
		}

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':search' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="text" name="keyword" value="'.htmlspecialchars( $this->req->in('keyword') ).'" /><input type="submit" name="s" value="再検索" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':search' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	検索インデックスの更新
	function start_update_search_index(){
		$error = $this->check_update_search_index_check();
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_update_search_index_thanks();
		}elseif( $this->req->in('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_update_search_index_execute();
		}elseif( !strlen( $this->req->in('mode') ) ){
			$error = array();
		}
		return	$this->page_update_search_index_confirm();
	}
	#--------------------------------------
	#	検索インデックスの更新：確認
	function page_update_search_index_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	全記事を対象とした検索インデックスの更新を行います。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	よろしいですか？<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="インデックスを更新する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '	<input type="submit" name="s" value="キャンセル" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	検索インデックスの更新：チェック
	function check_update_search_index_check(){
		$RTN = array();
		return	$RTN;
	}
	#--------------------------------------
	#	検索インデックスの更新：実行
	function execute_update_search_index_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao_search = $this->plog->factory_dao('search');
		if( !$dao_search->update_all_index() ){
			return	'<p class="ttr error">更新に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	検索インデックスの更新：完了
	function page_update_search_index_thanks(){
		$RTN = '';
		$RTN .= '<p>検索インデックスの更新処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	データベース作成処理
	function start_create_db(){
		if( $this->req->in('mode') == 'thanks' ){
			return	$this->page_create_db_thanks();
		}elseif( $this->req->in('mode') == 'execute' ){
			return	$this->execute_create_db_execute();
		}
		return	$this->page_create_db_confirm();
	}
	#--------------------------------------
	#	データベース作成処理：確認
	function page_create_db_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	この機能は、PLOG関連テーブルを作成し、初期設定を完了します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table class="deftable">'."\n";
		$RTN .= '	<caption>作成するテーブル名設定</caption>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>article</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->plog->table_name.'_article' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>category</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->plog->table_name.'_category' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>trackback</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->plog->table_name.'_trackback' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>comment</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->plog->table_name.'_comment' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>search</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->plog->table_name.'_search' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '<table class="deftable">'."\n";
		$RTN .= '	<caption>作成先のデータベース設定</caption>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>データベース種類</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->dbh->conf->rdb['type'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>サーバ名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->dbh->conf->rdb['server'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ポート</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->dbh->conf->rdb['port'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>データベース名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->dbh->conf->rdb['name'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ユーザ名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->dbh->conf->rdb['user'] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	関連テーブルを手動で作成する場合は、'.$this->theme->mk_link( ':create_db_sql_download' , array('label'=>'SQLをダウンロード') ).'してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->theme->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="テーブルを作成する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->theme->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	データベース作成処理：実行
	function execute_create_db_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
		}

		$dao = &$this->plog->factory_dao( 'dbcreate' );
		$result = $dao->create_tables();

		if( !$result ){
			return	'<p class="ttr error">テーブルの作成に失敗しました。</p>';
		}

		return	$this->theme->redirect( $this->req->p() , 'mode=thanks' );
	}
	#--------------------------------------
	#	データベース作成処理：完了
	function page_create_db_thanks(){
		$RTN = '';
		$RTN .= '<p>データベース作成処理処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	#--------------------------------------
	#	DBを作成するSQLをダウンロード
	function start_create_db_sql_download(){
		$dao = &$this->plog->factory_dao( 'dbcreate' );
		$SQL_SRC = $dao->create_tables( 'GET_SQL_SOURCE' );
		return	$this->theme->download( $SQL_SRC , array( 'filename'=>'PLOG_create_db.sql' , 'content-type'=>'x-download/download' ) );

	}





	#--------------------------------------
	#	PLOGプラグイン関連設定項目の確認
	function page_configcheck(){
		$RTN = '';

		$RTN .= $this->theme->mk_hx('内部パス関連設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">path_home_dir</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_home_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">path_cache_dir</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">path_public_dir</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_public_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">path_rss</th>'."\n";
		$RTN .= '		<td width="70%"><span class="notes">'.htmlspecialchars( $this->conf->path_docroot ).'</span>'.htmlspecialchars( $this->plog->path_rss ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('外部パス関連設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">url_public_dir</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_url_public_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">url_article</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">url_article_rss</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' , 'rss' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">url_article_admin</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' , 'admin' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('DBテーブル名設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">article</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->table_name.'_article' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">category</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->table_name.'_category' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">trackback</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->table_name.'_trackback' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->table_name.'_comment' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">search</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->table_name.'_search' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('ブログプロフィール設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">blog_name</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->blog_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">blog_description</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->blog_description ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">blog_language</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->blog_language ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">blog_author_name</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->blog_author_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('機能制御設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">enable_trackback</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->enable_trackback ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">enable_comments</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->enable_comments ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">trackback_auto_commit</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->trackback_auto_commit ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment_auto_commit</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->comment_auto_commit ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">article_summary_mode</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->article_summary_mode ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment_userinfo_name</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->comment_userinfo_name ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment_userinfo_email</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->comment_userinfo_email ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment_userinfo_url</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->comment_userinfo_url ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">comment_userinfo_passwd</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->comment_userinfo_passwd ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('RSSパス')."\n";
		$dao_rss = &$this->plog->factory_dao( 'rss' );
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%" rowspan="3"><div>RSS1.0</div></th>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>URL</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('rss1.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>XSLT</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->path_rss_xslt['rss1.0'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>保存先</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('rss1.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%" rowspan="3"><div>RSS2.0</div></th>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>URL</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('rss2.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>XSLT</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->path_rss_xslt['rss2.0'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>保存先</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('rss2.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%" rowspan="3"><div>Atom1.0</div></th>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>URL</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_url('atom1.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>XSLT</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->path_rss_xslt['atom1.0'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th align="left" valign="top" width="15%"><div>保存先</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $dao_rss->get_rss_realpath('atom1.0') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= $this->theme->mk_hx('その他の設定')."\n";
		$RTN .= '<table class="deftable" width="100%">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">send_tbp_log_name</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( $this->plog->send_tbp_log_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">enable_function_export</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->enable_function_export ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">enable_function_import</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->enable_function_import ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">rss_limit_number</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->rss_limit_number ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%">reportmail_to</th>'."\n";
		$RTN .= '		<td width="70%">'.htmlspecialchars( text::data2text( $this->plog->reportmail_to ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->theme->act( ':' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="alignC"><input type="submit" name="s" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->theme->mk_form_defvalues( ':' )."\n";
		$RTN .= '</form>'."\n";

		return	$RTN;
	}




}


?>