@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))

<h1 class="display" style="font-size: 26px;">Diseños</h1>
<p style="color: #777; font-size: 13px; margin: 4px 0 18px;">
    Plantillas globales del catálogo (<code>public_assets</code>). Publicá una para que la vean
    todos los usuarios. El preview es representativo — el render real lo hace la app.
</p>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
@forelse($presets as $p)
    @php($pubRows = $publishedByTitle[$p['title']] ?? [])
    <div class="sb" style="overflow: hidden;">
        <div style="height: 120px; background: {{ $p['preview']['bg'] }};
                    display: flex; align-items: center; justify-content: center;
                    border-bottom: 1.5px solid #111;">
            <span style="font-family: '{{ $p['preview']['titleFont'] }}', sans-serif;
                         color: {{ $p['preview']['titleColor'] }}; font-size: 22px;
                         font-weight: 900; text-align: center; padding: 0 10px;">
                {{ $p['title'] }}
            </span>
        </div>
        <div style="padding: 12px;">
            <div style="font-weight: 800; font-size: 14px;">{{ $p['title'] }}</div>
            <div style="color: #999; font-size: 11px; margin: 2px 0 10px;">
                patrón: {{ $p['preview']['pattern'] ?? '—' }}
                @if(count($pubRows)) · <span style="color: #2a9d2a; font-weight: 700;">publicado</span>@endif
            </div>
            <form method="POST" action="{{ $base }}/designs/{{ $p['key'] }}/publish">
                @csrf
                <button class="btn naranja-bg" style="width: 100%;">
                    {{ count($pubRows) ? 'Publicar otra vez' : 'Publicar' }}
                </button>
            </form>
            @foreach($pubRows as $row)
                @php($isPremium = (int)($row['is_premium'] ?? 1) === 1)
                <div style="margin-top: 10px; font-size: 11px; font-weight: 700;
                            color: {{ $isPremium ? '#e67300' : '#2a9d2a' }};">
                    #{{ $row['id'] }} · {{ $isPremium ? 'Premium (1 anuncio)' : 'Gratis' }}
                </div>
                <form method="POST" action="{{ $base }}/designs/{{ $row['id'] }}/premium" style="margin-top: 4px;">
                    @csrf
                    <input type="hidden" name="premium" value="{{ $isPremium ? 0 : 1 }}">
                    <button class="btn" style="width: 100%;">
                        {{ $isPremium ? 'Hacer gratis' : 'Hacer premium' }}
                    </button>
                </form>
                <form method="POST" action="{{ $base }}/designs/{{ $row['id'] }}/unpublish" style="margin-top: 6px;">
                    @csrf
                    <button class="btn peligro" style="width: 100%;">Despublicar #{{ $row['id'] }}</button>
                </form>
            @endforeach
        </div>
    </div>
@empty
    <p style="color: #999;">No hay presets en <code>resources/design_presets/</code>.</p>
@endforelse
</div>
@endsection
