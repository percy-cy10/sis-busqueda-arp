<template>
  <!-- ========================== -->
  <!-- CAMPOS PARA MINISTERIO PÚBLICO -->
  <!-- ========================== -->
  <template v-if="solicitudForm.tipo_tramite === 'ministerio_publico'">
    <div class="col-12">
      <div class="text-h6 text-red q-mb-md">
        <q-icon name="gavel" class="q-mr-sm" />
        Datos de Ministerio Público
      </div>
    </div>

    <!-- TIPO DE EXPEDIENTE -->
    <div class="col-12 q-pa-sm">
      <div class="text-subtitle2 q-mb-sm">
        Tipo de Expediente <span class="text-red-7">(*)</span>
      </div>
      <q-select
        class="col-12"
        dense
        outlined
        clearable
        v-model="solicitudForm.tipo_expediente_mp"
        :options="tiposExpedienteMP"
        option-label="label"
        option-value="value"
        :requerido="true"
        :rules="[
          (val) => (val && val !== '') || 'Seleccione el tipo de expediente',
        ]"
        emit-value
        map-options
      />
    </div>

    <!-- CASO -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Caso"
      dense
      outlined
      clearable
      v-model="solicitudForm.caso_mp"
      :rules="[(val) => (val && val.trim() !== '') || 'Ingrese el caso']"
      placeholder="Descripción del caso"
    />

    <!-- ÁREA -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Área"
      dense
      outlined
      clearable
      v-model="solicitudForm.area_mp"
      :rules="[(val) => (val && val.trim() !== '') || 'Ingrese el área']"
    />

    <!-- MATERIA -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Materia"
      dense
      outlined
      clearable
      v-model="solicitudForm.materia_mp"
      :rules="[(val) => (val && val.trim() !== '') || 'Ingrese la materia']"
    />

    <!-- AGRAVIADO/DENUNCIANTE -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Agraviado/Denunciante"
      dense
      outlined
      clearable
      v-model="solicitudForm.agraviado_mp"
      :rules="[
        (val) =>
          (val && val.trim() !== '') || 'Ingrese el agraviado o denunciante',
      ]"
    />

    <!-- IMPUTADO/DENUNCIADO -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Imputado/Denunciado"
      dense
      outlined
      clearable
      v-model="solicitudForm.imputado_mp"
      :rules="[
        (val) =>
          (val && val.trim() !== '') || 'Ingrese el imputado o denunciado',
      ]"
    />

    <!-- FISCALÍA -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Fiscalía"
      dense
      outlined
      clearable
      v-model="solicitudForm.fiscalia_mp"
      :rules="[(val) => (val && val.trim() !== '') || 'Ingrese la fiscalía']"
    />

    <!-- NÚMERO DE CASO -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Número de Caso"
      dense
      outlined
      clearable
      v-model="solicitudForm.numero_caso_mp"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese el número de caso',
      ]"
      mask="#########"
    />

    <!-- NÚMERO DE PAQUETE -->
    <q-input
      class="col-12 col-md-6 q-pa-sm"
      label="Número de Paquete"
      dense
      outlined
      clearable
      v-model="solicitudForm.numero_paquete_mp"
      :rules="[
        (val) => (val && val.trim() !== '') || 'Ingrese el número de paquete',
      ]"
      mask="#########"
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

// Tipos de expediente para Ministerio Público
const tiposExpedienteMP = [
  { label: "Penal", value: "penal" },
  { label: "Civil", value: "civil" },
  { label: "Constitucional", value: "constitucional" },
  { label: "Administrativo", value: "administrativo" },
  { label: "Laboral", value: "laboral" },
];

defineProps({
  solicitudForm: Object,
});
</script>
