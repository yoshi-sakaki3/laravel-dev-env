<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テストでは CSS・JS のビルド結果を読まない（Vite を動かさなくてもテストできる）
        $this->withoutVite();
    }

    /**
     * 念のための安全装置：RefreshDatabase がテーブルを作り直す前に、
     * テスト用以外の DB につながっていたら止める（開発中のデータを消さないため）。
     *
     * @return array<class-string, class-string>
     */
    protected function setUpTraits()
    {
        $database = config('database.connections.'.config('database.default').'.database');
        if ($database !== 'app_test') {
            throw new RuntimeException("テスト用の DB（app_test）ではなく「{$database}」につながっています。phpunit.xml を確認してください。");
        }

        return parent::setUpTraits();
    }
}
