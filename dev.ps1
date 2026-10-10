<#
  Laravel 開発環境の操作コマンド（PowerShell）
  使い方:  .\dev.ps1 <コマンド> [引数]
  一覧:    .\dev.ps1 help
#>
# 引数は param() で受けず、そのまま読む。
# param() を使うと、-c・-e・-v のようにハイフンで始まる指定を PowerShell がこのスクリプトあての指定と解釈して、
# エラーになったり、黙って消えたりするため（例：python -c ...、mvn -e ...）。
$Command = if ($args.Count -gt 0) { [string]$args[0] } else { 'help' }
$Rest = @(if ($args.Count -gt 1) { $args[1..($args.Count - 1)] })

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

function Invoke-Compose { docker compose @args; if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE } }

function Test-Docker {
    docker info *> $null
    if ($LASTEXITCODE -ne 0) {
        Write-Host 'Docker が起動していません。Docker Desktop を起動してから、もう一度実行してください。' -ForegroundColor Yellow
        exit 1
    }
}

function Set-EnvValue([string]$Key, [string]$Value) {
    # UTF-8 で読み書きする（PowerShell 5.1 の既定では日本語のコメント行が壊れ、次の行とくっつくため）
    $path = Join-Path $PSScriptRoot '.env'
    $utf8 = New-Object System.Text.UTF8Encoding($false)
    $lines = [IO.File]::ReadAllLines($path, $utf8)
    $found = $false
    $lines = foreach ($line in $lines) {
        if ($line -match "^$Key=") { $found = $true; "$Key=$Value" } else { $line }
    }
    if (-not $found) { $lines = @($lines) + "$Key=$Value" }
    [IO.File]::WriteAllLines($path, [string[]]$lines, $utf8)
}

switch ($Command) {
    'setup' {
        if (-not (Test-Path .env)) { Copy-Item env.example .env; Write-Host '.env を作成しました。' }
        Test-Docker
        Invoke-Compose build
        # src フォルダの内容を、コンテナ内の速い領域にそろえる（以後は sync サービスが 2 秒おきにそろえ続ける）
        Invoke-Compose run --rm sync sync-app once
        Invoke-Compose run --rm --no-deps php composer install --no-interaction
        # Laravel の .env（アプリの鍵など）を用意する。DB やメールの接続先は compose.yaml の値が優先される
        Invoke-Compose run --rm --no-deps php sh -c "[ -f .env ] || cp .env.example .env; grep -q '^APP_KEY=base64:' .env || php artisan key:generate --force"
        Invoke-Compose up -d db
        Invoke-Compose run --rm php php artisan migrate --seed --force
        Invoke-Compose up -d
        Write-Host ''
        Write-Host '準備ができました。初回は画面の CSS の準備（npm install）で 1 分ほどかかります。' -ForegroundColor Green
        & $PSCommandPath urls
    }
    'up'      { Test-Docker; Invoke-Compose up -d; & $PSCommandPath urls }
    'down'    { Invoke-Compose down }
    'restart' { Invoke-Compose restart }
    'rebuild' { Test-Docker; Invoke-Compose build --no-cache; Invoke-Compose up -d }
    'status'  { Invoke-Compose ps }
    'logs'    { Invoke-Compose logs -f --tail 100 @Rest }
    'sync'    { Invoke-Compose ps sync; Invoke-Compose logs --tail 20 sync }
    'sh'      { Invoke-Compose exec php bash }
    'artisan' { Invoke-Compose exec php php artisan @Rest }
    'composer' { Invoke-Compose exec php composer @Rest }
    'npm'     { Invoke-Compose exec vite npm @Rest }
    'tinker'  { Invoke-Compose exec php php artisan tinker }
    'migrate' { Invoke-Compose exec php php artisan migrate }
    'fresh' {
        $ans = Read-Host '開発用の DB を作り直して、見本データを入れ直します。よろしいですか？ (yes/no)'
        if ($ans -eq 'yes') { Invoke-Compose exec php php artisan migrate:fresh --seed }
    }
    'test'    { Invoke-Compose exec php php artisan test @Rest }
    'format'  { Invoke-Compose exec php vendor/bin/pint }
    'check' {
        Invoke-Compose exec php vendor/bin/pint --test
        Invoke-Compose exec php vendor/bin/phpstan analyse --memory-limit=1G --no-progress
        Invoke-Compose exec php php artisan test
    }
    'build'   { Invoke-Compose exec vite npm run build }
    'db'      { Invoke-Compose exec db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' }
    'xdebug' {
        $mode = if ($Rest -and $Rest[0] -eq 'on') { 'debug' } else { 'off' }
        Set-EnvValue 'XDEBUG_MODE' $mode
        Invoke-Compose up -d php
        Write-Host "Xdebug: $mode"
    }
    'urls' {
        Write-Host '  アプリ       http://localhost:8280'
        Write-Host '  管理画面     http://localhost:8280/admin/inquiries（admin@example.com / password）'
        Write-Host '  Mailpit      http://localhost:8030'
        Write-Host '  phpMyAdmin   http://localhost:8281'
        Write-Host '  DB           localhost:3307（ユーザー app / パスワード app）'
    }
    'clean' {
        $ans = Read-Host 'DB・vendor・node_modules を含めて全部消します。よろしいですか？ (yes/no)'
        if ($ans -eq 'yes') { Invoke-Compose down -v --rmi local }
    }
    default {
        @'
使い方: .\dev.ps1 <コマンド>

  setup          初回の準備（.env 作成・ビルド・同期・composer install・鍵の作成・テーブル作成・見本データ・起動）
  up / down      起動 / 停止
  restart        再起動
  rebuild        イメージを作り直して起動（Dockerfile を変えたとき）
  status         動いているサービスの一覧
  logs [名前]    ログを見る（例: .\dev.ps1 logs php）
  sync           src フォルダとコンテナの同期の状態を見る
  sh             PHP のコンテナの中に入る
  artisan ...    artisan コマンド（例: .\dev.ps1 artisan make:model Item -mf）
  composer ...   composer コマンド（例: .\dev.ps1 composer require spatie/laravel-permission）
  npm ...        npm コマンド（例: .\dev.ps1 npm install alpinejs）
  tinker         Laravel の対話コンソール
  migrate        テーブルの変更を DB に反映
  fresh          開発用の DB を作り直して見本データを入れ直す（確認あり）
  test           テストを実行（テスト用の DB を使う）
  format         コードの書き方を自動でそろえる（Pint）
  check          書き方のチェック・静的解析・テストをまとめて実行
  build          本番用の CSS・JS を作る
  db             MySQL に接続
  xdebug on|off  ステップ実行（Xdebug）の切り替え
  urls           各画面の URL を表示
  clean          データごと全部消す（確認あり）
'@ | Write-Host
    }
}
