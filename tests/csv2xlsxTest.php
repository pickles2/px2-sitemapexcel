<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class csv2xlsxTest extends PHPUnit_Framework_TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');
	}

	/**
	 * *.xlsx to .csv 変換のテスト
	 */
	public function testXlsx2CsvConvert(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		// $this->assertEquals( $toppage_info['title'], '<HOME>' );
		// $this->assertEquals( $toppage_info['path'], '/index.html' );

		$this->fs->mkdir(__DIR__.'/testData/files/dist/');

        $px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($px);
        $px2_sitemapexcel->csv2xlsx( __DIR__.'/testData/files/test1.csv', __DIR__.'/testData/files/dist/test1.xlsx' );
		$this->assertTrue( is_file( __DIR__.'/testData/files/dist/test1.xlsx' ) );


		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$this->fs->rm(__DIR__.'/testData/files/dist/');
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testXlsx2CsvConvert()

	/**
	 * `.px_execute.php` を実行し、標準出力値を返す
	 * @param string $path_entry_script エントリースクリプトのパス(testData起点)
	 * @param string $command コマンド(例: `/?PX=clearcache`)
	 * @return string コマンドの標準出力値
	 */
	private function px_execute( $path_entry_script, $command ){
		$output = $this->passthru( [
			'php', __DIR__.'/testData/'.$path_entry_script, $command
		] );
		clearstatcache();
		return $output;
	}

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellarg($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}
