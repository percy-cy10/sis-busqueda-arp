<template>
  <div class="q-px-md q-py-md">
    <div class="row justify-between">
        <div>
            <span class="text-h4 text-weight-bold">Historial de Busqueda</span>
            <div class="row q-mb-xs">
                <q-breadcrumbs>
                    <q-breadcrumbs-el label="Busqueda" icon="list" to="/busqueda" />
                    <q-breadcrumbs-el :label="stringid" icon="visibility" />
                </q-breadcrumbs>
            </div>
        </div>
        <div>
            <!-- <q-btn v-if="!periodoCerrado" color="negative" label="Eliminar" @click="eliminar(props.periodoId)"
                class="q-mr-sm" /> -->
            <q-btn color="warning" label="Volver"  @click="$router.push('/busqueda')"/>
        </div>
    </div>
</div>
    <!-- <q-card class="q-px-sm q-mt-sm" style="height: 80vh;"> -->
        <div class="row">
          <div class="q-pa-sm col-md-8 col-xs-12">
            <q-card>
              <q-card-section class="q-pt-sm">
                <div class="col-xs-12 col-md-8">
                  <div class="text-h6 text-weight-bold text-uppercase text-center">
                    DATOS DE SOLICITUD
                  </div>
                  <q-separator />
                  <div class="q-my-md row q-pa-md">
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase">Numero de Solicitud</span>
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ stringid }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Otorgante</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.otorgantes }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Favorecido</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.favorecidos }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase">Escritura Pública - Partida</span>
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.sub_serie.nombre }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase">Notario Público</span>
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.notario.nombre_completo }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase">Solicitante</span>
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.solicitante.nombre_completo }}
                      </p>
                    </div>
                    <div class="col-12 col-md-12">
                      <span class="text-weight-bold text-uppercase">Registrado Por</span>
                      <p class="text-uppercase"
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.solicitud.user.name }}
                      </p>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="q-pa-sm col-md-4 col-xs-12">
            <q-card>
              <q-card-section class="q-pt-sm">
                <div class="col-xs-12 col-md-8">
                  <div class="text-h6 text-weight-bold text-uppercase text-center">
                    Datos de Busqueda
                  </div>
                  <q-separator />
                  <div class="q-my-md row q-pa-md">
                    <div class="col-12 col-md-12">
                      <span class="text-weight-bold text-uppercase">Registrado Por</span>
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                       {{ form.user.name }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Codigo de Protocolo</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.cod_protocolo }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Codigo de Escritura</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.cod_escritura }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Folio Inicial</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.cod_folioInicial }}
                      </p>
                    </div>
                    <div class="col-12 col-md-6">
                      <span class="text-weight-bold text-uppercase"
                        >Folio Final</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.cod_folioFinal }}
                      </p>
                    </div>
                    <div class="col-12 col-md-12">
                      <span class="text-weight-bold text-uppercase"
                        >Observaciones</span
                      >
                      <p
                        :class="
                          $q.dark.isActive
                            ? 'custom-input--dark'
                            : 'custom-input--light'
                        "
                      >
                        {{ form.observaciones }}
                      </p>
                    </div>
    
                    <div>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <!-- <div class="q-pa-sm col-12">
            <q-card flat bordered>
              <q-card-section>
                <q-markup-table
                  separator="cell"
                  class="q-pa-none"
                  dense
                  flat
                  bordered
                >
                  <thead>
                    <tr>
                      <th class="text-left">N°</th>
                      <th class="text-right">Descripcion</th>
                      <th class="text-right">Archivo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(a, i) in folio.adjuntos" :key="i">
                      <td>{{ i + 1 }}</td>
                      <td>{{ a.descripcion }}</td>
                      <td style="white-space: normal">
                        <a v-if="a.archivo" :href="a.archivo" target="_blank"
                          >{{ i + 1 }} archivo</a
                        >
                        <span v-else>N/A</span>
                      </td>
                    </tr>
                  </tbody>
                </q-markup-table>
              </q-card-section>
            </q-card>
          </div> -->
        </div>
      <!-- </q-card> -->
</template>

<script setup>
import { ref, onMounted } from "vue";
import BusquedaService from "src/services/BusquedaService";
import { useQuasar } from "quasar";
import { useRoute, useRouter } from "vue-router";
const route = useRoute();
const stringid = ref();
const form = ref({
    solicitud: {
        sub_serie: {},
        notario: {},
        solicitante: {},
        user: {},
    },
    user: {},
});

onMounted(async () =>{
    console.log(route);
    const res = await BusquedaService.get(route.params.id);
    console.log(res);
    form.value = res;
    stringid.value = res.solicitud.id.toString().padStart(5, '0');
})

</script>

<style scoped>
.custom-input--dark {
    /* Añadir estilos específicos del input */
    padding: 10px;
    font-size: 16px;
    margin-right: 15px;
    border: 2px solid transparent;
    border-radius: 10px;
    outline: none;
    background-color: #424242;
    color: #fff;
    transition: 0.5s ease;
    /* Otros estilos según tus preferencias */
  }
  
  .custom-input--dark:focus,
  .custom-input--dark:hover {
    outline: none;
    border-color: #0b7b8d;
    box-shadow: 0 0 0 4px rgba(13, 182, 224, 0.2);
  }
  .custom-input--light {
    /* Añadir estilos específicos del input */
    padding: 10px;
    font-size: 16px;
    margin-right: 15px;
    border: 2px solid transparent;
    border-radius: 10px;
    outline: none;
    background-color: #e4e9ee81;
    color: #0d0c22;
    transition: 0.5s ease;
    /* Otros estilos según tus preferencias */
  }
  
  .custom-input--light:focus,
  .custom-input--light:hover {
    outline: none;
    border-color: rgba(76, 234, 226, 0.4);
    background-color: #fff;
    box-shadow: 0 0 0 4px rgba(13, 217, 224, 0.068);
  }
</style>