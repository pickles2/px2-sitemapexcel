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
	/** サイトマップフォルダのパス */
	private $realpath_sitemap_dir;
	/** アプリケーションロック */
	private $locker;

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
		return '2.2.1';
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定
	 */
	public function __construct( $px, $plugin_conf = null ){
		$this->px = $px;
		$this->plugin_conf = $plugin_conf;

		// object から 連想配列に変換
		$this->plugin_conf = json_decode( json_encode($this->plugin_conf), true );
		if( !is_array($this->plugin_conf) ){
			$this->plugin_conf = array();
		}
		if( !strlen($this->plugin_conf['master_format'] ?? '') ){
			$this->plugin_conf['master_format'] = 'timestamp';
		}
		if( !is_array($this->plugin_conf['files_master_format'] ?? null) ){
			$this->plugin_conf['files_master_format'] = array();
		}

		$this->realpath_sitemap_dir = $this->px->get_path_homedir().'sitemaps/';
		$this->locker = new lock($this->px, $this);
	}

	/**
	 * すべてのファイルを変換する
	 */
	public function convert_all(){
		$sitemap_files = array();
		$tmp_sitemap_files = $this->px->fs()->ls( $this->realpath_sitemap_dir );

		foreach( $tmp_sitemap_files as $filename ){
			if( preg_match( '/^\\~\\$/', $filename ) ){
				// エクセルの編集中のキャッシュファイルのファイル名だからスルー
				continue;
			}
			if( preg_match( '/^\\.\\~lock\\./', $filename ) ){
				// Libre Office, Open Office の編集中のキャッシュファイルのファイル名だからスルー
				continue;
			}
			$extless_basename = $this->px->fs()->trim_extension($filename);
			$extension = $this->px->fs()->get_extension($filename);
			$extension = strtolower($extension);

			if( $extension != 'xlsx' && $extension != 'xlsm' && $extension != 'csv' ){
				// 知らない拡張子はスキップ
				continue;
			}

			if( !array_key_exists($extless_basename, $sitemap_files) || !is_array($sitemap_files[$extless_basename]) ){
				$sitemap_files[$extless_basename] = array();
			}
			$sitemap_files[$extless_basename][$extension] = $filename;
		}

		$is_detect_update = false;
		foreach( $sitemap_files as $extless_basename=>$extensions ){

			$infos = $this->preprocess_of_update_detection($extless_basename, $extensions);

			if( $infos['master_format'] == 'pass' ){
				// `pass` の場合は、変換を行わずスキップ。
				continue;
			}

			if( $infos['detected_update'] == 'xlsx2csv' ){
				// XLSX または XLSM がマスターになる場合
				$is_detect_update = true;
				break;
			}elseif( $infos['detected_update'] == 'csv2xlsx' ){
				// CSV がマスターになる場合
				if( $infos['xlsx_ext'] == 'xlsx' ){
					// xlsx以外の場合だけ上書きする。
					// xlsx以外の場合(=xlsmの場合) は、上書きしない。
					$is_detect_update = true;
					break;
				}
			}
		}

		if( !$is_detect_update ){
			// ここで更新が検出されなければ、
			// あとの処理は実行しない。
			return;
		}

		if( $this->locker->lock() ){

			foreach( $sitemap_files as $extless_basename=>$extensions ){
				$this->locker->update();

				$infos = $this->preprocess_of_update_detection($extless_basename, $extensions);

				if( $infos['master_format'] == 'pass' ){
					// `pass` の場合は、変換を行わずスキップ。
					continue;
				}

				if( $infos['detected_update'] == 'xlsx2csv' ){
					// XLSX または XLSM がマスターになる場合
					$result = $this->xlsx2csv(
						$this->realpath_sitemap_dir.$infos['extensions'][$infos['xlsx_ext']],
						$this->realpath_sitemap_dir.$infos['extensions']['csv']
					);
					touch(
						$this->realpath_sitemap_dir.$infos['extensions']['csv'],
						filemtime( $this->realpath_sitemap_dir.$infos['extensions'][$infos['xlsx_ext']] )
					);

				}elseif( $infos['detected_update'] == 'csv2xlsx' ){
					// CSV がマスターになる場合
					if( $infos['xlsx_ext'] == 'xlsx' ){
						// xlsx以外の場合だけ上書きする。
						// xlsx以外の場合(=xlsmの場合) は、上書きしない。
						$result = $this->csv2xlsx(
							$this->realpath_sitemap_dir.$infos['extensions']['csv'],
							$this->realpath_sitemap_dir.$infos['extensions'][$infos['xlsx_ext']]
						);
						touch(
							$this->realpath_sitemap_dir.$infos['extensions'][$infos['xlsx_ext']],
							filemtime( $this->realpath_sitemap_dir.$infos['extensions']['csv'] )
						);
					}
				}

			}

			$this->locker->unlock();
		}
		return;
	}

	/**
	 * 更新抽出のための前処理
	 * @param  string $extless_basename 拡張子を除いたファイル名
	 * @param  string $extensions       拡張子ごとの実ファイル名一覧
	 * @return array 整理された各情報
	 */
	private function preprocess_of_update_detection($extless_basename, $extensions){
		$master_format = $this->get_master_format_of($extless_basename);

		// ファイルが既存しない場合、ファイル名がセットされていないので、
		// 明示的にセットする。
		if( !array_key_exists('xlsx', $extensions) && !array_key_exists('xlsm', $extensions) ){
			// xlsx も xlsm も存在しない場合、xlsx をデフォルトとする。
			$extensions['xlsx'] = $extless_basename.'.xlsx';
		}
		if( !array_key_exists('csv', $extensions) ){
			$extensions['csv'] = $extless_basename.'.csv';
		}

		$xlsx_ext = 'xlsx';
		if( !array_key_exists('xlsx', $extensions) && array_key_exists('xlsm', $extensions) ){
			// xlsm が存在し、かつ xlsx が存在しない場合、
			// xlsx の代わりに xlsm を扱う。
			$xlsx_ext = 'xlsm';
		}

		$detected_update = false;
		if(
			($master_format == 'timestamp' || $master_format == 'xlsx')
			&& true === $this->px->fs()->is_newer_a_than_b( $this->realpath_sitemap_dir.$extensions[$xlsx_ext], $this->realpath_sitemap_dir.$extensions['csv'] )
		){
			// XLSX または XLSM がマスターになる場合
			$detected_update = 'xlsx2csv';
		}elseif(
			($master_format == 'timestamp' || $master_format == 'csv')
			&& true === $this->px->fs()->is_newer_a_than_b( $this->realpath_sitemap_dir.$extensions['csv'], $this->realpath_sitemap_dir.$extensions[$xlsx_ext] )
		){
			// CSV がマスターになる場合
			$detected_update = 'csv2xlsx';
		}

		return array(
			'master_format' => $master_format,
			'extensions' => $extensions,
			'xlsx_ext' => $xlsx_ext,
			'detected_update' => $detected_update,
		);
	}

	/**
	 * ファイルの master format を調べる
	 * @param  string $extless_basename 調べる対象の拡張子を含まないファイル名
	 * @return string master format 名
	 */
	private function get_master_format_of( $extless_basename ){
		$rtn = $this->plugin_conf['master_format'];
		if( strlen($this->plugin_conf['files_master_format'][$extless_basename] ?? '') ){
			$rtn = $this->plugin_conf['files_master_format'][$extless_basename];
		}
		$rtn = strtolower($rtn);
		return $rtn;
	}

	/**
	 * サイトマップXLSX を サイトマップCSV に変換
	 *
	 * このメソッドは、変換後のファイルを生成するのみです。
	 * タイムスタンプの調整等は行いません。
	 *
	 * @param string $path_xlsx Excelファイルのパス
	 * @param string $path_csv CSVファイルのパス
	 * @return boolean 実行結果
	 */
	public function xlsx2csv($path_xlsx, $path_csv){
		$result = (new xlsx2csv($this->px, $this))->convert( $path_xlsx, $path_csv );
		return $result;
	}

	/**
	 * サイトマップCSV を サイトマップXLSX に変換
	 *
	 * このメソッドは、変換後のファイルを生成するのみです。
	 * タイムスタンプの調整等は行いません。
	 *
	 * @param string $path_csv CSVファイルのパス
	 * @param string $path_xlsx Excelファイルのパス
	 * @return boolean 実行結果
	 */
	public function csv2xlsx($path_csv, $path_xlsx){
		$result = (new csv2xlsx($this->px, $this))->convert( $path_csv, $path_xlsx );
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
		$rtn['proc_type'] = array('num'=>$num++,'col'=>$col++,'key'=>'proc_type','name'=>'コンテンツの処理方法');
		return $rtn;
	}

}
