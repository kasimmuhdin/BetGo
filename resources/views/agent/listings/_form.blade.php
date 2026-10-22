@csrf
@if ($listing->exists)
    @method('PUT')
@endif
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .card {
        background-color: #1e1b1b;
        border-radius: 0.75rem;
        border: 1px solid #3a3a3d;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.5s ease-in;
    }

    .card:hover {
        transform: translateY(-4px);
    }

    .bgforall {
        background-color: #1e1b1b;
        border: 1px solid #3a3a3d;
        color: #ffffff;
    }

    /* Add this inside your existing <style> block */
    .leaflet-tile-pane {
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }

    /* Fix input fields background and text globally inside the form */
    input[type="text"],
    input[type="number"],
    select,
    textarea {
        background-color: #161414 !important;
        border-color: #3a3a3d !important;
        color: #ffffff !important;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus,
    textarea:focus {
        border-color: #6366f1 !important;
        ring-color: #6366f1 !important;
    }

    table tbody tr:hover {
        background-color: #2a2727;
    }
</style>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-white">

    {{-- ══════════ Left column ══════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Information --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-white text-lg">Basic Information</h3>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-200 mb-1">Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $listing->title) }}"
                    class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                @error('title')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-200 mb-1">Description</label>
                <textarea id="description" name="description" rows="5"
                    class="w-full rounded-lg px-3 py-2 border focus:outline-none placeholder:text-gray-500">{{ old('description', $listing->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="property_type" class="block text-sm font-medium text-gray-200 mb-1">Property Type
                        *</label>
                    <select id="property_type" name="property_type"
                        class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                        @foreach (['apartment' => 'Apartment', 'house' => 'House', 'villa' => 'Villa', 'office' => 'Office', 'land' => 'Land', 'commercial' => 'Commercial', 'warehouse' => 'Warehouse'] as $value => $label)
                            <option value="{{ $value }}" class="bg-[#1e1b1b] text-white"
                                @selected(old('property_type', $listing->property_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('property_type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="listing_type" class="block text-sm font-medium text-gray-200 mb-1">Listing Type
                        *</label>
                    <select id="listing_type" name="listing_type"
                        class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                        <option value="rent" class="bg-[#1e1b1b] text-white" @selected(old('listing_type', $listing->listing_type) === 'rent')>For Rent
                        </option>
                        <option value="sale" class="bg-[#1e1b1b] text-white" @selected(old('listing_type', $listing->listing_type) === 'sale')>For Sale
                        </option>
                    </select>
                    @error('listing_type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Photos + Media Manager --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-4" x-data="photoPicker(@js($listing->exists ? $listing->media->map(fn($m) => ['id' => $m->id, 'url' => $m->url, 'cover' => (bool) $m->pivot->is_cover])->values() : []))">

            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-white text-lg">Photos</h3>
                <button type="button" @click="openSheet()"
                    class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    + Manage Photos
                </button>
            </div>

            {{-- Photos attached to THIS listing --}}
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" x-show="photos.length">
                <template x-for="(photo, i) in photos" :key="photo.id">
                    <div class="relative group">
                        <img :src="photo.url" class="h-24 w-full object-cover rounded-lg border-2"
                            :class="photo.cover ? 'border-amber-500' : 'border-[#3a3a3d]'" alt="">

                        <span x-show="photo.cover"
                            class="absolute top-1 left-1 text-[10px] bg-amber-500 text-black font-semibold px-1.5 py-0.5 rounded flex items-center gap-1">
                            ★ Cover
                        </span>
                        <button type="button" @click="setCover(photo.id)" title="Set as cover photo"
                            x-show="!photo.cover"
                            class="absolute top-1 left-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition hover:bg-amber-600">
                            Set cover
                        </button>

                        <button type="button" @click="detach(photo.id)" title="Remove from listing"
                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/70 text-white text-xs leading-none hover:bg-red-600 transition flex items-center justify-center">&times;</button>

                        <input type="hidden" name="media_ids[]" :value="photo.id">
                    </div>
                </template>
            </div>
            {{-- single source of truth for status photo is the cover --}}
            <input type="hidden" name="cover_photo_id" :value="photos.find(p => p.cover)?.id ?? ''">

            <p class="text-xs text-gray-400" x-show="!photos.length">No photos yet — click "Manage Photos" to pick
                from your media manager.</p>
            @error('media_ids.*')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
            @error('cover_photo_id')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror

            {{-- Bottom sheet: Media Manager (the FULL library) --}}
            <div x-show="open" x-cloak x-transition.opacity.duration.300ms class="fixed inset-0 z-50 bg-black/60"
                style="display:none" @click="closeSheet()">

                <div class="absolute inset-x-0 bottom-0 h-[90vh] sm:h-4/5 bg-[#1e1b1b] border-t border-[#3a3a3d] text-white rounded-t-2xl shadow-2xl flex flex-col overflow-hidden"
                    x-show="open" x-transition:enter="transition-transform duration-300 ease-out"
                    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                    x-transition:leave="transition-transform duration-300 ease-in"
                    x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" @click.stop>

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3 border-b border-[#3a3a3d] shrink-0">
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold text-white">Media Manager</h3>
                            <span class="text-xs text-gray-400 hidden sm:inline">(Max 5 photos at once, up to 20MB
                                each)</span>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <label x-show="!uploading"
                                class="px-3 py-1.5 rounded-lg border border-[#3a3a3d] text-xs sm:text-sm cursor-pointer hover:bg-[#2a2727] transition">
                                Upload new
                                {{-- Added 'multiple' attribute here --}}
                                <input type="file" class="hidden" accept="image/*" multiple
                                    @change="upload($event)">
                            </label>
                            <template x-if="uploading">
                                <div class="flex items-center gap-2 min-w-[8rem] sm:min-w-[11rem]">
                                    <div class="flex-1 h-1.5 bg-[#3a3a3d] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-indigo-500 transition-[width] duration-200 ease-out"
                                            :class="{ 'animate-pulse': processing }"
                                            :style="`width: ${uploadProgress}%`"></div>
                                    </div>
                                    <span class="text-[11px] text-gray-400 tabular-nums w-8 text-right"
                                        x-text="uploadProgress + '%'"></span>
                                </div>
                            </template>
                            <button type="button" @click="closeSheet()"
                                class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
                        </div>
                    </div>

                    {{-- Mobile tab switcher (hidden on desktop, where both columns sit side by side) --}}
                    <div class="flex md:hidden border-b border-[#3a3a3d] shrink-0">
                        <button type="button" @click="activeTab = 'cover'"
                            class="flex-1 py-2.5 text-sm font-medium flex items-center justify-center gap-1.5 border-b-2 transition"
                            :class="activeTab === 'cover'
                                ?
                                'border-amber-500 text-amber-400 bg-amber-500/[0.06]' :
                                'border-transparent text-gray-400'">
                            <span
                                class="flex items-center justify-center w-4 h-4 rounded-full bg-amber-500 text-black text-[10px] font-bold shrink-0">1</span>
                            Cover
                            <span x-show="coverPick || photos.some(p => p.cover)" class="text-amber-400">★</span>
                        </button>
                        <button type="button" @click="activeTab = 'gallery'"
                            class="flex-1 py-2.5 text-sm font-medium flex items-center justify-center gap-1.5 border-b-2 transition"
                            :class="activeTab === 'gallery'
                                ?
                                'border-indigo-500 text-indigo-400 bg-indigo-500/[0.06]' :
                                'border-transparent text-gray-400'">
                            <span
                                class="flex items-center justify-center w-4 h-4 rounded-full bg-indigo-600 text-white text-[10px] font-bold shrink-0">2</span>
                            Additional
                            <span x-show="galleryPicks.length"
                                class="inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-indigo-600 text-white text-[10px] font-semibold"
                                x-text="galleryPicks.length"></span>
                        </button>
                    </div>

                    {{-- Body: two independently-scrollable panels --}}
                    <div class="flex-1 flex flex-col md:flex-row md:divide-x md:divide-[#3a3a3d] overflow-hidden">

                        {{-- Column 1: Cover photo (single-select) --}}
                        {{-- Column 1: Cover photo (single-select) --}}
                        <div class="flex-1 min-h-0 flex-col bg-amber-500/[0.04]" x-show="activeTab === 'cover'"
                            x-cloak :class="{ 'md:!flex': true }">

                            <div class="hidden md:block px-4 sm:px-5 pt-4 pb-3 border-b border-amber-500/20 shrink-0">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-black text-xs font-bold shrink-0">1</span>
                                    <h4 class="font-medium text-white text-sm">Choose cover photo</h4>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">The main photo shown on the listing card — pick
                                    exactly one.</p>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5">
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                                    <template x-for="item in library" :key="'cover-' + item.id">
                                        {{-- Added 'group relative' here so the delete button shows on hover --}}
                                        <div class="relative aspect-square group">
                                            <button type="button" @click="pickCover(item)"
                                                class="w-full h-full rounded-lg overflow-hidden border-2 transition block focus:outline-none"
                                                :class="(coverPick?.id === item.id || isCurrentCover(item.id)) ?
                                                'border-amber-500 ring-2 ring-amber-500 ring-offset-2 ring-offset-[#1e1b1b]' :
                                                'border-[#3a3a3d] hover:border-amber-500/50 opacity-90 hover:opacity-100'">
                                                <img :src="item.url" :alt="item.name" loading="lazy"
                                                    class="w-full h-full object-cover">
                                                <span x-show="coverPick?.id === item.id || isCurrentCover(item.id)"
                                                    class="absolute inset-0 bg-amber-500/15 flex items-start justify-end p-1">
                                                    <span
                                                        class="w-6 h-6 rounded-full bg-amber-500 text-black text-sm font-bold flex items-center justify-center shadow">★</span>
                                                </span>
                                            </button>

                                            {{-- NEW: Delete button for the cover picker grid --}}
                                            <button type="button" @click.stop="deleteFromLibrary(item)"
                                                title="Delete permanently from your library"
                                                class="absolute bottom-1 right-1 w-6 h-6 rounded bg-red-600/90 text-white text-[11px] leading-none opacity-0 group-hover:opacity-100 transition flex items-center justify-center shadow">🗑</button>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="loading" class="text-center text-gray-400 py-10">Loading…</div>
                                <p x-show="!library.length && !loading" class="text-center text-gray-400 py-10">
                                    Your library is empty — upload your first photo.</p>
                            </div>
                        </div>

                        {{-- Column 2: Additional photos (multi-select) --}}
                        <div class="flex-1 min-h-0 flex-col bg-indigo-500/[0.04]" x-show="activeTab === 'gallery'"
                            x-cloak :class="{ 'md:!flex': true }">

                            <div class="hidden md:block px-4 sm:px-5 pt-4 pb-3 border-b border-indigo-500/20 shrink-0">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold shrink-0">2</span>
                                    <h4 class="font-medium text-white text-sm">Add more photos</h4>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Optional — pick any other photos for the
                                    gallery.</p>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5">
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                                    <template x-for="item in library" :key="'gallery-' + item.id">
                                        <div class="relative aspect-square group"
                                            :class="(coverPick?.id === item.id || isCurrentCover(item.id)) ?
                                            'opacity-25 pointer-events-none' : ''">
                                            <button type="button" @click="toggleGallery(item)"
                                                class="w-full h-full rounded-lg overflow-hidden border-2 transition block"
                                                :class="(isGalleryPicked(item.id) || (isAdded(item.id) && !isCurrentCover(item
                                                    .id))) ?
                                                'border-indigo-500 ring-2 ring-indigo-500 ring-offset-2 ring-offset-[#1e1b1b]' :
                                                'border-[#3a3a3d] hover:border-indigo-500/50 opacity-90 hover:opacity-100'">
                                                <img :src="item.url" :alt="item.name" loading="lazy"
                                                    class="w-full h-full object-cover">
                                            </button>
                                            <span
                                                x-show="isGalleryPicked(item.id) || (isAdded(item.id) && !isCurrentCover(item.id))"
                                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-indigo-600 text-white text-sm flex items-center justify-center shadow pointer-events-none">&#10003;</span>
                                            <button type="button" @click.stop="deleteFromLibrary(item)"
                                                title="Delete permanently from your library"
                                                class="absolute bottom-1 right-1 w-6 h-6 rounded bg-red-600/90 text-white text-[11px] leading-none opacity-0 group-hover:opacity-100 transition flex items-center justify-center">🗑</button>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="loading" class="text-center text-gray-400 py-10">Loading…</div>
                                <p x-show="!library.length && !loading" class="text-center text-gray-400 py-10">
                                    Your library is empty — upload your first photo.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="border-t border-[#3a3a3d] px-4 sm:px-5 py-3 flex items-center justify-between bg-[#1e1b1b] shrink-0">
                        <span class="text-xs sm:text-sm text-gray-400 flex items-center gap-2 flex-wrap">
                            <span x-show="coverPick" class="inline-flex items-center gap-1 text-amber-400">★ Cover
                                selected</span>
                            <span x-show="galleryPicks.length" class="inline-flex items-center gap-1 text-indigo-400">
                                <span x-text="galleryPicks.length"></span> additional selected
                            </span>
                            <span x-show="!coverPick && !galleryPicks.length">Nothing selected yet</span>
                        </span>
                        <button type="button" @click="confirmSelection()" :disabled="!pendingCount"
                            class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-45 disabled:cursor-not-allowed shrink-0">
                            Add to listing
                        </button>
                    </div>
                </div>
            </div>


        </div>
        {{-- Location (Moved to Left Column for wider layout) --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-white text-lg">Location Information</h3>

            {{-- Make inputs horizontal: 4 columns on desktop --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach (['city' => 'City *', 'sub_city' => 'Sub-city', 'woreda' => 'Woreda', 'neighborhood' => 'Neighborhood'] as $field => $label)
                    <div>
                        <label for="{{ $field }}"
                            class="block text-sm font-medium text-gray-200 mb-1">{{ $label }}</label>
                        <input type="text" id="{{ $field }}" name="{{ $field }}"
                            value="{{ old($field, $listing->$field) }}"
                            class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                        @error($field)
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-200 mb-1">Specific Address</label>
                <textarea id="address" name="address" rows="2"
                    class="w-full rounded-lg px-3 py-2 border focus:outline-none placeholder:text-gray-500"
                    placeholder="e.g. Near Bole Medhanialem, next to...">{{ old('address', $listing->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-[#3a3a3d]">
                <label class="block text-sm font-medium text-gray-200 mb-1">Pin on Map</label>
                <p class="text-xs text-gray-400 mb-3">Search an address, use your current location, or click/drag the
                    marker
                    to set the exact spot.</p>

                <div class="flex flex-col sm:flex-row gap-2 mb-3">
                    <div class="relative flex-1">
                        <input type="text" id="map-search"
                            placeholder="Search an address or area, e.g. Bole, Addis Ababa"
                            class="w-full rounded-lg px-3 py-2 border focus:outline-none" autocomplete="off">
                        <div id="map-search-results"
                            class="absolute z-20 mt-1 w-full bg-[#1e1b1b] border border-[#3a3a3d] rounded-lg shadow-lg max-h-56 overflow-y-auto hidden">
                        </div>
                    </div>
                    <button type="button" id="map-locate-btn"
                        class="px-3 py-2 rounded-lg border border-[#3a3a3d] text-sm text-gray-200 hover:bg-[#2a2727] transition flex items-center gap-2 justify-center whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M12 2v3m0 14v3m10-10h-3M5 12H2" />
                        </svg>
                        <span id="map-locate-label">Use my location</span>
                    </button>
                </div>

                <div id="map" class="w-full h-[400px] rounded-lg border border-[#3a3a3d] z-10 shadow-inner">
                </div>

                <div class="mt-2 flex items-start justify-between gap-3 text-xs text-gray-400">
                    <span id="map-coords" class="flex-1">No location selected yet</span>
                    <button type="button" id="map-sync-btn"
                        class="text-indigo-400 hover:text-indigo-300 whitespace-nowrap underline-offset-2 hover:underline">
                        Fill address fields from map
                    </button>
                </div>

                <input type="hidden" id="latitude" name="latitude"
                    value="{{ old('latitude', $listing->latitude) }}">
                <input type="hidden" id="longitude" name="longitude"
                    value="{{ old('longitude', $listing->longitude) }}">
            </div>
        </div>
    </div>

    {{-- ══════════ Right column ══════════ --}}
    <div class="space-y-6">

        {{-- Price --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-white text-lg">Price</h3>
            <div>
                <label for="price" class="block text-sm font-medium text-gray-200 mb-1">Price (ETB) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0"
                    value="{{ old('price', $listing->price) }}"
                    class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                @error('price')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-200 cursor-pointer">
                <input type="hidden" name="price_negotiable" value="0">
                <input type="checkbox" name="price_negotiable" value="1" @checked(old('price_negotiable', $listing->price_negotiable))
                    class="rounded border-[#3a3a3d] text-indigo-600 bg-[#161414] focus:ring-indigo-500">
                Price negotiable
            </label>
        </div>

        {{-- Specifications --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-white text-lg">Specifications</h3>
            <div class="grid grid-cols-3 gap-3">
                @foreach (['bedrooms' => 'Beds', 'bathrooms' => 'Baths', 'parking_spaces' => 'Parking'] as $field => $label)
                    <div>
                        <label for="{{ $field }}"
                            class="block text-sm font-medium text-gray-200 mb-1">{{ $label }}</label>
                        <input type="number" id="{{ $field }}" name="{{ $field }}" min="0"
                            value="{{ old($field, $listing->$field) }}"
                            class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                        @error($field)
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="area" class="block text-sm font-medium text-gray-200 mb-1">Area</label>
                    <input type="number" id="area" name="area" step="0.01" min="0"
                        value="{{ old('area', $listing->area) }}"
                        class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                    @error('area')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="furnishing_status"
                        class="block text-sm font-medium text-gray-200 mb-1">Furnishing</label>
                    <select id="furnishing_status" name="furnishing_status"
                        class="w-full rounded-lg px-3 py-2 border focus:outline-none">
                        <option value="" class="bg-[#1e1b1b] text-white">—</option>
                        @foreach (['unfurnished' => 'Unfurnished', 'semi_furnished' => 'Semi-furnished', 'furnished' => 'Furnished'] as $value => $label)
                            <option value="{{ $value }}" class="bg-[#1e1b1b] text-white"
                                @selected(old('furnishing_status', $listing->furnishing_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('furnishing_status')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>



        {{-- Actions --}}
        <div class="bgforall rounded-xl shadow-sm p-6 space-y-3">
            <button type="submit" name="status" value="draft" @click="status = 'draft'" :disabled="submitting"
                class="w-full py-2.5 rounded-lg border border-[#3a3a3d] text-gray-200 hover:bg-[#2a2727] font-medium transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg x-show="submitting && status === 'draft'" class="w-4 h-4 animate-spin" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-show="!(submitting && status === 'draft')">Save as Draft</span>
                <span x-show="submitting && status === 'draft'">Saving…</span>
            </button>

            <button type="submit" name="status" value="published" @click="status = 'published'"
                :disabled="submitting"
                class="w-full py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg x-show="submitting && status === 'published'" class="w-4 h-4 animate-spin" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span
                    x-show="!(submitting && status === 'published')">{{ $listing->exists ? 'Save & Publish' : 'Publish Listing' }}</span>
                <span x-show="submitting && status === 'published'">Publishing…</span>
            </button>
        </div>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hasInitialLocation = {{ old('latitude', $listing->latitude) ? 'true' : 'false' }};
        const defaultLat = {{ old('latitude', $listing->latitude ?? '9.0054') }};
        const defaultLng = {{ old('longitude', $listing->longitude ?? '38.7636') }};
        const zoomLevel = hasInitialLocation ? 16 : 12;

        const map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const coordsLabel = document.getElementById('map-coords');
        const searchInput = document.getElementById('map-search');
        const searchResults = document.getElementById('map-search-results');
        const locateBtn = document.getElementById('map-locate-btn');
        const locateLabel = document.getElementById('map-locate-label');
        const syncBtn = document.getElementById('map-sync-btn');

        let lastGeocode = null; // cached address data for the "fill fields" button

        function setSelected(lat, lng) {
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
        }

        function moveMarker(lat, lng) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], Math.max(map.getZoom(), 15));
            setSelected(lat, lng);
            reverseGeocode(lat, lng);
        }

        // ---- Reverse geocoding: pin position -> human-readable address ----
        let reverseTimer = null;

        function reverseGeocode(lat, lng) {
            clearTimeout(reverseTimer);
            coordsLabel.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)} — looking up address…`;
            reverseTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`
                    );
                    const data = await res.json();
                    lastGeocode = data;
                    coordsLabel.textContent = data.display_name ?
                        `${lat.toFixed(6)}, ${lng.toFixed(6)} — ${data.display_name}` :
                        `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                } catch (e) {
                    coordsLabel.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            }, 500); // debounce — avoid hammering Nominatim while the user drags
        }

        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            setSelected(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });

        map.on('click', (e) => {
            marker.setLatLng(e.latlng);
            setSelected(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        if (hasInitialLocation) {
            setSelected(defaultLat, defaultLng);
            coordsLabel.textContent = `${defaultLat}, ${defaultLng}`;
        } else {
            coordsLabel.textContent =
                'Marker defaults to Addis Ababa center — search, use your location, or drag it to the exact spot.';
        }

        // ---- Fill City/Sub-city/Woreda/Neighborhood/Address from the geocode result ----
        syncBtn.addEventListener('click', () => {
            if (!lastGeocode || !lastGeocode.address) {
                alert('Move the pin first so there is an address to pull from.');
                return;
            }
            const a = lastGeocode.address;
            const fill = (id, value) => {
                const el = document.getElementById(id);
                if (el && value) el.value = value; // explicit click = OK to overwrite
            };
            fill('city', a.city || a.town || a.county);
            fill('sub_city', a.city_district || a.suburb);
            fill('woreda', a.neighbourhood);
            fill('neighborhood', a.suburb || a.neighbourhood);
            fill('address', [a.road, a.house_number].filter(Boolean).join(' '));
        });

        // ---- Forward geocoding: search box ----
        let searchTimer = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = searchInput.value.trim();
            if (q.length < 3) {
                searchResults.classList.add('hidden');
                return;
            }
            searchTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=6&countrycodes=et&q=${encodeURIComponent(q)}`
                    );
                    renderSearchResults(await res.json());
                } catch (e) {
                    searchResults.classList.add('hidden');
                }
            }, 400);
        });

        function renderSearchResults(results) {
            if (!results.length) {
                searchResults.innerHTML = `<div class="px-3 py-2 text-sm text-gray-400">No matches found</div>`;
                searchResults.classList.remove('hidden');
                return;
            }
            searchResults.innerHTML = results.map((r, i) => `
            <button type="button" data-index="${i}"
                class="w-full text-left px-3 py-2 text-sm text-gray-200 hover:bg-[#2a2727] transition border-b border-[#3a3a3d] last:border-0">
                ${r.display_name}
            </button>
        `).join('');
            searchResults.classList.remove('hidden');

            searchResults.querySelectorAll('button').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const r = results[btn.dataset.index];
                    moveMarker(parseFloat(r.lat), parseFloat(r.lon));
                    lastGeocode = {
                        display_name: r.display_name,
                        address: r.address
                    };
                    searchInput.value = r.display_name;
                    searchResults.classList.add('hidden');
                });
            });
        }

        document.addEventListener('click', (e) => {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.classList.add('hidden');
            }
        });

        // ---- "Use my location" ----
        locateBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            locateLabel.textContent = 'Locating…';
            locateBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    moveMarker(pos.coords.latitude, pos.coords.longitude);
                    locateLabel.textContent = 'Use my location';
                    locateBtn.disabled = false;
                },
                () => {
                    alert(
                        'Could not get your location. Please allow location access or set the pin manually.'
                        );
                    locateLabel.textContent = 'Use my location';
                    locateBtn.disabled = false;
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        });

        setTimeout(() => map.invalidateSize(), 500);

        // ---- Guard: don't allow submit without a pinned location ----
        const form = document.getElementById('map').closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!latInput.value || !lngInput.value) {
                    e.preventDefault();
                    alert('Please set the property location on the map before submitting.');
                    document.getElementById('map').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
        }
    });
</script>
{{-- ══════════ Media manager logic ══════════ --}}
<script>
    function photoPicker(initial) {
        return {
            photos: initial ?? [],
            library: [],
            coverPick: null,
            galleryPicks: [],
            open: false,
            loading: false,
            activeTab: 'cover',
            uploading: false,
            uploadProgress: 0,
            processing: false,

            init() {
                if (this.photos.length && !this.hasCover()) {
                    this.photos[0].cover = true;
                }
            },

            hasCover() {
                return this.photos.some(p => p.cover);
            },
            isAdded(id) {
                return this.photos.some(p => p.id === id);
            },
            isCurrentCover(id) {
                const p = this.photos.find(p => p.id === id);
                return !!p?.cover;
            },
            isGalleryPicked(id) {
                return this.galleryPicks.some(s => s.id === id);
            },
            get pendingCount() {
                return (this.coverPick ? 1 : 0) + this.galleryPicks.length;
            },

            async openSheet() {
                this.open = true;
                this.loading = true;
                this.coverPick = null;
                this.galleryPicks = [];
                this.activeTab = 'cover';
                const res = await fetch("{{ route('agent.media.index') }}");
                const data = await res.json();
                this.library = data.data;
                this.loading = false;
            },

            closeSheet() {
                this.open = false;
                this.coverPick = null;
                this.galleryPicks = [];
                this.activeTab = 'cover';
            },

            pickCover(item) {
                this.coverPick = (this.coverPick?.id === item.id) ? null : item;
                this.galleryPicks = this.galleryPicks.filter(s => s.id !== item.id);
            },

            toggleGallery(item) {
                if (this.coverPick?.id === item.id || this.isCurrentCover(item.id)) return;
                this.isGalleryPicked(item.id) ?
                    this.galleryPicks = this.galleryPicks.filter(s => s.id !== item.id) :
                    this.galleryPicks.push(item);
            },

            confirmSelection() {
                if (this.coverPick) {
                    const id = this.coverPick.id;
                    this.photos.forEach(p => p.cover = false);
                    const existing = this.photos.find(p => p.id === id);
                    existing
                        ?
                        existing.cover = true :
                        this.photos.push({
                            id,
                            url: this.coverPick.url,
                            cover: true
                        });
                }

                const existingIds = new Set(this.photos.map(p => p.id));
                this.galleryPicks
                    .filter(s => !existingIds.has(s.id))
                    .forEach(s => this.photos.push({
                        id: s.id,
                        url: s.url,
                        cover: false
                    }));

                if (this.photos.length && !this.hasCover()) {
                    this.photos[0].cover = true;
                }

                this.closeSheet();
            },

            setCover(id) {
                this.photos.forEach(p => p.cover = p.id === id);
            },

            detach(id) {
                const wasCover = this.photos.find(p => p.id === id)?.cover;
                this.photos = this.photos.filter(p => p.id !== id);
                if (wasCover && this.photos.length) this.photos[0].cover = true;
            },

            upload(e) {
                const files = Array.from(e.target.files);
                if (!files.length) return;

                if (files.length > 5) {
                    alert('You can only upload up to 5 photos at a time.');
                    e.target.value = '';
                    return;
                }

                // Enforce 20MB per photo limit on client side (20MB = 20 * 1024 * 1024 bytes)
                const maxSizeBytes = 20 * 1024 * 1024;
                for (let file of files) {
                    if (file.size > maxSizeBytes) {
                        alert(`File "${file.name}" exceeds the 20MB size limit.`);
                        e.target.value = '';
                        return;
                    }
                }

                const fd = new FormData();
                files.forEach(file => {
                    fd.append('photos[]', file);
                });

                this.uploading = true;
                this.uploadProgress = 0;
                this.processing = false;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('agent.media.store') }}");
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
                        this.processing = false;
                    }
                });

                xhr.onload = () => {
                    this.uploadProgress = 100;
                    this.processing = false;

                    setTimeout(() => {
                        this.uploading = false;
                        this.processing = false;
                        this.uploadProgress = 0;
                    }, 400);

                    if (xhr.status === 201) {
                        const items = JSON.parse(xhr.responseText);
                        if (Array.isArray(items)) {
                            this.library.unshift(...items);
                        } else {
                            this.library.unshift(items);
                        }
                    } else {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            alert(res.message || 'Server Error');
                        } catch (e) {
                            alert('Server error (' + xhr.status + '): ' + xhr.responseText.substring(0, 150));
                        }
                        this.uploading = false;
                        this.uploadProgress = 0;
                        this.processing = false;
                    }
                    e.target.value = '';
                };

                xhr.onerror = () => {
                    this.uploading = false;
                    this.uploadProgress = 0;
                    this.processing = false;
                    alert('Network error occurred while uploading.');
                    e.target.value = '';
                };

                xhr.send(fd);
            },

            async deleteFromLibrary(item) {
                if (!confirm('Delete this photo permanently? It will be removed from every listing using it.'))
                    return;

                await fetch("{{ url('agent/media') }}/" + item.id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                });

                this.library = this.library.filter(m => m.id !== item.id);
                this.galleryPicks = this.galleryPicks.filter(s => s.id !== item.id);
                if (this.coverPick?.id === item.id) this.coverPick = null;

                const wasCover = this.photos.find(p => p.id === item.id)?.cover;
                this.photos = this.photos.filter(p => p.id !== item.id);
                if (wasCover && this.photos.length) this.photos[0].cover = true;
            },
        };
    }
</script>
