<?php

/**
 * 動的ページ管理オブジェクト
 */
class cont_pagemgr{
	private $px;
	private $current_page_info;
	private $queries = array();

	/**
	 * コンストラクタ
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->parse_query( $this->px->req()->get_path_param('') );
		$this->current_page_info = $this->px->site()->get_current_page_info();
	}

	/**
	 * ローカルなページを追加する。
	 */
	public function add_local_page_info( $path_part , $title , $parent = null , $options = array() ){
		//  ローカルなページを操作する手続きが複雑すぎる。
		//  このメソッドは、その複雑な操作を単純なものに変えた。
		//  [課題]この仕様を一般化し、PxFWコアに組み込めないだろうか？
		$options['title'] = $title;
		if( !is_null($parent) ){
			$options['logical_path'] = $this->get_logical_path_of($parent);
		}
		if( is_null($options['logical_path']) ){
			$options['logical_path'] = (strlen($this->current_page_info['logical_path'])?$this->current_page_info['logical_path'].'>':'').$this->current_page_info['id'];
		}
		if( is_null($options['list_flg']) ){
			$options['list_flg'] = 1;
		}
		$this->px->site()->set_page_info( $this->px->site()->bind_dynamic_path_param($this->current_page_info['path'],array(''=>$path_part)) , $options );
		return true;
	}

	/**
	 * 指定ページのパンくず値を取得する
	 */
	public function get_logical_path_of( $path ){
		$sitemap_path = $this->px->site()->get_page_info( $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'], array(''=>$path) ) , 'logical_path' ).'>'.$this->px->site()->get_page_info( $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'], array(''=>$path) ) , 'path' );
		return $sitemap_path;
	}

	/**
	 * コンテンツ内へのリンク先を調整する。
	 */
	public function href( $linkto = null ){
		if(is_null($linkto)){
			$rtn = $this->px->req()->get_request_file_path();
			$rtn = $this->px->theme()->href( $rtn );
			return $rtn;
		}
		$rtn = $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'] , array(''=>$linkto) );
		$rtn = preg_replace('/\/+/','/',$rtn);

		$rtn = $this->px->theme()->href( $rtn );
		return $rtn;
	}

	/**
	 * コンテンツ内へのリンクを生成する。
	 */
	public function mk_link( $linkto , $options = array() ){
		//$args = func_get_args();
		$rtn = $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'] , array(''=>$linkto) );
		$rtn = preg_replace('/\/+/','/',$rtn);

		$rtn = $this->px->theme()->mk_link( $rtn , $options );
		return $rtn;
	}

	/**
	 * クエリを解析する。
	 */
	private function parse_query( $query ){
		if($query == 'index.html'){ $query = ''; } //←PxFWが補完した index.html を削除
		$query = preg_replace( '/\/index\.html$/is','/',$query ); //←PxFWが補完した index.html を削除
		$query = preg_replace( '/\.html$/is','',$query ); //←拡張子を削除
		$query = preg_replace( '/\/$/is','',$query ); //←最後のスラッシュ閉じを削除
		$this->queries = explode('/',$query);

		return true;
	}

	/**
	 * クエリの一部分を取り出す
	 */
	public function get_query( $num = 0 ){
		if(is_null($num)){$num=0;}
		$rtn = $this->queries[$num];
		return $rtn;
	}

}

?>