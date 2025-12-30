<template>
  <!-- ========================== -->
  <!-- CAMPOS PARA ESCRITURAS -->
  <!-- ========================== -->
  <template v-if="solicitudForm.tipo_tramite === 'escrituras'">
    <!-- Selección Notario CORREGIDO -->
    <SelectInput
      class="col-12 col-md-6 q-pa-sm"
      label="Notarios"
      dense
      outlined
      clearable
      lazy-rules
      v-model="solicitudForm.notario_id"
      :rules="[
        (val) => (val && val !== '') || 'Por favor seleccione un notario',
      ]"
      :options="notarios"
      :option-label="
        (notario) => {
          const nombreUbigeo = notario.ubigeo?.nombre;
          return nombreUbigeo
            ? `${notario.nombre_completo} - ${nombreUbigeo}`.trim()
            : notario.nombre_completo;
        }
      "
      option-value="id"
      :requerido="true"
      @update:model-value="$emit('asignar-ubigeo-notario')"
    />

    <!-- Subserie -->
    <SelectInput
      class="col-12 col-md-6 q-pa-sm"
      label="Subserie"
      dense
      outlined
      clearable
      v-model="solicitudForm.subserie_id"
      :options="SubSerieService"
      OptionLabel="nombre"
      OptionValue="id"
    />

    <!-- Otorgante / Favorecido -->
    <InputTextSelect
      class="col-12 col-md-6 q-pa-sm"
      label="Otorgante"
      dense
      outlined
      clearable
      v-model="solicitudForm.otorgantes"
      :options="OtorganteService"
      OptionLabel="nombre_completo"
      OptionValue="nombre_completo"
      :requerido="true"
    />
    <InputTextSelect
      class="col-12 col-md-6 q-pa-sm"
      label="Favorecido"
      dense
      outlined
      clearable
      v-model="solicitudForm.favorecidos"
      :options="FavorecidoService"
      OptionLabel="nombre_completo"
      OptionValue="nombre_completo"
      :requerido="true"
    />

    <!-- Fecha -->
    <div class="col-12 col-md-6 q-pa-sm row">
      <InputAnio
        class="col-12 col-sm-4"
        dense
        outlined
        v-model="solicitudForm.anio"
        :RangoAnios="rangoAnios"
        :requerido="true"
        :readonly="!solicitudForm.notario_id"
        :key="'anio-' + rangoAnios.join('-')"
      />
      <InputMes
        class="col-12 col-sm-4"
        dense
        outlined
        clearable
        v-model="solicitudForm.mes"
        :modelAnio="solicitudForm.anio"
        :readonly="solicitudForm.anio === null"
      />
      <InputDia
        class="col-12 col-sm-4"
        dense
        outlined
        v-model="solicitudForm.dia"
        :modelAnio="solicitudForm.anio"
        :modelMes="solicitudForm.mes"
        :readonly="solicitudForm.anio === null || solicitudForm.mes === null"
      />
    </div>

    <!-- Campos específicos de Escrituras -->
    <div class="col-12 q-pa-sm row">
      <q-input
        class="col-12 col-md-6 q-pa-sm"
        label="Nombre del Bien"
        dense
        outlined
        clearable
        v-model="solicitudForm.bien"
      />
      <q-input
        class="col-12 col-md-3 q-pa-sm"
        label="Escritura"
        dense
        outlined
        clearable
        mask="E-######"
        v-model="solicitudForm.sescritura"
      />
      <q-input
        class="col-12 col-md-3 q-pa-sm"
        label="Protocolo"
        dense
        outlined
        clearable
        mask="P-######"
        v-model="solicitudForm.sprotocolo"
      />
      <q-input
        class="col-12 col-md-3 q-pa-sm"
        label="Folio"
        dense
        outlined
        clearable
        mask="F-######"
        v-model="solicitudForm.sfolio"
      />
      <q-input
        class="col-12 q-pa-sm"
        label="Observaciones"
        dense
        outlined
        clearable
        type="textarea"
        rows="3"
        v-model="solicitudForm.observaciones"
      />
    </div>
  </template>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import SelectInput from "src/components/SelectInput.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";
import InputTextSelect from "src/components/InputTextSelect.vue";

// Servicios (mantener igual que en el original)
import SubSerieService from "src/services/SubSerieService";
import OtorganteService from "src/services/OtorganteService";
import FavorecidoService from "src/services/FavorecidoService";

defineProps({
  solicitudForm: Object,
  notarios: Array,
  rangoAnios: Array,
});

defineEmits(["asignar-ubigeo-notario"]);
</script>
