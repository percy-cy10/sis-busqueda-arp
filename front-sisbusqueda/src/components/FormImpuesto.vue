<template>
  <!-- ========================== -->
  <!-- CAMPOS PARA IMPUESTO SUCESORIOS -->
  <!-- ========================== -->
  <template v-if="solicitudForm.tipo_tramite === 'impuesto'">
    <div class="col-12">
      <div class="text-h6 text-brown q-mb-md">
        <q-icon name="account_balance" class="q-mr-sm" />
        Datos de Impuesto Sucesorios
      </div>
    </div>

    <!-- CAUSANTE -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Causante"
      dense
      outlined
      clearable
      v-model="solicitudForm.causante_impuesto"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese el nombre del causante',
      ]"
      placeholder="Ingrese el nombre completo del causante"
    />

    <!-- DIRECCIÓN -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Dirección"
      dense
      outlined
      clearable
      v-model="solicitudForm.direccion_impuesto"
      :rules="[(val) => (val && val.trim() !== '') || 'Ingrese la dirección']"
      placeholder="Ingrese la dirección completa"
    />

    <!-- ===================== -->
    <!-- UBICACIÓN Y FECHA -->
    <!-- ===================== -->
    <div class="row q-col-gutter-sm q-mb-xs">
      <!-- Ubicación del Contrato -->
      <div class="col-12 col-md-8">
        <div class="text-caption q-pb-xs">
          Lugar del Contrato <span class="text-red-7">(*)</span>
        </div>
        <div class="row q-col-gutter-xs">
          <SelectUbigeoPlus
            class="col-3"
            dense
            outlined
            clearable
            v-model="solicitudForm.ubigeo_cod_soli"
            :loading="loading"
            :requerido="true"
          />
        </div>
      </div>

      <!-- Fecha del Contrato -->
      <div class="col-12 col-md-4">
        <div class="text-caption q-pb-xs">
          Fecha del Contrato <span class="text-red-7">(*)</span>
        </div>
        <div class="row q-col-gutter-xs">
          <InputAnio
            class="col-4"
            dense
            outlined
            v-model="solicitudForm.anio"
            :RangoAnios="[1900, new Date().getFullYear()]"
            :requerido="true"
            :rules="[(val) => (val !== null && val !== '') || 'Ingrese el año']"
          />
          <InputMes
            class="col-4"
            dense
            outlined
            clearable
            v-model="solicitudForm.mes"
            :modelAnio="solicitudForm.anio"
            :readonly="solicitudForm.anio === null"
            :rules="[(val) => (val !== null && val !== '') || 'Ingrese el mes']"
          />
          <InputDia
            class="col-4"
            dense
            outlined
            v-model="solicitudForm.dia"
            :modelAnio="solicitudForm.anio"
            :modelMes="solicitudForm.mes"
            :readonly="
              solicitudForm.anio === null || solicitudForm.mes === null
            "
            :rules="[(val) => (val !== null && val !== '') || 'Ingrese el día']"
          />
        </div>
      </div>
    </div>
    <!-- OBSERVACIONES -->
    <q-input
      class="col-12 q-pa-sm"
      label="Observaciones"
      dense
      outlined
      clearable
      type="textarea"
      rows="3"
      v-model="solicitudForm.mas_datos"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese las observaciones',
      ]"
      placeholder="Ingrese observaciones adicionales sobre el impuesto sucesorio..."
    />
  </template>
</template>

<script setup>
import { defineProps } from "vue";
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";

defineProps({
  solicitudForm: Object,
});
</script>
