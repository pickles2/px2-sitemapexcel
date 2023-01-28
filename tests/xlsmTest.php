<?php
/**
 * test for pickles2\px2-sitemapexcel
 */

class xlsmTest extends PHPUnit\Framework\TestCase{

	/**
	 * サイトマップディレクトリのパス
	 */
	private $path_sitemap;

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
		// CSV を削除してみる。
		@unlink( $this->path_sitemap.'macros.csv' );
		@unlink( $this->path_sitemap.'macros.xlsm' );
		$this->assertTrue( copy(
			__DIR__.'/testData/files/macros.xlsm',
			$this->path_sitemap.'macros.xlsm'
		) );


		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'macros.csv' );// CSVは復活しているはず。
		$mtime_xlsx = filemtime( $this->path_sitemap.'macros.xlsm' );
		$this->assertTrue( $mtime_csv === $mtime_xlsx );

		// CSV を古くしてみる。
		// XLSM→CSV の変換は、タイムスタンプの設定の影響を受けて、 *.xlsx と同様の処理が通常通り行われる。
		// ただし、 *.xlsm と *.xlsx の両方が存在する場合は、 *.xlsx を優先し、 *.xlsm は無視される。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'macros.csv', 1000 ) );
		$this->assertTrue( touch( $this->path_sitemap.'macros.xlsm', $this->test_timestamp ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'macros.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'macros.xlsm' );
		$this->assertTrue( $mtime_csv === $this->test_timestamp );
		$this->assertTrue( $mtime_xlsx === $this->test_timestamp );


		// XLSM を古くしてみる。
		// CSV→XLSM の変換は、タイムスタンプの設定にかかわらず行われない。
		// *.xlsx が存在せず、かつ *.xlsm が存在する場合、逆変換は起きない。
		// *.xlsm が存在しない場合、 *.xlsx に逆変換される。
		// *.xlsm と *.xlsx の両方が存在する場合、 *.xlsm は無視され、
		// 通常通り *.xlsx への変換が処理される。
		clearstatcache();
		$this->assertTrue( touch( $this->path_sitemap.'macros.csv', $this->test_timestamp ) );
		$this->assertTrue( touch( $this->path_sitemap.'macros.xlsm', 1000 ) );

		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);

		clearstatcache();
		$mtime_csv = filemtime( $this->path_sitemap.'macros.csv' );
		$mtime_xlsx = filemtime( $this->path_sitemap.'macros.xlsm' );
		$this->assertTrue( $mtime_csv !== $mtime_xlsx ); // 何も行われないため、タイムスタンプはずれたまま。



		// 後始末
		$this->assertTrue( unlink( $this->path_sitemap.'macros.csv' ) );
		$this->assertTrue( unlink( $this->path_sitemap.'macros.xlsm' ) );
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}//testMasterFormatIsXlsxConvert()



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
