<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectContractor;
use App\Models\ProjectMilestone;
use App\Models\ProjectSiteVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('client', 'manager')->latest()->paginate(20);
        $stats = [
            'total' => Project::count(),
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'overdue' => Project::where('status', 'active')->whereDate('end_date', '<', today())->count(),
        ];

        return view('projects.index', compact('projects', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company']);
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('projects.create', compact('clients', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'manager_id' => 'required|exists:users,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:200',
            'type' => 'required|in:workplace,hospitality,retail,residential,other',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $data['project_number'] = $this->generateNumber();
        $data['completion_percent'] = 0;
        $data['actual_cost'] = 0;

        $project = Project::create($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load(['client', 'manager', 'milestones' => fn ($q) => $q->orderBy('sort_order'), 'contractors', 'siteVisits' => fn ($q) => $q->with('user')->latest()]);
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('projects.show', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'completion_percent' => 'required|integer|min:0|max:100',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date',
            'manager_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:200',
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function addMilestone(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $order = $project->milestones()->max('sort_order') + 1;

        ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'sort_order' => $order,
        ]);

        return back()->with('success', 'Milestone added.');
    }

    public function updateMilestone(Request $request, ProjectMilestone $milestone)
    {
        $request->validate(['status' => 'required|in:pending,in_progress,completed']);
        $update = ['status' => $request->status];
        if ($request->status === 'completed') {
            $update['completed_date'] = today();
            $this->recalcProgress($milestone->project_id);
        }
        $milestone->update($update);

        return back()->with('success', 'Milestone updated.');
    }

    public function assignContractor(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'trade' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'contract_value' => 'nullable|numeric|min:0',
        ]);

        ProjectContractor::create([
            'project_id' => $project->id,
            'name' => $request->name,
            'company' => $request->company,
            'trade' => $request->trade,
            'phone' => $request->phone,
            'contract_value' => $request->contract_value,
        ]);

        return back()->with('success', 'Contractor assigned.');
    }

    public function logSiteVisit(Request $request, Project $project)
    {
        $data = $request->validate([
            'visit_date' => 'required|date',
            'observations' => 'required|string',
            'status' => 'required|in:on_track,delayed,issue',
            'photos' => 'nullable|string',
        ]);

        $data['project_id'] = $project->id;
        $data['user_id'] = Auth::id();
        $data['photos'] = $request->filled('photos')
            ? array_values(array_filter(array_map('trim', explode(',', $request->photos))))
            : null;

        ProjectSiteVisit::create($data);

        return back()->with('success', 'Site visit logged.');
    }

    public function timeline(Project $project)
    {
        $project->load(['client', 'manager', 'milestones' => fn ($q) => $q->orderBy('due_date'), 'contractors', 'siteVisits' => fn ($q) => $q->with('user')->oldest()]);
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('projects.show', compact('project', 'users'));
    }

    private function generateNumber(): string
    {
        $last = Project::orderByDesc('id')->value('project_number');
        $seq = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'MPS-PRJ-'.date('Y').'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function recalcProgress(int $projectId): void
    {
        $total = ProjectMilestone::where('project_id', $projectId)->count();
        $completed = ProjectMilestone::where('project_id', $projectId)->where('status', 'completed')->count();
        if ($total > 0) {
            Project::where('id', $projectId)->update(['completion_percent' => (int) round(($completed / $total) * 100)]);
        }
    }
}
