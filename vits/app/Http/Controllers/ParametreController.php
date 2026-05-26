<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametreController extends Controller
{
    public function index()
    {
        $motifs = config('vits.motifs_intervention', []);
        $parametres = [
            'seuil_echeance_mois' => config('vits.seuil_echeance_mois', 3),
            'seuil_heures_pct'    => config('vits.seuil_heures_pct', 80),
            'kizeo_frequence_min' => config('vits.kizeo_frequence_min', 15),
            'kizeo_api_key'       => env('KIZEO_API_KEY', ''),
            'session_heures'      => config('vits.session_heures', 8),
        ];
        $logoPath = file_exists(public_path('storage/logo/logo.png')) ? asset('storage/logo/logo.png') : null;
        return view('parametres.index', compact('motifs', 'parametres', 'logoPath'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'seuil_echeance_mois' => 'required|integer|min:1|max:12',
            'seuil_heures_pct'    => 'required|integer|min:50|max:100',
            'kizeo_frequence_min' => 'required|integer|min:5|max:120',
            'session_heures'      => 'required|integer|min:1|max:24',
            'logo'                => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Upload logo
        if ($request->hasFile('logo')) {
            Storage::disk('public')->makeDirectory('logo');
            $request->file('logo')->storeAs('logo', 'logo.png', 'public');
        }

        $this->updateEnv('KIZEO_API_KEY', $request->kizeo_api_key ?? '');
        $motifs = config('vits.motifs_intervention', []);
        $this->saveConfig($motifs, $request);
        return redirect()->route('parametres.index')->with('success', 'Paramètres enregistrés.');
    }

    public function updateMotifs(Request $request)
    {
        $motifs = array_values(array_filter($request->motifs ?? [], fn($m) => !empty(trim($m))));
        $this->saveConfig($motifs);
        return redirect()->route('parametres.index')->with('success', 'Motifs mis à jour.');
    }

    public function deleteLogo()
    {
        Storage::disk('public')->delete('logo/logo.png');
        return redirect()->route('parametres.index')->with('success', 'Logo supprimé.');
    }

    protected function saveConfig($motifs, $request = null)
    {
        $config = "<?php\nreturn [\n";
        $config .= "    'motifs_intervention' => " . var_export($motifs, true) . ",\n";
        $config .= "    'seuil_echeance_mois' => " . (int)($request?->seuil_echeance_mois ?? config('vits.seuil_echeance_mois', 3)) . ",\n";
        $config .= "    'seuil_heures_pct'    => " . (int)($request?->seuil_heures_pct ?? config('vits.seuil_heures_pct', 80)) . ",\n";
        $config .= "    'kizeo_frequence_min' => " . (int)($request?->kizeo_frequence_min ?? config('vits.kizeo_frequence_min', 15)) . ",\n";
        $config .= "    'session_heures'      => " . (int)($request?->session_heures ?? config('vits.session_heures', 8)) . ",\n";
        $config .= "    'kizeo_api_key'       => env('KIZEO_API_KEY', ''),\n";
        $config .= "];\n";
        file_put_contents(config_path('vits.php'), $config);
        \Artisan::call('config:clear');
    }

    protected function updateEnv($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, $key.'=') !== false) {
                $content = preg_replace('/^'.$key.'=.*/m', $key.'='.$value, $content);
            } else {
                $content .= "\n".$key.'='.$value;
            }
            file_put_contents($path, $content);
        }
    }
}
