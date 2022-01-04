<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class optionsTest extends PHPUnit\Framework\TestCase{

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
	public function setup() : void{
		$this->test_timestamp = @mktime(0, 0, 0, 1, 1, 2000);
		$this->path_sitemap = __DIR__.'/testData/standard/px-files/sitemaps/';
		$this->fs = new \tomk79\filesystem();
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');

	}

	/**
	 * master_format: xlsx のテスト
	 */
	public function testMasterFormatIsXlsxConvert(){

		clearstatcache();
		$this->assertTrue( copy(
			__DIR__.'/testData/standard/px-files/test_plugin_option_files/master_format_xlsx.json',
			__DIR__.'/testData/standard/px-files/plugin_options.json'
		) );

		// CSV を削除してみる。
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.csv' ) );
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
		$this->assertTrue( $mtime_csv === $mtime_xlsx );

		// CSV を古くしてみる。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );


		// XLSX を古くしてみる。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', $this->test_timestamp ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', 1000 ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv !== $mtime_xlsx ); // 何も行われないため、タイムスタンプはずれたまま。



		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testMasterFormatIsXlsxConvert()


	/**
	 * master_format: csv のテスト
	 */
	public function testMasterFormatIsCsvConvert(){

		clearstatcache();
		$this->assertTrue( copy(
			__DIR__.'/testData/standard/px-files/test_plugin_option_files/master_format_csv.json',
			__DIR__.'/testData/standard/px-files/plugin_options.json'
		) );

		// XLSX を削除してみる。
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.xlsx' ) );
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
		$this->assertTrue( $mtime_csv === $mtime_xlsx );

		// XLSX を古くしてみる。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', $this->test_timestamp ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', 1000 ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );


		// CSV を古くしてみる。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.xlsx', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv !== $mtime_xlsx ); // 何も行われないため、タイムスタンプはずれたまま。



		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testMasterFormatIsCsvConvert()


	/**
	 * master_format: pass のテスト
	 */
	public function testMasterFormatIsPassConvert(){

		clearstatcache();
		$this->assertTrue( copy(
			__DIR__.'/testData/standard/px-files/test_plugin_option_files/master_format_pass.json',
			__DIR__.'/testData/standard/px-files/plugin_options.json'
		) );

		// XLSX を削除してみる。
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( touch( $this->path_sitemap.'sitemap.csv', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$this->assertTrue( is_file($this->path_sitemap.'sitemap.csv') );
		$this->assertFalse( is_file($this->path_sitemap.'sitemap.xlsx') ); // 生成されていないはず

		// XLSX を復活させてCSVを削除してみる。
		clearstatcache();
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$this->assertTrue( @unlink( $this->path_sitemap.'sitemap.csv' ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$this->assertFalse( is_file($this->path_sitemap.'sitemap.csv') ); // 生成されていないはず
		$this->assertTrue( is_file($this->path_sitemap.'sitemap.xlsx') );



		// 後始末
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testMasterFormatIsPassConvert()


	/**
	 * files_master_format のテスト
	 */
	public function testFilesMasterFormatConvert(){

		clearstatcache();
		$this->assertTrue( copy(
			__DIR__.'/testData/standard/px-files/test_plugin_option_files/files_master_format.json',
			__DIR__.'/testData/standard/px-files/plugin_options.json'
		) );

		// XLSX を復活してみる。
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		@unlink( $this->path_sitemap.'sitemap.csv' );
		$this->assertFalse( @is_file( $this->path_sitemap.'sitemap.csv' ) );

		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'csv_master.xlsx' ) );
		$this->assertTrue( touch( $this->path_sitemap.'xlsx_master.csv' ) );
		$this->assertTrue( touch( $this->path_sitemap.'pass_master.csv' ) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'sitemap.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'sitemap.xlsx' );
		$this->assertTrue( $mtime_csv === $mtime_xlsx );

		// マスター不在だったので、マスターは生成されていない。
		$this->assertFalse( is_file( $this->path_sitemap.'csv_master.csv' ) );
		$this->assertFalse( is_file( $this->path_sitemap.'xlsx_master.xlsx' ) );
		$this->assertFalse( is_file( $this->path_sitemap.'pass_master.xlsx' ) );




		// 後始末
		$this->assertTrue( unlink( $this->path_sitemap.'csv_master.xlsx' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'xlsx_master.csv' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'pass_master.csv' ) );
		$this->assertTrue( copy( __DIR__.'/testData/standard/px-files/test_excel_data/sitemap_sample.xlsx', $this->path_sitemap.'sitemap.xlsx' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/' ] );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testFilesMasterFormatConvert()






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
