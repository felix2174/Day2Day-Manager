<x-app-layout>
    <div style="max-width: 1400px; margin: 0 auto; padding: 24px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 28px; font-weight: 600; color: #111827; margin: 0;">Benutzerverwaltung</h1>
            <a href="{{ route('users.create') }}" 
               style="display: inline-flex; align-items: center; gap: 8px; background: #111827; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Neuer Benutzer
            </a>
        </div>

        <!-- DEBUG INFO -->
        <div style="padding: 20px; background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; margin-bottom: 20px;">
            <strong style="color: #92400e;">DEBUG:</strong><br>
            <div style="color: #78350f; margin-top: 8px;">
                Users Count: {{ $users->count() }}<br>
                Roles Count: {{ $roles->count() }}<br>
                First User: {{ $users->first()->name ?? 'NONE' }}<br>
            </div>
        </div>

        @if(session('success'))
            <div style="background: #10b981; color: white; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #ef4444; color: white; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Users Table -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Name</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">E-Mail</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Rolle</th>
                        <th style="padding: 16px; text-align: center; font-weight: 600; color: #6b7280; font-size: 14px;">Status</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600; color: #6b7280; font-size: 14px;">Letzter Login</th>
                        <th style="padding: 16px; text-align: right; font-weight: 600; color: #6b7280; font-size: 14px;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 16px;">
                                <div style="font-weight: 500; color: #111827;">{{ $user->name }}</div>
                            </td>
                            <td style="padding: 16px;">
                                <div style="color: #6b7280;">{{ $user->email }}</div>
                            </td>
                            <td style="padding: 16px;">
                                @if($user->role)
                                    <span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500;
                                        background: {{ $user->role->name === 'admin' ? '#dbeafe' : ($user->role->name === 'management' ? '#fef3c7' : '#e5e7eb') }};
                                        color: {{ $user->role->name === 'admin' ? '#1e40af' : ($user->role->name === 'management' ? '#92400e' : '#374151') }};">
                                        {{ $user->role->display_name }}
                                    </span>
                                @else
                                    <span style="color: #9ca3af;">Keine Rolle</span>
                                @endif
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                <button onclick="toggleActive({{ $user->id }})" 
                                        id="status-btn-{{ $user->id }}"
                                        style="padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; border: none; cursor: pointer;
                                            background: {{ $user->is_active ? '#d1fae5' : '#fee2e2' }};
                                            color: {{ $user->is_active ? '#065f46' : '#991b1b' }};">
                                    <span id="status-text-{{ $user->id }}">{{ $user->is_active ? 'Aktiv' : 'Inaktiv' }}</span>
                                </button>
                            </td>
                            <td style="padding: 16px;">
                                <div style="color: #6b7280; font-size: 14px;">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Nie' }}
                                </div>
                            </td>
                            <td style="padding: 16px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('users.edit', $user) }}" 
                                       style="padding: 6px 12px; background: #f3f4f6; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                        Bearbeiten
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;"
                                              onsubmit="return confirm('Benutzer wirklich löschen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    style="padding: 6px 12px; background: #fee2e2; color: #991b1b; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                                Löschen
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 48px; text-align: center; color: #9ca3af;">
                                Keine Benutzer gefunden
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleActive(userId) {
            fetch(`/users/${userId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById(`status-btn-${userId}`);
                    const text = document.getElementById(`status-text-${userId}`);
                    
                    if (data.is_active) {
                        btn.style.background = '#d1fae5';
                        btn.style.color = '#065f46';
                        text.textContent = 'Aktiv';
                    } else {
                        btn.style.background = '#fee2e2';
                        btn.style.color = '#991b1b';
                        text.textContent = 'Inaktiv';
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Fehler beim Ändern des Status');
            });
        }
    </script>
</x-app-layout>

