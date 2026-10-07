@props(['name'])
<svg {{ $attributes->class(['ui-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>@break
@case('moon')<path d="M20 15.5A9 9 0 0 1 8.5 4 9 9 0 1 0 20 15.5Z"/>@break
@case('login')<path d="M14 3h6v18h-6M3 12h12m-4-4 4 4-4 4"/>@break
@case('logout')<path d="M10 3H4v18h6m2-9h9m-4-4 4 4-4 4"/>@break
@case('eye')<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('database')<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 4 16 4 16 0V5M4 12c0 4 16 4 16 0"/>@break
@case('save')<path d="M5 3h12l4 4v14H3V3h2Z"/><path d="M7 3v6h9V3M7 21v-8h10v8"/>@break
@case('edit')<path d="m16 3 5 5-12 12-6 1 1-6L16 3Zm-2 2 5 5"/>@break
@case('trash')<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>@break
@case('filter')<path d="M3 4h18l-7 8v7l-4 2v-9L3 4Z"/>@break
@case('reset')<path d="M4 9a8 8 0 1 1 0 6M4 3v6h6"/>@break
@case('back')<path d="m10 5-7 7 7 7M3 12h18"/>@break
@case('print')<path d="M7 8V3h10v5M7 17H3V8h18v9h-4M7 14h10v7H7v-7Z"/><circle cx="17.5" cy="10.5" r=".5"/>@break
@case('download')<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>@break
@case('send')<path d="m3 3 18 9-18 9 4-9-4-9Zm4 9h14"/>@break
@case('check')<path d="m5 12 4 4L19 6"/><path d="M21 13v7H3V4h12"/>@break
@case('return')<path d="m8 4-5 5 5 5M3 9h11a6 6 0 0 1 0 12h-4"/>@break
@case('book')<path d="M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Zm0 0v15"/>@break
@case('student')<path d="m2 8 10-5 10 5-10 5L2 8Zm4 2v7c4 3 8 3 12 0v-7M22 8v8"/>@break
@case('teacher')<path d="M10 3h11v11h-8M14 7h3M14 10h3"/><circle cx="6" cy="8" r="3"/><path d="M2 21v-6h8v6M10 16l5-4"/>@break
@case('users')<circle cx="9" cy="7" r="3"/><path d="M3 21v-6a6 6 0 0 1 12 0v6M17 4a3 3 0 0 1 0 6m2 4c2 1 2 4 2 7"/>@break
@case('calendar')<path d="M3 5h18v16H3V5ZM3 10h18M7 3v4m10-4v4M7 14h3m4 0h3m-10 4h3"/>@break
@case('chart')<path d="M3 3v18h18M7 16v-4m5 4V7m5 9V4"/>@break
@case('relations')<rect x="2" y="2" width="6" height="6" rx="1"/><rect x="16" y="16" width="6" height="6" rx="1"/><rect x="16" y="2" width="6" height="6" rx="1"/><path d="M8 5h8M5 8v11h11"/>@break
@case('home')<path d="m3 10 9-7 9 7v11H3V10Zm6 11v-7h6v7"/>@break
@case('monitor')<rect x="2" y="3" width="20" height="14" rx="1"/><path d="M8 21h8m-4-4v4"/>@break
@default<path d="M3 4h18v16H3V4ZM7 8h10M7 12h7M7 16h4"/>
@endswitch
</svg>
