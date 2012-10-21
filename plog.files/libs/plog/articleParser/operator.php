<?php

#	Copyright (C)Tomoya Koyanagi, All rights reserved.
#	Last Update : 23:16 2010/07/04

#------------------------------------------------------------------------------------------------------------------
#	コンテンツ解析オブジェクトクラス [ cont_plog_articleParser_operator ]
class cont_plog_articleParser_operator{
	var $plog;
	var $px;

	var $BLOCK_MODE = 'p';

	var $preview_mode = false;

	#--------------------------------------
	#	コンストラクタ
	function cont_plog_articleParser_operator( $plog ){
		$this->plog = $plog;
		$this->px = $plog->px;
	}



	#--------------------------------------
	#	記事本文のHTMLソースを作成して返す。
	function get_article_content( $article_cd , $type = null ){
		$article_cd = intval( $article_cd );
		if( $article_cd < 1 ){
			return	false;
		}
		$this->article_cd = $article_cd;

		$path_cache = $this->get_cache_file_path( $article_cd );

		if( $this->is_cache_file( $article_cd ) ){
			return	$this->load_cache( $path_cache , $type );
		}

		$ORIGINALSRC = $this->get_original_src( $article_cd );
		$CAHCE_SRC = $this->src_original2php( $ORIGINALSRC );

		if( !is_dir( dirname( $path_cache ) ) ){
			if( !$this->px->dbh()->mkdir_all( dirname( $path_cache ) ) ){
				return	false;
			}
		}
		if( !$this->px->dbh()->file_overwrite( $path_cache , $CAHCE_SRC ) ){
			return	false;
		}
		$this->px->dbh()->fclose( $path_cache );

		return	$this->load_cache( $path_cache , $type );
	}

	/**
	 * 作成中の記事本文のプレビューを作成して返す。
	 */
	public function get_article_content_preview( $ORIGINALSRC , $type = null ){
		return $ORIGINALSRC;
/**
		//↓一旦コメントアウト
		$this->preview_mode = true;	//←プレビューモードのスイッチを入れる

		$CAHCE_SRC = $this->src_original2php( $ORIGINALSRC );

		$path_cache = dirname( $this->get_cache_file_path( $article_cd ) ).'/'.session_id().time().'.php';
		if( !is_dir( dirname( $path_cache ) ) ){
			if( !$this->px->dbh()->mkdir_all( dirname( $path_cache ) ) ){
				$this->preview_mode = false;
				return	false;
			}
		}
		if( !$this->px->dbh()->file_overwrite( $path_cache , $CAHCE_SRC ) ){//PLOG 0.1.9 : savefile()をfile_overwrite()に変更。Windowsでキャッシュを開けないバグへの対応。
			$this->preview_mode = false;
			return	false;
		}
		$this->px->dbh()->fclose( $path_cache );
		clearstatcache();

		$RTN = $this->load_cache( $path_cache , $type );
		$this->px->dbh()->rmdir( $path_cache );

		$this->preview_mode = false;
		return	$RTN;
**/
	}//get_article_content_preview()





	#--------------------------------------
	#	オリジナルソースをPHPスクリプト形式のキャッシュにする
	function src_original2php( $ary_ORIGINALSRC ){
		$RTN = '';

		$SRCMEMO = '';
		$this->BLOCK_MODE = 'p';
		$MODE_LOCK = false;

		if( is_string( $ary_ORIGINALSRC ) ){
			$ary_ORIGINALSRC = preg_split( '/\r|\n|\r|\n/' , $ary_ORIGINALSRC );
		}
		if( is_array( $ary_ORIGINALSRC ) ){

			foreach( $ary_ORIGINALSRC as $LINE ){
				if( preg_match( '/^-- /si' , $LINE ) ){
					#	コメント行
					continue;
				}

				$LAST_BLOCK_MODE = $this->BLOCK_MODE;

				preg_match( '/^([\t| ]*)/is' , $LINE , $result );
				$indentNumber = strlen( $result[1] );

				$LINE = trim($LINE);

				if( !strlen( $LINE ) ){
					#	段落の区切り
					$RTN .= $this->finish( $LAST_BLOCK_MODE , $SRCMEMO );
					$SRCMEMO = '';
					continue;
				}
				if( preg_match( '/^<html>$/si' , $LINE ) ){
					#	HTML開始
					$MODE_LOCK = true;
					$this->BLOCK_MODE = 'html';
					$LINE = '';
				}elseif( preg_match( '/^<\/html>$/si' , $LINE ) ){
					#	HTML終了
					$RTN .= $this->finish( $LAST_BLOCK_MODE , $SRCMEMO );
					$SRCMEMO = '';
					$MODE_LOCK = false;
					$this->BLOCK_MODE = 'p';
					$LINE = '';
					continue;
				}elseif( preg_match( '/^---$/si' , $LINE , $preg_result ) ){
					#	水平線
					$this->BLOCK_MODE = 'hr';
					$LINE = trim($preg_result[0]);
				}elseif( preg_match( '/^【(.*?)】$/si' , $LINE , $preg_result ) ){
					#	見出し(h2)
					$this->BLOCK_MODE = 'h2';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^■(.*?)$/si' , $LINE , $preg_result ) ){
					#	見出し(h3)
					$this->BLOCK_MODE = 'h3';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^□(.*?)$/si' , $LINE , $preg_result ) ){
					#	見出し(h4)
					$this->BLOCK_MODE = 'h4';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^・(.*?)$/si' , $LINE , $preg_result ) ){
					#	リスト
					$this->BLOCK_MODE = 'ul';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^※(.*?)$/si' , $LINE , $preg_result ) ){
					#	注釈リスト(PLOG 0.1.2)
					$this->BLOCK_MODE = 'ul_annotation';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^@(.*?)$/si' , $LINE , $preg_result ) ){
					#	連番付きリスト
					$this->BLOCK_MODE = 'ol';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^:(.*?)$/si' , $LINE , $preg_result ) ){
					#	定義リスト(10:02 2008/09/09 追加)
					$this->BLOCK_MODE = 'dl';
					$LINE = trim($preg_result[1]);
				}elseif( preg_match( '/^CODE>[ ]?(.*?)$/si' , $LINE , $preg_result ) ){
					#	ソースコード
					$this->BLOCK_MODE = 'sourcecode';
					$LINE = $preg_result[1];
				}elseif( preg_match( '/^>(.*?)$/si' , $LINE , $preg_result ) ){
					#	引用
					$this->BLOCK_MODE = 'blockquote';
					$LINE = trim($preg_result[1]);
				}elseif( !$MODE_LOCK ){
					#	段落
					$this->BLOCK_MODE = 'p';
					$LINE = preg_replace( '/^\\\\/' , '' , $LINE );
						//	ブロック要素のエスケープ処理
						//	バックスラッシュを、ひとつだけ削除
				}

				if( $LAST_BLOCK_MODE != $this->BLOCK_MODE ){
					#	モードが変わったら、
					#	メモを清算。
					$RTN .= $this->finish( $LAST_BLOCK_MODE , $SRCMEMO );
					$SRCMEMO = '';
				}

				$SRCMEMO .= $LINE."\n";

			}

		}
		$RTN .= $this->finish( $this->BLOCK_MODE , $SRCMEMO );
		$SRCMEMO = '';

		return	$RTN;
	}






	#--------------------------------------
	#	ソースブロックを完了する
	function finish( $func_name , $BLOCK_SRC ){
		if( method_exists( $this , 'finish_'.$func_name ) ){
			return	eval( 'return $this->finish_'.$func_name.'( $BLOCK_SRC );' );
		}
		return	$this->finish_p( $BLOCK_SRC );
	}

	#--------------------------------------
	#	段落ソースを完了する
	function finish_p( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = $this->exec_inline_elements( $BLOCK_SRC );
			$BLOCK_SRC = preg_replace( '/\r\n|\r|\n/is' , '<br />'."\n".'	' , $BLOCK_SRC );
			$BLOCK_SRC = '<p>'."\n".'	'.trim($BLOCK_SRC)."\n".'</p>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}

	#--------------------------------------
	#	リストソースを完了する
	function finish_ul( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$SRC_ROWS = preg_split( '/\r\n|\r|\n/' , $BLOCK_SRC );
			$MEMO = '';
			foreach( $SRC_ROWS as $line ){
				$line = trim($line);
				if( !strlen($line) ){ continue; }
				$MEMO .= '	<li>'.$this->exec_inline_elements( $line ).'</li>'."\n";
			}
			$BLOCK_SRC = '<ul>'."\n".$MEMO.'</ul>'."\n";
			unset($MEMO);
			$RTN .= $BLOCK_SRC;
			unset($BLOCK_SRC);
		}
		return	$RTN;
	}

	#--------------------------------------
	#	注釈リストソースを完了する (PLOG 0.1.2)
	function finish_ul_annotation( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$SRC_ROWS = preg_split( '/\r\n|\r|\n/' , $BLOCK_SRC );
			$MEMO = '';
			foreach( $SRC_ROWS as $line ){
				$line = trim($line);
				if( !strlen($line) ){ continue; }
				$MEMO .= '	<li>※'.$this->exec_inline_elements( $line ).'</li>'."\n";
			}
			$BLOCK_SRC = '<ul class="annotation">'."\n".$MEMO.'</ul>'."\n";
			unset($MEMO);
			$RTN .= $BLOCK_SRC;
			unset($BLOCK_SRC);
		}
		return	$RTN;
	}

	#--------------------------------------
	#	番号付きリストソースを完了する
	function finish_ol( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$SRC_ROWS = preg_split( '/\r\n|\r|\n/' , $BLOCK_SRC );
			$MEMO = '';
			foreach( $SRC_ROWS as $line ){
				$line = trim($line);
				if( !strlen($line) ){ continue; }
				$MEMO .= '	<li>'.$this->exec_inline_elements( $line ).'</li>'."\n";
			}
			$BLOCK_SRC = '<ol>'."\n".$MEMO.'</ol>'."\n";
			unset($MEMO);
			$RTN .= $BLOCK_SRC;
			unset($BLOCK_SRC);
		}
		return	$RTN;
	}

	#--------------------------------------
	#	定義リストソースを完了する
	function finish_dl( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$SRC_ROWS = preg_split( '/\r\n|\r|\n/' , $BLOCK_SRC );
			$MEMO = '';
			foreach( $SRC_ROWS as $line ){
				$line = trim($line);
				if( !strlen($line) ){ continue; }
				preg_match( '/^(.*?)(?:\|(.*))?$/' , $line , $preg_result );
				$MEMO .= '	<dt>'.$this->exec_inline_elements( $preg_result[1] ).'</dt>'."\n";
				$MEMO .= '		<dd>'.$this->exec_inline_elements( $preg_result[2] ).'</dd>'."\n";
			}
			$BLOCK_SRC = '<dl>'."\n".$MEMO.'</dl>'."\n";
			unset($MEMO);
			$RTN .= $BLOCK_SRC;
			unset($BLOCK_SRC);
		}
		return	$RTN;
	}

	#--------------------------------------
	#	水平線ソースを完了する
	function finish_hr( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$SRC_ROWS = preg_split( '/\r\n|\r|\n/' , $BLOCK_SRC );
			foreach( $SRC_ROWS as $line ){
				$line = trim($line);
				if( !strlen($line) ){ continue; }
				$RTN .= '<'.'?php print $theme->mk_hr(); ?'.'>'."\n";
			}
		}
		return	$RTN;
	}

	#--------------------------------------
	#	引用ソースを完了する
	function finish_blockquote( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = $this->exec_inline_elements( $BLOCK_SRC );
			$BLOCK_SRC = preg_replace( '/\r\n|\r|\n/is' , '<br />'."\n".'	' , $BLOCK_SRC );
			$BLOCK_SRC = '<blockquote><div>'."\n".'	'.trim($BLOCK_SRC)."\n".'</div></blockquote>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}

	#--------------------------------------
	#	ソースコードソースを完了する
	function finish_sourcecode( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = $this->exec_inline_elements( $BLOCK_SRC );
//			$BLOCK_SRC = preg_replace( '/\r\n|\r|\n/is' , '<br />' , $BLOCK_SRC );
			$BLOCK_SRC = '<blockquote class="sourcecode"><pre>'.$BLOCK_SRC.'</pre></blockquote>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}

	#--------------------------------------
	#	見出しソースを完了する
	function finish_h2( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = '<?php print $theme->mk_hx( '.text::data2text( trim( $BLOCK_SRC ) ).' , $default_hxnum ); ?>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}
	function finish_h3( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = '<?php print $theme->mk_hx( '.text::data2text( trim( $BLOCK_SRC ) ).' , $default_hxnum-1 ); ?>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}
	function finish_h4( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$BLOCK_SRC = '<?php print $theme->mk_hx( '.text::data2text( trim( $BLOCK_SRC ) ).' , $default_hxnum-2 ); ?>'."\n";
			$RTN .= $BLOCK_SRC;
		}
		return	$RTN;
	}

	#--------------------------------------
	#	HTMLソースを完了する
	function finish_html( $BLOCK_SRC ){
		$RTN = '';
		if( strlen( $BLOCK_SRC ) ){
			$className = $this->plog->require_lib( '/PLOG/articleParser/htmloperator.php' );
			$htmloperator = new $className( $this->article_cd );
			$htmloperator->html_parse( $BLOCK_SRC );
			$RTN .= $htmloperator->publish();
		}
		return	$RTN;
	}





	#--------------------------------------
	#	インライン要素を変換する
	function exec_inline_elements( $BLOCK_SRC ){
		$BLOCK_SRC = htmlspecialchars( $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<b>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</b>'),'/').'/si' , '<strong>$1</strong>' , $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<strong>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</strong>'),'/').'/si' , '<strong>$1</strong>' , $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<s>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</s>'),'/').'/si' , '<strike>$1</strike>' , $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<strike>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</strike>'),'/').'/si' , '<strike>$1</strike>' , $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<code>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</code>'),'/').'/si' , '<code>$1</code>' , $BLOCK_SRC );
		$BLOCK_SRC = preg_replace( '/'.preg_quote(htmlspecialchars('<q>'),'/').'(.*?)'.preg_quote(htmlspecialchars('</q>'),'/').'/si' , '<q>$1</q>' , $BLOCK_SRC );
#		$BLOCK_SRC = preg_replace( '/'.preg_quote('&amp;','/').'/si' , '&' , $BLOCK_SRC );//←11:47 2009/11/09 やめる。

		#	リンクの処理
		$MEMO = $BLOCK_SRC;
		$BLOCK_SRC = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)'.preg_quote('[[','/').'(.*?)(?:\|(.*?))?'.preg_quote(']]','/').'(.*)$/is' , $MEMO , $preg_result ) ){
				$BLOCK_SRC .= $MEMO;
				break;
			}
			$prevstr = $preg_result[1];
			$href = preg_replace( '/'.preg_quote('&amp;','/').'/si' , '&' , $preg_result[2] );
			$label = preg_replace( '/'.preg_quote('&amp;','/').'/si' , '&' , $preg_result[3] );
			$MEMO = $preg_result[4];

			$BLOCK_SRC .= $prevstr;
			$BLOCK_SRC .= '<'.'?php print $theme->mk_link( '.text::data2text($href).' , array(\'label\'=>'.text::data2text($label).',\'allow_html\'=>false) ); ?'.'>';
				#	14:52 2008/06/30 : 'allow_html'オプションを false に変更。TomK

		}
		unset( $MEMO );

		#	画像の処理
		$MEMO = $BLOCK_SRC;
		$BLOCK_SRC = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)'.preg_quote('[','/').'(?:image|img|画像)\:(.*?)(?:\|(.*?))?'.preg_quote(']','/').'(.*)$/is' , $MEMO , $preg_result ) ){
				$BLOCK_SRC .= $MEMO;
				break;
			}
			$prevstr = $preg_result[1];
			$src = preg_replace( '/'.preg_quote('&amp;','/').'/si' , '&' , $preg_result[2] );
			$alt = preg_replace( '/'.preg_quote('&amp;','/').'/si' , '&' , $preg_result[3] );
			$MEMO = $preg_result[4];

			$BLOCK_SRC .= $prevstr;
			$BLOCK_SRC .= '<'.'?php print $this->mk_img( '.intval( $this->article_cd ).' , '.text::data2text($src).' , array(\'alt\'=>'.text::data2text($alt).') ); ?'.'>';
				#	14:52 2008/06/30 : 'allow_html'オプションを false に変更。TomK

		}
		unset( $MEMO );
//		$BLOCK_SRC = preg_replace( '/'.preg_quote('[','/').'(?:image|img|画像)\:(.*?)(?:\|(.*?))?'.preg_quote(']','/').'/i' , '<'.'?php print $this->mk_img( '.intval( $this->article_cd ).' , '.text::data2text("$1").' , array(\'alt\'=>'.text::data2text("$2").') ); ?'.'>' , $BLOCK_SRC );

		return	$BLOCK_SRC;
	}


	#	キャッシュファイルのパスを得る
	function get_cache_file_path( $article_cd ){
		return	$this->plog->get_cache_dir().'/article_'.intval( $article_cd ).'.php';
	}

	#	キャッシュが有効か調べる
	function is_cache_file( $article_cd ){
		$path_cache = $this->get_cache_file_path( $article_cd );
		$path_article = $this->plog->get_article_dir( $article_cd ).'/contents.txt';

		if( !is_file( $path_cache ) ){ return false; }
		if( !is_file( $path_article ) ){ return false; }
		if( $this->px->dbh()->is_newer_a_than_b( $path_article , $path_cache ) ){
			return	false;
		}
		return	true;
	}

	#	キャッシュをロードしてHTMLを返す。
	function load_cache( $path_cache , $type = null ){
		$conf = &$this->conf;
		$errors = &$this->errors;
		$dbh = &$this->dbh;
		$req = &$this->req;
		$user = &$this->user;
		$site = &$this->site;
		$theme = &$this->theme;
		$custom = &$this->custom;

		$default_hxnum = 0;
		if( $type == 'article_list' ){
			$default_hxnum = -1;
		}

		ob_start();
		$RTN = @include( $path_cache );
		if( !is_string( $RTN ) ){
			$RTN = '';
		}
		$RTN .= @ob_get_clean();

		return	$RTN;
	}

	#	オリジナルのソースを取得する
	function get_original_src( $article_cd ){
		$base_path = $this->plog->get_article_dir( $article_cd );
		if( !is_dir( $base_path ) ){
			return	false;
		}

		$ary_original_src = $this->px->dbh()->file_get_lines( $base_path.'/contents.txt' );

		return	$ary_original_src;

	}



	#--------------------------------------
	#	画像を表示する
	function mk_img( $article_cd , $filename , $option = array() ){
		static $callNumber = 0;
		$callNumber ++;

		$resource_type = null;
		switch( strtolower( $this->px->dbh()->get_extension($filename) ) ){
			case 'gif':
			case 'png':
			case 'jpg':
			case 'jpe':
			case 'jpeg':
			case 'bmp':
				#	画像
				$resource_type = 'image';
				break;
			case 'mm':
				#	★FreeMind形式(2:24 2008/11/08)
				#	Flashのライブラリ「FreeMind Flash Browser 0.99」を使って、
				#	ブログ上にFreeMind形式のファイルを表示します。
				#	参考URL：
				#		http://www.freemind-club.com/
				#		http://sourceforge.net/forum/forum.php?thread_id=1238822&forum_id=22101
				#		http://www.efectokiwano.net/mm/mindmaps.html
				$resource_type = 'freemind';
				break;
			default:
				#	画像ファイルと認識できない拡張子がついていたら
				#	リサイズとかはしないで、ダウンロードファイルとして取り扱う。
				$resource_type = null;
				break;
		}
		if( preg_match( '/^https?\:\/\//is' , $filename ) ){
			#	ネットワークリソースは画像扱いする。
			$resource_type = 'image';
		}

		#--------------------------------------
		#	プレビューモードの処理
		if( $this->preview_mode ){
			if( $resource_type == 'image' ){
				#	画像
				$RTN = '';
				$RTN .= '<img';
				$RTN .= ' src="'.htmlspecialchars( $this->theme->href( $this->req->p() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.$filename ) ) ).'"';
				$RTN .= ' alt="'.htmlspecialchars( text::html2text( $option['alt'] ) ).'"';
				if( strlen( $option['width'] ) ){
					$RTN .= ' width="'.htmlspecialchars( text::html2text( $option['width'] ) ).'"';
				}
				if( strlen( $option['height'] ) ){
					$RTN .= ' height="'.htmlspecialchars( text::html2text( $option['height'] ) ).'"';
				}
				$RTN .= ' />';
				return $RTN;

			}elseif( $resource_type == 'freemind' && $this->plog->helpers['freemind']['url_freemind_flash_browser'] ){
				#	FreeMind
				$RTN .= '<span id="cont_plog_flashcontent_'.htmlspecialchars( $callNumber ).'" style="display:block; width:auto; height:300px; border:1px solid #999999; background-color:#ffffff;">';
				$RTN .= 'マインドマップファイル「'.htmlspecialchars($filename).'」を表示します。<br />';
				$RTN .= '</span>'."\n";

				//	↓どうやら visorFreemind.swf をロードすると画像がリセットされてしまうようなので、
				//	↓プレビューするのはやめた。
				//	↓おそらく、visorFreemind.swf に、呼び出し側のURLをたたく癖があるのが原因ではないかと思う。
#				$RTN .= '<script type="text/javascript" src="'.htmlspecialchars( $this->plog->helpers['freemind']['url_freemind_flash_browser'] ).'/flashobject.js"></script>'."\n";
#				$RTN .= '<script type="text/javascript">'."\n";
#				$RTN .= '	// <![CDATA['."\n";
#				$RTN .= '	function getMap(map){'."\n";
#				$RTN .= '		var result=map;'."\n";
#				$RTN .= '		var loc=document.location+\'\';'."\n";
#				$RTN .= '		if(loc.indexOf(".mm")>0 && loc.indexOf("?")>0){'."\n";
#				$RTN .= '			result=loc.substring(loc.indexOf("?")+1);'."\n";
#				$RTN .= '		}'."\n";
#				$RTN .= '		return result;'."\n";
#				$RTN .= '	}'."\n";
#				$RTN .= '	var fo = new FlashObject('.text::data2jstext( $this->plog->helpers['freemind']['url_freemind_flash_browser'].'/visorFreemind.swf' ).', "visorFreeMind", "100%", "100%", 6, "#9999ff");'."\n";
#				$RTN .= '	fo.addParam("quality", "high");'."\n";
#				$RTN .= '	fo.addParam("bgcolor", "#ffffff");'."\n";
#				$RTN .= '	fo.addVariable("openUrl", "_blank");'."\n";
#				$RTN .= '	fo.addVariable("initLoadFile", getMap('.text::data2jstext( $this->theme->href( $this->req->p() , array( 'additionalquery'=>'mode=imagepreview&preview_image_name='.urlencode( $filename ) ) ) ).'));'."\n";
#				$RTN .= '	fo.addVariable("startCollapsedToLevel","2");'."\n";
#				$RTN .= '	fo.write("cont_plog_flashcontent_'.$callNumber.'");'."\n";
#				$RTN .= '	// ]]>'."\n";
#				$RTN .= '</script>';

				$RTN .= '<span class="notes ttrss p" style="display:block;">';
				$RTN .= 'powered by [FreeMind Flash Browser] and [FreeMind].';
				$RTN .= ' [<a href="javascript:alert(\'Start download file ['.htmlspecialchars($filename).']\');">Download mindmap</a>]';
				$RTN .= '</span>';
				return	$RTN;
			}

			#	その他(ダウンロード)
			if( !strlen( $option['alt'] ) ){
				$option['alt'] = basename( $filename );
			}
			$RTN .= '<a';
			$RTN .= ' href="javascript:alert(\'Start download file ['.htmlspecialchars($filename).']\');"';
			$RTN .= '>';
			$RTN .= ''.htmlspecialchars( text::html2text( $option['alt'] ) ).'';
			$RTN .= '</a>';
			return $RTN;

		}
		#	/ プレビューモードの処理
		#--------------------------------------

		$dao_visitor = &$this->plog->factory_dao('visitor');
		$url_image = $dao_visitor->create_image_cache( $article_cd , $filename , array( 'width'=>$option['width'] , 'height'=>$option['height'] ) );
		if( $url_image === false ){
			return	'[イメージエラー]';
		}

		#	このメソッドは、実際に画像を表示するものです。
		$RTN = '';
		if( $resource_type == 'image' ){
			#	画像
			$RTN .= '<img';
			$RTN .= ' src="'.htmlspecialchars( $url_image ).'"';
			$RTN .= ' alt="'.htmlspecialchars( text::html2text( $option['alt'] ) ).'"';
			$RTN .= ' />';
			return	$RTN;

		}elseif( $resource_type == 'freemind' && $this->plog->helpers['freemind']['url_freemind_flash_browser'] ){
			#	FreeMind
			$RTN .= '<span id="cont_plog_flashcontent_'.$callNumber.'" style="display:block; width:auto; height:300px; border:1px solid #999999; background-color:#ffffff;">';
			$RTN .= 'マインドマップを表示します。';
			$RTN .= '表示されない場合は、JavaScriptがオフになっていたり、Flash Player がインストールされていないなどの可能性があります。';
			$RTN .= '</span>'."\n";
			$RTN .= '<script type="text/javascript" src="'.htmlspecialchars( $this->plog->helpers['freemind']['url_freemind_flash_browser'] ).'/flashobject.js"></script>'."\n";
			$RTN .= '<script type="text/javascript">'."\n";
			$RTN .= '	// <![CDATA['."\n";
			$RTN .= '	function getMap(map){'."\n";
			$RTN .= '		var result=map;'."\n";
			$RTN .= '		var loc=document.location+\'\';'."\n";
			$RTN .= '		if(loc.indexOf(".mm")>0 && loc.indexOf("?")>0){'."\n";
			$RTN .= '			result=loc.substring(loc.indexOf("?")+1);'."\n";
			$RTN .= '		}'."\n";
			$RTN .= '		return result;'."\n";
			$RTN .= '	}'."\n";
			$RTN .= '	var fo = new FlashObject('.text::data2jstext( $this->plog->helpers['freemind']['url_freemind_flash_browser'].'/visorFreemind.swf' ).', "visorFreeMind", "100%", "100%", 6, "#9999ff");'."\n";
			$RTN .= '	fo.addParam("quality", "high");'."\n";
			$RTN .= '	fo.addParam("bgcolor", "#ffffff");'."\n";
			$RTN .= '	fo.addVariable("openUrl", "_blank");'."\n";
			$RTN .= '	fo.addVariable("initLoadFile", getMap('.text::data2jstext( $url_image ).'));'."\n";
			$RTN .= '	fo.addVariable("startCollapsedToLevel","2");'."\n";
			$RTN .= '	fo.write("cont_plog_flashcontent_'.$callNumber.'");'."\n";
			$RTN .= '	// ]]>'."\n";
			$RTN .= '</script>';
			$RTN .= '<span class="notes ttrss p" style="display:block;">';
			$RTN .= 'powered by [FreeMind Flash Browser] and [FreeMind].';
			$RTN .= ' [<a href="'.htmlspecialchars( $url_image ).'" onclick="window.open(this.href);return false;">Download mindmap</a>]';
			$RTN .= '</span>';
			return	$RTN;

		}

		#	その他(ダウンロード)
		if( !strlen( $option['alt'] ) ){
			$option['alt'] = basename( $url_image );
		}
		$RTN .= '<a';
		$RTN .= ' href="'.htmlspecialchars( $url_image ).'"';
		$RTN .= ' onclick="window.open(this.href);return false;"';
		$RTN .= '>';
		$RTN .= ''.htmlspecialchars( text::html2text( $option['alt'] ) ).'';
		$RTN .= '</a>';

		return	$RTN;
	}

	#--------------------------------------
	#	記事コンテンツのHTMLから、自動サマリ文字列を生成する
	function mk_summary_by_html( $SRC_HTML ){
		$SRC_HTML = $SRC_HTML;
		$SRC_HTML = strip_tags( $SRC_HTML );//タグを消す
		$SRC_HTML = trim( $SRC_HTML );
		$SRC_HTML = preg_replace( '/(\r\n|\r|\n)\t+/is' , '$1' , $SRC_HTML );//HTMLソース上のタブインデントを削除
		$SRC_HTML = preg_replace( '/(?:\r\n|\r|\n)+/is' , "\n" , $SRC_HTML );//連続する改行を一つにまとめる
		$SRC_HTML = text::html2text( $SRC_HTML );//htmlspecialchars_decode()する。00:30 2007/12/29 追加
		$SRC_HTML = mb_strimwidth( $SRC_HTML , 0 , 256 , '...');//丸める
		return	$SRC_HTML;
	}

}

?>