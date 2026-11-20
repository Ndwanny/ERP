<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\MarketingEvent;
use App\Models\SocialPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function index()
    {
        $total_campaigns = MarketingCampaign::count();
        $active_campaigns = MarketingCampaign::where('status', 'active')->count();
        $total_leads = Lead::count();
        $new_leads = Lead::where('status', 'new')->count();

        return view('marketing.index', compact('total_campaigns', 'active_campaigns', 'total_leads', 'new_leads'));
    }

    public function campaigns()
    {
        $campaigns = MarketingCampaign::withCount('leads')->latest()->paginate(20);

        return view('marketing.campaigns', compact('campaigns'));
    }

    public function createCampaign()
    {
        return view('marketing.create-campaign');
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:email,social_media,event,corporate_outreach,whatsapp,print',
            'status' => 'required|in:draft,scheduled,active,paused,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data['created_by'] = Auth::id();
        MarketingCampaign::create($data);

        return redirect()->route('marketing.campaigns')->with('success', 'Campaign created.');
    }

    public function showCampaign(MarketingCampaign $campaign)
    {
        $campaign->load(['leads', 'posts' => fn ($q) => $q->latest()]);

        return view('marketing.show-campaign', compact('campaign'));
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,scheduled,active,paused,completed',
            'budget' => 'nullable|numeric|min:0',
            'actual_spend' => 'nullable|numeric|min:0',
        ]);
        $campaign->update($data);

        return back()->with('success', 'Campaign updated.');
    }

    public function socialScheduler()
    {
        $posts = SocialPost::with('campaign')->latest()->paginate(20);
        $campaigns = MarketingCampaign::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('marketing.social-scheduler', compact('posts', 'campaigns'));
    }

    public function schedulePost(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'required|in:facebook,instagram,tiktok,whatsapp,twitter,linkedin',
            'content' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,published,failed',
        ]);

        $data['created_by'] = Auth::id();
        SocialPost::create($data);

        return back()->with('success', 'Post scheduled.');
    }

    public function events()
    {
        $events = MarketingEvent::with('createdBy')->latest()->paginate(20);

        return view('marketing.events', compact('events'));
    }

    public function storeEvent(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,scheduled,active,paused,completed',
        ]);

        $data['created_by'] = Auth::id();
        $data['type'] = 'event';
        MarketingEvent::create($data);

        return redirect()->route('marketing.events')->with('success', 'Event saved.');
    }

    public function leads()
    {
        $leads = Lead::with('campaign', 'assignedTo')->latest()->paginate(20);
        $campaigns = MarketingCampaign::orderBy('name')->get(['id', 'name']);

        return view('marketing.leads', compact('leads', 'campaigns'));
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'company' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:30',
            'source' => 'required|string|max:50',
            'interest' => 'required|in:bags,furniture,interiors,corporate_bags,other',
            'campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'notes' => 'nullable|string',
        ]);

        $data['status'] = 'new';
        Lead::create($data);

        return back()->with('success', 'Lead captured.');
    }
}
