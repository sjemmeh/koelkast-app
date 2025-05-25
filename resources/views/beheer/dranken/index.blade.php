@extends('layouts.beheer')

@section('title', 'Dranken Beheer')

@push('styles')
{{-- Hier kan ZEER specifieke CSS voor alleen deze drankenbeheerpagina geplaatst worden. --}}
@endpush

@section('content')
    <h1 class="mb-4 text-center display-5">Dranken Beheer</h1>

    {{-- Sectie voor het tonen van succes-, fout- of validatieberichten --}}
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
    {{-- Toon algemene validatiefouten alleen als er geen specifiek succes- of foutbericht is --}}
    @if ($errors->any() && !session('success') && !session('error'))
        <div class="alert alert-danger shadow-sm" role="alert">
            <p class="fw-bold">Oeps! Er ging iets mis met de invoer:</p>
            <ul class="mt-2 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulier voor het toevoegen van een nieuw drankje --}}
    <section class="mb-5 p-4 bg-body-tertiary rounded shadow-sm border"> {{-- bg-body-tertiary voor theming-compatibele achtergrond --}}
        <h2 class="h4 mb-3 border-bottom pb-3">Nieuw Drankje Toevoegen</h2>
        <form action="{{ route('beheer.dranken.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="naam_drank_add" class="form-label">Naam Drankje:</label>
                <input type="text" id="naam_drank_add" name="naam_drank" value="{{ old('naam_drank') }}" required class="form-control @error('naam_drank') is-invalid @enderror">
                @error('naam_drank') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="barcode_add" class="form-label">Hoofd Barcode (optioneel):</label>
                <div class="input-group">
                    <input type="text" id="barcode_add" name="barcode" value="{{ old('barcode') }}" class="form-control @error('barcode') is-invalid @enderror">
                    {{-- Knop om dynamisch extra barcodevelden toe te voegen via JavaScript --}}
                    <button type="button" id="addBarcodeFieldBtn" class="btn btn-success" title="Extra barcode toevoegen">+</button>
                </div>
                @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Container voor dynamisch toegevoegde barcodevelden --}}
            <div id="additionalBarcodesContainer" class="mb-3">
                {{-- Herstel eventuele 'oude' extra barcodes na een validatiefout --}}
                @if(is_array(old('additional_barcodes')))
                    @foreach(old('additional_barcodes') as $i => $oldBarcode)
                        @if(!empty($oldBarcode))
                        <div class="input-group input-group-sm mb-2 barcode-input-group">
                            <input type="text" name="additional_barcodes[]" placeholder="Extra barcode" value="{{ $oldBarcode }}" class="form-control">
                            <button type="button" class="btn btn-danger btn-sm remove-barcode-btn" title="Verwijder deze barcode">&times;</button>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
            @error('additional_barcodes.*') <div class="text-danger small mb-2 d-block">{{ $message }}</div> @enderror

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="categorie_id_add" class="form-label">Categorie:</label>
                    <div class="input-group">
                        <select id="categorie_id_add" name="categorie_id" required class="form-select @error('categorie_id') is-invalid @enderror">
                            <option value="">-- Selecteer Categorie --</option>
                            @foreach ($categorieen as $categorie)
                                <option value="{{ $categorie->categorie_id }}" {{ old('categorie_id') == $categorie->categorie_id ? 'selected' : '' }}>
                                    {{ $categorie->naam }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Knop om de categoriebeheer modal te openen --}}
                        <button type="button" id="manageCategoriesBtnBootstrap" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#categoriesModalBootstrap" title="Beheer Categorieën">Beheer</button>
                    </div>
                    @error('categorie_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="prijs_add" class="form-label">Prijs:</label>
                    <div class="input-group">
                        <span class="input-group-text">€</span>
                        <input type="number" step="0.01" id="prijs_add" name="prijs" value="{{ old('prijs') }}" required placeholder="0.00" class="form-control @error('prijs') is-invalid @enderror">
                    </div>
                    @error('prijs') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="tht_datum_add" class="form-label">THT Datum:</label>
                <input type="month" id="tht_datum_add" name="tht_datum" value="{{ old('tht_datum') }}" required class="form-control @error('tht_datum') is-invalid @enderror" title="Jaar en maand">
                @error('tht_datum') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary px-4">Drankje Toevoegen</button>
        </form>
    </section>

    <hr class="my-5">

    {{-- Sectie voor het weergeven van bestaande drankjes --}}
    <section>
        <h2 class="h4 mb-3 border-bottom pb-3">Bestaande Drankjes</h2>
        @if ($dranken->count() > 0)
            <div class="table-responsive shadow-sm rounded border">
                {{-- table-striped voor betere leesbaarheid; werkt goed met standaard rijkleuren en de THT statuskleuren. --}}
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table"> {{-- Thead met standaard achtergrond (afhankelijk van thema) --}}
                        <tr>
                            <th>Naam</th>
                            <th>Barcodes</th>
                            <th>Categorie</th>
                            <th class="text-end">Prijs</th>
                            <th>THT</th>
                            <th class="text-center">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dranken as $drank)
                            @php
                                // Bepaal THT status en bijbehorende styling
                                $thtDate = $drank->tht_datum ? \Carbon\Carbon::parse($drank->tht_datum)->startOfDay() : null;
                                $now = \Carbon\Carbon::now()->startOfDay();
                                $oneMonthFromNow = $now->copy()->addMonth();
                                $twoMonthsFromNow = $now->copy()->addMonths(2);

                                $isExpired = $thtDate && $thtDate->isPast() && !$thtDate->isToday();
                                $isNearExpiryCritical = false;
                                $isNearExpiryWarning = false;
                                if ($thtDate && !$isExpired && $thtDate->isFuture()) {
                                    if ($thtDate->isBefore($oneMonthFromNow)) { $isNearExpiryCritical = true; } // Kritiek: binnen 1 maand THT
                                    elseif ($thtDate->isBefore($twoMonthsFromNow)) { $isNearExpiryWarning = true; } // Waarschuwing: binnen 2 maanden THT
                                }
                                
                                // Bepaal de CSS class voor de rij en de tekst/badge voor THT status.
                                // Bootstrap's dark mode past de weergave van table-danger, -warning, -info automatisch aan.
                                $rowClass = ''; 
                                $thtStatusText = ''; $thtBadgeClass = '';
                                if ($isExpired) { $rowClass = 'table-danger'; $thtStatusText = 'OVER DATUM'; $thtBadgeClass = 'text-bg-danger'; }
                                elseif ($isNearExpiryCritical) { $rowClass = 'table-warning'; $thtStatusText = '< 1 MND'; $thtBadgeClass = 'text-bg-warning'; }
                                elseif ($isNearExpiryWarning) { $rowClass = 'table-info'; $thtStatusText = '< 2 MND'; $thtBadgeClass = 'text-bg-info'; }
                            @endphp
                            <tr class="{{ $rowClass }}"> {{-- Toon THT status via rij achtergrondkleur --}}
                                <td>
                                    {{ $drank->naam_drank }}
                                    {{-- Toon THT status als badge naast de naam --}}
                                    @if($thtStatusText) <span class="badge rounded-pill {{ $thtBadgeClass }} tht-status-badge ms-1">{{ $thtStatusText }}</span> @endif
                                </td>
                                <td class="small">
                                    {{-- Toon alle product barcodes; markeer de hoofdbarcode (indien aanwezig op Drank model) --}}
                                    @if($drank->productBarcodes && $drank->productBarcodes->isNotEmpty())
                                        <ul class="list-unstyled mb-0">
                                            @foreach($drank->productBarcodes as $pb)
                                                <li>
                                                    {{ $pb->barcode_value }}
                                                    @if($drank->barcode == $pb->barcode_value)
                                                        <span class="badge bg-primary lh-1">Hoofd</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif($drank->barcode) {{-- Fallback als alleen de hoofdbarcode op Drank model bestaat --}}
                                        {{ $drank->barcode }} <span class="badge bg-primary lh-1">Hoofd</span>
                                    @else - @endif
                                </td>
                                <td>{{ $drank->categorie->naam ?? 'N/A' }}</td>
                                <td class="text-end">€ {{ number_format($drank->prijs, 2, ',', '.') }}</td>
                                <td>{{ $drank->tht_datum ? \Carbon\Carbon::parse($drank->tht_datum)->format('m/Y') : '-' }}</td>
                                <td class="text-center">
                                    {{-- Knop om de 'Drankje Bewerken' modal te openen --}}
                                    <button type="button" class="btn btn-sm btn-warning edit-drank-btn" data-id="{{ $drank->drank_id }}" data-bs-toggle="modal" data-bs-target="#editDrankModalBootstrap">Bewerken</button>
                                    {{-- Formulier voor het direct verwijderen van een drankje, met bevestiging --}}
                                    <form action="{{ route('beheer.dranken.destroy', $drank->drank_id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je {{ htmlspecialchars($drank->naam_drank, ENT_QUOTES) }} wilt verwijderen?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Verwijder</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-muted py-4">Er zijn nog geen drankjes toegevoegd.</p>
        @endif
    </section>

    {{-- Modal voor het beheren van categorieën (toevoegen, verwijderen) --}}
    <div class="modal fade" id="categoriesModalBootstrap" tabindex="-1" aria-labelledby="categoriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h5" id="categoriesModalLabel">Categorieën Beheren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="categoryListContainerBootstrap" class="mb-3">
                        <h6>Bestaande Categorieën:</h6>
                        <ul id="categoryListULBootstrap" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            {{-- Categorie lijst wordt dynamisch gevuld door JavaScript --}}
                        </ul>
                    </div>
                    <hr class="my-3">
                    <div>
                        <h6>Nieuwe Categorie Toevoegen:</h6>
                        <div class="mb-2">
                            <label for="newCategoryNameBootstrap" class="form-label visually-hidden">Naam:</label>
                            <input type="text" id="newCategoryNameBootstrap" placeholder="Categorienaam" class="form-control form-control-sm">
                        </div>
                        <button type="button" id="addCategoryBtnBootstrap" class="btn btn-primary btn-sm w-100">Toevoegen</button>
                    </div>
                    {{-- Ruimte voor berichten binnen de categorie modal --}}
                    <div id="categoryModalMessagesBootstrap" class="mt-3 text-center small"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal voor het bewerken van een bestaand drankje --}}
    <div class="modal fade" id="editDrankModalBootstrap" tabindex="-1" aria-labelledby="editDrankModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- Grotere modal voor het bewerkformulier --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h5" id="editDrankModalLabel">Drankje Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDrankFormBootstrap">
                        <input type="hidden" id="editDrankIdBootstrap" name="drank_id"> {{-- Verborgen veld voor drank_id --}}
                        
                        <div class="mb-3">
                            <label for="editDrankNaamBootstrap" class="form-label">Naam:</label>
                            <input type="text" id="editDrankNaamBootstrap" name="naam_drank" required class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label for="editDrankBarcodeBootstrap" class="form-label">Hoofd Barcode:</label>
                            <div class="input-group">
                                <input type="text" id="editDrankBarcodeBootstrap" name="barcode" class="form-control">
                                <button type="button" id="addBarcodeFieldBtnEditBootstrap" class="btn btn-success btn-sm" title="Extra barcode toevoegen">+</button>
                            </div>
                        </div>
                        {{-- Container voor dynamische extra barcodevelden in de bewerkmodal --}}
                        <div id="additionalBarcodesContainerEditBootstrap" class="mb-3" style="max-height: 120px; overflow-y: auto;">
                            {{-- Extra barcode velden worden dynamisch gevuld door JavaScript --}}
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label for="editDrankCategorieBootstrap" class="form-label">Categorie:</label>
                                <select id="editDrankCategorieBootstrap" name="categorie_id" required class="form-select">
                                    {{-- Categorie opties worden dynamisch gevuld door JavaScript --}}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editDrankPrijsBootstrap" class="form-label">Prijs:</label>
                                 <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" step="0.01" id="editDrankPrijsBootstrap" name="prijs" required placeholder="0.00" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editDrankThtBootstrap" class="form-label">THT Datum:</label>
                            <input type="month" id="editDrankThtBootstrap" name="tht_datum" required class="form-control" title="Jaar en maand">
                        </div>
                        <button type="submit" id="saveDrankChangesBtnBootstrap" class="btn btn-success w-100 mt-2">Wijzigingen Opslaan</button>
                    </form>
                    {{-- Ruimte voor berichten binnen de bewerkmodal --}}
                    <div id="editDrankModalMessagesBootstrap" class="mt-3 text-center small"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// JavaScript voor de drankenbeheerpagina.
// Dit script handelt API-calls, categoriebeheer via modal, dynamische barcodevelden,
// en het vullen en submitten van de "Drankje Bewerken" modal.

document.addEventListener('DOMContentLoaded', function() {
    // CSRF token voor beveiligde AJAX requests (POST, PUT, DELETE).
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken && document.querySelector('form[method="POST"], form[method="PUT"], form[method="DELETE"]')) {
        console.warn('CSRF Token meta tag niet gevonden! API calls die CSRF protectie vereisen kunnen falen.');
    }

    // Referenties naar de categorie dropdowns op de hoofdpagina en in de bewerkmodal.
    const mainPageCategoryDropdown = document.getElementById('categorie_id_add');
    const editModalCategoryDropdown = document.getElementById('editDrankCategorieBootstrap');

    /**
     * Generieke functie voor het uitvoeren van API calls.
     * @param {string} url - De URL voor de API call.
     * @param {string} [method='GET'] - De HTTP methode.
     * @param {object|null} [body=null] - De request body voor POST/PUT requests.
     * @returns {Promise<object>} De JSON response data.
     * @throws {Error} Als de API call faalt of een non-OK status retourneert.
     */
    async function apiCall(url, method = 'GET', body = null) {
        const headers = { 'Accept': 'application/json', };
        if (csrfToken && (method === 'POST' || method === 'PUT' || method === 'DELETE')) { headers['X-CSRF-TOKEN'] = csrfToken; }
        if (method !== 'GET' && method !== 'HEAD') { headers['Content-Type'] = 'application/json'; }
        
        const options = { method, headers };
        if (body && (method === 'POST' || method === 'PUT')) { options.body = JSON.stringify(body); }
        
        const response = await fetch(url, options);
        // Probeer JSON te parsen, vang exceptions als de body leeg is of geen JSON.
        const responseData = await response.json().catch(() => ({ message: 'Antwoord kon niet verwerkt worden (geen JSON).', errors: {} }));
        
        if (!response.ok) {
            const error = new Error(responseData.message || `HTTP error! status: ${response.status}`);
            error.errors = responseData.errors || {}; // Voeg eventuele validatiefouten toe
            error.status = response.status;
            throw error;
        }
        return responseData;
    }

    // --- Categorieën Modal Functionaliteit ---
    const manageCategoriesBtn = document.getElementById('manageCategoriesBtnBootstrap'); // Knop die modal opent
    const categoriesModalEl = document.getElementById('categoriesModalBootstrap');       // Het modal element
    const categoriesBootstrapModal = categoriesModalEl ? new bootstrap.Modal(categoriesModalEl) : null; // Bootstrap modal instance
    const categoryListUL = document.getElementById('categoryListULBootstrap');           // UL voor categorielijst
    const newCategoryNameInput = document.getElementById('newCategoryNameBootstrap');    // Input voor nieuwe categorienaam
    const addCategoryBtn = document.getElementById('addCategoryBtnBootstrap');           // Knop om categorie toe te voegen
    const categoryModalMessages = document.getElementById('categoryModalMessagesBootstrap'); // Div voor berichten in modal

    /**
     * Toont een bericht (succes/fout) in de categorie modal.
     * @param {string} message - Het te tonen bericht.
     * @param {string} [type='success'] - 'success' of 'error'.
     */
    function displayCategoryModalMessage(message, type = 'success') {
        const alertType = type === 'success' ? 'alert-success' : 'alert-danger';
        categoryModalMessages.innerHTML = `<div class="alert ${alertType} alert-sm py-1 px-2 small mb-0">${message}</div>`;
        setTimeout(() => categoryModalMessages.innerHTML = '', 4000); // Verberg bericht na 4 seconden
    }
    
    /** Haalt categorieën op via API en vult de lijst in de categoriebeheer modal. */
    async function fetchAndPopulateCategoriesInModal() { 
        if (!categoryListUL) return; 
        categoryListUL.innerHTML = '<li class="list-group-item text-center text-muted">Laden...</li>'; 
        try { 
            const categories = await apiCall('/api/categorieen'); // API endpoint voor categorieën
            categoryListUL.innerHTML = ''; 
            if (categories.length === 0) { categoryListUL.innerHTML = '<li class="list-group-item text-center text-muted">Geen categorieën.</li>'; } 
            categories.forEach(cat => { 
                const li = document.createElement('li'); 
                li.className = 'list-group-item d-flex justify-content-between align-items-center py-2'; 
                li.textContent = cat.naam; 
                
                const deleteBtn = document.createElement('button'); 
                deleteBtn.textContent = 'Verwijder'; 
                deleteBtn.className = 'btn btn-danger btn-sm py-0 px-1'; 
                deleteBtn.dataset.id = cat.categorie_id; 
                deleteBtn.type = 'button'; 
                deleteBtn.addEventListener('click', handleDeleteCategory); // Voeg event listener toe
                
                li.appendChild(deleteBtn); 
                categoryListUL.appendChild(li); 
            }); 
        } catch (error) { 
            console.error("Fout bij ophalen categorieën voor modal:", error); 
            categoryListUL.innerHTML = '<li class="list-group-item text-center text-danger">Kon categorieën niet laden.</li>'; 
            displayCategoryModalMessage(`Fout: ${error.message}`, 'error'); 
        } 
    }
    
    /** Ververst alle categorie dropdowns op de pagina met de laatste data. */
    async function refreshAllCategoryDropdowns(selectedCategoryId = null) { 
        try { 
            const categories = await apiCall('/api/categorieen'); 
            const dropdowns = [mainPageCategoryDropdown, editModalCategoryDropdown]; 
            
            dropdowns.forEach(dropdown => { 
                if (dropdown) { 
                    const currentValue = selectedCategoryId || dropdown.value; // Behoud huidige selectie indien mogelijk
                    let firstOptionText = dropdown.id === 'editDrankCategorieBootstrap' ? "-- Selecteer --" : "-- Selecteer Categorie --"; 
                    dropdown.innerHTML = `<option value="">${firstOptionText}</option>`; 
                    
                    categories.forEach(cat => { 
                        const option = document.createElement('option'); 
                        option.value = cat.categorie_id; 
                        option.textContent = cat.naam; 
                        dropdown.appendChild(option); 
                    }); 
                    // Herstel de eerder geselecteerde waarde als deze nog bestaat in de nieuwe lijst
                    if (currentValue && Array.from(dropdown.options).some(opt => opt.value == currentValue)) { 
                        dropdown.value = currentValue; 
                    } 
                } 
            }); 
        } catch (error) { console.error("Fout bij verversen categorie dropdowns:", error); } 
    }
    
    /** Handler voor het toevoegen van een nieuwe categorie. */
    async function handleAddCategory() { 
        const categoryName = newCategoryNameInput.value.trim(); 
        if (!categoryName) { displayCategoryModalMessage('Categorienaam is leeg.', 'error'); return; } 
        try { 
            const result = await apiCall('/api/categorieen', 'POST', { naam: categoryName }); 
            displayCategoryModalMessage(result.message || 'Categorie toegevoegd!', 'success'); 
            newCategoryNameInput.value = ''; // Leeg inputveld na succes
            await fetchAndPopulateCategoriesInModal(); // Ververs lijst in modal
            await refreshAllCategoryDropdowns(result.categorie ? result.categorie.categorie_id : null); // Ververs alle dropdowns
        } catch (error) { 
            let msg = error.message || 'Fout bij toevoegen.'; 
            if (error.errors && error.errors.naam) msg = error.errors.naam[0]; // Toon specifieke validatiefout
            displayCategoryModalMessage(msg, 'error'); 
        } 
    }
    
    /** Handler voor het verwijderen van een categorie. */
    async function handleDeleteCategory(event) { 
        const categoryId = event.target.dataset.id; 
        if (!confirm('Weet je zeker dat je deze categorie wilt verwijderen? Dit kan niet ongedaan gemaakt worden.')) return; 
        try { 
            const result = await apiCall(`/api/categorieen/${categoryId}`, 'DELETE'); 
            displayCategoryModalMessage(result.message || 'Categorie verwijderd!', 'success'); 
            await fetchAndPopulateCategoriesInModal(); // Ververs lijst in modal
            await refreshAllCategoryDropdowns();       // Ververs alle dropdowns
        } catch (error) { 
            displayCategoryModalMessage(error.message || 'Fout bij verwijderen.', 'error'); 
        } 
    }

    // Event listeners voor categorie modal
    if (manageCategoriesBtn && categoriesModalEl) {
        categoriesModalEl.addEventListener('show.bs.modal', function (event) { // Bij openen modal
            fetchAndPopulateCategoriesInModal(); // Haal categorieën op
            categoryModalMessages.innerHTML = '';    // Leeg eventuele oude berichten
        });
    }
    if (addCategoryBtn) addCategoryBtn.addEventListener('click', handleAddCategory);

    // --- Dynamische Barcode Velden ---
    /**
     * Creëert en voegt een nieuw barcode inputveld toe aan de gespecificeerde container.
     * @param {HTMLElement} container - De container waar het veld aan toegevoegd wordt.
     * @param {string} nameAttribute - De waarde voor het 'name' attribuut van de input.
     * @param {string} [value=''] - Optionele startwaarde voor de input.
     * @param {string} [placeholderText='Extra barcode'] - Placeholder tekst.
     */
    function createBarcodeField(container, nameAttribute, value = '', placeholderText = 'Extra barcode') {
        const index = container.children.length;
        const newBarcodeGroup = document.createElement('div');
        newBarcodeGroup.className = 'input-group input-group-sm mb-2 barcode-input-group';
        
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = nameAttribute; // Bijv. 'additional_barcodes[]'
        newInput.className = 'form-control form-control-sm';
        newInput.placeholder = placeholderText + ' ' + (index + 1);
        newInput.value = value;
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm remove-barcode-btn';
        removeBtn.title = 'Verwijder deze barcode';
        removeBtn.innerHTML = '&times;'; // 'x' symbool
        removeBtn.addEventListener('click', function() { newBarcodeGroup.remove(); });
        
        newBarcodeGroup.appendChild(newInput);
        newBarcodeGroup.appendChild(removeBtn);
        container.appendChild(newBarcodeGpid);
        // Focus op het nieuwe veld als de modal (indien van toepassing) al zichtbaar is.
        if (newInput.closest('.modal.show')) { 
            newInput.focus();
        }
    }

    // Event listener voor 'Extra barcode toevoegen' knop op hoofdpagina
    const addBarcodeFieldBtn = document.getElementById('addBarcodeFieldBtn');
    const additionalBarcodesContainer = document.getElementById('additionalBarcodesContainer');
    if (addBarcodeFieldBtn && additionalBarcodesContainer) { 
        addBarcodeFieldBtn.addEventListener('click', () => createBarcodeField(additionalBarcodesContainer, 'additional_barcodes[]'));
    }
    // Event delegation voor 'verwijder' knoppen op hoofdpagina (indien al aanwezig door 'old' input)
    if(additionalBarcodesContainer) { 
      additionalBarcodesContainer.addEventListener('click', e => { 
        if (e.target && e.target.classList.contains('remove-barcode-btn')) {
          e.target.closest('.barcode-input-group').remove();
        }
      });
    }

    // Event listener voor 'Extra barcode toevoegen' knop in bewerkmodal
    const addBarcodeFieldBtnEdit = document.getElementById('addBarcodeFieldBtnEditBootstrap');
    const additionalBarcodesContainerEdit = document.getElementById('additionalBarcodesContainerEditBootstrap');
    if (addBarcodeFieldBtnEdit && additionalBarcodesContainerEdit) { 
        addBarcodeFieldBtnEdit.addEventListener('click', () => createBarcodeField(additionalBarcodesContainerEdit, 'additional_barcodes[]'));
    }
     // Event delegation voor 'verwijder' knoppen in bewerkmodal (voor dynamisch toegevoegde velden)
    if(additionalBarcodesContainerEdit) { 
      additionalBarcodesContainerEdit.addEventListener('click', e => {
        if (e.target && e.target.classList.contains('remove-barcode-btn')) {
          e.target.closest('.barcode-input-group').remove();
        }
      });
    }

    // --- Drankje Bewerken Modal Functionaliteit ---
    const editDrankModalEl = document.getElementById('editDrankModalBootstrap');
    const editDrankBootstrapModal = editDrankModalEl ? new bootstrap.Modal(editDrankModalEl) : null;
    const editDrankForm = document.getElementById('editDrankFormBootstrap');
    const editDrankModalMessages = document.getElementById('editDrankModalMessagesBootstrap');
    // Formuliervelden in de bewerkmodal
    const editDrankIdInput = document.getElementById('editDrankIdBootstrap');
    const editDrankNaamInput = document.getElementById('editDrankNaamBootstrap');
    const editDrankBarcodeInput = document.getElementById('editDrankBarcodeBootstrap');
    const editDrankPrijsInput = document.getElementById('editDrankPrijsBootstrap');
    const editDrankThtInput = document.getElementById('editDrankThtBootstrap');
    
    /**
     * Toont een bericht (succes/fout/info) in de "Drankje Bewerken" modal.
     * @param {string} message - Het te tonen bericht.
     * @param {string} [type='success'] - 'success', 'error', of 'info'.
     */
    function displayEditDrankModalMessage(message, type = 'success') {
        const alertType = type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : 'alert-danger');
        editDrankModalMessages.innerHTML = `<div class="alert ${alertType} py-1 px-2 small mb-0">${message}</div>`;
        if(type !== 'info') { // Info berichten (zoals 'Laden...') niet automatisch verbergen
            setTimeout(() => editDrankModalMessages.innerHTML = '', 4000);
        }
    }
    
    /** Haalt drankdetails op en vult de "Drankje Bewerken" modal. */
    async function openEditDrankModal(drankId) {
        if (!editDrankBootstrapModal) return;
        displayEditDrankModalMessage('Gegevens laden...', 'info');
        if(additionalBarcodesContainerEdit) additionalBarcodesContainerEdit.innerHTML = ''; // Leeg oude extra barcodes
        if(editDrankForm) editDrankForm.reset(); // Reset het formulier voor nieuwe data

        try {
            await refreshAllCategoryDropdowns(); // Zorg dat categorieën actueel zijn
            const drank = await apiCall(`/api/dranken/${drankId}`); // API endpoint voor specifiek drankje
            
            editDrankIdInput.value = drank.drank_id;
            editDrankNaamInput.value = drank.naam_drank;
            editDrankBarcodeInput.value = drank.barcode || ''; // Hoofdbarcode
            
            if (editModalCategoryDropdown) editModalCategoryDropdown.value = drank.categorie_id; 
            
            editDrankPrijsInput.value = parseFloat(drank.prijs).toFixed(2);
            if (drank.tht_datum) { // Formatteer THT datum naar JJJJ-MM voor <input type="month">
                const dateParts = drank.tht_datum.split('-'); // Assumptie: tht_datum is YYYY-MM-DD
                editDrankThtInput.value = `${dateParts[0]}-${dateParts[1]}`;
            } else {
                editDrankThtInput.value = '';
            }

            // Vul de extra product barcodes, sla de hoofdbarcode over als die al in het hoofdbalkveld staat
            if (drank.product_barcodes && drank.product_barcodes.length > 0) {
                drank.product_barcodes.forEach((pb) => {
                    // Voeg alleen toe als extra barcode als het NIET de hoofdbarcode is, 
                    // of als er GEEN hoofdbarcode is (dan is de eerste product_barcode mogelijk de 'hoofd' in de lijst)
                    if (pb.barcode_value !== drank.barcode || !drank.barcode) { 
                        createBarcodeField(additionalBarcodesContainerEdit, 'additional_barcodes[]', pb.barcode_value);
                    }
                });
            }
            editDrankModalMessages.innerHTML = ''; // Verwijder 'Laden...' bericht
        } catch (error) {
            displayEditDrankModalMessage(`Kon drankdetails niet laden: ${error.message}`, 'error');
            console.error("Fout bij laden drank voor bewerken:", error);
        }
    }
    
    // Event listener voor het openen van de "Drankje Bewerken" modal
    if (editDrankModalEl) {
        editDrankModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // De knop die de modal triggerde
            // Controleer of de modal is geopend door een 'edit-drank-btn'
            if (button && button.classList.contains('edit-drank-btn')) {
                const drankId = button.getAttribute('data-id');
                if (drankId) {
                    openEditDrankModal(drankId);
                } else {
                     displayEditDrankModalMessage('Fout: Geen Drank ID gevonden om te bewerken.', 'error');
                }
            }
        });
    }

    // Event listener voor het submitten van het "Drankje Bewerken" formulier
    if (editDrankForm) {
        editDrankForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Voorkom standaard form submit
            displayEditDrankModalMessage('Bezig met opslaan...', 'info');
            const drankId = editDrankIdInput.value;
            
            // Verzamel formulierdata
            const data = {
                naam_drank: editDrankNaamInput.value,
                barcode: editDrankBarcodeInput.value.trim() === '' ? null : editDrankBarcodeInput.value.trim(), // Stuur null als leeg
                categorie_id: editModalCategoryDropdown.value,
                prijs: editDrankPrijsInput.value,
                tht_datum: editDrankThtInput.value,
                additional_barcodes: [] 
            };
            
            // Verzamel dynamisch toegevoegde barcodes
            if (additionalBarcodesContainerEdit) {
                const additionalInputs = additionalBarcodesContainerEdit.querySelectorAll('input[name="additional_barcodes[]"]');
                additionalInputs.forEach(input => {
                    if (input.value.trim() !== '') data.additional_barcodes.push(input.value.trim());
                });
            }

            if (!drankId) { displayEditDrankModalMessage('Fout: Drank ID ontbreekt. Kan niet opslaan.', 'error'); return; }
            
            try {
                const result = await apiCall(`/api/dranken/${drankId}`, 'PUT', data); // PUT request naar API
                displayEditDrankModalMessage(result.message || 'Drankje succesvol bijgewerkt!', 'success');
                // Wacht even en herlaad de pagina om wijzigingen te zien
                setTimeout(() => {
                    if(editDrankBootstrapModal) editDrankBootstrapModal.hide();
                    location.reload(); 
                }, 1500);
            } catch (error) {
                let msg = error.message || 'Fout bij bijwerken van drankje.';
                if (error.errors && Object.keys(error.errors).length > 0) { // Als er validatiefouten zijn
                    msg = 'Validatiefouten:<ul class="list-unstyled text-start small ps-3">';
                    for (const field in error.errors) { 
                        let fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        // Maak veldnamen voor extra barcodes leesbaarder
                        if(field.startsWith('additional_barcodes.')) fieldName = `Extra barcode ${parseInt(field.split('.')[1]) + 1}`;
                        msg += `<li>${fieldName}: ${error.errors[field].join(', ')}</li>`; 
                    }
                    msg += '</ul>';
                }
                displayEditDrankModalMessage(msg, 'error');
                console.error("Fout bij opslaan drankwijzigingen:", error);
            }
        });
    }

    // Initieel verversen van categorie dropdowns bij laden van de pagina
    refreshAllCategoryDropdowns();

    // Focus op het eerste input veld van het "Nieuw Drankje Toevoegen" formulier
    const naamDrankAddInput = document.getElementById('naam_drank_add');
    if (naamDrankAddInput && !document.querySelector('.alert-danger')) { // Alleen focus als er geen validatiefouten zijn
         // En als er geen modal open is (om te voorkomen dat de focus ongewenst verspringt)
        const anyModalOpen = document.querySelector('.modal.show');
        if (!anyModalOpen) {
            naamDrankAddInput.focus();
        }
    }
});
</script>
@endpush