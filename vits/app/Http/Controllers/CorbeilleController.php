<?php
namespace App\Http\Controllers;
use App\Models\Intervention;
use Illuminate\Http\Request;

class CorbeilleController extends Controller
{
    public function index()
    {
        $interventions = Intervention::onlyTrashed()
            ->with(['contrat.client', 'deletedBy'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(50);
        return view('corbeille.index', compact('interventions'));
    }

    public function restaurer(int $id)
    {
        $intervention = Intervention::onlyTrashed()->findOrFail($id);
        $intervention->deleted_by = null;
        $intervention->save();
        $intervention->restore();
        return redirect()->route('corbeille.index')->with('success', 'Intervention restaurée.');
    }

    public function forceDelete(int $id)
    {
        $intervention = Intervention::onlyTrashed()->findOrFail($id);
        $intervention->forceDelete();
        return redirect()->route('corbeille.index')->with('success', 'Intervention supprimée définitivement.');
    }
}
