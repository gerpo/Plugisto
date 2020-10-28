<?php

namespace Gerpo\Plugisto\Controllers;

use Gerpo\Plugisto\Models\Plugisto;
use Gerpo\Plugisto\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PlugistoController extends Controller
{
    public function index()
    {
        $packages = Plugisto::withoutGlobalScope(ActiveScope::class)->get();

        return view('plugisto::index', compact('packages'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'data.*.id' => 'integer|required',
            'data.*.is_active' => 'boolean|required',
        ]);

        $packages = $request->get('data');

        foreach ($packages as $package) {
            if (($model = Plugisto::withoutGlobalScope(ActiveScope::class)->findOrFail($package['id'])) == null) {
                continue;
            }

            $model->update($package);
        }
    }

    public function destroy($id)
    {
        if (($plugisto = Plugisto::withoutGlobalScope(ActiveScope::class)->findOrFail($id)) == null || ! $plugisto->manually_added) {
            return;
        }

        $plugisto->delete();
    }
}
