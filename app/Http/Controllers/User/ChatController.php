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
        if (!$selectedClaim && !$request->filled('claim_id')) {
            $selectedClaim = $activeClaims->count() === 1 ? $activeClaims->first() : null;
        }
        $legacyMode = !$selectedClaim && $request->boolean('legacy');
        $canChat = $selectedClaim && $selectedClaim->status === 'approved';
        $hasHistory = ChatMessage::where('user_id', auth()->id())->exists();
        $messages = ChatMessage::with('sender')
            ->where('user_id', auth()->id())
            ->when(
                $selectedClaim,
                fn ($q) => $q->where('claim_id', $selectedClaim->id),
                fn ($q) => $legacyMode ? $q->whereNull('claim_id') : $q->whereRaw('1 = 0'),
            )
            ->latest('id')
            ->paginate(50)
            ->withQueryString();
        if ($canChat) {
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

        $path = null;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat-attachments', 'public');
        }

        ChatMessage::create([
            'user_id' => auth()->id(),
            'claim_id' => $claim->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'attachment_path' => $path,
        ]);

        return redirect()
            ->route('user.chat.index', ['claim_id' => $claim->id])
            ->with('success', 'Pesan berhasil dikirim.');
    }

    /**
     * Mengambil pesan baru secara otomatis.
     */
    public function messages(Request $request)
    {
        $claim = \App\Models\ItemClaim::whereKey($request->integer('claim_id'))
            ->where('user_id', auth()->id())
            ->first();
        abort_unless($claim, 403, 'Pilih klaim yang sesuai.');
        $userId = auth()->id();

        $after = max(0, (int) $request->query('after', 0));

        $messages = ChatMessage::with('sender')
            ->where('user_id', $userId)
            ->where('claim_id', $claim->id)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(function ($message) use ($userId) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'Admin',
                    'is_mine' => (int) $message->sender_id === (int) $userId,
                    'message' => $message->message,
                    'attachment_url' => $message->attachment_path
                        ? Storage::disk('public')->url($message->attachment_path)
                        : null,
                    'created_at' => $message->created_at
                        ->timezone('Asia/Jakarta')
                        ->format('d/m/Y H:i'),
                ];
            });

        ChatMessage::where('user_id', $userId)
            ->where('sender_id', '!=', $userId)
            ->whereIn('id', $messages->pluck('id'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json([
            'messages' => $messages,
        ]);
    }
}
