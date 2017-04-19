<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class cleaningTest extends PHPUnit_Framework_TestCase{

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
	 * 後始末
	 */
	public function testClear(){

		// 変換後ファイルの後始末
		$this->fs->rm(__DIR__.'/testData/files/dist/');

		// Pickles 2 設定ファイルをもとに戻す
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/test_plugin_option_files/default.json',
			__DIR__.'/testData/standard/px-files/plugin_options.json'
		);

		// キャッシュ消去
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testClear()

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
