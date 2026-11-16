<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    private function activeClaims()
    {
        return \App\Models\ItemClaim::with('item')
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->get();
    }

    private function canChat(): bool
    {
        return $this->activeClaims()->isNotEmpty();
    }

    public function index(Request $request)
    {
        $activeClaims = $this->activeClaims();
        $claimOptions = \App\Models\ItemClaim::with('item')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();
        $selectedClaim = $claimOptions->firstWhere('id', $request->integer('claim_id'));
        
        
        $legacyMode = !$selectedClaim && $request->boolean('legacy');
        $canChat = $selectedClaim && $selectedClaim->status === 'approved';
        $hasHistory = ChatMessage::where('user_id', auth()->id())->exists();
      
$messages = ChatMessage::with('sender')
    ->where('user_id', auth()->id())
    ->where('hidden_for_user', false)
    ->when(
        $selectedClaim,
        fn ($q) => $q->where(
            'claim_id',
            $selectedClaim->id
        ),
        fn ($q) => $legacyMode
            ? $q->whereNull('claim_id')
            : $q->whereRaw('1 = 0'),
    )
    ->latest('id')
    ->paginate(50)
    ->withQueryString();

        if ($selectedClaim || $legacyMode) {
            ChatMessage::where('user_id', auth()->id())
                ->where('sender_id', '!=', auth()->id())
                ->whereIn('id', $messages->pluck('id'))
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
        return view(
            'user.chat.index',
            compact(
                'messages',
                'activeClaims',
                'canChat',
                'hasHistory',
                'selectedClaim',
                'claimOptions',
            ),
        );
    }

    
/**
 * Menyiapkan data pesan untuk AJAX.
 */
private function chatPayload(ChatMessage $message): array
{
    return [
        'id' => $message->id,

        'is_mine' =>
            (int) $message->sender_id ===
            (int) auth()->id(),

        'message' => $message->recalled_at
            ? null
            : $message->message,

        'attachment_url' => (
            !$message->recalled_at &&
            $message->attachment_path
        )
            ? Storage::disk('public')->url(
                $message->attachment_path
            )
            : null,

        'recalled_at' =>
            $message->recalled_at?->toIso8601String(),

        'edited_at' =>
            $message->edited_at?->toIso8601String(),

        'created_at' =>
            $message->created_at
                ->timezone('Asia/Jakarta')
                ->format('d M Y · H:i'),
    ];
}

    public function store(Request $request)
    {
        $request->validate(['claim_id' => ['required', 'integer', 'min:1']]);
        $claim = \App\Models\ItemClaim::whereKey($request->integer('claim_id'))
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->first();
        abort_unless(
            $claim,
            403,
            'Chat hanya tersedia untuk klaim yang disetujui dan belum selesai.',
        );
        $validated = $request->validate(
            [
                'message' => ['nullable', 'string', 'max:3000', 'required_without:attachment'],
                'attachment' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                    'required_without:message',
                ],
            ],
            [
                'message.required_without' => 'Tuliskan pesan atau lampirkan gambar.',
                'attachment.required_without' => 'Lampirkan gambar atau tuliskan pesan.',
                'attachment.image' => 'Lampiran harus berupa gambar.',
                'attachment.max' => 'Ukuran gambar maksimal 4 MB.',
            ],
        );

       
$sent = app(\App\Services\ClaimChatService::class)->send(
    $claim,
    $request->user(),
    $validated,
    $request->file('attachment')
);

// Jika dikirim menggunakan AJAX,
// kirim respons JSON tanpa reload.
if ($request->expectsJson()) {
    return response()->json([
        'message' => $this->chatPayload($sent),
    ], 201);
}

// Tetap mendukung pengiriman formulir biasa.
return redirect()
    ->route('user.chat.index', [
        'claim_id' => $claim->id
    ])
    ->with(
        'success',
        'Pesan berhasil dikirim.'
    );

    }

    /**
     * Mengambil pesan baru secara otomatis.
     */
   
/**
 * Mengambil pesan percakapan untuk pembaruan otomatis.
 */
public function messages(Request $request)
{
    $claim = \App\Models\ItemClaim::whereKey(
        $request->integer('claim_id')
    )
        ->where('user_id', auth()->id())
        ->first();

    abort_unless(
        $claim,
        403,
        'Pilih klaim yang sesuai.'
    );

    $userId = auth()->id();

    $after = max(
        0,
        (int) $request->query('after', 0)
    );

    $messages = ChatMessage::with('sender')
        ->where('user_id', $userId)
        ->where('claim_id', $claim->id)
        ->where('hidden_for_user', false)
        ->where('id', '>', $after)
        ->orderBy('id')
        ->limit(100)
        ->get()
        ->map(function ($message) use ($userId) {
            return [
                'id' => $message->id,

                'sender_id' => $message->sender_id,

                'sender_name' =>
                    $message->sender?->name ?? 'Admin',

                'is_mine' =>
                    (int) $message->sender_id ===
                    (int) $userId,

                'message' => $message->recalled_at
                    ? null
                    : $message->message,

                'recalled_at' =>
                    $message->recalled_at?->toIso8601String(),

                'edited_at' =>
                    $message->edited_at?->toIso8601String(),

                'attachment_url' =>
                    !$message->recalled_at
                    && $message->attachment_path
                        ? Storage::disk('public')->url(
                            $message->attachment_path
                        )
                        : null,

                'created_at' =>
                    $message->created_at
                        ->timezone('Asia/Jakarta')
                        ->format('d/m/Y H:i'),
            ];
        });

    ChatMessage::where('user_id', $userId)
        ->where('sender_id', '!=', $userId)
        ->whereIn('id', $messages->pluck('id'))
        ->whereNull('read_at')
        ->update([
            'read_at' => now()
        ]);

    return response()->json([
        'messages' => $messages,
    ]);
}

/**
 * Memastikan pesan berasal dari pengguna
 * dan klaim yang menjadi miliknya.
 */
private function ownedMessage(ChatMessage $message): ChatMessage
{
    abort_unless(
        (int) $message->sender_id === (int) auth()->id()
        && (int) $message->user_id === (int) auth()->id()
        && $message->claim_id !== null
        && \App\Models\ItemClaim::whereKey(
            $message->claim_id
        )
            ->where('user_id', auth()->id())
            ->exists(),
        403,
        'Tidak berhak mengubah pesan ini.'
    );

    return $message;
}

/**
 * Edit pesan milik sendiri.
 */
public function update(Request $request, ChatMessage $message)
{
    $message = $this->ownedMessage($message);

    abort_if(
        $message->recalled_at || $message->hidden_for_user,
        422,
        'Pesan tidak dapat diedit.'
    );

    abort_if(
        !$message->message,
        422,
        'Pesan tanpa teks tidak dapat diedit.'
    );

    $data = $request->validate([
        'message' => [
            'required',
            'string',
            'max:3000',
        ],
    ]);

    $message->update([
        'message' => trim($data['message']),
        'edited_at' => now(),
    ]);

    
if ($request->expectsJson()) {
    return response()->json([
        'message' => $this->chatPayload(
            $message->fresh()
        ),
    ]);
}

return back()->with(
    'success',
    'Pesan berhasil diedit.'
);

}

/**
 * Tarik pesan untuk kedua pihak.
 */
public function recall(Request $request, ChatMessage $message)
{
    $message = $this->ownedMessage($message);

    abort_if(
        $message->recalled_at,
        422,
        'Pesan sudah ditarik.'
    );

    $attachmentPath = $message->attachment_path;

    $message->update([
        'message' => null,
        'attachment_path' => null,
        'recalled_at' => now(),
    ]);

    if ($attachmentPath) {
        Storage::disk('public')->delete(
            $attachmentPath
        );
    }

   
if ($request->expectsJson()) {
    return response()->json([
        'message' => $this->chatPayload(
            $message->fresh()
        ),
    ]);
}

return back()->with(
    'success',
    'Pesan ditarik untuk semua.'
);

}

/**
 * Hapus pesan dari tampilan pengguna saja.
 */
public function hide(Request $request, ChatMessage $message)
{
    abort_unless(
        (int) $message->user_id === (int) auth()->id()
        && (
            $message->claim_id === null
            || \App\Models\ItemClaim::whereKey($message->claim_id)
                ->where('user_id', auth()->id())
                ->exists()
        ),
        403,
        'Tidak berhak menyembunyikan pesan ini.'
    );

    $message->update([
        'hidden_for_user' => true,
    ]);

    
if ($request->expectsJson()) {
    return response()->json([
        'id' => $message->id,
        'hidden' => true,
    ]);
}

return back()->with(
    'success',
    'Pesan dihapus dari tampilan kamu.'
);

}

}

