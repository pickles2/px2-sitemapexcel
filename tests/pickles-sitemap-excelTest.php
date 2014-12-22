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

	public function setup(){
		$this->path_sitemap = __DIR__.'/testData/standard/px-files/sitemaps/';
	}

	/**
	 * *.xlsx to .csv 変換のテスト
	 */
	public function testXlsx2CsvConvert(){

		// CSV を削除してみる。
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.csv' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'sitemapexcel.csv' ) );
		clearstatcache();
		$this->assertFalse( is_file( $this->path_sitemap.'sitemapexcel.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.xlsx', 20000 ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemapexcel.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemapexcel.xlsx' );
		$this->assertTrue( $mtime_csv === 20000 );
		$this->assertTrue( $mtime_xlsx === 20000 );

		// CSV を古くしてみる。
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.csv', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.xlsx', 20000 ) );
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.csv' ) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemapexcel.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemapexcel.xlsx' );
		$this->assertTrue( $mtime_csv === 20000 );
		$this->assertTrue( $mtime_xlsx === 20000 );

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
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.xlsx' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'sitemapexcel.xlsx' ) );
		clearstatcache();
		$this->assertFalse( is_file( $this->path_sitemap.'sitemapexcel.xlsx' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.csv', 20000 ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemapexcel.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemapexcel.xlsx' );
		$this->assertTrue( $mtime_csv === 20000 );
		$this->assertTrue( $mtime_xlsx === 20000 );

		// XLSX を古くしてみる。
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.xlsx' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.xlsx', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemapexcel.csv', 20000 ) );
		clearstatcache();
		$this->assertTrue( is_file( $this->path_sitemap.'sitemapexcel.xlsx' ) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemapexcel.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemapexcel.xlsx' );// XLSXは復活しているはず。
		$this->assertTrue( $mtime_csv === 20000 );
		$this->assertTrue( $mtime_xlsx === 20000 );

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
