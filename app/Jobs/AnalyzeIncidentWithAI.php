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
        if (empty(env('GEMINI_API_KEY'))) {
            Log::warning("GEMINI_API_KEY not set. Skipping AI analysis for Incident {$this->incidentId}");
            return;
        }

        $incident = Incident::find($this->incidentId);
        if (!$incident || $incident->ai_summary) {
            return; // Already analyzed or deleted
        }

        try {
            $prompt = "You are an elite Security Operations Center (SOC) AI analyst. Your job is to analyze server incidents, logs, and security events. You must output raw JSON only without markdown code blocks.\n\n" . $this->buildPrompt($incident);

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API Error: ' . $response->body());
            }

            $content = trim($response->json('candidates.0.content.parts.0.text'));
            
            // Clean markdown json block if Gemini includes it
            if (str_starts_with($content, '```json')) {
                $content = substr($content, 7);
                $content = substr($content, 0, -3);
            } elseif (str_starts_with($content, '```')) {
                $content = substr($content, 3);
                $content = substr($content, 0, -3);
            }
            
            $data = json_decode(trim($content), true);

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
            } else {
                throw new \Exception('Invalid JSON returned by Gemini: ' . $content);
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
