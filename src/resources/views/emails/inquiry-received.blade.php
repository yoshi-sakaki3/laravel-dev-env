お問い合わせが届きました。

受付番号：{{ $inquiry->id }}
お名前：{{ $inquiry->name }}
メールアドレス：{{ $inquiry->email }}

――― 内容 ―――
{!! $inquiry->body !!}
―――――――――

管理画面で確認する：
{{ route('admin.inquiries.show', $inquiry) }}

このメールに返信すると、お問い合わせをした方に届きます。
