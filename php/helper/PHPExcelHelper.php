<?php
/**
 * PX Plugin "sitemapExcel"
 */
namespace tomk79\pickles2\sitemap_excel\helper;

class PHPExcelHelper {

	/**
	 * コンストラクタ
	 */
	public function __construct(){
	}

	/**
	 * 新規ファイルを作成
	 */
	public function create(){
		$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		return $objPHPExcel;
	}

	/**
	 * 既存のファイルを開く
	 */
	public function load( $path ){
		if(!strlen($path)){ return false; }
		if(!is_file($path)){ return false; }
		if(!is_readable($path)){ return false; }

		$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
		return $objPHPExcel;
	}

	/**
	 * 保存する
	 */
	public function save( $objPHPExcel, $path, $type = 'Xlsx' ){
		$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, $type);
		return $objWriter->save($path);
	}

}
