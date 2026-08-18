<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Models\IncidentTimeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AnalyzeIncidentWithAI implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $incidentId)
    {
    }

    public function handle(): void
    {
        if (empty(config('openai.api_key'))) {
            Log::warning("OpenAI API Key not set. Skipping AI analysis for Incident {$this->incidentId}");
            return;
        }

        $incident = Incident::find($this->incidentId);
        if (!$incident || $incident->ai_summary) {
            return; // Already analyzed or deleted
        }

        try {
            $prompt = $this->buildPrompt($incident);

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an elite Security Operations Center (SOC) AI analyst. Your job is to analyze server incidents, logs, and security events. You must output raw JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $data = json_decode($content, true);

            if ($data && isset($data['ai_summary'], $data['ai_diagnosis'])) {
                $incident->update([
                    'ai_summary' => $data['ai_summary'],
                    'ai_diagnosis' => $data['ai_diagnosis'],
                ]);

                IncidentTimeline::create([
                    'incident_id' => $incident->id,
                    'action' => 'ai_analysis_complete',
                    'performed_by' => 'System (AI)',
                    'notes' => 'AI incident analysis completed successfully.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to analyze incident with AI: " . $e->getMessage());
        }
    }

    private function buildPrompt(Incident $incident): string
    {
        $logSnippet = "";
        if (!empty($incident->related_log_entry_ids)) {
            $logs = \App\Models\LogEntry::whereIn('id', $incident->related_log_entry_ids)->limit(5)->get();
            foreach ($logs as $log) {
                $logSnippet .= "[{$log->level}] {$log->message}\n";
            }
        }

        return <<<PROMPT
Please analyze the following security incident and provide a natural language executive summary and a technical diagnosis.

Incident Title: {$incident->title}
Incident Description: {$incident->description}
Severity: {$incident->severity}
Source: {$incident->source}

Related Logs:
{$logSnippet}

Provide your response in the exact following JSON format:
{
    "ai_summary": "A 2-3 sentence executive summary of the incident written in natural language for a non-technical client.",
    "ai_diagnosis": "A detailed technical diagnosis explaining the root cause, potential impact, and suggested remediation steps for a developer."
}
PROMPT;
    }
}
