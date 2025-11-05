<x-app-layout>
    <div style="max-width: 800px; margin: 0 auto; padding: 24px;">
        <!-- Header -->
        <div style="margin-bottom: 24px;">
            <a href="{{ route('users.index') }}" 
               style="display: inline-flex; align-items: center; gap: 8px; color: #6b7280; text-decoration: none; margin-bottom: 16px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Zurück zur Übersicht
            </a>
            <h1 style="font-size: 28px; font-weight: 600; color: #111827; margin: 0;">Benutzer bearbeiten</h1>
        </div>

        <!-- Form -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px;">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    @error('name')
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        E-Mail <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    @error('email')
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Neues Passwort
                    </label>
                    <input type="password" name="password"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    <div style="color: #6b7280; font-size: 13px; margin-top: 4px;">Leer lassen, um das Passwort nicht zu ändern</div>
                    @error('password')
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Passwort bestätigen
                    </label>
                    <input type="password" name="password_confirmation"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <!-- Role -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Rolle <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="role_id" required
                            style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        <option value="">Rolle auswählen</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} (Level {{ $role->level }})
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div style="color: #ef4444; font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Active Status -->
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-weight: 500; color: #374151;">Benutzer ist aktiv</span>
                    </label>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <a href="{{ route('users.index') }}" 
                       style="padding: 10px 20px; background: #f3f4f6; color: #374151; border-radius: 8px; text-decoration: none; font-weight: 500;">
                        Abbrechen
                    </a>
                    <button type="submit" 
                            style="padding: 10px 20px; background: #111827; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                        Änderungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

