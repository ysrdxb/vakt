<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
  body { background:#0a0e1a; color:#e8edf5; font-family:'Inter',Arial,sans-serif; margin:0; padding:0; }
  .wrapper { max-width:600px; margin:0 auto; padding:24px 16px; }
  .header { background:#111827; border:1px solid #1f2d45; border-radius:12px; padding:24px; margin-bottom:20px; text-align:center; }
  .logo { font-size:1.5rem; font-weight:700; color:#e8edf5; margin-bottom:4px; }
  .sub { font-size:0.75rem; color:#8899aa; text-transform:uppercase; letter-spacing:.1em; }
  .severity-p1 { color:#ff4757; }
  .severity-p2 { color:#ffa502; }
  .severity-p3 { color:#00d4ff; }
  .severity-p4 { color:#2ed573; }
  .incident-card { background:#111827; border:1px solid #1f2d45; border-left:4px solid; border-radius:12px; padding:24px; margin-bottom:20px; }
  .incident-card.p1 { border-left-color:#ff4757; }
  .incident-card.p2 { border-left-color:#ffa502; }
  .incident-card.p3 { border-left-color:#00d4ff; }
  .incident-card.p4 { border-left-color:#2ed573; }
  .badge { display:inline-block; padding:3px 10px; border-radius:4px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px; }
  .badge-p1 { background:rgba(255,71,87,.15); color:#ff4757; border:1px solid rgba(255,71,87,.3); }
  .badge-p2 { background:rgba(255,165,2,.15); color:#ffa502; border:1px solid rgba(255,165,2,.3); }
  .badge-p3 { background:rgba(0,212,255,.12); color:#00d4ff; border:1px solid rgba(0,212,255,.25); }
  .badge-p4 { background:rgba(46,213,115,.12); color:#2ed573; border:1px solid rgba(46,213,115,.25); }
  .title { font-size:1.2rem; font-weight:700; color:#e8edf5; margin-bottom:8px; }
  .meta { font-size:0.8rem; color:#8899aa; margin-bottom:16px; font-family:monospace; }
  .description { font-size:0.875rem; color:#8899aa; background:#1a2235; border-radius:8px; padding:12px; margin-bottom:16px; }
  .btn { display:inline-block; background:#00d4ff; color:#0a0e1a; padding:11px 24px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.9rem; }
  .footer { text-align:center; font-size:0.75rem; color:#6b7a8d; margin-top:24px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo">🛡️ Vakt</div>
    <div class="sub">Security Operations Center</div>
  </div>

  <div class="incident-card {{ $incident->severity }}">
    <span class="badge badge-{{ $incident->severity }}">{{ $incident->severity_label }}</span>
    <div class="title">{{ $incident->title }}</div>
    <div class="meta">
      Project: {{ $incident->project->domain }}<br/>
      Detected: {{ $incident->detected_at?->format('Y-m-d H:i:s') }} UTC<br/>
      Status: {{ ucfirst($incident->status) }}
    </div>
    @if($incident->description)
    <div class="description">{{ $incident->description }}</div>
    @endif
    <a href="{{ config('app.url') }}/incidents/{{ $incident->id }}" class="btn">View Incident →</a>
  </div>

  <div class="footer">
    Vakt SOC Platform · Automated alert<br/>
    Do not reply to this email.
  </div>
</div>
</body>
</html>
