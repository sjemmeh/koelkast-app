@extends('layouts.beheer')

@section('title', 'Personen Beheer')

@section('content')
    <h1 class="mb-4 text-center display-5">Personen Beheer</h1>

    {{-- Sectie voor het tonen van succes- of foutberichten na een actie --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Formulier voor het toevoegen van een nieuwe persoon --}}
    <section class="mb-5 p-4 bg-body-tertiary rounded shadow-sm border">
        <h2 class="h4 mb-3 border-bottom pb-3">Nieuwe Persoon Toevoegen</h2>
        <form action="{{ route('beheer.personen.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="naam_add_persoon" class="form-label">Naam:</label>
                <input type="text" id="naam_add_persoon" name="naam" value="{{ old('naam') }}" required class="form-control @error('naam') is-invalid @enderror">
                @error('naam')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- Optioneel: Checkbox om direct status in te stellen. --}}
            {{-- De 'actief' status wordt nu standaard op true gezet in het Persoon model bij aanmaken. --}}
            {{-- 
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="actief_add_persoon" name="actief" checked>
                <label class="form-check-label" for="actief_add_persoon">
                    Actief
                </label>
            </div>
            --}}
            <button type="submit" class="btn btn-primary px-4">Persoon Toevoegen</button>
        </form>
    </section>

    <hr class="my-5">

    {{-- Sectie voor het weergeven van bestaande personen --}}
    <section>
        <h2 class="h4 mb-3 border-bottom pb-3">Bestaande Personen</h2>
        @if (isset($personen) && $personen->count() > 0)
            <div class="table-responsive shadow-sm rounded border">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table"> {{-- Thead met standaard achtergrond (afhankelijk van thema) --}}
                        <tr>
                            <th>Naam</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personen as $persoon)
                            <tr>
                                <td>{{ $persoon->naam }}</td>
                                <td class="text-center">
                                    {{-- Toon status (Actief/Inactief) met een gekleurde badge --}}
                                    @if ($persoon->actief)
                                        <span class="badge bg-success">Actief</span>
                                    @else
                                        <span class="badge bg-danger">Inactief</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Formulier om de 'actief' status van een persoon te wijzigen (toggle) --}}
                                    <form action="{{ route('beheer.personen.toggleActief', $persoon->persoon_id) }}" method="POST" class="d-inline me-1">
                                        @csrf
                                        @method('PATCH') {{-- Gebruik PATCH voor gedeeltelijke updates zoals een status toggle --}}
                                        <button type="submit" class="btn btn-sm {{ $persoon->actief ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $persoon->actief ? 'Persoon deactiveren' : 'Persoon activeren' }}">
                                            {{ $persoon->actief ? 'Deactiveren' : 'Activeren' }}
                                        </button>
                                    </form>
                                    
                                    {{-- Formulier om een persoon definitief te verwijderen, met JavaScript bevestigingsdialog --}}
                                    <form action="{{ route('beheer.personen.destroy', $persoon->persoon_id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je {{ htmlspecialchars($persoon->naam, ENT_QUOTES) }} definitief wilt verwijderen? Dit kan niet ongedaan worden gemaakt.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        {{-- De verwijderknop is mogelijk disabled voor inactieve personen als extra veiligheidsmaatregel of business rule. De controller checkt ook op transacties. --}}
                                        <button type="submit" class="btn btn-sm btn-danger" {{ !$persoon->actief ? 'disabled' : '' }} title="{{ !$persoon->actief ? 'Een inactieve persoon kan niet direct verwijderd worden. Activeer eerst indien nodig.' : 'Definitief verwijderen' }}">
                                            Verwijder
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center shadow-sm">
                Er zijn nog geen personen toegevoegd.
            </div>
        @endif
    </section>
@endsection

@push('scripts')
{{-- Voor deze pagina is geen specifieke JavaScript nodig, basisfunctionaliteit wordt afgehandeld door formulieren en server-side logica. --}}
@endpush