<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map {
        height: 75vh;
        width: 100%;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border: 1px solid #e5e7eb;
    }

    /* Estilos para o marcador personalizado */
    .custom-marker-icon {
        background-color: #008000; /* Verde padrão para disponível */
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
        position: relative;
        /* Centraliza o ícone no ponto */
    }
    .custom-marker-icon.unavailable {
        background-color: #FF0000; /* Vermelho para indisponível */
    }
    .custom-marker-icon.unavailable::after {
        border-top-color: #FF0000; /* Cor do triângulo, igual ao fundo vermelho */
    }
</style>

<?php
$arenas_mapa = Arena::getArenasComHorariosParaMapa();
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-6xl mx-auto w-full pt-32"> <!-- Adicionado pt-32 para empurrar o conteúdo para baixo, evitando sobreposição com o botão fixo -->
                <!-- Botão Voltar ao Início -->
                <!-- Novo contêiner fixo para o botão "Voltar ao Início" -->
                <div class="fixed top-16 left-0 right-0 z-30 bg-gray-100 p-4 shadow-md border-b border-gray-200">
                    <!-- O botão agora ocupa a largura total do contêiner fixo e é centralizado, com um estilo mais moderno -->
                    <a href="principal.php" class="btn btn-md w-full max-w-6xl mx-auto block bg-gradient-to-r from-blue-600 to-blue-800 text-white font-bold shadow-lg hover:from-blue-700 hover:to-blue-900 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" /></svg>
                        Voltar ao Início
                    </a>
                </div>

                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center mb-2">
                        <span class="text-5xl">🗺️</span> <!-- Ícone de mapa -->
                    </div>
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-500 to-pink-500 drop-shadow-lg mb-2">Mapa de Arenas</h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">Encontre arenas próximas e veja a disponibilidade de horários para hoje.
                    </p>
                </div>

                <!-- Map Container -->
                <div id="map"></div>
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

            if (arenasData.length === 0) {
                document.getElementById('map').innerHTML = '<p class="text-center text-gray-500 p-8">Nenhuma arena com localização definida foi encontrada.</p>';
                return;
            }

            // Filtra arenas com coordenadas válidas
            const validArenas = arenasData.filter(a => a.latitude && a.longitude && parseFloat(a.latitude) !== 0 && parseFloat(a.longitude) !== 0);

            // Define o centro e zoom inicial do mapa
            let centerLat = -15.7801; // Centro do Brasil (Brasília)
            let centerLng = -47.9292;
            let zoomLevel = 4; // Zoom para o Brasil
            let bounds = null;

            if (validArenas.length > 0) {
                const latLngs = validArenas.map(arena => [parseFloat(arena.latitude), parseFloat(arena.longitude)]);
                bounds = L.latLngBounds(latLngs);
                // Se houver apenas uma arena, centraliza nela com um zoom mais próximo
                if (validArenas.length === 1) {
                    centerLat = parseFloat(validArenas[0].latitude);
                    centerLng = parseFloat(validArenas[0].longitude);
                    zoomLevel = 15; // Zoom mais próximo para uma única arena
                } else {
                    // Para múltiplas arenas, o fitBounds será usado, mas um zoom padrão razoável
                    zoomLevel = 10;
                }
            }

            const map = L.map('map').setView([centerLat, centerLng], zoomLevel);

            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Ajusta o mapa para cobrir todas as arenas se houver mais de uma
            if (bounds && validArenas.length > 1) {
                map.fitBounds(bounds, { padding: [50, 50] }); // Adiciona um padding para não colar nas bordas
            }

            validArenas.forEach(arena => {
                const hasAvailableSlots = parseInt(arena.horarios_disponiveis_hoje) > 0;
                const markerClass = hasAvailableSlots ? 'custom-marker-icon' : 'custom-marker-icon unavailable';

                const customIcon = L.divIcon({
                    className: markerClass,
                    iconSize: [40, 40], // Largura e Altura iguais para um círculo perfeito
                    iconAnchor: [15, 15], // Metade da largura e altura para centralizar o ícone no ponto
                    popupAnchor: [0, -15], // Metade da altura para o popup aparecer acima do círculo
                    html: `<span>${arena.horarios_disponiveis_hoje}</span>` // Exibe a quantidade de horários dentro do marcador
                });

                const marker = L.marker([arena.latitude, arena.longitude], { icon: customIcon }).addTo(map);

                const popupContent = `
                    <div class="font-sans">
                        <h3 class="text-lg font-bold mb-1">${arena.bandeira} ${arena.titulo}</h3>
                        <p class="text-sm text-gray-600 mb-2">${arena.endereco || 'Endereço não informado'}</p>
                        <div class="border-t pt-2 mt-2">
                            <p class="text-base font-semibold text-blue-600">
                                ${arena.horarios_disponiveis_hoje} horários abertos hoje
                            </p>
                        </div>
                        <div class="mt-3">
                            <a href="arena-page.php?id=${arena.id}" class="btn btn-sm btn-primary w-full">Fazer Reserva</a>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);
            });
        });
    </script>
</body>
</html>