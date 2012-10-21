<?php

/**
 * PxFW - Content - [PLOG]
 * (C)Tomoya Koyanagi
 * コンテンツオブジェクトクラス [ cont_plog_contents_article ]
 */
class cont_plog_contents_article{
	public $plog;
	public $px;
	public $pagemgr;
#	public $conf;
#	public $errors;
#	public $dbh;
#	public $req;
#	public $user;
#	public $site;
#	public $theme;
#	public $custom;

	/**
	 * コンストラクタ
	 */
	function cont_plog_contents_article( $plog ){
		$this->plog = $plog;
		$this->px = $plog->px;
#		$this->conf = &$plog->get_basicobj_conf();
#		$this->errors = &$plog->get_basicobj_errors();
#		$this->dbh = &$plog->get_basicobj_dbh();
#		$this->req = &$plog->get_basicobj_req();
#		$this->user = &$plog->get_basicobj_user();
#		$this->site = &$plog->get_basicobj_site();
#		$this->theme = &$plog->get_basicobj_theme();
#		$this->custom = &$plog->get_basicobj_custom();

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

/*
		$this->site->setpageinfoall(
			$this->req->po().'.list' ,
			array(
				'title'=>'カテゴリ別記事一覧' ,
				'path'=>$this->px->site()->get_page_info( $this->req->po() , 'path' ) ,
			)
		);
		if( strlen( $this->pagemgr->get_query(1) ) ){
			$this->site->setpageinfoall(
				$this->req->po().'.article.'.$this->pagemgr->get_query(1) ,
				array(
					'title'=>'記事本文' ,
					'path'=>$this->px->site()->get_page_info( $this->req->po() , 'path' ) ,
				)
			);
			if( $this->plog->enable_comments ){
				$this->site->setpageinfoall(
					$this->req->po().'.create_comment.'.$this->pagemgr->get_query(1) ,
					array(
						'title'=>'コメントの投稿' ,
						'path'=>$this->px->site()->get_page_info( $this->req->po().'.article.'.$this->pagemgr->get_query(1) , 'path' ) ,
						'list_flg'=>true ,
					)
				);
				$this->site->setpageinfoall(
					$this->req->po().'.delete_comment.'.$this->pagemgr->get_query(1) ,
					array(
						'title'=>'コメントの削除' ,
						'path'=>$this->px->site()->get_page_info( $this->req->po().'.article.'.$this->pagemgr->get_query(1) , 'path' ) ,
						'list_flg'=>false ,
					)
				);
			}
		}
		$this->site->setpageinfoall(
			$this->req->po().'.search' ,
			array(
				'title'=>'記事検索' ,
				'path'=>$this->px->site()->get_page_info( $this->req->po() , 'path' ) ,
				'list_flg'=>true ,
			)
		);
		$this->site->setpageinfoall(
			$this->req->po().'.entrylist' ,
			array(
				'title'=>'エントリの一覧' ,
				'path'=>$this->px->site()->get_page_info( $this->req->po() , 'path' ) ,
				'list_flg'=>true ,
			)
		);
*/

		if( $this->pagemgr->get_query() == 'article' ){
			return	$this->page_article();
		}elseif( $this->pagemgr->get_query() == 'list' ){
			return	$this->page_start( $this->pagemgr->get_query(1) );
		}elseif( $this->pagemgr->get_query() == 'create_comment' ){
			return	$this->start_create_comment();
		}elseif( $this->pagemgr->get_query() == 'delete_comment' ){
			return	$this->start_delete_comment();
		}elseif( $this->pagemgr->get_query() == 'tb' ){
			return	$this->execute_trackback_ping_execute();
		}elseif( $this->pagemgr->get_query() == 'search' ){
			return	$this->start_search();
		}elseif( $this->pagemgr->get_query() == 'entrylist' ){
			return	$this->page_entrylist();
		}elseif( !preg_match( '/^[0-9]*$/is' , $this->pagemgr->get_query() ) ){
			return	$this->px->theme()->printnotfound();
		}

		return	$this->page_start();
	}


	#--------------------------------------
	#	最初のページ
	function page_start( $category_cd = null ){
		if( $this->pagemgr->get_query() == 'list' && strlen( $this->pagemgr->get_query(1) ) ){
			$page_number = intval( $this->pagemgr->get_query(2) );
		}else{
			$page_number = intval( $this->pagemgr->get_query() );
		}
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$dao = $this->plog->factory_dao( 'visitor' );
		$pager_info = $this->px->theme()->get_pager_info( $dao->get_article_count( $category_cd ) , $page_number , 5 );

		$article_list = $dao->get_article_list( $category_cd , $pager_info['offset'] , $pager_info['dpp'] );
		$target_article_cd_list = array();
		foreach( $article_list as $line ){
			array_push( $target_article_cd_list , intval($line['article_cd']) );
		}
		if( count( $target_article_cd_list ) ){
			$comment_count = $dao->get_comment_count( $target_article_cd_list );
			$trackback_count = $dao->get_trackback_count( $target_article_cd_list );
		}

		#--------------------------------------
		#	記事全件分のHTMLソースを作成
		$SRCMEMO = '';
		foreach( $article_list as $line ){
			$SRCMEMO .= '<div class="cont_plog_article">'."\n";
			$SRCMEMO .= $this->px->theme()->mk_hx( '<span class="date">'.htmlspecialchars( $this->px->theme()->dateformat( 'date' , time::datetime2int($line['release_date']) ) ).'</span> '.$this->plog->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ) , null , array('allow_html'=>true) )."\n";

			$operator = $this->plog->factory_articleparser();
			$operator->default_hxnum = -1;
			$ARTICLE_BODY_SRC = $operator->get_article_content( $line['article_cd'] , 'article_list' );

			if( $this->plog->article_summary_mode == 'manual' ){
				$SRCMEMO .= '<p>'."\n";
				$SRCMEMO .= preg_replace( '/\r\n|\r|\n/si' , '<br />' , htmlspecialchars( $line['article_summary'] ) )."\n";
				$SRCMEMO .= '</p>'."\n";
			}else{
				$SRCMEMO .= '<div class="hide_screen">'."\n";
				$SRCMEMO .= '<p>'."\n";
				$SRCMEMO .= preg_replace( '/\r\n|\r|\n/si' , '<br />' , htmlspecialchars( $line['article_summary'] ) )."\n";
				$SRCMEMO .= '</p>'."\n";
				$SRCMEMO .= '</div>'."\n";
				if( $this->user->is_pc() ){
					$SRCMEMO .= '<div class="show_screen"><div class="unit">'."\n".$ARTICLE_BODY_SRC.'</div></div>'."\n";
				}
			}
			if( $this->user->is_mp() ){
				$SRCMEMO .= '<div><font size="1">'."\n";
				if( !strlen($line['category_title']) ){
					$SRCMEMO .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$line['category_cd'] , array( 'label'=>'(未分類)' , 'style'=>'inside' , 'active'=>false ) ).'<br />'."\n";
				}else{
					$SRCMEMO .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$line['category_cd'] , array( 'label'=>$line['category_title'] , 'style'=>'inside' , 'active'=>false ) ).'<br />'."\n";
				}
				$SRCMEMO .= '	公開日：'.htmlspecialchars( $this->px->theme()->dateformat( 'datetime' , time::datetime2int($line['release_date']) ) ).'<br />'."\n";
				$SRCMEMO .= '</font></div>'."\n";
				$SRCMEMO .= '<div><font size="1">'."\n";
				$SRCMEMO .= '	'.$this->plog->mk_link( ':article.'.$line['article_cd'] , array('label'=>'記事を読む','style'=>'inside') ).'<br />'."\n";
				if( $this->plog->enable_comments ){
					$SRCMEMO .= '	'.$this->plog->mk_link( ':article.'.$line['article_cd'].'#cont_article_comment' , array('label'=>'コメント['.intval($comment_count[$line['article_cd']]).']件','style'=>'inside') ).'<br />'."\n";
				}
				if( $this->plog->enable_trackback ){
					$SRCMEMO .= '	'.$this->plog->mk_link( ':article.'.$line['article_cd'].'#cont_article_trackback' , array('label'=>'トラックバック['.intval($trackback_count[$line['article_cd']]).']件','style'=>'inside') ).'<br />'."\n";
				}
				$SRCMEMO .= '</font></div>'."\n";
				$SRCMEMO .= '<div><font size="1"><br /></font></div>'."\n";
			}else{
				$SRCMEMO .= '<div class="ttrs">'."\n";
				if( !strlen($line['category_title']) ){
					$SRCMEMO .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$line['category_cd'] , array( 'label'=>'(未分類)' , 'style'=>'inside' , 'active'=>false ) ).''."\n";
				}else{
					$SRCMEMO .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$line['category_cd'] , array( 'label'=>$line['category_title'] , 'style'=>'inside' , 'active'=>false ) ).''."\n";
				}
				$SRCMEMO .= '	公開日：'.htmlspecialchars( $this->px->theme()->dateformat( 'datetime' , time::datetime2int($line['release_date']) ) ).''."\n";
				$SRCMEMO .= '</div>'."\n";
				$SRCMEMO .= '<ul class="horizontal">'."\n";
				$SRCMEMO .= '	<li class="ttrs">'.$this->plog->mk_link( ':article.'.$line['article_cd'] , array('label'=>'記事を読む','style'=>'inside') ).'</li>'."\n";
				if( $this->plog->enable_comments ){
					$SRCMEMO .= '	<li class="ttrs">'.$this->plog->mk_link( ':article.'.$line['article_cd'].'#cont_article_comment' , array('label'=>'コメント['.intval($comment_count[$line['article_cd']]).']件','style'=>'inside') ).'</li>'."\n";
				}
				if( $this->plog->enable_trackback ){
					$SRCMEMO .= '	<li class="ttrs">'.$this->plog->mk_link( ':article.'.$line['article_cd'].'#cont_article_trackback' , array('label'=>'トラックバック['.intval($trackback_count[$line['article_cd']]).']件','style'=>'inside') ).'</li>'."\n";
				}
				$SRCMEMO .= '</ul>'."\n";
			}
			$SRCMEMO .= '</div>'."\n";
		}
		#	/ 記事全件分のHTMLソースを作成
		#--------------------------------------

		#--------------------------------------
		#	ページャ生成
		$PID_BASE = '';
		if( $this->pagemgr->get_query() == 'list' && strlen( $this->pagemgr->get_query(1) ) ){
			$PID_BASE = 'list.'.$this->pagemgr->get_query(1).'.';
		}

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->px->theme()->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$pager_info['prev'] , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$i , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$pager_info['next'] , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
				}
				$PAGER = '';
				if( $pager_info['total_page_count'] > 1 ){
					if( $this->user->is_mp() ){
						$PAGER .= '<table width="100%" bgcolor="#dddddd"><tr><td>'."\n";
						$PAGER .= '<div align="center"><font size="1">'."\n";
						$PAGER .= implode( ' | ' , $PAGER_ARY )."\n";
						$PAGER .= '</font></div>'."\n";
						$PAGER .= '</td></tr></table>'."\n";
						$PAGER .= '<div><font size="1"><br /></font></div>'."\n";
					}else{
						$PAGER .= '<p class="ttr alignC cont_pager">'."\n";
						$PAGER .= implode( ' | ' , $PAGER_ARY )."\n";
						$PAGER .= '</p>'."\n";
					}
				}
			}
		}
		#	/ページャ生成
		#--------------------------------------

		$RTN = '';
		$RTN .= $PAGER;
		$RTN .= ''."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= $SRCMEMO;
		}else{
			$RTN .= '<p>'."\n";
			$RTN .= '	記事はありません。<br />'."\n";
			$RTN .= '</p>'."\n";
		}
		$RTN .= $PAGER;

		$this_pid = $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'id' );
		if( $this->pagemgr->get_query() == 'list' && strlen( $this->pagemgr->get_query(1) ) ){
			#	【カテゴリ別一覧】ページ名を変えてみる。
			$category_info = $dao->get_category_info( $this->pagemgr->get_query(1) );
			$title = 'カテゴリ『'.$category_info['category_title'].'』の記事';
			if( !strlen( $category_info['category_title'] ) ){
				$title = 'カテゴリに分類されていない記事';
			}
#			$this->site->setpageinfo( $this_pid , 'title' , $title.' ('.$pager_info['current'].'/'.$pager_info['total_page_count'].')' );
#			$this->site->setpageinfo( $this_pid , 'title_page' , $title );
#			$this->site->setpageinfo( $this_pid , 'title_breadcrumb' , $title );
#			$this->site->setpageinfo( $this_pid , 'title_label' , $title );//21:41 2009/05/20 PLOG 0.1.5
		}else{
			#	【全部の一覧】ページ名を変えてみる。
			if( $pager_info['current'] != 1 ){
				$title = $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'title' );
#				$this->site->setpageinfo( $this_pid , 'title' , $title.' ('.$pager_info['current'].'/'.$pager_info['total_page_count'].')' );
#				$this->site->setpageinfo( $this_pid , 'title_page' , $title );
#				$this->site->setpageinfo( $this_pid , 'title_breadcrumb' , $title );
#				$this->site->setpageinfo( $this_pid , 'title_label' , $title );//21:41 2009/05/20 PLOG 0.1.5
			}
		}

		return	$RTN;
	}


	#--------------------------------------
	#	エントリの一覧
	function page_entrylist( $category_cd = null ){
		$page_number = intval( $this->pagemgr->get_query(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$dao = $this->plog->factory_dao( 'visitor' );
		$pager_info = $this->px->dbh()->get_pager_info( $dao->get_article_count( $category_cd ) , $page_number , 50 );


		$article_list = $dao->get_article_list( $category_cd , $pager_info['offset'] , $pager_info['dpp'] );
		$target_article_cd_list = array();
		foreach( $article_list as $line ){
			array_push( $target_article_cd_list , intval($line['article_cd']) );
		}
		if( count( $target_article_cd_list ) ){
			$comment_count = $dao->get_comment_count( $target_article_cd_list );
			$trackback_count = $dao->get_trackback_count( $target_article_cd_list );
		}

		#--------------------------------------
		#	記事全件分のHTMLソースを作成
		$SRCMEMO = '';
		foreach( $article_list as $line ){
			$SRCMEMO .= '<li><span class="date">'.htmlspecialchars( $this->px->theme()->dateformat( 'date' , time::datetime2int($line['release_date']) ) ).'</span> '.$this->plog->mk_link( ':article.'.$line['article_cd'] , array( 'label'=>$line['article_title'] ) ).'</li>'."\n";
		}
		#	/ 記事全件分のHTMLソースを作成
		#--------------------------------------

		#--------------------------------------
		#	ページャ生成
		$PID_BASE = 'entrylist.';

		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->px->theme()->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':'.$PID_BASE.'${num}' ) );
			}else{
				$PAGER_ARY = array();
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$pager_info['prev'] , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$i , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':'.$PID_BASE.$pager_info['next'] , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
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
		$RTN .= $PAGER;
		$RTN .= ''."\n";
		if( strlen( $SRCMEMO ) ){
			$RTN .= '<ul>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</ul>'."\n";
		}else{
			$RTN .= '<p>'."\n";
			$RTN .= '	記事はありません。<br />'."\n";
			$RTN .= '</p>'."\n";
		}
		$RTN .= $PAGER;


		#	ページ名を変えてみる。
		if( $pager_info['current'] != 1 ){
			$title = $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'title' );
			$this_pid = $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'id' );
			$this->site->setpageinfo( $this_pid , 'title' , $title.' ('.$pager_info['current'].'/'.$pager_info['total_page_count'].')' );
			$this->site->setpageinfo( $this_pid , 'title_page' , $title );
			$this->site->setpageinfo( $this_pid , 'title_breadcrumb' , $title );
			$this->site->setpageinfo( $this_pid , 'title_label' , $title );//21:41 2009/05/20 PLOG 0.1.5
		}

		return	$RTN;
	}


	#--------------------------------------
	#	記事本文ページ
	function page_article(){
		$dao_visitor = $this->plog->factory_dao( 'visitor' );
		$article_info = $dao_visitor->get_article_info( $this->pagemgr->get_query(1) );
		if( !is_array( $article_info ) ){
			return	$this->px->theme()->printnotfound();
		}

		#	サイトマップ登録
		$this->site->setpageinfo( $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'id' ) , 'title' , $article_info['article_title'] );
		$this->site->setpageinfo( $this->px->site()->get_page_info( $this->px->req()->get_request_file_path() , 'id' ) , 'list_flg' , true );

		$operator = $this->plog->factory_articleparser();
		$ARTICLE_BODY_SRC = $operator->get_article_content( $this->pagemgr->get_query(1) );

		$RTN = '';
		$RTN .= ''."\n";
		$RTN .= $this->plog->get_src('article_header')."\n";
		if( strlen( $ARTICLE_BODY_SRC ) ){
			$RTN .= '<div class="unit">'.$ARTICLE_BODY_SRC.'</div>'."\n";
		}
		$RTN .= '<p class="ttrs alignR">'."\n";
		$RTN .= '	公開日：'.htmlspecialchars( $this->px->theme()->dateformat( 'datetime' , time::datetime2int( $article_info['release_date'] ) ) ).'<br />'."\n";
		if( !strlen($article_info['category_title']) ){
			$RTN .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$article_info['category_cd'] , array( 'label'=>'(未分類)' , 'style'=>'inside' , 'active'=>false ) ).'<br />'."\n";
		}else{
			$RTN .= '	カテゴリ：'.$this->plog->mk_link( ':list.'.$article_info['category_cd'] , array( 'label'=>$article_info['category_title'] , 'style'=>'inside' , 'active'=>false ) ).'<br />'."\n";
		}
		$RTN .= '</p>'."\n";
		$RTN .= $this->plog->get_src('article_footer')."\n";

		#--------------------------------------
		#	次の記事/前の記事
		$next_article_info = $dao_visitor->get_next_article_info( $article_info['release_date'] , true );
		$prev_article_info = $dao_visitor->get_next_article_info( $article_info['release_date'] , false );

		if( is_array( $next_article_info ) || is_array( $prev_article_info ) ){
			$RTN .= '<div class="cont_plog_article_prev_and_next">'."\n";
			$RTN .= '<ul>'."\n";
			if( is_array( $prev_article_info ) ){
				$RTN .= '	<li class="ttr cont_plog_prev_article">'.$this->plog->mk_link( $this->plog->get_article_pid( $prev_article_info['article_cd'] ) , array( 'label'=>mb_strimwidth( $prev_article_info['article_title'] , 0 , 36 , '...' ) , 'style'=>'prev' ) ).'</li>'."\n";
			}
			if( is_array( $next_article_info ) ){
				$RTN .= '	<li class="ttr cont_plog_next_article">'.$this->plog->mk_link( $this->plog->get_article_pid( $next_article_info['article_cd'] ) , array( 'label'=>mb_strimwidth( $next_article_info['article_title'] , 0 , 36 , '...' ) , 'style'=>'next' ) ).'</li>'."\n";
			}
			$RTN .= '</ul>'."\n";
			$RTN .= '</div>'."\n";
		}

		if( $this->plog->enable_trackback ){
			#--------------------------------------
			#	トラックバックを表示
			$trackback_count = $dao_visitor->get_trackback_count( $this->pagemgr->get_query(1) );
			$dao_trackback = &$this->plog->factory_dao( 'trackback' );
			$trackback_list = $dao_trackback->get_trackback_list( $this->pagemgr->get_query(1) );

			$SRCMEMO = '';
			foreach( $trackback_list as $Line ){
				$SRCMEMO .= $this->px->theme()->mk_hx( '<span class="date">'.htmlspecialchars( $this->px->theme()->dateformat( 'datetime' , time::datetime2int( $Line['trackback_date'] ) ) ).'</span> '.$this->plog->mk_link( $Line['trackback_url'] , array( 'label'=>$Line['trackback_title'] ) ) , -1 , array( 'allow_html'=>true ) )."\n";
				$SRCMEMO .= '<p>'.text::text2html( $Line['trackback_excerpt'] ).'</p>'."\n";
				$SRCMEMO .= '<p class="ttrs alignR">( '.htmlspecialchars( $Line['trackback_blog_name'] ).' - '.$this->plog->mk_link( $Line['trackback_url'] , array( 'label'=>'記事を開く','style'=>'inside' ) ).')</p>'."\n";
				$SRCMEMO .= $this->px->theme()->mk_hr()."\n";
			}

			$RTN .= '<div id="cont_article_trackback">'."\n";
			$RTN .= '	<div class="p cont_trackback2thisarticle">'."\n";
			$RTN .= '		この記事のトラックバック先：<br />'."\n";
			$gene_deltemp = array(
				'ID',
				'PW',
				'CT',
				'OUTLINE',
				'THEME',
				'LANG',
				'T1',
			);
			$RTN .= '		<div style="overflow:auto;">'.htmlspecialchars( $this->px->theme()->href( ':tb.'.$this->pagemgr->get_query(1) , array('protocol'=>'http','gene_deltemp'=>$gene_deltemp) ) ).'</div>'."\n";
			$RTN .= '	</div>'."\n";
			$RTN .= $this->px->theme()->mk_hx('トラックバック ('.intval($trackback_count[$this->pagemgr->get_query(1)]).'件)')."\n";
			if( strlen( $SRCMEMO ) ){
				$RTN .= $SRCMEMO;
			}else{
				$RTN .= '<p>表示できるトラックバックはありません。</p>'."\n";
			}
			$RTN .= '</div>'."\n";
			#	/ トラックバックを表示
			#--------------------------------------
		}


		if( $this->plog->enable_comments ){
			#--------------------------------------
			#	コメントを表示
			$comment_count = $dao_visitor->get_comment_count( $this->pagemgr->get_query(1) );
			$obj_comment = &$this->plog->factory_dao( 'comment' );
			$comment_list = $obj_comment->get_comment_list( $this->pagemgr->get_query(1) );

			$SRCMEMO = '';
			foreach( $comment_list as $Line ){
				if( !strlen( $Line['commentator_name'] ) ){
					$Line['commentator_name'] = 'No Name';
				}
				$commentator_name = htmlspecialchars( $Line['commentator_name'] );
				if( strlen( $Line['commentator_url'] ) ){
					$commentator_name = '<a href="'.htmlspecialchars( $Line['commentator_url'] ).'" onclick="window.open(this.href,\'_blank\');return false;">'.$commentator_name.'</a>';
				}
				$SRCMEMO .= $this->px->theme()->mk_hx( '<span class="date">'.htmlspecialchars( $this->px->theme()->dateformat( 'datetime' , time::datetime2int( $Line['comment_date'] ) ) ).'</span> '.$commentator_name , -1 , array('allow_html'=>true) )."\n";
				if( $this->plog->comment_userinfo_passwd && strlen( $Line['password'] ) ){
					$SRCMEMO .= '<ul class="horizontal floatR">'."\n";
					$SRCMEMO .= '	<li class="ttrs">'.$this->plog->mk_link( ':delete_comment.'.$this->pagemgr->get_query(1).'.'.$Line['keystr'] , array( 'label'=>'このコメントを削除する','style'=>'inside' ) ).'</li>'."\n";
					$SRCMEMO .= '</ul>'."\n";
				}
				$SRCMEMO .= '<p>'.$obj_comment->view_comment2html( $Line['comment'] ).'</p>'."\n";
				$SRCMEMO .= $this->px->theme()->mk_hr()."\n";
			}

			$RTN .= '<div id="cont_article_comment">'."\n";
			if( strlen( $SRCMEMO ) ){
				$RTN .= $this->px->theme()->mk_hx('コメント ('.intval($comment_count[$this->pagemgr->get_query(1)]).'件)')."\n";
				$RTN .= $SRCMEMO;
				$RTN .= $this->px->theme()->mk_hx('この記事にコメントする',-1)."\n";
			}else{
				$RTN .= $this->px->theme()->mk_hx('この記事にコメントする')."\n";
			}
			$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( $this->req->po().'.create_comment.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
			$RTN .= '<div>'."\n";
			$RTN .= '	<table width="100%">'."\n";
			$mustmark = array( 'must'=>' <span class="must">*</span>' );
			if( $this->plog->comment_userinfo_name ){
				$RTN .= '		<tr>'."\n";
				$RTN .= '			<th>お名前'.$mustmark[$this->plog->comment_userinfo_name].'</th>'."\n";
				$RTN .= '			<td><input type="text" name="commentator_name" value="'.htmlspecialchars( $this->user->getname() ).'" class="inputitems" /></td>'."\n";
				$RTN .= '		</tr>'."\n";
			}
			if( $this->plog->comment_userinfo_email ){
				$RTN .= '		<tr>'."\n";
				$RTN .= '			<th>メールアドレス'.$mustmark[$this->plog->comment_userinfo_email].'</th>'."\n";
				$RTN .= '			<td><input type="text" name="commentator_email" value="'.htmlspecialchars( $this->user->getemail() ).'" class="inputitems" /></td>'."\n";
				$RTN .= '		</tr>'."\n";
			}
			if( $this->plog->comment_userinfo_url ){
				$RTN .= '		<tr>'."\n";
				$RTN .= '			<th>あなたのサイトのURL'.$mustmark[$this->plog->comment_userinfo_url].'</th>'."\n";
				$RTN .= '			<td><input type="text" name="commentator_url" value="" class="inputitems" /></td>'."\n";
				$RTN .= '		</tr>'."\n";
			}
			if( $this->plog->comment_userinfo_passwd ){
				$RTN .= '		<tr>'."\n";
				$RTN .= '			<th>削除用パスワード'.$mustmark[$this->plog->comment_userinfo_passwd].'</th>'."\n";
				$RTN .= '			<td><input type="text" name="commentpw" value="" class="inputitems" /></td>'."\n";
				$RTN .= '		</tr>'."\n";
			}
			if( strlen( $this->plog->helpers['captcha']['name'] ) ){
				$RTN .= '	<tr>'."\n";
				$RTN .= '		<th><div>スパム対策'.$mustmark['must'].'</div></th>'."\n";
				$RTN .= '		<td>'."\n";
				if( $this->plog->helpers['captcha']['name'] == 'kcaptcha' ){
					#	kcaptcha を使う設定の場合
					$RTN .= '			<div><img src="'.htmlspecialchars( $this->plog->helpers['captcha']['url'] ).'/index.php?'.session_name().'='.session_id().'" alt="" /></div>'."\n";
					$RTN .= '			<div><input type="text" name="captchastring" value="" class="inputitems" /></div>'."\n";
					if( strlen( $error['captchastring'] ) ){
						$RTN .= '			<div class="ttr error">'.$error['captchastring'].'</div>'."\n";
					}
				}
				$RTN .= '		</td>'."\n";
				$RTN .= '	</tr>'."\n";
			}
			$RTN .= '	</table>'."\n";
			$RTN .= '	<p class="ttr alignC"><textarea name="comment" class="inputitems" rows="11" cols="20">'.htmlspecialchars( $this->px->req()->get_param('comment') ).'</textarea></p>'."\n";
			$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="コメントを投稿する" /></p>'."\n";
			$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
			$RTN .= $this->px->theme()->mk_form_defvalues( $this->req->po().'.create_comment.'.$this->pagemgr->get_query(1) )."\n";
			$RTN .= '</div>'."\n";
			$RTN .= '</form>'."\n";
			$RTN .= '</div>'."\n";
			#	/ コメントを表示
			#--------------------------------------
		}

		return	$RTN;
	}





	###################################################################################################################
	#	コメントの投稿
	function start_create_comment(){
		if( !$this->plog->enable_comments ){
			return	$this->px->theme()->printnotfound();
		}
		if( !strlen( $this->pagemgr->get_query(1) ) ){
			return	$this->px->theme()->printnotfound();
		}
		$error = $this->check_create_comment_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_create_comment_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_create_comment_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_create_comment_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			$this->req->delsession('cont_captchastring_corrected');
			$this->req->delsession('captcha_keystring');
		}
		return	$this->page_create_comment_input( $error );
	}
	#--------------------------------------
	#	コメントの投稿：入力
	function page_create_comment_input( $error ){
		$RTN = ''."\n";
		$RTN .= '<div id="cont_article_comment">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '<div>'."\n";
		$RTN .= '<table width="100%">'."\n";
		$mustmark = array( 'must'=>' <span class="must">*</span>' );
		if( $this->plog->comment_userinfo_name ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>お名前'.$mustmark[$this->plog->comment_userinfo_name].'</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div><input type="text" name="commentator_name" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_name') ).'" class="inputitems" /></div>'."\n";
			if( strlen( $error['commentator_name'] ) ){
				$RTN .= '			<div class="ttr error">'.$error['commentator_name'].'</div>'."\n";
			}
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_email ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>メールアドレス'.$mustmark[$this->plog->comment_userinfo_email].'</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div><input type="text" name="commentator_email" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_email') ).'" class="inputitems" /></div>'."\n";
			if( strlen( $error['commentator_email'] ) ){
				$RTN .= '			<div class="ttr error">'.$error['commentator_email'].'</div>'."\n";
			}
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_url ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>あなたのサイトのURL'.$mustmark[$this->plog->comment_userinfo_url].'</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div><input type="text" name="commentator_url" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_url') ).'" class="inputitems" /></div>'."\n";
			if( strlen( $error['commentator_url'] ) ){
				$RTN .= '			<div class="ttr error">'.$error['commentator_url'].'</div>'."\n";
			}
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_passwd ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>削除用パスワード'.$mustmark[$this->plog->comment_userinfo_passwd].'</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div><input type="text" name="commentpw" value="'.htmlspecialchars( $this->px->req()->get_param('commentpw') ).'" class="inputitems" /></div>'."\n";
			if( strlen( $error['commentpw'] ) ){
				$RTN .= '			<div class="ttr error">'.$error['commentpw'].'</div>'."\n";
			}
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( strlen( $this->plog->helpers['captcha']['name'] ) ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>スパム対策'.$mustmark['must'].'</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			if( $this->plog->helpers['captcha']['name'] == 'kcaptcha' ){
				#	kcaptcha を使う設定の場合
				if( strlen( $this->req->getsession('cont_captchastring_corrected') ) && $this->req->getsession('cont_captchastring_corrected') == $this->req->getsession('captcha_keystring') ){
					$RTN .= '			<div>パスしています。</div>'."\n";
				}else{
					$RTN .= '			<div><img src="'.htmlspecialchars( $this->plog->helpers['captcha']['url'] ).'/index.php?'.session_name().'='.session_id().'" alt="" /></div>'."\n";
					$RTN .= '			<div><input type="text" name="captchastring" value="" class="inputitems" /></div>'."\n";
					if( strlen( $error['captchastring'] ) ){
						$RTN .= '			<div class="ttr error">'.$error['captchastring'].'</div>'."\n";
					}
				}
			}
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		$RTN .= '</table>'."\n";
		$RTN .= '	<p>'."\n";
		$RTN .= '		<textarea name="comment" class="inputitems" rows="11" cols="20">'.htmlspecialchars( $this->px->req()->get_param('comment') ).'</textarea><br />'."\n";
		if( strlen( $error['comment'] ) ){
			$RTN .= '		<span class="ttr error">'.$error['comment'].'</span><br />'."\n";
		}
		$RTN .= '	</p>'."\n";

		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="コメントを確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues()."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの投稿：確認
	function page_create_comment_confirm(){
		$obj_comment = &$this->plog->factory_dao( 'comment' );
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<div id="cont_article_comment">'."\n";
		$RTN .= '<table width="100%">'."\n";
		if( $this->plog->comment_userinfo_name ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>お名前</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('commentator_name') ).'</div>'."\n";
			$HIDDEN .= '<input type="hidden" name="commentator_name" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_name') ).'" />';
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_email ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>メールアドレス</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('commentator_email') ).'</div>'."\n";
			$HIDDEN .= '<input type="hidden" name="commentator_email" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_email') ).'" />';
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_url ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>あなたのサイトのURL</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('commentator_url') ).'</div>'."\n";
			$HIDDEN .= '<input type="hidden" name="commentator_url" value="'.htmlspecialchars( $this->px->req()->get_param('commentator_url') ).'" />';
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
		if( $this->plog->comment_userinfo_passwd ){
			$RTN .= '	<tr>'."\n";
			$RTN .= '		<th><div>削除用パスワード</div></th>'."\n";
			$RTN .= '		<td>'."\n";
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('commentpw') ).'</div>'."\n";
			$HIDDEN .= '<input type="hidden" name="commentpw" value="'.htmlspecialchars( $this->px->req()->get_param('commentpw') ).'" />';
			$RTN .= '		</td>'."\n";
			$RTN .= '	</tr>'."\n";
		}
#		if( strlen( $this->plog->helpers['captcha']['name'] ) ){
#			#	kcaptcha を使う設定の場合
#			$HIDDEN .= '<input type="hidden" name="captchastring" value="'.htmlspecialchars( $this->px->req()->get_param('captchastring') ).'" />';
#		}
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th><div>コメント</div></th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div>'.$obj_comment->view_comment2html( $this->px->req()->get_param('comment') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="comment" value="'.htmlspecialchars( $this->px->req()->get_param('comment') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="コメントを投稿する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->px->theme()->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':' ) ).'" method="get">'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':' )."\n";
		$RTN .= '	<div align="center"><input type="submit" name="s" value="キャンセル" /></div>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの投稿：チェック
	function check_create_comment_check(){
		$RTN = array();
		if( !strlen( $this->px->req()->get_param('commentator_name') ) ){
			if( $this->plog->comment_userinfo_name === 'must' ){
				$RTN['commentator_name'] = 'お名前は必ず入力してください。';
			}
		}elseif( preg_match( '/\r\n|\r|\n/si' , $this->px->req()->get_param('commentator_name') ) ){
			$RTN['commentator_name'] = 'お名前に改行を含めることはできません。';
		}elseif( text::mdc_exists( $this->px->req()->get_param('commentator_name') ) ){
			$RTN['commentator_name'] = 'お名前に使用できない文字が含まれています。';
		}elseif( strlen( $this->px->req()->get_param('commentator_name') ) > 64 ){
			$RTN['commentator_name'] = 'お名前が長すぎます。('.strlen( $this->px->req()->get_param('commentator_name') ).'/64)';
		}
		if( !strlen( $this->px->req()->get_param('commentator_email') ) ){
			if( $this->plog->comment_userinfo_email === 'must' ){
				$RTN['commentator_email'] = 'メールアドレスは必ず入力してください。';
			}
		}elseif( preg_match( '/\r\n|\r|\n/si' , $this->px->req()->get_param('commentator_email') ) ){
			$RTN['commentator_email'] = 'メールアドレスに改行を含めることはできません。';
		}elseif( text::mdc_exists( $this->px->req()->get_param('commentator_email') ) ){
			$RTN['commentator_email'] = 'メールアドレスに使用できない文字が含まれています。';
		}elseif( strlen( $this->px->req()->get_param('commentator_email') ) > 128 ){
			$RTN['commentator_email'] = 'メールアドレスが長すぎます。('.strlen( $this->px->req()->get_param('commentator_email') ).'/128)';
		}
		if( !strlen( $this->px->req()->get_param('commentator_url') ) ){
			if( $this->plog->comment_userinfo_url === 'must' ){
				$RTN['commentator_url'] = 'URLは必ず入力してください。';
			}
		}elseif( preg_match( '/\r\n|\r|\n/si' , $this->px->req()->get_param('commentator_url') ) ){
			$RTN['commentator_url'] = 'URLに改行を含めることはできません。';
		}elseif( text::mdc_exists( $this->px->req()->get_param('commentator_url') ) ){
			$RTN['commentator_url'] = 'URLに使用できない文字が含まれています。';
		}elseif( !preg_match( '/^https?:\/\//si' , $this->px->req()->get_param('commentator_url') ) ){
			$RTN['commentator_url'] = 'URLの形式が不正です。';
		}elseif( strlen( $this->px->req()->get_param('commentator_url') ) > 255 ){
			$RTN['commentator_url'] = 'URLが長すぎます。('.strlen( $this->px->req()->get_param('commentator_url') ).'/255)';
		}

		if( preg_match( '/\r\n|\r|\n/si' , $this->px->req()->get_param('commentpw') ) ){
			$RTN['commentpw'] = '削除用パスワードに改行を含めることはできません。';
		}elseif( text::mdc_exists( $this->px->req()->get_param('commentpw') ) ){
			$RTN['commentpw'] = '削除用パスワードに使用できない文字が含まれています。';
		}elseif( strlen( $this->px->req()->get_param('commentpw') ) > 64 ){
			$RTN['commentpw'] = '削除用パスワードが長すぎます。('.strlen( $this->px->req()->get_param('commentpw') ).'/64)';
		}

		if( !strlen( $this->px->req()->get_param('comment') ) ){
			$RTN['comment'] = 'コメントを入力してください。';
		}elseif( text::mdc_exists( $this->px->req()->get_param('comment') ) ){
			$RTN['comment'] = 'コメントに使用できない文字が含まれています。';
		}elseif( strlen( $this->px->req()->get_param('comment') ) > 1024 ){
			$RTN['comment'] = 'コメントが長すぎます。('.strlen( $this->px->req()->get_param('comment') ).'/1024)';
		}

		if( $this->plog->helpers['captcha']['name'] == 'kcaptcha' ){
			#	kcaptcha を使う設定の場合
			if( strlen( $this->req->getsession('captcha_keystring') ) && $this->req->getsession('captcha_keystring') == $this->px->req()->get_param('captchastring') ){
				#	Correct!
				$this->req->setsession('cont_captchastring_corrected',$this->px->req()->get_param('captchastring'));//一度パスしたら、パスした文字列をセッションにメモる
			}elseif( strlen( $this->req->getsession('cont_captchastring_corrected') ) && $this->req->getsession('cont_captchastring_corrected') == $this->req->getsession('captcha_keystring') ){
				#	過去に一度パスしてる場合
			}else{
				#	Wrong!!
				$RTN['captchastring'] = '文字列が違います。';
			}
		}

		return	$RTN;
	}
	#--------------------------------------
	#	コメントの投稿：実行
	function execute_create_comment_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->theme()->redirect( $this->px->req()->get_request_file_path() , 'mode=thanks' );
		}

		$status = 0;
		if( $this->plog->comment_auto_commit ){
			#	コメントの即座反映設定の反映
			$status = 1;
		}

		$comment_dao = &$this->plog->factory_dao( 'comment' );
		$result = $comment_dao->add_comment(
			$this->pagemgr->get_query(1) ,
			$this->px->req()->get_param('comment').'' ,
			$this->px->req()->get_param('commentator_name').'' ,
			$this->px->req()->get_param('commentator_email').'' ,
			$this->px->req()->get_param('commentator_url').'' ,
			$this->px->req()->get_param('commentpw').'' ,
			$_SERVER['REMOTE_ADDR'] ,
			$status
		);

		if( !$result ){
			return	'<p class="ttr error">申し訳ございません。<br />コメントの保存に失敗しました。</p>';
		}

		return	$this->px->theme()->redirect( $this->px->req()->get_request_file_path() , 'mode=thanks' );
	}
	#--------------------------------------
	#	コメントの投稿：完了
	function page_create_comment_thanks(){
		$RTN = ''."\n";
		$RTN .= '<p>コメントの投稿処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<input type="submit" name="s" value="戻る" />'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':article.'.$this->pagemgr->get_query(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

	###################################################################################################################
	#	コメントの削除
	function start_delete_comment(){
		if( !$this->plog->enable_comments ){
			return	$this->px->theme()->printnotfound();
		}
		if( !strlen( $this->pagemgr->get_query(1) ) || !strlen( $this->pagemgr->get_query(2) ) ){
			return	$this->px->theme()->printnotfound();
		}
		$error = $this->check_delete_comment_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_delete_comment_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_delete_comment_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_delete_comment_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
		}
		return	$this->page_delete_comment_input( $error );
	}
	#--------------------------------------
	#	コメントの削除：入力
	function page_delete_comment_input( $error ){
		$RTN = ''."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	コメントを削除します。コメントの削除には、コメント投稿時に指定したパスワードが必要です。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	パスワードを入力して「確認する」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<table width="100%" class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th width="30%"><div>パスワード</div></th>'."\n";
		$RTN .= '		<td width="70%">'."\n";
		$RTN .= '			<div><input type="text" name="commentpw" value="'.htmlspecialchars( $this->px->req()->get_param('commentpw') ).'" class="inputitems" /></div>'."\n";
		if( strlen( $error['commentpw'] ) ){
			$RTN .= '			<div class="ttr error">'.$error['commentpw'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="submit" name="s" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues()."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの削除：確認
	function page_delete_comment_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	パスワードが確認されました。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	コメントを削除します。この操作は元に戻すことができませんが、よろしいですか？<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	よろしければ、「コメントを削除する」ボタンをクリックして、削除を実行してください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$HIDDEN .= '<input type="hidden" name="commentpw" value="'.htmlspecialchars( $this->px->req()->get_param('commentpw') ).'" />';

		$RTN .= '<div class="alignC">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues()."\n";
		$RTN .= '	<input type="submit" name="s" value="コメントを削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= $this->px->theme()->mk_hr()."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':article.'.$this->pagemgr->get_query(1) )."\n";
		$RTN .= '	<div align="center"><input type="submit" name="s" value="キャンセル" /></div>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	#--------------------------------------
	#	コメントの削除：チェック
	function check_delete_comment_check(){
		$RTN = array();

		if( !strlen( $this->px->req()->get_param('commentpw') ) ){
			$RTN['commentpw'] = 'パスワードを入力してください。';
		}else{
			$comment_dao = &$this->plog->factory_dao( 'comment' );
			if( !$comment_dao->check_password( $this->pagemgr->get_query(1) , $this->pagemgr->get_query(2) , $this->px->req()->get_param('commentpw') ) ){
				$RTN['commentpw'] = 'パスワードが違います。';
			}
		}

		return	$RTN;
	}
	#--------------------------------------
	#	コメントの削除：実行
	function execute_delete_comment_execute(){
		if( !$this->user->save_t_lastaction() ){
			#	2重書き込み防止
			return	$this->px->theme()->redirect( $this->px->req()->get_request_file_path() , 'mode=thanks' );
		}

		$comment_dao = &$this->plog->factory_dao( 'comment' );
		$result = $comment_dao->delete_comment( $this->pagemgr->get_query(1) , $this->pagemgr->get_query(2) );
		if( !$result ){
			return	'<p class="ttr error">コメントの削除に失敗しました。</p>';
		}

		return	$this->px->theme()->redirect( $this->px->req()->get_request_file_path() , 'mode=thanks' );
	}
	#--------------------------------------
	#	コメントの削除：完了
	function page_delete_comment_thanks(){
		$RTN = ''."\n";
		$RTN .= '<p>コメントを削除しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':article.'.$this->pagemgr->get_query(1) ) ).'" method="post">'."\n";
		$RTN .= '	<input type="submit" name="s" value="記事本文へ戻る" />'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':article.'.$this->pagemgr->get_query(1) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	#--------------------------------------
	#	トラックバックピングを受け付ける
	function execute_trackback_ping_execute(){
		if( !$this->plog->enable_trackback ){
			return	$this->px->theme()->printnotfound();
		}

		$className = $this->plog->require_lib( '/PLOG/resources/tbp.php' );
		if( !$className ){
			#	トラックバックオブジェクトのインスタンス化に失敗
			$RTN = '';
			$RTN .= '<'.'?xml version="1.0" encoding="iso-8859-1"?'.'>'."\n";
			$RTN .= '<response>'."\n";
			$RTN .= '<error>1</error>'."\n";
			$RTN .= '<message>Internal Server Error.</message>'."\n";
			$RTN .= '</response>'."\n";
			return	$this->px->theme()->download( $RTN , array( 'content-type'=>'application/xml' ) );
		}

		$tbp = new $className( &$this->plog , &$this->conf , &$this->dbh , &$this->theme );
		return	$tbp->receive_trackback_ping( $this->px->req()->get_param() , $this->pagemgr->get_query(1) );

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
		$RTN .= '	<p>'."\n";
		$RTN .= '		探したいキーワードを入力して、「検索する」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '	</p>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':search' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="text" name="keyword" value="'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'" /><input type="submit" name="s" value="検索する" /></p>'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':search' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	function page_search_result(){
		$page_number = intval( $this->pagemgr->get_query(1) );
		if( $page_number < 1 ){
			$page_number = 1;
		}

		$option = array();
		$dao_admin = &$this->plog->factory_dao('visitor');
		$hit_count = $dao_admin->search_article_count( $this->px->req()->get_param('keyword') , $option );
		$pager_info = $this->px->dbh()->get_pager_info( $hit_count , $page_number , 10 );
		$search_result = $dao_admin->search_article( $this->px->req()->get_param('keyword') , $pager_info['offset'] , $pager_info['dpp'] , $option );

		$SRCMEMO = '';
		foreach( $search_result as $result_line ){
			#	仮に結果を出力してみる。
			$SRCMEMO .= '	<dt>'.$this->plog->mk_link( ':article.'.intval($result_line['article_cd']) , array('label'=>$result_line['article_title']) ).'</dt>'."\n";
			$SRCMEMO .= '		<dd>'.text::text2html($result_line['article_summary']).'</dd>'."\n";
		}
		
		#--------------------------------------
		#	ページャ生成
		$PAGER_ARY = array();
		if( $pager_info['total_page_count'] > 1 ){
			if( is_callable( array( $this->theme , 'mk_pager' ) ) ){
				//	PLOG 0.1.6 追加 : $theme->mk_pager() が使えたら、そっちに従う。
				$PAGER = $this->px->theme()->mk_pager( $pager_info['tc'] , $pager_info['current'] , $pager_info['dpp'] , array( 'href'=>':search.${num}?keyword='.urlencode($this->px->req()->get_param('keyword')) ) );
			}else{
				if( $pager_info['prev'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':search.'.$pager_info['prev'].'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>'<前の'.$pager_info['dpp'].'件','active'=>false) ) );
				}
				for( $i = intval($pager_info['index_start']); $i <= intval($pager_info['index_end']); $i ++ ){
					if( $i == $pager_info['current'] ){
						array_push( $PAGER_ARY , '<strong>'.$i.'</strong>' );
					}else{
						array_push( $PAGER_ARY , $this->plog->mk_link( ':search.'.$i.'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>$i,'active'=>false) ) );
					}
				}
				if( $pager_info['next'] ){
					array_push( $PAGER_ARY , $this->plog->mk_link( ':search.'.$pager_info['next'].'?keyword='.urlencode($this->px->req()->get_param('keyword')) , array('label'=>'次の'.$pager_info['dpp'].'件>','active'=>false) ) );
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
		$RTN .= '<p>『'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'』による '.intval( $hit_count ).'件 の検索結果</p>'."\n";
		$RTN .= $this->px->theme()->mk_hr()."\n";
		if( strlen($SRCMEMO) ){
			if( strlen( $PAGER ) ){
				$RTN .= $PAGER;
				$RTN .= $this->px->theme()->mk_hr()."\n";
			}
			$RTN .= '<dl>'."\n";
			$RTN .= $SRCMEMO;
			$RTN .= '</dl>'."\n";
			$RTN .= $this->px->theme()->mk_hr()."\n";
			if( strlen( $PAGER ) ){
				$RTN .= $PAGER;
				$RTN .= $this->px->theme()->mk_hr()."\n";
			}
		}
		$RTN .= '<form action="'.htmlspecialchars( $this->pagemgr->href( ':search' ) ).'" method="post">'."\n";
		$RTN .= '	<p class="ttr alignC"><input type="text" name="keyword" value="'.htmlspecialchars( $this->px->req()->get_param('keyword') ).'" /><input type="submit" name="s" value="再検索" /></p>'."\n";
		$RTN .= '	'.$this->px->theme()->mk_form_defvalues( ':search' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}



}


?>