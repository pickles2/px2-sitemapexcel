<?php
/**
 * PX Plugin "sitemapExcel" import
 */
namespace tomk79\pickles2\sitemap_excel\apis;
use tomk79\pickles2\sitemap_excel\helper\PHPExcelHelper;

/**
 * PX Plugin "sitemapExcel" import
 */
class xlsx2csv {

	/** Picklesオブジェクト */
	private $px;
	/** sitemapExcelオブジェクト */
	private $plugin;
	/** オプション */
	private $options;

	/** 出力先パス */
	private $path_xlsx, $path_csv, $path_tmp_csv;
	/** ページID自動発行のための通し番号 */
	private $auto_id_num = 0;
	/** ページID自動発行のためのファイル名 */
	private $extless_basename = '';

	/** CSVに書き込むデータの一時記憶領域 */
	private $output_csv_stack = array();

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin プラグインオブジェクト
	 * @param array|object $options オプション
	 * @param string $options['target'] 対象 (`sitemap` | `blogmap`)
	 */
	public function __construct( $px, $plugin, $options ){
		$this->px = $px;
		$this->plugin = $plugin;
		$this->options = (object) $options;
		$this->options->target = $this->options->target ?? 'sitemap';
		// $this->path_import_data_dir = $this->plugin->get_path_import_data_dir();
		$this->output_csv_stack = array();
	}

	/**
	 * エクセルのパスを取得
	 */
	private function get_realpath_xlsx(){
		return $this->path_xlsx;
	}

	/**
	 * 出力先CSVのパスを取得
	 */
	private function get_realpath_csv(){
		return $this->path_csv;
	}

	/**
	 * xlsxからサイトマップCSVを出力する。
	 */
	public function convert( $path_xlsx, $path_csv ){
		$this->path_xlsx = $path_xlsx;
		$this->path_csv = $path_csv;
		$this->path_tmp_csv = $path_csv.'.tmp'.time();
		do{
			$this->path_tmp_csv = $path_csv.'.tmp'.time();
		} while( is_file($this->path_tmp_csv) );

		// ページID自動発行のための情報をリセット
		$this->auto_id_num = 0; // 通し番号をリセット
		$this->extless_basename = $this->px->fs()->trim_extension(basename($this->path_xlsx)); // ファイル名を記憶; 入力側のファイル名に準じる。


		$path_toppage = '/';
		if( strlen($this->px->conf()->path_top) ){
			$path_toppage = $this->px->conf()->path_top;
		}
		$path_toppage = $this->regulize_path( $path_toppage );

		// サイトマップCSVの定義を取得
		$sitemap_definition = $this->get_sitemap_definition();

		$phpExcelHelper = new PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		set_time_limit(0);
		$objPHPExcel = $phpExcelHelper->load($this->path_xlsx);

		$table_definition = $this->parse_definition($objPHPExcel, 0); // xlsxの構造定義を読み解く
		$col_title = array();
		foreach($table_definition['col_define'] as $tmp_col_define){
			if( isset( $col_title['start'] ) ){
				$col_title['end'] = $tmp_col_define['col'] ?? null;
				break;
			}
			if( $tmp_col_define['key'] == 'title' ){
				$col_title['start'] = $tmp_col_define['col'] ?? null;
			}
		}
		unset($tmp_col_define);

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		// xlsxにあってサイトマップ定義にないカスタムカラムを定義に反映
		$xls_custom_column_definition = $table_definition['col_define'];
		$tmp_last_elm_info = array();
		foreach( $sitemap_definition as $tmp_row ){
			unset($xls_custom_column_definition[$tmp_row['key']]);
			$tmp_last_elm_info = $tmp_row;
		}
		$tmp_last_elm_info['num'] = $tmp_last_elm_info['num'] ?? 0;
		$tmp_last_elm_info['col'] = $tmp_last_elm_info['col'] ?? 0;
		foreach( $xls_custom_column_definition as $tmp_key=>$tmp_row ){
			$tmp_last_elm_info['num'] ++;
			$tmp_last_elm_info['col'] ++;
			$tmp_last_elm_info['key'] = $tmp_row['key'];
			$tmp_last_elm_info['name'] = $tmp_row['key'];
			$sitemap_definition[$tmp_last_elm_info['key']] = $tmp_last_elm_info;
		}


		// 定義行を首都力
		$page_info = array();
		foreach($sitemap_definition as $row){
			if( $row['key'] == '**delete_flg' ){
				continue;
			}
			$page_info[$row['key']] = '* '.$row['key'];
		}
		$this->output_csv_row( $page_info );

		$last_breadcrumb = array();
		$last_page_id = null;
		$logical_path_last_depth = 0;
		$xlsx_row = $table_definition['row_data_start'];
		$xlsx_row --;

		while(1){
			set_time_limit(30);
			$xlsx_row ++;

			if( $xlsx_row > $table_definition['tbl_highest_row'] ){
				// エクセルの最終行に達していたら、終了。
				break;
			}
			if( $objSheet->getCell('A'.$xlsx_row)->getCalculatedValue() == 'EndOfData' ){
				// A列が 'EndOfData' だったら、終了。
				break;
			}

			$page_info = array();
			$tmp_page_info = array();
			foreach($sitemap_definition as $key=>$row){
				$tmp_col_name = $table_definition['col_define'][$row['key']]['col'] ?? null;
				if(strlen($tmp_col_name ?? '')){
					$tmp_page_info[$row['key']] = $objSheet->getCell($tmp_col_name.$xlsx_row)->getCalculatedValue();

					// ユーザーが設定したセルフォーマットに従って文字列を復元する
					$cell_format = $objSheet->getStyle($tmp_col_name.$xlsx_row)->getNumberFormat()->getFormatCode();
					if( $cell_format !== 'General' ){
						$tmp_cell_value = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::toFormattedString( $tmp_page_info[$row['key']], $cell_format, null );
						if( !is_null($tmp_cell_value) ){
							$tmp_page_info[$row['key']] = $tmp_cell_value;
						}
					}
					switch($cell_format){
						case 'General':
						case '@':
							// 標準 `General` と 文字列 `@` は、
							// 前後に空白文字を表現したい場合がありそうなので、そのままにする。
							break;
						default:
							// その他のフォーマットでは、
							// 前後に空白文字を表現したい場合はなさそうなので、trim() する。
							if( is_string($tmp_page_info[$row['key']]) ){
								$tmp_page_info[$row['key']] = trim($tmp_page_info[$row['key']]);
							}
							break;
					}
					unset($cell_format, $tmp_cell_value);
				}else{
					$tmp_page_info[$row['key']] = '';
				}
			}

			// --------------------
			// 削除フラグ
			if( $tmp_page_info['**delete_flg'] ?? null ){
				continue;
			}

			// --------------------
			// タイトルだけ特別
			$col_title_col = $col_title['start'] ?? null;
			$tmp_page_info['title'] = '';
			$logical_path_depth = 0;
			$alias_title_list = array();
			while( strcmp( strtoupper($col_title_col ?? ''), strtoupper($col_title['end'] ?? '') ) < 0 ){
					// ↑ $col_title['end'] には、titleの終端の右の列の名前が入っている。 よって `strcmp()` の結果は最大で `-1` となる。
				$tmp_page_info['title'] .= trim( $objSheet->getCell($col_title_col.$xlsx_row)->getCalculatedValue() ?? '' );
				if(strlen($tmp_page_info['title'])){
					$col_title_col ++;
					while( strcmp( strtoupper($col_title_col ?? '') , strtoupper($col_title['end'] ?? '') ) < 0 && strlen( $tmp_alias_title = trim( $objSheet->getCell(($col_title_col).$xlsx_row)->getCalculatedValue() ?? '' ) ) ){
						array_push( $alias_title_list, $tmp_alias_title );
						$col_title_col ++;
					}
					break;
				}
				$col_title_col ++;
				$logical_path_depth ++;
			}
			unset($col_title_col);
			unset($tmp_alias_title);

			if( !strlen( $tmp_page_info['path'] ?? '' ) ){
				if( !strlen( $tmp_page_info['title'] ?? '' ) ){
					// pathもtitleも空白なら終わったものと思う。
					// 	→スキップする に変更
					continue;
				}
				$tmp_page_info['path'] = 'alias:/_tbd.html'; // pathがなくてもtitleがあれば、仮の値を入れて通す。
			}

			// --------------------
			// list_flgの処理
			if( !array_key_exists('list_flg', $table_definition['col_define']) ){
				// エクセルの定義にlist_flg列がなかったら、
				// 全ページにlist_flg=1をセット。
				$tmp_page_info['list_flg'] = 1;
			}

			// --------------------
			// 読み込んだパスを正規化
			$tmp_page_info['path'] = $this->regulize_path( $tmp_page_info['path'] );

			// --------------------
			// 省略されたIDを自動的に付与
			if(!strlen($tmp_page_info['id'] ?? '')){
				if( $path_toppage != $tmp_page_info['path'] ){
					// トップページは空白でなければならない。
					if( preg_match( '/^alias\\:/', $tmp_page_info['path'] ) ){
						// エイリアスだったら自動付与
						$tmp_page_info['id'] = $this->generate_auto_page_id();
					}elseif( count($alias_title_list) ){
						// 隠れエイリアスだったら自動付与
						$tmp_page_info['id'] = $this->generate_auto_page_id();
					}
				}
			}

			// トップページのIDは空白文字列でなければならない。
			if( $path_toppage == $tmp_page_info['path'] ){
				$tmp_page_info['id'] = '';
			}

			// --------------------
			// パンくずも特別
			$tmp_breadcrumb = $last_breadcrumb;
			if( $logical_path_last_depth === $logical_path_depth ){
				// 前回と深さが変わっていなかったら
			}elseif( $logical_path_last_depth < $logical_path_depth ){
				// 前回の深さより深くなっていたら
				$tmp_breadcrumb = $last_breadcrumb;
				array_push($tmp_breadcrumb, $last_page_id );
			}elseif( $logical_path_last_depth > $logical_path_depth ){
				// 前回の深さより浅くなっていたら
				$tmp_breadcrumb = array();
				for($i = 0; $i < $logical_path_depth; $i ++){
					if(is_null($last_breadcrumb[$i])){break;}
					$tmp_breadcrumb[$i] = $last_breadcrumb[$i];
				}
			}
			if( strlen($tmp_page_info['logical_path'] ?? '') ){
				// エクセルの定義にlogical_path列があったら、
				// この値を優先して採用する。
			}else{
				$tmp_page_info['logical_path'] = '';
				if( count($tmp_breadcrumb) >= 2 ){
					$tmp_page_info['logical_path'] = implode('>', $tmp_breadcrumb);
					$tmp_page_info['logical_path'] = preg_replace('/^(.*?)\\>/s', '', $tmp_page_info['logical_path']);
				}
			}

			// 今回のパンくずとパンくずの深さを記録
			$logical_path_last_depth = $logical_path_depth;
			$last_breadcrumb = $tmp_breadcrumb;
			$last_page_id = $tmp_page_info['path'];
			if( preg_match( '/^alias\\:/', $tmp_page_info['path'] ) ){
				$last_page_id = $tmp_page_info['id'];
			}


			// --------------------
			// サイトマップにページを追加する
			$page_info = array();
			foreach($sitemap_definition as $row){
				if( $row['key'] == '**delete_flg' ){
					continue;
				}
				$page_info[$row['key']] = $tmp_page_info[$row['key']];
			}
			if( count($alias_title_list) ){
				// エイリアスが省略されている場合
				$page_info_base = $page_info;
				$page_info['path'] = 'alias:'.$page_info['path'];
				$this->output_csv_row( $page_info );
				$tmp_last_page_id = $page_info['id'];
				foreach( $alias_title_list as $key=>$row ){
					$page_info = $page_info_base;
					if( count($alias_title_list) > $key+1 ){
						// 最後の1件以外なら
						$page_info['path'] = 'alias:'.$page_info['path'];
					}
					if( $page_info_base['category_top_flg'] ){
						$page_info['category_top_flg'] = null;
					}
					array_push($tmp_breadcrumb, $tmp_last_page_id);
					$page_info['logical_path'] = '';
					if( count($tmp_breadcrumb) >= 2 ){
						$page_info['logical_path'] = implode('>', $tmp_breadcrumb);
						$page_info['logical_path'] = preg_replace('/^(.*?)\\>/s', '', $page_info['logical_path']);
					}
					$page_info['id'] = $this->generate_auto_page_id();
					$page_info['title'] = $row;

					$this->output_csv_row( $page_info );

					$tmp_last_page_id = $page_info['id'];
					$logical_path_last_depth ++;
					$last_breadcrumb = $tmp_breadcrumb;
					$last_page_id = $tmp_last_page_id;
				}

				unset($page_info_base);
				unset($tmp_last_page_id);
			}else{
				// 通常のページの場合
				$this->output_csv_row( $page_info );
			}

			if( $xlsx_row % 1000 === 0 ){
				$this->output_csv_row_flush();
			}

			continue;
		}

		$this->output_csv_row_flush();

		$this->px->fs()->mkdir(dirname($this->path_csv));
		$this->px->fs()->rename($this->path_tmp_csv, $this->path_csv);
		$this->px->fs()->chmod($this->path_csv, octdec( $this->px->conf()->file_default_permission ));

		set_time_limit(30);

		clearstatcache();
		return $this;
	}

	/**
	 * サイトマップの行を一時ファイルに出力する
	 * @return boolean result
	 */
	private function output_csv_row( $row ){
		array_push($this->output_csv_stack, $row);
		return;
	}
	/**
	 * サイトマップの行を一時ファイルに出力する
	 * @return boolean result
	 */
	private function output_csv_row_flush(){
		$this->px->fs()->mkdir(dirname($this->path_tmp_csv));
		file_put_contents(
			$this->path_tmp_csv,
			$this->px->fs()->mk_csv(
				$this->output_csv_stack,
				array('charset'=>'UTF-8')
			),
			FILE_APPEND|LOCK_EX
		);

		// リセットする
		$this->output_csv_stack = array();
		return;
	}

	/**
	 * サイトマップCSVの定義を取得する
	 */
	private function get_sitemap_definition(){
		$rtn = array();
		if( $this->options->target == 'blogmap' ){
			$rtn = $this->plugin->get_blogmap_definition();
		}else{
			$rtn = $this->plugin->get_sitemap_definition();
		}
		return $rtn;
	}

	/**
	 * ページIDを自動生成する
	 */
	private function generate_auto_page_id(){
		$this->auto_id_num ++;
		$rtn = 'sitemapExcel_auto_id_'.$this->extless_basename.'-'.intval($this->auto_id_num);
		return $rtn;
	}

	/**
	 * パス文字列の正規化
	 */
	private function regulize_path($path){
		switch( $this->px->get_path_type($path) ){
			case 'full_url':
			case 'data':
			case 'javascript':
			case 'anchor':
				break;
			default:
				$parsed_url = parse_url($path);
				$path_path = preg_replace( '/(?:\?|\#).*$/', '', $path);
				$path_path = preg_replace( '/\/$/s', '/'.$this->px->get_directory_index_primary(), $path_path);
				$path = $path_path.(strlen($parsed_url['query'] ?? '')?'?'.($parsed_url['query'] ?? ''):'').(strlen($parsed_url['fragment'] ?? '')?'#'.($parsed_url['fragment'] ?? ''):'');
				break;
		}
		return $path;
	}

	/**
	 * xlsxの構造定義設定を解析する
	 */
	private function parse_definition( $objPHPExcel, $sheetIndex = 0 ){
		$rtn = array();
		$objPHPExcel->setActiveSheetIndex($sheetIndex);
		$objSheet = $objPHPExcel->getActiveSheet();

		parse_str( $objSheet->getCell('A1')->getCalculatedValue(), $rtn );

		$rtn['tbl_highest_row'] = $objSheet->getHighestRow(); // e.g. 10
		$rtn['tbl_highest_col_name'] = $objSheet->getHighestColumn(); // e.g 'F'
		$rtn['tbl_highest_col'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $rtn['tbl_highest_col_name'] ); // e.g. 5

		$rtn['row_definition'] = intval($rtn['row_definition'] ?? null);
		$rtn['row_data_start'] = intval($rtn['row_data_start'] ?? null);
		if( !strlen($rtn['skip_empty_col'] ?? '') ){
			// 省略されていた場合にデフォルト値を与える
			$rtn['skip_empty_col'] = 20;
		}
		$rtn['skip_empty_col'] = intval($rtn['skip_empty_col']);

		$rtn['col_define'] = array();

		$mergedCells = $objSheet->getMergeCells();
		$mergeInfo = array();
		foreach( $mergedCells as $mergeRow ){
			if( preg_match( '/^([a-zA-Z]+)'.$rtn['row_definition'].'\:([a-zA-Z]+)'.$rtn['row_definition'].'$/', $mergeRow, $matched ) ){
				$mergeInfo[$matched[1]] = $matched[2];
			}
		}

		$col = 'A';
		$skip_count = 0;
		while(1){
			$def_key = $objSheet->getCell($col.$rtn['row_definition'])->getCalculatedValue();
			if(!strlen($def_key ?? '')){
				$skip_count ++;
				$col ++;
				if( $skip_count > $rtn['skip_empty_col'] ){
					break;
				}
				continue;
			}
			$skip_count = 0;

			$rtn['col_define'][$def_key] = array(
				'key'=>trim($def_key),
				'col'=>$col,
			);

			if( strlen($mergeInfo[$col] ?? '') ){
				$mergeStartCol = $mergeInfo[$col];
				while( strcmp($mergeStartCol, $col) ){
					$col ++;
				}
			}else{
				$col ++;
			}
		}

		return $rtn;
	}

}
