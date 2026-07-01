<?php
namespace App\Http\Controllers;

use App\Models\BoiteIdee;
use Illuminate\Http\Request;

class BoiteIdeeController extends Controller
{
    public function index()
    {
        $idees = BoiteIdee::orderBy('created_at', 'desc')->paginate(20);

        $nbNouvelles = auth()->user()->isAdmin()
            ? BoiteIdee::where('statut', 'en_attente')->count()
            : 0;

        return view('boite-idees.index', compact('idees', 'nbNouvelles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:bug,modif,ajout',
            'commentaire' => 'required|string|min:10',
        ]);

        BoiteIdee::create([
            'user_id'     => auth()->id(),
            'prenom'      => auth()->user()->name,
            'type'        => $data['type'],
            'commentaire' => $data['commentaire'],
        ]);

        return redirect()->route('boite-idees.index')
            ->with('success', 'Votre idée a bien été envoyée.');
    }

    public function vote(BoiteIdee $boiteIdee)
    {
        $userId = auth()->id();
        $votes  = $boiteIdee->votes ?? [];

        if (in_array($userId, $votes, true)) {
            $votes = array_values(array_filter($votes, fn ($id) => $id !== $userId));
        } else {
            $votes[] = $userId;
        }

        $boiteIdee->votes = $votes;
        $boiteIdee->save();

        if (request()->wantsJson()) {
            return response()->json([
                'votes'  => count($votes),
                'voted'  => in_array($userId, $votes, true),
            ]);
        }

        return back();
    }

    public function updateStatut(Request $request, BoiteIdee $boiteIdee)
    {
        $data = $request->validate([
            'statut'     => 'required|in:en_attente,en_cours,fait,refuse',
            'priorite'   => 'required|in:normal,urgent,bas',
            'admin_note' => 'nullable|string',
        ]);

        $boiteIdee->update($data);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Idée mise à jour.');
    }

    public function destroy(BoiteIdee $boiteIdee)
    {
        $boiteIdee->delete();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Idée supprimée.');
    }
}
