@extends('layouts.app')
@section('title', 'Modifier intervention')
@section('content')
<a href="{{ route('contrats.show', $intervention->contrat) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:1.5rem">Modifier l'intervention</h1>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.update', $intervention) }}">
        @csrf @method('PUT')

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention', $intervention->date_intervention->format('Y-m-d')) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif</label>
                    <select name="motif" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner —</option>
                        @foreach($motifs as $motif)
                            <option value="{{ $motif }}" {{ $intervention->motif==$motif?'selected':'' }}>{{ $motif }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type et durée</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="site" {{ $intervention->type=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance" {{ $intervention->type=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash" {{ $intervention->type=='flash'?'selected':'' }}>Flash</option>
                    </select>
                </div>
                @if($intervention->type !== 'flash')
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="number" name="heures" value="{{ floor($intervention->duree_minutes/60) }}" min="0" max="23" style="width:70px;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                        <span style="font-size:13px;color:#888">h</span>
                        <input type="number" name="minutes" value="{{ $intervention->duree_minutes%60 }}" min="0" max="59" step="5" style="width:70px;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                        <span style="font-size:13px;color:#888">min</span>
                    </div>
                </div>
                @else
                <div style="padding:8px 12px;background:#FFF3E6;border-radius:8px;border:1px solid #E8720C;font-size:12px;color:#854F0B;align-self:end">
                    ⚡ Flash {{ $intervention->flash_numero }}/3 — {{ $intervention->flash_numero == 3 ? '20 min débités' : '0 min débité' }}
                </div>
                @endif
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <form action="{{ route('interventions.destroy', $intervention) }}" method="POST" onsubmit="return confirm('Supprimer cette intervention ?')">
                @csrf @method('DELETE')
                <button type="submit" style="padding:8px 14px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#dc2626;background:#fff;cursor:pointer">🗑 Supprimer</button>
            </form>
            <div style="display:flex;gap:8px">
                <a href="{{ route('contrats.show', $intervention->contrat) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer</button>
            </div>
        </div>
    </form>
</div>
@endsection
