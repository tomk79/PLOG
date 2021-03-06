<?php

/**
 * PxFW - Content - [PLOG]
 * (C)Tomoya Koyanagi
 * コンテンツオブジェクトクラス [ cont_plog_contents_admin ]
 */
class cont_plog_contents_admin{
	public $plog;
	public $px;
	public $pagemgr;

	/**
	 * コンストラクタ
	 */
	public function cont_plog_contents_admin( $plog ){
		$this->plog = $plog;
		$this->px = $plog->px;

		$contentpath = $this->px->realpath_files();
		if( !include_once( $contentpath.'/libs/pagemgr.php' ) ){
			$this->px->error()->error_log('pagemgrのロードに失敗しました。',__FILE__,__LINE__);
		}
		$this->pagemgr = new cont_pagemgr($this->px);

	}

	/**
	 * コンテンツ処理を開始
	 */
	public function start(){

		$article_info = array();

		if( strlen( $this->pagemgr->get_query(1) ) ){
			$dao_admin = &$this->plog->factory_dao( 'admin' );
			$article_info = $dao_admin->get_article_info( $this->pagemgr->get_query(1) );
		}

		#--------------------------------------
		#	サイトマップ定義
		$this->pagemgr->add_local_page_info('io/','インポート/エクスポート');
		$this->pagemgr->add_local_page_info('article_list/','記事一覧');

		$this->pagemgr->add_local_page_info('create_db/','テーブル作成');
		$this->pagemgr->add_local_page_info('category/','カテゴリ一覧');
		$this->pagemgr->add_local_page_info('update_rss/','RSSの更新');
		$this->pagemgr->add_local_page_info('configcheck/','関連設定の確認');
		$this->pagemgr->add_local_page_info('search/','記事検索');
		$this->pagemgr->add_local_page_info('update_search_index/','検索インデックス更新');
		$this->pagemgr->add_local_page_info('req_comments/','承認待ちコメント一覧');
		$this->pagemgr->add_local_page_info('new_comments/','新着コメント一覧');
		$this->pagemgr->add_local_page_info('req_trackbacks/','承認待ちトラックバック一覧');
		$this->pagemgr->add_local_page_info('new_trackbacks/','新着トラックバック一覧');

		$this->pagemgr->add_local_page_info( 'create_article/' , '新規記事作成', 'article_list/' );
		$this->pagemgr->add_local_page_info( 'article/'.$this->pagemgr->get_query(1).'/' , '記事詳細: '.$article_info['article_title'] , 'article_list/' , array('title_breadcrumb'=>mb_strimwidth( $article_info['article_title'] , 0 , strlen('あ')*10 , '...' ),'list_flg'=>0 ) );

		$this->pagemgr->add_local_page_info( 'edit_article/'.$this->pagemgr->get_query(1).'/' , '記事編集' , 'article/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'delete_article/'.$this->pagemgr->get_query(1).'/' , '記事削除' , 'article/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'send_tbp/'.$this->pagemgr->get_query(1).'/' , 'トラックバック送信' , 'article/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'tb_list/'.$this->pagemgr->get_query(1).'/' , 'トラックバック一覧' , 'article/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'comment_list/'.$this->pagemgr->get_query(1).'/' , 'コメント一覧' , 'article/'.$this->pagemgr->get_query(1).'/' );

		$this->pagemgr->add_local_page_info( 'tb_cst/'.$this->pagemgr->get_query(1).'/' , 'トラックバックステータス変更' , 'tb_list/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'tb_delete/'.$this->pagemgr->get_query(1).'/' , 'トラックバック削除' , 'tb_list/'.$this->pagemgr->get_query(1).'/' );

		$this->pagemgr->add_local_page_info( 'comment_cst/'.$this->pagemgr->get_query(1).'/' , 'コメントステータス変更' , 'comment_list/'.$this->pagemgr->get_query(1).'/' );
		$this->pagemgr->add_local_page_info( 'comment_delete/'.$this->pagemgr->get_query(1).'/' , 'コメント削除' , 'comment_list/'.$this->pagemgr->get_query(1).'/' );

		$this->pagemgr->add_local_page_info( 'create_category/' , '新規カテゴリ作成' , 'category/' );
		$this->pagemgr->add_local_page_info( 'edit_category/'.$this->pagemgr->get_query(1).'/' , 'カテゴリ編集' , 'category/' );
		$this->pagemgr->add_local_page_info( 'make_categories_flat/'.$this->pagemgr->get_query(1).'/' , 'カテゴリ階層構造のリセット' , 'category/' );
		#	/ サイトマップ定義
		#--------------------------------------

		if( $this->pagemgr->get_query() == 'article_list' ){
			return	$this->page_article_list();
		}elseif( $this->pagemgr->get_query() == 'create_article' || $this->pagemgr->get_query() == 'edit_article' ){
			return	$this->start_edit_article();
		}elseif( $this->pagemgr->get_query() == 'delete_article' ){
			return	$this->start_delete_article();
		}elseif( $this->pagemgr->get_query() == 'article' ){
			return	$this->page_article();
		}elseif( $this->pagemgr->get_query() == 'create_db' ){
			return	$this->start_create_db();
		}elseif( $this->pagemgr->get_query() == 'category' ){
			return	$this->page_category();
		}elseif( $this->pagemgr->get_query() == 'update_rss' ){
			return	$this->start_update_rss();
		}elseif( $this->pagemgr->get_query() == 'create_category' || $this->pagemgr->get_query() == 'edit_category' ){
			return	$this->start_edit_category();
		}elseif( $this->pagemgr->get_query() == 'make_categories_flat' ){
			return	$this->start_make_categories_flat();
		}elseif( $this->pagemgr->get_query() == 'create_db_sql_download' ){
			return	$this->start_create_db_sql_download();
		}elseif( $this->pagemgr->get_query() == 'send_tbp' ){
			return	$this->start_send_tbp();
		}elseif( $this->pagemgr->get_query() == 'tb_list' ){
			return	$this->page_tb_list();
		}elseif( $this->pagemgr->get_query() == 'tb_cst' ){
			return	$this->start_tb_cst();
		}elseif( $this->pagemgr->get_query() == 'tb_delete' ){
			return	$this->start_tb_delete();
		}elseif( $this->pagemgr->get_query() == 'comment_cst' ){
			return	$this->start_comment_cst();
		}elseif( $this->pagemgr->get_query() == 'comment_delete' ){
			return	$this->start_comment_delete();
		}elseif( $this->pagemgr->get_query() == 'comment_list' ){
			return	$this->page_comment_list();
		}elseif( $this->pagemgr->get_query() == 'req_comments' ){
			return	$this->page_req_comments();
		}elseif( $this->pagemgr->get_query() == 'new_comments' ){
			return	$this->page_new_comments();
		}elseif( $this->pagemgr->get_query() == 'req_trackbacks' ){
			return	$this->page_req_trackbacks();
		}elseif( $this->pagemgr->get_query() == 'new_trackbacks' ){
			return	$this->page_new_trackbacks();
		}elseif( $this->pagemgr->get_query() == 'search' ){
			return	$this->start_search();
		}elseif( $this->pagemgr->get_query() == 'configcheck' ){
			return	$this->page_configcheck();
		}elseif( $this->pagemgr->get_query() == 'io' ){
			return	$this->start_io();
		}elseif( $this->pagemgr->get_query() == 'update_search_index' ){
			return	$this->start_update_search_index();
		}
		return	$this->page_start();
	}//start()

	/**
	 * スタートページ
	 */
	private function page_start(){
		$page_info = $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() );

		$MENU = '';
		$MENU .= '<h2>記事管理</h2>'."\n";
		$MENU .= '<ul>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'article_list/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'create_article/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'category/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'update_rss/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'search/' ).'</li>'."\n";
		$MENU .= '</ul>'."\n";
		$MENU .= '<div class="unit cols">'."\n";
		$MENU .= '	<div class="cols-col cols-w50per"><div class="cols-pad">'."\n";
		$MENU .= '		<h2>コメント管理</h2>'."\n";
		$MENU .= '		<ul>'."\n";
		$MENU .= '			<li>'.$this->pagemgr->mk_link( 'req_comments/' ).'</li>'."\n";
		$MENU .= '			<li>'.$this->pagemgr->mk_link( 'new_comments/' ).'</li>'."\n";
		$MENU .= '		</ul>'."\n";
		$MENU .= '	</div></div>'."\n";
		$MENU .= '	<div class="cols-col cols-w50per"><div class="cols-pad">'."\n";
		$MENU .= '		<h2>トラックバック管理</h2>'."\n";
		$MENU .= '		<ul>'."\n";
		$MENU .= '			<li>'.$this->pagemgr->mk_link( 'req_trackbacks/' ).'</li>'."\n";
		$MENU .= '			<li>'.$this->pagemgr->mk_link( 'new_trackbacks/' ).'</li>'."\n";
		$MENU .= '		</ul>'."\n";
		$MENU .= '	</div></div>'."\n";
		$MENU .= '</div>'."\n";
		$MENU .= '<h2>その他</h2>'."\n";
		$MENU .= '<ul>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'update_search_index/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'io/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'configcheck/' ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'create_db/' ).'</li>'."\n";
		$MENU .= '</ul>'."\n";

		$RTN = '';
		$RTN .= $MENU;

		return	$RTN;
	}//page_start()

	/**
	 * 記事一覧ページ
	 */
	private function page_article_list(){
		#	イベントハンドラを一時的に無効にする
		$METHOD_MEMO = $this->px->dbh()->get_eventhdl_query_error();
		$this->px->dbh()->set_eventhdl_query_error(null);

		$page_number = intval( $this->pagemgr->get_query(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$dao = &$this->plog->factory_dao( 'admin' );
		$pager_info = $this->px->theme()->get_pager_info( $dao->get_article_count() , $page_number , 20 );
		$article_list = $dao->get_article_list( null , $pager_info['offset'] , $pager_info['dpp'] );

		#	イベントハンドラを元に戻す
		$this->px->dbh()->set_eventhdl_query_error($METHOD_MEMO);
		unset($METHOD_MEMO);

		$SRCMEMO = '';
		foreach( $article_list as $Line ){
			$status_label = array( 0=>'執筆中' , 1=>'公開中' );
			if( time() < $this->px->dbh()->datetime2int( $Line['release_date'] ) ){
				$status_label[1] = '公開待ち';
			}
			$status_class = array( 0=>'progress' , 1=>'public' );
			$SRCMEMO .= '	<tr class="'.htmlspecialchars($status_class[$Line['status']]).'">'."\n";
			$SRCMEMO .= '		<th>'.htmlspecialchars($Line['article_cd']).'</th>';
			$SRCMEMO .= '		<td>'."\n";
			$SRCMEMO .= '			<div>'.$this->pagemgr->mk_link( 'article/'.$Line['article_cd'].'/' , array('label'=>$Line['article_title'],'style'=>'inside') ).'</div>'."\n";
			$SRCMEMO .= '			<div class="ttrs">公開日時：'.htmlspecialchars( date( 'Y年m月d日 H時i分' , $this->px->dbh()->datetime2int( $Line['release_date'] ) ) ).'</div>'."\n";
			$SRCMEMO .= '		</td>'."\n";
			$SRCMEMO .= '		<td>'.htmlspecialchars($status_label[$Line['status']]).'</td>';
			$SRCMEMO .= '		<td class="nowrap">'.$this->pagemgr->mk_link( 'edit_article/'.$Line['article_cd'].'/' , array('label'=>'編集','style'=>'inside') ).'</td>';
			$SRCMEMO .= '	</tr>'."\n";
		}

		$MENU = '';
		$MENU .= '<ul class="horizontal">'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'create_article/' , array('label'=>'新しい記事を作成する','style'=>'inside') ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'category/' , array('label'=>'カテゴリ一覧','style'=>'inside') ).'</li>'."\n";
		$MENU .= '</ul>'."\n";

		#--------------------------------------
		#	ページャ生成
		$PID_BASE = 'article_list.';

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->px->theme()->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->pagemgr->mk_link( $PID_BASE.$pager_info['prev'].'/' , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->pagemgr->mk_link( $PID_BASE.$i.'/' , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->pagemgr->mk_link( $PID_BASE.$pager_info['next'].'/' , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
				}
				$PAGER = '';
				if( $pager_info['total_page_count'] > 1 ){
					$PAGER .= '<p class="center cont_pager">'."\n";
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
			$RTN .= '<table class="def" style="width:100%;">'."\n";
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
	}//page_article_list()


	/**
	 * 記事の詳細画面
	 */
	private function page_article(){

		$dao_admin = &$this->plog->factory_dao( 'admin' );
		$article_info = $dao_admin->get_article_info( $this->pagemgr->get_query(1) );
		if( !is_array($article_info) ){
			return	$this->px->theme()->printnotfound();
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'edit_article/'.$this->pagemgr->get_query(1).'/' , array('label'=>'この記事を編集する','style'=>'inside') ).'</li>'."\n";
		if( intval( $article_info['status'] ) && $this->px->dbh()->datetime2int( $article_info['release_date'] ) <= time() ){
			$RTN .= '	<li>'.$this->pagemgr->mk_link( 'send_tbp/'.$this->pagemgr->get_query(1).'/' , array('label'=>'TrackbackPingを送信する','style'=>'inside') ).'</li>'."\n";
		}
		$RTN .= '</ul>'."\n";

		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>記事番号</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->pagemgr->get_query(1) ).'</td>'."\n";
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
		$RTN .= '		<td>'.htmlspecialchars( date( 'Y年m月d日 H時i分s秒' , $this->px->dbh()->datetime2int($article_info['release_date']) ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ステータス</th>'."\n";
		$status_view = array( 0=>'執筆中' , 1=>'公開中' );
		if( time() < $this->px->dbh()->datetime2int( $article_info['release_date'] ) ){
			$status_view[1] = '公開待ち';
		}
		$RTN .= '		<td>'.htmlspecialchars( $status_view[$article_info['status']] ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$tb_count = $dao_visitor->get_trackback_count( $this->pagemgr->get_query(1) );
		$RTN .= '		<th>トラックバック</th>'."\n";
		$RTN .= '		<td>有効件数：'.intval( $tb_count[$this->pagemgr->get_query(1)] ).'件 '.$this->pagemgr->mk_link( 'tb_list/'.$this->pagemgr->get_query(1).'/' , array('label'=>'トラックバック一覧','style'=>'inside') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$comment_count = $dao_visitor->get_comment_count( $this->pagemgr->get_query(1) );
		$RTN .= '		<th>コメント</th>'."\n";
		$RTN .= '		<td>有効件数：'.intval( $comment_count[$this->pagemgr->get_query(1)] ).'件 '.$this->pagemgr->mk_link( 'comment_list/'.$this->pagemgr->get_query(1).'/' , array('label'=>'コメント一覧','style'=>'inside') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $this->pagemgr->get_query(1) );

		$RTN .= '<h2>記事プレビュー</h2>'."\n";
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}else{
			$RTN .= '<p class="error">記事は作成されていません。</p>'."\n";
		}

		$RTN .= '<hr />'."\n";
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'edit_article/'.$this->pagemgr->get_query(1).'/' , array('label'=>'この記事を編集する','style'=>'inside') ).'</li>'."\n";
		if( intval( $article_info['status'] ) && $this->px->dbh()->datetime2int( $article_info['release_date'] ) <= time() ){
			$RTN .= '	<li>'.$this->pagemgr->mk_link( 'send_tbp/'.$this->pagemgr->get_query(1).'/' , array('label'=>'TrackbackPingを送信する','style'=>'inside') ).'</li>'."\n";
		}
		$RTN .= '</ul>'."\n";
		$RTN .= '<hr />'."\n";

		$RTN .= '<p class="ttrs alignR">'."\n";
		$RTN .= '	作成日:'.htmlspecialchars( date( 'Y年m月d日 H時i分s秒' , $this->px->dbh()->datetime2int($article_info['create_date']) ) ).''."\n";
		$RTN .= '	更新日:'.htmlspecialchars( date( 'Y年m月d日 H時i分s秒' , $this->px->dbh()->datetime2int($article_info['update_date']) ) ).''."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<hr />'."\n";
		$RTN .= '<div class="unit p">'."\n";
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'delete_article/'.$this->pagemgr->get_query(1).'/' , array('label'=>'この記事を削除する','style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '</div>'."\n";

		return	$RTN;

	}// page_article()

	###################################################################################################################
	#	記事のトラックバック一覧
	function page_tb_list(){
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$trackback_count = $dao_visitor->get_trackback_allcount( $this->pagemgr->get_query(1) );
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_list = $dao_trackback->get_trackback_alllist( $this->pagemgr->get_query(1) );

		$SRCMEMO = '';
		foreach( $trackback_list as $Line ){
			$SRCMEMO .= '<hr />'."\n";
			$SRCMEMO .= $this->px->theme()->mk_hx( $this->pagemgr->mk_link( $Line['trackback_url'].'/' , array( 'label'=>$Line['trackback_date'].' - '.$Line['trackback_title'] ) ) , null , array( 'allow_html'=>true ) )."\n";
			$SRCMEMO .= '<p>'.htmlspecialchars( $Line['trackback_blog_name'] ).'</p>'."\n";
			$SRCMEMO .= '<p>'.t::text2html( $Line['trackback_excerpt'] ).'</p>'."\n";
			$pid = ':tb_cst.'.$this->pagemgr->get_query(1);
			$query = '';
			$query .= 'keystr='.urlencode( $Line['keystr'] );
			$query .= '&trackback_url='.urlencode( $Line['trackback_url'] );
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			if( $Line['status'] ){
				$query .= '&status=0';
				$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( $pid.'/' , array('additionalquery'=>$query,'label'=>'承認を取り消す') ).'</li>'."\n";
			}else{
				$query .= '&status=1';
				$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( $pid.'/' , array('additionalquery'=>$query,'label'=>'承認する') ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( 'tb_delete/'.$this->pagemgr->get_query(1).'/' , array('additionalquery'=>$query,'label'=>'削除する') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<p>'.intval($trackback_count[$this->pagemgr->get_query(1)]).'件のトラックバックが登録されています。</p>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '<hr />'."\n";
		}else{
			$RTN .= '<p class="error">表示できるトラックバックありません。</p>'."\n";
		}

		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";

		return	$RTN;
	}

	###################################################################################################################
	#	トラックバックのステータス変更
	function start_tb_cst(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_tb_cst_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_tb_cst_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
		}
		return	$this->page_tb_cst_confirm();
	}
	#--------------------------------------
	#	トラックバックのステータス変更：確認
	function page_tb_cst_confirm(){
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_info = $dao_trackback->get_trackback_info( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->px->req()->get_param('trackback_url') ).'" />';
		$HIDDEN .= '<input type="hidden" name="status" value="'.htmlspecialchars( $this->px->req()->get_param('status') ).'" />';

		$RTN .= '<p>'."\n";
		$RTN .= '	次のトラックバックのステータスを変更します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->pagemgr->mk_link( 'article/'.$this->pagemgr->get_query(1).'/' , array('label'=>$this->pagemgr->get_query(1)) ).'</div>'."\n";
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
		$RTN .= '			<div>'.date( 'Y年m月d日 H時i分s秒' , $comment_info['trackback_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$btnlabel = 'ステータスを変更する';
		switch( intval( $this->px->req()->get_param('status') ) ){
			case '0':
				$btnlabel = 'このトラックバックの承認を取り消す';
				break;
			case '1':
				$btnlabel = 'このトラックバックを承認する';
				break;
			default:
				return	$this->px->theme()->errorend( '不明なステータスへの変更指示です。' );
				break;
		}
		$RTN .= '<p>'."\n";
		$RTN .= '	内容を確認し、間違いなければ、「'.htmlspecialchars( $btnlabel ).'」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="'.htmlspecialchars( $btnlabel ).'" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':tb_list.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバックのステータス変更：実行
	function execute_tb_cst_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$obj_trackback = &$this->plog->factory_dao( 'trackback' );
		$result = $obj_trackback->update_trackback_status( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') , $this->px->req()->get_param('trackback_url') , $this->px->req()->get_param('status') );
		if( !$result ){
			return	'<p class="error">トラックバック['.htmlspecialchars( $this->px->req()->get_param('keystr') ).']のステータスの更新に失敗しました。</p>';
		}


		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	トラックバックのステータス変更：完了
	function page_tb_cst_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバックのステータス変更処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':tb_list.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

	###################################################################################################################
	#	トラックバックの削除
	function start_tb_delete(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_tb_delete_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_tb_delete_execute();
		}
		return	$this->page_tb_delete_confirm();
	}
	#--------------------------------------
	#	トラックバックの削除：確認
	function page_tb_delete_confirm(){
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$trackback_info = $dao_trackback->get_trackback_info( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->px->req()->get_param('trackback_url') ).'" />';

		$RTN .= '<p>次のトラックバックを削除します。</p>'."\n";

		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->pagemgr->mk_link( 'article/'.$this->pagemgr->get_query(1).'/' , array('label'=>$this->pagemgr->get_query(1)) ).'</div>'."\n";
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
		$RTN .= '			<div>'.date( 'Y年m月d日 H時i分s秒' , $comment_info['trackback_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<p>このトラックバックを削除してよろしければ、「削除する」ボタンをクリックしてください。</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post" onsubmit="if( !confirm(\'この作業は取り消せません。本当によろしいですか？\') ){return false;}">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<p class="center"><input type="submit" value="削除する" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':tb_list.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバックの削除：実行
	function execute_tb_delete_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		#	トラックバックDAOの生成
		$dao_trackback = &$this->plog->factory_dao( 'trackback' );
		$result = $dao_trackback->delete_trackback( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') , $this->px->req()->get_param('trackback_url') );
		if( !$result ){
			return	'<p class="error">トラックバックの削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	トラックバックの削除：完了
	function page_tb_delete_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバックの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':tb_list.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	記事のコメント一覧
	function page_comment_list(){
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$comment_count = $dao_visitor->get_comment_allcount( $this->pagemgr->get_query(1) );
		$obj_comment = &$this->plog->factory_dao( 'comment' );
		$comment_list = $obj_comment->get_comment_alllist( $this->pagemgr->get_query(1) );

		$SRCMEMO = '';
		foreach( $comment_list as $Line ){
			if( !strlen( $Line['commentator_name'] ) ){
				$Line['commentator_name'] = 'No Name';
			}
			$SRCMEMO .= $this->px->theme()->mk_hx( '<span class="date">'.htmlspecialchars( date( 'Y年m月d日 H時i分s秒' , $this->px->dbh()->datetime2int( $Line['comment_date'] ) ) ).'</span> '.htmlspecialchars( $Line['commentator_name'] ) , null , array('allow_html'=>true) )."\n";
			$SRCMEMO .= '<p>'.preg_replace( '/\r\n|\r|\n/' , '<br />' , htmlspecialchars( $Line['comment'] ) ).'</p>'."\n";
			$pid = ':comment_cst.'.$this->pagemgr->get_query(1);
			$query = '';
			$query .= 'keystr='.urlencode( $Line['keystr'] );
			$query .= '&create_date='.urlencode( $Line['create_date'] );
			$query .= '&client_ip='.urlencode( $Line['client_ip'] );
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			if( $Line['status'] ){
				$query .= '&status=0';
				$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( $pid.'/' , array('additionalquery'=>$query,'label'=>'承認を取り消す') ).'</li>'."\n";
			}else{
				$query .= '&status=1';
				$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( $pid.'/' , array('additionalquery'=>$query,'label'=>'承認する') ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li class="ttrs">'.$this->pagemgr->mk_link( 'comment_delete/'.$this->pagemgr->get_query(1).'/' , array('additionalquery'=>$query,'label'=>'削除する') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= '<hr />'."\n";
		}

		$RTN = '';
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<p>'.intval($comment_count[$this->pagemgr->get_query(1)]).'件のコメントが登録されています。</p>'."\n";
			$RTN .= '<hr />'."\n";
			$RTN .= $SRCMEMO;
		}else{
			$RTN .= '<p class="error">表示できるコメントありません。</p>'."\n";
		}

		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";

		return	$RTN;
	}

	###################################################################################################################
	#	コメントのステータス変更
	function start_comment_cst(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_comment_cst_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_comment_cst_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
		}
		return	$this->page_comment_cst_confirm();
	}
	#--------------------------------------
	#	コメントのステータス変更：確認
	function page_comment_cst_confirm(){
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$comment_info = $dao_comment->get_comment_info( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="create_date" value="'.htmlspecialchars( $this->px->req()->get_param('create_date') ).'" />';
		$HIDDEN .= '<input type="hidden" name="client_ip" value="'.htmlspecialchars( $this->px->req()->get_param('client_ip') ).'" />';
		$HIDDEN .= '<input type="hidden" name="status" value="'.intval( $this->px->req()->get_param('status') ).'" />';

		$RTN .= '<p>'."\n";
		$RTN .= '	次のコメントのステータスを変更します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->pagemgr->mk_link( 'article/'.$this->pagemgr->get_query(1).'/' , array('label'=>$this->pagemgr->get_query(1)) ).'</div>'."\n";
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
		$RTN .= '			<div>'.date( 'Y年m月d日 H時i分s秒' , $comment_info['comment_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$btnlabel = 'ステータスを変更する';
		switch( intval( $this->px->req()->get_param('status') ) ){
			case '0':
				$btnlabel = 'このコメントの承認を取り消す';
				break;
			case '1':
				$btnlabel = 'このコメントを承認する';
				break;
			default:
				return	$this->px->theme()->errorend( '不明なステータスへの変更指示です。' );
				break;
		}
		$RTN .= '<p>'."\n";
		$RTN .= '	内容を確認し、間違いなければ、「'.htmlspecialchars( $btnlabel ).'」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<p class="center"><input type="submit" value="'.htmlspecialchars( $btnlabel ).'" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':comment_list.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントのステータス変更：実行
	function execute_comment_cst_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$obj_comment = &$this->plog->factory_dao( 'comment' );
		$result = $obj_comment->update_comment_status( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') , $this->px->req()->get_param('create_date') , $this->px->req()->get_param('client_ip') , $this->px->req()->get_param('status') );
		if( !$result ){
			return	'<p class="error">コメント['.htmlspecialchars( $this->px->req()->get_param('keystr') ).']のステータスの更新に失敗しました。</p>';
		}


		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	コメントのステータス変更：完了
	function page_comment_cst_thanks(){
		$RTN = '';
		$RTN .= '<p>コメントのステータス変更処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':comment_list.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

	###################################################################################################################
	#	コメントの削除
	function start_comment_delete(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_comment_delete_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_comment_delete_execute();
		}
		return	$this->page_comment_delete_confirm();
	}
	#--------------------------------------
	#	コメントの削除：確認
	function page_comment_delete_confirm(){
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$comment_info = $dao_comment->get_comment_info( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') );

		$RTN = '';
		$HIDDEN = '';

		$HIDDEN .= '<input type="hidden" name="keystr" value="'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'" />';
		$HIDDEN .= '<input type="hidden" name="create_date" value="'.htmlspecialchars( $this->px->req()->get_param('create_date') ).'" />';
		$HIDDEN .= '<input type="hidden" name="client_ip" value="'.htmlspecialchars( $this->px->req()->get_param('client_ip') ).'" />';

		$RTN .= '<p>次のコメントを削除します。</p>'."\n";

		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>投稿先記事番号</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.$this->pagemgr->mk_link( 'article/'.$this->pagemgr->get_query(1).'/' , array('label'=>$this->pagemgr->get_query(1)) ).'</div>'."\n";
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
		$RTN .= '			<div>'.date( 'Y年m月d日 H時i分s秒' , $comment_info['comment_date'] ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ユニークキー</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('keystr') ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<p>このコメントを削除してよろしければ、「削除する」ボタンをクリックしてください。</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post" onsubmit="if( !confirm(\'この作業は取り消せません。本当によろしいですか？\') ){return false;}">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<p class="center"><input type="submit" value="削除する" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':comment_list.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの削除：実行
	function execute_comment_delete_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		#	コメントDAOの生成
		$dao_comment = &$this->plog->factory_dao( 'comment' );
		$result = $dao_comment->delete_comment( $this->pagemgr->get_query(1) , $this->px->req()->get_param('keystr') );
		if( !$result ){
			return	'<p class="error">コメントの削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	コメントの削除：完了
	function page_comment_delete_thanks(){
		$RTN = '';
		$RTN .= '<p>コメントの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':comment_list.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	/**
	 * 記事作成・編集
	 */
	private function start_edit_article(){
		$error = $this->check_edit_article_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_edit_article_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'imagepreview' ){
			return	$this->page_edit_article_imagepreview();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_article_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_article_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			$this->px->req()->delete_uploadfile_all();
			if( $this->pagemgr->get_query() == 'edit_article' ){
				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$article_info = $dao_admin->get_article_info( $this->pagemgr->get_query(1) );
				if( !is_array( $article_info ) || !count( $article_info ) ){
					#	記事が存在しません。
					return	$this->px->theme()->printnotfound();
				}
				$this->px->req()->set_param( 'article_title' , $article_info['article_title'] );
				$this->px->req()->set_param( 'article_summary' , $article_info['article_summary'] );
				$this->px->req()->set_param( 'status' , $article_info['status'] );
				$this->px->req()->set_param( 'category_cd' , $article_info['category_cd'] );
				$this->px->req()->set_param( 'release_date' , $article_info['release_date'] );

//				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$this->px->req()->set_param( 'contents' , $dao_admin->get_contents_src( $this->pagemgr->get_query(1) ) );

				$file_name_list = $dao_admin->get_contents_image_list( $this->pagemgr->get_query(1) );
				if( is_array( $file_name_list ) ){
					foreach( $file_name_list as $filename ){
						if( $filename == '.' || $filename == '..' ){ continue; }
						$this->px->req()->save_uploadfile(
							$filename ,
							array(
								'name'=>$filename ,
								'type'=>'image/jpeg' ,
								'content'=>$dao_admin->load_contents_image( $this->pagemgr->get_query(1) , $filename ) ,
							)
						);
					}
				}

			}
		}
		return	$this->page_edit_article_input( $error );
	}
	/**
	 * 記事作成・編集：画像プレビュー
	 */
	private function page_edit_article_imagepreview(){
		$filename = $this->px->req()->get_param('preview_image_name');
		$image_info = $this->px->req()->get_uploadfile( $filename );
		if( !strlen( $image_info['name'] ) ){
			return	$this->px->theme()->printnotfound();
		}

		$mime = null;
		switch( strtolower( $this->px->dbh()->get_extension( $filename ) ) ){
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

		return	$this->px->theme()->download( $image_info['content'] , array('filename'=>$image_info['name'],'content-type'=>$mime) );
	}
	/**
	 * 記事作成・編集：入力
	 */
	private function page_edit_article_input( $error ){
		$RTN = '';

		if( count( $error ) ){
			$RTN .= '<p class="error">'.count( $error ).'件の入力エラーがあります。もう一度ご確認ください。</p>'."\n";
		}else{
			$RTN .= '<p>'."\n";
			if( $this->pagemgr->get_query() == 'edit_article' ){
				$RTN .= '	記事を編集して、「確認する」ボタンをクリックしてください。<br />'."\n";
			}else{
				$RTN .= '	新しい記事を作成します。記事の内容を編集したら、「確認する」ボタンをクリックしてください。<br />'."\n";
			}
			$RTN .= '</p>'."\n";
		}

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post" enctype="multipart/form-data" name="editForm">'."\n";

		$RTN .= '<h2>基本情報</h2>'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		if( $this->pagemgr->get_query() == 'edit_article' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>記事番号</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->pagemgr->get_query(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>タイトル <span class="must">*</span></div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="article_title" value="'.htmlspecialchars( $this->px->req()->get_param('article_title') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['article_title'] ) ){
			$RTN .= '			<div class="error">'.$error['article_title'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$c = array($this->px->req()->get_param('category_cd')=>' selected="selected"');
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
			$RTN .= '			<div class="error">'.$error['category_cd'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>公開日</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.$this->plog->mk_form_select_date( 'input' , array( 'default'=>$this->px->req()->get_param('release_date'),'max_year'=>date('Y')+3 ) ).'</div>'."\n";
		if( strlen( $error['release_date'] ) ){
			$RTN .= '			<div class="error">'.$error['release_date'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ステータス</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$c = array( intval( $this->px->req()->get_param('status') )=>' checked="checked"' );
		$RTN .= '				<input type="radio" name="status" id="status_0" value="0"'.$c[0].' /><label for="status_0">執筆中</label>'."\n";
		$RTN .= '				<input type="radio" name="status" id="status_1" value="1"'.$c[1].' /><label for="status_1">公開中</label>'."\n";
		$RTN .= '			</div>'."\n";
		if( strlen( $error['status'] ) ){
			$RTN .= '			<div class="error">'.$error['status'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		if( $this->plog->article_summary_mode == 'manual' ){
			$RTN .= '<h2>サマリ</h2>'."\n";
			if( strlen( $error['article_summary'] ) ){
				$RTN .= '<div class="error">'.$error['article_summary'].'</div>'."\n";
			}
			$RTN .= '<p><textarea name="article_summary" rows="7" cols="20" class="inputitems">'.htmlspecialchars( $this->px->req()->get_param('article_summary') ).'</textarea></p>'."\n";
		}

		$RTN .= '<h2>内容 <span class="must">*</span></h2>'."\n";
		if( strlen( $error['contents'] ) ){
			$RTN .= '<div class="error">'.$error['contents'].'</div>'."\n";
		}
		$RTN .= '<p><textarea name="contents" rows="11" cols="20" class="inputitems">'.htmlspecialchars( $this->px->req()->get_param('contents') ).'</textarea></p>'."\n";

		$RTN .= '<h2>画像</h2>'."\n";
		$tmp_image_list = $this->px->req()->get_uploadfile_list();
		$IMAGE_SRC = '';
		#	既にアップロードされた画像の一覧
		foreach( $tmp_image_list as $image_key ){
			$image_info = $this->px->req()->get_uploadfile( $image_key );
			$IMAGE_SRC .= '	<tr>'."\n";
			$IMAGE_SRC .= '		<td width="30%" class="alignC">'."\n";
			$tmp_ext = strtolower( $this->px->dbh()->get_extension( $image_key ) );
			switch( $tmp_ext ){
				case 'gif':
				case 'png':
				case 'jpg':
				case 'jpe':
				case 'jpeg':
				case 'bmp':
					$IMAGE_SRC .= '			<div><img src="'.htmlspecialchars( $this->px->theme()->href( $this->px->req()->get_request_file_path() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.urlencode($image_key) ) ) ).'" width="100" alt="" /></div>'."\n";
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
			if( !$this->px->user()->is_mp() ){
				$IMAGE_SRC .= '			<input type="file" name="'.htmlspecialchars($this->exchange_edit_article_key('imagefile:'.$image_key)).'" value="" /><br />'."\n";
				$IMAGE_SRC .= '			<input type="checkbox" name="'.htmlspecialchars($this->exchange_edit_article_key('deleteimage:'.$image_key)).'" id="deleteimage:'.htmlspecialchars($this->exchange_edit_article_key($image_key)).'" value="1" /><label for="deleteimage:'.htmlspecialchars($this->exchange_edit_article_key($image_key)).'">削除する</label><br />'."\n";
			}
			$IMAGE_SRC .= '		</td>'."\n";
			$IMAGE_SRC .= '	</tr>'."\n";
		}

		if( strlen( $IMAGE_SRC ) ){
			$RTN .= '<table style="width:100%;" class="def">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th class="center">サムネイル</th>'."\n";
			$RTN .= '			<th class="center">画像名</th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			$RTN .= $IMAGE_SRC;
			$RTN .= '</table>'."\n";
		}
		#	新しい画像をアップロードする
		$RTN .= '<h3>新しい画像をアップロード</h3>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	<input type="file" name="new_image" value="" /><br />'."\n";
		$RTN .= '	ファイル名：<input type="text" name="new_imagename" value="" /><br />'."\n";
		if( strlen( $error['new_image'] ) ){
			$RTN .= '	<span class="error">'.$error['new_image'].'</span><br />'."\n";
		}
		$RTN .= '</p>'."\n";

		$RTN .= '	<p class="center"><input type="submit" value="確認する" /><input type="submit" value="画像を反映" onclick="document.editForm.mode.value=\'input\';return true;" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}//page_edit_article_input()

	/**
	 * 記事作成・編集：確認
	 */
	private function page_edit_article_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	編集した内容を確認してください。これでよろしければ、「保存する」ボタンをクリックして、作業を完了してください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<h2>基本情報</h2>'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		if( $this->pagemgr->get_query() == 'edit_article' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>記事番号</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->pagemgr->get_query(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>タイトル <span class="must">*</span></div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('article_title') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="article_title" value="'.htmlspecialchars( $this->px->req()->get_param('article_title') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$dao_visitor = &$this->plog->factory_dao( 'visitor' );
		$category_list = $dao_visitor->get_category_list();
		$category_title = '(選択しない)';
		foreach( $category_list as $line ){
			if( $line['category_cd'] == $this->px->req()->get_param('category_cd') ){
				$category_title = $line['category_title'];
				break;
			}
		}
		$RTN .= '			<div>'.htmlspecialchars( $category_title ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_cd" value="'.intval( $this->px->req()->get_param('category_cd') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>公開日</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.$this->plog->mk_form_select_date( 'confirm' , array( 'default'=>$this->px->req()->get_param('release_date'),'max_year'=>date('Y')+3 ) ).'</div>'."\n";
		$HIDDEN .= $this->plog->mk_form_select_date( 'hidden' , array( 'default'=>$this->px->req()->get_param('release_date'),'max_year'=>date('Y')+3 ) );
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>ステータス</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$status = array( '執筆中' , '公開中' );
		$RTN .= '			<div>'.$status[intval($this->px->req()->get_param('status'))].'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="status" value="'.htmlspecialchars( $this->px->req()->get_param('status') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		if( $this->plog->article_summary_mode == 'manual' ){
			$RTN .= '<h2>サマリ</h2>'."\n";
			$RTN .= '<blockquote class="sourcecode"><div>'.htmlspecialchars( $this->px->req()->get_param('article_summary') ).'</div></blockquote>'."\n";
			$HIDDEN .= '<input type="hidden" name="article_summary" value="'.htmlspecialchars( $this->px->req()->get_param('article_summary') ).'" />';
		}

		$RTN .= '<h2>内容プレビュー</h2>'."\n";

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content_preview( $this->px->req()->get_param('contents') );
		$RTN .= '<div class="unit">';
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= $ARTICLE_BODY_SRC;
		}else{
			$RTN .= '<p class="error">記事は作成されていません。</p>'."\n";
		}
		$RTN .= '</div><!-- /.unit -->'."\n";

		$RTN .= '<h2>ソース</h2>'."\n";
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '	<div class="code"><pre><code>'.htmlspecialchars( $this->px->req()->get_param('contents') ).'</code></pre></div>'."\n";
		$RTN .= '</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="contents" value="'.htmlspecialchars( $this->px->req()->get_param('contents') ).'" />';

		$RTN .= '<h2>画像</h2>'."\n";
		$tmp_image_list = $this->px->req()->get_uploadfile_list();
		$IMAGE_SRC = '';
		foreach( $tmp_image_list as $image_key ){
			$image_info = $this->px->req()->get_uploadfile( $image_key );
			$IMAGE_SRC .= '	<tr>'."\n";
			$IMAGE_SRC .= '		<td width="30%" class="alignC">'."\n";
			$tmp_ext = strtolower( $this->px->dbh()->get_extension( $image_key ) );
			switch( $tmp_ext ){
				case 'gif':
				case 'png':
				case 'jpg':
				case 'jpe':
				case 'jpeg':
				case 'bmp':
					$IMAGE_SRC .= '			<div><img src="'.htmlspecialchars( $this->px->theme()->href( $this->px->req()->get_request_file_path() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.urlencode($image_key) ) ) ).'" width="100" alt="" /></div>'."\n";
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
			$RTN .= '<table style="width:100%;" class="def">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th class="center">サムネイル</th>'."\n";
			$RTN .= '			<th class="center">画像名</th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			$RTN .= $IMAGE_SRC;
			$RTN .= '</table>'."\n";
		}else{
			$RTN .= '<p>画像は登録されていません。</p>'."\n";
		}

		$RTN .= '<div class="p center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post" onsubmit="document.getElementById(\'cont_saveform\').disabled=true;return true;">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="保存する" id="cont_saveform" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		if( $this->pagemgr->get_query() == 'edit_article' ){
			$cancel_pid = 'article/'.$this->pagemgr->get_query(1).'/';
		}else{
			$cancel_pid = 'article_list/';
		}
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $cancel_pid ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * 記事作成・編集：チェック
	 */
	private function check_edit_article_check(){
		$RTN = array();

		#--------------------------------------
		#	新しい画像の登録
		$new_image_file_info = $this->px->req()->get_param('new_image');
		if( strlen( $new_image_file_info['name'] ) ){
			$imagename = $this->px->req()->get_param('new_imagename');
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
				$this->px->req()->save_uploadfile( $imagename , $new_image_file_info );
			}
		}
		unset($new_image_file_info);

		#--------------------------------------
		#	既存画像の処理
		$tmp_image_list = $this->px->req()->get_uploadfile_list();
		foreach( $tmp_image_list as $image_key ){
			if( $this->px->req()->get_param($this->exchange_edit_article_key( 'deleteimage:'.$image_key )) ){
				#	画像削除
				$this->px->req()->delete_uploadfile( $image_key );
				continue;
			}
			$uploaded_image = $this->px->req()->get_param($this->exchange_edit_article_key('imagefile:'.$image_key));
			if( !strlen( $uploaded_image['name'] ) ){
				continue;
			}
			$uploaded_image['name'] = $image_key;
			$this->px->req()->save_uploadfile( $uploaded_image['name'] , $uploaded_image );
			unset( $uploaded_image );
		}
		unset( $tmp_image_list );

		if( !strlen( $this->px->req()->get_param('article_title') ) ){
			$RTN['article_title'] = 'タイトルを入力してください。';
		}elseif( preg_match( '/\r\n|\r|\n/si' ,  $this->px->req()->get_param('article_title') ) ){
			$RTN['article_title'] = 'タイトルに改行を含めることはできません。';
		}elseif( strlen( $this->px->req()->get_param('article_title') ) > 124 ){
			$RTN['article_title'] = 'タイトルが長すぎます。('.strlen( $this->px->req()->get_param('article_title') ).'/124)';
		}

		if( !strlen( $this->px->req()->get_param('category_cd') ) ){
			$RTN['category_cd'] = 'カテゴリを選択してください。';
		}elseif( !preg_match( '/^[0-9]+$/si' ,  $this->px->req()->get_param('category_cd') ) ){
			$RTN['category_cd'] = 'カテゴリの形式が不正です。';
		}

		if( !strlen( $this->px->req()->get_param('contents') ) ){
			$RTN['contents'] = '内容を入力してください。';
		}

		return	$RTN;
	}
	/**
	 * 記事作成・編集：実行
	 */
	private function execute_edit_article_execute(){
		$this->px->req()->set_param( 'release_date' , $this->plog->mk_form_select_date( 'get_datetime' , array('max_year'=>date('Y')+3) ) );//公開日を反映

		$dao_admin = &$this->plog->factory_dao( 'admin' );

		if( $this->pagemgr->get_query() == 'create_article' ){
			#--------------------------------------
			#	新規作成の処理
			$result = $dao_admin->create_article(
				$this->px->req()->get_param('article_title') ,
				$this->px->req()->get_param('status') ,
				$this->px->req()->get_param('contents') ,
				array(
					'article_summary'=>$this->px->req()->get_param('article_summary') ,
					'user_id'=>$this->px->user()->get_login_user_id() ,
					'release_date'=>$this->px->req()->get_param('release_date') ,
					'category_cd'=>intval( $this->px->req()->get_param('category_cd') ) ,
				)
			);
			if( $result === false ){
				return	'<p class="error">記事の作成に失敗しました。</p>';
			}
			$article_cd = $result;


		}elseif( $this->pagemgr->get_query() == 'edit_article' && strlen( $this->pagemgr->get_query(1) ) ){
			#--------------------------------------
			#	既存編集の処理

			$result = $dao_admin->update_article(
				$this->pagemgr->get_query(1) ,
				$this->px->req()->get_param('article_title') ,
				$this->px->req()->get_param('status') ,
				$this->px->req()->get_param('contents') ,
				array(
					'article_summary'=>$this->px->req()->get_param('article_summary') ,
					'release_date'=>$this->px->req()->get_param('release_date') ,
					'category_cd'=>intval( $this->px->req()->get_param('category_cd') ) ,
				)
			);
			if( $result === false ){
				return	'<p class="error">記事の保存に失敗しました。</p>';
			}
			$article_cd = $this->pagemgr->get_query(1);


		}else{
			#--------------------------------------
			#	どちらでもなければエラー
			$errorMsg = '不明な命令です。['.$this->pagemgr->get_query().']';
			$this->px->error()->error_log( $errorMsg , __FILE__ , __LINE__ );
			return	'<p class="error">'.htmlspecialchars( $errorMsg ).'</p>';
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
		$tmp_image_list = $this->px->req()->get_uploadfile_list();
		if( is_array( $tmp_image_list ) ){
			foreach( $tmp_image_list as $filename ){
				$fileinfo = $this->px->req()->get_uploadfile( $filename );
				$dao_admin->save_contents_image(
					$article_cd ,
					$fileinfo['name'] ,
					$fileinfo['content']
				);
			}
		}

		#	アップロード画像のメモをクリア
		$this->px->req()->delete_uploadfile_all();

		#	記事インデックスの更新
		$result = $dao_admin->update_article_index( $article_cd );
		if( !$result ){
			return	'<p class="error">記事インデックスの更新に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks&article_cd='.$article_cd );
	}
	/**
	 * キーに使える文字列に変換
	 */
	private function exchange_edit_article_key( $key ){
		$key = preg_replace( '/\./' , '_' , $key );
		return	$key;
	}
	/**
	 * 記事作成・編集：完了
	 */
	private function page_edit_article_thanks(){
		$RTN = '';
		if( $this->pagemgr->get_query() == 'edit_article' ){
			$next_message = '記事 '.$this->pagemgr->get_query(1).' の編集を保存しました。';
			$next_pid = 'article/'.$this->pagemgr->get_query(1).'/';
		}else{
			$next_message = '新しい記事 '.$this->pagemgr->get_query(1).' を作成しました。';
			$next_pid = 'article/'.$this->px->req()->get_param('article_cd').'/';
		}
		$RTN .= '<p>'.htmlspecialchars($next_message).'</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $next_pid ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}




	###################################################################################################################
	#	トラックバック送信
	function start_send_tbp(){
		if( !strlen( $this->pagemgr->get_query(1) ) ){
			#	記事が指定されていません。
			return	$this->px->theme()->printnotfound();
		}
		$dao_admin = &$this->plog->factory_dao('admin');
		$article_info = $dao_admin->get_article_info( $this->pagemgr->get_query(1) );
		if( !is_array( $article_info ) && !count( $article_info ) ){
			#	記事が存在しません。
			return	$this->px->theme()->printnotfound();
		}

		$error = $this->check_send_tbp_check( $article_info );
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_send_tbp_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_send_tbp_confirm( $article_info );
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_send_tbp_execute( $article_info );
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			$this->px->req()->setin( 'article_summary' , $article_info['article_summary'] );
		}
		return	$this->page_send_tbp_input( $article_info , $error );
	}
	#--------------------------------------
	#	トラックバック送信：入力
	function page_send_tbp_input( $article_info , $error ){
		$RTN = '';

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '<div>'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
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
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->get_article_url( $this->pagemgr->get_query(1) ) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>トラックバック先URI</h2>'."\n";
		$RTN .= '<p><textarea name="trackback_url" class="inputitems" rows="5" cols="20">'.htmlspecialchars( $this->px->req()->get_param('trackback_url') ).'</textarea></p>'."\n";
		if( strlen( $error['trackback_url'] ) ){
			$RTN .= '<p class="error">'.$error['trackback_url'].'</p>'."\n";
		}

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $this->pagemgr->get_query(1) );

		$RTN .= '<h2>サマリ</h2>'."\n";
		$article_summary = $this->px->req()->get_param('article_summary');
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
			$RTN .= '			<div class="error">'.$error['article_summary'].'</div>'."\n";
		}
		$RTN .= '			</div>'."\n";

		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";

		$RTN .= '<h2>記事プレビュー</h2>'."\n";
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}else{
			$RTN .= '<p class="error">記事は作成されていません。</p>'."\n";
		}


		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
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
		$RTN .= '<table style="width:100%;" class="def">'."\n";
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
		$RTN .= '			<div>'.htmlspecialchars( $this->plog->get_article_url( $this->pagemgr->get_query(1) ) ).'</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>トラックバック先URI</h2>'."\n";
		$tburl_list = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('trackback_url') );
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
//		$RTN .= '<p>'.t::text2html( $this->px->req()->get_param('trackback_url') ).'</p>'."\n";
		$HIDDEN .= '<input type="hidden" name="trackback_url" value="'.htmlspecialchars( $this->px->req()->get_param('trackback_url') ).'" />';

		$RTN .= '<h2>サマリ</h2>'."\n";
		$RTN .= '<p>'.t::text2html( $this->px->req()->get_param('article_summary') ).'</p>'."\n";
		$HIDDEN .= '<input type="hidden" name="article_summary" value="'.htmlspecialchars( $this->px->req()->get_param('article_summary') ).'" />';

		$RTN .= '<hr />'."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	この操作は、相手方のブログへ通知されます。一度送信したトラックバックピングの処理は、<strong>取り消すことができません</strong>。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	もう一度よく確認し、問題がなければ、「トラックバックピングを送信する」をクリックして、トラックバックピング送信を完了してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="トラックバックピングを送信する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバック送信：チェック
	function check_send_tbp_check( $article_info ){
		$RTN = array();
		if( !strlen( $this->px->req()->get_param('trackback_url') ) ){
			$RTN['trackback_url'] = 'トラックバック先URIが設定されていません。';
		}
		if( !strlen( $this->px->req()->get_param('article_summary') ) ){
			$RTN['article_summary'] = 'サマリは必ず指定してください。';
		}
		return	$RTN;
	}
	#--------------------------------------
	#	トラックバック送信：実行
	function execute_send_tbp_execute( $article_info ){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		if( !$this->plog->enable_trackback ){
			return	'<p class="error">トラックバック機能が無効になっています。</p>';
		}

		#--------------------------------------
		#	TrackbackPing送信先の一覧を作成する
		$trackback_url_list = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('trackback_url') );
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
			return	'<p class="error">TrackbackPingクラスのロードに失敗しました。</p>';
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
				$this->pagemgr->get_query(1) ,
				$tbp_url ,
				$this->plog->get_article_url( $this->pagemgr->get_query(1) ) ,
				$article_info['article_title'] ,
				$this->px->req()->get_param('article_summary') ,
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
#			return	'<p class="error">トラックバックPING送信ログを保存できませんでした。</p>';
#		}

		return	$this->px->redirect( $this->px->req()->get_request_file_path() , 'mode=thanks&count='.count($done).'&ok_list='.urlencode(implode("\n",$done_ok)).'&ng_list='.urlencode(implode("\n",$done_ng)) );
	}
	#--------------------------------------
	#	トラックバック送信：完了
	function page_send_tbp_thanks(){
		$RTN = '';
		$RTN .= '<p>トラックバック送信処理を完了しました。</p>'."\n";
		$RTN .= '<p>総数：'.$this->px->req()->get_param('count').'</p>'."\n";

		$RTN .= '<h2>OK</h2>'."\n";
		$LIST_VIEW = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('ok_list') );
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

		$RTN .= '<h2>NG</h2>'."\n";
		$LIST_VIEW = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('ng_list') );
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

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	/**
	 * 記事を削除する(論理削除)
	 */
	function start_delete_article(){
		$error = $this->check_delete_article_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_delete_article_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_delete_article_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
		}
		return	$this->page_delete_article_confirm();
	}
	/**
	 * 記事を削除する(論理削除)：確認
	 */
	function page_delete_article_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>記事['.htmlspecialchars( $this->pagemgr->get_query(1) ).']を削除します。</p>'."\n";
		$RTN .= '<p>よろしいですか？</p>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * 記事を削除する(論理削除)：チェック
	 */
	function check_delete_article_check(){
		$RTN = array();
		return	$RTN;
	}
	/**
	 * 記事を削除する(論理削除)：実行
	 */
	function execute_delete_article_execute(){
		$dao_admin = &$this->plog->factory_dao( 'admin' );
		if( !$dao_admin->delete_article( $this->pagemgr->get_query(1) ) ){
			return	'<p class="error">記事の削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	/**
	 * 記事を削除する(論理削除)：完了
	 */
	function page_delete_article_thanks(){
		$RTN = '';
		$RTN .= '<p>記事['.htmlspecialchars( $this->pagemgr->get_query(1) ).']を削除しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'article_list/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
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
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'create_category/' , array( 'label'=>'新規カテゴリ作成','style'=>'inside' ) ).'</li>'."\n";
		$MENU .= '	<li>'.$this->pagemgr->mk_link( 'make_categories_flat/' , array( 'label'=>'階層構造のリセット','style'=>'inside' ) ).'</li>'."\n";
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
				$MEMO .= '<li>'.$this->pagemgr->mk_link( 'edit_category/'.$category_info['category_cd'].'/' , array( 'label'=>$category_info['category_title'] ) );
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
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_edit_category_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_category_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_category_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			if( $this->pagemgr->get_query() == 'edit_category' ){
				$dao_admin = &$this->plog->factory_dao( 'admin' );
				$category_info = $dao_admin->get_category_info( $this->pagemgr->get_query(1) );
				if( !is_array($category_info) ){
					return	'<p class="error">指定されたカテゴリは存在しません。</p>';
				}
				$this->px->req()->setin( 'category_title' , $category_info['category_title'] );
				$this->px->req()->setin( 'category_subtitle' , $category_info['category_subtitle'] );
				$this->px->req()->setin( 'category_summary' , $category_info['category_summary'] );
				$this->px->req()->setin( 'parent_category_cd' , $category_info['parent_category_cd'] );

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
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		if( $this->pagemgr->get_query() == 'edit_category' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>カテゴリコード</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->pagemgr->get_query(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>カテゴリ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="category_title" value="'.htmlspecialchars( $this->px->req()->get_param('category_title') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['category_title'] ) ){
			$RTN .= '			<div class="error">'.$error['category_title'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>カテゴリサブタイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="text" name="category_subtitle" value="'.htmlspecialchars( $this->px->req()->get_param('category_subtitle') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['category_subtitle'] ) ){
			$RTN .= '			<div class="error">'.$error['category_subtitle'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>サマリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><textarea name="category_summary" class="inputitems" cols="24" rows="5">'.htmlspecialchars( $this->px->req()->get_param('category_summary') ).'</textarea></div>'."\n";
		if( strlen( $error['category_summary'] ) ){
			$RTN .= '			<div class="error">'.$error['category_summary'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>親カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'."\n";
		$RTN .= '				<select name="parent_category_cd">'."\n";
		$c = array( $this->px->req()->get_param('parent_category_cd')=>' selected="selected"' );
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
			$RTN .= '			<div class="error">'.$error['parent_category_cd'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
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
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		if( $this->pagemgr->get_query() == 'edit_category' ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th style="width:30%;"><div>カテゴリコード</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->pagemgr->get_query(1) ).'</div>'."\n";
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>カテゴリ名</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('category_title') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_title" value="'.htmlspecialchars( $this->px->req()->get_param('category_title') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>カテゴリサブタイトル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('category_subtitle') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_subtitle" value="'.htmlspecialchars( $this->px->req()->get_param('category_subtitle') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>サマリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.t::text2html( $this->px->req()->get_param('category_summary') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="category_summary" value="'.htmlspecialchars( $this->px->req()->get_param('category_summary') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>親カテゴリ</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		if( !intval( $this->px->req()->get_param('parent_category_cd') ) ){
			$RTN .= '			<div>(最上位階層)</div>'."\n";
		}else{
			$dao_admin = &$this->plog->factory_dao( 'admin' );
			$category_list = $dao_admin->get_category_list();
			foreach( $category_list as $category_info ){
				if( $category_info['category_cd'] == $this->px->req()->get_param('parent_category_cd') ){
					$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('parent_category_cd') ).'	'.$category_info['category_title'].'</div>'."\n";
					break;
				}
			}
		}
		$HIDDEN .= '<input type="hidden" name="parent_category_cd" value="'.htmlspecialchars( $this->px->req()->get_param('parent_category_cd') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="保存する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':category' ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリの作成・編集：チェック
	function check_edit_category_check(){
		$RTN = array();
		if( !strlen( $this->px->req()->get_param('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名を入力してください。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名に改行を含めることはできません。';
		}elseif( t::mdc_exists( $this->px->req()->get_param('category_title') ) ){
			$RTN['category_title'] = 'カテゴリ名に機種依存文字が含まれています。';
		}elseif( strlen( $this->px->req()->get_param('category_title') ) > 64 ){
			$RTN['category_title'] = 'カテゴリ名が長すぎます。('.strlen( $this->px->req()->get_param('category_title') ).'/64)';
		}

		if( strlen( $this->px->req()->get_param('category_subtitle') ) > 255 ){
			$RTN['category_subtitle'] = 'カテゴリサブタイトルが長すぎます。('.strlen( $this->px->req()->get_param('category_subtitle') ).'/255)';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('category_subtitle') ) ){
			$RTN['category_subtitle'] = 'カテゴリサブタイトルに改行を含めることはできません。';
		}

		if( strlen( $this->px->req()->get_param('category_summary') ) > 1024 ){
			$RTN['category_summary'] = 'サマリが長すぎます。('.strlen( $this->px->req()->get_param('category_summary') ).'/1024)';
		}

		if( !strlen( $this->px->req()->get_param('parent_category_cd') ) ){
			$RTN['parent_category_cd'] = '親カテゴリを選択してください。';
		}elseif( !preg_match( '/^[0-9]+$/is' , $this->px->req()->get_param('parent_category_cd') ) ){
			$RTN['parent_category_cd'] = '親カテゴリの指定値が不正です。';
		}elseif( $this->pagemgr->get_query() == 'edit_category' ){
			if( intval( $this->px->req()->get_param('parent_category_cd') ) == intval( $this->pagemgr->get_query(1) ) ){
				$RTN['parent_category_cd'] = '自分自身を親カテゴリには指定できません。';
			}
		}

		return	$RTN;
	}
	#--------------------------------------
	#	カテゴリの作成・編集：実行
	function execute_edit_category_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$dao_admin = &$this->plog->factory_dao( 'admin' );

		if( $this->pagemgr->get_query() == 'create_category' ){
			#--------------------------------------
			#	カテゴリ作成
			$result = $dao_admin->create_category(
				$this->px->req()->get_param('category_title') ,
				$this->px->req()->get_param('category_subtitle') ,
				$this->px->req()->get_param('category_summary')
			);
		}elseif( $this->pagemgr->get_query() == 'edit_category' ){
			#--------------------------------------
			#	カテゴリ編集
			$result = $dao_admin->update_category(
				$this->pagemgr->get_query(1),
				$this->px->req()->get_param('category_title') ,
				$this->px->req()->get_param('category_subtitle') ,
				$this->px->req()->get_param('category_summary') ,
				intval( $this->px->req()->get_param('parent_category_cd') )
			);
		}else{
			return	'<p class="error">不明な指示です。</p>';
		}

		if( !$result ){
			return	'<p class="error">失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	カテゴリの作成・編集：完了
	function page_edit_category_thanks(){
		$RTN = '';
		$RTN .= '<p>カテゴリの作成・編集処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':category' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	カテゴリ階層構造のクリア
	function start_make_categories_flat(){
		$error = $this->check_make_categories_flat_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_make_categories_flat_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_make_categories_flat_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
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
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= '	<input type="submit" value="リセットを実行する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'category/' ) ).'" method="get">'."\n";
		$RTN .= '	<input type="submit" value="キャンセル" />'."\n";
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
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$dao_admin = &$this->plog->factory_dao( 'admin' );
		if( !$dao_admin->make_all_categories_flat() ){
			$errorMsg = 'カテゴリ階層構造のリセットに失敗しました。';
			$this->px->error()->error_log( $errorMsg , __FILE__ , __LINE__ );
			return	'<p class="error">'.htmlspecialchars( $errorMsg ).'</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	カテゴリ階層構造のクリア：完了
	function page_make_categories_flat_thanks(){
		$RTN = '';
		$RTN .= '<p>カテゴリ階層構造のリセット処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'category/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	データのインポート/エクスポート
	function start_io(){
		if( $this->pagemgr->get_query(1) == 'import' ){
			return	$this->start_io_article_import();
		}elseif( $this->pagemgr->get_query(1) == 'export' ){
			return	$this->start_io_article_export();
		}
		return	$this->page_io();
	}
	function page_io(){
		$RTN = '';
		$RTN .= '<p>この機能は、サーバに登録された記事情報をファイルとしてエクスポート、エクスポートされたデータの登録が行えます。</p>'."\n";
		if( !strlen( $this->conf->path_commands['tar'] ) ){
			$RTN .= '<p class="error">この機能は、UNIXの tar コマンドを使用します。<code>$conf->path_commands[\'tar\']</code> に、適切な tarコマンド のパスを設定してください。</p>'."\n";
		}

		$RTN .= '<div class="unit_pane2">'."\n";
		$RTN .= '	<div class="pane2L">'."\n";
		$RTN .= '<h2>記事情報のエクスポート</h2>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $this->pagemgr->get_query().'/export/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="エクスポートする" /><input type="hidden" name="mode" value="execute" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '	</div>'."\n";

		$RTN .= '	<div class="pane2R">'."\n";
		$RTN .= '<h2>記事情報のインポート</h2>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $this->pagemgr->get_query().'/import/' ) ).'" method="post" enctype="multipart/form-data">'."\n";
		$RTN .= '	<p class="center">'."\n";
		$RTN .= '		<input type="file" name="exported_data" value="" />'."\n";
		$RTN .= '		<input type="submit" value="インポートする" />'."\n";
		$RTN .= '	</p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '	</div>'."\n";
		$RTN .= '</div>'."\n";

		return	$RTN;
	}


	###################################################################################################################
	#	全記事データのエクスポート
	function start_io_article_export(){
		if( $this->px->req()->get_param('mode') == 'execute' ){
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
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="エクスポートする" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	#--------------------------------------
	#	全記事データのエクスポート
	function download_io_article_export(){
		if( !$this->plog->enable_function_export ){
			return	'<p class="error">エクスポート機能が無効に設定されています。</p>';
		}

		$dao_io = &$this->plog->factory_dao( 'io' );

		$export_tmp_dir = $this->plog->get_home_dir().'/tmp_io/export';
		if( !is_dir( $export_tmp_dir ) ){
			$this->px->dbh()->mkdirall( $export_tmp_dir );
		}
		$result = $dao_io->export( $export_tmp_dir );
		if( $result === false ){
			$RTN = '';
			$RTN .= '	<p class="error">出力ファイルの生成に失敗しました。</p>'."\n";
			return	$RTN;
		}

		if( !is_file( $result ) ){
			$RTN = '';
			$RTN .= '	<p class="error">出力ファイルが存在しません。</p>'."\n";
			return	$RTN;
		}

		return	$this->px->theme()->flush_file( $result , array( 'content-type'=>'x-download/download' , 'filename'=>'plog_articles_'.date('Ymd_Hi').'.tgz' , 'delete'=>true ) );
	}


	###################################################################################################################
	#	全記事データのインポート
	function start_io_article_import(){
		$error = $this->check_io_article_import_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_io_article_import_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_io_article_import_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_io_article_import_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
		}
		return	$this->page_io_article_import_input( $error );
	}
	#--------------------------------------
	#	全記事データのインポート：入力
	function page_io_article_import_input( $error ){
		$RTN = '';

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post" enctype="multipart/form-data">'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>インポートファイル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div><input type="file" name="exported_data" value="" /></div>'."\n";
		if( strlen( $error['exported_data'] ) ){
			$RTN .= '			<div class="error">'.$error['exported_data'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：確認
	function page_io_article_import_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>インポートファイル</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$UPFILE_INFO = $this->px->req()->get_uploadfile('exported_data');
		$RTN .= '			<div>'.htmlspecialchars( $UPFILE_INFO['name'] ).'</div>'."\n";
#		$HIDDEN .= '<input type="hidden" name="exported_data" value="'.htmlspecialchars( $this->px->req()->get_param('exported_data') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="p alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="インポートする" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $this->pagemgr->get_query().'/' ) ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：チェック
	function check_io_article_import_check(){
		$RTN = array();

		$UPFILE_INFO = $this->px->req()->get_param('exported_data');
		if( strlen( $UPFILE_INFO['tmp_name'] ) && is_file( $UPFILE_INFO['tmp_name'] ) ){
			#	ファイルがアップロードされていたら。
			if( !preg_match( '/\.tgz/si' , $UPFILE_INFO['name'] ) ){
				$RTN['exported_data'] = 'TGZファイルのみアップロード可能です。';
			}else{
				$this->px->req()->save_uploadfile( 'exported_data' , $UPFILE_INFO );
			}
		}
		$UPFILE_INFO = $this->px->req()->get_uploadfile('exported_data');

		if( !strlen( $UPFILE_INFO['content'] ) ){
			$RTN['exported_data'] = 'TGZファイルをアップロードしてください。';
		}
		if( !$this->plog->enable_function_import ){
			$RTN['exported_data'] = 'インポート機能が無効に設定されています。';
		}

		if( count( $RTN ) ){
			$this->px->req()->delete_uploadfile_all();
		}

		return	$RTN;
	}
	#--------------------------------------
	#	全記事データのインポート：実行
	function execute_io_article_import_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$dao_io = &$this->plog->factory_dao( 'io' );

		$import_tmp_dir = $this->plog->get_home_dir().'/tmp_io/import';

		if( !is_dir( $import_tmp_dir ) ){
			#	作業ディレクトリを作成
			$this->px->dbh()->mkdirall( $import_tmp_dir );
		}

#		$UPFILE_INFO = $this->px->req()->get_param('exported_data');
		$UPFILE_INFO = $this->px->req()->get_uploadfile('exported_data');
		if( !strlen( $UPFILE_INFO['content'] ) ){
			$RTN = '';
			$RTN .= '<p class="error">アップロードファイルがゼロバイトです。</p>'."\n";
			return	$RTN;
		}
		if( !$this->px->dbh()->file_overwrite( $this->plog->get_home_dir().'/tmp_io/import/export.tgz' , $UPFILE_INFO['content'] ) ){//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
			$RTN = '';
			$RTN .= '<p class="error">アップロードファイルの一時領域への保存に失敗しました。</p>'."\n";
			return	$RTN;
		}
		$UPFILE_INFO['tmp_name'] = $this->plog->get_home_dir().'/tmp_io/import/export.tgz';

		$result = $dao_io->import( $import_tmp_dir , $UPFILE_INFO );
		if( $result === false ){
			$RTN = '';
			$RTN .= '<p class="error">記事データの入力に失敗しました。</p>'."\n";
			return	$RTN;
		}

		#	アップロード一時ファイルの削除
		$this->px->dbh()->rmdir( $this->plog->get_home_dir().'/tmp_io/import/export.tgz' );
		$this->px->req()->delete_uploadfile_all();

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	全記事データのインポート：完了
	function page_io_article_import_thanks(){
		$RTN = '';
		$RTN .= '<p>全記事データのインポート処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'io/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
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
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_update_rss_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_update_rss_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
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

		$RTN .= '<h2>記事の一覧</h2>'."\n";
		$RTN .= '<dl>'."\n";
		$RTN .= $SRC_MEMO;
		$RTN .= '</dl>'."\n";

		$RTN .= '<table style="width:100%;" class="def">'."\n";
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
		$RTN .= '			<div>'.htmlspecialchars( $this->px->theme()->resource( $this->plog->path_rss_xslt['rss1.0'] ) ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->theme()->resource( $this->plog->path_rss_xslt['rss2.0'] ) ).'<br /></div>'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->theme()->resource( $this->plog->path_rss_xslt['atom1.0'] ) ).'<br /></div>'."\n";
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
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="更新する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	RSSの更新：実行
	function execute_update_rss_execute(){
		if( !$this->px->user()->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
		}

		$dao_rss = &$this->plog->factory_dao( 'rss' );
		$result = $dao_rss->update_rss_file();
		if( $result === false ){
			$ERROR = '';
			$error_list = $dao_rss->get_error_list();
			foreach( $error_list as $error_cont ){
				$ERROR .= '	<li class="error">'.htmlspecialchars($error_cont['message']).'</li>'."\n";
			}
			$RTN = '';
			$RTN .= '<p class="error">RSSファイルの書き出し中にエラーが発生しました。</p>'."\n";
			$RTN .= '<ul>'."\n";
			$RTN .= $ERROR;
			$RTN .= '</ul>'."\n";
			return	$RTN;
		}


		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	RSSの更新：完了
	function page_update_rss_thanks(){
		$RTN = '';
		$RTN .= '<p>RSSの更新処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
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
			$SRCMEMO .= '	<dt>From: '.htmlspecialchars( $line['commentator_name'] ).' To: '.$this->pagemgr->mk_link( 'article/'.$line['article_cd'].'/' , array( 'label'=>$line['article_title'] ) ).'</dt>'."\n";
			$SRCMEMO .= '		<dd>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['comment'] ).'</div>'."\n";
			$SRCMEMO .= '			<ul class="horizontal">'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">投稿日: '.htmlspecialchars($line['comment_date']).'</li>'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">'.$this->pagemgr->mk_link( 'comment_list/'.$line['article_cd'].'/' , array('label'=>'この記事のコメント一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '			</ul>'."\n";
			$SRCMEMO .= '		</dd>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '<hr />'."\n";
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
			$SRCMEMO .= '<h2>To: '.$this->pagemgr->mk_link( 'article/'.$line['article_cd'].'/' , array( 'label'=>$line['article_title'] ) ).'</h2>'."\n";
			$SRCMEMO .= '<ul>'."\n";
			$SRCMEMO .= '	<li>From: '.htmlspecialchars( $line['commentator_name'] ).'</li>'."\n";
			if( strlen( $line['commentator_url'] ) ){
				$SRCMEMO .= '	<li>URL: '.$this->pagemgr->mk_link( $line['commentator_url'].'/' ).'</li>'."\n";
			}
			if( strlen( $line['commentator_email'] ) ){
				$SRCMEMO .= '	<li>メールアドレス: '.htmlspecialchars( $line['commentator_email'] ).'</li>'."\n";
			}
			$SRCMEMO .= '	<li>投稿日: '.htmlspecialchars($line['comment_date']).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			$SRCMEMO .= '	<li class="ttrs alignR">'.$this->pagemgr->mk_link( 'comment_list/'.$line['article_cd'].'/' , array('label'=>'この記事のコメント一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_comments/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '<hr />'."\n";
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
			$SRCMEMO .= '	<dt>To: '.$this->pagemgr->mk_link( 'article/'.$line['article_cd'].'/' , array( 'label'=>$line['article_title'] ) ).'</dt>'."\n";
			$SRCMEMO .= '		<dd>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['trackback_blog_name'] ).'</div>'."\n";
			$SRCMEMO .= '			<div>'.$this->pagemgr->mk_link( $line['trackback_url'].'/' , array( 'label'=>$line['trackback_title'] ) ).'</div>'."\n";
			$SRCMEMO .= '			<div>'.htmlspecialchars( $line['trackback_excerpt'] ).'</div>'."\n";
			$SRCMEMO .= '			<ul class="horizontal">'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">投稿日: '.htmlspecialchars($line['trackback_date']).'</li>'."\n";
			$SRCMEMO .= '				<li class="ttrs alignR">'.$this->pagemgr->mk_link( 'tb_list/'.$line['article_cd'].'/' , array('label'=>'この記事のトラックバック一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '			</ul>'."\n";
			$SRCMEMO .= '		</dd>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '<hr />'."\n";
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
			$SRCMEMO .= '<h2>To: '.$this->pagemgr->mk_link( 'article/'.$line['article_cd'].'/' , array( 'label'=>$line['article_title'] ) ).'</h2>'."\n";
			$SRCMEMO .= '<ul>'."\n";
			$SRCMEMO .= '	<li>ブログ名: '.htmlspecialchars( $line['trackback_blog_name'] ).'</li>'."\n";
			$SRCMEMO .= '	<li>記事名: '.$this->pagemgr->mk_link( $line['trackback_url'].'/' , array( 'label'=>$line['trackback_title'],'target'=>'_blank' ) ).'</li>'."\n";
			$SRCMEMO .= '	<li>投稿日: '.htmlspecialchars($line['trackback_date']).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
			$SRCMEMO .= '<ul class="horizontal">'."\n";
			$SRCMEMO .= '	<li class="ttrs alignR">'.$this->pagemgr->mk_link( 'tb_list/'.$line['article_cd'].'/' , array('label'=>'この記事のトラックバック一覧','style'=>'inside') ).'</li>'."\n";
			$SRCMEMO .= '</ul>'."\n";
		}

		$RTN = '';
		$RTN .= '<ul class="horizontal">'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'req_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '	<li>'.$this->pagemgr->mk_link( 'new_trackbacks/' , array('style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '<hr />'."\n";
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
		if( strlen( $this->px->req()->get_param('keyword') ) ){
			return	$this->page_search_result();
		}
		return	$this->page_search();
	}
	function page_search(){
		$RTN = '';
		$RTN .= '<p>探したいキーワードを入力して、「検索する」ボタンをクリックしてください。</p>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'search/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="text" name="keyword" value="'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'" /><input type="submit" value="検索する" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	function page_search_result(){
		$page_number = intval( $this->pagemgr->get_query(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$option = array();
		$dao_admin = &$this->plog->factory_dao('admin');
		$hit_count = $dao_admin->search_article_count( $this->px->req()->get_param('keyword') , $option );
		$pager_info = $this->px->dbh()->get_pager_info( $hit_count , $page_number , 20 );
		$search_result = $dao_admin->search_article( $this->px->req()->get_param('keyword') , $pager_info['offset'] , $pager_info['dpp'] , $option );

		$SRCMEMO = '';
		foreach( $search_result as $result_line ){
			#	仮に結果を出力してみる。
			$SRCMEMO .= '	<tr>'."\n";
			$SRCMEMO .= '		<th><div>'.htmlspecialchars($result_line['article_cd']).'</div></th>'."\n";
			$SRCMEMO .= '		<td><div>'.$this->pagemgr->mk_link( 'article/'.intval($result_line['article_cd']).'/' , array('label'=>$result_line['article_title']) ).'</div></td>'."\n";
			$SRCMEMO .= '	</tr>'."\n";
		}
		
		#--------------------------------------
		#	ページャ生成
		$PID_BASE = '';

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->px->theme()->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->pagemgr->mk_link( 'search/'.$pager_info['prev'].'/'.'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->pagemgr->mk_link( 'search/'.$i.'/'.'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->pagemgr->mk_link( 'search/'.$pager_info['next'].'/'.'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
				}
				$PAGER = '';
				if( $pager_info['total_page_count'] > 1 ){
					$PAGER .= '<p class="center cont_pager">'."\n";
					$PAGER .= implode( ' | ' , $PAGER_ARY )."\n";
					$PAGER .= '</p>'."\n";
				}
			}
		}
		#	/ページャ生成
		#--------------------------------------

		$RTN = '';
		$RTN .= '<p>『<strong>'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'</strong>』で検索した結果、 '.intval( $hit_count ).' 件の記事がヒットしました。</p>'."\n";
		$this->site->setpageinfo( $this->px->req()->po().'.'.$this->pagemgr->get_query() , 'title' , '『'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'』による '.intval( $hit_count ).' 件の検索結果' );

		if( strlen($SRCMEMO) ){
			$RTN .= $PAGER;
			$RTN .= '<table style="width:100%;" class="def">'."\n";
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

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( 'search/' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="text" name="keyword" value="'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'" /><input type="submit" value="再検索" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	###################################################################################################################
	#	検索インデックスの更新
	function start_update_search_index(){
		$error = $this->check_update_search_index_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_update_search_index_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_update_search_index_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
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
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="インデックスを更新する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="get">'."\n";
		$RTN .= '	<input type="submit" value="キャンセル" />'."\n";
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
		$dao_search = $this->plog->factory_dao('search');
		if( !$dao_search->update_all_index() ){
			return	'<p class="error">更新に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}
	#--------------------------------------
	#	検索インデックスの更新：完了
	function page_update_search_index_thanks(){
		$RTN = '';
		$RTN .= '<p>検索インデックスの更新処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



	/**
	 * データベース作成処理
	 */
	private function start_create_db(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_create_db_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_create_db_execute();
		}
		return	$this->page_create_db_confirm();
	}//start_create_db()
	/**
	 * データベース作成処理：確認
	 */
	private function page_create_db_confirm(){
		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	この機能は、PLOG関連テーブルを作成し、初期設定を完了します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
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
		$RTN .= '</div>'."\n";
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<table style="width:100%;" class="def">'."\n";
		$RTN .= '	<caption>作成先のデータベース設定</caption>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>データベース種類</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->px->dbh()->get_db_conf('dbms') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>サーバ名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->px->dbh()->get_db_conf('host') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ポート</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->px->dbh()->get_db_conf('port') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>データベース名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->px->dbh()->get_db_conf('database_name') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ユーザ名</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $this->px->dbh()->get_db_conf('user') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	関連テーブルを手動で作成する場合は、'.$this->pagemgr->mk_link( 'create_db_sql_download/' , array('label'=>'SQLをダウンロード') ).'してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<div class="center">'."\n";
		$RTN .= '<form action="" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	<input type="submit" value="テーブルを作成する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.t::h($this->pagemgr->href('')).'" method="get">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}//page_create_db_confirm()
	/**
	 * データベース作成処理：実行
	 */
	private function execute_create_db_execute(){

		$dao = &$this->plog->factory_dao( 'dbcreate' );
		$result = $dao->create_tables();

		if( !$result ){
			return	'<p class="error">テーブルの作成に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->px->theme()->href($this->px->req()->get_request_file_path()).'?mode=thanks' );
	}//execute_create_db_execute()
	/**
	 * データベース作成処理：完了
	 */
	private function page_create_db_thanks(){
		$RTN = '';
		$RTN .= '<p>データベース作成処理処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}//page_create_db_thanks()


	/**
	 * DBを作成するSQLをダウンロード
	 */
	private function start_create_db_sql_download(){
		$dao = &$this->plog->factory_dao( 'dbcreate' );
		$SQL_SRC = $dao->create_tables( 'GET_SQL_SOURCE' );
		return	$this->px->download( $SQL_SRC , array( 'filename'=>'PLOG_create_db.sql' , 'content-type'=>'x-download/download' ) );
	}//start_create_db_sql_download()





	/**
	 * PLOGプラグイン関連設定項目の確認
	 */
	private function page_configcheck(){
		$RTN = '';

		$RTN .= '<h2>内部パス関連設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">path_home_dir</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_home_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">path_cache_dir</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">path_public_dir</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_public_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">path_rss</th>'."\n";
		$RTN .= '		<td style="width:70%;"><span class="notes">'.htmlspecialchars( $this->conf->path_docroot ).'</span>'.htmlspecialchars( $this->plog->path_rss ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>外部パス関連設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">url_public_dir</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_url_public_cache_dir() ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">url_article</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">url_article_rss</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' , 'rss' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">url_article_admin</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->get_article_url( 'XXXXXX' , 'admin' ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>DBテーブル名設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">article</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->table_name.'_article' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">category</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->table_name.'_category' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">trackback</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->table_name.'_trackback' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->table_name.'_comment' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">search</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->table_name.'_search' ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>ブログプロフィール設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">blog_name</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->blog_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">blog_description</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->blog_description ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">blog_language</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->blog_language ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">blog_author_name</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->blog_author_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>機能制御設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">enable_trackback</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->enable_trackback ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">enable_comments</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->enable_comments ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">trackback_auto_commit</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->trackback_auto_commit ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment_auto_commit</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->comment_auto_commit ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">article_summary_mode</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->article_summary_mode ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment_userinfo_name</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->comment_userinfo_name ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment_userinfo_email</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->comment_userinfo_email ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment_userinfo_url</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->comment_userinfo_url ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">comment_userinfo_passwd</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->comment_userinfo_passwd ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<h2>RSSパス</h2>'."\n";
		$dao_rss = &$this->plog->factory_dao( 'rss' );
		$RTN .= '<table class="def" style="width:100%;">'."\n";
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

		$RTN .= '<h2>その他の設定</h2>'."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">send_tbp_log_name</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->plog->send_tbp_log_name ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">enable_function_export</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->enable_function_export ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">enable_function_import</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->enable_function_import ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">rss_limit_number</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->rss_limit_number ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">reportmail_to</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( t::data2text( $this->plog->reportmail_to ) ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href('') ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";

		return	$RTN;
	}//page_configcheck()

}

?>