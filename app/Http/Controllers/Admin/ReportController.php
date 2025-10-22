<?php

// app/Http/Controllers/Admin/DuplicateReportController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\DuplicateRefereeReported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class ReportController extends Controller
{
    public function dupicateCreate(Request $request)
    {
        // クエリに元入力が付与されていれば、そのまま表示
        return view('admin.referees.duplicate_report', [
            'prefill' => $request->all(),
        ]);
    }

    public function duplicateStore(Request $request)
    {

        $data = $request->validate([
            'message' => ['required','string','max:2000'],
            // 元入力は input[...] で渡している想定（hidden）
            'input'   => ['array'],
        ]);

        // 1) 通知先の決め方（どちらか）
        // A. 管理者ロールのユーザ全員へ
        $admins = User::where('role_id', User::ADMIN_ROLE_ID)->get();

        // B. または config で単一メールへ（両方併用も可）
        $fallbackEmail = config('mail.admin_notification_to'); // 後述

        // 2) 送信
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new DuplicateRefereeReported(
                reportPayload: $data,
                originalInput: $data['input'] ?? []
            ));
        }
        if ($fallbackEmail) {
            Notification::route('mail', $fallbackEmail)
                ->notify(new DuplicateRefereeReported(
                    reportPayload: $data,
                    originalInput: $data['input'] ?? []
                ));
        }
    }
}