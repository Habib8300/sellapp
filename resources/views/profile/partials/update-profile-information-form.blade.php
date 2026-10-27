<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send', absolute: false) }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update', absolute: false) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="profile_photo" :value="__('Profile photo')" />
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                @if ($user->profile_photo_path)
                    <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}" class="mt-3 h-16 w-16 rounded-full object-cover border border-gray-200" />
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>

            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="admin_title" :value="__('Admin title')" />
                <x-text-input id="admin_title" name="admin_title" type="text" class="mt-1 block w-full" :value="old('admin_title', $user->admin_title)" maxlength="80" placeholder="Super Admin" />
                <x-input-error class="mt-2" :messages="$errors->get('admin_title')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="admin_bio" :value="__('Bio')" />
                <textarea id="admin_bio" name="admin_bio" rows="4" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tell your team about your role and responsibilities.">{{ old('admin_bio', $user->admin_bio) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('admin_bio')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" maxlength="32" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="old('timezone', $user->timezone ?: 'Asia/Dhaka')" maxlength="64" />
                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
            </div>

            <div>
                <x-input-label for="preferred_language" :value="__('Preferred language')" />
                <select id="preferred_language" name="preferred_language" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="bn" {{ old('preferred_language', $user->preferred_language ?? 'bn') === 'bn' ? 'selected' : '' }}>বাংলা</option>
                    <option value="en" {{ old('preferred_language', $user->preferred_language ?? 'bn') === 'en' ? 'selected' : '' }}>English</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('preferred_language')" />
            </div>

            <div>
                <x-input-label for="theme" :value="__('Theme')" />
                <select id="theme" name="theme" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="default" {{ old('theme', $user->theme ?? 'default') === 'default' ? 'selected' : '' }}>Default</option>
                    <option value="dark" {{ old('theme', $user->theme ?? 'default') === 'dark' ? 'selected' : '' }}>Dark</option>
                    <option value="contrast" {{ old('theme', $user->theme ?? 'default') === 'contrast' ? 'selected' : '' }}>Contrast</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('theme')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="signature" :value="__('Signature / team note')" />
                <x-text-input id="signature" name="signature" type="text" class="mt-1 block w-full" :value="old('signature', $user->signature)" maxlength="255" placeholder="Operations lead • DCU Jersey House" />
                <x-input-error class="mt-2" :messages="$errors->get('signature')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
