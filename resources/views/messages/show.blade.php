@extends('layouts.app')
@php
    $otherUser = $conversation->otherUser(auth()->id());

@endphp
@section('title', 'conversation with ' . $otherUser->name)
@section('content')
    @php
        $otherUser = $conversation->otherUser(auth()->id());
        $initialMessages = $conversation->messages
            ->map(
                fn($m) => [
                    'id' => $m->id,
                    'body' => $m->body,
                    'image_url' => $m->image_path ? asset('storage/' . $m->image_path) : null,
                    'sender_id' => $m->sender_id,
                    'reply_to' => $m->reply_to_id
                        ? ['body' => $m->replyTo->body, 'sender_name' => $m->replyTo->sender->name]
                        : null,
                    'read_at' => $m->read_at?->toIso8601String(),
                    'created_at' => $m->created_at->toIso8601String(),
                ],
            )
            ->values()
            ->toArray();
    @endphp

    <style>
        .bgforall {
            background-color: #1e1b1b;
            border: 1px solid #3a3a3d;
            color: #ffffff;
        }

        .hide-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .hide-scroll::-webkit-scrollbar-thumb {
            background: #3a3a3d;
            border-radius: 4px;
        }
    </style>

    <div class="bgforall max-w-2xl mx-auto text-white relative flex flex-col" style="height: 85vh" x-data="messageThread({{ $conversation->id }}, {{ auth()->id() }}, {{ $otherUser?->id ?? 'null' }})"
        x-init="initThread()" @focus.window="markAsReadVisible()" @presence-changed.window="checkPresence()">

        {{-- Thread header --}}
        <div class="flex items-center justify-between p-4 border-b border-[#3a3a3d] shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-white shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <div class="relative">
                    @if ($otherUser?->profile_picture)
                        <img src="{{ $otherUser->profile_picture }}"
                            class="w-10 h-10 rounded-full object-cover shrink-0 border border-[#3a3a3d]">
                    @else
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold shrink-0">
                            {{ strtoupper(substr($otherUser->name ?? '?', 0, 1)) }}</div>
                    @endif
                    <!-- Online Status Indicator -->
                    <span x-show="isOnline" style="display:none;"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-[#1e1b1b] rounded-full"></span>
                </div>
                <div>
                    <h1 class="text-base font-semibold text-white leading-none">{{ $otherUser->name }}</h1>
                    <p class="text-xs mt-1" :class="isOnline ? 'text-green-400' : 'text-gray-500'"
                        x-text="isOnline ? 'Online' : 'Offline'"></p>
                </div>
            </div>

            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Search chat..."
                    class="bg-[#161414] border border-[#3a3a3d] text-sm rounded-full px-3 py-1.5 focus:outline-none focus:border-indigo-500 w-32 md:w-48 transition-all">
            </div>
        </div>

        {{-- Property Context --}}
        @if ($conversation->property)
            <div class="px-4 py-2 shrink-0 border-b border-[#3a3a3d]">
                <a href="{{ route('property.show', $conversation->property) }}"
                    class="flex items-center gap-3 hover:opacity-80 transition">
                    <img src="{{ $conversation->property->cover_url ?? 'https://placehold.co/80x80/161414/6366f1?text=Prop' }}"
                        class="w-10 h-10 rounded object-cover shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white truncate">{{ $conversation->property->title }}</p>
                        <p class="text-xs text-indigo-400">View Listing →</p>
                    </div>
                </a>
            </div>
        @endif

        {{-- Thread body --}}
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-2 hide-scroll" x-ref="scrollBox" @scroll="onScroll()">
            <template x-for="(group, gi) in groupedMessages" :key="gi + '-' + group.messages.length">
                <div>
                    <div x-show="group.dateLabel" class="flex items-center justify-center my-4">
                        <span class="text-[11px] uppercase tracking-wide text-gray-500 bg-[#161414] px-3 py-1 rounded-full"
                            x-text="group.dateLabel"></span>
                    </div>

                    <div class="flex gap-2 group" :class="group.mine ? 'flex-row-reverse' : 'flex-row'">
                        <div class="flex flex-col max-w-[75%]" :class="group.mine ? 'items-end' : 'items-start'">
                            <template x-for="(msg, mi) in group.messages" :key="msg.id">
                                <div class="relative w-full flex flex-col mb-1"
                                    :class="group.mine ? 'items-end' : 'items-start'">

                                    <div class="px-3.5 py-2 text-sm leading-snug relative group/msg flex flex-col gap-1 max-w-full"
                                        :class="[
                                            group.mine ? 'bg-indigo-600 text-white' : 'bg-[#2a2727] text-gray-100',
                                            bubbleRadius(group, mi, msg.reply_to),
                                            msg.highlight ? 'ring-2 ring-yellow-400' : ''
                                        ]">

                                        {{-- Professional Reply Context Box (INSIDE the bubble) --}}
                                        <template x-if="msg.reply_to">
                                            <div class="rounded flex flex-col px-2.5 py-1.5 text-[11px] w-full border-l-[3px] bg-black/10 overflow-hidden"
                                                :class="group.mine ? 'border-white/50 text-indigo-100' :
                                                    'border-indigo-500 text-gray-300'">
                                                <span class="font-bold mb-0.5"
                                                    :class="group.mine ? 'text-white' : 'text-indigo-400'"
                                                    x-text="msg.reply_to.sender_name"></span>
                                                <span class="truncate opacity-90 line-clamp-2"
                                                    x-text="msg.reply_to.body || '📷 Image'"></span>
                                            </div>
                                        </template>

                                        {{-- Image Support --}}
                                        <template x-if="msg.image_url">
                                            <img :src="msg.image_url"
                                                class="max-w-full h-auto rounded-md mt-0.5 border border-black/20"
                                                style="max-height: 200px;">
                                        </template>

                                        <span x-text="msg.body" class="break-words mt-0.5"></span>

                                        {{-- Quick Reply Action (Hover) --}}
                                        <button @click="setReply(msg)"
                                            class="absolute top-1/2 -translate-y-1/2 opacity-0 group-hover/msg:opacity-100 p-1.5 bg-[#161414] rounded-full border border-[#3a3a3d] text-gray-400 hover:text-white transition"
                                            :class="group.mine ? '-left-10' : '-right-10'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 11l9-9v6c10 0 12 12 12 12-3-4-8-4-12-4v6l-9-9z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="flex items-center gap-1 mt-0.5 px-1">
                                <span class="text-[10px] text-gray-500"
                                    x-text="formatTime(group.messages[group.messages.length - 1].created_at)"></span>
                                <template x-if="group.mine">
                                    <span x-html="readTicks(group.messages[group.messages.length - 1])"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="isTyping" class="flex gap-2 p-2">
                <div class="bg-[#2a2727] rounded-full px-3 py-2 text-xs text-gray-400 flex gap-1 items-center">
                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="border-t border-[#3a3a3d] bgforall shrink-0 p-3 rounded-b-2xl">

            {{-- Replying Context Indicator --}}
            <div x-show="replyTo" style="display:none;"
                class="flex items-center justify-between bg-[#161414] border border-[#3a3a3d] rounded-lg px-3 py-2 mb-2">
                <div class="text-xs text-gray-400 truncate flex-1">
                    <span class="text-indigo-400 font-semibold text-[11px] block">Replying to</span>
                    <span x-text="replyTo?.body || 'Image'" class="truncate"></span>
                </div>
                <button @click="replyTo = null" class="text-gray-500 hover:text-white p-1 shrink-0"><svg
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg></button>
            </div>

            {{-- Image Preview --}}
            <div x-show="imagePreview" style="display:none;" class="relative inline-block mb-2">
                <img :src="imagePreview" class="h-16 w-16 object-cover rounded-lg border border-[#3a3a3d]">
                <button @click="clearImage()"
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-0.5"><svg
                        xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg></button>
            </div>

            <form @submit.prevent="send()" class="flex items-end gap-2">
                {{-- Hidden File Input --}}
                <input type="file" x-ref="fileInput" @change="handleFileSelected" accept="image/*" class="hidden">
                <button type="button" @click="$refs.fileInput.click()"
                    class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full bg-[#2a2727] hover:bg-[#3a3a3d] text-gray-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                </button>

                <textarea x-model="draft" @input="handleTyping" @keydown.enter.prevent="send()" rows="1"
                    placeholder="Type a message..."
                    class="flex-1 resize-none rounded-2xl px-4 py-2.5 bg-[#161414] border border-[#3a3a3d] text-white text-sm focus:outline-none focus:border-indigo-500 hide-scroll"></textarea>

                <button type="submit" :disabled="sending || (!draft.trim() && !selectedFile)"
                    class="w-10 h-10 shrink-0 rounded-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center transition">
                    <svg x-show="!sending" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 3 3 9-3 9 19-9Z" />
                        <path d="M6 12h16" />
                    </svg>
                    <svg x-show="sending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        function messageThread(conversationId, userId, otherUserId) {
            return {
                conversationId,
                userId,
                otherUserId,
                draft: '',
                sending: false,
                messages: @json($initialMessages),
                searchQuery: '',
                isOnline: false,
                isTyping: false,
                typingTimer: null,
                replyTo: null,
                selectedFile: null,
                imagePreview: null,
                needsReadMark: false,

                initThread() {
                    this.markAsReadVisible();
                    this.checkPresence();

                    const connectToEcho = () => {
                        if (typeof window.Echo !== 'undefined') {

                            window.Echo.private(`conversation.${this.conversationId}`)
                                .listen('.message.sent', (e) => {
                                    // FIX 1: Strict check to prevent duplicate Echo messages crashing the UI
                                    if (!this.messages.some(m => m.id == e.id)) {
                                        // FIX 2: Reassign array instead of .push()
                                        this.messages = [...this.messages, e];

                                        this.isTyping = false;
                                        this.$nextTick(() => {
                                            this.scroll();
                                            if (document.hasFocus()) this.triggerMarkAsRead();
                                            else this.needsReadMark = true;
                                        });
                                    }
                                })
                                .listen('.messages.read', (e) => {
                                    if (e.reader_id == this.userId) return;

                                    // FIX 3: Reassign array to ensure read receipts trigger a UI update instantly
                                    this.messages = this.messages.map(m => {
                                        if (m.sender_id == this.userId && !m.read_at) {
                                            return {
                                                ...m,
                                                read_at: e.read_at
                                            };
                                        }
                                        return m;
                                    });
                                })
                                .listenForWhisper('typing', (e) => {
                                    this.isTyping = e.typing;
                                    if (this.typingTimer) clearTimeout(this.typingTimer);
                                    this.typingTimer = setTimeout(() => {
                                        this.isTyping = false;
                                    }, 2000);
                                });
                        } else {
                            setTimeout(connectToEcho, 100);
                        }
                    };

                    connectToEcho();
                    this.$nextTick(() => this.scroll());
                },

                checkPresence() {
                    if (typeof window.onlineUsers !== 'undefined') {
                        this.isOnline = window.onlineUsers.some(u => u.id == this.otherUserId);
                    }
                },
                handleTyping() {
                    if (typeof window.Echo !== 'undefined') {
                        window.Echo.private(`conversation.${this.conversationId}`).whisper('typing', {
                            typing: this.draft.length > 0
                        });
                    }
                },

                handleFileSelected(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.selectedFile = file;
                    this.imagePreview = URL.createObjectURL(file);
                },

                clearImage() {
                    this.selectedFile = null;
                    this.imagePreview = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                },

                setReply(msg) {
                    this.replyTo = msg;
                },

                triggerMarkAsRead() {
                    fetch(`/messages/${this.conversationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    this.needsReadMark = false;
                },

                markAsReadVisible() {
                    const hasUnread = this.messages.some(m => m.sender_id !== this.userId && !m.read_at);
                    if (hasUnread || this.needsReadMark) {
                        this.triggerMarkAsRead();
                    }
                },

                async send() {
                    if ((!this.draft.trim() && !this.selectedFile) || this.sending) return;
                    this.sending = true;

                    const formData = new FormData();
                    if (this.draft.trim()) formData.append('body', this.draft.trim());
                    if (this.selectedFile) formData.append('image', this.selectedFile);
                    if (this.replyTo) formData.append('reply_to_id', this.replyTo.id);

                    this.draft = '';
                    if (typeof window.Echo !== 'undefined') {
                        window.Echo.private(`conversation.${this.conversationId}`).whisper('typing', {
                            typing: false
                        });
                    }

                    try {
                        const res = await fetch(`/messages/${this.conversationId}/reply`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        if (res.ok) {
                            const msg = await res.json();

                            // FIX 4: Ensure the API response isn't already caught by Echo, then reassign
                            if (!this.messages.some(m => m.id == msg.id)) {
                                this.messages = [...this.messages, msg];
                            }

                            this.clearImage();
                            this.replyTo = null;
                            this.$nextTick(() => this.scroll());
                        }
                    } catch (err) {
                        alert('Failed to send message.');
                    } finally {
                        this.sending = false;
                    }
                },

                get groupedMessages() {
                    let filtered = this.messages;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = this.messages.filter(m => m.body && m.body.toLowerCase().includes(q));
                        filtered.forEach(m => m.highlight = true);
                    } else {
                        this.messages.forEach(m => m.highlight = false);
                    }

                    const groups = [];
                    let lastSender = null;
                    let lastDateKey = null;

                    for (const msg of filtered) {
                        const dateKey = new Date(msg.created_at).toDateString();
                        const isNewDay = dateKey !== lastDateKey;

                        const isNewSender = msg.sender_id != lastSender || isNewDay || msg.reply_to;

                        if (isNewSender) {
                            groups.push({
                                mine: msg.sender_id == this.userId,
                                dateLabel: isNewDay ? this.formatDate(msg.created_at) : null,
                                messages: [msg],
                            });
                        } else {
                            groups[groups.length - 1].messages.push(msg);
                        }
                        lastSender = msg.sender_id;
                        lastDateKey = dateKey;
                    }
                    return groups;
                },
                bubbleRadius(group, index, hasReply) {
                    if (hasReply) return 'rounded-2xl';
                    const isFirst = index === 0;
                    const isLast = index === group.messages.length - 1;
                    const side = group.mine ? 'r' : 'l';
                    let classes = 'rounded-2xl';
                    if (!isFirst) classes += ` rounded-t${side}-md`;
                    if (!isLast) classes += ` rounded-b${side}-md`;
                    return classes;
                },

                readTicks(msg) {
                    if (!msg.id) return '';
                    return msg.read_at ?
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 6 7 17 3 13"></polyline><polyline points="22 10 11 21 8 18"></polyline></svg>' :
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 6 7 17 3 13"></polyline><polyline points="22 10 11 21 8 18"></polyline></svg>';
                },

                formatTime(iso) {
                    return new Date(iso).toLocaleTimeString([], {
                        hour: 'numeric',
                        minute: '2-digit'
                    });
                },
                formatDate(iso) {
                    return new Date(iso).toLocaleDateString([], {
                        month: 'short',
                        day: 'numeric'
                    });
                },
                scroll() {
                    if (this.$refs.scrollBox && !this.searchQuery) this.$refs.scrollBox.scrollTop = this.$refs.scrollBox
                        .scrollHeight;
                }
            };
        }
    </script>
@endsection
