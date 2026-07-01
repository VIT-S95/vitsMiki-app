@extends('layouts.app')
@section('title', 'Boîte à idées')
@section('content')

@php
    $user    = auth()->user();
    $isAdmin = $user->isAdmin();

    $typeBadges = [
        'bug'   => ['label' => 'Bug 🐛',          'bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#b91c1c'],
        'modif' => ['label' => 'Modification ✏️', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'color' => '#1d4ed8'],
        'ajout' => ['label' => 'Ajout ➕',        'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#15803d'],
    ];
    $statutBadges = [
        'en_attente' => ['label' => 'En attente', 'bg' => '#f3f4f6', 'border' => '#e5e7eb', 'color' => '#6b7280', 'strike' => false],
        'en_cours'   => ['label' => 'En cours',   'bg' => '#fff8f3', 'border' => '#fed7aa', 'color' => '#E8720C', 'strike' => false],
        'fait'       => ['label' => 'Fait',       'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#15803d', 'strike' => false],
        'refuse'     => ['label' => 'Refusé',     'bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#b91c1c', 'strike' => true],
    ];
    $prioriteBadges = [
        'urgent' => ['label' => 'Urgent', 'bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#b91c1c'],
        'normal' => ['label' => 'Normal', 'bg' => '#f3f4f6', 'border' => '#e5e7eb', 'color' => '#6b7280'],
        'bas'    => ['label' => 'Bas',    'bg' => '#eff6ff', 'border' => '#bfdbfe', 'color' => '#3b82f6'],
    ];
    $statutOptions   = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'fait' => 'Fait', 'refuse' => 'Refusé'];
    $prioriteOptions = ['urgent' => 'Urgent', 'normal' => 'Normal', 'bas' => 'Bas'];
@endphp

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Boîte à idées</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $idees->total() }} idée{{ $idees->total() > 1 ? 's' : '' }}
            @if($isAdmin && $nbNouvelles > 0) · <span style="color:#E8720C;font-weight:500">{{ $nbNouvelles }} en attente</span>@endif
        </p>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

{{-- Formulaire de saisie --}}
<div style="background:#fff;border:1px solid #eee;border-radius:10px;padding:1.1rem 1.2rem;margin-bottom:1.25rem">
    <form method="POST" action="{{ route('boite-idees.store') }}">
        @csrf
        <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
            <select name="type" required style="padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:160px">
                <option value="bug">Bug 🐛</option>
                <option value="modif">Modification ✏️</option>
                <option value="ajout">Ajout ➕</option>
            </select>
            <textarea name="commentaire" required minlength="10" rows="2" placeholder="Décrivez votre idée (10 caractères min.)…"
                style="flex:1;min-width:260px;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical">{{ old('commentaire') }}</textarea>
            <button type="submit" style="padding:9px 18px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap">Envoyer</button>
        </div>
        @error('commentaire')<p style="color:#b91c1c;font-size:12px;margin-top:6px">{{ $message }}</p>@enderror
        @error('type')<p style="color:#b91c1c;font-size:12px;margin-top:6px">{{ $message }}</p>@enderror
    </form>
</div>

{{-- Tableau des idées --}}
<div style="background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden">
<table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
        <tr style="background:#fafafa;border-bottom:1px solid #eee;text-align:left;color:#888;font-size:12px">
            <th style="padding:10px 12px;font-weight:500">Votes</th>
            <th style="padding:10px 12px;font-weight:500">Date</th>
            <th style="padding:10px 12px;font-weight:500">Prénom</th>
            <th style="padding:10px 12px;font-weight:500">Type</th>
            <th style="padding:10px 12px;font-weight:500">Statut</th>
            <th style="padding:10px 12px;font-weight:500">Priorité</th>
            <th style="padding:10px 12px;font-weight:500">Commentaire</th>
            <th style="padding:10px 12px;font-weight:500">Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($idees as $idee)
        @php
            $votes    = $idee->votes ?? [];
            $nbVotes  = count($votes);
            $hasVoted = in_array($user->id, $votes);
            $t = $typeBadges[$idee->type]     ?? $typeBadges['ajout'];
            $s = $statutBadges[$idee->statut] ?? $statutBadges['en_attente'];
            $p = $prioriteBadges[$idee->priorite] ?? $prioriteBadges['normal'];
        @endphp
        <tr style="border-bottom:1px solid #f2f2f2" data-idee-row="{{ $idee->id }}">
            {{-- Votes --}}
            <td style="padding:10px 12px">
                <button type="button" onclick="toggleVote({{ $idee->id }}, this)"
                    data-voted="{{ $hasVoted ? '1' : '0' }}"
                    style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:13px;cursor:pointer;border:1px solid {{ $hasVoted ? '#E8720C' : '#ddd' }};background:{{ $hasVoted ? '#fff8f3' : '#fff' }};color:{{ $hasVoted ? '#E8720C' : '#666' }};font-weight:500">
                    👍 <span data-vote-count>{{ $nbVotes }}</span>
                </button>
            </td>
            {{-- Date --}}
            <td style="padding:10px 12px;color:#666;white-space:nowrap">{{ $idee->created_at->format('d/m/Y') }}</td>
            {{-- Prénom --}}
            <td style="padding:10px 12px;color:#1a1a1a">{{ $idee->prenom }}</td>
            {{-- Type --}}
            <td style="padding:10px 12px">
                <span style="display:inline-block;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:500;background:{{ $t['bg'] }};border:1px solid {{ $t['border'] }};color:{{ $t['color'] }}">{{ $t['label'] }}</span>
            </td>
            {{-- Statut --}}
            <td style="padding:10px 12px">
                @if($isAdmin)
                    <select data-field="statut" onchange="saveStatut({{ $idee->id }})"
                        style="padding:5px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px">
                        @foreach($statutOptions as $val => $lbl)
                            <option value="{{ $val }}" {{ $idee->statut === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                @else
                    <span style="display:inline-block;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:500;background:{{ $s['bg'] }};border:1px solid {{ $s['border'] }};color:{{ $s['color'] }};{{ $s['strike'] ? 'text-decoration:line-through' : '' }}">{{ $s['label'] }}</span>
                @endif
            </td>
            {{-- Priorité --}}
            <td style="padding:10px 12px">
                @if($isAdmin)
                    <select data-field="priorite" onchange="saveStatut({{ $idee->id }})"
                        style="padding:5px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px">
                        @foreach($prioriteOptions as $val => $lbl)
                            <option value="{{ $val }}" {{ $idee->priorite === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                @else
                    <span style="display:inline-block;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:500;background:{{ $p['bg'] }};border:1px solid {{ $p['border'] }};color:{{ $p['color'] }}">{{ $p['label'] }}</span>
                @endif
            </td>
            {{-- Commentaire --}}
            <td style="padding:10px 12px;color:#444;max-width:320px">
                <div style="white-space:pre-wrap">{{ $idee->commentaire }}</div>
                @if($isAdmin)
                    <textarea data-field="admin_note" onblur="saveStatut({{ $idee->id }})" rows="1" placeholder="Note interne admin…"
                        style="margin-top:6px;width:100%;padding:5px 8px;border:1px solid #eee;border-radius:6px;font-size:12px;background:#fafafa;resize:vertical">{{ $idee->admin_note }}</textarea>
                @elseif($idee->admin_note)
                    <div style="margin-top:6px;padding:5px 8px;border-left:2px solid #E8720C;background:#fff8f3;font-size:12px;color:#92400e">{{ $idee->admin_note }}</div>
                @endif
            </td>
            {{-- Actions --}}
            <td style="padding:10px 12px;white-space:nowrap">
                @if($isAdmin)
                    <button type="button" onclick="supprimerIdee({{ $idee->id }})" title="Supprimer"
                        style="padding:5px 9px;background:#fff;border:1px solid #fecaca;color:#b91c1c;border-radius:6px;font-size:13px;cursor:pointer">🗑️</button>
                @else
                    <span style="color:#ccc;font-size:12px">—</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="8" style="padding:2rem;text-align:center;color:#aaa">Aucune idée pour le moment. Soyez le premier à en proposer une !</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div style="margin-top:1rem">
    {{ $idees->links() }}
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    window.toggleVote = function (id, btn) {
        fetch('/boite-idees/' + id + '/vote', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const count = btn.querySelector('[data-vote-count]');
            if (count) count.textContent = data.votes;
            if (data.voted) {
                btn.style.border = '1px solid #E8720C';
                btn.style.background = '#fff8f3';
                btn.style.color = '#E8720C';
            } else {
                btn.style.border = '1px solid #ddd';
                btn.style.background = '#fff';
                btn.style.color = '#666';
            }
        });
    };

    window.saveStatut = function (id) {
        const row = document.querySelector('[data-idee-row="' + id + '"]');
        if (!row) return;
        const statut     = row.querySelector('[data-field="statut"]')?.value;
        const priorite   = row.querySelector('[data-field="priorite"]')?.value;
        const adminNote  = row.querySelector('[data-field="admin_note"]')?.value ?? '';
        fetch('/boite-idees/' + id + '/statut', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ statut, priorite, admin_note: adminNote })
        });
    };

    window.supprimerIdee = function (id) {
        if (!confirm('Supprimer définitivement cette idée ?')) return;
        fetch('/boite-idees/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(() => {
            const row = document.querySelector('[data-idee-row="' + id + '"]');
            if (row) row.remove();
        });
    };
})();
</script>

@endsection
