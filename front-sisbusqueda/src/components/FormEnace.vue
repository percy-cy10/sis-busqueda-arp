<template>
  <!-- ===================================== -->
  <!-- FORMULARIO ESPECÍFICO PARA TRÁMITE ENACE -->
  <!-- ===================================== -->
  <template v-if="solicitudForm.tipo_tramite === 'enace'">

    <!-- ENCABEZADO DE LA SECCIÓN -->
    <div class="col-12 q-mb-sm">
      <div class="text-h6 text-teal">
        <q-icon name="school" class="q-mr-sm" />
        Datos de Contrato Privado - ENACE
      </div>
    </div>

    <!-- ===================== -->
    <!-- DATOS BÁSICOS DEL CONTRATO -->
    <!-- ===================== -->

    <!-- Número/Descripción del Contrato -->
    <div class="input-group col-12 col-md-6  q-mb-xs">
      <div class="text-caption q-pb-xs">
        Ingrese el contrato privado <span class="text-red-7">(*)</span>
      </div>
      <q-input
        label="Contrato Privado"
        dense outlined clearable
        v-model="solicitudForm.contrato_privado"
        :rules="[(val) => val && val.trim() !== '' || 'Ingrese el contrato privado']"
        placeholder="Ingrese el número o descripción del contrato privado"
      />
    </div>

    <!-- Otorgante -->
    <div class="input-group col-12 col-md-6  q-mb-xs">
      <div class="text-caption q-pb-xs">
        Ingrese el otorgante <span class="text-red-7">(*)</span>
      </div>
      <q-input
        label="Otorgante"
        dense outlined clearable
        v-model="solicitudForm.otorgante_enace"
        :rules="[(val) => val && val.trim() !== '' || 'Ingrese el nombre del otorgante']"
      />
    </div>

    <!-- Favorecido -->
    <div class="input-group col-12 col-md-6  q-mb-xs">
      <div class="text-caption q-pb-xs">
        Ingrese el favorecido <span class="text-red-7">(*)</span>
      </div>
      <q-input
        label="Favorecido"
        dense outlined clearable
        v-model="solicitudForm.favorecido_enace"
        :rules="[(val) => val && val.trim() !== '' || 'Ingrese el nombre del favorecido']"
      />
    </div>

    <!-- Institución Educativa -->
    <div class="input-group col-12 col-md-6  q-mb-xs">
      <div class="text-caption q-pb-xs">
        Ingrese la institución <span class="text-red-7">(*)</span>
      </div>
      <q-input
        label="Nombre de Institución"
        dense outlined clearable
        v-model="solicitudForm.institucion_enace"
        :rules="[(val) => val && val.trim() !== '' || 'Ingrese el nombre de la institución']"
      />
    </div>

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
            :rules="[(val) => val !== null && val !== '' || 'Ingrese el año']"
          />
          <InputMes
            class="col-4"
            dense
            outlined
            clearable
            v-model="solicitudForm.mes"
            :modelAnio="solicitudForm.anio"
            :readonly="solicitudForm.anio === null"
            :rules="[(val) => val !== null && val !== '' || 'Ingrese el mes']"
          />
          <InputDia
            class="col-4"
            dense
            outlined
            v-model="solicitudForm.dia"
            :modelAnio="solicitudForm.anio"
            :modelMes="solicitudForm.mes"
            :readonly="solicitudForm.anio === null || solicitudForm.mes === null"
            :rules="[(val) => val !== null && val !== '' || 'Ingrese el día']"
          />
        </div>
      </div>
    </div>

    <!-- ===================== -->
    <!-- OBSERVACIONES ADICIONALES -->
    <!-- ===================== -->
    <div class="input-group col-12 q-mb-xs">
      <div class="text-caption q-pb-xs">
        Observaciones
      </div>
      <q-input
        label="Observaciones"
        dense outlined clearable
        type="textarea"
        rows="3"
        v-model="solicitudForm.mas_datos"
        :rules="[(val) => val && val.trim() !== '' || 'Ingrese las observaciones']"
        placeholder="Ingrese observaciones adicionales sobre el contrato..."
      />
    </div>

  </template>
</template>

<script setup>
import { defineProps } from 'vue';
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";

// Props del componente
defineProps({
  solicitudForm: Object
});
</script>
