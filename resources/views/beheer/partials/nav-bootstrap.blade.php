{{-- Hoofdnavigatiebalk voor het "Beheer" gedeelte van de applicatie --}}
<nav class="navbar navbar-expand-lg bg-body-tertiary rounded mb-4 shadow-sm border">
    <div class="container-fluid">
        {{-- Merk/Logo link naar de hoofdpagina van het beheergedeelte --}}
        <a class="navbar-brand" href="{{ route('beheer.dranken.index') }}">Koelkast Beheer</a>
        
        {{-- Hamburger menu knop voor kleinere schermen --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#beheerNavbar" aria-controls="beheerNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="beheerNavbar">
            {{-- Hoofdnavigatie links (links uitgelijnd) --}}
            <ul class="navbar-nav nav-pills me-auto mb-2 mb-lg-0">
                {{-- Navigatie-item: Dranken --}}
                {{-- De 'active' class en 'aria-current' attribuut worden dynamisch toegepast --}}
                {{-- op basis van de huidige route om de actieve pagina te markeren. --}}
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'beheer.dranken.index' ? 'active' : '' }}" 
                       {{ Route::currentRouteName() == 'beheer.dranken.index' ? 'aria-current="page"' : '' }} 
                       href="{{ route('beheer.dranken.index') }}">Dranken</a>
                </li>
                {{-- Navigatie-item: Personen --}}
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'beheer.personen.index' ? 'active' : '' }}"
                       {{ Route::currentRouteName() == 'beheer.personen.index' ? 'aria-current="page"' : '' }}
                       href="{{ route('beheer.personen.index') }}">Personen</a>
                </li>
                {{-- Navigatie-item: Transacties --}}
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'beheer.transacties.index' ? 'active' : '' }}"
                       {{ Route::currentRouteName() == 'beheer.transacties.index' ? 'aria-current="page"' : '' }}
                       href="{{ route('beheer.transacties.index') }}">Transacties</a>
                </li>
            </ul>
            
            {{-- Rechts uitgelijnde navigatie-elementen --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item me-lg-2">
                    <a class="nav-link nav-link-kiosk" href="{{ route('kiosk.index') }}">&larr; Naar Kiosk</a>
                </li>
                {{-- Bootstrap themawisselaar (licht, donker, systeemvoorkeur) --}}
                <li class="nav-item dropdown">
                    <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Toggle theme (auto)">
                        <svg class="bi theme-icon-active" width="1em" height="1em" fill="currentColor"><use href="#circle-half"></use></svg> {{-- Icoon voor actieve thema --}}
                        <span class="d-lg-none ms-2" id="bd-theme-text">Thema</span> {{-- Tekst alleen zichtbaar op kleine schermen --}}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                                <svg class="bi theme-icon me-2" width="1em" height="1em" fill="currentColor"><use href="#sun-fill"></use></svg>
                                Licht
                                <svg class="bi ms-auto d-none" width="1em" height="1em" fill="currentColor"><use href="#check2"></use></svg> {{-- Vinkje voor actieve keuze (via JS) --}}
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                                <svg class="bi theme-icon me-2" width="1em" height="1em" fill="currentColor"><use href="#moon-stars-fill"></use></svg>
                                Donker
                                <svg class="bi ms-auto d-none" width="1em" height="1em" fill="currentColor"><use href="#check2"></use></svg>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="true"> {{-- 'auto' (systeem) is hier de standaard actieve keuze --}}
                                <svg class="bi theme-icon me-2" width="1em" height="1em" fill="currentColor"><use href="#circle-half"></use></svg>
                                Systeem
                                <svg class="bi ms-auto d-none" width="1em" height="1em" fill="currentColor"><use href="#check2"></use></svg>
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>