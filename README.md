# Laravel 開発環境（お問い合わせ管理の見本つき・Docker Compose + Dev Containers）

Docker Compose と VS Code Dev Containers で動く、Laravel の開発環境です。
**Windows でも画面の表示が速い**ように、ソースをコンテナ内の速い領域に同期して動かします。Windows（PowerShell）向けの操作コマンド `dev.ps1` 付き。

動作確認済み（2026-10-10）：Windows 11 + Docker Desktop で、環境チェック画面・お問い合わせの送信（入力チェック）・通知と自動返信のメール（Mailpit）・管理画面のログインと検索・対応状況の更新・保存時の自動反映・双方向の同期（作成・変更・削除）・テスト（テスト用 DB）・書き方のチェック・静的解析。

| 画面 | URL | 内容 |
|---|---|---|
| 環境チェック | http://localhost:8280 | PHP・Laravel のバージョン、DB の接続 |
| お問い合わせフォーム | http://localhost:8280/contact | 送信すると DB に保存し、通知メールと自動返信メールを送る |
| 管理画面 | http://localhost:8280/admin/inquiries | 検索・対応状況の絞り込み・社内メモ（ログイン：`admin@example.com` / `password`。ローカル開発専用） |
| Mailpit | http://localhost:8030 | アプリが送ったメールを受け止めて表示（外部には送られない） |
| phpMyAdmin | http://localhost:8281 | DB を画面で見る |

## 入っているもの

- PHP 8.4（PHP-FPM）＋ nginx、Laravel 13、MySQL 8.4
- 画面：Blade ＋ Tailwind CSS 4 ＋ Vite（保存するとブラウザが自動で読み込み直す）
- メール：Mailpit（開発中のメールは外に出ない）
- 品質チェック：PHPUnit（テスト）、Laravel Pint（書き方をそろえる）、Larastan（静的解析）
- テスト用の DB（`app_test`）を開発用とは別に用意。テストで開発中のデータが消えない
- Xdebug（ステップ実行。普段はオフ、`.\dev.ps1 xdebug on` で有効）

### 見本（お問い合わせ管理）で気を付けていること

- 入力チェックは FormRequest にまとめ、エラーは日本語で欄ごとに表示
- 迷惑送信の対策：人には見えない欄（ハニーポット）と、同じ人からの送信を1分に5回までにする制限
- 名前に改行が入った送信を断る（メールの件名に入るため）
- 管理画面はログイン必須。ログインは1分に5回まで、ログイン時にセッション ID を作り直す
- 画面に出す値はすべてエスケープ（本文に HTML が入っていても、文字として表示）
- 検索語の `%` や `_` は「文字」として探す

> 自動返信メールは、フォームに入力されたアドレスあてに送ります。本番で使うときは、いたずらで他人のアドレスを入れられる可能性を考えて、送信回数の制限や reCAPTCHA などを案件に合わせて足してください。

## 初回だけの準備

1. Docker Desktop を起動する（初めての PC は、先に管理者の PowerShell で `wsl --install` と再起動が必要）
2. このフォルダで準備コマンドを実行する
   ```powershell
   cd laravel-dev-env
   .\dev.ps1 setup
   ```
   Composer の部品の取得・アプリの鍵の作成・テーブルの作成・見本データ（18件）の投入まで自動で行います。
   終わってから 1 分ほど（画面の CSS の準備）待って、http://localhost:8280 を開いてください。

> PowerShell で「スクリプトの実行が無効」と出たら：`powershell -ExecutionPolicy Bypass -File .\dev.ps1 setup`

## 普段の使い方

```powershell
.\dev.ps1 up              # 起動
.\dev.ps1 down            # 停止
.\dev.ps1 artisan make:model Item -mf   # artisan コマンド
.\dev.ps1 composer require xxx          # ライブラリを追加
.\dev.ps1 check           # 書き方のチェック・静的解析・テストをまとめて実行
.\dev.ps1 format          # 書き方を自動でそろえる
.\dev.ps1 help            # コマンド一覧
```

## Windows でも速い理由（同期の仕組み）

Docker Desktop で Windows のフォルダをコンテナから直接読むと、ファイル1つの確認に数ミリ秒かかります。Laravel は1回の表示で多くのファイルを確認するので、直接つなぐと表示に 1 秒前後かかります。
この環境では、Laravel にはコンテナ内の速い領域を読ませ、`src` フォルダとは同期ツール（unison）が 2 秒おきに双方向でそろえます。

| 測ったもの（この環境を作った PC での実測） | 直接つないだとき | 同期したとき |
|---|---|---|
| 画面の表示（1回） | 0.5〜2 秒 | 0.05〜0.1 秒 |
| テスト 12 件 | 約 24 秒 | 約 4 秒 |

- `src` で保存した内容は、1 秒ほどで画面に反映されます。
- コンテナの中で作ったファイル（`artisan make:...` や `format` の結果）は、1〜2 秒で `src` に戻ります。削除は 3〜4 秒で伝わります。
- `vendor` と `node_modules` は同期しません（コンテナ側だけにあります）。エディタで補完を効かせたいときは、下の「VS Code で開発する」を使ってください。
- 両方で同じファイルを同時に変えたときは、新しいほうを採用します。
- 片方のフォルダが丸ごと空に見えるときは、全消しを防ぐために同期が止まります。様子は `.\dev.ps1 sync` で見られます。
- 同期役は、待機中も CPU を少し使います（実測で 1 コアの 10〜25%）。使わないときは `.\dev.ps1 down` で止めてください。

## 設定の優先順位

DB やメールの接続先は、`compose.yaml` の `environment` に書いてあり、Laravel の `src/.env` より優先されます（Docker 用の値にそろえるため）。
アプリの鍵（`APP_KEY`）だけは `src/.env` に入ります（`setup` が自動で作ります）。

## テストについて

テストは必ずテスト用の DB（`app_test`）で動きます。Docker の環境変数より優先させるため、`phpunit.xml` では `env`（force）と `server` の両方に書いています（Laravel は `$_SERVER` を先に読むので、`env` だけでは開発用の DB につながります）。
念のため、テスト用以外の DB につながっていたら、テーブルを作り直す前に止める安全装置も入れています（`tests/TestCase.php`）。

## 開発用の割り切り（本番では使わない設定）

- PHP を root で動かしています。artisan（コマンド）と画面でユーザーが分かれると、片方が作ったログやキャッシュにもう片方が書けなくなるためです（`docker/php/fpm-env.conf`）。
- 管理画面の見本ログイン（`admin@example.com` / `password`）は、ローカル専用です。

## VS Code で開発する（おすすめ）

VS Code でこのフォルダを開き、「コンテナーで再度開く（Reopen in Container）」を選ぶと、コンテナの中で補完・Pint・静的解析・テストがそのまま使えます（拡張機能「Dev Containers」が必要）。中で編集した内容は `src` にも反映されます。

## お客さまのソースを扱うときの注意

- お客さまのソースや個人情報を AI（Claude など）に渡す前に、秘密保持契約で外部の AI サービスの利用が認められているかを確認する
- 本物の個人情報（お問い合わせの内容など）を開発環境に持ち込まない（見本データか、匿名化したデータを使う）
- `.env` には本番のパスワードや API キーを入れない（この環境はローカル専用の値で動く）

## フォルダ構成

```
laravel-dev-env/
├─ compose.yaml            … どのサービスを起動するか
├─ env.example             … 設定の見本（setup で .env にコピーされる）
├─ dev.ps1                 … 操作コマンド
├─ docker/
│  ├─ php/                 … PHP のイメージと設定
│  ├─ nginx/               … Web サーバーの設定
│  ├─ mysql/               … テスト用 DB を作るスクリプト
│  └─ sync/                … src とコンテナをそろえる同期役（unison）
├─ src/                    … Laravel のソース（ここを案件ごとに入れ替える）
│  ├─ app/                 … コントローラー・モデル・メール・入力チェック
│  ├─ resources/views/     … 画面のひな形（Blade）
│  ├─ database/            … テーブルの定義・見本データ
│  └─ tests/               … テスト
└─ .devcontainer/          … VS Code Dev Containers の設定
```
