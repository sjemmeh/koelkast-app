@extends('layouts.beheer') {{-- Maakt gebruik van de hoofdlayout voor het beheergedeelte --}}

@section('title', 'Transactieoverzicht') {{-- Paginatitel specifiek voor deze view --}}

@push('styles')
{{-- Hier kan eventuele specifieke CSS voor alleen deze transactieoverzichtpagina. --}}
@endpush

@section('content')
    <h1 class="mb-3 text-center display-5">Transactieoverzicht</h1>
    
    {{-- Formulier voor het selecteren van de maand voor het transactieoverzicht --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-body-tertiary p-3"> {{-- bg-body-tertiary voor een achtergrond die zich aanpast aan het thema --}}
            <form method="GET" action="{{ route('beheer.transacties.index') }}" class="row gx-2 gy-2 align-items-center">
                <div class="col-auto">
                    <label for="maand_selectie_input" class="form-label visually-hidden">Selecteer Maand:</label>
                </div>
                <div class="col-auto">
                    {{-- Inputveld voor maandselectie (JJJJ-MM), voorgevuld met de huidige selectie --}}
                    <input type="month" id="maand_selectie_input" name="maand_selectie" value="{{ $geselecteerdeMaandVoorInput ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Toon Overzicht</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Subtitel die de geselecteerde periode voor het overzicht aangeeft --}}
    <h2 class="h5 mb-4 text-center text-muted fw-normal">Overzicht per persoon voor {{ $periode }}</h2>

    {{-- Weergave van succes- of foutberichten via de sessie --}}
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

    {{-- Hoofdtabel met het transactieoverzicht per persoon --}}
    @if (isset($overzichtPerPersoon) && $overzichtPerPersoon->isNotEmpty())
        <div class="table-responsive shadow-sm rounded border">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table"> {{-- Standaard tabelhoofd styling, past zich aan het actieve thema aan --}}
                    <tr>
                        <th>Naam Persoon</th>
                        <th class="text-end">Aantal Transacties</th>
                        <th class="text-end">Totaalbedrag</th>
                        {{-- Uitgecommentarieerd: Mogelijke kolom voor een "Details" knop per persoon --}}
                        {{-- <th class="text-center">Details</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overzichtPerPersoon as $data)
                        <tr>
                            <td>
                                {{-- Link om de modal met gedetailleerde transacties voor deze persoon te openen --}}
                                {{-- data-* attributen worden gebruikt door JavaScript om de modal te vullen --}}
                                <a href="#" class="view-persoon-transacties" {{-- Link styling via Bootstrap; text-decoration-none is standaard in BS5 voor links --}}
                                   data-persoon-id="{{ $data->persoon_id }}"
                                   data-persoon-naam="{{ htmlspecialchars($data->persoon_naam, ENT_QUOTES) }}"
                                   data-start-datum="{{ $startDatumPeriode }}"
                                   data-eind-datum="{{ $eindDatumPeriode }}"
                                   data-bs-toggle="modal" 
                                   data-bs-target="#persoonTransactiesModalBootstrap">
                                    {{ $data->persoon_naam }}
                                </a>
                            </td>
                            <td class="text-end">{{ $data->aantal_transacties }}</td>
                            <td class="text-end">€ {{ number_format($data->totaal_bedrag, 2, ',', '.') }}</td>
                            {{-- Uitgecommentarieerd: Mogelijke "Bekijk" knop die dezelfde modal opent als de naam-link --}}
                            {{-- 
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm view-persoon-transacties"
                                    data-persoon-id="{{ $data->persoon_id }}"
                                    data-persoon-naam="{{ htmlspecialchars($data->persoon_naam, ENT_QUOTES) }}"
                                    data-start-datum="{{ $startDatumPeriode }}"
                                    data-eind-datum="{{ $eindDatumPeriode }}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#persoonTransactiesModalBootstrap">
                                    Bekijk
                                </button>
                            </td>
                            --}}
                        </tr>
                    @endforeach
                </tbody>
                {{-- Tabelvoet met het totaalsaldo van alle personen in de getoonde periode --}}
                @if($overzichtPerPersoon->count() > 0)
                <tfoot class="table-group-divider"> {{-- Bootstrap class voor een duidelijke scheidingslijn boven de footer --}}
                    <tr class="fw-bold table"> {{-- Styling voor de totaalsrij, consistent met tabelhoofd --}}
                        <td colspan="2" class="text-end">Totaal Alle Personen:</td>
                        <td class="text-end">€ {{ number_format($overzichtPerPersoon->sum('totaal_bedrag'), 2, ',', '.') }}</td>
                        {{-- Lege cel indien de "Details" kolom actief zou zijn --}}
                        {{-- <td></td> --}}
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    @else
        {{-- Melding als er geen transacties zijn voor de geselecteerde periode --}}
        <div class="alert alert-info text-center shadow-sm" role="alert">
            Geen transacties gevonden voor de periode {{ $periode }}.
        </div>
    @endif

    {{-- Modal voor het tonen van de gedetailleerde transacties van een specifieke persoon --}}
    <div class="modal fade" id="persoonTransactiesModalBootstrap" tabindex="-1" aria-labelledby="persoonTransactiesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"> {{-- Grotere, scrollbare modal voor potentieel lange lijsten --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h5" id="persoonTransactiesModalLabel">Transacties voor [Persoon Naam]</h5> {{-- Titel wordt dynamisch aangepast door JS --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Container waar JavaScript de transactietabel of een laadbericht plaatst --}}
                    <div id="persoonTransactiesModalBodyContent">
                        <p class="text-center text-muted p-3">Laden...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- Ruimte voor foutmeldingen of andere berichten in de modal footer, links uitgelijnd --}}
                    <div id="persoonTransactiesModalMessages" class="w-100 text-center small me-auto"></div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Sluiten</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// JavaScript voor de interactieve functionaliteit op de transactieoverzichtpagina,
// met name het laden en weergeven van gedetailleerde transacties in een modal.
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token voor beveiligde AJAX POST/PUT/DELETE requests (hier niet direct gebruikt, maar goede practice).
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken && document.querySelector('form[method="POST"], form[method="PUT"], form[method="DELETE"]')) {
        console.warn('CSRF Token meta tag niet gevonden! API calls die CSRF protectie vereisen kunnen falen.');
    }

    /**
     * Helper functie voor het uitvoeren van API calls via Fetch.
     * @param {string} url - De URL voor de API call.
     * @param {string} [method='GET'] - De HTTP methode (GET, POST, PUT, DELETE, etc.).
     * @param {object|null} [body=null] - De request body voor POST/PUT requests (als JSON).
     * @returns {Promise<object>} De JSON response data van de API.
     * @throws {Error} Als de API call faalt, een non-OK status retourneert, of de response geen JSON is.
     */
    async function apiCall(url, method = 'GET', body = null) {
        const headers = { 'Accept': 'application/json' };
        if (csrfToken && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        if (method !== 'GET' && method !== 'HEAD') { headers['Content-Type'] = 'application/json'; }
        
        const options = { method, headers };
        if (body && (method === 'POST' || method === 'PUT')) { options.body = JSON.stringify(body); }
        
        const response = await fetch(url, options);
        const responseData = await response.json().catch(() => ({ message: 'Antwoord van server kon niet als JSON verwerkt worden.' , errors: {} }));
        
        if (!response.ok) {
            const error = new Error(responseData.message || `HTTP error! Status: ${response.status}`);
            error.errors = responseData.errors || {}; 
            error.status = response.status; 
            throw error;
        }
        return responseData;
    }

    /**
     * Helper functie om speciale HTML karakters te escapen om XSS te voorkomen bij het invoegen van tekst in HTML.
     * @param {*} unsafe - De input string of waarde die ge-escaped moet worden.
     * @returns {string} De ge-escapede string.
     */
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return ''; // Handel null/undefined af
        return unsafe.toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // --- Persoon Transacties Modal Functionaliteit ---
    const persoonTransactiesModalEl = document.getElementById('persoonTransactiesModalBootstrap');
    // Initialisatie van de Bootstrap Modal instance kan hier als je later direct modal methods (zoals .hide()) wilt aanroepen.
    // const persoonTransactiesBootstrapModal = persoonTransactiesModalEl ? new bootstrap.Modal(persoonTransactiesModalEl) : null; 
    const persoonTransactiesModalTitle = document.getElementById('persoonTransactiesModalLabel');
    const persoonTransactiesModalBodyContent = document.getElementById('persoonTransactiesModalBodyContent');
    const persoonTransactiesModalMessages = document.getElementById('persoonTransactiesModalMessages');

    /**
     * Toont een bericht (succes/fout/info) onderin de "Persoon Transacties" modal.
     * @param {string} message - Het te tonen bericht.
     * @param {string} [type='danger'] - Het type bericht ('success', 'info', 'danger', etc.).
     */
    function displayPersoonTransactiesModalMessage(message, type = 'danger') {
        const alertType = type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : `alert-${type}`); // Fallback op type als class
        persoonTransactiesModalMessages.innerHTML = `<div class="alert ${alertType} py-1 px-2 small mb-0">${message}</div>`;
        if(type !== 'info') { // 'info' berichten (zoals "Laden...") niet automatisch verbergen
            setTimeout(() => persoonTransactiesModalMessages.innerHTML = '', 5000); // Verberg na 5 seconden
        }
    }
    
    /**
     * Laadt en toont de transacties voor een specifieke persoon binnen een periode in de modal.
     * @param {string|number} persoonId - Het ID van de persoon.
     * @param {string} persoonNaam - De naam van de persoon (voor de modal titel).
     * @param {string} startDatum - De startdatum van de periode (YYYY-MM-DD).
     * @param {string} eindDatum - De einddatum van de periode (YYYY-MM-DD).
     */
    async function loadPersoonTransacties(persoonId, persoonNaam, startDatum, eindDatum) {
        if (!persoonTransactiesModalEl) return; // Doe niets als de modal niet bestaat

        // Update modal titel en toon laadbericht
        persoonTransactiesModalTitle.textContent = `Transacties voor ${escapeHtml(persoonNaam)}`;
        persoonTransactiesModalBodyContent.innerHTML = '<p class="text-center text-muted p-3">Laden...</p>';
        persoonTransactiesModalMessages.innerHTML = ''; // Leeg eventuele vorige berichten

        try {
            // Bouw de API URL en haal de transactiedata op.
            const apiUrl = `/api/personen/${persoonId}/transacties?start_datum=${startDatum}&eind_datum=${eindDatum}`;
            const data = await apiCall(apiUrl);

            if (data.transacties && data.transacties.length > 0) {
                // Bouw de HTML voor de transactietabel
                let tableHtml = '<div class="table-responsive"><table class="table table-sm table-striped table-hover mb-0"><thead><tr><th>Datum & Tijd</th><th>Omschrijving</th><th class="text-end">Prijs</th></tr></thead><tbody>';
                data.transacties.forEach(tx => {
                    const txDate = new Date(tx.transactie_datum_tijd);
                    // Formatteer datum/tijd naar een lokaal leesbaar formaat (Nederlands)
                    const formattedDate = txDate.toLocaleString('nl-NL', { 
                        day: '2-digit', month: '2-digit', year: 'numeric', 
                        hour: '2-digit', minute: '2-digit' 
                    });
                    // Formatteer prijs naar een lokaal valutaformaat (Euro)
                    const prijs = parseFloat(tx.prijs_ten_tijde_van_transactie).toFixed(2).replace('.', ',');

                    tableHtml += `<tr>
                        <td>${formattedDate}</td>
                        <td>${escapeHtml(tx.omschrijving_ten_tijde_van_transactie)}</td>
                        <td class="text-end">€ ${prijs}</td>
                    </tr>`;
                });
                tableHtml += '</tbody></table></div>';
                persoonTransactiesModalBodyContent.innerHTML = tableHtml;
            } else {
                persoonTransactiesModalBodyContent.innerHTML = '<p class="text-center text-muted p-3">Geen transacties gevonden voor deze persoon in deze periode.</p>';
            }
        } catch (error) {
            console.error("Fout bij ophalen transacties persoon:", error);
            persoonTransactiesModalBodyContent.innerHTML = '<p class="text-center text-danger p-3">Kon transacties niet laden.</p>';
            displayPersoonTransactiesModalMessage(`Fout: ${error.message || 'Onbekende API fout.'}`, 'danger');
        }
    }

    // Event listener voor het 'show.bs.modal' event. Dit vuurt wanneer de modal op het punt staat getoond te worden.
    // Gebruikt om de transactiedata te laden op basis van de data-* attributen van de triggerende link/knop.
    if (persoonTransactiesModalEl) {
        persoonTransactiesModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Het element (link/knop) dat de modal opende
            // Controleer of de modal geopend is door een element met de class 'view-persoon-transacties'
            if (button && button.classList.contains('view-persoon-transacties')) { 
                const persoonId = button.dataset.persoonId;
                const persoonNaam = button.dataset.persoonNaam;
                const startDatum = button.dataset.startDatum;
                const eindDatum = button.dataset.eindDatum;
                
                // Alleen data laden als alle benodigde informatie aanwezig is in de data attributen.
                if (persoonId && persoonNaam && startDatum && eindDatum) {
                    loadPersoonTransacties(persoonId, persoonNaam, startDatum, eindDatum);
                } else {
                    console.error("Ontbrekende data attributen op modal trigger voor transacties.", button.dataset);
                    persoonTransactiesModalBodyContent.innerHTML = '<p class="text-center text-danger p-3">Kon transacties niet laden: benodigde informatie ontbreekt.</p>';
                }
            }
        });
    }
});
</script>
@endpush