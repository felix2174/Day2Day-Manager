@if(session('error'))
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <strong>⚠️ Fehler:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <strong>✅ Erfolg:</strong> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <strong>⚠️ Validierungsfehler:</strong>
        <ul style="margin: 10px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

