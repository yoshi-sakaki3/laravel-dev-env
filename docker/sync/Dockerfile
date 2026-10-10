# Windows のフォルダと、コンテナ内の速い領域をそろえる同期役（unison）
FROM debian:bookworm-slim

RUN apt-get update \
 && apt-get install -y --no-install-recommends unison \
 && rm -rf /var/lib/apt/lists/*

# unison の記録（前回そろえた時点の状態）の置き場。ここが残っていると、削除も正しく伝わる
ENV HOME=/state \
    TZ=Asia/Tokyo

COPY sync-app.sh /usr/local/bin/sync-app
RUN chmod +x /usr/local/bin/sync-app

CMD ["sync-app", "watch"]
