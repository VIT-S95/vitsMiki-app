<x-app-layout>
    <div class="page-content">
        <div class="page-header">
            <h1 class="page-title">Calculateur Prorata</h1>
            <p class="page-subtitle">Calcul du montant au prorata entre deux dates</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

            {{-- Bloc paramètres --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="ti ti-settings mr-2"></i>Paramètres</h2>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" id="date_debut"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" id="date_fin"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant annuel HT (€)</label>
                        <input type="number" id="montant_annuel" step="0.01" min="0" placeholder="0.00"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>
            </div>

            {{-- Bloc résultats --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="ti ti-calculator mr-2"></i>Résultats</h2>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Nombre de jours</span>
                        <span id="res_jours" class="text-sm font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Décomposition</span>
                        <span id="res_decomposition" class="text-sm font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Base de calcul</span>
                        <span id="res_base" class="text-sm font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between items-center py-3 mt-2 bg-orange-50 rounded-lg px-3">
                        <span class="text-sm font-bold text-orange-700">Montant prorata HT</span>
                        <span id="res_montant" class="text-xl font-bold text-orange-600">—</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const fields = ['date_debut', 'date_fin', 'montant_annuel'];
        fields.forEach(id => document.getElementById(id).addEventListener('input', calculer));

        function estBissextile(annee) {
            return (annee % 4 === 0 && annee % 100 !== 0) || (annee % 400 === 0);
        }

        function decomposer(debut, fin) {
            let mois = 0;
            let d = new Date(debut);
            const f = new Date(fin);

            while (true) {
                const next = new Date(d);
                next.setMonth(next.getMonth() + 1);
                if (next > f) break;
                mois++;
                d = next;
            }

            const diffMs = f - d;
            const joursRestants = Math.round(diffMs / (1000 * 60 * 60 * 24));
            return { mois, jours: joursRestants };
        }

        function calculer() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin   = document.getElementById('date_fin').value;
            const montant   = parseFloat(document.getElementById('montant_annuel').value);

            if (!dateDebut || !dateFin || isNaN(montant)) {
                ['res_jours', 'res_decomposition', 'res_base', 'res_montant'].forEach(id => {
                    document.getElementById(id).textContent = '—';
                });
                return;
            }

            const d1 = new Date(dateDebut);
            const d2 = new Date(dateFin);

            if (d2 <= d1) {
                ['res_jours', 'res_decomposition', 'res_base', 'res_montant'].forEach(id => {
                    document.getElementById(id).textContent = '—';
                });
                return;
            }

            const diffMs   = d2 - d1;
            const nbJours  = Math.round(diffMs / (1000 * 60 * 60 * 24));
            const annee    = d1.getFullYear();
            const base     = estBissextile(annee) ? 366 : 365;
            const prorata  = (montant * nbJours) / base;
            const { mois, jours } = decomposer(d1, d2);

            document.getElementById('res_jours').textContent = nbJours + ' jour(s)';
            document.getElementById('res_decomposition').textContent =
                mois > 0 ? mois + ' mois ' + jours + ' jour(s)' : jours + ' jour(s)';
            document.getElementById('res_base').textContent = base + ' jours (' + (base === 366 ? 'année bissextile' : 'année normale') + ')';
            document.getElementById('res_montant').textContent = prorata.toFixed(2) + ' €';
        }
    </script>
</x-app-layout>
