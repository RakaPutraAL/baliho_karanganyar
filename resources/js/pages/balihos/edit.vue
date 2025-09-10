<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface Baliho {
    id: number;
    opd_id?: number | null;
    jenis_baliho: string;
    pemasangan: string;
    view: string;
    dimensi: string;
    jenis_kontruksi: string;
    alamat: string;
    kode: string;
    latitude: number | null;
    longitude: number | null;
    foto?: string | null;
}

interface Kecamatan {
    kode: string;
    nama_kecamatan: string;
}

interface Opd {
    id: number;
    nama_opd: string;
}

const props = defineProps<{
    baliho: Baliho;
    baseUrl: string;
    kecamatans: Kecamatan[];
    opds: Opd[];
}>();

const form = useForm({
    jenis_baliho: props.baliho.jenis_baliho ?? '',
    pemasangan: props.baliho.pemasangan ?? '',
    view: props.baliho.view ?? '',
    dimensi: props.baliho.dimensi ?? '',
    jenis_kontruksi: props.baliho.jenis_kontruksi ?? '',
    alamat: props.baliho.alamat ?? '',
    kode: props.baliho.kode ?? '',
    opd_id: props.baliho.opd_id ?? '',
    latitude: props.baliho.latitude !== null ? String(props.baliho.latitude) : '',
    longitude: props.baliho.longitude !== null ? String(props.baliho.longitude) : '',
    foto: undefined as File | undefined,
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.foto = target.files[0];
    }
};

const handleSubmit = () => {
    const submitData = {
        ...form.data(),
        _method: 'PUT',
    };

    form.transform(() => submitData).post(route('balihos.update', { baliho: props.baliho.id }), {
        forceFormData: true,
        preserveScroll: true,
    });
};

// ✅ Map & Marker untuk edit
onMounted(() => {
    const defaultLat = props.baliho.latitude ?? -7.5;
    const defaultLng = props.baliho.longitude ?? 110.5;

    const map = L.map('map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker: L.Marker | null = null;
    let geoLayer: L.GeoJSON;

    // kalau sudah ada koordinat di database → tampilkan marker awal
    if (props.baliho.latitude && props.baliho.longitude) {
        marker = L.marker([props.baliho.latitude, props.baliho.longitude]).addTo(map);
    }

    // klik untuk update koordinat
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

    // ✅ watch input manual → update marker di map
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
        .then(r => r.json())
        .then(data => {
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
                        mouseover: e => e.target.setStyle({ weight: 3, color: '#ff9900', fillOpacity: 0.25 }),
                        mouseout: e => geoLayer.resetStyle(e.target),
                    });
                },
            }).addTo(map);

            map.fitBounds(geoLayer.getBounds());
        })
        .catch(err => console.error('Gagal load GeoJSON:', err));
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Baliho', href: `/balihos/${props.baliho.id}/edit` }];
</script>

<template>
    <Head title="Edit Baliho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="handleSubmit" class="w-8/12 space-y-4">
                <!-- OPD -->
                <div class="space-y-2">
                    <Label class="font-semibold text-gray-200">OPD</Label>
                    <select v-model="form.opd_id"
                        class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-gray-100 shadow-sm">
                        <option value="">-- Pilih OPD --</option>
                        <option v-for="opd in props.opds" :key="opd.id" :value="opd.id">
                            {{ opd.nama_opd }}
                        </option>
                    </select>
                    <div class="text-sm text-red-400" v-if="form.errors.opd_id">{{ form.errors.opd_id }}</div>
                </div>

                <!-- Jenis Baliho -->
                <div class="space-y-2">
                    <Label>Jenis Baliho</Label>
                    <Input v-model="form.jenis_baliho" type="text" placeholder="Jenis Baliho" />
                    <div v-if="form.errors.jenis_baliho" class="text-sm text-red-600">{{ form.errors.jenis_baliho }}</div>
                </div>

                <!-- Pemasangan -->
                <div class="space-y-2">
                    <Label>Pemasangan</Label>
                    <Input v-model="form.pemasangan" type="text" placeholder="Pemasangan" />
                    <div v-if="form.errors.pemasangan" class="text-sm text-red-600">{{ form.errors.pemasangan }}</div>
                </div>

                <!-- View -->
                <div class="space-y-2">
                    <Label>View</Label>
                    <Input v-model="form.view" type="text" placeholder="View" />
                    <div v-if="form.errors.view" class="text-sm text-red-600">{{ form.errors.view }}</div>
                </div>

                <!-- Dimensi -->
                <div class="space-y-2">
                    <Label>Dimensi</Label>
                    <Input v-model="form.dimensi" type="text" placeholder="Dimensi" />
                    <div v-if="form.errors.dimensi" class="text-sm text-red-600">{{ form.errors.dimensi }}</div>
                </div>

                <!-- Jenis Kontruksi -->
                <div class="space-y-2">
                    <Label>Jenis Kontruksi</Label>
                    <Input v-model="form.jenis_kontruksi" type="text" placeholder="Jenis Kontruksi" />
                    <div v-if="form.errors.jenis_kontruksi" class="text-sm text-red-600">{{ form.errors.jenis_kontruksi }}</div>
                </div>

                <!-- Alamat -->
                <div class="space-y-2">
                    <Label>Alamat</Label>
                    <Input v-model="form.alamat" type="text" placeholder="Alamat" />
                    <div v-if="form.errors.alamat" class="text-sm text-red-600">{{ form.errors.alamat }}</div>
                </div>

                <!-- Kecamatan -->
                <div class="space-y-2">
                    <Label class="font-semibold text-gray-200">Kecamatan</Label>
                    <select v-model="form.kode"
                        class="w-full rounded-xl border border-gray-600 bg-gray-900 p-3 text-gray-100 shadow-sm">
                        <option value="">-- Pilih Kecamatan --</option>
                        <option v-for="kecamatan in props.kecamatans" :key="kecamatan.kode" :value="kecamatan.kode">
                            {{ kecamatan.nama_kecamatan }}
                        </option>
                    </select>
                    <div class="text-sm text-red-400" v-if="form.errors.kode">{{ form.errors.kode }}</div>
                </div>

                <!-- Latitude & Longitude -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label>Latitude</Label>
                        <Input v-model="form.latitude" type="text" placeholder="Latitude" />
                        <div v-if="form.errors.latitude" class="text-sm text-red-600">{{ form.errors.latitude }}</div>
                    </div>
                    <div>
                        <Label>Longitude</Label>
                        <Input v-model="form.longitude" type="text" placeholder="Longitude" />
                        <div v-if="form.errors.longitude" class="text-sm text-red-600">{{ form.errors.longitude }}</div>
                    </div>
                </div>

                <!-- Foto -->
                <div class="space-y-2">
                    <Label>Foto (Opsional)</Label>
                    <input type="file" accept="image/*" @change="handleFileChange" class="block w-full text-sm" />
                    <div v-if="form.errors.foto" class="text-sm text-red-600">{{ form.errors.foto }}</div>
                    <img v-if="props.baliho.foto" :src="`${props.baseUrl}/uploads/${props.baliho.foto}`"
                        alt="Gambar Baliho" class="mt-2 h-auto w-40 rounded shadow" />
                </div>

                <!-- Map -->
                <div id="map" class="h-96 w-full mb-4"></div>

                <Button type="submit" :disabled="form.processing">Update Baliho</Button>
            </form>
        </div>
    </AppLayout>
</template>
