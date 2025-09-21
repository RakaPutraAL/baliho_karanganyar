<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
   <img
    src="/logo-karanganyar.png"
    alt="Logo Karanganyar"
    class="mx-auto w-72 md:w-80 lg:w-96"
  />
</template>
