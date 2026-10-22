@extends('layouts.guest')

@section('content')
    <style>
        .surface {
            background-color: #1e1b1b;
            border: 1px solid #3a3a3d;
        }

        .surface-input {
            background-color: #161414 !important;
            border-color: #3a3a3d !important;
            color: #ffffff !important;
        }

        .surface-input:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }

        .surface-input::placeholder {
            color: #6b7280;
        }

        .listing-card-dark {
            background-color: #1e1b1b;
            border: 1px solid #3a3a3d;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .listing-card-dark:hover {
            transform: translateY(-4px);
            border-color: #6366f1;
        }

        .gallery-main {
            background-color: #161414;
        }

        .thumb {
            border: 2px solid transparent;
            transition: border-color 0.2s ease, opacity 0.2s ease;
            opacity: 0.6;
            cursor: pointer;
        }

        .thumb:hover {
            opacity: 1;
        }

        .thumb.active {
            border-color: #6366f1;
            opacity: 1;
        }

        .gallery-arrow {
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(6px);
            transition: background 0.2s ease;
        }

        .gallery-arrow:hover {
            background: rgba(99, 102, 241, 0.7);
        }

        .spinner {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-loading {
            opacity: 0.75;
            pointer-events: none;
        }

        /* Lightbox */
        #lightbox {
            background: rgba(0, 0, 0, 0.92);
        }

        #lightbox img {
            max-height: 85vh;
            max-width: 90vw;
        }

        #lightbox {
            background: rgba(0, 0, 0, 0.92);
        }

        #lightbox img {
            max-height: 80vh;
            max-width: 90vw;
        }
    </style>

    @php
        $images = $property->media->map(fn($m) => $m->url)->values();
        if ($images->isEmpty()) {
            $images = collect(['https://placehold.co/1200x800/1e1b1b/6366f1?text=No+Photo']);
        }
        $location = collect([$property->neighborhood, $property->sub_city, $property->city, $property->woreda])
            ->filter()
            ->implode(', ');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-white">

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-indigo-400 transition">Home</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('home', ['property_type' => $property->property_type]) }}"
                class="hover:text-indigo-400 transition capitalize">{{ $property->property_type }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-300 truncate max-w-[200px]">{{ $property->title }}</span>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">

            <!-- ============ LEFT: GALLERY + DETAILS ============ -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Gallery -->
                <div class="surface rounded-2xl overflow-hidden">
                    <div class="gallery-main relative aspect-[16/10] overflow-hidden group">
                        <img id="galleryMain" src="{{ $images->first() }}" alt="{{ $property->title }}"
                            class="w-full h-full object-cover transition-opacity duration-300 cursor-zoom-in"
                            onclick="openLightbox(currentIndex)">

                        <!-- Type badge -->
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span
                                class="bg-black/60 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide">
                                {{ $property->listing_type === 'rent' ? 'For Rent' : 'For Sale' }}
                            </span>
                            @if ($property->is_verified)
                                <span
                                    class="bg-emerald-500/80 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Verified
                                </span>
                            @endif
                            @if ($property->price_negotiable)
                                <span
                                    class="bg-amber-500/80 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide">Negotiable</span>
                            @endif
                        </div>

                        <!-- Counter -->
                        <span id="galleryCounter"
                            class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-semibold text-gray-200">
                            1 / {{ $images->count() }}
                        </span>

                        @if ($images->count() > 1)
                            <button onclick="prevImage()"
                                class="gallery-arrow absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button onclick="nextImage()"
                                class="gallery-arrow absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>

                    <!-- Thumbnails -->
                    @if ($images->count() > 1)
                        <div
                            class="flex gap-3 p-4 overflow-x-auto [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-[#3a3a3d] [&::-webkit-scrollbar-thumb]:rounded-full">
                            @foreach ($images as $i => $url)
                                <img src="{{ $url }}" data-index="{{ $i }}"
                                    alt="Photo {{ $i + 1 }}"
                                    class="thumb {{ $i === 0 ? 'active' : '' }} w-24 h-16 object-cover rounded-lg shrink-0"
                                    onclick="setImage({{ $i }})">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Title & Key Info -->
                <div class="surface rounded-2xl p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ $property->title }}</h1>
                            <p class="text-gray-400 flex items-center gap-2 mt-2">
                                <i class="fas fa-map-marker-alt text-indigo-400"></i> {{ $location ?: 'Addis Ababa' }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-3xl font-extrabold text-indigo-400">
                                {{ number_format($property->price) }}
                                <span class="text-base font-medium text-gray-400">{{ $property->currency ?? 'ETB' }}</span>
                                @if ($property->listing_type === 'rent')
                                    <span
                                        class="text-base font-medium text-gray-400">/{{ $property->rent_period ?? 'mo' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @if ($property->bedrooms)
                            <div class="bg-[#161414] border border-[#3a3a3d] rounded-xl p-4 text-center">
                                <i class="fas fa-bed text-indigo-400 text-xl mb-2"></i>
                                <div class="font-bold text-lg">{{ $property->bedrooms }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Bedrooms</div>
                            </div>
                        @endif
                        @if ($property->bathrooms)
                            <div class="bg-[#161414] border border-[#3a3a3d] rounded-xl p-4 text-center">
                                <i class="fas fa-bath text-indigo-400 text-xl mb-2"></i>
                                <div class="font-bold text-lg">{{ $property->bathrooms }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Bathrooms</div>
                            </div>
                        @endif
                        @if ($property->area)
                            <div class="bg-[#161414] border border-[#3a3a3d] rounded-xl p-4 text-center">
                                <i class="fas fa-vector-square text-indigo-400 text-xl mb-2"></i>
                                <div class="font-bold text-lg">{{ $property->area }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">{{ $property->area_unit }}</div>
                            </div>
                        @endif
                        @if ($property->parking_spaces)
                            <div class="bg-[#161414] border border-[#3a3a3d] rounded-xl p-4 text-center">
                                <i class="fas fa-parking text-indigo-400 text-xl mb-2"></i>
                                <div class="font-bold text-lg">{{ $property->parking_spaces }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Parking</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div class="surface rounded-2xl p-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-indigo-400"></i> Description
                    </h3>
                    <p class="text-gray-300 whitespace-pre-line leading-relaxed">
                        {{ $property->description ?: 'No description provided.' }}</p>
                </div>

                <!-- Features / Details -->
                @if (!empty($property->details) && is_array($property->details))
                    <div class="surface rounded-2xl p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-list-check text-indigo-400"></i> Features &amp; Amenities
                        </h3>
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($property->details as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <div
                                            class="flex items-center gap-3 bg-[#161414] border border-[#3a3a3d] rounded-xl px-4 py-3">
                                            <i class="fas fa-check-circle text-emerald-400 shrink-0"></i>
                                            <span
                                                class="text-sm text-gray-300 capitalize">{{ str_replace('_', ' ', $item) }}</span>
                                        </div>
                                    @endforeach
                                @elseif ($value)
                                    <div
                                        class="flex items-center gap-3 bg-[#161414] border border-[#3a3a3d] rounded-xl px-4 py-3">
                                        <i class="fas fa-check-circle text-emerald-400 shrink-0"></i>
                                        <span
                                            class="text-sm text-gray-300 capitalize">{{ str_replace('_', ' ', is_string($key) ? $key : $value) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Location -->
                <div class="surface rounded-2xl p-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-map text-indigo-400"></i> Location
                    </h3>
                    <div class="space-y-2 text-sm text-gray-400">
                        @if ($property->address)
                            <p><span class="text-gray-500 w-28 inline-block">Address:</span> {{ $property->address }}</p>
                        @endif
                        @if ($property->neighborhood)
                            <p><span class="text-gray-500 w-28 inline-block">Neighborhood:</span>
                                {{ $property->neighborhood }}</p>
                        @endif
                        @if ($property->sub_city)
                            <p><span class="text-gray-500 w-28 inline-block">Sub-city:</span> {{ $property->sub_city }}</p>
                        @endif
                        @if ($property->woreda)
                            <p><span class="text-gray-500 w-28 inline-block">Woreda:</span> {{ $property->woreda }}</p>
                        @endif
                        @if ($property->city)
                            <p><span class="text-gray-500 w-28 inline-block">City:</span> {{ $property->city }}</p>
                        @endif
                    </div>
                    @if ($property->latitude && $property->longitude)
                        <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}"
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-indigo-500/10 text-indigo-300 font-semibold rounded-lg hover:bg-indigo-500/20 transition text-sm">
                            <i class="fas fa-directions"></i> Open in Google Maps
                        </a>
                    @endif
                </div>
            </div>

            <!-- ============ RIGHT: STICKY CONTACT CARD ============ -->
            <div>
                <div class="surface rounded-2xl p-6 lg:sticky lg:top-8">
                    <div class="flex items-center gap-4 pb-5 border-b border-[#3a3a3d]">
                        <div
                            class="w-14 h-14 rounded-full bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center shrink-0">
                            <span
                                class="text-xl font-bold text-indigo-300">{{ strtoupper(substr($property->owner->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-white truncate">{{ $property->owner->name ?? 'Property Owner' }}
                            </div>
                            <div class="text-xs text-gray-500 capitalize">{{ $property->owner->role ?? 'Owner' }} </div>
                        </div>
                        @if ($property->is_verified)
                            <i class="fas fa-badge-check text-emerald-400 ml-auto" title="Verified owner"></i>
                        @endif
                    </div>

                    <div class="py-5 border-b border-[#3a3a3d] space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Property Type</span><span
                                class="font-semibold capitalize">{{ $property->property_type }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Listing Type</span><span
                                class="font-semibold capitalize">{{ $property->listing_type }}</span></div>
                        @if ($property->furnishing_status)
                            <div class="flex justify-between"><span class="text-gray-500">Furnishing</span><span
                                    class="font-semibold capitalize">{{ str_replace('_', ' ', $property->furnishing_status) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between"><span class="text-gray-500">Listed</span><span
                                class="font-semibold">{{ $property->published_at?->diffForHumans() ?? 'Recently' }}</span>
                        </div>
                    </div>

                    <div class="pt-5 space-y-3">
                        <button id="contactBtn"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i> Message Owner
                        </button>

                        @if ($property->owner?->phone)
                            <a href="tel:{{ $property->owner->phone }}"
                                class="w-full flex items-center justify-center gap-2 bg-[#161414] border border-[#3a3a3d] hover:border-indigo-500 text-white font-semibold py-3.5 rounded-xl transition">
                                <i class="fas fa-phone-alt text-indigo-400"></i> {{ $property->owner->phone }}
                            </a>
                        @endif

                        <button onclick="copyLink()"
                            class="w-full flex items-center justify-center gap-2 text-sm text-gray-400 hover:text-indigo-300 py-2 transition">
                            <i class="fas fa-share-alt"></i> Share this listing
                        </button>
                    </div>

                    <p class="mt-4 text-xs text-gray-600 text-center">
                        <i class="fas fa-shield-alt text-emerald-500 mr-1"></i> Never pay in advance before viewing the
                        property.
                    </p>
                </div>
            </div>
        </div>

        <!-- ============ RELATED PROPERTIES ============ -->
        @if ($relatedProperties->isNotEmpty())
            <div class="mt-16">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Similar Properties</h2>
                        <p class="text-gray-500 text-sm mt-1">More {{ $property->property_type }}s in
                            {{ $property->city ?? 'the area' }}</p>
                    </div>
                    <a href="{{ route('home', ['property_type' => $property->property_type]) }}"
                        class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 shrink-0">
                        View all <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($relatedProperties as $rel)
                        <div class="listing-card-dark group relative rounded-2xl overflow-hidden flex flex-col">
                            <a href="{{ route('property.show', $rel) }}" class="flex flex-col flex-1">
                                <div class="relative aspect-[4/3] overflow-hidden bg-[#161414]">
                                    <img src="{{ $rel->cover_url ?? 'https://placehold.co/600x400/1e1b1b/6366f1?text=No+Photo' }}"
                                        alt="{{ $rel->title }}" loading="lazy"
                                        class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-700 ease-in-out">

                                    <div class="absolute top-4 left-4">
                                        <span
                                            class="bg-black/60 backdrop-blur-sm px-3 py-1.5 rounded-lg text-xs font-bold text-white tracking-wide uppercase">
                                            {{ $rel->listing_type === 'rent' ? 'For Rent' : 'For Sale' }}
                                        </span>
                                    </div>

                                    <div
                                        class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-5 pt-12">
                                        <span class="text-xl font-extrabold text-white">
                                            {{ number_format($rel->price) }}
                                            <span
                                                class="text-sm font-medium text-gray-300">{{ $rel->currency ?? 'ETB' }}</span>
                                            @if ($rel->listing_type === 'rent')
                                                <span
                                                    class="text-sm font-medium text-gray-300">/{{ $rel->rent_period ?? 'mo' }}</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="p-5 flex-1 flex flex-col">
                                    <h3
                                        class="font-bold text-white line-clamp-1 mb-1 group-hover:text-indigo-400 transition-colors">
                                        {{ $rel->title }}</h3>
                                    <p class="text-sm text-gray-400 flex items-center gap-1.5 line-clamp-1">
                                        <i class="fas fa-map-marker-alt text-indigo-400"></i>
                                        {{ collect([$rel->neighborhood, $rel->sub_city, $rel->city])->filter()->implode(', ') }}
                                    </p>
                                </div>
                            </a>

                            {{-- Sibling of the link, not a child --}}
                            <div class="absolute top-4 right-4 z-10">
                                <x-save-button :property="$rel" :saved="in_array($rel->id, $savedIds)" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- ============ LIGHTBOX ============ -->
    <div id="lightbox" class="hidden fixed inset-0 z-[100] items-center justify-center pt-24 pb-8">
        <!-- Navigation Arrows -->
        <button onclick="prevImage()"
            class="gallery-arrow absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full flex items-center justify-center text-white z-[110]"><i
                class="fas fa-chevron-left"></i></button>
        <button onclick="nextImage()"
            class="gallery-arrow absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full flex items-center justify-center text-white z-[110]"><i
                class="fas fa-chevron-right"></i></button>

        <!-- Image Wrapper for positioning the X button -->
        <div id="lightboxWrapper" class="relative inline-flex items-center justify-center">
            <button onclick="closeLightbox()"
                class="absolute -top-4 -right-4 w-10 h-10 bg-black/80 hover:bg-gray-600 rounded-full flex items-center justify-center text-white text-xl transition z-[110] shadow-lg border border-gray-600">
                <i class="fas fa-times"></i>
            </button>

            <img id="lightboxImg" src="" alt="Full size" class="rounded-lg shadow-2xl">
        </div>
    </div>

    <!-- ============ REGISTER MODAL (dark) ============ -->
    <div id="registerModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4">
        <div class="surface rounded-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold">Create an Account</h2>
                <button class="close-modal text-gray-500 hover:text-gray-300 transition text-lg"><i
                        class="fas fa-times"></i></button>
            </div>
            <p class="text-sm text-gray-500 mb-5 -mt-3">Quick sign up to message the owner directly.</p>
            <form id="registerForm" class="space-y-4">
                <input type="text" name="name" required placeholder="Full name"
                    class="surface-input w-full rounded-lg px-4 py-3 text-sm focus:outline-none">
                <input type="email" name="email" required placeholder="Email address"
                    class="surface-input w-full rounded-lg px-4 py-3 text-sm focus:outline-none">
                <input type="text" name="phone" required placeholder="Phone number"
                    class="surface-input w-full rounded-lg px-4 py-3 text-sm focus:outline-none">
                <input type="password" name="password" required minlength="8"
                    placeholder="Password (min. 8 characters)"
                    class="surface-input w-full rounded-lg px-4 py-3 text-sm focus:outline-none">
                <button type="submit" id="registerSubmitBtn"
                    class="btn-submit w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="spinner hidden w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Create Account</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ============ MESSAGE MODAL (dark) ============ -->
    <div id="messageModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4">
        <div class="surface rounded-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold">Message Owner</h2>
                <button class="close-modal text-gray-500 hover:text-gray-300 transition text-lg"><i
                        class="fas fa-times"></i></button>
            </div>
            <p class="text-sm text-gray-500 mb-4 -mt-3">To: {{ $property->owner->name ?? 'Owner' }} ·
                {{ $property->title }}</p>
            <form id="messageForm" class="space-y-4">
                <textarea name="body" rows="5" required
                    class="surface-input w-full rounded-lg px-4 py-3 text-sm focus:outline-none resize-none"
                    placeholder="Hi, I am interested in this property. Is it still available?"></textarea>
                <button type="submit" id="messageSubmitBtn"
                    class="btn-submit w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="spinner hidden w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Send Message</span>
                </button>
            </form>
        </div>
    </div>
    <!-- ============ TOAST NOTIFICATION ============ -->
    <div id="toast"
        class="fixed top-24 right-4 z-[150] transform transition-all duration-300 translate-x-full opacity-0 flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl text-white font-bold pointer-events-none">
        <i id="toastIcon" class="fas fa-check-circle text-xl"></i>
        <span id="toastText">Success!</span>
    </div>

    <script>
        // ===== Gallery =====
        const galleryImages = @json($images);
        let currentIndex = 0;
        const mainImg = document.getElementById('galleryMain');
        const counter = document.getElementById('galleryCounter');
        const thumbs = document.querySelectorAll('.thumb');

        function setImage(i) {
            currentIndex = (i + galleryImages.length) % galleryImages.length;
            mainImg.style.opacity = 0;
            setTimeout(() => {
                mainImg.src = galleryImages[currentIndex];
                mainImg.style.opacity = 1;
            }, 150);
            counter.textContent = (currentIndex + 1) + ' / ' + galleryImages.length;
            thumbs.forEach(t => t.classList.toggle('active', +t.dataset.index === currentIndex));
        }

        function nextImage() {
            setImage(currentIndex + 1);
            updateLightbox();
        }

        function prevImage() {
            setImage(currentIndex - 1);
            updateLightbox();
        }

        // ===== Lightbox =====
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');

        function openLightbox(i) {
            setImage(i);
            updateLightbox();
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function updateLightbox() {
            lightboxImg.src = galleryImages[currentIndex];
        }

        function closeLightbox() {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.body.style.overflow = '';
        }
        lightbox.addEventListener('click', e => {
            if (e.target === lightbox || e.target.id === 'lightboxWrapper') {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', e => {
            if (lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        // ===== Share =====
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            });
        }
        // ===== Toast Notification =====
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastText = document.getElementById('toastText');
            const toastIcon = document.getElementById('toastIcon');

            toastText.textContent = message;

            // Reset base classes to ensure clean state
            toast.className =
                'fixed top-24 right-4 z-[150] transform transition-all duration-300 flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl text-white font-bold translate-x-full opacity-0';

            if (type === 'success') {
                toast.classList.add('bg-emerald-500'); // Green background
                toastIcon.className = 'fas fa-check-circle text-xl';
            } else {
                toast.classList.add('bg-red-500'); // Red background for errors
                toastIcon.className = 'fas fa-exclamation-circle text-xl';
            }

            // Animate in (small delay to allow DOM to register the class reset)
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            }, 10);

            // Animate out after 3 seconds
            setTimeout(() => {
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-full', 'opacity-0');
            }, 3000);
        }
        // ===== Modals & AJAX =====
        document.addEventListener('DOMContentLoaded', function() {
            const contactBtn = document.getElementById('contactBtn');
            const registerModal = document.getElementById('registerModal');
            const messageModal = document.getElementById('messageModal');
            const registerForm = document.getElementById('registerForm');
            const messageForm = document.getElementById('messageForm');
            const closeButtons = document.querySelectorAll('.close-modal');

            let isAuthenticated = @json(auth()->check());
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const propertyId = @json($property->id);

            // Spinner helpers
            function setLoading(btn, loading) {
                btn.classList.toggle('btn-loading', loading);
                btn.querySelector('.spinner').classList.toggle('hidden', !loading);
            }

            contactBtn.addEventListener('click', () => {
                if (isAuthenticated) {
                    messageModal.classList.remove('hidden');
                } else {
                    registerModal.classList.remove('hidden');
                }
            });

            const hideModals = () => {
                registerModal.classList.add('hidden');
                messageModal.classList.add('hidden');
            };
            closeButtons.forEach(btn => btn.addEventListener('click', hideModals));
            [registerModal, messageModal].forEach(m => m.addEventListener('click', e => {
                if (e.target === m) hideModals();
            }));

            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('registerSubmitBtn');
                setLoading(btn, true);

                try {
                    const response = await fetch('/ajax-register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData(registerForm)
                            .entries()))
                    });

                    if (response.ok) {
                        isAuthenticated = true;
                        registerForm.reset();
                        registerModal.classList.add('hidden');
                        messageModal.classList.remove('hidden');
                        showToast('Account created! You can now send your message.', 'success');
                    } else {
                        const err = await response.json();
                        alert('Registration failed: ' + (err.message || JSON.stringify(err.errors ||
                            'Check your inputs.')));
                    }
                } catch (error) {
                    console.error(error);
                    alert('Something went wrong. Please try again.');
                } finally {
                    setLoading(btn, false);
                }
            });

            messageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('messageSubmitBtn');
                setLoading(btn, true);

                try {
                    const response = await fetch(`/property/${propertyId}/message`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            body: new FormData(messageForm).get('body')
                        })
                    });

                    if (response.ok) {
                        messageModal.classList.add('hidden');
                        messageForm.reset();
                        showToast('Message sent successfully!', 'success');
                    } else {
                        showToast('Failed to send message.', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    showToast('Something went wrong. Please try again.', 'error');
                } finally {
                    setLoading(btn, false);
                }
            });
        });
    </script>
@endsection
