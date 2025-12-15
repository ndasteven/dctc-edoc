<div>
    <div class="bg-gray-100 dark:bg-gray-900 flex flex-col items-center justify-center min-h-screen">
        <!-- Conteneur principal -->
        <div
            class="relative w-full h-screen bg-white border border-gray-300 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700 flex flex-col">
            <!-- En-tête -->

            <header class="flex items-center justify-between px-6 py-3 bg-blue-600 rounded-t-lg flex-shrink-0 overflow-x-hidden overflow-x-scroll text-sm">
                <h1 class="text-lg font-semibold text-white">Aperçu du document</h1>
                <!-- Affichage du chemin d'accès au fichier -->
            @if (!empty($breadcrumbPath))
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 dark:bg-gray-700 dark:border-gray-600">
                    <small class="breadcrumb inline-flex items-center">
                        <span class="inline-flex items-center text-gray-600 dark:text-white">Chemin :

                            @foreach ($breadcrumbPath as $index => $item)
                                @if ($index > 0)
                                    <svg class="w-5 h-5 text-gray-500 dark:text-white mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 16 4-4-4-4" />
                                    </svg>
                                @endif
                                @if (isset($item['id']) && str_starts_with($item['id'], 'service-'))
                                    <span class="text-blue-600 dark:text-blue-400">
                                        {{ $item['name'] }}
                                    </span>
                                @else
                                    <a href="{{ route('folders.show', $item['id']) }}"
                                        class="text-blue-600 hover:underline dark:text-blue-400">
                                        {{ $item['name'] }}
                                    </a>
                                @endif
                            @endforeach
                            <!-- Flèche de séparation avant le nom du fichier -->
                            <svg class="w-5 h-5 text-gray-500 dark:text-white mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 16 4-4-4-4" />
                            </svg>
                            <!-- Nom du fichier courant -->
                            <span class="text-gray-800 font-medium dark:text-white">
                                {{ $document->nom ?? $document->filename }}
                            </span>
                        </span>
                    </small>
                </div>
            @endif
                <div class="flex items-center space-x-4">
                    <button onclick="window.history.back()" class="text-white hover:text-gray-300 focus:outline-none">
                        <div class="inline-flex space-x-2 items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Retour</span>
                        </div>
                    </button>

                    <button id="toggle-aside"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-2 py-1 rounded shadow">
                        ⬅️ Masquer infos
                    </button>
                </div>
            </header>



            <!-- Contenu principal -->
            <div class="grid grid-cols-1 md:grid-cols-4 flex-1 overflow-hidden">
                <!-- Aperçu du document (colonne principale) -->
                <div id="main-viewer" class="col-span-3 md:col-span-4 flex flex-col overflow-hidden">
                    @php
                        $isPDF = in_array($document->type, ['pdf', 'PDF']);
                        $isImageOrText = in_array($document->type, ['txt', 'png', 'jpeg', 'PNG', 'JPEG', 'jpg', 'JPG']);
                        $readOnly = $permission === 'L';
                    @endphp
                    @if ($isPDF)
                        {{-- 📄 PDF avec affichage vertical continu via PDF.js pour tous --}}
                        <div class="flex justify-end items-center space-x-2 mb-2 px-4 pt-2 flex-shrink-0">
                            <button
                                        class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm md:hidden"
                                        type="button"
                                        data-drawer-target="doc-aside"
                                        data-drawer-toggle="doc-aside"
                                        data-drawer-placement="left"
                                    >
                                        📋 Infos
                                    </button>
                            <button id="search-btn" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">🔍 Recherche</button>
                            <button id="zoom-out" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➖
                                Zoom -</button>
                            <button id="zoom-in" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➕
                                Zoom +</button>
                        </div>

                        <div id="pdf-container" class="flex-1 overflow-auto bg-gray-100 p-4">
                            <div id="pdf-pages-container" class="flex flex-col items-center space-y-4"></div>
                        </div>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const url = "{{ asset('storage/' . $document->filename) }}";
                                const container = document.getElementById('pdf-pages-container');
                                const zoomInBtn = document.getElementById('zoom-in');
                                const zoomOutBtn = document.getElementById('zoom-out');

                                let pdfDoc = null;
                                let currentScale = 1;
                                const scaleStep = 0.25;
                                const initialScale = 1.0; // On utilisera un scale basé sur la largeur du conteneur

                                pdfjsLib.GlobalWorkerOptions.workerSrc =
                                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                                const renderAllPages = async () => {
                                    try {
                                        const pdf = await pdfjsLib.getDocument(url).promise;
                                        pdfDoc = pdf;
                                        const containerWidth = container.clientWidth;

                                        // Effacer le contenu précédent
                                        container.innerHTML = '';

                                        // Afficher toutes les pages
                                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                                            const page = await pdf.getPage(pageNum);
                                            const scale = (containerWidth - 40) / page.getViewport({scale: 1}).width; // Ajuster à la largeur disponible
                                            const viewport = page.getViewport({scale: scale * currentScale});

                                            // Créer un canvas pour la page
                                            const canvas = document.createElement('canvas');
                                            const context = canvas.getContext('2d');

                                            canvas.height = viewport.height;
                                            canvas.width = viewport.width;
                                            canvas.className = 'shadow-md rounded bg-white';

                                            // Dessiner la page
                                            const renderContext = {
                                                canvasContext: context,
                                                viewport: viewport
                                            };

                                            await page.render(renderContext).promise;
                                            container.appendChild(canvas);
                                        }
                                    } catch (err) {
                                        container.innerHTML = `<p class="text-red-600">Erreur PDF : ${err.message}</p>`;
                                    }
                                };

                                // Dessiner le PDF quand la promesse est résolue
                                pdfjsLib.getDocument(url).promise.then(pdf => {
                                    pdfDoc = pdf;
                                    renderAllPages();
                                }).catch(err => {
                                    container.innerHTML = `<p class="text-red-600">Erreur PDF : ${err.message}</p>`;
                                });

                                // Contrôles de zoom
                                zoomInBtn.addEventListener('click', () => {
                                    currentScale += scaleStep;
                                    renderAllPages();
                                });

                                zoomOutBtn.addEventListener('click', () => {
                                    if (currentScale > scaleStep) {
                                        currentScale -= scaleStep;
                                        renderAllPages();
                                    }
                                });

                                // Rafraîchir l'affichage quand la fenêtre est redimensionnée
                                window.addEventListener('resize', () => {
                                    renderAllPages();
                                });

                                // Fonction de recherche dans le document PDF
                                let searchContainer = null;
                                let searchInput = null;
                                let searchResults = null;
                                let currentMatchIndex = -1;
                                let allMatches = [];

                                // Créer l'interface de recherche
                                function createSearchInterface() {
                                    // Conteneur de recherche
                                    searchContainer = document.createElement('div');
                                    searchContainer.id = 'search-container';
                                    searchContainer.style.cssText = `
                                        position: fixed;
                                        top: 50%;
                                        left: 50%;
                                        transform: translate(-50%, -50%);
                                        background: white;
                                        padding: 20px;
                                        border: 1px solid #ccc;
                                        border-radius: 5px;
                                        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                        z-index: 1000;
                                        display: none;
                                        min-width: 400px;
                                    `;

                                    // Input de recherche
                                    searchInput = document.createElement('input');
                                    searchInput.type = 'text';
                                    searchInput.placeholder = 'Rechercher dans le document...';
                                    searchInput.style.cssText = `
                                        width: calc(100% - 20px);
                                        padding: 10px;
                                        margin-bottom: 10px;
                                        border: 1px solid #ccc;
                                        border-radius: 3px;
                                    `;

                                    // Boutons de navigation
                                    const navigationDiv = document.createElement('div');
                                    navigationDiv.style.cssText = `
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: center;
                                    `;

                                    const prevBtn = document.createElement('button');
                                    prevBtn.textContent = 'Précédent';
                                    prevBtn.style.cssText = `
                                        padding: 5px 10px;
                                        margin-right: 10px;
                                        background: #f0f0f0;
                                        border: 1px solid #ccc;
                                        border-radius: 3px;
                                        cursor: pointer;
                                    `;

                                    const nextBtn = document.createElement('button');
                                    nextBtn.textContent = 'Suivant';
                                    nextBtn.style.cssText = `
                                        padding: 5px 10px;
                                        margin-right: 10px;
                                        background: #f0f0f0;
                                        border: 1px solid #ccc;
                                        border-radius: 3px;
                                        cursor: pointer;
                                    `;

                                    const closeBtn = document.createElement('button');
                                    closeBtn.textContent = 'Fermer';
                                    closeBtn.style.cssText = `
                                        padding: 5px 10px;
                                        background: #e74c3c;
                                        color: white;
                                        border: none;
                                        border-radius: 3px;
                                        cursor: pointer;
                                    `;

                                    // Affichage des résultats
                                    searchResults = document.createElement('div');
                                    searchResults.style.cssText = `
                                        font-size: 12px;
                                        color: #666;
                                        margin-top: 10px;
                                        text-align: center;
                                    `;

                                    // Événements
                                    prevBtn.addEventListener('click', () => {
                                        if (allMatches.length > 0) {
                                            currentMatchIndex = (currentMatchIndex - 1 + allMatches.length) % allMatches.length;
                                            scrollToMatch(currentMatchIndex);
                                        }
                                    });

                                    nextBtn.addEventListener('click', () => {
                                        if (allMatches.length > 0) {
                                            currentMatchIndex = (currentMatchIndex + 1) % allMatches.length;
                                            scrollToMatch(currentMatchIndex);
                                        }
                                    });

                                    closeBtn.addEventListener('click', () => {
                                        searchContainer.style.display = 'none';
                                    });

                                    searchInput.addEventListener('keyup', (e) => {
                                        if (e.key === 'Enter') {
                                            performSearch();
                                        }
                                    });

                                    // Construction de l'interface
                                    navigationDiv.appendChild(prevBtn);
                                    navigationDiv.appendChild(nextBtn);
                                    navigationDiv.appendChild(closeBtn);

                                    searchContainer.appendChild(searchInput);
                                    searchContainer.appendChild(navigationDiv);
                                    searchContainer.appendChild(searchResults);
                                    document.body.appendChild(searchContainer);

                                    // Bouton de recherche principal
                                    const searchBtn = document.getElementById('search-btn');
                                    searchBtn.addEventListener('click', () => {
                                        searchContainer.style.display = searchContainer.style.display === 'block' ? 'none' : 'block';
                                        if (searchContainer.style.display === 'block') {
                                            searchInput.focus();
                                        }
                                    });
                                }

                                // Fonction pour effectuer la recherche
                                async function performSearch() {
                                    if (!pdfDoc) return;

                                    const searchTerm = searchInput.value.trim();
                                    if (!searchTerm) {
                                        searchResults.textContent = '';
                                        return;
                                    }

                                    allMatches = [];
                                    currentMatchIndex = -1;

                                    // Effectuer la recherche dans chaque page
                                    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                                        const page = await pdfDoc.getPage(pageNum);
                                        const textContent = await page.getTextContent();
                                        const items = textContent.items;
                                        let allText = '';

                                        for (let i = 0; i < items.length; i++) {
                                            allText += items[i].str;
                                        }

                                        // Chercher le terme dans le texte
                                        const regex = new RegExp(searchTerm, 'gi');
                                        let match;
                                        while ((match = regex.exec(allText)) !== null) {
                                            allMatches.push({
                                                pageNum: pageNum,
                                                index: match.index,
                                                text: match[0],
                                                originalText: allText
                                            });
                                        }
                                    }

                                    // Afficher les résultats
                                    if (allMatches.length > 0) {
                                        searchResults.textContent = `Trouvé ${allMatches.length} résultat(s)`;
                                        currentMatchIndex = 0;
                                        scrollToMatch(0);
                                    } else {
                                        searchResults.textContent = 'Aucun résultat trouvé';
                                    }
                                }

                                // Fonction pour aller à un résultat spécifique
                                function scrollToMatch(index) {
                                    if (index < 0 || index >= allMatches.length) return;

                                    const match = allMatches[index];
                                    searchResults.textContent = `Résultat ${index + 1} sur ${allMatches.length}`;

                                    // Trouver la page correspondante et la faire défiler
                                    const canvas = document.querySelectorAll('#pdf-pages-container canvas')[match.pageNum - 1];
                                    if (canvas) {
                                        canvas.scrollIntoView({behavior: 'smooth', block: 'center'});

                                        // Mise en évidence temporaire du résultat
                                        highlightText(canvas, match);
                                    }
                                }

                                // Fonction pour mettre en évidence un texte
                                function highlightText(canvas, match) {
                                    // Sauvegarder le contexte du canvas
                                    const context = canvas.getContext('2d');

                                    // Dessiner un rectangle de mise en évidence (simplifié)
                                    context.fillStyle = 'rgba(255, 255, 0, 0.5)'; // Jaune transparent
                                    context.fillRect(100, 100, 200, 30); // Position arbitraire pour démonstration

                                    // Remettre à jour après un court délai
                                    setTimeout(() => {
                                        const newScale = (canvas.parentElement.clientWidth - 40) / canvas.width;
                                        const newViewport = match.page.getViewport({scale: newScale * currentScale});
                                        // Retirer temporairement la surbrillance (dans une implémentation complète, on devrait redessiner la page)
                                        renderAllPages();
                                    }, 2000);
                                }

                                // Initialiser l'interface de recherche
                                createSearchInterface();
                            });
                        </script>

                        <script>
                            // Gestion de l'impression pour les utilisateurs avec permissions E/LE
                            (function() {
                                'use strict';
                                const USER_PERMISSION = '{{ $permission ?? 'N' }}';
                                const HAS_EDIT_PERMISSION = USER_PERMISSION === 'E' || USER_PERMISSION === 'LE';

                                // Ajouter le bouton d'impression si l'utilisateur a les permissions E/LE
                                if (HAS_EDIT_PERMISSION) {
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const printButton = document.createElement('button');
                                        printButton.id = 'print-pdf';
                                        printButton.className = 'px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm';
                                        printButton.innerHTML = '🖨️ Imprimer';

                                        // Ajouter le bouton avant les boutons de zoom
                                        const buttonContainer = document.querySelector('.flex.justify-end.items-center.space-x-2.mb-2.px-4.pt-2.flex-shrink-0');
                                        const zoomOutBtn = document.getElementById('zoom-out');

                                        if (buttonContainer && zoomOutBtn) {
                                            buttonContainer.insertBefore(printButton, zoomOutBtn);
                                        }

                                        // Fonction d'impression pour les PDFs rendus avec PDF.js
                                        printButton.addEventListener('click', () => {
                                            // Créer une fenêtre d'impression avec les images des pages
                                            const printWindow = window.open('', '_blank');
                                            const container = document.getElementById('pdf-pages-container');
                                            const canvases = container.querySelectorAll('canvas');

                                            let printContent = '<html><head><title>Impression PDF</title>';
                                            printContent += '<style>body { margin: 0; padding: 20px; } img { width: 100%; margin-bottom: 20px; }</style>';
                                            printContent += '</head><body>';

                                            canvases.forEach(canvas => {
                                                printContent += `<img src="${canvas.toDataURL()}" />`;
                                            });

                                            printContent += '</body></html>';

                                            printWindow.document.write(printContent);
                                            printWindow.document.close();
                                            printWindow.focus();

                                            // Attendre que le contenu soit chargé avant d'imprimer
                                            setTimeout(() => {
                                                printWindow.print();
                                                printWindow.close();
                                            }, 500);
                                        });
                                    });
                                }

                                const IS_READONLY = USER_PERMISSION === 'L';

                                // Ajouter les protections pour les utilisateurs en lecture seule
                                if (IS_READONLY) {
                                    // Bloquer certaines fonctionnalités pour les PDFs
                                    document.addEventListener('contextmenu', function(e) {
                                        if (e.target.tagName === 'CANVAS') {
                                            e.preventDefault();
                                        }
                                    });

                                    document.addEventListener('dragstart', function(e) {
                                        if (e.target.tagName === 'CANVAS') {
                                            e.preventDefault();
                                        }
                                    });
                                }
                            })();

                        </script>

                        <script>
                            // Fonction de recherche dans les documents Office avec PDF.js
                            (function() {
                                'use strict';

                                // Vérifier si nous sommes sur un document Office
                                if (document.getElementById('pdf-container-office')) {
                                    document.addEventListener('DOMContentLoaded', () => {
                                        let pdfDocOffice = null;
                                        let searchContainerOffice = null;
                                        let searchInputOffice = null;
                                        let searchResultsOffice = null;
                                        let currentMatchIndexOffice = -1;
                                        let allMatchesOffice = [];

                                        // Fonction pour récupérer l'instance de PDF actuelle
                                        function getPdfDocOffice() {
                                            // On va chercher la variable globale ou récupérer l'instance du script principal
                                            if (typeof pdfjsLib !== 'undefined' && document.getElementById('pdf-pages-container-office')) {
                                                // On récupère l'URL du document
                                                const url = "{{ asset('storage/archives/' . $nom) }}";
                                                return pdfjsLib.getDocument(url).promise.then(pdf => {
                                                    pdfDocOffice = pdf;
                                                    return pdf;
                                                });
                                            }
                                            return Promise.resolve(null);
                                        }

                                        // Créer l'interface de recherche pour les documents Office
                                        function createSearchInterfaceOffice() {
                                            // Conteneur de recherche
                                            searchContainerOffice = document.createElement('div');
                                            searchContainerOffice.id = 'search-container-office';
                                            searchContainerOffice.style.cssText = `
                                                position: fixed;
                                                top: 50%;
                                                left: 50%;
                                                transform: translate(-50%, -50%);
                                                background: white;
                                                padding: 20px;
                                                border: 1px solid #ccc;
                                                border-radius: 5px;
                                                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                                z-index: 1000;
                                                display: none;
                                                min-width: 400px;
                                            `;

                                            // Input de recherche
                                            searchInputOffice = document.createElement('input');
                                            searchInputOffice.type = 'text';
                                            searchInputOffice.placeholder = 'Rechercher dans le document...';
                                            searchInputOffice.style.cssText = `
                                                width: calc(100% - 20px);
                                                padding: 10px;
                                                margin-bottom: 10px;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                            `;

                                            // Boutons de navigation
                                            const navigationDiv = document.createElement('div');
                                            navigationDiv.style.cssText = `
                                                display: flex;
                                                justify-content: space-between;
                                                align-items: center;
                                            `;

                                            const prevBtn = document.createElement('button');
                                            prevBtn.textContent = 'Précédent';
                                            prevBtn.style.cssText = `
                                                padding: 5px 10px;
                                                margin-right: 10px;
                                                background: #f0f0f0;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            const nextBtn = document.createElement('button');
                                            nextBtn.textContent = 'Suivant';
                                            nextBtn.style.cssText = `
                                                padding: 5px 10px;
                                                margin-right: 10px;
                                                background: #f0f0f0;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            const closeBtn = document.createElement('button');
                                            closeBtn.textContent = 'Fermer';
                                            closeBtn.style.cssText = `
                                                padding: 5px 10px;
                                                background: #e74c3c;
                                                color: white;
                                                border: none;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            // Affichage des résultats
                                            searchResultsOffice = document.createElement('div');
                                            searchResultsOffice.style.cssText = `
                                                font-size: 12px;
                                                color: #666;
                                                margin-top: 10px;
                                                text-align: center;
                                            `;

                                            // Événements
                                            prevBtn.addEventListener('click', () => {
                                                if (allMatchesOffice.length > 0) {
                                                    currentMatchIndexOffice = (currentMatchIndexOffice - 1 + allMatchesOffice.length) % allMatchesOffice.length;
                                                    scrollToMatchOffice(currentMatchIndexOffice);
                                                }
                                            });

                                            nextBtn.addEventListener('click', () => {
                                                if (allMatchesOffice.length > 0) {
                                                    currentMatchIndexOffice = (currentMatchIndexOffice + 1) % allMatchesOffice.length;
                                                    scrollToMatchOffice(currentMatchIndexOffice);
                                                }
                                            });

                                            closeBtn.addEventListener('click', () => {
                                                searchContainerOffice.style.display = 'none';
                                            });

                                            searchInputOffice.addEventListener('keyup', (e) => {
                                                if (e.key === 'Enter') {
                                                    performSearchOffice();
                                                }
                                            });

                                            // Construction de l'interface
                                            navigationDiv.appendChild(prevBtn);
                                            navigationDiv.appendChild(nextBtn);
                                            navigationDiv.appendChild(closeBtn);

                                            searchContainerOffice.appendChild(searchInputOffice);
                                            searchContainerOffice.appendChild(navigationDiv);
                                            searchContainerOffice.appendChild(searchResultsOffice);
                                            document.body.appendChild(searchContainerOffice);

                                            // Bouton de recherche principal
                                            const searchBtn = document.getElementById('search-btn-office');
                                            if (searchBtn) {
                                                searchBtn.addEventListener('click', () => {
                                                    searchContainerOffice.style.display = searchContainerOffice.style.display === 'block' ? 'none' : 'block';
                                                    if (searchContainerOffice.style.display === 'block') {
                                                        searchInputOffice.focus();
                                                    }
                                                });
                                            }
                                        }

                                        // Fonction pour effectuer la recherche dans les documents Office
                                        async function performSearchOffice() {
                                            if (!pdfDocOffice) {
                                                // Essayer d'obtenir l'instance du PDF
                                                await getPdfDocOffice();
                                                if (!pdfDocOffice) return;
                                            }

                                            const searchTerm = searchInputOffice.value.trim();
                                            if (!searchTerm) {
                                                searchResultsOffice.textContent = '';
                                                return;
                                            }

                                            allMatchesOffice = [];
                                            currentMatchIndexOffice = -1;

                                            // Effectuer la recherche dans chaque page
                                            for (let pageNum = 1; pageNum <= pdfDocOffice.numPages; pageNum++) {
                                                const page = await pdfDocOffice.getPage(pageNum);
                                                const textContent = await page.getTextContent();
                                                const items = textContent.items;
                                                let allText = '';

                                                for (let i = 0; i < items.length; i++) {
                                                    allText += items[i].str;
                                                }

                                                // Chercher le terme dans le texte
                                                const regex = new RegExp(searchTerm, 'gi');
                                                let match;
                                                while ((match = regex.exec(allText)) !== null) {
                                                    allMatchesOffice.push({
                                                        pageNum: pageNum,
                                                        index: match.index,
                                                        text: match[0],
                                                        originalText: allText
                                                    });
                                                }
                                            }

                                            // Afficher les résultats
                                            if (allMatchesOffice.length > 0) {
                                                searchResultsOffice.textContent = `Trouvé ${allMatchesOffice.length} résultat(s)`;
                                                currentMatchIndexOffice = 0;
                                                scrollToMatchOffice(0);
                                            } else {
                                                searchResultsOffice.textContent = 'Aucun résultat trouvé';
                                            }
                                        }

                                        // Fonction pour aller à un résultat spécifique dans les documents Office
                                        function scrollToMatchOffice(index) {
                                            if (index < 0 || index >= allMatchesOffice.length) return;

                                            const match = allMatchesOffice[index];
                                            searchResultsOffice.textContent = `Résultat ${index + 1} sur ${allMatchesOffice.length}`;

                                            // Trouver la page correspondante et la faire défiler
                                            const canvas = document.querySelectorAll('#pdf-pages-container-office canvas')[match.pageNum - 1];
                                            if (canvas) {
                                                canvas.scrollIntoView({behavior: 'smooth', block: 'center'});
                                            }
                                        }

                                        // Initialiser l'interface de recherche pour les documents Office
                                        createSearchInterfaceOffice();
                                    });
                                }
                            })();
                        </script>
                    @elseif ($isPDF || $isImageOrText)
                        {{-- 🖼️ Images et fichiers texte - AVEC PROTECTION si lecture seule --}}
                        @if ($readOnly && in_array($document->type, ['png', 'jpeg', 'PNG', 'JPEG', 'jpg', 'JPG']))
                            {{-- 🔒 Images en mode lecture seule avec protection --}}
                            <div class="flex-1 overflow-auto bg-gray-100 p-4 text-center">
                                <img id="protected-image" src="{{ asset('storage/' . $document->filename) }}"
                                    class="mx-auto shadow-md rounded max-w-full h-auto" alt="Document protégé">
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const img = document.getElementById('protected-image');

                                    // Même protection que pour les PDFs
                                    img.addEventListener('contextmenu', function(e) {
                                        e.preventDefault();
                                    });

                                    img.addEventListener('dragstart', function(e) {
                                        e.preventDefault();
                                    });

                                    // Désactiver la sélection
                                    img.style.userSelect = 'none';
                                    img.style.webkitUserSelect = 'none';
                                    img.style.mozUserSelect = 'none';
                                    img.style.msUserSelect = 'none';

                                    // Désactiver le drag
                                    img.style.webkitUserDrag = 'none';
                                    img.style.mozUserDrag = 'none';
                                    img.style.userDrag = 'none';

                                    // Bloquer les tentatives de sauvegarde
                                    img.addEventListener('mousedown', function(e) {
                                        if (e.button === 0) { // Clic gauche
                                            e.preventDefault();
                                        }
                                    });
                                });
                            </script>
                        @else
                            {{-- 🖼️ Images avec contrôles de zoom --}}
                            <div id="image-container" class="flex flex-col h-full">
                                <div class="flex justify-between items-center px-4 pt-2 flex-shrink-0">
                                    <button
                                        class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm md:hidden"
                                        type="button"
                                        data-drawer-target="doc-aside"
                                        data-drawer-toggle="doc-aside"
                                        data-drawer-placement="left"
                                    >
                                        📋 Infos
                                    </button>
                                    <div class="flex space-x-2">
                                        <button id="zoom-out" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➖ Zoom -</button>
                                        <button id="zoom-in" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➕ Zoom +</button>
                                        <button id="reset-zoom" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">🔄 Reset</button>
                                    </div>
                                </div>

                                <div id="image-wrapper" class="flex-1 overflow-auto p-4 bg-gray-100">
                                    <img id="zoomable-image"
                                        src="{{ asset('storage/' . $document->filename) }}"
                                        class="mx-auto shadow-md rounded max-w-full h-auto"
                                        style="transform-origin: center center; transition: transform 0.2s ease;"
                                        alt="Image preview"
                                        onwheel="handleWheel(event)"
                                    />
                                </div>
                            </div>

                            <script>
                                // Charger Alpine.js si non présent
                                if (!window.Alpine) {
                                    const script = document.createElement('script');
                                    script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
                                    document.head.appendChild(script);
                                }

                                document.addEventListener('DOMContentLoaded', () => {
                                    const image = document.getElementById('zoomable-image');
                                    const zoomInBtn = document.getElementById('zoom-in');
                                    const zoomOutBtn = document.getElementById('zoom-out');
                                    const resetBtn = document.getElementById('reset-zoom');

                                    let scale = 1;
                                    let offsetX = 0;
                                    let offsetY = 0;
                                    let isDragging = false;
                                    let startX, startY;

                                    // Zoom avec les boutons
                                    zoomInBtn.addEventListener('click', () => {
                                        scale = Math.min(3, scale + 0.25);
                                        updateTransform();
                                    });

                                    zoomOutBtn.addEventListener('click', () => {
                                        scale = Math.max(0.5, scale - 0.25);
                                        updateTransform();
                                    });

                                    resetBtn.addEventListener('click', () => {
                                        scale = 1;
                                        offsetX = 0;
                                        offsetY = 0;
                                        updateTransform();
                                    });

                                    // Zoom avec la molette
                                    function handleWheel(e) {
                                        e.preventDefault();
                                        scale += e.deltaY * -0.001;
                                        scale = Math.min(Math.max(0.5, scale), 3);
                                        updateTransform();
                                    }

                                    // Déplacement de l'image
                                    image.addEventListener('mousedown', (e) => {
                                        if (e.button === 0) { // Clic gauche
                                            isDragging = true;
                                            startX = e.clientX - offsetX;
                                            startY = e.clientY - offsetY;
                                            image.style.cursor = 'grabbing';
                                        }
                                    });

                                    document.addEventListener('mousemove', (e) => {
                                        if (isDragging) {
                                            offsetX = e.clientX - startX;
                                            offsetY = e.clientY - startY;
                                            updateTransform();
                                        }
                                    });

                                    document.addEventListener('mouseup', () => {
                                        isDragging = false;
                                        image.style.cursor = 'grab';
                                    });

                                    // Mise à jour de la transformation
                                    function updateTransform() {
                                        image.style.transform = `scale(${scale}) translate(${offsetX}px, ${offsetY}px)`;
                                    }
                                });
                            </script>
                        @endif
                    @elseif ($isOfficeDocument)
                        {{-- 📁 Documents Office affichés avec PDF.js --}}
                        <div class="flex justify-end items-center space-x-2 mb-2 px-4 pt-2 flex-shrink-0">
                            <button
                                        class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm md:hidden"
                                        type="button"
                                        data-drawer-target="doc-aside"
                                        data-drawer-toggle="doc-aside"
                                        data-drawer-placement="left"
                                    >
                                        📋 Infos
                                    </button>
                            <button id="search-btn-office" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">🔍 Recherche</button>
                            <button id="zoom-out-office" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➖
                                Zoom -</button>
                            <button id="zoom-in-office" class="px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm">➕
                                Zoom +</button>
                        </div>

                        <div id="pdf-container-office" class="flex-1 overflow-auto bg-gray-100 p-4">
                            <div id="pdf-pages-container-office" class="flex flex-col items-center space-y-4"></div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const url = "{{ asset('storage/archives/' . $nom) }}";
                                const container = document.getElementById('pdf-pages-container-office');
                                const zoomInBtn = document.getElementById('zoom-in-office');
                                const zoomOutBtn = document.getElementById('zoom-out-office');

                                let pdfDoc = null;
                                let currentScale = 1;
                                const scaleStep = 0.25;
                                const initialScale = 1.0;

                                // Configuration du worker PDF.js
                                if (typeof pdfjsLib === 'undefined') {
                                    // Charger la bibliothèque PDF.js dynamiquement si elle n'est pas présente
                                    const script = document.createElement('script');
                                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                                    script.onload = () => {
                                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                        initializePDFViewer();
                                    };
                                    document.head.appendChild(script);
                                } else {
                                    // Si la bibliothèque est déjà chargée, initialiser directement
                                    initializePDFViewer();
                                }

                                function initializePDFViewer() {
                                    const renderAllPages = async () => {
                                        try {
                                            const pdf = await pdfjsLib.getDocument(url).promise;
                                            pdfDoc = pdf;
                                            const containerWidth = container.clientWidth;

                                            // Effacer le contenu précédent
                                            container.innerHTML = '';

                                            // Afficher toutes les pages
                                            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                                                const page = await pdf.getPage(pageNum);
                                                const scale = (containerWidth - 40) / page.getViewport({scale: 1}).width;
                                                const viewport = page.getViewport({scale: scale * currentScale});

                                                // Créer un canvas pour la page
                                                const canvas = document.createElement('canvas');
                                                const context = canvas.getContext('2d');

                                                canvas.height = viewport.height;
                                                canvas.width = viewport.width;
                                                canvas.className = 'shadow-md rounded bg-white';

                                                // Dessiner la page
                                                const renderContext = {
                                                    canvasContext: context,
                                                    viewport: viewport
                                                };

                                                await page.render(renderContext).promise;
                                                container.appendChild(canvas);
                                            }
                                        } catch (err) {
                                            container.innerHTML = `<p class="text-red-600">Erreur PDF : ${err.message}</p>`;
                                        }
                                    };

                                    // Dessiner le PDF
                                    pdfjsLib.getDocument(url).promise.then(pdf => {
                                        pdfDoc = pdf;
                                        renderAllPages();
                                    }).catch(err => {
                                        container.innerHTML = `<p class="text-red-600">Erreur PDF : ${err.message}</p>`;
                                    });

                                    // Contrôles de zoom
                                    zoomInBtn.addEventListener('click', () => {
                                        currentScale += scaleStep;
                                        renderAllPages();
                                    });

                                    zoomOutBtn.addEventListener('click', () => {
                                        if (currentScale > scaleStep) {
                                            currentScale -= scaleStep;
                                            renderAllPages();
                                        }
                                    });

                                    // Rafraîchir l'affichage quand la fenêtre est redimensionnée
                                    window.addEventListener('resize', () => {
                                        renderAllPages();
                                    });
                                }
                            });
                        </script>

                        <script>
                            // Gestion de l'impression pour les documents Office avec PDF.js
                            (function() {
                                'use strict';
                                const USER_PERMISSION = '{{ $permission ?? 'N' }}';
                                const HAS_EDIT_PERMISSION = USER_PERMISSION === 'E' || USER_PERMISSION === 'LE';

                                // Ajouter le bouton d'impression si l'utilisateur a les permissions E/LE
                                if (HAS_EDIT_PERMISSION) {
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const printButton = document.createElement('button');
                                        printButton.id = 'print-pdf-office';
                                        printButton.className = 'px-3 py-1 bg-gray-300 hover:bg-gray-400 text-black rounded text-sm';
                                        printButton.innerHTML = '🖨️ Imprimer';

                                        // Ajouter le bouton avant les boutons de zoom
                                        const buttonContainer = document.querySelector('.flex.justify-end.items-center.space-x-2.mb-2.px-4.pt-2.flex-shrink-0');
                                        const zoomOutBtn = document.getElementById('zoom-out-office');

                                        if (buttonContainer && zoomOutBtn) {
                                            buttonContainer.insertBefore(printButton, zoomOutBtn);
                                        }

                                        // Fonction d'impression pour les PDFs rendus avec PDF.js
                                        printButton.addEventListener('click', () => {
                                            // Créer une fenêtre d'impression avec les images des pages
                                            const printWindow = window.open('', '_blank');
                                            const container = document.getElementById('pdf-pages-container-office');
                                            const canvases = container.querySelectorAll('canvas');

                                            let printContent = '<html><head><title>Impression PDF</title>';
                                            printContent += '<style>body { margin: 0; padding: 20px; } img { width: 100%; margin-bottom: 20px; }</style>';
                                            printContent += '</head><body>';

                                            canvases.forEach(canvas => {
                                                printContent += `<img src="${canvas.toDataURL()}" />`;
                                            });

                                            printContent += '</body></html>';

                                            printWindow.document.write(printContent);
                                            printWindow.document.close();
                                            printWindow.focus();

                                            // Attendre que le contenu soit chargé avant d'imprimer
                                            setTimeout(() => {
                                                printWindow.print();
                                                printWindow.close();
                                            }, 500);
                                        });
                                    });
                                }

                                const IS_READONLY = USER_PERMISSION === 'L';

                                // Ajouter les protections pour les utilisateurs en lecture seule
                                if (IS_READONLY) {
                                    // Bloquer certaines fonctionnalités pour les PDFs
                                    document.addEventListener('contextmenu', function(e) {
                                        if (e.target.tagName === 'CANVAS') {
                                            e.preventDefault();
                                        }
                                    });

                                    document.addEventListener('dragstart', function(e) {
                                        if (e.target.tagName === 'CANVAS') {
                                            e.preventDefault();
                                        }
                                    });
                                }
                            })();

                        </script>

                        <script>
                            // Fonction de recherche dans les documents Office avec PDF.js
                            (function() {
                                'use strict';

                                // Vérifier si nous sommes sur un document Office
                                if (document.getElementById('pdf-container-office')) {
                                    document.addEventListener('DOMContentLoaded', () => {
                                        let pdfDocOffice = null;
                                        let searchContainerOffice = null;
                                        let searchInputOffice = null;
                                        let searchResultsOffice = null;
                                        let currentMatchIndexOffice = -1;
                                        let allMatchesOffice = [];

                                        // Fonction pour récupérer l'instance de PDF actuelle
                                        function getPdfDocOffice() {
                                            // On va chercher la variable globale ou récupérer l'instance du script principal
                                            if (typeof pdfjsLib !== 'undefined' && document.getElementById('pdf-pages-container-office')) {
                                                // On récupère l'URL du document
                                                const url = "{{ asset('storage/archives/' . $nom) }}";
                                                return pdfjsLib.getDocument(url).promise.then(pdf => {
                                                    pdfDocOffice = pdf;
                                                    return pdf;
                                                });
                                            }
                                            return Promise.resolve(null);
                                        }

                                        // Créer l'interface de recherche pour les documents Office
                                        function createSearchInterfaceOffice() {
                                            // Conteneur de recherche
                                            searchContainerOffice = document.createElement('div');
                                            searchContainerOffice.id = 'search-container-office';
                                            searchContainerOffice.style.cssText = `
                                                position: fixed;
                                                top: 50%;
                                                left: 50%;
                                                transform: translate(-50%, -50%);
                                                background: white;
                                                padding: 20px;
                                                border: 1px solid #ccc;
                                                border-radius: 5px;
                                                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                                z-index: 1000;
                                                display: none;
                                                min-width: 400px;
                                            `;

                                            // Input de recherche
                                            searchInputOffice = document.createElement('input');
                                            searchInputOffice.type = 'text';
                                            searchInputOffice.placeholder = 'Rechercher dans le document...';
                                            searchInputOffice.style.cssText = `
                                                width: calc(100% - 20px);
                                                padding: 10px;
                                                margin-bottom: 10px;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                            `;

                                            // Boutons de navigation
                                            const navigationDiv = document.createElement('div');
                                            navigationDiv.style.cssText = `
                                                display: flex;
                                                justify-content: space-between;
                                                align-items: center;
                                            `;

                                            const prevBtn = document.createElement('button');
                                            prevBtn.textContent = 'Précédent';
                                            prevBtn.style.cssText = `
                                                padding: 5px 10px;
                                                margin-right: 10px;
                                                background: #f0f0f0;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            const nextBtn = document.createElement('button');
                                            nextBtn.textContent = 'Suivant';
                                            nextBtn.style.cssText = `
                                                padding: 5px 10px;
                                                margin-right: 10px;
                                                background: #f0f0f0;
                                                border: 1px solid #ccc;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            const closeBtn = document.createElement('button');
                                            closeBtn.textContent = 'Fermer';
                                            closeBtn.style.cssText = `
                                                padding: 5px 10px;
                                                background: #e74c3c;
                                                color: white;
                                                border: none;
                                                border-radius: 3px;
                                                cursor: pointer;
                                            `;

                                            // Affichage des résultats
                                            searchResultsOffice = document.createElement('div');
                                            searchResultsOffice.style.cssText = `
                                                font-size: 12px;
                                                color: #666;
                                                margin-top: 10px;
                                                text-align: center;
                                            `;

                                            // Événements
                                            prevBtn.addEventListener('click', () => {
                                                if (allMatchesOffice.length > 0) {
                                                    currentMatchIndexOffice = (currentMatchIndexOffice - 1 + allMatchesOffice.length) % allMatchesOffice.length;
                                                    scrollToMatchOffice(currentMatchIndexOffice);
                                                }
                                            });

                                            nextBtn.addEventListener('click', () => {
                                                if (allMatchesOffice.length > 0) {
                                                    currentMatchIndexOffice = (currentMatchIndexOffice + 1) % allMatchesOffice.length;
                                                    scrollToMatchOffice(currentMatchIndexOffice);
                                                }
                                            });

                                            closeBtn.addEventListener('click', () => {
                                                searchContainerOffice.style.display = 'none';
                                            });

                                            searchInputOffice.addEventListener('keyup', (e) => {
                                                if (e.key === 'Enter') {
                                                    performSearchOffice();
                                                }
                                            });

                                            // Construction de l'interface
                                            navigationDiv.appendChild(prevBtn);
                                            navigationDiv.appendChild(nextBtn);
                                            navigationDiv.appendChild(closeBtn);

                                            searchContainerOffice.appendChild(searchInputOffice);
                                            searchContainerOffice.appendChild(navigationDiv);
                                            searchContainerOffice.appendChild(searchResultsOffice);
                                            document.body.appendChild(searchContainerOffice);

                                            // Bouton de recherche principal
                                            const searchBtn = document.getElementById('search-btn-office');
                                            if (searchBtn) {
                                                searchBtn.addEventListener('click', () => {
                                                    searchContainerOffice.style.display = searchContainerOffice.style.display === 'block' ? 'none' : 'block';
                                                    if (searchContainerOffice.style.display === 'block') {
                                                        searchInputOffice.focus();
                                                    }
                                                });
                                            }
                                        }

                                        // Fonction pour effectuer la recherche dans les documents Office
                                        async function performSearchOffice() {
                                            if (!pdfDocOffice) {
                                                // Essayer d'obtenir l'instance du PDF
                                                await getPdfDocOffice();
                                                if (!pdfDocOffice) return;
                                            }

                                            const searchTerm = searchInputOffice.value.trim();
                                            if (!searchTerm) {
                                                searchResultsOffice.textContent = '';
                                                return;
                                            }

                                            allMatchesOffice = [];
                                            currentMatchIndexOffice = -1;

                                            // Effectuer la recherche dans chaque page
                                            for (let pageNum = 1; pageNum <= pdfDocOffice.numPages; pageNum++) {
                                                const page = await pdfDocOffice.getPage(pageNum);
                                                const textContent = await page.getTextContent();
                                                const items = textContent.items;
                                                let allText = '';

                                                for (let i = 0; i < items.length; i++) {
                                                    allText += items[i].str;
                                                }

                                                // Chercher le terme dans le texte
                                                const regex = new RegExp(searchTerm, 'gi');
                                                let match;
                                                while ((match = regex.exec(allText)) !== null) {
                                                    allMatchesOffice.push({
                                                        pageNum: pageNum,
                                                        index: match.index,
                                                        text: match[0],
                                                        originalText: allText
                                                    });
                                                }
                                            }

                                            // Afficher les résultats
                                            if (allMatchesOffice.length > 0) {
                                                searchResultsOffice.textContent = `Trouvé ${allMatchesOffice.length} résultat(s)`;
                                                currentMatchIndexOffice = 0;
                                                scrollToMatchOffice(0);
                                            } else {
                                                searchResultsOffice.textContent = 'Aucun résultat trouvé';
                                            }
                                        }

                                        // Fonction pour aller à un résultat spécifique dans les documents Office
                                        function scrollToMatchOffice(index) {
                                            if (index < 0 || index >= allMatchesOffice.length) return;

                                            const match = allMatchesOffice[index];
                                            searchResultsOffice.textContent = `Résultat ${index + 1} sur ${allMatchesOffice.length}`;

                                            // Trouver la page correspondante et la faire défiler
                                            const canvas = document.querySelectorAll('#pdf-pages-container-office canvas')[match.pageNum - 1];
                                            if (canvas) {
                                                canvas.scrollIntoView({behavior: 'smooth', block: 'center'});
                                            }
                                        }

                                        // Initialiser l'interface de recherche pour les documents Office
                                        createSearchInterfaceOffice();
                                    });
                                }
                            })();
                        </script>
                        
                    @else
                        {{-- ❌ Format non reconnu --}}
                        <div
                            class="w-full h-full bg-yellow-100 p-6 rounded-bl-lg flex items-center justify-center text-gray-700 flex-1">
                            <p>Ce format de fichier n'est pas prévisualisable directement. <br> Téléchargez-le ou
                                ouvrez-le dans une application compatible.</p>
                        </div>
                    @endif
                </div>
                {{-- aside pour Pc --}}
                <aside id="doc-aside-pc"
                    class="bg-gray-50 p-6 dark:bg-gray-700 rounded-br-lg border-l border-gray-200 dark:border-gray-600 space-y-3 overflow-y-auto sm:hidden hidden lg:block" >
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Informations sur le document</h2>
                    <ul class="mt-4 space-y-2 text-gray-600 dark:text-gray-400">
                        <li><strong>Titre :</strong> {{ $document->nom }}</li>
                        <li><strong>Type du fichier :</strong> {{ $document->type }}</li>
                        <li><strong>Auteur :</strong> {{ $document->user->name }}</li>
                        @php
                            $lines = explode("\n", $document->content); // Divise le contenu en lignes
                            $lastLine = end($lines); // Récupère la dernière ligne
                        @endphp
                        <li><strong>Mot clé de recherche :</strong> {{ $lastLine }}</li>
                        <li><strong>Date de création :</strong> {{ $document->created_at->format('d/m/Y') }}</li>
                    </ul>
                    <div class="pdf-viewer-container">

                        {{-- Infos document --}}
                        <div class="document-info mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        Créé par : {{ $document->user->name ?? 'Utilisateur supprimé' }}
                                    </p>
                                </div>

                                {{-- Badge de permission --}}
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">{{ $this->getPermissionIcon() }}</span>
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-full
                                        @if ($permission === 'LE') bg-green-100 text-green-800
                                        @elseif($permission === 'E') bg-blue-100 text-blue-800
                                        @elseif($permission === 'L') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $this->getPermissionLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Messages de session --}}
                        @if (session()->has('message'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ session('message') }}
                                </div>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        {{-- Bloc accès refusé --}}
                        @if (!$hasAccess)
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">🚫</div>
                                <h2 class="text-xl font-semibold text-gray-700 mb-2">Accès refusé</h2>
                                <p class="text-gray-600">Vous n'avez pas la permission d'accéder à ce document.</p>
                            </div>
                        @else
                            {{-- Viewer PDF --}}
                            <div class="pdf-content mb-6">
                                <div
                                    class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                    {{-- <div class="text-4xl mb-4">📄</div>
                                    <p class="text-gray-600">Visualiseur PDF</p>
                                    <p class="text-sm text-gray-500 mt-2">Le contenu du PDF sera affiché ici</p> --}}
                                </div>
                            </div>

                            {{-- Boutons d'action --}}
                            <div class="actions-buttons">
                                <div class="flex flex-col items-center space-y-2 w-full">

                                    {{-- Message --}}
                                    @if ($showMessageButton)
                                        <a href="{{ route('tag', $document->id) }}">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                                </svg>
                                                Laisser un message
                                            </button>
                                        </a>
                                    @endif

                                    {{-- Ouvrir --}}
                                    @if ($showOpenButton)
                                        <a href="{{ asset('storage/' . $document->filename) }}" target="_blank">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                                Ouvrir hors de l'application
                                            </button>
                                        </a>
                                    @endif

                                    {{-- Éditer --}}
                                    @if ($showEditButton)
                                        <a href="{{ route('documents.edit', $document->id) }}" target="_blank">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-green-500 rounded-lg hover:bg-green-600 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Éditer document
                                            </button>
                                        </a>
                                    @endif
                                </div>

                                {{-- Aucun bouton --}}
                                @if (!$showMessageButton && !$showOpenButton && !$showEditButton)
                                    <div class="text-center py-4">
                                        <p class="text-gray-500">Aucune action disponible pour ce document.</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Infos de debug (dev uniquement) --}}
                        @if (config('app.debug'))
                            <div class="debug-info mt-6 p-4 bg-gray-100 rounded-lg border-l-4 border-gray-400">
                                <h4 class="font-semibold text-gray-700 mb-2">Informations de debug</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div><strong>Permission actuelle:</strong> {{ $permission ?? 'Aucune' }}</div>
                                    <div><strong>Accès autorisé:</strong> {{ $hasAccess ? 'Oui' : 'Non' }}</div>
                                    <div><strong>Utilisateur:</strong> {{ auth()->user()->name ?? 'Non connecté' }}
                                    </div>
                                    <div><strong>Propriétaire du document:</strong>
                                        {{ $document->user->name ?? 'Inconnu' }}</div>
                                    <div><strong>Boutons visibles:</strong>
                                        @if ($showMessageButton)
                                            Message
                                        @endif
                                        @if ($showOpenButton)
                                            Ouvrir
                                        @endif
                                        @if ($showEditButton)
                                            Éditer
                                        @endif
                                        @if (!$showMessageButton && !$showOpenButton && !$showEditButton)
                                            Aucun
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </aside>

                {{-- aside pour mobile --}}
                <aside id="doc-aside" class="fixed top-0 left-0 z-40 w-80 h-screen transition-transform -translate-x-full md:translate-x-0 bg-gray-50 p-6 dark:bg-gray-700 rounded-br-lg border-l border-gray-200 dark:border-gray-600 space-y-3 overflow-y-auto md:block">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Informations sur le document</h2>
                        <button
                            type="button"
                            data-drawer-target="doc-aside"
                            data-drawer-toggle="doc-aside"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white md:hidden"
                        >
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">Fermer menu</span>
                        </button>
                    </div>
                    <ul class="mt-4 space-y-2 text-gray-600 dark:text-gray-400">
                        <li><strong>Titre :</strong> {{ $document->nom }}</li>
                        <li><strong>Type du fichier :</strong> {{ $document->type }}</li>
                        <li><strong>Auteur :</strong> {{ $document->user->name }}</li>
                        @php
                            $lines = explode("\n", $document->content); // Divise le contenu en lignes
                            $lastLine = end($lines); // Récupère la dernière ligne
                        @endphp
                        <li><strong>Mot clé de recherche :</strong> {{ $lastLine }}</li>
                        <li><strong>Date de création :</strong> {{ $document->created_at->format('d/m/Y') }}</li>
                    </ul>
                    <div class="pdf-viewer-container">

                        {{-- Infos document --}}
                        <div class="document-info mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        Créé par : {{ $document->user->name ?? 'Utilisateur supprimé' }}
                                    </p>
                                </div>

                                {{-- Badge de permission --}}
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">{{ $this->getPermissionIcon() }}</span>
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-full
                                        @if ($permission === 'LE') bg-green-100 text-green-800
                                        @elseif($permission === 'E') bg-blue-100 text-blue-800
                                        @elseif($permission === 'L') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $this->getPermissionLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Messages de session --}}
                        @if (session()->has('message'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ session('message') }}
                                </div>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        {{-- Bloc accès refusé --}}
                        @if (!$hasAccess)
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">🚫</div>
                                <h2 class="text-xl font-semibold text-gray-700 mb-2">Accès refusé</h2>
                                <p class="text-gray-600">Vous n'avez pas la permission d'accéder à ce document.</p>
                            </div>
                        @else
                            {{-- Viewer PDF --}}
                            <div class="pdf-content mb-6">
                                <div
                                    class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                    {{-- <div class="text-4xl mb-4">📄</div>
                                    <p class="text-gray-600">Visualiseur PDF</p>
                                    <p class="text-sm text-gray-500 mt-2">Le contenu du PDF sera affiché ici</p> --}}
                                </div>
                            </div>

                            {{-- Boutons d'action --}}
                            <div class="actions-buttons">
                                <div class="flex flex-col items-center space-y-2 w-full">

                                    {{-- Message --}}
                                    @if ($showMessageButton)
                                        <a href="{{ route('tag', $document->id) }}">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                                </svg>
                                                Laisser un message
                                            </button>
                                        </a>
                                    @endif

                                    {{-- Ouvrir --}}
                                    @if ($showOpenButton)
                                        <a href="{{ asset('storage/' . $document->filename) }}" target="_blank">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                                Ouvrir hors de l'application
                                            </button>
                                        </a>
                                    @endif

                                    {{-- Éditer --}}
                                    @if ($showEditButton)
                                        <a href="{{ route('documents.edit', $document->id) }}" target="_blank">
                                            <button type="button"
                                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-green-500 rounded-lg hover:bg-green-600 focus:ring-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Éditer document
                                            </button>
                                        </a>
                                    @endif
                                </div>

                                {{-- Aucun bouton --}}
                                @if (!$showMessageButton && !$showOpenButton && !$showEditButton)
                                    <div class="text-center py-4">
                                        <p class="text-gray-500">Aucune action disponible pour ce document.</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Infos de debug (dev uniquement) --}}
                        @if (config('app.debug'))
                            <div class="debug-info mt-6 p-4 bg-gray-100 rounded-lg border-l-4 border-gray-400">
                                <h4 class="font-semibold text-gray-700 mb-2">Informations de debug</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div><strong>Permission actuelle:</strong> {{ $permission ?? 'Aucune' }}</div>
                                    <div><strong>Accès autorisé:</strong> {{ $hasAccess ? 'Oui' : 'Non' }}</div>
                                    <div><strong>Utilisateur:</strong> {{ auth()->user()->name ?? 'Non connecté' }}
                                    </div>
                                    <div><strong>Propriétaire du document:</strong>
                                        {{ $document->user->name ?? 'Inconnu' }}</div>
                                    <div><strong>Boutons visibles:</strong>
                                        @if ($showMessageButton)
                                            Message
                                        @endif
                                        @if ($showOpenButton)
                                            Ouvrir
                                        @endif
                                        @if ($showEditButton)
                                            Éditer
                                        @endif
                                        @if (!$showMessageButton && !$showOpenButton && !$showEditButton)
                                            Aucun
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            const USER_PERMISSION = '{{ $permission ?? 'N' }}'; // L = Lecture
            const IS_READONLY = USER_PERMISSION === 'L';

            if (!IS_READONLY) return;

            alert('[🔒] Mode lecture seule activé');

            // 🔒 Blocage clic droit global
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showProtectionMessage('Clic droit désactivé');
            });

            // 🔒 Blocage raccourcis clavier dangereux
            document.addEventListener('keydown', function(e) {
                const key = e.key.toLowerCase();
                const ctrl = e.ctrlKey;
                const shift = e.shiftKey;
                const alt = e.altKey;

                const blocked = [
                    (ctrl && key === 's'), // Enregistrer
                    (ctrl && key === 'p'), // Imprimer
                    (ctrl && key === 'c'), // Copier
                    (ctrl && key === 'x'), // Couper
                    (ctrl && key === 'a'), // Tout sélectionner
                    (key === 'f12'), // Dev tools
                    (ctrl && shift && key === 'i'),
                    (ctrl && key === 'u'), // Source
                    (ctrl && key === 't'), // Nouvel onglet
                    (ctrl && key === 'f'), // Rechercher
                    (ctrl && key === 'h'), // Historique
                ];

                if (blocked.some(Boolean)) {
                    e.preventDefault();
                    e.stopPropagation();
                    showProtectionMessage(`Raccourci ${key.toUpperCase()} bloqué`);
                }
            });

            // 🔒 Blocage impression (CSS print)
            const style = document.createElement('style');
            style.textContent = `
                    @media print {
                        body * { display: none !important; }
                        body::after {
                            content: "Impression désactivée - Document protégé, vous êtes en mode lecture seul:\ 🔒 Blocage impression veillez bien contacte votre Administrateur";
                            display: block;
                            font-size: 20px;
                            text-align: center;
                            padding: 200px;
                        }
                    }
                `;

            document.head.appendChild(style);

            // 🔒 Blocage copier
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                showProtectionMessage('Copie désactivée');
                if (e.clipboardData) {
                    e.clipboardData.setData('text/plain', 'Document protégé - copie interdite');
                }
            });

            function showProtectionMessage(message) {
                let alertDiv = document.getElementById('protection-alert');
                if (!alertDiv) {
                    alertDiv = document.createElement('div');
                    alertDiv.id = 'protection-alert';
                    alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #dc2626;
                color: white;
                padding: 10px 16px;
                border-radius: 6px;
                font-family: Arial, sans-serif;
                z-index: 9999;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    `;
                    document.body.appendChild(alertDiv);
                }

                alertDiv.textContent = message;
                alertDiv.style.display = 'block';

                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 3000);
            }

        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggle-aside');
            const aside = document.getElementById('doc-aside');
            const mainViewer = document.getElementById('main-viewer');

            let isHidden = false;

            toggleBtn.addEventListener('click', () => {
                isHidden = !isHidden;

                if (isHidden) {
                    aside.classList.add('hidden');
                    mainViewer.classList.remove('col-span-3');
                    mainViewer.classList.add('col-span-4');
                    toggleBtn.innerHTML = '➡️ Afficher infos';
                } else {
                    aside.classList.remove('hidden');
                    mainViewer.classList.remove('col-span-4');
                    mainViewer.classList.add('col-span-3');
                    toggleBtn.innerHTML = '⬅️ Masquer infos';
                }
            });
        });
    </script>

</div>
