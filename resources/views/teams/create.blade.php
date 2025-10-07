@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Page Header -->
        <x-page-header 
            title="Neues Team erstellen" 
            subtitle="Erstellen Sie ein neues Team für Ihr Projektmanagement"
            :actions="
                '<a href=\"' . route('teams.index') . '\" class=\"inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200\">
                    <svg class=\"w-4 h-4 mr-2\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 19l-7-7m0 0l7-7m-7 7h18\"></path>
                    </svg>
                    Zurück
                </a>'
            "
        />

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('teams.store') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Team-Name *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="z.B. Frontend-Team"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                        Abteilung *
                    </label>
                    <select id="department" 
                            name="department" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department') border-red-500 @enderror"
                            required>
                        <option value="">Abteilung auswählen</option>
                        <option value="IT" {{ old('department') == 'IT' ? 'selected' : '' }}>IT</option>
                        <option value="Management" {{ old('department') == 'Management' ? 'selected' : '' }}>Management</option>
                        <option value="Support" {{ old('department') == 'Support' ? 'selected' : '' }}>Support</option>
                        <option value="Design" {{ old('department') == 'Design' ? 'selected' : '' }}>Design</option>
                        <option value="Marketing" {{ old('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="Sales" {{ old('department') == 'Sales' ? 'selected' : '' }}>Sales</option>
                        <option value="HR" {{ old('department') == 'HR' ? 'selected' : '' }}>HR</option>
                        <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>Finance</option>
                    </select>
                    @error('department')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Beschreibung
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Beschreibung des Teams und seiner Aufgaben...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('teams.index') }}" 
                       style="padding: 12px 24px; background: #ffffff; color: #374151;
                       border: none; border-radius: 12px; text-decoration: none;
                       font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                       onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                       onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                        Abbrechen
                    </a>
                    <button type="submit" 
                            style="padding: 12px 24px; background: #ffffff; color: #374151;
                   border: none; border-radius: 12px; cursor: pointer;
                   font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                   onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                   onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                        Team erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
