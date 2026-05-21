<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="560" style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(0,51,102,.12);">

      {{-- Header --}}
      <tr><td style="background:linear-gradient(135deg,#003366 0%,#004a99 100%);padding:36px 40px;text-align:center;">
        <div style="font-size:48px;margin-bottom:12px;">
          @if($permission === 'approve photos')📸@else⭐@endif
        </div>
        <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:6px;">
          @if($permission === 'approve photos')You're now a photo approver!@else You can now feature photos!@endif
        </div>
        <div style="font-size:14px;color:rgba(255,255,255,.6);">{{ $groupName }}</div>
      </td></tr>

      {{-- Body --}}
      <tr><td style="padding:36px 40px;">
        <p style="font-size:15px;color:#1e3a5f;line-height:1.7;margin:0 0 20px;">
          Hey {{ $user->name }} 👋 — <strong>{{ $grantedBy->name }}</strong> has given you the power to
          @if($permission === 'approve photos')
            <strong>review and approve member photos</strong> for the {{ $groupName }} gallery.
          @else
            <strong>feature photos</strong> on the {{ $groupName }} homepage.
          @endif
        </p>

        @if($permission === 'approve photos')
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f7ff;border-radius:8px;margin-bottom:24px;">
          <tr><td style="padding:20px 24px;">
            <div style="font-size:13px;color:#1e3a5f;line-height:1.9;">
              <div style="margin-bottom:6px;">🔍 <strong>Go to Members → Photo Approval</strong></div>
              <div style="margin-bottom:6px;">✅ Click <strong>L1 Approve</strong> for photos that look good</div>
              <div style="margin-bottom:6px;">✕ Click <strong>Reject</strong> (with a reason) for anything unsuitable</div>
              <div style="color:#6b7f96;font-size:12px;margin-top:8px;">Approved photos are visible to members only — an admin then does a final check before they go public.</div>
            </div>
          </td></tr>
        </table>
        @else
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border-radius:8px;margin-bottom:24px;">
          <tr><td style="padding:20px 24px;">
            <div style="font-size:13px;color:#1e3a5f;line-height:1.9;">
              <div style="margin-bottom:6px;">📷 <strong>Go to Members → Photo Approval → Approved</strong></div>
              <div style="margin-bottom:6px;">⭐ Click <strong>Feature</strong> on any great photo</div>
              <div style="color:#6b7f96;font-size:12px;margin-top:8px;">Featured photos appear on the {{ $groupName }} homepage. Pick ones that show the group at its best!</div>
            </div>
          </td></tr>
        </table>
        @endif

        <div style="text-align:center;">
          <a href="{{ url('/members/photo-approval') }}" style="display:inline-block;background:#C8102E;color:#fff;padding:14px 36px;border-radius:999px;font-weight:700;text-decoration:none;font-size:15px;letter-spacing:-.01em;">
            Get started →
          </a>
        </div>
      </td></tr>

      {{-- Footer --}}
      <tr><td style="background:#f2f5f9;padding:16px 40px;border-top:1px solid #e5e7eb;text-align:center;">
        <p style="font-size:11px;color:#9aa3ae;margin:0;">{{ $groupName }} · RAYNET-UK</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
