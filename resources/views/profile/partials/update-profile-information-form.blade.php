<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Added enctype for profile picture upload -->
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Picture (Editable) -->
        <div>
            <x-input-label for="profile_picture" :value="__('Profile Picture')" />
            @if ($user->profile_picture)
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture"
                        class="h-16 w-16 rounded-full object-cover">
                </div>
            @endif
            <input id="profile_picture" name="profile_picture" type="file"
                class="mt-1 block w-full text-sm text-gray-500 text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-900 file:text-indigo-700 hover:file:bg-gray-900 dark:file:bg-gray-700 dark:file:text-gray-300" />
            <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
        </div>

        <!-- Name (Editable) -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email (Editable) -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Phone (Editable) -->
        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)"
                required autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <!-- National ID (Conditional & Editable) -->
        @if (in_array($user->role, ['owner', 'agent']))
            <div>
                <x-input-label for="national_id" :value="__('National ID')" />
                <x-text-input id="national_id" name="national_id" type="text" class="mt-1 block w-full"
                    :value="old('national_id', $user->national_id)" />
                <x-input-error class="mt-2" :messages="$errors->get('national_id')" />
            </div>
        @endif

        <!-- TIN (Conditional & Editable) -->
        @if ($user->role === 'agent')
            <div>
                <x-input-label for="tin" :value="__('TIN Number')" />
                <x-text-input id="tin" name="tin" type="text" class="mt-1 block w-full"
                    :value="old('tin', $user->tin)" />
                <x-input-error class="mt-2" :messages="$errors->get('tin')" />
            </div>
        @endif

        <hr class="border-gray-300 dark:border-gray-700">

        <!-- Read-Only Administrative Fields -->

        <!-- Role (Read-Only) -->
        <div>
            <x-input-label for="role" :value="__('Account Role')" />
            <x-text-input id="role" type="text"
                class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 opacity-70 cursor-not-allowed" :value="ucfirst($user->role)"
                disabled />
        </div>

        <!-- Status (Read-Only) -->
        <div>
            <x-input-label for="status" :value="__('Account Status')" />
            <x-text-input id="status" type="text"
                class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 opacity-70 cursor-not-allowed" :value="ucfirst($user->status)"
                disabled />
        </div>

        <!-- Email Verified At (Read-Only) -->
        <div>
            <x-input-label for="email_verified_at" :value="__('Email Verification Date')" />
            <x-text-input id="email_verified_at" type="text"
                class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 opacity-70 cursor-not-allowed" :value="$user->email_verified_at
                    ? $user->email_verified_at->format('Y-m-d H:i')
                    : __('Not Verified')"
                disabled />
        </div>

        <!-- Identity Verified At (Read-Only) -->
        <div>
            <x-input-label for="verified_at" :value="__('Identity Verification Date')" />
            <x-text-input id="verified_at" type="text"
                class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 opacity-70 cursor-not-allowed" :value="$user->verified_at
                    ? \Carbon\Carbon::parse($user->verified_at)->format('Y-m-d H:i')
                    : __('Pending Verification')"
                disabled />
        </div>

        <!-- Verified By (Read-Only) -->
        @if ($user->verified_by)
            <div>
                <x-input-label for="verified_by" :value="__('Verified By (Admin ID)')" />
                <x-text-input id="verified_by" type="text"
                    class="mt-1 block w-full bg-gray-100 dark:bg-gray-900 opacity-70 cursor-not-allowed"
                    :value="$user->verified_by" disabled />
            </div>
        @endif

        <!-- Rejection Reason (Read-Only, displays only if rejected) -->
        @if ($user->verification_rejection_reason)
            <div>
                <x-input-label for="verification_rejection_reason" :value="__('Verification Rejection Reason')"
                    class="text-red-600 dark:text-red-400" />
                <textarea id="verification_rejection_reason"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400 opacity-80 cursor-not-allowed shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    disabled>{{ $user->verification_rejection_reason }}</textarea>
            </div>
        @endif

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
