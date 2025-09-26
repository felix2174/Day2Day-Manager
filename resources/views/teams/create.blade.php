@extends('layouts.app')

@section('title', 'Neues Team')

@section('content')
    <div class="card">
        <h2>Neues Team anlegen</h2>

        <form method="POST" action="/teams" style="margin-top: 20px;">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Team-Name *</label>
                <input type="text" name="name" required maxlength="100"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Abteilung *</label>
                <select name="department" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Abteilung wählen --</option>
                    <option value="IT">IT</option>
                    <option value="Management">Management</option>
                    <option value="Support">Support</option>
                    <option value="Design">Design</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Sales">Sales</option>
                    <option value="HR">HR</option>
                    <option value="Finance">Finance</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Beschreibung</label>
                <textarea name="description" rows="3"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                            border: none; border-radius: 12px; cursor: pointer; margin-right: 10px;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Team anlegen
                </button>
                <a href="/teams" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                          border: none; border-radius: 12px; text-decoration: none; display: inline-block;
                                          font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                          onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                          onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
