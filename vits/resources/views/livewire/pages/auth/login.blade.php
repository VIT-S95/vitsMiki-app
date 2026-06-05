<?php
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.login')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();

        if (auth()->user()->client_id) {
            $this->redirect(route('portail.index', absolute: false));
        } else {
            $this->redirectIntended(default: route('dashboard', absolute: false));
        }
    }
}; ?>

<div class="card">
    <div class="logo">
        @php $logoPath = file_exists(public_path('storage/logo/logo.png')) ? asset('storage/logo/logo.png') : null; @endphp
        @if($logoPath)
            <img src="{{ $logoPath }}" style="max-height:40px;object-fit:contain">
        @else
            <div class="logo-badge">VIT-S</div>
        @endif
        <div class="logo-sub">Application interne</div>
    </div>

    @if ($errors->any())
        <div class="error">Email ou mot de passe incorrect.</div>
    @endif

    <form wire:submit="login">
        <div class="field">
            <label for="email">Adresse email</label>
            <input wire:model="form.email" type="email" id="email" placeholder="votre@email.fr" required autofocus>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input wire:model="form.password" type="password" id="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn">Se connecter</button>
    </form>
    <hr class="divider">
    <p class="hint">Accès réservé à l'équipe VIT-S</p>
</div>
