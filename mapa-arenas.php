<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map-card {
        position: relative;
        z-index: 1;
        height: 60vh; /* Altura ajustada para a visualização em card */
        width: 100%;
        border-radius: 1rem;
    }

    #fullscreen-map {
        height: 100%;
        width: 100%;
    }

    /* Estilos para o marcador personalizado (formato de pino) */
    .custom-marker-icon {
        background-color: #10b981; /* Verde (disponível) */
        width: 40px;
        height: 40px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    .custom-marker-icon-inner {
        transform: rotate(45deg); /* Gira o conteúdo de volta para a posição normal */
    }
    .custom-marker-icon.unavailable {
        background-color: #ef4444; /* Vermelho (indisponível) */
    }
    .leaflet-control-locate {
        border: 2px solid rgba(0,0,0,0.2);
        background-clip: padding-box;
    }
</style>

<?php
$hi = date('H:i');
// --- Lógica de Filtros ---
// Pega os valores do GET ou define padrões
$data_selecionada = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
$hora_inicio = filter_input(INPUT_GET, 'hora_inicio', FILTER_SANITIZE_STRING) ?: $hi;
$hora_fim = filter_input(INPUT_GET, 'hora_fim', FILTER_SANITIZE_STRING) ?: '23:00';

// Busca as arenas já com os filtros aplicados.
// A classe Arena::getArenasComHorariosParaMapa() precisa ser atualizada para aceitar esses parâmetros.
$arenas_mapa = Arena::getArenasComHorariosParaMapa($data_selecionada, $hora_inicio, $hora_fim);
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>    

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-6xl mx-auto w-full">

                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-500 to-pink-500 drop-shadow-lg mb-2">Explore as Arenas</h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Encontre a quadra perfeita para seu próximo jogo. Use os filtros para ver a disponibilidade.
                    </p>
                </div>

                <!-- Filtros de Data e Horário -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6 top-16">
                    <form method="GET" action="mapa-arenas.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        <div class="form-control">
                            <label for="data" class="label-text font-semibold pb-1">Data</label>
                            <input type="date" id="data" name="data" value="<?= htmlspecialchars($data_selecionada) ?>" class="input input-bordered input-sm w-full">
                        </div>
                        <div class="form-control">
                            <label for="hora_inicio" class="label-text font-semibold pb-1">Das</label>
                            <select id="hora_inicio" name="hora_inicio" class="select select-bordered select-sm w-full">
                                <?php for ($h = 6; $h <= 22; $h++): $time = sprintf('%02d:00', $h); ?>
                                    <option value="<?= $time ?>" <?= ($hora_inicio == $time) ? 'selected' : '' ?>><?= $time ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-control">
                            <label for="hora_fim" class="label-text font-semibold pb-1">Até</label>
                            <select id="hora_fim" name="hora_fim" class="select select-bordered select-sm w-full">
                                <?php for ($h = 7; $h <= 23; $h++): $time = sprintf('%02d:00', $h); ?>
                                    <option value="<?= $time ?>" <?= ($hora_fim == $time) ? 'selected' : '' ?>><?= $time ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full lg:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Map Card Container -->
                <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-200 relative">
                    <div id="map-card"></div>
                    <div class="absolute top-6 right-6 z-10">
                        <button id="open-fullscreen-map" class="btn btn-primary shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 0h-4m4 0l-5-5" /></svg>
                            Ver em Tela Cheia
                        </button>
                    </div>
                </div>

                <!-- Fullscreen Map Modal -->
                <div id="fullscreen-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
                    <div class="relative w-full h-full">
                        <div id="fullscreen-map" class="w-full h-full rounded-lg"></div>
                        <button id="close-fullscreen-map" class="btn btn-circle btn-ghost absolute top-2 right-2 z-[1000] bg-white/80 hover:bg-white sm:top-6 sm:right-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const arenasData = <?= json_encode($arenas_mapa) ?>;
            const dataSelecionada = '<?= htmlspecialchars($data_selecionada) ?>';
            const openMapBtn = document.getElementById('open-fullscreen-map');
            const closeMapBtn = document.getElementById('close-fullscreen-map');
            const fullscreenModal = document.getElementById('fullscreen-modal');

            if (arenasData.length === 0) {
                document.getElementById('map-card').innerHTML = '<p class="text-center text-gray-500 p-8">Nenhuma arena com localização definida foi encontrada para os filtros selecionados.</p>';
                if(openMapBtn) openMapBtn.style.display = 'none';
                return;
            }

            // Filtra arenas com coordenadas válidas
            const validArenas = arenasData.filter(a => a.latitude && a.longitude && parseFloat(a.latitude) !== 0 && parseFloat(a.longitude) !== 0);

            let mapCard, fullscreenMap;
            let bounds = null;

            if (validArenas.length > 0) {
                const latLngs = validArenas.map(arena => [parseFloat(arena.latitude), parseFloat(arena.longitude)]);
                bounds = L.latLngBounds(latLngs);
            }

            function createCustomIcon(arena) {
                const hasAvailableSlots = parseInt(arena.horarios_disponiveis_hoje) > 0;
                const markerClass = hasAvailableSlots ? 'custom-marker-icon' : 'custom-marker-icon unavailable';
                return L.divIcon({
                    className: markerClass,
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -40],
                    html: `<div class="custom-marker-icon-inner">${arena.horarios_disponiveis_hoje}</div>`
                });
            }

            function createPopupContent(arena) {
                const dataFormatada = new Date(dataSelecionada + 'T00:00:00').toLocaleDateString('pt-BR', { timeZone: 'America/Sao_Paulo' });
                const hasAvailableSlots = parseInt(arena.horarios_disponiveis_hoje) > 0;
                return `
                    <div class="font-sans w-64">
                        <h3 class="text-lg font-bold mb-1">${arena.bandeira} ${arena.titulo}</h3>
                        <p class="text-sm text-gray-600 mb-2">${arena.endereco || 'Endereço não informado'}</p>
                        <div class="border-t pt-2 mt-2">
                            <p class="text-base font-semibold ${hasAvailableSlots ? 'text-green-600' : 'text-red-600'}">
                                ${arena.horarios_disponiveis_hoje} horários disponíveis em ${dataFormatada}
                            </p>
                        </div>
                        <div class="mt-3">
                            <a href="arena-page.php?id=${arena.id}" class="btn btn-sm btn-primary w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Ver Quadras e Agendar
                            </a>
                        </div>
                    </div>
                `;
            }

            function populateMap(mapInstance) {
                validArenas.forEach(arena => {
                    const customIcon = createCustomIcon(arena);
                    const marker = L.marker([arena.latitude, arena.longitude], { icon: customIcon }).addTo(mapInstance);
                    marker.bindPopup(createPopupContent(arena));
                });
            }

            // Initialize Card Map
            mapCard = L.map('map-card').setView([-15.7801, -47.9292], 4);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(mapCard);
            
            populateMap(mapCard);
            if (bounds) {
                mapCard.fitBounds(bounds, { padding: [30, 30] });
            }

            // Add Locate Control
            L.control.locate({
                position: 'topleft',
                drawCircle: false,
                showPopup: false
            }).addTo(mapCard);

            // Event Listeners for Fullscreen Modal
            if (openMapBtn) {
                openMapBtn.addEventListener('click', () => {
                    fullscreenModal.classList.remove('hidden');
                    
                    // Initialize map only if it hasn't been initialized yet
                    if (!fullscreenMap) {
                        fullscreenMap = L.map('fullscreen-map').setView([-15.7801, -47.9292], 4);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
                        }).addTo(fullscreenMap);
                        populateMap(fullscreenMap);
                    }

                    // Add Locate Control
                    L.control.locate({
                        position: 'topleft',
                        drawCircle: false,
                        showPopup: false
                    }).addTo(fullscreenMap);
                    
                    // Always invalidate size and fit bounds when opening
                    setTimeout(() => {
                        fullscreenMap.invalidateSize();
                        if (bounds) {
                            fullscreenMap.fitBounds(bounds, { padding: [50, 50] });
                        }
                    }, 10); // Small delay to ensure container is visible
                });
            }

            if (closeMapBtn) {
                closeMapBtn.addEventListener('click', () => {
                    fullscreenModal.classList.add('hidden');
                });
            }
        });
    </script>

    <!-- Leaflet Locate Control Plugin -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js" charset="utf-8"></script>

</body>
</html>