<template>
  <!-- Layout principal que contiene el encabezado, barra lateral y contenido -->
  <q-layout view="hHh Lpr lff" class="">
    <!-- Cabecera (Header) -->
    <q-header elevated :class="$q.dark.isActive ? 'bg-primary' : 'bg-primary'">
      <q-toolbar>
        <!-- Botón de menú para abrir y cerrar el drawer -->
        <q-btn flat @click="drawer = !drawer" round dense icon="menu" />

        <!-- Título del sitio -->
        <q-toolbar-title>Archivo Regional Puno</q-toolbar-title>

        <!-- Componente para cambiar el modo oscuro -->
        <SwitchDarkMode></SwitchDarkMode>

        <!-- Componente para mostrar el nombre del usuario (o avatar) -->
        <NabvarUser></NabvarUser>
      </q-toolbar>
    </q-header>

    <!-- Barra lateral (Drawer) que contiene la navegación -->
    <q-drawer
      v-model="drawer"
      show-if-above
      :mini="miniState"
      @dblclick="miniState = true"
      @click="miniState = false"
      :width="250"
      :breakpoint="500"
      bordered
      :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-2'"
    >
      <!-- Área con scroll para la lista de elementos -->
      <q-scroll-area class="fit" :horizontal-thumb-style="{ opacity: 0 }">
        <q-list padding>
          <!-- Item de inicio -->
          <q-item
            :to="{ name: 'Dash' }"
            :active="link === 'Dash'"
            @click="link = 'Dash'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-home" /> <!-- Icono para Inicio -->
            </q-item-section>
            <q-item-section> Inicio </q-item-section>
          </q-item>

          <q-separator />

          <!-- Item de Permisos -->
          <q-item v-if="userStore.hasPermission('admin-permisos')"
            :to="{ name: 'Permisos' }"
            :active="link === 'Permisos'"
            @click="link = 'Permisos'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-key" /> <!-- Icono para Permisos -->
            </q-item-section>
            <q-item-section> Permisos </q-item-section>
          </q-item>

          <!-- Item de Roles -->
          <q-item v-if="userStore.hasPermission('admin-roles')"
            :to="{ name: 'Roles' }"
            :active="link === 'Roles'"
            @click="link = 'Roles'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-account-key" /> <!-- Icono para Roles -->
            </q-item-section>
            <q-item-section> Roles </q-item-section>
          </q-item>

          <!-- Item de Usuarios -->
          <q-item v-if="userStore.hasPermission('admin-usuarios')"
            :to="{ name: 'Usuarios' }"
            :active="link === 'Usuarios'"
            @click="link = 'Usuarios'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-account-multiple" /> <!-- Icono para Usuarios -->
            </q-item-section>
            <q-item-section> Usuarios </q-item-section>
          </q-item>

          <!-- Item de Áreas -->
          <q-item v-if="userStore.hasPermission('admin-areas')"
            :to="{ name: 'Areas' }"
            :active="link === 'Areas'"
            @click="link = 'Areas'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-domain" /> <!-- Icono para Áreas -->
            </q-item-section>
            <q-item-section> Áreas </q-item-section>
          </q-item>

          <!-- Item de Busqueda -->
          <q-item v-if="userStore.hasPermission('admin-busquedas')"
            :to="{ name: 'Busqueda' }"
            :active="link === 'Busqueda'"
            @click="link = 'Busqueda'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-magnify" /> <!-- Icono para Busqueda -->
            </q-item-section>
            <q-item-section> Busqueda </q-item-section>
          </q-item>

          <!-- Item de Verificación -->
          <q-item v-if="userStore.hasPermission('admin-verificaciones')"
            :to="{ name: 'Verificacion' }"
            :active="link === 'Verificacion'"
            @click="link = 'Verificacion'"
            clickable
            v-ripple
            class="q-ma-xs rounded-borders"
            active-class="my-menu-link"
          >
            <q-item-section avatar>
              <q-icon name="mdi-check-circle" /> <!-- Icono para Verificación -->
            </q-item-section>
            <q-item-section> Verificacion </q-item-section>
          </q-item>

          <!-- Expansión para Panel de Escrituras -->
          <q-expansion-item expand-separator icon="collections_bookmark" label="Panel de escrituras" default-opened header-class="bg-blue-6 text-white" expand-icon-class="text-white">
            <div class="q-ml-md line-l">
              <!-- Notarios con ícono -->
              <q-item v-if="userStore.hasPermission('admin-notario')" clickable v-ripple class="q-ma-xs rounded-borders" active-class="my-menu-link" :to="{ name: 'Notarios' }" :active="link === 'Notarios'" @click="link = 'Notarios'">
                <q-item-section avatar>
                  <!-- Ícono para Notarios (por ejemplo, un ícono de persona o documento) -->
                  <q-icon name="mdi-account" />
                </q-item-section>
                <q-item-section> Notarios </q-item-section>
              </q-item>

              <!-- Subseries con ícono -->
              <q-item v-if="userStore.hasPermission('admin-subseries')" clickable v-ripple class="q-ma-xs rounded-borders" active-class="my-menu-link" :to="{ name: 'SubSeries' }" :active="link === 'SubSeries'" @click="link = 'SubSeries'">
                <q-item-section avatar>
                  <!-- Ícono para Subseries (por ejemplo, un ícono de lista) -->
                  <q-icon name="mdi-format-list-bulleted" />
                </q-item-section>
                <q-item-section> Subseries </q-item-section>
              </q-item>
            </div>
          </q-expansion-item>


          <q-separator />

          <!-- Expansión para Sistema Administrativo -->
          <q-expansion-item expand-separator icon="list" label="Sistema Administrativo" default-opened header-class="bg-blue-6 text-white" expand-icon-class="text-white">
            <div class="q-ml-md line-l">
              <!-- Solicitudes -->
              <q-item v-if="userStore.hasPermission('admin-solicitudes')" :to="{ name: 'Solicitudes' }" clickable v-ripple class="q-ma-xs rounded-borders">
                <q-item-section avatar>
                  <q-icon name="mdi-file-document" /> <!-- Icono para Solicitudes -->
                </q-item-section>
                <q-item-section> Solicitudes </q-item-section>
              </q-item>

              <!-- Registro de Búsquedas -->
              <q-item v-if="userStore.hasPermission('admin-registro_busquedas')" :to="{ name: 'RegistroBusquedas' }" clickable v-ripple class="q-ma-xs rounded-borders">
                <q-item-section avatar>
                  <q-icon name="mdi-database-search" /> <!-- Icono para Registro de Búsquedas -->
                </q-item-section>
                <q-item-section> Registro de Búsquedas </q-item-section>
              </q-item>

              <!-- Registro de Verificaciones -->
              <q-item v-if="userStore.hasPermission('admin-registro_verificaciones')" :to="{ name: 'RegistroVerificaciones' }" clickable v-ripple class="q-ma-xs rounded-borders">
                <q-item-section avatar>
                  <q-icon name="mdi-check-circle" /> <!-- Icono para Registro de Verificaciones -->
                </q-item-section>
                <q-item-section> Registro de Verificaciones </q-item-section>
              </q-item>
            </div>
          </q-expansion-item>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <!-- Contenedor de la página que carga el contenido de las vistas -->
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { onMounted, ref } from "vue";
import SwitchDarkMode from "components/SwitchDarkMode.vue";
import { useUserStore } from "src/stores/user-store";
import NabvarUser from "components/NabvarUser.vue";
import { useRoute } from "vue-router";
import { format } from "quasar";

// Estado del drawer (barra lateral) y modo mini
const drawer = ref(false);
const miniState = ref(true);

// Ruta activa
const route = useRoute();
const link = ref(route.name);

// Usuario conectado y permisos
const userStore = useUserStore();
</script>

<style lang="sass">
.my-menu-link
  color: white
  background: $primary

.line-l
  border-left: 1px solid $grey-5
</style>
