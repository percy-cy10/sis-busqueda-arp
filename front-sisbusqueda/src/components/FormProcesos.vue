<template>
  <!-- ======================================================= -->
  <!-- SECCIÓN: CAMPOS PARA PROCESOS NO CONTENCIOSOS -->
  <!-- ======================================================= -->
  <template v-if="solicitudForm.tipo_tramite === 'procesos'">

    <!-- ============================================ -->
    <!-- ENCABEZADO -->
    <!-- ============================================ -->
    <div class="col-12">
      <div class="text-h6 text-purple q-mb-md">
        <q-icon name="fact_check" class="q-mr-sm" />
        Datos de Proceso No Contencioso
      </div>
    </div>

    <!-- ============================================ -->
    <!-- CAMPOS PRINCIPALES -->
    <!-- ============================================ -->

    <!-- Proceso de -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Proceso de"
      dense
      outlined
      clearable
      v-model="solicitudForm.proceso_de"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese el proceso'
      ]"
      placeholder="Descripción del proceso"
    />

    <!-- En contra de -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="En contra de"
      dense
      outlined
      clearable
      v-model="solicitudForm.en_contra_de"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese contra quién es el proceso'
      ]"
    />

    <!-- Causante -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Causante"
      dense
      outlined
      clearable
      v-model="solicitudForm.causante_proceso"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese el nombre del causante'
      ]"
    />

    <!-- Notario -->
    <SelectInput
      class="col-12 col-md-6 q-pa-sm"
      label="Notarios"
      dense
      outlined
      clearable
      v-model="solicitudForm.notario_proceso"
      :options="notarios"
      :option-label="(notario) => notario.nombre_completo"
      option-value="id"
      :requerido="true"
      :rules="[
        (val) => (val && val !== '') || 'Seleccione o ingrese un notario'
      ]"
    />

    <!-- ============================================ -->
    <!-- UBICACIÓN Y FECHA -->
    <!-- ============================================ -->
    <div class="row q-col-gutter-sm q-mb-xs">

      <!-- Ubicación del contrato -->
      <div class="col-12 col-md-8">
        <div class="text-caption q-pb-xs">
          Municipalidad de Registro <span class="text-red-7">(*)</span>
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

      <!-- Fecha de nacimiento -->
      <div class="col-12 col-md-4">
        <div class="text-caption q-pb-xs">
          Fecha de Nacimiento <span class="text-red-7">(*)</span>
        </div>

        <!-- Campos: Año, Mes, Día -->
        <div class="row q-col-gutter-xs">
          <InputAnio
            class="col-4"
            dense
            outlined
            v-model="solicitudForm.anio"
            :RangoAnios="[1900, new Date().getFullYear()]"
            :requerido="true"
            :rules="[
              (val) => val !== null && val !== '' || 'Ingrese el año'
            ]"
          />

          <InputMes
            class="col-4"
            dense
            outlined
            clearable
            v-model="solicitudForm.mes"
            :modelAnio="solicitudForm.anio"
            :readonly="solicitudForm.anio === null"
            :rules="[
              (val) => val !== null && val !== '' || 'Ingrese el mes'
            ]"
          />

          <InputDia
            class="col-4"
            dense
            outlined
            v-model="solicitudForm.dia"
            :modelAnio="solicitudForm.anio"
            :modelMes="solicitudForm.mes"
            :readonly="solicitudForm.anio === null || solicitudForm.mes === null"
            :rules="[
              (val) => val !== null && val !== '' || 'Ingrese el día'
            ]"
          />
        </div>
      </div>
    </div>

    <!-- ============================================ -->
    <!-- OBSERVACIONES -->
    <!-- ============================================ -->
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
        (val) => (val && val.trim() !== '') || 'Ingrese las observaciones'
      ]"
      placeholder="Ingrese observaciones adicionales sobre el proceso..."
    />
  </template>
</template>

<!-- ======================================================= -->
<!-- SCRIPTS Y COMPONENTES HIJOS -->
<!-- ======================================================= -->
<script setup>
import { defineProps } from "vue";

// Componentes reutilizables
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import SelectInput from "src/components/SelectInput.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";

// Props recibidas
defineProps({
  solicitudForm: Object,
  notarios: {
    type: Array,
    default: () => [],
  },
});
</script>
