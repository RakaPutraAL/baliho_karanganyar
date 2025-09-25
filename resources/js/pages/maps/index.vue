<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, onMounted, ref, watch,nextTick } from 'vue';

type Kecamatan = {
    kode: string;
    nama_kecamatan: string;
};

type Opd = {
    id: number;
    nama_opd: string;
};

type Baliho = {
    id: number;
    view: string;
    dimensi: string;
    jenis_kontruksi: string;
    alamat: string;
    opd?: Opd;
    kecamatan?: Kecamatan;
    latitude: string;
    longitude: string;
    foto?: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Peta Baliho', href: '/maps' }];
const page = usePage();
const balihos = page.props.balihos as Baliho[];

/* -------------------------------------------------
   Filter states
-------------------------------------------------- */
const selectedKecamatan = ref<string>('');
const selectedOpd = ref<string>('');
const selectedJenisKontruksi = ref<string>('');

/* kita tetap pakai showTable supaya bisa di-reset,
   tapi akan otomatis true saat filter berubah */
const showTable = ref<boolean>(false);

/* -------------------------------------------------
   Unique options
-------------------------------------------------- */
const allKecamatanOptions = computed(() => {
    const unique = new Set<string>();
    balihos.forEach((b) => b.kecamatan?.nama_kecamatan && unique.add(b.kecamatan.nama_kecamatan));
    return Array.from(unique).sort();
});

const allOpdOptions = computed(() => {
    const unique = new Set<string>();
    balihos.forEach((b) => b.opd?.nama_opd && unique.add(b.opd.nama_opd));
    return Array.from(unique).sort();
});

const allJenisKontruksiOptions = computed(() => {
    const unique = new Set<string>();
    balihos.forEach((b) => b.jenis_kontruksi && unique.add(b.jenis_kontruksi));
    return Array.from(unique).sort();
});

/* -------------------------------------------------
   Filtered list
-------------------------------------------------- */
const filteredBalihos = computed(() =>
    balihos.filter((b) => {
        const matchKecamatan = !selectedKecamatan.value || b.kecamatan?.nama_kecamatan === selectedKecamatan.value;
        const matchOpd = !selectedOpd.value || b.opd?.nama_opd === selectedOpd.value;
        const matchJenisKontruksi = !selectedJenisKontruksi.value || b.jenis_kontruksi === selectedJenisKontruksi.value; 
        return matchKecamatan && matchOpd && matchJenisKontruksi;
    }),
);



/* -------------------------------------------------
   Count helpers
-------------------------------------------------- */
const getKecamatanCount = (kecamatanName: string) =>
    balihos.filter((b) => b.kecamatan?.nama_kecamatan === kecamatanName && (!selectedOpd.value || b.opd?.nama_opd === selectedOpd.value)).length;

const getOpdCount = (opdName: string) =>
    balihos.filter((b) => b.opd?.nama_opd === opdName && (!selectedKecamatan.value || b.kecamatan?.nama_kecamatan === selectedKecamatan.value))
        .length;

const getJenisKontruksiCount = (jenisKontruksi: string) =>
    balihos.filter((b) => 
        b.jenis_kontruksi === jenisKontruksi && 
        (!selectedKecamatan.value || b.kecamatan?.nama_kecamatan === selectedKecamatan.value) &&
        (!selectedOpd.value || b.opd?.nama_opd === selectedOpd.value)
    ).length;


/* -------------------------------------------------
   Map
-------------------------------------------------- */
const balihoIcon = L.icon({
    iconUrl: 'https://simpel.karanganyarkab.go.id/markerreklame.png',
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40],
});

const pengaduanIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>`;
const closeIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>`;

let map: L.Map;
let markers: L.Marker[] = [];
let geoLayer: L.GeoJSON;

/* -------------------------------------------------
   Methods
-------------------------------------------------- */
function clearFilters() {
    selectedKecamatan.value = '';
    selectedOpd.value = '';
    selectedJenisKontruksi.value = '';
    showTable.value = false;
}

function applyFilters() {
    showTable.value = true;
    
    // Scroll ke tabel dengan animasi smooth setelah DOM ter-update
    nextTick(() => {
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            tableContainer.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest'
            });
        }
    });
}

function handleDelete(id: number) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('balihos.destroy', { baliho: id }));
    }
}

function updateMarkers() {
    markers.forEach((m) => map.removeLayer(m));
    markers = [];

    filteredBalihos.value.forEach((b) => {
        const fotoUrl = b.foto ? `/uploads/${b.foto}` : 'https://via.placeholder.com/600x400?text=No+Image';

        const popupContent = `
            <div style="display:flex;flex-direction:row;width:900px;max-width:95vw;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.15);font-family:'Segoe UI',sans-serif;position:relative;">
                <div style="flex:0 0 45%;background:#fafafa;border-right:2px solid #f0f0f0;">
                    <img src="${fotoUrl}" style="width:100%;height:100%;object-fit:cover;transition:transform .3s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" />
                </div>
                <div style="flex:1;padding:16px 20px;background:linear-gradient(135deg,#fdfdfd,#f8f9fa);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <h3 style="margin:0 0 12px;font-size:18px;color:#2c3e50;border-bottom:2px solid #e0e0e0;padding-bottom:6px;">📋 Detail Informasi</h3>
                        <ul style="padding:0;margin:0;list-style:none;font-size:14px;color:#555">
                            <li style="margin:8px 0"><strong>Nama OPD:</strong> ${b.opd?.nama_opd ?? '-'}</li>
                            <li style="margin:8px 0"><strong>View:</strong> ${b.view}</li>
                            <li style="margin:8px 0"><strong>Dimensi:</strong> ${b.dimensi}</li>
                            <li style="margin:8px 0"><strong>Jenis Konstruksi:</strong> ${b.jenis_kontruksi}</li>
                            <li style="margin:8px 0"><strong>Alamat:</strong> ${b.alamat}</li>
                            <li style="margin:8px 0"><strong>Kecamatan:</strong> ${b.kecamatan?.nama_kecamatan ?? '-'}</li>
                        </ul>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:8px;margin-top:12px;flex-wrap:wrap">
  <a href="https://www.google.com/maps?q=${b.latitude},${b.longitude}" target="_blank"
     style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
            background:#28a745;color:#fff;padding:6px 12px;
            border-radius:20px;font-size:14px;text-decoration:none">
     📍 <span>Share Lokasi</span>
  </a>

  <a href="https://wa.me/628112639332?text=Halo%20*DPMPTSP*,%20Saya%0ANama:%20...%0ADari:%20...%0ANIK%20/%20Email:%20...%0A%0Aada%20kendala%20terkait:%0A...." target="_blank"
     style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
            background:#ff0000;color:#fff;padding:6px 12px;
            border-radius:20px;font-size:14px;text-decoration:none">
     ${pengaduanIcon} <span>Pengaduan</span>
  </a>

  <button onclick="window.closePopup()"
     style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
            background:linear-gradient(to right,#36d1dc,#5b86e5);
            color:#fff;padding:6px 12px;
            border-radius:20px;border:none;font-size:14px;cursor:pointer">
     ${closeIcon} <span>Tutup</span>
  </button>
</div>

                </div>
            </div>
        `;

        const marker = L.marker([parseFloat(b.latitude), parseFloat(b.longitude)], { icon: balihoIcon })
            .addTo(map)
            .bindPopup(popupContent, { maxWidth: 950, minWidth: 600, autoPan: true });

        marker.on('click', () => {
            map.flyTo([parseFloat(b.latitude), parseFloat(b.longitude)], 17, { animate: true, duration: 1.0 });
            setTimeout(() => marker.openPopup(), 1200);
        });
        markers.push(marker);
    });
}

/* -------------------------------------------------
   Lifecycle
-------------------------------------------------- */
onMounted(() => {
    map = L.map('map').setView([-7.5949457, 110.9372971], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    fetch('/karanganyar.geojson')
        .then((r) => r.json())
        .then((data) => {
            geoLayer = L.geoJSON(data, {
                style: { color: '#ffcc00', weight: 2, opacity: 0.7, fillColor: '#ffcc00', fillOpacity: 0.15 },
                onEachFeature: (_, layer) => {
                    layer.on({
                        mouseover: (e) => e.target.setStyle({ weight: 3, color: '#ff9900', fillOpacity: 0.25 }),
                        mouseout: (e) => geoLayer.resetStyle(e.target),
                    });
                },
            }).addTo(map);
            map.fitBounds(geoLayer.getBounds());
        });

    (window as any).closePopup = () => map.closePopup();
    updateMarkers();
});

/* -------------------------------------------------
   Watch filters -> auto show table
-------------------------------------------------- */
watch([selectedKecamatan, selectedOpd ,selectedJenisKontruksi], () => {
    showTable.value = true; // <--- ini kunci otomatis tampil
    if (map) updateMarkers();
});
</script>

<template>
    <Head title="Peta Baliho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Ganti bagian header lama -->
        <div class="baliho-banner">
            <div class="background-elements">
                <!-- Floating Map Icons -->
                <div class="floating-icon map-icon-1">🗺️</div>
                <div class="floating-icon map-icon-2">📍</div>
                <div class="floating-icon map-icon-3">🗺️</div>
                <div class="floating-icon map-icon-4">📍</div>

                <!-- Floating Geometric Shapes -->
                <div class="geometric-shape shape-1"></div>
                <div class="geometric-shape shape-2"></div>
                <div class="geometric-shape shape-3"></div>
                <div class="geometric-shape shape-4"></div>

                <!-- Animated Lines -->
                <div class="animated-line line-1"></div>
                <div class="animated-line line-2"></div>
                <div class="animated-line line-3"></div>

                <!-- Floating Particles -->
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
                <div class="particle particle-6"></div>
            </div>

            <!-- Main Content -->
            <div class="banner-content">
                <div class="logo-container">
                    <img src="/logo-karanganyar.png" alt="Logo Karanganyar" class="banner-logo" />
                    <div class="logo-glow"></div>
                </div>
                <div class="title-container">
                    <h1 class="banner-title">
                        <span class="title-word word-1">Baliho</span>
                        <span class="title-word word-2">Karanganyar</span>
                    </h1>
                    <div class="title-underline"></div>
                </div>
            </div>

            <!-- Corner Decorations -->
            <div class="corner-decoration corner-top-left"></div>
            <div class="corner-decoration corner-top-right"></div>
            <div class="corner-decoration corner-bottom-left"></div>
            <div class="corner-decoration corner-bottom-right"></div>
        </div>

        <!-- Filter Controls -->
        <!-- Filter Controls -->
        <div class="filter-container">
            <div class="filter-wrapper">
                 <div class="filter-item">
                    <label for="konstruksi-filter" class="filter-label">🏗️ Filter Jenis Konstruksi</label>
                    <select id="konstruksi-filter" v-model="selectedJenisKontruksi" class="filter-select">
                        <option value="">Semua Jenis ({{ balihos.length }})</option>
                        <option v-for="jk in allJenisKontruksiOptions" :key="jk" :value="jk">{{ jk }} ({{ getJenisKontruksiCount(jk) }})</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="kecamatan-filter" class="filter-label">🗺️ Filter Kecamatan</label>
                    <select id="kecamatan-filter" v-model="selectedKecamatan" class="filter-select">
                        <option value="">Semua Kecamatan ({{ balihos.length }})</option>
                        <option v-for="k in allKecamatanOptions" :key="k" :value="k">{{ k }} ({{ getKecamatanCount(k) }})</option>
                    </select>
                </div>


                <div class="filter-item">
                    <label for="opd-filter" class="filter-label">🏢 Filter OPD Pemilik Aset</label>
                    <select id="opd-filter" v-model="selectedOpd" class="filter-select">
                        <option value="">Semua OPD ({{ balihos.length }})</option>
                        <option v-for="o in allOpdOptions" :key="o" :value="o">{{ o }} ({{ getOpdCount(o) }})</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <div class="action-buttons">
                        <!-- Tombol Tampilkan Tabel bisa dihapus jika ingin sepenuhnya otomatis -->
                        <button @click="applyFilters" class="apply-button">📊 Tampilkan Tabel</button>
                        <button @click="clearFilters" class="clear-button">🔄 Reset Filter</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="map"></div>

        <!-- Tabel Data Terfilter -->
        <div v-if="showTable" class="table-container">
            <div class="table-header">
                <h3 class="table-title">📊 Data Baliho Hasil Filter</h3>
                <p class="table-subtitle">
                    <span v-if="selectedKecamatan && selectedOpd">
                        Filter: {{ selectedKecamatan }} & {{ selectedOpd }} - Menampilkan {{ filteredBalihos.length }} data
                    </span>
                    <span v-else-if="selectedKecamatan"> Filter: {{ selectedKecamatan }} - Menampilkan {{ filteredBalihos.length }} data </span>
                    <span v-else-if="selectedOpd"> Filter: {{ selectedOpd }} - Menampilkan {{ filteredBalihos.length }} data </span>
                    <span v-else> Semua Data - Menampilkan {{ filteredBalihos.length }} dari {{ balihos.length }} total data baliho </span>
                </p>
            </div>

            <div class="table-wrapper">
                <Table>
                    <TableCaption>Daftar Baliho Berdasarkan Filter yang Dipilih</TableCaption>
                    <TableHeader>
                        <TableRow class="table-header-row">
                            <TableHead class="table-head">Foto</TableHead>
                            <TableHead class="table-head">Nama OPD</TableHead>
                            <TableHead class="table-head">View</TableHead>
                            <TableHead class="table-head">Dimensi</TableHead>
                            <TableHead class="table-head">Jenis Konstruksi</TableHead>
                            <TableHead class="table-head">Alamat</TableHead>
                            <TableHead class="table-head">Kecamatan</TableHead>
                            <TableHead class="table-head text-center">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="b in filteredBalihos" :key="b.id" class="table-body-row">
                            <TableCell class="table-cell">
                                <img
                                    v-if="b.foto"
                                    :src="`/uploads/${b.foto}`"
                                    alt="Foto Baliho"
                                    class="h-16 w-16 cursor-pointer rounded-lg object-cover transition-all duration-300 hover:scale-110 hover:shadow-lg"
                                    @click="map && map.flyTo([parseFloat(b.latitude), parseFloat(b.longitude)], 17)"
                                />
                                <span v-else class="font-medium text-amber-600">📷 Tidak ada foto</span>
                            </TableCell>
                            <TableCell class="table-cell"
                                ><span class="font-medium text-blue-700">{{ b.opd?.nama_opd ?? '🏢 Tidak ada OPD' }}</span></TableCell >
                            <TableCell class="table-cell">{{ b.view }}</TableCell>
                            <TableCell class="table-cell">{{ b.dimensi }}</TableCell>
                            <TableCell class="table-cell">{{ b.jenis_kontruksi }}</TableCell>
                            <TableCell class="table-cell max-w-xs" :title="b.alamat"
                                ><div class="truncate">📍 {{ b.alamat }}</div></TableCell
                            >
                            <TableCell class="table-cell"
                                ><span class="inline-block rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800"
                                    >🗺️ {{ b.kecamatan?.nama_kecamatan ?? 'Tidak ada kecamatan' }}</span
                                ></TableCell
                            >
                            <TableCell class="table-cell text-center">
                                <Button
                                    class="transform bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-2 text-sm font-medium shadow-md transition-all duration-200 hover:scale-105 hover:from-blue-600 hover:to-blue-700 hover:shadow-lg"
                                    @click="map && map.flyTo([parseFloat(b.latitude), parseFloat(b.longitude)], 17)"
                                    >📍 Lihat di Peta</Button
                                >
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredBalihos.length === 0" class="table-empty-row">
                            <TableCell :colspan="10" class="py-12 text-center">
                                <div class="flex flex-col items-center space-y-3">
                                    <div class="text-6xl">📭</div>
                                    <div class="text-lg font-medium text-gray-600">Tidak ada data baliho</div>
                                    <div class="text-sm text-gray-500">yang sesuai dengan filter yang dipilih</div>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Judul */
/* Import font dari Google Fonts */
/* Import font dari Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&display=swap');

.baliho-banner {
    background: linear-gradient(135deg, #ffcc00, #ff9900, #ffcc00);
    background-size: 200% 200%;
    animation: gradientShift 6s ease-in-out infinite;
    padding: 24px;
    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    margin-bottom: 20px;
    border: 3px solid #e6b800;
    position: relative;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 120px;
}

/* Background Gradient Animation */
@keyframes gradientShift {
    0%,
    100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

/* Background Elements Container */
.background-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
}

/* Floating Map Icons */
.floating-icon {
    position: absolute;
    font-size: 24px;
    opacity: 0.6;
    animation-fill-mode: both;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.map-icon-1 {
    top: 15%;
    left: 10%;
    animation: floatLeftRight 8s ease-in-out infinite;
    animation-delay: 0s;
}

.map-icon-2 {
    top: 20%;
    right: 15%;
    animation: floatUpDown 6s ease-in-out infinite;
    animation-delay: 1s;
}

.map-icon-3 {
    bottom: 20%;
    left: 20%;
    animation: floatDiagonal 7s ease-in-out infinite;
    animation-delay: 2s;
}

.map-icon-4 {
    bottom: 15%;
    right: 10%;
    animation: floatCircular 9s ease-in-out infinite;
    animation-delay: 3s;
}

/* Geometric Shapes */
.geometric-shape {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(2px);
}

.shape-1 {
    width: 40px;
    height: 40px;
    top: 10%;
    left: 5%;
    border-radius: 50%;
    animation:
        rotate360 12s linear infinite,
        pulse 3s ease-in-out infinite;
}

.shape-2 {
    width: 30px;
    height: 30px;
    top: 70%;
    right: 8%;
    transform: rotate(45deg);
    animation:
        rotateReverse 15s linear infinite,
        scaleUpDown 4s ease-in-out infinite;
}

.shape-3 {
    width: 25px;
    height: 25px;
    bottom: 10%;
    left: 15%;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    animation:
        floatUpDown 6s ease-in-out infinite,
        colorShift 8s ease-in-out infinite;
    animation-delay: 1s;
}

.shape-4 {
    width: 35px;
    height: 20px;
    top: 25%;
    right: 25%;
    border-radius: 20px;
    animation:
        stretch 5s ease-in-out infinite,
        floatLeftRight 7s ease-in-out infinite;
    animation-delay: 2s;
}

/* Animated Lines */
.animated-line {
    position: absolute;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    height: 2px;
}

.line-1 {
    top: 30%;
    left: 0;
    width: 100%;
    animation: lineSlideRight 8s ease-in-out infinite;
}

.line-2 {
    bottom: 40%;
    right: 0;
    width: 100%;
    animation: lineSlideLeft 10s ease-in-out infinite;
    animation-delay: 2s;
}

.line-3 {
    top: 60%;
    left: 0;
    width: 100%;
    animation: lineSlideRight 12s ease-in-out infinite;
    animation-delay: 4s;
}

/* Floating Particles */
.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(255, 255, 255, 0.5);
}

.particle-1 {
    top: 25%;
    left: 12%;
    animation: particleFloat 6s ease-in-out infinite;
}

.particle-2 {
    top: 15%;
    right: 20%;
    animation: particleFloat 8s ease-in-out infinite;
    animation-delay: 1s;
}

.particle-3 {
    bottom: 30%;
    left: 8%;
    animation: particleFloat 7s ease-in-out infinite;
    animation-delay: 2s;
}

.particle-4 {
    bottom: 20%;
    right: 12%;
    animation: particleFloat 9s ease-in-out infinite;
    animation-delay: 3s;
}

.particle-5 {
    top: 45%;
    left: 18%;
    animation: particleFloat 5s ease-in-out infinite;
    animation-delay: 4s;
}

.particle-6 {
    top: 55%;
    right: 18%;
    animation: particleFloat 10s ease-in-out infinite;
    animation-delay: 5s;
}

/* Main Content Styling */
.banner-content {
    display: flex;
    align-items: center;
    gap: 20px;
    z-index: 10;
    position: relative;
}

.logo-container {
    position: relative;
}

.banner-logo {
    height: 80px;
    width: auto;
    object-fit: contain;
    animation: logoFloat 4s ease-in-out infinite;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    z-index: 2;
    position: relative;
}

.logo-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    animation: glowPulse 3s ease-in-out infinite;
    z-index: 1;
}

.title-container {
    position: relative;
}

.banner-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.8rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    gap: 12px;
}

.title-word {
    background: linear-gradient(45deg, #ffffff, #fff4cc, #ffffff);
    background-size: 200% 200%;
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: textShimmer 4s ease-in-out infinite;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    position: relative;
}

.word-1 {
    animation-delay: 0s;
}

.word-2 {
    animation-delay: 0.5s;
}

.title-underline {
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #ffffff, transparent);
    margin-top: 8px;
    animation: underlineGrow 6s ease-in-out infinite;
}

/* Corner Decorations */
.corner-decoration {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 3px solid rgba(255, 255, 255, 0.4);
}

.corner-top-left {
    top: 10px;
    left: 10px;
    border-right: none;
    border-bottom: none;
    animation: cornerPulse 4s ease-in-out infinite;
}

.corner-top-right {
    top: 10px;
    right: 10px;
    border-left: none;
    border-bottom: none;
    animation: cornerPulse 4s ease-in-out infinite;
    animation-delay: 0.5s;
}

.corner-bottom-left {
    bottom: 10px;
    left: 10px;
    border-right: none;
    border-top: none;
    animation: cornerPulse 4s ease-in-out infinite;
    animation-delay: 1s;
}

.corner-bottom-right {
    bottom: 10px;
    right: 10px;
    border-left: none;
    border-top: none;
    animation: cornerPulse 4s ease-in-out infinite;
    animation-delay: 1.5s;
}

/* Animation Keyframes */
@keyframes floatLeftRight {
    0%,
    100% {
        transform: translateX(0px) translateY(0px) rotate(0deg);
    }
    25% {
        transform: translateX(15px) translateY(-5px) rotate(5deg);
    }
    50% {
        transform: translateX(30px) translateY(0px) rotate(0deg);
    }
    75% {
        transform: translateX(15px) translateY(5px) rotate(-5deg);
    }
}

@keyframes floatUpDown {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

@keyframes floatDiagonal {
    0%,
    100% {
        transform: translate(0px, 0px) rotate(0deg);
    }
    25% {
        transform: translate(10px, -10px) rotate(90deg);
    }
    50% {
        transform: translate(20px, -5px) rotate(180deg);
    }
    75% {
        transform: translate(10px, 5px) rotate(270deg);
    }
}

@keyframes floatCircular {
    0% {
        transform: rotate(0deg) translateX(20px) rotate(0deg);
    }
    100% {
        transform: rotate(360deg) translateX(20px) rotate(-360deg);
    }
}

@keyframes rotate360 {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes rotateReverse {
    from {
        transform: rotate(45deg);
    }
    to {
        transform: rotate(-315deg);
    }
}

@keyframes pulse {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.6;
    }
    50% {
        transform: scale(1.2);
        opacity: 1;
    }
}

@keyframes scaleUpDown {
    0%,
    100% {
        transform: scale(1) rotate(45deg);
    }
    50% {
        transform: scale(1.3) rotate(45deg);
    }
}

@keyframes colorShift {
    0%,
    100% {
        background: rgba(255, 255, 255, 0.1);
    }
    50% {
        background: rgba(255, 255, 255, 0.3);
    }
}

@keyframes stretch {
    0%,
    100% {
        transform: scaleX(1);
    }
    50% {
        transform: scaleX(1.5);
    }
}

@keyframes lineSlideRight {
    0% {
        transform: translateX(-100%);
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        transform: translateX(100%);
        opacity: 0;
    }
}

@keyframes lineSlideLeft {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        transform: translateX(-100%);
        opacity: 0;
    }
}

@keyframes particleFloat {
    0%,
    100% {
        transform: translateY(0px);
        opacity: 0.8;
    }
    25% {
        transform: translateY(-10px);
        opacity: 1;
    }
    50% {
        transform: translateY(-5px);
        opacity: 0.9;
    }
    75% {
        transform: translateY(-15px);
        opacity: 1;
    }
}

@keyframes logoFloat {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    25% {
        transform: translateY(-3px) rotate(1deg);
    }
    50% {
        transform: translateY(-6px) rotate(0deg);
    }
    75% {
        transform: translateY(-3px) rotate(-1deg);
    }
}

@keyframes glowPulse {
    0%,
    100% {
        opacity: 0.3;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.6;
        transform: translate(-50%, -50%) scale(1.1);
    }
}

@keyframes textShimmer {
    0%,
    100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

@keyframes underlineGrow {
    0%,
    20% {
        width: 0;
    }
    40%,
    60% {
        width: 100%;
    }
    80%,
    100% {
        width: 0;
    }
}

@keyframes cornerPulse {
    0%,
    100% {
        opacity: 0.4;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .banner-title {
        font-size: 2.2rem;
        flex-direction: column;
        gap: 4px;
    }

    .banner-content {
        gap: 15px;
    }

    .banner-logo {
        height: 60px;
    }

    .floating-icon {
        font-size: 20px;
    }

    .geometric-shape {
        transform: scale(0.8);
    }
}

@media (max-width: 480px) {
    .banner-title {
        font-size: 1.8rem;
    }

    .banner-logo {
        height: 50px;
    }

    .floating-icon {
        font-size: 16px;
    }

    .corner-decoration {
        width: 20px;
        height: 20px;
    }

    .baliho-banner {
        padding: 16px;
    }
}

/* Logo tanpa background */
.banner-logo {
    height: 80px;
    width: auto;
    object-fit: contain;
    animation: floatLogo 3s ease-in-out infinite;
}

.banner-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.8rem;
    font-weight: 900;
    background: linear-gradient(to right, #ffffff, #fff4cc);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
}

/* Animasi Glow */
@keyframes glowTitle {
    0% {
        text-shadow:
            0 0 10px rgba(255, 255, 255, 0.4),
            0 0 20px rgba(255, 204, 0, 0.3);
    }
    100% {
        text-shadow:
            0 0 20px rgba(255, 255, 255, 0.8),
            0 0 40px rgba(255, 204, 0, 0.6);
    }
}

#map {
    height: 70vh;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 8px 32px rgba(255, 204, 0, 0.15),
        0 4px 15px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(255, 204, 0, 0.2);
}

.filter-container {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 248, 220, 0.95) 100%);
    padding: 20px;
    margin-bottom: 12px;
    border-radius: 12px;
    box-shadow:
        0 4px 20px rgba(255, 204, 0, 0.1),
        0 2px 10px rgba(0, 0, 0, 0.05);
    border: 2px solid rgba(255, 204, 0, 0.3);
    backdrop-filter: blur(10px);
}

.filter-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: end;
    max-width: 1200px;
    margin: 0 auto;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 220px;
    flex: 1;
}

.filter-label {
    font-size: 14px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.filter-select {
    padding: 12px 16px;
    border: 2px solid rgba(255, 204, 0, 0.3);
    border-radius: 8px;
    font-size: 14px;
    background: linear-gradient(135deg, #ffffff 0%, #fffef7 100%);
    color: #2c3e50;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(255, 204, 0, 0.1);
    font-weight: 500;
}

.filter-select:focus {
    outline: none;
    border-color: #ffcc00;
    box-shadow:
        0 0 0 4px rgba(255, 204, 0, 0.15),
        0 4px 12px rgba(255, 204, 0, 0.2);
    transform: translateY(-1px);
}

.filter-select:hover {
    border-color: rgba(255, 204, 0, 0.5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 204, 0, 0.15);
}

.filter-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    min-width: 220px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    width: 100%;
}

.apply-button {
    padding: 12px 18px;
    background: linear-gradient(135deg, #ffcc00 0%, #ff9900 100%);
    color: #2c3e50;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 204, 0, 0.3);
    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
    flex: 1;
}

.apply-button:hover {
    background: linear-gradient(135deg, #ff9900 0%, #ffcc00 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 204, 0, 0.4);
}

.clear-button {
    padding: 12px 18px;
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    flex: 1;
}

.clear-button:hover {
    background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-wrapper {
        flex-direction: column;
        gap: 15px;
    }

    .filter-item {
        min-width: auto;
        width: 100%;
    }

    .filter-actions {
        width: 100%;
    }

    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }

    .apply-button,
    .clear-button {
        width: 100%;
        flex: none;
    }
}

@media (max-width: 480px) {
    .filter-container {
        padding: 16px;
        margin-bottom: 8px;
    }

    .filter-label {
        font-size: 13px;
    }

    .filter-select {
        padding: 10px 12px;
        font-size: 13px;
    }

    .apply-button,
    .clear-button {
        padding: 10px 14px;
        font-size: 13px;
    }
}

/* Tabel Styles - Tema yang selaras dengan peta */
.table-container {
    margin-top: 24px;
    background: linear-gradient(135deg, #ffffff 0%, #fffef7 100%);
    border-radius: 16px;
    box-shadow:
        0 8px 32px rgba(255, 204, 0, 0.15),
        0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    animation: slideIn 0.4s ease-out;
    border: 2px solid rgba(255, 204, 0, 0.2);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-header {
    background: linear-gradient(135deg, #ffcc00 0%, #ff9900 100%);
    color: #2c3e50;
    padding: 24px 28px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
}

.table-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 6px 0;
    text-shadow: 0 2px 4px rgba(255, 255, 255, 0.3);
}

.table-subtitle {
    font-size: 15px;
    margin: 0;
    opacity: 0.85;
    font-weight: 600;
}

.table-wrapper {
    overflow-x: auto;
    max-height: 600px;
    overflow-y: auto;
    background: linear-gradient(135deg, #ffffff 0%, #fffef7 100%);
}

.table-header-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff8e1 100%);
    border-bottom: 2px solid rgba(255, 204, 0, 0.2);
}

.table-head {
    font-weight: 700;
    color: #2c3e50;
    padding: 16px 12px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-right: 1px solid rgba(255, 204, 0, 0.1);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 248, 220, 0.8) 100%);
}

.table-body-row {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(255, 204, 0, 0.1);
}

.table-body-row:hover {
    background: linear-gradient(135deg, rgba(255, 204, 0, 0.05) 0%, rgba(255, 248, 220, 0.1) 100%);
    transform: scale(1.002);
    box-shadow: 0 2px 8px rgba(255, 204, 0, 0.1);
}

.table-cell {
    padding: 14px 12px;
    font-size: 14px;
    color: #2c3e50;
    border-right: 1px solid rgba(255, 204, 0, 0.05);
    vertical-align: middle;
}

.table-empty-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff8e1 100%);
}

.leaflet-popup-content-wrapper {
    padding: 0 !important;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
    background: #fff;
    border: 2px solid rgba(255, 204, 0, 0.3);
}

.leaflet-popup-content {
    margin: 0 !important;
    padding: 0 !important;
}

.leaflet-popup-tip {
    border-top-color: rgba(255, 204, 0, 0.3) !important;
}

/* Scrollbar Styling untuk Table */
.table-wrapper::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: rgba(255, 204, 0, 0.1);
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ffcc00 0%, #ff9900 100%);
    border-radius: 4px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #ff9900 0%, #ffcc00 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-wrapper {
        flex-direction: column;
        gap: 15px;
    }

    .filter-item {
        min-width: auto;
        width: 100%;
    }

    .filter-actions {
        width: 100%;
    }

    .apply-button,
    .clear-button {
        width: 100%;
    }

    #map {
        height: 50vh;
        border-radius: 8px;
    }

    .table-wrapper {
        max-height: 400px;
    }

    .table-container {
        margin-top: 16px;
        border-radius: 12px;
    }

    .table-header {
        padding: 16px 20px;
    }

    .table-title {
        font-size: 18px;
    }

    .table-subtitle {
        font-size: 13px;
    }

    .table-cell {
        padding: 10px 8px;
        font-size: 12px;
    }

    .table-head {
        padding: 12px 8px;
        font-size: 11px;
    }

    .filter-container {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .filter-container {
        padding: 12px;
        margin-bottom: 8px;
    }

    .filter-label {
        font-size: 13px;
    }

    .filter-select {
        padding: 10px 12px;
        font-size: 13px;
    }

    .apply-button,
    .clear-button {
        padding: 10px 14px;
        font-size: 13px;
    }

    #map {
        height: 45vh;
    }

    .table-cell img {
        width: 48px;
        height: 48px;
    }
}
</style>
