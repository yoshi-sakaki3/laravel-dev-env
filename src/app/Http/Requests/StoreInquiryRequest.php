<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** お問い合わせフォームの入力チェック */
class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 改行は受け付けない（メールの件名に入るため）
            'name' => ['required', 'string', 'max:50', 'not_regex:/[\r\n]/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'agree' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'body' => 'お問い合わせ内容',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'not_regex' => ':attributeに使えない文字が入っています。',
            'email' => 'メールアドレスの形が正しくありません。',
            'agree.accepted' => '個人情報の取り扱いへの同意にチェックを入れてください。',
        ];
    }

    /** 前後の空白（全角も）を取り除いてからチェックする */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->trimWide($this->input('name')),
            'email' => $this->trimWide($this->input('email')),
        ]);
    }

    private function trimWide(mixed $value): mixed
    {
        return is_string($value) ? preg_replace('/\A[\s　]+|[\s　]+\z/u', '', $value) : $value;
    }
}
