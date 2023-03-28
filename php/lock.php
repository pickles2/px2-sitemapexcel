<?php
/**
 * lock.php
 */
namespace tomk79\pickles2\sitemap_excel;

/**
 * lock.php
 */
class lock {
	/** Picklesオブジェクト */
	private $px;
	/** sitemapExcelオブジェクト */
	private $plugin;
	/** */
	private $path_sitemap_cache_dir;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin プラグインオブジェクト
	 */
	public function __construct( $px, $plugin ){
		$this->px = $px;
		$this->plugin = $plugin;
		$this->path_sitemap_cache_dir = $this->px->get_path_homedir().'_sys/ram/caches/sitemaps/';
		if( !$this->px->fs()->is_dir( $this->path_sitemap_cache_dir ) ){
			$this->px->fs()->mkdir_r( $this->path_sitemap_cache_dir );
		}
	}

	/**
	 * lock
	 */
	public function lock(){
		$i = 0;
		clearstatcache();
		while( @is_file( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt' ) ){
			$i ++;
			if( $i > 2 ){
				// 他のプロセスがサイトマップキャッシュを作成中。
				// 2秒待って解除されなければ、true を返して終了する。
				$this->px->error('Sitemap cache generating is now in progress. This page has been incompletely generated.');
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}

		touch( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt' );
		touch( $this->path_sitemap_cache_dir.'making_sitemapexcel.lock.txt' );

		return $this->update();
	}

	/**
	 * Update Lockfile
	 */
	public function update(){
		if( !is_file( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt' )
			|| !is_file( $this->path_sitemap_cache_dir.'making_sitemapexcel.lock.txt' ) ){
			// ロックされていない場合、更新できない。
			return false;
		}

		$lockfile_src = '';
		$lockfile_src .= 'ProcessID='.getmypid()."\r\n";
		$lockfile_src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$lockfile_src .= '* pickles2/px2-sitemapexcel'."\r\n";
		$res1 = $this->px->fs()->save_file( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt', $lockfile_src );
		$res2 = $this->px->fs()->save_file( $this->path_sitemap_cache_dir.'making_sitemapexcel.lock.txt', $lockfile_src );

		clearstatcache();
		if( !$res1 || !$res2 ){
			return false;
		}
		return true;
	}

	/**
	 * is_locked
	 */
	public function is_locked(){
		clearstatcache();
		$res1 = $this->px->fs()->is_file( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt' );
		$res2 = $this->px->fs()->is_file( $this->path_sitemap_cache_dir.'making_sitemapexcel.lock.txt' );
		if( $res1 || $res2 ){
			return true;
		}
		return false;
	}

	/**
	 * unlock
	 */
	public function unlock(){
		clearstatcache();
		$res1 = $this->px->fs()->rm( $this->path_sitemap_cache_dir.'making_sitemap_cache.lock.txt' );
		$res2 = $this->px->fs()->rm( $this->path_sitemap_cache_dir.'making_sitemapexcel.lock.txt' );
		if( !$res1 || !$res2 ){
			return false;
		}
		return true;
	}

}
