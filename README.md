pickles-sitemap-excel-2
=======================

*pickles-sitemap-excel-2* は、[Pickles2](https://github.com/tomk79/pickles2) のサイトマップを、エクセル形式(`*.xlsx`)で編集できるようにするプラグインです。

本来の Pickles2 のサイトマップは、CSV形式で管理されます。CSVはもっとも単純で基本的なデータ形式の1つで、コンピューターが処理するには扱いやすいフォーマットですが、人間が編集するには不便なこともありました。このプラグインを導入することで、より直感的に、グラフィカルに編集することができるようになります。

- `*.xlsx` 形式のサイトマップファイルを直接読み込むことができるようになります。
- ページの階層構造(`logical_path`)を、視覚的な階層構造で表現できます。
- エクセルの 色付きセル や テキスト装飾 などの編集機能を使い、美しい表をアレンジできます。
- セルの値として計算式を使うことができます。

エクセルファイルの操作には、 [phpoffice/phpexcel](https://github.com/PHPOffice/PHPExcel) を利用しています。


## 導入手順 - Setup

### 1. composer.json に pickles-sitemap-excel-2 を追加

require の項目に、"tomk79/pickles-sitemap-excel-2" を追加します。

```
{
	〜 中略 〜
    "require": {
        "php": ">=5.3.0" ,
        "tomk79/px-fw-2.x": "dev-master",
        "tomk79/pickles-sitemap-excel-2": "*"
    },
	〜 中略 〜
}
```


追加したら、`composer update` を実行して変更を反映することを忘れずに。

```
$ composer update
```


### 2. config.php に、機能を追加

`before_sitemap` のどこか(例では最後)に、`tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec` を追加します。

```
	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		// sitemapExcel
		'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec'
	];
```

Pickles2 の設定をJSON形式で編集している方は、config.json の該当箇所に追加してください。


### 3. サイトマップディレクトリに sitemap.xlsx を設置

エクセルファイルは、このリポジトリに同梱されている [sitemapexcel.xlsx](./tests/htdocs/.pickles/sitemaps/sitemapexcel.xlsx) をサンプルに作成してみてください。

編集したファイルは、あなたの Pickles2 のサイトマップディレクトリ `.pickles/sitemaps` に置きます。次回、ブラウザでアクセスした最初に、同名のCSVファイル(sitemap.xlsx なら、sitemap.csv)が自動的に更新されます。

その後も、エクセルのタイムスタンプが更新されるたびに、CSVファイルは自動更新されます。



## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
