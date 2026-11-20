data-chat-admin-stream
data-recall-url="{{ route('admin.chat.recall', ['message' => '__ID__']) }}"
data-hide-url="{{ route('admin.chat.hide', ['message' => '__ID__']) }}"
data-chat-token="{{ csrf_token() }}"
