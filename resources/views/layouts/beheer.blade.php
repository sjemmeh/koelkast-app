<!DOCTYPE html>
{{-- Hoofd layout-bestand voor het "Beheer" gedeelte van de applicatie. --}}
<html lang="nl"> {{-- Het 'data-bs-theme' attribuut (light/dark/auto) wordt dynamisch door JavaScript (theme switcher) ingesteld. --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CSRF Token voor beveiliging van AJAX requests en formulieren. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Koelkast Beheer') - Dranken Kiosk</title>

    {{-- Bootstrap CSS via CDN voor snelle setup en theming. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    {{-- Applicatie-specifieke custom CSS voor het beheergedeelte. --}}
    <link rel="stylesheet" href="{{ asset('css/beheer-custom.css') }}">
    
    {{-- Overweeg Vite voor asset bundling als je project groeit of als je app.js gebruikt. --}}
    {{-- Indien Vite niet gebruikt wordt en app.js leeg is of niet bestaat, kan de link naar app.js verwijderd worden. --}}
    {{-- Voorbeeld: @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    <style>
        /* Minimale inline stijlen voor de basis layout (sticky footer).
           Meer specifieke stijlen staan in beheer-custom.css. */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Zorgt dat de body altijd de volledige viewhoogte inneemt. */
        }
        main.flex-grow-1 { /* De classnaam hier moet overeenkomen met de class op de <main> tag. */
            flex-grow: 1; /* Zorgt dat de main content de beschikbare ruimte opvult. */
        }

        /* Basisstijlen voor de iconen in de Bootstrap themawisselaar. */
        .theme-icon-active { 
            /* De kleur (fill) van het actieve thema-icoon wordt meestal via 'currentColor' door Bootstrap's knopstyling bepaald. */
        }
        .dropdown-item .theme-icon { 
            opacity: .5; 
            margin-right: .5rem; 
        }
        .dropdown-item.active .theme-icon { 
            opacity: 1; /* Volledig zichtbaar icoon voor het geselecteerde thema. */
        }
        .dropdown-item .bi.ms-auto { /* Styling voor het vinkje naast het actieve thema. */ }
    </style>
    @stack('styles') {{-- Placeholder voor pagina-specifieke CSS-stijlen, te pushen vanuit child views. --}}
</head>
<body class="beheer-page-active"> {{-- 'beheer-page-active' class voor specifieke layoutstijlen uit beheer-custom.css. --}}

    <header>
        {{-- Inclusie van de navigatiebalk specifiek voor het beheergedeelte. --}}
        @include('beheer.partials.nav-bootstrap')
    </header>

    {{-- Hoofdcontent container, vult de ruimte tussen header en footer. --}}
    <main class="container mt-4 mb-5 flex-grow-1"> {{-- 'flex-grow-1' is cruciaal voor de sticky footer layout. --}}
        @yield('content') {{-- Placeholder waar de content van specifieke beheerpagina's wordt ingeladen. --}}
    </main>

    {{-- Footer die onderaan de pagina blijft, zelfs bij weinig content. --}}
    <footer class="py-3 mt-auto bg-dark text-white text-center">
        <div class="container">
            <small>Koelkast Beheer &copy; {{ date('Y') }}</small>
        </div>
    </footer>

    {{-- Inline SVG symbolen die gebruikt worden door de Bootstrap themawisselaar. 
         Worden onderaan de body geplaatst voor betere laadprestaties en om de HTML structuur schoon te houden. --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check2" viewBox="0 0 16 16">
            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
        </symbol>
        <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
        </symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
        </symbol>
        <symbol id="sun-fill" viewBox="0 0 16 16">
            <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
        </symbol>
    </svg>

    {{-- Bootstrap JavaScript bundle (inclusief Popper voor dropdowns, modals, etc.) via CDN. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    
    {{-- JavaScript voor de Bootstrap themawisselaar. 
         Dit script is afkomstig van de Bootstrap documentatie. --}}
    <script>
        /*!
         * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
         * Copyright 2011-2024 The Bootstrap Authors
         * Licensed under the Creative Commons Attribution 3.0 Unported License.
         */
        (() => {
            'use strict' // Strict mode voor betere codekwaliteit en error handling.

            // Haalt het opgeslagen thema op uit localStorage.
            const getStoredTheme = () => localStorage.getItem('theme')
            // Slaat het gekozen thema op in localStorage.
            const setStoredTheme = theme => localStorage.setItem('theme', theme)

            // Bepaalt het voorkeursthema: eerst check localStorage, dan systeemvoorkeur.
            const getPreferredTheme = () => {
                const storedTheme = getStoredTheme()
                if (storedTheme) {
                    return storedTheme
                }
                // Standaard naar 'auto' (systeemvoorkeur) als er niets is opgeslagen in localStorage.
                return 'auto' 
            }

            // Past het thema toe op het <html> element.
            const setTheme = theme => {
                if (theme === 'auto') {
                    // Als 'auto', pas thema aan op basis van besturingssysteem voorkeur (dark/light).
                    document.documentElement.setAttribute('data-bs-theme', (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'))
                } else {
                    // Anders, pas het expliciet gekozen thema (light/dark) toe.
                    document.documentElement.setAttribute('data-bs-theme', theme)
                }
            }

            setTheme(getPreferredTheme()) // Stel het thema in direct bij het laden van de pagina.

            // Update de UI van de themawisselaar knop om het actieve thema te reflecteren.
            const showActiveTheme = (theme, focus = false) => {
                const themeSwitcher = document.querySelector('#bd-theme') // De hoofdknop van de themawisselaar.
                if (!themeSwitcher) { return }

                const themeSwitcherText = document.querySelector('#bd-theme-text') // Tekstlabel (zichtbaar op kleine schermen).
                const activeThemeIcon = themeSwitcher.querySelector('.theme-icon-active use') // Het <use> element van het actieve icoon.
                const btnToActivate = document.querySelector(`[data-bs-theme-value="${theme}"]`) // De dropdown knop die overeenkomt met het actieve thema.
                
                if (!btnToActivate || !activeThemeIcon) return; // Stop als cruciale elementen niet gevonden zijn.

                const svgOfActiveBtn = btnToActivate.querySelector('svg use').getAttribute('href') // Haal href van het icoon van de te activeren knop.

                // Reset 'active' status en verberg vinkjes voor alle thema-opties in de dropdown.
                document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
                    element.classList.remove('active')
                    element.setAttribute('aria-pressed', 'false')
                    const checkIcon = element.querySelector('svg.bi.ms-auto'); // Het vink-icoon.
                    if (checkIcon) checkIcon.classList.add('d-none');
                })

                // Activeer de geselecteerde thema-optie en toon het vinkje.
                btnToActivate.classList.add('active')
                btnToActivate.setAttribute('aria-pressed', 'true')
                const activeCheckIcon = btnToActivate.querySelector('svg.bi.ms-auto');
                if (activeCheckIcon) activeCheckIcon.classList.remove('d-none');
                
                // Update het icoon van de hoofdknop van de themawisselaar.
                activeThemeIcon.setAttribute('href', svgOfActiveBtn)
                // Update het aria-label van de hoofdknop voor accessibility.
                if (themeSwitcherText) {
                    const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActivate.dataset.bsThemeValue})`
                    themeSwitcher.setAttribute('aria-label', themeSwitcherLabel)
                }

                // Focus op de themawisselaar knop indien aangegeven (bijv. na een klik).
                if (focus) {
                    themeSwitcher.focus()
                }
            }

            // Luister naar wijzigingen in systeemvoorkeur voor kleurenschema (dark/light mode).
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                // Als het opgeslagen thema 'auto' is of niet bestaat, update het thema.
                if (getStoredTheme() === 'auto' || !getStoredTheme()) {
                    setTheme(getPreferredTheme())
                    showActiveTheme(getPreferredTheme()) // Update ook de UI van de themawisselaar knop.
                }
            })

            // Wanneer de DOM volledig geladen is:
            window.addEventListener('DOMContentLoaded', () => {
                // Toon het actieve thema in de UI van de themawisselaar.
                showActiveTheme(getPreferredTheme())

                // Voeg click event listeners toe aan alle thema selectie knoppen in de dropdown.
                document.querySelectorAll('[data-bs-theme-value]')
                  .forEach(toggle => {
                      toggle.addEventListener('click', () => {
                          const theme = toggle.getAttribute('data-bs-theme-value')
                          setStoredTheme(theme) // Sla het gekozen thema op.
                          setTheme(theme)       // Pas het thema toe.
                          showActiveTheme(theme, true) // Update de UI en focus op de knop.
                      })
                  })
            })
        })()
    </script>
    @stack('scripts') {{-- Placeholder voor pagina-specifieke JavaScript, te pushen vanuit child views. --}}
</body>
</html>