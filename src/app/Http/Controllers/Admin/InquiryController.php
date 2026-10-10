<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** 管理画面：お問い合わせの一覧・詳細・対応状況の更新 */
class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->toString();
        $status = InquiryStatus::tryFrom($request->string('status')->toString());

        $inquiries = Inquiry::query()
            ->search($keyword)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'keyword' => $keyword,
            'status' => $status,
            'counts' => Inquiry::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', ['inquiry' => $inquiry]);
    }

    public function update(UpdateInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry->status = InquiryStatus::from($request->validated('status'));
        $inquiry->note = $request->validated('note');
        $inquiry->save();

        return to_route('admin.inquiries.show', $inquiry)->with('message', '保存しました。');
    }
}
