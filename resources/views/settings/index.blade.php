@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Hospital Settings</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="hospital_name">
                        Hospital Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('hospital_name') border-red-500 @enderror" 
                           id="hospital_name" type="text" name="hospital_name" 
                           value="{{ old('hospital_name', $setting->hospital_name ?? '') }}" required>
                    @error('hospital_name')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                        Hospital Address
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror" 
                              id="address" name="address" rows="3">{{ old('address', $setting->address ?? '') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="currency_symbol">
                        Currency Symbol
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('currency_symbol') border-red-500 @enderror" 
                           id="currency_symbol" type="text" name="currency_symbol" 
                           value="{{ old('currency_symbol', $setting->currency_symbol ?? 'Rs') }}" required placeholder="e.g. PKR, USD, Rs">
                    @error('currency_symbol')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="logo">
                        Hospital Logo (Optional)
                    </label>
                    @if(isset($setting) && $setting->logo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $setting->logo) }}" alt="Current Logo" class="h-20 bg-gray-100 rounded border p-2">
                            <p class="text-xs text-gray-500 mt-1">Current Logo</p>
                        </div>
                    @endif
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('logo') border-red-500 @enderror" 
                           id="logo" type="file" name="logo" accept="image/*">
                    @error('logo')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200" 
                            type="submit">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
