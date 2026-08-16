<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use App\Models\Project;
use App\Models\AuditItem;

class AuditTracker extends Component
{
    public ?int $projectId = null;

    public function mount(?Project $project = null)
    {
        if ($project && $project->exists) {
            $this->projectId = $project->id;
        }
    }

    public function getProjectsProperty()
    {
        return Project::orderBy('domain')->get();
    }

    public function getItemsByCategoryProperty(): array
    {
        if (!$this->projectId) return [];
        return AuditItem::where('project_id', $this->projectId)->get()->groupBy('category')->toArray();
    }

    public function getScoreProperty(): int
    {
        if (!$this->projectId) return 0;
        
        $items = AuditItem::where('project_id', $this->projectId)->get();
        if ($items->isEmpty()) return 0;
        
        $score = 100;
        foreach ($items as $item) {
            if ($item->status === 'fail') {
                $score -= match($item->severity) { 'critical'=>20, 'high'=>10, 'medium'=>5, 'low'=>2, default=>0 };
            } elseif ($item->status === 'partial') {
                $score -= match($item->severity) { 'critical'=>10, 'high'=>5, 'medium'=>2, 'low'=>1, default=>0 };
            }
        }
        
        return max(0, min(100, $score));
    }

    public function updateStatus(AuditItem $item, string $status)
    {
        $item->update([
            'status' => $status,
            'last_checked_at' => now()
        ]);
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Updated', 'message' => 'Audit item updated.']);
    }

    public function updateNotes(int $itemId, string $notes)
    {
        AuditItem::find($itemId)?->update(['notes' => $notes]);
    }

    public function seedAuditItems()
    {
        if (!$this->projectId) return;
        
        $items = [
            ['category'=>'Application Security','item_name'=>'APP_DEBUG=false in production','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'APP_ENV=production set','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'No stack traces exposed publicly','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'HTTPS enforced','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'CSRF protection active','severity'=>'critical'],
            ['category'=>'Application Security','item_name'=>'XSS protection headers set','severity'=>'high'],
            ['category'=>'Application Security','item_name'=>'Content Security Policy header','severity'=>'medium'],
            ['category'=>'Authentication','item_name'=>'Strong password policy enforced','severity'=>'high'],
            ['category'=>'Authentication','item_name'=>'Rate limiting on login','severity'=>'high'],
            ['category'=>'Authentication','item_name'=>'Account lockout after failed attempts','severity'=>'medium'],
            ['category'=>'Data Protection','item_name'=>'.env file not publicly accessible','severity'=>'critical'],
            ['category'=>'Data Protection','item_name'=>'Database backups running','severity'=>'high'],
            ['category'=>'Data Protection','item_name'=>'API keys not in repository','severity'=>'critical'],
            ['category'=>'Dependencies','item_name'=>'Composer packages up to date','severity'=>'medium'],
            ['category'=>'Dependencies','item_name'=>'No known vulnerable packages','severity'=>'high'],
            ['category'=>'Infrastructure','item_name'=>'File permissions correct (644 files, 755 dirs)','severity'=>'medium'],
            ['category'=>'Infrastructure','item_name'=>'Upload directory PHP execution disabled','severity'=>'high'],
            ['category'=>'Infrastructure','item_name'=>'Error logging to file not screen','severity'=>'medium'],
        ];
        
        foreach ($items as $item) {
            AuditItem::firstOrCreate(
                ['project_id' => $this->projectId, 'item_name' => $item['item_name']],
                array_merge($item, ['status' => 'unchecked', 'project_id' => $this->projectId])
            );
        }
        
        $this->dispatch('toast', ['type' => 'success', 'title' => 'Seeded', 'message' => 'Audit checklist loaded.']);
    }

    public function render()
    {
        return view('livewire.audit.audit-tracker', [
            'projects' => $this->projects,
            'itemsByCategory' => $this->itemsByCategory,
            'score' => $this->score
        ])->layout('layouts.app', ['title' => 'Security Audit']);
    }
}
