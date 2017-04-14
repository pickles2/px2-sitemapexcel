<?php
/**
 * test for pickles2\px2-sitemapexcel
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
		@date_default_timezone_set('Asia/Tokyo');

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
		// var_dump($output);

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
		// var_dump($output);

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

		// 外部リンクを確認
		$this->assertEquals( $sitemapAry['alias28:http://pickles2.pxt.jp/']['path'], 'alias28:http://pickles2.pxt.jp/' );
		$this->assertEquals( $sitemapAry['alias29://pickles2.pxt.jp/']['path'], 'alias29://pickles2.pxt.jp/' );
		$this->assertEquals( $sitemapAry['alias30:http://pickles2.pxt.jp/index.html']['path'], 'alias30:http://pickles2.pxt.jp/index.html' );
		$this->assertEquals( $sitemapAry['alias31://pickles2.pxt.jp/index.html']['path'], 'alias31://pickles2.pxt.jp/index.html' );
		$this->assertEquals( $sitemapAry['alias32:http://pickles2.pxt.jp/abc.html']['path'], 'alias32:http://pickles2.pxt.jp/abc.html' );
		$this->assertEquals( $sitemapAry['alias33://pickles2.pxt.jp/abc.html']['path'], 'alias33://pickles2.pxt.jp/abc.html' );

		// セルフォーマットの処理を確認
		// var_dump($sitemapAry['/index.html']);
		// var_dump($sitemapAry['/sample_pages/index.html']);
		$this->assertEquals( $sitemapAry['/index.html']['cell_formats'], '2000/06/12' );
		$this->assertEquals( $sitemapAry['/sample_pages/index.html']['cell_formats'], '2001/09/08 1:50' );
		$this->assertEquals( $sitemapAry['/sample_pages/fess/index.html']['cell_formats'], 37143.0769675347); // Excel上では '平成13年09月09日' だが、未対応なので Float のまま置き換えられる。
		$this->assertEquals( $sitemapAry['/sample_pages/fess/units/index.html']['cell_formats'], 'Sep-01' );
		$this->assertEquals( $sitemapAry['/sample_pages/fess/parts/index.html']['cell_formats'], '09"月"11"日"' ); // Excel上では '09月11日'
		$this->assertEquals( $sitemapAry['/sample_pages/fess/statics/index.html']['cell_formats'], '12-Sep-01' );
		$this->assertEquals( $sitemapAry['/sample_pages/fess/boxes/index.html']['cell_formats'], '13-Sep' );
		$this->assertEquals( $sitemapAry['/sample_pages/page1/1.html']['cell_formats'], '10.00%' );
		$this->assertEquals( $sitemapAry['/sample_pages/page1/2.html']['cell_formats'], '955-9900' );
		$this->assertEquals( $sitemapAry['/sample_pages/page1/3.html']['cell_formats'], '955-9900' );
		$this->assertEquals( $sitemapAry['/sample_pages/page2/index.html']['cell_formats'], '2001/09/18 1:50' );


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
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );// XLSXは復活しているはず。
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
		$this->assertEquals( $objSheet->getCell('W8')->getCalculatedValue(), 'proc_type' );
		$this->assertEquals( $objSheet->getCell('X8')->getCalculatedValue(), '**delete_flg' );
		$this->assertEquals( $objSheet->getCell('Y8')->getCalculatedValue(), 'test_custom_col_1' );
		$this->assertEquals( $objSheet->getCell('Z8')->getCalculatedValue(), 'test_custom_col_2' );
		$this->assertEquals( $objSheet->getCell('B9')->getCalculatedValue(), 'ホーム' );

		$this->assertEquals( $objSheet->getCell('C34')->getCalculatedValue(), 'ヘルプ' );
		$this->assertEquals( $objSheet->getCell('D35')->getCalculatedValue(), 'Pickles 2 への外部リンク (1)' );
		$this->assertEquals( $objSheet->getCell('N35')->getCalculatedValue(), 'http://pickles2.pxt.jp/' );
		$this->assertEquals( $objSheet->getCell('D36')->getCalculatedValue(), 'Pickles 2 への外部リンク (2)' );
		$this->assertEquals( $objSheet->getCell('N36')->getCalculatedValue(), '//pickles2.pxt.jp/' );
		$this->assertEquals( $objSheet->getCell('D37')->getCalculatedValue(), 'Pickles 2 への外部リンク (3)' );
		$this->assertEquals( $objSheet->getCell('N37')->getCalculatedValue(), 'http://pickles2.pxt.jp/index.html' );
		$this->assertEquals( $objSheet->getCell('D38')->getCalculatedValue(), 'Pickles 2 への外部リンク (4)' );
		$this->assertEquals( $objSheet->getCell('N38')->getCalculatedValue(), '//pickles2.pxt.jp/index.html' );
		$this->assertEquals( $objSheet->getCell('D39')->getCalculatedValue(), 'Pickles 2 への外部リンク (5)' );
		$this->assertEquals( $objSheet->getCell('N39')->getCalculatedValue(), 'http://pickles2.pxt.jp/abc.html' );
		$this->assertEquals( $objSheet->getCell('D40')->getCalculatedValue(), 'Pickles 2 への外部リンク (6)' );
		$this->assertEquals( $objSheet->getCell('N40')->getCalculatedValue(), '//pickles2.pxt.jp/abc.html' );

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
			$param = escapeshellarg($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
