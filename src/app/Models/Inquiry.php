<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * お問い合わせ（inquiries テーブル）。
 * status と note（社内メモ）は管理画面からだけ変える。
 *
 * @property InquiryStatus $status
 * @property string|null $note
 */
#[Fillable(['name', 'email', 'body'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /**
     * 届いたばかりのお問い合わせは「未対応」。
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => InquiryStatus::New->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InquiryStatus::class,
        ];
    }

    /**
     * 名前・メールアドレス・本文の部分一致で絞り込む。
     *
     * @param  Builder<Inquiry>  $query
     */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return;
        }
        // % と _ を「文字」として探す
        $like = '%'.addcslashes($keyword, '%_\\').'%';
        $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('body', 'like', $like);
        });
    }
}
