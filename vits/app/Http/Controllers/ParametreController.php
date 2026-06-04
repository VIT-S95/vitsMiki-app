<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametreController extends Controller
{
    public function index()
    {
        $motifs = config('vits.motifs_intervention', []);
        $parametres = [
            'seuil_echeance_mois' => (int) Setting::get('seuil_echeance_mois', config('vits.seuil_echeance_mois', 3)),
            'seuil_heures_pct'    => (int) Setting::get('seuil_heures_pct',    config('vits.seuil_heures_pct', 80)),
            'kizeo_frequence_min' => (int) Setting::get('kizeo_frequence_min', config('vits.kizeo_frequence_min', 60)),
            'kizeo_api_key'       => env('KIZEO_API_KEY', ''),
            'session_minutes'     => (int) Setting::get('session_minutes',     config('vits.session_minutes', 30)),
        ];
        $mailSettings = [
            'mail_host'         => Setting::get('mail_host', ''),
            'mail_port'         => Setting::get('mail_port', '587'),
            'mail_username'     => Setting::get('mail_username', ''),
            'mail_password'     => Setting::get('mail_password', ''),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name'    => Setting::get('mail_from_name', 'VIT-S'),
            'mail_signature'    => Setting::get('mail_signature', ''),
        ];
        $logoPath = file_exists(public_path('storage/logo/logo.png')) ? asset('storage/logo/logo.png') : null;
        return view('parametres.index', compact('motifs', 'parametres', 'mailSettings', 'logoPath'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'seuil_echeance_mois' => 'required|integer|min:1|max:12',
            'seuil_heures_pct'    => 'required|integer|min:50|max:100',
            'kizeo_frequence_min' => 'required|integer|min:5|max:120',
            'session_minutes'      => 'required|integer|min:5|max:480',
            'logo'                => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            Storage::disk('public')->makeDirectory('logo');
            $request->file('logo')->storeAs('logo', 'logo.png', 'public');
        }

        $this->updateEnv('KIZEO_API_KEY', $request->kizeo_api_key ?? '');
        $this->saveConfig(config('vits.motifs_intervention', []), $request);
        return redirect()->route('parametres.index')->with('success', 'Paramètres enregistrés.');
    }

    public function updateMail(Request $request)
    {
        $request->validate([
            'mail_host'         => 'nullable|string|max:255',
            'mail_port'         => 'nullable|integer|min:1|max:65535',
            'mail_username'     => 'nullable|string|max:255',
            'mail_password'     => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name'    => 'nullable|string|max:255',
            'mail_signature'    => 'nullable|string',
        ]);

        $keys = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name', 'mail_signature'];
        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }

        return redirect()->route('parametres.index')->with('success', 'Configuration mail enregistrée.');
    }

    public function testSmtp(Request $request)
    {
        $host     = Setting::get('mail_host', '');
        $port     = (int) Setting::get('mail_port', 587);
        $username = Setting::get('mail_username', '');
        $password = Setting::get('mail_password', '');
        $from     = Setting::get('mail_from_address', '');

        if (!$host || !$username || !$from) {
            return response()->json(['ok' => false, 'message' => 'Configuration SMTP incomplète.']);
        }

        try {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport($host, $port);
            $transport->setUsername($username);
            $transport->setPassword($password);
            $transport->start();
            $transport->stop();
            return response()->json(['ok' => true, 'message' => 'Connexion SMTP réussie.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
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
        // Persist scalars to DB (survives cache:clear)
        if ($request) {
            Setting::set('seuil_echeance_mois', (int) $request->seuil_echeance_mois);
            Setting::set('seuil_heures_pct',    (int) $request->seuil_heures_pct);
            Setting::set('kizeo_frequence_min', (int) $request->kizeo_frequence_min);
            Setting::set('session_minutes',     (int) $request->session_minutes);
        }

        // Also regenerate config/vits.php as a readable fallback
        $freq   = (int) Setting::get('kizeo_frequence_min', config('vits.kizeo_frequence_min', 60));
        $seuil  = (int) Setting::get('seuil_echeance_mois', config('vits.seuil_echeance_mois', 3));
        $heures = (int) Setting::get('seuil_heures_pct',    config('vits.seuil_heures_pct', 80));
        $sess   = (int) Setting::get('session_minutes',     config('vits.session_minutes', 30));

        $config = "<?php\nreturn [\n";
        $config .= "    'motifs_intervention' => " . var_export($motifs, true) . ",\n";
        $config .= "    'seuil_echeance_mois' => {$seuil},\n";
        $config .= "    'seuil_heures_pct'    => {$heures},\n";
        $config .= "    'kizeo_frequence_min' => {$freq},\n";
        $config .= "    'session_minutes'      => {$sess},\n";
        $config .= "    'kizeo_api_key'       => env('KIZEO_API_KEY', ''),\n";
        $config .= "];\n";
        file_put_contents(config_path('vits.php'), $config);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(config_path('vits.php'), true);
        }
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
