<?php
namespace App\Http\Controllers;

use App\Models\Intervention;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $techniciens = Intervention::whereNotNull('technicien')
            ->distinct()->orderBy('technicien')->pluck('technicien');

        $query = Intervention::whereNotNull('technicien');

        $query->when($request->technicien, fn($q) => $q->where('technicien', $request->technicien));

        match ($request->periode) {
            'today'    => $query->whereDate('date_intervention', today()),
            'week'     => $query->whereBetween('date_intervention', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'    => $query->whereBetween('date_intervention', [now()->startOfMonth(), now()->endOfMonth()]),
            'semestre' => $query->where('date_intervention', '>=', now()->subMonths(6)->toDateString()),
            'annee'    => $query->whereBetween('date_intervention', [now()->startOfYear(), now()->endOfYear()]),
            'n1'       => $query->whereBetween('date_intervention', [
                              now()->subYear()->startOfYear()->toDateString(),
                              now()->subYear()->endOfYear()->toDateString(),
                          ]),
            'custom'   => $query
                              ->when($request->date_debut, fn($q) => $q->where('date_intervention', '>=', $request->date_debut))
                              ->when($request->date_fin,   fn($q) => $q->where('date_intervention', '<=', $request->date_fin)),
            default    => null,
        };

        $interventions = $query->orderBy('date_intervention')->get();

        $statsByTech = $interventions->groupBy('technicien')
            ->map(fn($items) => [
                'nb'       => $items->count(),
                'heures'   => round($items->sum('duree_minutes') / 60, 1),
                'site'     => $items->where('type', 'site')->count(),
                'distance' => $items->where('type', 'distance')->count(),
                'flash'    => $items->where('type', 'flash')->count(),
            ])
            ->sortByDesc('heures');

        $moisPresents = $interventions
            ->map(fn($i) => $i->date_intervention->format('Y-m'))
            ->unique()->sort()->values();

        $moisLabels = $moisPresents
            ->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->locale('fr')->isoFormat('MMM YYYY'))
            ->toArray();

        $techLabels = $statsByTech->keys()->values()->toArray();
        $techHeures = $statsByTech->pluck('heures')->values()->toArray();

        $couleurs = ['#E8720C', '#0C6AAE', '#166534', '#7C3AED', '#DC2626', '#0891B2', '#B45309'];

        $evolutionDatasets = [];
        foreach ($techLabels as $i => $tech) {
            $techItems = $interventions->where('technicien', $tech);
            $data = $moisPresents->map(
                fn($mois) => $techItems->filter(fn($int) => $int->date_intervention->format('Y-m') === $mois)->count()
            )->values()->toArray();
            $evolutionDatasets[] = [
                'label'           => $tech,
                'data'            => $data,
                'borderColor'     => $couleurs[$i % count($couleurs)],
                'backgroundColor' => $couleurs[$i % count($couleurs)],
                'tension'         => 0.3,
                'fill'            => false,
                'pointRadius'     => 4,
            ];
        }

        return view('performance.index', compact(
            'techniciens', 'statsByTech', 'moisLabels',
            'techLabels', 'techHeures', 'evolutionDatasets'
        ));
    }
}
