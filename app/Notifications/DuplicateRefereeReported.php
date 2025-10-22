<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // キューに乗せるなら
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DuplicateRefereeReported extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $reportPayload,   // 連絡フォームの入力など
        public array $originalInput    // 重複チェック時の元入力（氏名等）
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // 表示用
        $payload = $this->reportPayload;
        $input   = $this->originalInput;

        return (new MailMessage)
            ->subject('【重複疑い】審判員登録の確認依頼')
            ->greeting('管理者各位')
            ->line('審判員の新規登録で重複の可能性が報告されました。')
            ->line('―― 元入力（氏名等）――')
            ->line('漢字: ' . (($input['surname_kanji'] ?? '') . ' ' . ($input['name_kanji'] ?? '')))
            ->line('カナ: ' . (($input['surname_kana']  ?? '') . ' ' . ($input['name_kana']  ?? '')))
            ->line('英字: ' . (($input['surname']       ?? '') . ' ' . ($input['name']       ?? '')))
            ->line('―― 申請者からのメッセージ ――')
            ->line($payload['message'] ?? '(なし)')
            // 管理画面への導線があれば action() にURLを
            // ->action('管理画面で確認', route('admin.referees.show', [...]))
            ->line('このメールは自動送信です。対応をお願いします。');
    }
}