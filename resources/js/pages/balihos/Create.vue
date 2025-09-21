<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onMounted, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create a Baliho', href: '/balihos/Create' }];

interface Kecamatan {
    kode: string;
    nama_kecamatan: string;
}

interface Opd {
    id: number;
    nama_opd: string;
}

interface Props {
    kecamatans: Kecamatan[];
    opds: Opd[];
}

const props = defineProps<Props>();

// Form data
const form = useForm({
    view: '',
    dimensi: '',
    jenis_kontruksi: '',
    alamat: '',
    kode: '',
    opd_id: '',
    latitude: '',
    longitude: '',
    foto: null as File | null,
});

// Submit form
const handleSubmit = () => {
    form.post(route('balihos.store'), { forceFormData: true });
};

// Leaflet map
onMounted(() => {
    const map = L.map('map').setView([-7.5, 110.5], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker: L.Marker | null = null;
    let geoLayer: L.GeoJSON;

    // Klik untuk menandai titik baliho
    map.on('click', (e: L.LeafletMouseEvent) => {
        const { lat, lng } = e.latlng;
        form.latitude = lat.toString();
        form.longitude = lng.toString();

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });

    // Watch input manual → update marker di map
    watch([() => form.latitude, () => form.longitude], ([lat, lng]) => {
        if (lat && lng && !isNaN(Number(lat)) && !isNaN(Number(lng))) {
            const latLng = L.latLng(Number(lat), Number(lng));
            if (marker) {
                marker.setLatLng(latLng);
            } else {
                marker = L.marker(latLng).addTo(map);
            }
            map.setView(latLng, 15);
        }
    });

    // Load GeoJSON wilayah Karanganyar
    fetch('/karanganyar.geojson')
        .then((r) => r.json())
        .then((data) => {
            geoLayer = L.geoJSON(data, {
                style: {
                    color: '#ffcc00',
                    weight: 2,
                    opacity: 0.7,
                    fillColor: '#ffcc00',
                    fillOpacity: 0.15,
                },
                onEachFeature: (_, layer) => {
                    layer.on({
                        mouseover: (e) => e.target.setStyle({ weight: 3, color: '#ff9900', fillOpacity: 0.25 }),
                        mouseout: (e) => geoLayer.resetStyle(e.target),
                    });
                },
            }).addTo(map);

            map.fitBounds(geoLayer.getBounds());
        })
        .catch((err) => console.error('Gagal load GeoJSON:', err));
});
</script>

<template>
    <Head title="Create Baliho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="handleSubmit" class="w-full space-y-4 md:w-8/12" enctype="multipart/form-data">
                <!-- Dropdown OPD -->
                <div class="space-y-2">
                    <Label class="font-semibold text-black-200">OPD</Label>
                    <select
                        v-model="form.opd_id"
                        class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-gray-100 shadow-sm focus:border-gray-400 focus:ring focus:ring-gray-500"
                    >
                        <option value="" class="bg-gray-900 text-gray-400">-- Pilih OPD --</option>
                        <option v-for="opd in props.opds" :key="opd.id" :value="opd.id" class="bg-gray-900 text-gray-100 hover:bg-gray-700">
                            {{ opd.nama_opd }}
                        </option>
                    </select>
                    <div class="text-sm text-red-400" v-if="form.errors.opd_id">{{ form.errors.opd_id }}</div>
                </div>


                <!-- View -->
                <div class="space-y-2">
                    <Label>View</Label>
                    <Input v-model="form.view" type="text" placeholder="View" />
                    <div class="text-sm text-red-600" v-if="form.errors.view">{{ form.errors.view }}</div>
                </div>

                <!-- Dimensi -->
                <div class="space-y-2">
                    <Label>Dimensi</Label>
                    <Input v-model="form.dimensi" type="text" placeholder="Dimensi (Contoh=2x3M)" />
                    <div class="text-sm text-red-600" v-if="form.errors.dimensi">{{ form.errors.dimensi }}</div>
                </div>

                <!-- Jenis Kontruksi -->
                <div class="space-y-2">
                    <Label class="font-semibold text-black-200">Jenis Kontruksi</Label>
                    <select
                        v-model="form.jenis_kontruksi"
                        class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-gray-100 shadow-sm focus:border-gray-400 focus:ring focus:ring-gray-500"
                    >
                        <option value="" class="bg-gray-900 text-gray-400">-- Pilih Jenis Kontruksi --</option>
                        <option value="Baliho" class="bg-gray-900 text-gray-100 hover:bg-gray-700">Baliho</option>
                        <option value="Reklame" class="bg-gray-900 text-gray-100 hover:bg-gray-700">Reklame</option>
                    </select>
                    <div class="text-sm text-red-600" v-if="form.errors.jenis_kontruksi">{{ form.errors.jenis_kontruksi }}</div>
                </div>

                <!-- Alamat -->
                <div class="space-y-2">
                    <Label>Alamat</Label>
                    <Input v-model="form.alamat" type="text" placeholder="Alamat" />
                    <div class="text-sm text-red-600" v-if="form.errors.alamat">{{ form.errors.alamat }}</div>
                </div>

                <!-- Dropdown Kecamatan -->
                <div class="space-y-2">
                    <Label class="font-semibold text-black-200">Kecamatan</Label>
                    <select
                        v-model="form.kode"
                        class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-gray-100 shadow-sm focus:border-gray-400 focus:ring focus:ring-gray-500"
                    >
                        <option value="" class="bg-gray-900 text-gray-400">-- Pilih Kecamatan --</option>
                        <option
                            v-for="kecamatan in props.kecamatans"
                            :key="kecamatan.kode"
                            :value="kecamatan.kode"
                            class="bg-gray-900 text-gray-100 hover:bg-gray-700"
                        >
                            {{ kecamatan.nama_kecamatan }}
                        </option>
                    </select>
                    <div class="text-sm text-red-400" v-if="form.errors.kode">{{ form.errors.kode }}</div>
                </div>

                <!-- Latitude & Longitude -->
                <div class="grid grid-cols-1 gap-4 space-y-2 md:grid-cols-2">
                    <div>
                        <Label>Latitude</Label>
                        <Input v-model="form.latitude" type="text" placeholder="Latitude" />
                        <div class="text-sm text-red-600" v-if="form.errors.latitude">{{ form.errors.latitude }}</div>
                    </div>
                    <div>
                        <Label>Longitude</Label>
                        <Input v-model="form.longitude" type="text" placeholder="Longitude" />
                        <div class="text-sm text-red-600" v-if="form.errors.longitude">{{ form.errors.longitude }}</div>
                    </div>
                </div>

                <!-- Foto -->
                <div class="space-y-2">
                    <Label>Foto</Label>
                    <input
                        type="file"
                        @change="(e) => (form.foto = (e.target as HTMLInputElement)?.files?.[0] ?? null)"
                        class="w-full rounded border p-2"
                    />
                    <div class="text-sm text-red-600" v-if="form.errors.foto">{{ form.errors.foto }}</div>
                </div>

                <!-- Leaflet Map -->
                <div id="map" class="mb-4 h-96 w-full"></div>

                <Button type="submit" :disabled="form.processing">Tambah Baliho</Button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
#map {
    border: 1px solid #ccc;
    border-radius: 8px;
}
</style>
