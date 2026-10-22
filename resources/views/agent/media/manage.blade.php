@extends('layouts.app')
@section('title', 'Media Menager')
@section('content')
    <div class="max-w-6xl mx-auto text-white" x-data="mediaManager()" x-init="load()">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold">Media Library</h1>
                <p class="text-sm text-gray-400">All photos you've uploaded across every property.</p>
            </div>
            <label
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium cursor-pointer transition">
                Upload photos
                <input type="file" class="hidden" accept="image/*" multiple @change="upload($event)">
            </label>
        </div>

        <template x-if="uploading">
            <div class="mb-4 h-1.5 bg-[#3a3a3d] rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full transition-[width] duration-200"
                    :class="{ 'animate-pulse': processing }" :style="`width: ${uploadProgress}%`"></div>
            </div>
        </template>

        <div x-show="loading" class="text-center text-gray-400 py-16">Loading…</div>

        <div x-show="!loading && !library.length" class="text-center text-gray-400 py-16">
            No photos yet — upload your first one.
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            <template x-for="item in library" :key="item.id">
                <div class="relative aspect-square group">
                    <img :src="item.url" class="w-full h-full object-cover rounded-lg border border-[#3a3a3d]">

                    <span x-show="item.properties_count > 0"
                        class="absolute bottom-1 left-1 text-[10px] bg-black/70 text-gray-200 px-1.5 py-0.5 rounded"
                        x-text="`Used in ${item.properties_count}`"></span>
                    <span x-show="!item.properties_count"
                        class="absolute bottom-1 left-1 text-[10px] bg-black/70 text-gray-400 px-1.5 py-0.5 rounded">
                        Unused
                    </span>

                    <button type="button" @click="remove(item)"
                        class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/70 text-white text-xs opacity-0 group-hover:opacity-100 hover:bg-red-600 transition flex items-center justify-center">
                        &times;
                    </button>
                </div>
            </template>
        </div>

        <div x-show="nextPageUrl" class="text-center mt-6">
            <button type="button" @click="loadMore()" :disabled="loadingMore"
                class="px-4 py-2 rounded-lg border border-[#3a3a3d] text-sm text-gray-200 hover:bg-[#2a2727] transition disabled:opacity-50">
                <span x-show="!loadingMore">Load more</span>
                <span x-show="loadingMore">Loading…</span>
            </button>
        </div>
    </div>
    @php
        $role = auth()->user()->role;
    @endphp
    <script>
        function mediaManager() {
            return {
                library: [],
                nextPageUrl: null,
                loading: false,
                loadingMore: false,
                uploading: false,
                uploadProgress: 0,
                processing: false,

                async load() {
                    this.loading = true;
                    const res = await fetch("{{ route($role . '.media.index') }}");
                    const data = await res.json();
                    this.library = data.data;
                    this.nextPageUrl = data.next_page_url;
                    this.loading = false;
                },

                async loadMore() {
                    if (!this.nextPageUrl) return;
                    this.loadingMore = true;
                    const res = await fetch(this.nextPageUrl);
                    const data = await res.json();
                    this.library.push(...data.data);
                    this.nextPageUrl = data.next_page_url;
                    this.loadingMore = false;
                },

                upload(e) {
                    const files = Array.from(e.target.files);
                    if (!files.length) return;

                    if (files.length > 5) {
                        alert('You can only upload up to 5 photos at a time.');
                        e.target.value = '';
                        return;
                    }

                    const maxSizeBytes = 20 * 1024 * 1024;
                    for (const file of files) {
                        if (file.size > maxSizeBytes) {
                            alert(`File "${file.name}" exceeds the 20MB size limit.`);
                            e.target.value = '';
                            return;
                        }
                    }

                    const fd = new FormData();
                    files.forEach(f => fd.append('photos[]', f));

                    this.uploading = true;
                    this.uploadProgress = 0;
                    this.processing = false;

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "{{ route($role . '.media.store') }}");
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.addEventListener('progress', (evt) => {
                        if (!evt.lengthComputable) return;
                        const percent = Math.round((evt.loaded / evt.total) * 100);
                        if (percent >= 100) {
                            this.uploadProgress = 99;
                            this.processing = true;
                        } else {
                            this.uploadProgress = percent;
                        }
                    });

                    xhr.onload = () => {
                        this.uploadProgress = 100;
                        this.processing = false;
                        setTimeout(() => {
                            this.uploading = false;
                            this.uploadProgress = 0;
                        }, 400);

                        if (xhr.status === 201) {
                            const items = JSON.parse(xhr.responseText);
                            // new uploads have no properties_count yet — they're unused by definition
                            (Array.isArray(items) ? items : [items]).forEach(i => i.properties_count = 0);
                            this.library.unshift(...(Array.isArray(items) ? items : [items]));
                        } else {
                            try {
                                alert(JSON.parse(xhr.responseText).message || 'Server error');
                            } catch {
                                alert('Server error (' + xhr.status + ')');
                            }
                            this.uploading = false;
                        }
                        e.target.value = '';
                    };

                    xhr.onerror = () => {
                        this.uploading = false;
                        alert('Network error occurred while uploading.');
                        e.target.value = '';
                    };

                    xhr.send(fd);
                },

                async remove(item) {
                    const msg = item.properties_count > 0 ?
                        `This photo is used in ${item.properties_count} propert${item.properties_count > 1 ? 'ies' : 'y'}. Delete anyway?` :
                        'Delete this photo permanently?';
                    if (!confirm(msg)) return;
                    const deleteUrl = "{{ route($role . '.media.destroy', ['media' => '__ID__']) }}".replace('__ID__',
                        item.id);

                    await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                    });

                    this.library = this.library.filter(m => m.id !== item.id);
                },
            };
        }
    </script>
@endsection
