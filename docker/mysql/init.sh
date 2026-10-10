#!/bin/sh
# DB を初めて作るときだけ実行される。テスト用の DB（app_test）を、開発用とは別に用意する
mysql -uroot -p"$MYSQL_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS app_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON app_test.* TO '$MYSQL_USER'@'%';
SQL
