<!-- Leaflet (carte dashboard / kiosques) : chargé globalement pour que la carte fonctionne après navigation AJAX -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Scripts -->
<script src="{{ asset('assets/js/core.bundle.js') }}" data-navigate-once></script>
<script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}" data-navigate-once></script>
<script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}" data-navigate-once></script>
<script src="{{ asset('assets/js/layouts/demo1.js') }}" data-navigate-once></script>

<!-- Compiled App Scripts -->
@vite(['resources/js/app.js'])
<!-- End of Scripts -->
