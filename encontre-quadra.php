<?php
// We don't need a full session check here, as it's a public page.
// We'll just include the global config for class autoloading.
require_once '#_global.php';
$hi = date('H:i');
// Pega os valores do GET ou define padrões
$filtro_aplicado = isset($_GET['data']) || isset($_GET['hora_inicio']) || isset($_GET['hora_fim']);
$data_selecionada = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
$hora_inicio = filter_input(INPUT_GET, 'hora_inicio', FILTER_SANITIZE_STRING) ?: $hi;
$hora_fim = filter_input(INPUT_GET, 'hora_fim', FILTER_SANITIZE_STRING) ?: '23:00';

$arenas_mapa = Arena::getArenasComHorariosParaMapa($data_selecionada, $hora_inicio, $hora_fim);

?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontre Quadras - DUPLA</title>
    <meta name="description" content="Encontre as melhores quadras de Beach Tennis perto de você. Veja a disponibilidade e reserve seu horário.">
    
    <!-- DaisyUI and Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet Locate Control CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .map-container {
            height: 500px;
            width: 100%;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            border: 1px solid #e5e7eb;
            z-index: 1;
        }
        #fullscreen-map {
            height: 100%;
            width: 100%;
        }
        .custom-marker-icon {
            background-color: #10b981; /* emerald-500 */
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
            transform: rotate(45deg);
        }
        .custom-marker-icon.unavailable {
            background-color: #ef4444; /* red-500 */
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navbar -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm fixed w-full z-40">
        <div class="navbar max-w-7xl mx-auto">
            <div class="navbar-start">
                <div class="dropdown">
                    <label tabindex="0" class="btn btn-ghost lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="https://promocao.appdupla.com">Promoção</a></li>
                        <li><a href="https://site.appdupla.com">Sobre Nós</a></li>
                        <li>
                            <a href="dayuse.php" class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500 hover:scale-110 transition-transform">DAYUSE 🔥</a>
                        </li>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-ghost text-xl font-bold">DUPLA</a>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li><a href="https://promocao.appdupla.com" class="font-semibold">Promoção</a></li>
                    <li><a href="https://site.appdupla.com" class="font-semibold">Sobre Nós</a></li>
                    <li>
                        <a href="dayuse.php" class="font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500 hover:scale-110 transition-transform">DAYUSE 🔥</a>
                    </li>
                </ul>
            </div>
            <div class="navbar-end gap-2">
                <a href="index.php" class="btn btn-primary">Acessar Dupla</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-24">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight">
                    Quadras para você jogar <span class="text-primary">qualquer hora</span>
                </h1>
                <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-600">
                    Explore as arenas parceiras, encontre a quadra perfeita e veja a disponibilidade em tempo real.
                </p>
            </div>

            <!-- Map Card -->
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 relative">
                <div id="map-card" class="map-container"></div>
                <div class="absolute top-4 right-4 z-10">
                    <button id="open-fullscreen-map" class="btn btn-primary shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 0h-4m4 0l-5-5" /></svg>
                        Ver Mapa
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- Fullscreen Map Modal -->
    <div id="fullscreen-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center">
        <div class="relative w-full h-full p-4">
            <div id="fullscreen-map" class="w-full h-full rounded-lg"></div>
            <button id="close-fullscreen-map" class="btn btn-circle btn-ghost absolute top-6 right-6 z-[1000] bg-white/80 hover:bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <!-- Floating Filter Button -->
    <div class="fixed bottom-6 right-6 z-40">
        <button class="btn btn-primary btn-circle shadow-lg animate-pulse" onclick="filter_modal.showModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </div>

    <!-- Filter Modal -->
    <dialog id="filter_modal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Filtrar Horários Disponíveis</h3>
            <form method="GET" action="encontre-quadra.php" class="py-4 space-y-4">
                <div class="form-control">
                    <label for="data_modal" class="label-text font-semibold pb-1 text-sm">Data</label>
                    <input type="date" id="data_modal" name="data" value="<?= htmlspecialchars($data_selecionada) ?>" class="input input-bordered input-primary w-full">
                </div>
                <div class="form-control">
                    <label for="hora_inicio_modal" class="label-text font-semibold pb-1 text-sm">Das</label>
                    <select id="hora_inicio_modal" name="hora_inicio" class="select select-bordered select-primary w-full">
                        <option value="<?= $hi ?>"> <?= $hi ?></option>
                        <?php for ($h = 6; $h <= 22; $h++): $time = sprintf('%02d:00', $h); ?>
                            <option value="<?= $time ?>" <?= ($hora_inicio == $time) ? 'selected' : '' ?>><?= $time ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label for="hora_fim_modal" class="label-text font-semibold pb-1 text-sm">Até</label>
                    <select id="hora_fim_modal" name="hora_fim" class="select select-bordered select-primary w-full">
                        <?php for ($h = 7; $h <= 23; $h++): $time = sprintf('%02d:00', $h); ?>
                            <option value="<?= $time ?>" <?= ($hora_fim == $time) ? 'selected' : '' ?>><?= $time ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="modal-action mt-6">
                    <form method="dialog"><button class="btn">Fechar</button></form>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Aplicar Filtro
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <!-- Toast Notification -->
    <?php if ($filtro_aplicado): ?>
    <div id="toast-notification" class="toast toast-top toast-center z-50 transition-opacity duration-500">
        <div class="alert alert-success shadow-lg">
            <span>Filtro aplicado com sucesso!</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Leaflet Locate Control JS -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js" charset="utf-8"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast notification logic
            const toast = document.getElementById('toast-notification');
            if (toast) {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    // After the transition, set display to none to prevent it from blocking clicks
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 500); // This duration should match your CSS transition (duration-500)
                }, 3000); // Start fading out after 3 seconds
            }

            const arenasData = <?= json_encode($arenas_mapa) ?>;
            const dataSelecionada = '<?= htmlspecialchars($data_selecionada) ?>';
            const openMapBtn = document.getElementById('open-fullscreen-map');
            const closeMapBtn = document.getElementById('close-fullscreen-map');
            const fullscreenModal = document.getElementById('fullscreen-modal');

            if (arenasData.length === 0) {
                document.getElementById('map-card').innerHTML = '<p class="text-center text-gray-500 p-8">Nenhuma arena com localização definida foi encontrada.</p>';
                openMapBtn.style.display = 'none';
                return;
            }

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
                        <h3 class="text-base font-bold mb-1 flex items-center gap-2">${arena.bandeira} ${arena.titulo}</h3>
                        <p class="text-xs text-gray-600 mb-2">${arena.endereco || 'Endereço não informado'}</p>
                        <div class="border-t pt-2 mt-2">
                            <p class="text-sm font-semibold ${hasAvailableSlots ? 'text-green-600' : 'text-red-600'}">
                                ${arena.horarios_disponiveis_hoje} horários disponíveis em ${dataFormatada}
                            </p>
                        </div>
                        <div class="mt-3">
                            <a href="reserva-arena.php?data=${dataSelecionada}&arena=${arena.id}" class="btn btn-sm btn-primary w-full">
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

            // Add Locate Control to Card Map
            L.control.locate({
                position: 'topleft',
                strings: {
                    title: "Mostrar minha localização"
                },
                flyTo: true,
                drawCircle: false,
                showPopup: false
            }).addTo(mapCard);

            // Event Listeners for Fullscreen Modal
            openMapBtn.addEventListener('click', () => {
                fullscreenModal.classList.remove('hidden');
                
                // Initialize map only if it hasn't been initialized yet
                if (!fullscreenMap) {
                    fullscreenMap = L.map('fullscreen-map').setView([-15.7801, -47.9292], 4);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
                    }).addTo(fullscreenMap);
                    populateMap(fullscreenMap);

                    // Add Locate Control to Fullscreen Map
                    L.control.locate({
                        position: 'topleft',
                        strings: {
                            title: "Mostrar minha localização"
                        },
                        flyTo: true,
                        drawCircle: false,
                        showPopup: false
                    }).addTo(fullscreenMap);
                }
                
                // Always invalidate size and fit bounds when opening
                setTimeout(() => {
                    fullscreenMap.invalidateSize();
                    if (bounds) {
                        fullscreenMap.fitBounds(bounds, { padding: [50, 50] });
                    }
                }, 10); // Small delay to ensure container is visible
            });

            closeMapBtn.addEventListener('click', () => {
                fullscreenModal.classList.add('hidden');
            });
        });
    </script>

</body>
</html>