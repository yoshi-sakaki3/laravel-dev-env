<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Mail\InquiryAutoReply;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** お問い合わせフォーム（公開側） */
class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact.create');
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        // 迷惑送信の対策：人には見えない欄（website）が埋まっていたら、保存せずに完了画面だけ見せる
        if (filled($request->input('website'))) {
            return to_route('contact.thanks');
        }

        $inquiry = Inquiry::create($request->safe()->only(['name', 'email', 'body']));

        Mail::to(config('contact.notify_to'))->send(new InquiryReceived($inquiry));
        Mail::to($inquiry->email)->send(new InquiryAutoReply($inquiry));

        return to_route('contact.thanks');
    }

    public function thanks(): View
    {
        return view('contact.thanks');
    }
}
