<?php
/**
 * test for tomk79\pickles-sitemap-excel
 * 
 * $ cd (project dir)
 * $ ./vendor/phpunit/phpunit/phpunit tests/pickles-sitemap-excelTest.php picklesSitemapExcel
 */

class picklesSitemapExcelTest extends PHPUnit_Framework_TestCase{

	/**
	 * サイトマップディレクトリのパス
	 */
	private $path_sitemaps;

	/**
	 * テスト用のファイル更新日タイムスタンプ
	 */
	private $test_timestamp;

	/**
	 * setup
	 */
	public function setup(){
		$this->test_timestamp = @mktime(0, 0, 0, 1, 1, 2000);
		$this->path_sitemap = __DIR__.'/testData/standard/px-files/sitemaps/';
	}

	/**
	 * *.xlsx to .csv 変換のテスト
	 */
	public function testXlsx2CsvConvert(){

		// CSV を削除してみる。
		$this->assertTrue( is_file( $this->path_sitemap.'sitemap.csv' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'sitemap.csv' ) );
		clearstatcache();
		$this->assertFalse( is_file( $this->path_sitemap.'sitemap.csv' ) );
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

		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
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

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );// XLSXは復活しているはず。
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );

		clearstatcache();
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testCsv2XlsxConvert()




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
