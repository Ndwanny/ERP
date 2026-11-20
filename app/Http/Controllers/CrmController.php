<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmController extends Controller
{
    public function index()
    {
        $clients = Client::with('assignedTo')->latest()->paginate(20);

        return view('crm.index', compact('clients'));
    }

    public function createClient()
    {
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('crm.create', compact('users'));
    }

    public function storeClient(Request $request)
    {
        $data = $request->validate($this->clientRules());
        $client = Client::create($data);

        return redirect()->route('crm.clients.show', $client)->with('success', 'Client created.');
    }

    public function showClient(Client $client)
    {
        $client->load([
            'assignedTo',
            'interactions' => fn ($q) => $q->with('user')->latest(),
            'projects' => fn ($q) => $q->latest(),
        ]);

        return view('crm.show', compact('client'));
    }

    public function editClient(Client $client)
    {
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('crm.edit', compact('client', 'users'));
    }

    public function updateClient(Request $request, Client $client)
    {
        $client->update($request->validate($this->clientRules()));

        return redirect()->route('crm.clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroyClient(Client $client)
    {
        $client->delete();

        return redirect()->route('crm.index')->with('success', 'Client deleted.');
    }

    public function pipeline()
    {
        $statuses = ['lead', 'prospect', 'active', 'dormant', 'lost'];
        $pipeline = Client::with('assignedTo')->orderBy('name')->get()->groupBy('status');
        foreach ($statuses as $status) {
            $pipeline->put($status, $pipeline->get($status, collect())->values());
        }
        $stats = [
            'total' => Client::count(),
            'active' => Client::where('status', 'active')->count(),
            'leads' => Client::where('status', 'lead')->count(),
        ];

        return view('crm.pipeline', compact('pipeline', 'stats'));
    }

    public function logInteraction(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:call,email,meeting,whatsapp,site_visit,note',
            'subject' => 'required|string|max:200',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);
        $data['user_id'] = Auth::id();
        ClientInteraction::create($data);

        return back()->with('success', 'Interaction logged.');
    }

    public function reports()
    {
        $stats = [
            'total' => Client::count(),
            'active' => Client::where('status', 'active')->count(),
            'leads' => Client::where('status', 'lead')->count(),
            'vip' => Client::whereIn('tier', ['vip', 'enterprise'])->count(),
        ];
        $byType = Client::selectRaw('type, count(*) as aggregate')->groupBy('type')->pluck('aggregate', 'type');
        $byStatus = Client::selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $topClients = Client::orderByDesc('lifetime_value')->limit(10)->get(['id', 'name', 'company', 'status', 'lifetime_value']);

        return view('crm.reports', compact('stats', 'byType', 'byStatus', 'topClients'));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }
        $clients = Client::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('company', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%");
        })->orderBy('name')->limit(10)->get(['id', 'name', 'company', 'email', 'phone', 'type', 'status', 'tier']);

        return response()->json($clients->map(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'company' => $client->company,
            'email' => $client->email,
            'phone' => $client->phone,
            'type' => $client->type,
            'status' => $client->status,
            'tier' => $client->tier,
            'url' => route('crm.clients.show', $client),
        ]));
    }

    private function clientRules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'type' => 'required|in:institutional,corporate,individual,retail',
            'company' => 'nullable|string|max:150',
            'industry' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:lead,prospect,active,dormant,lost',
            'tier' => 'required|in:standard,vip,enterprise',
            'notes' => 'nullable|string',
            'referral_source' => 'nullable|string|max:150',
            'assigned_to' => 'nullable|exists:users,id',
            'lifetime_value' => 'nullable|numeric|min:0',
        ];
    }
}
