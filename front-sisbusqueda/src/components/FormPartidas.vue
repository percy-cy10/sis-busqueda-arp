<template>
  <!-- ========================== -->
  <!-- CAMPOS PARA PARTIDAS -->
  <!-- ========================== -->
  <template v-if="solicitudForm.tipo_tramite === 'partidas'">
    <!-- SELECTOR DE TIPO DE PARTIDA - BOTONES -->
    <div class="col-12 q-pa-sm">
      <div class="text-subtitle2 q-mb-sm">
        Tipo de Partida <span class="text-red-7">(*)</span>
      </div>

      <!-- BOTONES DE OPCIONES -->
      <div class="row q-col-gutter-sm q-mb-md">
        <div
          class="col-12 col-sm-4"
          v-for="tipo in tiposPartida"
          :key="tipo.value"
        >
          <q-card
            class="tipo-partida-card cursor-pointer text-center q-pa-md"
            :class="{
              'tipo-partida-selected':
                solicitudForm.tipo_partida === tipo.value,
              'tipo-partida-default': solicitudForm.tipo_partida !== tipo.value,
            }"
            flat
            bordered
            @click="$emit('seleccionar-tipo-partida', tipo.value)"
          >
            <q-card-section class="q-pa-sm">
              <q-icon
                :name="tipo.icon"
                size="40px"
                :color="
                  solicitudForm.tipo_partida === tipo.value
                    ? 'white'
                    : tipo.color
                "
                class="q-mb-sm"
              />
              <div
                class="text-subtitle2 text-weight-medium"
                :class="
                  solicitudForm.tipo_partida === tipo.value
                    ? 'text-white'
                    : 'text-grey-8'
                "
              >
                {{ tipo.label }}
              </div>
            </q-card-section>

            <!-- BADGE DE SELECCIONADO -->
            <q-badge
              v-if="solicitudForm.tipo_partida === tipo.value"
              color="white"
              text-color="primary"
              label="✓ Seleccionado"
              class="absolute-top-right q-ma-xs"
            />
          </q-card>
        </div>
      </div>

      <!-- MENSAJE DE ERROR -->
      <div v-if="errorTipoPartida" class="text-negative q-mt-sm">
        ⚠ Por favor seleccione un tipo de partida
      </div>
    </div>

    <!-- CAMPOS DINÁMICOS PARA PARTIDAS -->
    <div class="col-12 row" v-if="solicitudForm.tipo_partida">
      <!-- FORMULARIO PARA DEFUNCIÓN -->
      <div
        v-if="solicitudForm.tipo_partida === 'defuncion'"
        class="col-12 row q-mt-sm"
      >
        <div class="col-12">
          <div class="text-h6 text-primary q-mb-md">
            <q-icon name="person_remove" class="q-mr-sm" />
            Datos de Defunción
          </div>
        </div>

        <q-input
          class="col-12 col-md-6 q-pa-sm"
          label="Nombre del Fallecido"
          dense
          outlined
          clearable
          v-model="solicitudForm.nombre_fallecido"
          :rules="[
            (val) =>
              (val && val.trim() !== '') || 'Ingrese el nombre del fallecido',
          ]"
        />

        <!-- ===================== -->
        <!-- UBICACIÓN Y FECHA -->
        <!-- ===================== -->
        <div class="row q-col-gutter-sm q-mb-xs">
          <!-- Ubicación del Contrato -->
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

          <!-- Fecha del Contrato -->
          <div class="col-12 col-md-4">
            <div class="text-caption q-pb-xs">
              Fecha de Defunción <span class="text-red-7">(*)</span>
            </div>
            <div class="row q-col-gutter-xs">
              <InputAnio
                class="col-4"
                dense
                outlined
                v-model="solicitudForm.anio"
                :RangoAnios="[1900, new Date().getFullYear()]"
                :requerido="true"
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el año',
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
                  (val) => (val !== null && val !== '') || 'Ingrese el mes',
                ]"
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
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el día',
                ]"
              />
            </div>
          </div>
        </div>

        <q-input
          class="col-12 q-pa-sm"
          label="Observaciones"
          dense
          outlined
          clearable
          type="textarea"
          rows="2"
          v-model="solicitudForm.mas_datos"
          placeholder="Ingrese observaciones adicionales sobre la defunción..."
        />
      </div>

      <!-- FORMULARIO PARA NACIMIENTO -->
      <div
        v-else-if="solicitudForm.tipo_partida === 'nacimiento'"
        class="col-12 row q-mt-sm"
      >
        <div class="col-12">
          <div class="text-h6 text-green q-mb-md">
            <q-icon name="child_friendly" class="q-mr-sm" />
            Datos de Nacimiento
          </div>
        </div>

        <q-input
          class="col-12 col-md-6 q-pa-sm"
          label="Nombre del Nacido"
          dense
          outlined
          clearable
          v-model="solicitudForm.nombre_nacido"
          :rules="[
            (val) =>
              (val && val.trim() !== '') || 'Ingrese el nombre del nacido',
          ]"
        />

        <!-- ===================== -->
        <!-- UBICACIÓN Y FECHA -->
        <!-- ===================== -->
        <div class="row q-col-gutter-sm q-mb-xs">
          <!-- Ubicación del Contrato -->
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

          <!-- Fecha del Contrato -->
          <div class="col-12 col-md-4">
            <div class="text-caption q-pb-xs">
              Fecha de Nacimiento <span class="text-red-7">(*)</span>
            </div>
            <div class="row q-col-gutter-xs">
              <InputAnio
                class="col-4"
                dense
                outlined
                v-model="solicitudForm.anio"
                :RangoAnios="[1900, new Date().getFullYear()]"
                :requerido="true"
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el año',
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
                  (val) => (val !== null && val !== '') || 'Ingrese el mes',
                ]"
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
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el día',
                ]"
              />
            </div>
          </div>
        </div>

        <q-input
          class="col-12 q-pa-sm"
          label="Observaciones"
          dense
          outlined
          clearable
          type="textarea"
          rows="2"
          v-model="solicitudForm.observaciones"
          placeholder="Ingrese observaciones adicionales sobre el nacimiento..."
        />
      </div>

      <!-- FORMULARIO PARA MATRIMONIO -->
      <div
        v-else-if="solicitudForm.tipo_partida === 'matrimonio'"
        class="col-12 row q-mt-sm"
      >
        <div class="col-12">
          <div class="text-h6 text-pink q-mb-md">
            <q-icon name="favorite" class="q-mr-sm" />
            Datos de Matrimonio
          </div>
        </div>

        <!-- NOMBRES DE LOS CÓNYUGES -->
        <q-input
          class="col-12 col-md-6 q-pa-sm"
          label="Nombre del Esposo"
          dense
          outlined
          clearable
          v-model="solicitudForm.nombre_esposo"
          :rules="[
            (val) =>
              (val && val.trim() !== '') || 'Ingrese el nombre del esposo',
          ]"
        />
        <q-input
          class="col-12 col-md-6 q-pa-sm"
          label="Nombre de la Esposa"
          dense
          outlined
          clearable
          v-model="solicitudForm.nombre_esposa"
          :rules="[
            (val) =>
              (val && val.trim() !== '') || 'Ingrese el nombre de la esposa',
          ]"
        />

        <!-- ===================== -->
        <!-- UBICACIÓN Y FECHA -->
        <!-- ===================== -->
        <div class="row q-col-gutter-sm q-mb-xs">
          <!-- Ubicación del Contrato -->
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

          <!-- Fecha del Contrato -->
          <div class="col-12 col-md-4">
            <div class="text-caption q-pb-xs">
              Fecha de Matrimonio <span class="text-red-7">(*)</span>
            </div>
            <div class="row q-col-gutter-xs">
              <InputAnio
                class="col-4"
                dense
                outlined
                v-model="solicitudForm.anio"
                :RangoAnios="[1900, new Date().getFullYear()]"
                :requerido="true"
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el año',
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
                  (val) => (val !== null && val !== '') || 'Ingrese el mes',
                ]"
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
                :rules="[
                  (val) => (val !== null && val !== '') || 'Ingrese el día',
                ]"
              />
            </div>
          </div>
        </div>

        <q-input
          class="col-12 q-pa-sm"
          label="Observaciones"
          dense
          outlined
          clearable
          type="textarea"
          rows="2"
          v-model="solicitudForm.observaciones"
          placeholder="Ingrese observaciones adicionales sobre el matrimonio..."
        />
      </div>
    </div>

    <!-- MENSAJE CUANDO NO HAY TIPO SELECCIONADO -->
    <div v-else class="col-12 q-pa-sm text-center text-grey q-mt-md">
      <q-icon name="info" size="30px" class="q-mb-sm" />
      <div>
        Seleccione un tipo de partida para mostrar los campos correspondientes
      </div>
    </div>
  </template>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import SelectUbigeoPlus from "src/components/SelectUbigeoPlus.vue";
import InputAnio from "src/components/InputAnio.vue";
import InputMes from "src/components/InputMes.vue";
import InputDia from "src/components/InputDia.vue";

// Tipos de partida con iconos y colores
const tiposPartida = [
  {
    label: "Defunción",
    value: "defuncion",
    icon: "person_remove",
    color: "red",
  },
  {
    label: "Nacimiento",
    value: "nacimiento",
    icon: "child_friendly",
    color: "green",
  },
  { label: "Matrimonio", value: "matrimonio", icon: "favorite", color: "pink" },
];

defineProps({
  solicitudForm: Object,
  errorTipoPartida: Boolean,
});

defineEmits(["seleccionar-tipo-partida"]);
</script>
