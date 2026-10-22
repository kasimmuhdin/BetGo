@extends('layouts.app')
@section('title', 'Your Messages')
@section('content')
    <div class="max-w-3xl mx-auto text-white" x-data="conversationsList({{ auth()->id() }}, {{ json_encode($conversations) }})" x-init="listen()">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Messages</h1>
                <p class="text-sm text-gray-400">Conversations with other users.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <a href="{{ route('messages.compose') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    New message
                </a>
            <div class="relative max-w-sm w-full">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" x-model="searchQuery" placeholder="Search conversations..."
                    class="w-full bg-[#1e1b1b] border border-[#3a3a3d] rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            </div>
        </div>

        <template x-if="filteredConversations.length === 0">
            <div class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-10 text-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 text-gray-500">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                <span x-text="searchQuery ? 'No matches found.' : 'No conversations yet.'"></span>
            </div>
        </template>

        <div class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl divide-y divide-[#3a3a3d] overflow-hidden"
            x-show="filteredConversations.length > 0">
            <template x-for="conv in filteredConversations" :key="conv.id">
                <a :href="`/messages/${conv.id}`" class="flex items-center gap-4 px-5 py-4 hover:bg-[#2a2727] transition">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold flex-shrink-0 text-white"
                        x-text="conv.other_user.name.charAt(0).toUpperCase()"></div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium text-white truncate" x-text="conv.other_user.name"></span>
                            <span class="text-xs text-gray-500 flex-shrink-0" x-text="formatDate(conv.last_at)"></span>
                        </div>
                        <template x-if="conv.property">
                            <p class="text-xs text-indigo-400 truncate mt-0.5" x-text="conv.property.title"></p>
                        </template>
                        <p class="text-sm truncate mt-0.5"
                            :class="conv.unread_count > 0 ? 'text-white font-medium' : 'text-gray-400'"
                            x-text="conv.last_message || (conv.image_url ? '📷 Image' : 'No messages yet')"></p>
                    </div>

                    <template x-if="conv.unread_count > 0">
                        <span
                            class="flex-shrink-0 min-w-[1.5rem] h-6 px-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold flex items-center justify-center"
                            x-text="conv.unread_count"></span>
                    </template>
                </a>
            </template>
        </div>
    </div>

    <script>
        function conversationsList(userId, initialConversations) {
            return {
                userId,
                searchQuery: '',
                conversations: initialConversations,

                get filteredConversations() {
                    if (this.searchQuery === '') return this.conversations;
                    const q = this.searchQuery.toLowerCase();
                    return this.conversations.filter(c =>
                        c.other_user.name.toLowerCase().includes(q) ||
                        (c.property && c.property.title.toLowerCase().includes(q)) ||
                        (c.last_message && c.last_message.toLowerCase().includes(q))
                    );
                },

                listen() {
                    // Listen globally for updates to index page counts
                    window.Echo.private(`App.Models.User.${this.userId}`)
                        .listen('.message.sent', (e) => {
                            let convIndex = this.conversations.findIndex(c => c.id === e.conversation_id);
                            if (convIndex !== -1) {
                                let conv = this.conversations[convIndex];
                                conv.last_message = e.body;
                                conv.last_at = e.created_at;
                                conv.unread_count += 1;
                                // Move to top
                                this.conversations.splice(convIndex, 1);
                                this.conversations.unshift(conv);
                            }
                        });
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const d = new Date(dateString);
                    return d.toLocaleDateString([], {
                        month: 'short',
                        day: 'numeric'
                    });
                }
            }
        }
    </script>
@endsection
