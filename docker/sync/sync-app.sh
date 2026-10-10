#!/bin/sh
# Windows 側のフォルダ（/host = src）と、コンテナ内の速い領域（/app）を双方向にそろえる。
#
# なぜ必要か：Docker Desktop で Windows のフォルダを直接読むと、ファイル1つの確認に数ミリ秒かかる。
# Laravel は1回の表示で多くのファイルを確認するので、直接つなぐと表示に 1〜2 秒かかる。
# そこで Laravel には /app（速い）を読ませ、/host とは 2 秒おきにそろえる。
#
#   sync-app once   1回だけそろえて終わる（初回の準備で使う）
#   sync-app watch  そろえ続ける（普段はこれ）
set -u

MODE="${1:-watch}"
READY=/state/ready
rm -f "$READY"

if [ ! -f /host/artisan ]; then
    echo "[sync] /host に Laravel（artisan）が見つかりません。src フォルダを確認してください。" >&2
    exit 1
fi

# Laravel が書き込むフォルダは同期しないので、先に作っておく
mkdir -p /app/storage/framework/cache/data /app/storage/framework/sessions \
         /app/storage/framework/views /app/storage/framework/testing \
         /app/storage/logs /app/bootstrap/cache

run_unison() {
    # -batch          質問せずに進める
    # -prefer newer   両方で同じファイルを変えたときは、新しいほうを採用する
    # -times          更新日時もそろえる（どちらが新しいかを正しく比べるため）
    # -perms 0        Windows 側は権限を持てないので、権限はそろえない
    # 片方のフォルダが丸ごと空に見えるときは、unison が安全のために止まる（全消しを防ぐ既定の動き）
    unison /host /app \
        -batch -auto -prefer newer -times -perms 0 -dontchmod \
        -fastcheck true -links false -terse -log=false \
        -ignore 'Path vendor' \
        -ignore 'Path node_modules' \
        -ignore 'Path bootstrap/cache' \
        -ignore 'Path storage/framework' \
        -ignore 'Path public/hot' \
        -ignore 'Path public/build' \
        -ignore 'Path public/storage' \
        -ignore 'Name .git' \
        -ignore 'Name .phpunit.cache' \
        -ignore 'Name .phpunit.result.cache' \
        -ignore 'Name *.tmp' \
        "$@"
}

echo "[sync] 最初の同期をしています..."
run_unison
code=$?
# 0 = 差分なし・すべて成功、1 = 一部を見送り（次の回でそろう）、2 以上 = 失敗
if [ "$code" -ge 2 ]; then
    echo "[sync] 同期に失敗しました（終了コード $code）。" >&2
    exit "$code"
fi
touch "$READY"
echo "[sync] 最初の同期が終わりました。"

[ "$MODE" = "once" ] && exit 0

# 2 秒おきにそろえ続ける。失敗しても止まらず、少し待ってやり直す
while true; do
    run_unison -repeat 2 -silent
    echo "[sync] 同期が止まったので、やり直します（終了コード $?）。" >&2
    sleep 3
done
