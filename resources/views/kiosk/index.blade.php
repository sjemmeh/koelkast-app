@extends('layouts.kiosk')

@section('title', 'Drank Kiosk')

@push('styles')
<style>
    /* Pagina-specifieke CSS voor de kiosk-interface.
       De algemene body styling (centreren) wordt door kiosk-custom.css afgehandeld.
       Deze stijlen zijn overrides of toevoegingen specifiek voor deze pagina. */

    .kiosk-main-container {
        width: 100%;
        max-width: 480px; /* Compacte breedte, geschikt voor kiosk displays. */
    }

    /* Styling voor de hoofd 'kaart' van de kiosk. */
    .kiosk-card {
        padding: 1.5rem; /* Ruimere padding binnen de kaart. */
        width:100%;
    }

    /* Grotere, beter leesbare labels voor formulierelementen. */
    .kiosk-card .form-label {
        font-size: 0.95rem;
        margin-bottom: .5rem;
    }

    /* Grotere inputvelden en selectboxen voor betere touch-interactie. */
    .kiosk-card .form-control, 
    .kiosk-card .form-select {
        min-height: calc(1.5em + 1rem + 2px); /* Verhoogde minimale hoogte. */
        font-size: 1.05rem; /* Groter lettertype in de velden. */
    }

    /* Grotere knoppen voor duidelijkere acties. */
    .kiosk-card .btn {
        padding: 0.7rem 1.1rem;
        font-size: 1.05rem;
    }

    /* Extra ruimte onder prijsselectieknoppen als ze via d-grid onder elkaar staan. */
    .price-buttons .btn {
        margin-bottom: 0.5rem;
    }

    /* Styling voor het informatieblok over een gescand drankje. */
    .drink-info-kiosk {
        /* Achtergrond en randen worden idealiter via Bootstrap variabelen (--bs-body-bg, --bs-tertiary-bg, --bs-border-color) 
           en het actieve thema (light/dark) geregeld voor consistentie.
           Voorbeeld: background-color: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color); */
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: .375rem; /* Standaard Bootstrap border-radius. */
    }
    .drink-info-kiosk p { 
        margin-bottom: 0.5rem; /* Consistentie in paragraaf-afstand. */
    }
</style>
@endpush

@section('content')
<div class="kiosk-main-container">
    {{-- Hoofdkaart-container voor de kiosk interface, gestyled met Bootstrap .card en .shadow-lg. --}}
    <div class="card shadow-lg kiosk-card">
        <div class="card-body">
            <h1 class="card-title text-center h2 mb-4">Drank Kiosk</h1>

            {{-- Sectie voor het tonen van (flash)berichten: succes, fout, of validatiefouten --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <p class="fw-bold mb-1">Oeps! Er ging iets mis:</p>
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- STAP 1: Barcode Invoeren (Initiële Staat van de Kiosk) --}}
            @if ($kioskState == 'initial')
                <h3 class="h5 mb-3 text-center">Scan Barcode</h3>
                <form action="{{ route('kiosk.processBarcode') }}" method="POST" id="scanBarcodeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="barcode_scan_input" class="form-label sr-only">Barcode:</label> {{-- sr-only voor accessibility, label is visueel verborgen --}}
                        <input type="text" id="barcode_scan_input" name="barcode" 
                               value="{{ old('barcode', $kioskData['scanned_barcode'] ?? '') }}" 
                               autofocus {{-- Automatische focus voor directe input via scanner --}}
                               required 
                               class="form-control form-control-lg text-center @error('barcode') is-invalid @enderror" 
                               placeholder="Scan barcode hier...">
                        @error('barcode') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verwerk Barcode</button>
                </form>
            @endif

            {{-- STAP 2A: Bevestig Bekend Drankje & Selecteer Persoon --}}
            {{-- Wordt getoond als een barcode is gescand en herkend als een bekend, actief drankje. --}}
            @if ($kioskState == 'confirm_drink' && isset($kioskData['drank_id']))
                <h3 class="h5 mb-3 text-center">Bevestig Drankje</h3>
                <div class="p-3 mb-3 rounded drink-info-kiosk text-center bg-body-tertiary border"> {{-- Informatie over het gescande drankje --}}
                    <p class="h5 mb-1"><strong>{{ $kioskData['naam_drank'] ?? 'N/A' }}</strong></p>
                    <p class="h6 mb-2 text-success fw-bold">€ {{ number_format($kioskData['prijs'] ?? 0, 2, ',', '.') }}</p>
                    @if(isset($kioskData['scanned_barcode']))
                        <p class="mb-0 mt-1"><small class="text-body-secondary">Barcode: {{ htmlspecialchars($kioskData['scanned_barcode']) }}</small></p> {{-- text-body-secondary voor subtiel contrast --}}
                    @endif
                </div>
                <form action="{{ route('kiosk.finalizeTransaction') }}" method="POST" id="confirmDrinkForm">
                    @csrf
                    {{-- Verborgen velden om de context van de transactie door te geven --}}
                    <input type="hidden" name="drank_id" value="{{ $kioskData['drank_id'] }}">
                    <input type="hidden" name="current_kiosk_state" value="confirm_drink"> {{-- Huidige state voor eventuele foutafhandeling/terugkeer --}}
                    <input type="hidden" name="current_kiosk_data_json" value="{{ htmlspecialchars(json_encode($kioskData)) }}"> {{-- Volledige kiosk data voor herpopulatie bij fouten --}}

                    <div class="mb-3">
                        <label for="persoon_id_confirm" class="form-label">Wie ben je?</label>
                        <select id="persoon_id_confirm" name="persoon_id" required class="form-select form-select-lg @error('persoon_id') is-invalid @enderror">
                            <option value="">-- Selecteer je naam --</option>
                            @foreach ($personen as $persoon)
                                <option value="{{ $persoon->persoon_id }}" {{ old('persoon_id') == $persoon->persoon_id ? 'selected' : '' }}>
                                    {{ $persoon->naam }}
                                </option>
                            @endforeach
                        </select>
                        @error('persoon_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100">Aankoop Bevestigen</button>
                    <a href="{{ route('kiosk.index') }}" class="btn btn-outline-secondary w-100 mt-2">Annuleren</a> {{-- Reset de kiosk flow --}}
                </form>
            @endif

            {{-- STAP 2B: Barcode Onbekend - Selecteer Prijs & Persoon --}}
            {{-- Wordt getoond als de gescande barcode niet herkend wordt. --}}
            @if ($kioskState == 'select_price' && isset($kioskData['scanned_barcode']))
                <h3 class="h5 mb-3 text-center">Barcode Onbekend</h3>
                <div class="p-3 mb-3 rounded drink-info-kiosk text-center bg-body-tertiary border">
                    <p>Barcode <strong>{{ htmlspecialchars($kioskData['scanned_barcode']) }}</strong> is niet gevonden.</p>
                    <p class="mb-0">Selecteer hieronder een prijs en geef aan wie je bent.</p>
                </div>

                <form action="{{ route('kiosk.finalizeTransaction') }}" method="POST" id="unknownDrinkForm">
                    @csrf
                    {{-- Verborgen velden om de context van de transactie door te geven --}}
                    <input type="hidden" name="scanned_barcode_for_unknown" value="{{ htmlspecialchars($kioskData['scanned_barcode']) }}">
                    <input type="hidden" name="current_kiosk_state" value="select_price"> {{-- Huidige state voor eventuele foutafhandeling/terugkeer --}}
                    <input type="hidden" name="current_kiosk_data_json" value="{{ htmlspecialchars(json_encode($kioskData)) }}"> {{-- Volledige kiosk data voor herpopulatie bij fouten --}}

                    <div class="mb-3">
                        <label for="persoon_id_unknown" class="form-label">Wie ben je?</label>
                        <select id="persoon_id_unknown" name="persoon_id" required class="form-select form-select-lg @error('persoon_id') is-invalid @enderror">
                            <option value="">-- Selecteer je naam --</option>
                            @foreach ($personen as $persoon)
                                <option value="{{ $persoon->persoon_id }}" {{ old('persoon_id') == $persoon->persoon_id ? 'selected' : '' }}>
                                    {{ $persoon->naam }}
                                </option>
                            @endforeach
                        </select>
                         @error('persoon_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <label class="form-label mt-3 text-center d-block">Kies een prijs:</label>
                    {{-- Knoppen voor prijsselectie; elke knop submit het formulier met de geselecteerde prijs ('key') --}}
                    <div class="price-buttons d-grid gap-2"> 
                        @if(isset($selectablePrices) && count($selectablePrices) > 0)
                            @foreach ($selectablePrices as $priceInfo)
                                 <button type="submit" name="selected_price_key" value="{{ $priceInfo['key'] }}" class="btn btn-info w-100">
                                     {{ $priceInfo['display'] }}
                                 </button>
                            @endforeach
                        @else
                            <p class="text-body-secondary text-center">Geen prijzen beschikbaar voor selectie.</p> {{-- text-body-secondary voor subtiel contrast --}}
                        @endif
                    </div>
                </form>
                <a href="{{ route('kiosk.index') }}" class="btn btn-outline-secondary w-100 mt-3">Annuleren</a> {{-- Reset de kiosk flow --}}
            @endif
        </div>
    </div>
    {{-- Een eventuele link naar beheer/admin is hier niet geplaatst om de kiosk-interface simpel te houden. --}}
</div>
@endsection

@push('scripts')
<script>
// JavaScript voor basisinteractie op de kioskpagina, met name autofocus-logica.
document.addEventListener('DOMContentLoaded', function() {
    // Haal het barcode input veld op.
    const barcodeScanInput = document.getElementById('barcode_scan_input');
    
    // Injecteer de huidige kiosk-staat veilig vanuit Blade naar JavaScript.
    const currentKioskState = @json($kioskState ?? 'initial'); 
    
    // Controleer of er validatiefouten zijn (algemeen en specifiek voor 'persoon_id').
    const errorsExist = {{ $errors->any() ? 'true' : 'false' }};
    const personIdErrorExists = {{ $errors->has('persoon_id') ? 'true' : 'false' }};

    // Focus op het barcode-scanveld als de kiosk in de initiële staat is.
    if (barcodeScanInput && currentKioskState === 'initial') {
        barcodeScanInput.focus();
    }

    // Als er validatiefouten zijn en specifiek een fout bij het selecteren van een persoon,
    // focus dan op het relevante persoon-selectieveld, afhankelijk van de kiosk-staat.
    if (errorsExist && personIdErrorExists) {
        if (currentKioskState === 'confirm_drink') {
            const personSelectConfirm = document.getElementById('persoon_id_confirm');
            if(personSelectConfirm) personSelectConfirm.focus();
        } else if (currentKioskState === 'select_price') {
            const personSelectUnknown = document.getElementById('persoon_id_unknown');
            if(personSelectUnknown) personSelectUnknown.focus();
        }
    }
});
</script>
@endpush