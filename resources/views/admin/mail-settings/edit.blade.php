<x-layout>
    <x-slot name="title">{{ __('Mail settings') }}</x-slot>

    <div class="max-w-2xl mx-auto mt-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Mail settings') }}</h1>

        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-5">
            @if (session('status_mail'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('status_mail') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('mail-settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="host" class="block text-sm font-medium text-gray-700 mb-1">{{ __('SMTP host') }}</label>
                    <input id="host" type="text" name="host" value="{{ old('host', $mailSetting->host) }}" required
                        class="w-full rounded-lg border-gray-300 @if($errors->has('host')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500"
                        placeholder="smtp.qq.com / localhost">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 mb-1">{{ __('SMTP port') }}</label>
                        <input id="port" type="number" name="port" value="{{ old('port', $mailSetting->port) }}" required min="1" max="65535"
                            class="w-full rounded-lg border-gray-300 @if($errors->has('port')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500"
                            placeholder="465 / 1025">
                    </div>

                    <div>
                        <label for="encryption" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Encryption') }}</label>
                        <select id="encryption" name="encryption"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @php
                                // 库里存空串 = 明文（本地 Mailpit）；提交时也转成空串，与存储约定一致
                                $currentEncryption = old('encryption', $mailSetting->encryption ?? '');
                            @endphp
                            <option value="" @selected($currentEncryption === '')>None（{{ __('local dev') }}）</option>
                            <option value="tls" @selected($currentEncryption === 'tls')>TLS</option>
                            <option value="ssl" @selected($currentEncryption === 'ssl')>SSL</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">{{ __('SMTP username') }}</label>
                    <input id="username" type="text" name="username" value="{{ old('username', $mailSetting->username) }}"
                        class="w-full rounded-lg border-gray-300 @if($errors->has('username')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500"
                        placeholder="{{ __('Leave empty for local dev') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('SMTP password') }}</label>
                    {{-- 安全考虑：不回填已存密码的明文；留空提交 = 沿用旧值 --}}
                    <input id="password" type="password" name="password" value="" autocomplete="new-password"
                        class="w-full rounded-lg border-gray-300 @if($errors->has('password')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500"
                        placeholder="{{ __('Leave empty to keep current password') }}">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="from_address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('From address') }}</label>
                        <input id="from_address" type="email" name="from_address" value="{{ old('from_address', $mailSetting->from_address) }}" required
                            class="w-full rounded-lg border-gray-300 @if($errors->has('from_address')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('From name') }}</label>
                        <input id="from_name" type="text" name="from_name" value="{{ old('from_name', $mailSetting->from_name) }}" required
                            class="w-full rounded-lg border-gray-300 @if($errors->has('from_name')) border-red-400 @endif focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('settings.edit') }}" class="text-sm text-gray-500 hover:underline">
                        {{ __('Back to settings') }}
                    </a>
                    <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>