{* Campaigns detail action / hero icons *}
{strip}
{if $ICON eq 'MEGAPHONE'}
<svg class="mk-camp-detail-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<path d="M4 10v4c0 .55.45 1 1 1h1l4 4V4L6 10H5c-.55 0-1 .45-1 1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
	<path d="M15 8.5c1.25 1.2 2 2.85 2 4.5s-.75 3.3-2 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
{elseif $ICON eq 'FOLLOW'}
<svg class="mk-camp-detail-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
</svg>
{elseif $ICON eq 'DUPLICATE'}
<svg class="mk-camp-detail-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="1.75"/>
	<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.75"/>
</svg>
{elseif $ICON eq 'DELETE'}
<svg class="mk-camp-detail-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
	<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
</svg>
{/if}
{/strip}
