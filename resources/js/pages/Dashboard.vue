<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { Chart, ArcElement, Tooltip, Legend, DoughnutController, BarElement, CategoryScale, LinearScale, BarController } from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend, DoughnutController, BarElement, CategoryScale, LinearScale, BarController);

interface JenisItem {
    jenis_kontruksi: string;
    total: number;
}

interface KecamatanItem {
    nama: string;
    total: number;
}

interface Props {
    totalBaliho: number;
    totalBerFoto: number;
    perJenis: JenisItem[];
    perKecamatan: KecamatanItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

const persentaseFoto = props.totalBaliho > 0
    ? Math.round((props.totalBerFoto / props.totalBaliho) * 100)
    : 0;

const maxKecamatan = Math.max(...props.perKecamatan.map(k => k.total), 1);

const donutCanvas = ref<HTMLCanvasElement | null>(null);
const barCanvas = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    // Donut Chart - Jenis Konstruksi
    if (donutCanvas.value) {
        new Chart(donutCanvas.value, {
            type: 'doughnut',
            data: {
                labels: props.perJenis.map(j => j.jenis_kontruksi),
                datasets: [{
                    data: props.perJenis.map(j => j.total),
                    backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                }
            }
        });
    }

    // Bar Chart - Per Kecamatan
    if (barCanvas.value) {
        new Chart(barCanvas.value, {
            type: 'bar',
            data: {
                labels: props.perKecamatan.map(k => k.nama),
                datasets: [{
                    label: 'Jumlah Baliho',
                    data: props.perKecamatan.map(k => k.total),
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    }
                }
            }
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">

            <!-- Kartu Statistik Utama -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <!-- Total Baliho -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Baliho</p>
                    <p class="mt-2 text-4xl font-bold text-blue-600">{{ props.totalBaliho }}</p>
                    <p class="mt-1 text-xs text-gray-400">Seluruh data terdaftar</p>
                </div>

                <!-- Total Berfoto -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Baliho Berfoto</p>
                    <p class="mt-2 text-4xl font-bold text-green-600">{{ props.totalBerFoto }}</p>
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-gray-400 mb-1">
                            <span>{{ persentaseFoto }}% dari total</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-2 rounded-full bg-green-500 transition-all duration-500"
                                :style="{ width: persentaseFoto + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Jenis Konstruksi -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Per Jenis Konstruksi</p>
                    <div class="space-y-3">
                        <div
                            v-for="jenis in props.perJenis"
                            :key="jenis.jenis_kontruksi"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ jenis.jenis_kontruksi }}
                            </span>
                            <span class="rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 px-3 py-0.5 text-sm font-semibold">
                                {{ jenis.total }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Diagram Row -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <!-- Donut Chart - Jenis Konstruksi -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <p class="mb-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Diagram Jenis Konstruksi</p>
                    <div class="flex justify-center">
                        <div class="w-64 h-64">
                            <canvas ref="donutCanvas" />
                        </div>
                    </div>
                </div>

                <!-- Bar Chart - Per Kecamatan -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <p class="mb-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Diagram Per Kecamatan</p>
                    <canvas ref="barCanvas" />
                </div>

            </div>

            <!-- Statistik Per Kecamatan (bar manual) -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <p class="mb-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Jumlah Baliho per Kecamatan</p>
                <div class="space-y-3">
                    <div
                        v-for="kec in props.perKecamatan"
                        :key="kec.nama"
                        class="flex items-center gap-3"
                    >
                        <span class="w-36 shrink-0 text-sm text-gray-600 dark:text-gray-400 truncate">
                            {{ kec.nama }}
                        </span>
                        <div class="flex-1 h-4 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div
                                class="h-4 rounded-full bg-blue-500 transition-all duration-500"
                                :style="{ width: (kec.total / maxKecamatan * 100) + '%' }"
                            />
                        </div>
                        <span class="w-6 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ kec.total }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>