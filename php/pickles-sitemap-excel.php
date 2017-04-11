<?php
/**
 * pickles-sitemap-excel.php
 */
namespace tomk79\pickles2\sitemap_excel;

/**
 * pickles-sitemap-excel.php
 */
class pickles_sitemap_excel{
	/** Picklesオブジェクト */
	private $px;
	/** プラグイン設定 */
	private $plugin_conf;

	/**
	 * entry
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定
	 */
	static public function exec($px, $plugin_conf){
		(new self($px, $plugin_conf))->convert_all();
	}

	/**
	 * px2-sitemapexcel のバージョン情報を取得する。
	 *
	 * px2-sitemapexcel のバージョン番号はこのメソッドにハードコーディングされます。
	 *
	 * バージョン番号発行の規則は、 Semantic Versioning 2.0.0 仕様に従います。
	 * - [Semantic Versioning(英語原文)](http://semver.org/)
	 * - [セマンティック バージョニング(日本語)](http://semver.org/lang/ja/)
	 *
	 * *[ナイトリービルド]*<br />
	 * バージョン番号が振られていない、開発途中のリビジョンを、ナイトリービルドと呼びます。<br />
	 * ナイトリービルドの場合、バージョン番号は、次のリリースが予定されているバージョン番号に、
	 * ビルドメタデータ `+nb` を付加します。
	 * 通常は、プレリリース記号 `alpha` または `beta` を伴うようにします。
	 * - 例：1.0.0-beta.12+nb (=1.0.0-beta.12リリース前のナイトリービルド)
	 *
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.7-alpha.1+nb';
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定
	 */
	public function __construct( $px, $plugin_conf ){
		$this->px = $px;
		$this->plugin_conf = $plugin_conf;
	}

	/**
	 * すべてのファイルを変換する
	 */
	public function convert_all(){
		$path_base = $this->px->get_path_homedir().'sitemaps/';
		$sitemap_files = $this->px->fs()->ls( $path_base );

		$locker = new pxplugin_sitemapExcel_lock($this->px, $this);

		foreach( $sitemap_files as $filename ){
			if( preg_match( '/^\\~\\$/', $filename ) ){
				// エクセルの編集中のキャッシュファイルのファイル名だからスルー
				continue;
			}
			if( preg_match( '/^\\.\\~lock\\./', $filename ) ){
				// Libre Office, Open Office の編集中のキャッシュファイルのファイル名だからスルー
				continue;
			}
			$basename = $this->px->fs()->trim_extension($filename);
			$extension = $this->px->fs()->get_extension($filename);
			switch( strtolower($extension) ){
				// case 'xls':
				case 'xlsx':
					if( true === $this->px->fs()->is_newer_a_than_b( $path_base.$filename, $path_base.$basename.'.csv' ) ){
						if( $locker->lock() ){
							$result = $this->xlsx2csv( $path_base.$filename, $path_base.$basename.'.csv' );
							touch($path_base.$basename.'.csv', filemtime( $path_base.$filename ));
							$locker->unlock();
						}
					}
					break;
				case 'csv':
					if( true === $this->px->fs()->is_newer_a_than_b( $path_base.$filename, $path_base.$basename.'.xlsx' ) ){
						if( $locker->lock() ){
							$result = $this->csv2xlsx( $path_base.$filename, $path_base.$basename.'.xlsx' );
							touch($path_base.$basename.'.xlsx', filemtime( $path_base.$filename ));
							$locker->unlock();
						}
					}
					break;
				default:
					// 知らない拡張子はスルー
					break;
			}
		}
		return;
	}

	/**
	 * サイトマップXLSX を サイトマップCSV に変換
	 */
	public function xlsx2csv($path_xlsx, $path_csv){
		$result = @(new pxplugin_sitemapExcel_apis_xlsx2csv($this->px, $this))->convert( $path_xlsx, $path_csv );
		return $result;
	}

	/**
	 * サイトマップCSV を サイトマップXLSX に変換
	 */
	public function csv2xlsx($path_csv, $path_xlsx){
		$result = @(new pxplugin_sitemapExcel_apis_csv2xlsx($this->px, $this))->convert( $path_csv, $path_xlsx );
		return $result;
	}

	/**
	 * サイトマップCSVの定義を取得する
	 * @return array サイトマップCSV定義配列
	 */
	public function get_sitemap_definition(){
		$col = 'A';
		$num = 0;
		$rtn = array();
		$rtn['path'] = array('num'=>$num++,'col'=>$col++,'key'=>'path','name'=>'ページのパス');
		$rtn['content'] = array('num'=>$num++,'col'=>$col++,'key'=>'content','name'=>'コンテンツファイルの格納先');
		$rtn['id'] = array('num'=>$num++,'col'=>$col++,'key'=>'id','name'=>'ページID');
		$rtn['title'] = array('num'=>$num++,'col'=>$col++,'key'=>'title','name'=>'ページタイトル');
		$rtn['title_breadcrumb'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_breadcrumb','name'=>'ページタイトル(パン屑表示用)');
		$rtn['title_h1'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_h1','name'=>'ページタイトル(H1表示用)');
		$rtn['title_label'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_label','name'=>'ページタイトル(リンク表示用)');
		$rtn['title_full'] = array('num'=>$num++,'col'=>$col++,'key'=>'title_full','name'=>'ページタイトル(タイトルタグ用)');
		$rtn['logical_path'] = array('num'=>$num++,'col'=>$col++,'key'=>'logical_path','name'=>'論理構造上のパス');
		$rtn['list_flg'] = array('num'=>$num++,'col'=>$col++,'key'=>'list_flg','name'=>'一覧表示フラグ');
		$rtn['layout'] = array('num'=>$num++,'col'=>$col++,'key'=>'layout','name'=>'レイアウト');
		$rtn['orderby'] = array('num'=>$num++,'col'=>$col++,'key'=>'orderby','name'=>'表示順');
		$rtn['keywords'] = array('num'=>$num++,'col'=>$col++,'key'=>'keywords','name'=>'metaキーワード');
		$rtn['description'] = array('num'=>$num++,'col'=>$col++,'key'=>'description','name'=>'metaディスクリプション');
		$rtn['category_top_flg'] = array('num'=>$num++,'col'=>$col++,'key'=>'category_top_flg','name'=>'カテゴリトップフラグ');
		$rtn['role'] = array('num'=>$num++,'col'=>$col++,'key'=>'role','name'=>'ロール');
		return $rtn;
	}

}
