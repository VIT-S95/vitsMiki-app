<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'page'        => 'required|string|max:100',
            'preferences' => 'required|array',
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => auth()->id(), 'page' => $request->page],
            ['preferences' => $request->preferences]
        );

        return response()->json(['ok' => true]);
    }

    public function show(string $page)
    {
        $pref = UserPreference::where('user_id', auth()->id())
            ->where('page', $page)
            ->first();

        return $pref
            ? response()->json(['preferences' => $pref->preferences])
            : response()->json(null);
    }
}
