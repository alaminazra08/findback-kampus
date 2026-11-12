<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ItemClaim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ChatController extends Controller
{
    /**
     * Menampilkan daftar percakapan pengguna.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->where('role', 'user')
            ->whereHas('chatMessages')
            ->withCount([
                'chatMessages as unread_count' => function ($query) {
                    $query
                        ->whereColumn('chat_messages.sender_id', 'chat_messages.user_id')
                        ->whereNull('read_at');
                },
            ])
            ->withMax('chatMessages', 'created_at')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere(
                        'email',
                        'like',
                        "%{$search}%",
                    );
                });
            })
            ->orderByDesc('chat_messages_max_created_at')
            ->paginate(20)
            ->withQueryString();

        $selectedUser = null;
        $selectedClaim = null;
        $claimOptions = collect();
        $selectedMessages = collect();
        $canChat = false;
        if ($request->filled('user')) {
            $selectedUser = User::where('role', 'user')->findOrFail($request->integer('user'));
            // Only users with conversation history are selectable from this inbox.
            abort_unless($selectedUser->chatMessages()->exists(), 404);
            $claimOptions = ItemClaim::with('item')
                ->where('user_id', $selectedUser->id)
                ->latest('id')
                ->get();
            $selectedClaim = $claimOptions->firstWhere('id', $request->integer('claim_id'));
            $selectedMessages = $selectedClaim
                ? ChatMessage::where('user_id', $selectedUser->id)
                    ->where('claim_id', $selectedClaim->id)
                    ->with('sender')
                    ->latest('id')
                    ->limit(100)
                    ->get()
                    ->reverse()
                : collect();
            if (!$selectedClaim && $request->boolean('legacy')) {
                $selectedMessages = ChatMessage::where('user_id', $selectedUser->id)
                    ->whereNull('claim_id')
                    ->with('sender')
                    ->latest('id')
                    ->limit(100)
                    ->get()
                    ->reverse();
            }
            $canChat = $selectedClaim && $selectedClaim->status === 'approved';
            $this->markMessagesRead($selectedUser, $selectedMessages->pluck('id'));
        }
        return view(
            'admin.chat.index',
            compact(
                'users',
                'search',
                'selectedUser',
                'selectedMessages',
                'canChat',
                'selectedClaim',
                'claimOptions',
            ),
        );
    }

    /**
     * Menampilkan percakapan dengan pengguna.
     */
    public function show(Request $request, User $user)
    {
        abort_unless($user->role === 'user', 404);

        $claimOptions = ItemClaim::with('item')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();
        $selectedClaim = $claimOptions->firstWhere('id', $request->integer('claim_id'));
        $canChat = $selectedClaim && $selectedClaim->status === 'approved';
        $legacyMode = !$selectedClaim && $request->boolean('legacy');
        $messages = ChatMessage::with('sender')
            ->where('user_id', $user->id)
            ->when(
                $selectedClaim,
                fn ($q) => $q->where('claim_id', $selectedClaim->id),
                fn ($q) => $legacyMode ? $q->whereNull('claim_id') : $q->whereRaw('1 = 0'),
            )
            ->latest('id')
            ->paginate(50)
            ->withQueryString();
        $this->markMessagesRead($user, $messages->pluck('id'));

        return view(
            'admin.chat.show',
            compact('user', 'messages', 'canChat', 'selectedClaim', 'claimOptions'),
        );
    }

    /**
     * Admin mengirim balasan.
     */
    public function store(Request $request, User $user)
    {
        abort_unless($user->role === 'user', 404);
        abort_unless(
            ItemClaim::where('user_id', $user->id)
                ->where('status', 'approved')
                ->exists(),
            403,
            'Chat hanya tersedia setelah klaim disetujui dan sebelum serah terima selesai.',
        );

        // Setiap pesan baru wajib memiliki konteks klaim yang dipilih secara eksplisit.
        $request->validate(['from_claim' => ['required', 'integer', 'min:1']]);
        $claim = null;
        if ($request->exists('from_claim')) {
            $claim = ItemClaim::query()
                ->whereKey($request->integer('from_claim'))
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();
            abort_unless(
                $claim,
                403,
                'Chat hanya dapat digunakan untuk klaim yang disetujui dan belum selesai.',
            );
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:3000', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'required_without:message',
            ],
        ]);

        app(\App\Services\ClaimChatService::class)->send(
            $claim, $request->user(), $validated, $request->file('attachment'),
        );

        // Tetap di halaman Detail Klaim ketika pesan dikirim dari modal klaim.
        if ($request->boolean('from_inbox')) {
            return redirect()
                ->route('admin.chat.index', ['user' => $user->id, 'claim_id' => $claim->id])
                ->with('success', 'Pesan berhasil dikirim.');
        }
        if ($claim) {
            return redirect()
                ->route('admin.claims.show', ['claim' => $claim->id, 'chat' => 1])
                ->with('success', 'Pesan berhasil dikirim.');
        }

        return redirect()
            ->route('admin.chat.index', ['user' => $user->id])
            ->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Mengambil pesan baru dari pengguna secara otomatis.
     */
    public function messages(Request $request, User $user)
    {
        abort_unless($user->role === 'user', 404);

        $claim = ItemClaim::whereKey($request->integer('claim_id'))
            ->where('user_id', $user->id)
            ->first();
        abort_unless($claim, 403, 'Pilih klaim yang sesuai.');
        $after = max(0, (int) $request->query('after', 0));

        $messages = ChatMessage::with('sender')
            ->where('user_id', $user->id)
            ->where('claim_id', $claim->id)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'Pengguna',
                    'is_mine' => (int) $message->sender_id === (int) auth()->id(),
                    'message' => $message->message,
                    'attachment_url' => $message->attachment_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                            $message->attachment_path,
                        )
                        : null,
                    'created_at' => $message->created_at
                        ->timezone('Asia/Jakarta')
                        ->format('d/m/Y H:i'),
                ];
            });

        $this->markMessagesRead($user, $messages->pluck('id'));
        return response()->json([
            'messages' => $messages,
        ]);
    }

    private function markMessagesRead(User $user, Collection $ids): void
    {
        if ($ids->isNotEmpty()) {
            ChatMessage::where('user_id', $user->id)
                ->where('sender_id', $user->id)
                ->whereIn('id', $ids)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }
}
