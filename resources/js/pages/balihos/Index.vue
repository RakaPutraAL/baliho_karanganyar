<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Kecamatan {
    kode: string;
    nama_kecamatan: string;
}

interface Opd {
    id: number;
    nama_opd: string;
}

interface Baliho {
    id: number;
    foto?: string | null;
    opd_id?: number | null;
    view: string;
    dimensi: string;
    jenis_kontruksi: string;
    alamat: string;
    latitude: number | null;
    longitude: number | null;
    kode: string;         // 🔹 foreign key
    kecamatan?: Kecamatan | null;
    opd?: Opd | null;  // 🔹 relasi (optional, kalau di-load dari backend)
}

interface Props {
    balihos: Baliho[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Baliho',
        href: '/balihos',
    },
];

const page = usePage();

const handleDelete = (id: number) => {
    if (confirm('Do you want to delete this data?')) {
        router.delete(route('balihos.destroy', { baliho: id }));
    }
};
</script>

<template>
    <Head title="Baliho" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div v-if="page.props.flash?.message" class="mb-4">
                <Alert class="bg-blue-200">
                    <Rocket class="h-4 w-4" />
                    <AlertTitle>Notification</AlertTitle>
                    <AlertDescription>{{ page.props.flash.message }}</AlertDescription>
                </Alert>
            </div>
            
            <div class="mb-4 flex space-x-2">
                <Link :href="route('balihos.create')">
                    <Button>Create a Baliho Entry</Button>
                </Link>

                <a :href="route('balihos.export')" class="inline-block">
                    <Button class="bg-green-600">Download Excel</Button>
                </a>
            </div>       
            <div>
                <Table>
                    <TableCaption>Daftar Baliho</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Foto</TableHead>
                            <TableHead>Nama Opd</TableHead>
                            <TableHead>View</TableHead>
                            <TableHead>Dimensi</TableHead>
                            <TableHead>Jenis Kontruksi</TableHead>
                            <TableHead>Alamat</TableHead>
                            <TableHead>Kecamatan</TableHead> <!-- 🔹 Tambah Kecamatan -->
                            <TableHead>Latitude</TableHead>
                            <TableHead>Longitude</TableHead>
                            <TableHead class="text-center">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="baliho in props.balihos" :key="baliho.id">
                            <TableCell>
                                <img
                                    v-if="baliho.foto"
                                    :src="`/uploads/${baliho.foto}`"
                                    alt="Foto Baliho"
                                    class="w-30 h-30 object-cover rounded"
                                />
                                <span v-else>-</span>
                            </TableCell>
                            <TableCell>{{ baliho.opd?.nama_opd ?? '-' }}</TableCell>
                            <TableCell>{{ baliho.view }}</TableCell>
                            <TableCell>{{ baliho.dimensi }}</TableCell>
                            <TableCell>{{ baliho.jenis_kontruksi }}</TableCell>
                            <TableCell>{{ baliho.alamat }}</TableCell>
                            <TableCell>
                                <!-- 🔹 Tampilkan nama_kecamatan kalau relasi diload -->
                                {{ baliho.kecamatan?.nama_kecamatan ?? '-' }}
                            </TableCell>
                            <TableCell>{{ baliho.latitude }}</TableCell>
                            <TableCell>{{ baliho.longitude }}</TableCell>
                            <TableCell class="text-center space-x-2">
                                <Link :href="route('balihos.edit', { baliho: baliho.id })">
                                    <Button class="bg-slate-600">Edit</Button>
                                </Link>
                                <Button class="bg-red-600" @click="handleDelete(baliho.id)">Delete</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
