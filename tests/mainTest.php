<?php
/**
 * test for tomk79\px2-sitemapexcel
 */

class mainTest extends PHPUnit_Framework_TestCase{

	/**
	 * サイトマップディレクトリのパス
	 */
	private $path_sitemaps;

	/**
	 * テスト用のファイル更新日タイムスタンプ
	 */
	private $test_timestamp;

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup(){
		$this->test_timestamp = @mktime(0, 0, 0, 1, 1, 2000);
		$this->path_sitemap = __DIR__.'/testData/standard/px-files/sitemaps/';
		$this->fs = new \tomk79\filesystem();
		mb_internal_encoding('utf-8');
	}

	/**
	 * *.xlsx to .csv 変換のテスト
	 */
	public function testXlsx2CsvConvert(){

		// CSV を削除してみる。
		clearstatcache();
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.csv' ) );
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.xlsx' ) );

		clearstatcache();
		$this->assertFalse( is_file( $this->path_sitemap.'sitemap.csv' ) );
		$this->assertFalse( is_file( $this->path_sitemap.'sitemap.xlsx' ) );

		clearstatcache();
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.xlsx' ) );

		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		// CSV を古くしてみる。
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', $this->test_timestamp ) );
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.csv' ) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		clearstatcache();
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// 値をチェック
		$sitemapAry = include( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' );
		// var_dump($sitemapAry);
		$this->assertTrue( is_array( $sitemapAry ) );
		$this->assertEquals( $sitemapAry['/sample_pages/page5/5-1-1-1-2.html']['title'], 'サンプルページ5-1-1-1-2' );
		$this->assertEquals( $sitemapAry['alias21:/sample_pages/page5/index.html']['title'], 'サンプルページ5' );
		$this->assertEquals( $sitemapAry['alias21:/sample_pages/page5/index.html']['category_top_flg'], '1' );
		$this->assertEquals( $sitemapAry['alias22:/sample_pages/page5/index.html']['title'], 'サンプルページ5-1' );
		$this->assertEquals( $sitemapAry['alias22:/sample_pages/page5/index.html']['category_top_flg'], null );
		$this->assertEquals( $sitemapAry['alias24:/sample_pages/page5/index.html']['title'], 'サンプルページ5-1-1-1' );
		$this->assertEquals( $sitemapAry['alias24:/sample_pages/page5/index.html']['category_top_flg'], null );
		$this->assertEquals( $sitemapAry['/sample_pages/page5/index.html']['title'], 'サンプルページ5-1-1-1-1' );
		$this->assertEquals( $sitemapAry['/sample_pages/page5/index.html']['category_top_flg'], null );
		$this->assertEquals( $sitemapAry['/index.html']['test_custom_column_xlsx_1'], 'test1' );
		$this->assertEquals( $sitemapAry['/index.html']['test_custom_column_xlsx_2'], 'test2' );

		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testXlsx2CsvConvert()

	/**
	 * *.csv to .xlsx 変換のテスト
	 */
	public function testCsv2XlsxConvert(){

		// XLSX を削除してみる。
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'sitemap.xlsx' ) );
		clearstatcache();
		$this->assertFalse( is_file( $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_custom.csv', $this->path_sitemap.'sitemap.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		// XLSX を古くしてみる。
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', $this->test_timestamp ) );
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.xlsx' ) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );// XLSXは復活しているはず。
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		clearstatcache();
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// 値をチェック
		$objPHPExcel = \PHPExcel_IOFactory::load( $this->path_sitemap.'sitemap.xlsx' );
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		$this->assertEquals( $objSheet->getCell('T8')->getCalculatedValue(), 'description' );
		$this->assertEquals( $objSheet->getCell('V8')->getCalculatedValue(), 'role' );
		$this->assertEquals( $objSheet->getCell('W8')->getCalculatedValue(), '**delete_flg' );
		$this->assertEquals( $objSheet->getCell('X8')->getCalculatedValue(), 'test_custom_col_1' );
		$this->assertEquals( $objSheet->getCell('Y8')->getCalculatedValue(), 'test_custom_col_2' );
		$this->assertEquals( $objSheet->getCell('B9')->getCalculatedValue(), 'ホーム' );

		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testCsv2XlsxConvert()


	/**
	 * EndOfDataの記載がないエクセルファイルの変換テスト
	 */
	public function testEndlessXlsx(){

		clearstatcache();
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_endless.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.xlsx' ) );

		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', $this->test_timestamp ) );


		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		clearstatcache();
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// $csvFile = $this->fs->read_file($this->path_sitemap.'sitemap.csv');
		// var_dump($csvFile);
		$csvAry = $this->fs->read_csv($this->path_sitemap.'sitemap.csv');
		// var_dump($csvAry);
		$this->assertEquals( 'サンプルページ1', $csvAry[2][3] );
		$this->assertEquals( 'サンプルページ5', $csvAry[11][3] );
		$this->assertEquals( 'ヘルプ', $csvAry[17][3] );


		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testEndlessXlsx()




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
