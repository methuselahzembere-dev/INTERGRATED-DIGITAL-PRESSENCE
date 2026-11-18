{{-- Circular emblem + party name. Sidebar: compact. Login (.fi-simple-layout): large, stacked, with the motto. See brand-head.blade.php for the styles. --}}
<div class="zp-brand">
    <span class="zp-seal">
        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('branding.party_name') }} emblem">
    </span>
    <span class="zp-brand-text">
        <span class="zp-brand-name">{{ config('branding.party_name') }}</span>
        <span class="zp-brand-sub">Integrated Digital Presence</span>
        <span class="zp-brand-motto">{{ config('branding.motto') }}</span>
    </span>
</div>
