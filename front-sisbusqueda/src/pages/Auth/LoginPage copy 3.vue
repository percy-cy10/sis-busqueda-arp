<template>
  <q-layout view="hHh lpR fFf">
    <!-- Encabezado -->
    <q-header elevated class="bg-white header-custom">
      <q-toolbar class="q-px-lg">
        <q-toolbar-title class="row items-center no-wrap">
          <img :src="logoSrc" alt="Logo ARP" class="logo-large" />
          <span class="text-h6 q-ml-md text-sky-500">Archivo Regional de Puno</span>
        </q-toolbar-title>

        <q-btn-group flat class="q-ml-lg">
          <!-- <q-btn flat label="Inicio" to="/" color="primary" />
          <q-btn flat label="Nosotros" to="#nosotros" color="primary" />
          <q-btn flat label="SEGUIMIENTO" to="#seguimiento" color="primary" />
          <q-btn flat label="BUSQUEDA" to="#busqueda" color="primary" />
          <q-btn flat label="Comunicados" to="#comunicados" color="primary" />
          <q-btn flat label="Contacto" to="#contacto" color="primary" /> -->
          <q-btn flat label="Inicio" @click="goToSection('inicio')" color="primary" />
          <q-btn flat label="Nosotros" @click="goToSection('nosotros')" color="primary" />
          <q-btn flat label="SEGUIMIENTO" @click="goToSection('seguimiento')" color="primary" />
          <q-btn flat label="BUSQUEDA" @click="goToSection('busqueda')" color="primary" />
          <q-btn flat label="Comunicados" @click="goToSection('comunicados')" color="primary" />
          <q-btn flat label="Contacto" @click="goToSection('contacto')" color="primary" />

        </q-btn-group>

        <q-space />
        <q-btn unelevated color="primary" label="Iniciar Sesión" @click="showLogin = true" />
      </q-toolbar>
    </q-header>

    <!-- Contenido principal -->
    <q-page-container class="bg-dark text-light q-pa-md bg-white ">
      <div class="full-height flex flex-center">
        <!-- <img :src="fondoSrc" alt="Foto" class="responsive-img" /> -->
        <!-- INICIO -->
        <div v-if="currentSection === 'inicio'" class="full-height flex flex-center q-pa-lg">
          <img :src="fondoSrc" alt="Foto" class="responsive-img" />
        </div>

        <!-- COMUNICADOS -->
        <!-- <div v-else-if="currentSection === 'comunicados'" class="row q-col-gutter-md q-pa-md">
          <q-card
            v-for="n in 3"
            :key="n"
            class="col-12 col-md-4 q-pa-sm shadow-2 rounded-borders"
          >
            <q-img :src="logoAGN" ratio="16/9" class="q-mb-sm" />
            <q-card-section>
              <div class="text-h6 text-blue">Comunicado {{ n }}</div>
              <div class="text-caption text-grey-7 q-mt-xs">
                Fecha: {{ new Date().toLocaleDateString() }}
              </div>
              <q-separator class="q-my-sm" />
              <div class="text-body2">
                Este es el detalle del comunicado {{ n }}. Aquí se puede incluir una descripción más extensa o información relevante.
              </div>
            </q-card-section>
          </q-card>
        </div> -->
        <div v-else-if="currentSection === 'comunicados1'" class="q-pa-md">
          <q-img
            :src="logoAgn"
            contain
            style="max-height: 80px; max-width: 40%;"
            class="q-mb-md"
          />

          <!-- Grid de comunicados -->
          <div class="row q-col-gutter-md">
            <q-card
              v-for="(item, index) in paginatedComunicados"
              :key="index"
              class="col-12 col-md-4 shadow-2 rounded-borders q-pa-md"
            >
              <q-img :src="logoAGN" ratio="16/9" class="q-mb-sm" />
              <q-card-section>
                <div class="text-h6 text-blue">Comunicado {{ item.id }}</div>
                <div class="text-caption text-grey-7 q-mt-xs">
                  Fecha: {{ new Date().toLocaleDateString() }}
                </div>
                <q-separator class="q-my-sm" />
                <div class="text-body2">
                  {{ item.detalle }}
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Paginación solo para esta sección -->
          <div class="row justify-center q-mt-md">
            <q-pagination
              v-model="page"
              :max="pagesNumber"
              color="primary"
              boundary-numbers
              size="md"
            />
          </div>
        </div>

        <div v-else-if="currentSection === 'comunicados'" class="q-pa-md">
  <q-img
    :src="logoAgn"
    contain
    style="max-height: 80px; max-width: 40%;"
    class="q-mb-md"
  />

  <!-- Grid de comunicados -->
  <div class="row q-col-gutter-lg">
    <q-card
      v-for="(item, index) in paginatedComunicados"
      :key="index"
      class="col-12 col-md-4 shadow-2 rounded-borders q-pa-md q-mb-lg"
    >
      <!-- Imagen dentro del comunicado -->
      <q-img
        :src="item.imagen || logoAGN"
        ratio="16/9"
        class="q-mb-md rounded-borders"
      />

      <q-card-section>
        <div class="text-h6 text-blue">
          Comunicado {{ item.id }}
        </div>
        <div class="text-caption text-grey-7 q-mt-xs">
          Fecha: {{ new Date().toLocaleDateString() }}
        </div>
        <q-separator class="q-my-sm" />
        <div class="text-body2">
          {{ item.detalle }}
        </div>
      </q-card-section>
    </q-card>
  </div>

  <!-- Paginación solo para esta sección -->
  <div class="row justify-center q-mt-md">
    <q-pagination
      v-model="page"
      :max="pagesNumber"
      color="primary"
      boundary-numbers
      size="md"
    />
  </div>
</div>





        <!-- SEGUIMIENTO -->
        <div v-else-if="currentSection === 'seguimiento'" class="q-pa-xl flex flex-center">
          <q-card flat bordered class="q-pa-lg shadow-2" style="width: 100%; max-width: 600px;">
            <q-card-section>
              <div class="text-h6 text-blue q-mb-md">Seguimiento de Trámite</div>
              <q-form @submit.prevent="enviar">
                <q-input filled label="Número de trámite" class="q-mb-md" />
                <q-input filled label="DNI del solicitante" class="q-mb-md" />
                <q-btn label="Consultar" type="submit" color="primary" class="full-width" />
              </q-form>
            </q-card-section>
          </q-card>
        </div>

        <!-- NOSOTROS -->
        <!-- <div v-else-if="currentSection === 'nosotros'" class="q-pa-xl">
          <q-card flat bordered class="q-pa-lg shadow-1">
            <q-card-section>
              <h5 class="text-blue">Misión</h5>
              <p>Conservar, custodiar y difundir el patrimonio documental de la región.</p>
              <h5 class="text-blue">Visión</h5>
              <p>Ser referente nacional en la preservación de la memoria histórica.</p>
              <h5 class="text-blue">Servicios</h5>
              <ul>
                <li>Atención al ciudadano</li>
                <li>Acceso a la información</li>
                <li>Gestión documental</li>
              </ul>
            </q-card-section>
          </q-card>
        </div> -->

        <!-- NOSOTROS -->
        <div v-else-if="currentSection === 'nosotros'" class="q-pa-xl">

          <!-- Primera fila: Misión y Visión -->
          <div class="row q-col-gutter-md q-mb-lg">
            <div class="col-12 col-md-6">
              <q-card flat bordered class="q-pa-lg shadow-1">
                <q-card-section>
                  <h5 class="text-blue">Misión</h5>
                  <p>
                    Conservar, custodiar y difundir el patrimonio documental de la región,
                    garantizando el acceso a la información pública y preservando la memoria histórica.
                  </p>
                </q-card-section>
              </q-card>
            </div>
            <div class="col-12 col-md-6">
              <q-card flat bordered class="q-pa-lg shadow-1">
                <q-card-section>
                  <h5 class="text-blue">Visión</h5>
                  <p>
                    Ser un referente nacional en la preservación documental,
                    con innovación tecnológica y calidad en el servicio.
                  </p>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Segunda fila: Servicios y Foto -->
          <div class="row q-col-gutter-md q-mb-lg">
            <div class="col-12 col-md-6">
              <q-card flat bordered class="q-pa-lg shadow-1">
                <q-card-section>
                  <h5 class="text-blue">Servicios</h5>
                  <ul>
                    <li>Atención al ciudadano</li>
                    <li>Acceso a la información</li>
                    <li>Gestión documental</li>
                    <li>Conservación preventiva</li>
                    <li>Reprografía documental</li>
                  </ul>
                </q-card-section>
              </q-card>
            </div>
            <div class="col-12 col-md-6 flex flex-center">
              <q-card flat bordered class="shadow-1 q-pa-md" style="max-width: 400px;">
                <q-img :src="logoAgn" ratio="16/9" class="rounded-borders" />
                <q-card-section class="text-center text-blue">
                  <div class="text-subtitle1">Archivo General de la Nación</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Tercera fila: Tipos de archivos -->
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-4">
              <q-card flat bordered class="q-pa-md shadow-2">
                <q-img :src="logoAgn" ratio="16/9" class="rounded-borders q-mb-sm" />
                <q-card-section>
                  <div class="text-h6 text-blue">Archivo Histórico</div>
                  <div class="text-body2">
                    Documentos antiguos que forman parte del patrimonio cultural e histórico de la región.
                  </div>
                </q-card-section>
              </q-card>
            </div>
            <div class="col-12 col-md-4">
              <q-card flat bordered class="q-pa-md shadow-2">
                <q-img :src="logoAgn" ratio="16/9" class="rounded-borders q-mb-sm" />
                <q-card-section>
                  <div class="text-h6 text-blue">Archivo Administrativo</div>
                  <div class="text-body2">
                    Documentación de gestión pública generada por entidades de la administración estatal.
                  </div>
                </q-card-section>
              </q-card>
            </div>
            <div class="col-12 col-md-4">
              <q-card flat bordered class="q-pa-md shadow-2">
                <q-img :src="logoAgn" ratio="16/9" class="rounded-borders q-mb-sm" />
                <q-card-section>
                  <div class="text-h6 text-blue">Archivo Intermedio</div>
                  <div class="text-body2">
                    Documentos en trámite o con valor administrativo temporal, en proceso de clasificación.
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

        </div>


        <!-- BÚSQUEDA -->
        <div v-else-if="currentSection === 'busqueda'" class="q-pa-xl flex flex-center">
          <q-card flat bordered class="q-pa-lg shadow-2" style="width: 100%; max-width: 600px;">
            <q-card-section>
              <div class="text-h6 text-blue q-mb-md">Búsqueda de Documentos</div>
              <q-form @submit.prevent="enviar">
                <q-input filled label="Otorgante" class="q-mb-md" />
                <q-input filled label="Favorecido" class="q-mb-md" />
                <q-input filled label="Notario" class="q-mb-md" />
                <q-btn label="Buscar" type="submit" color="primary" class="full-width" />
              </q-form>
            </q-card-section>
          </q-card>
        </div>

        <!-- CONTACTO / OTROS -->
        <div v-else class="q-pa-xl text-center">
          <q-card flat bordered class="q-pa-lg shadow-1">
            <q-card-section>
              <q-icon name="info" size="48px" color="blue" />
              <div class="text-subtitle1 q-mt-md">Sección en construcción...</div>
            </q-card-section>
          </q-card>
        </div>



      </div>

      <!-- Sección de tres columnas -->
      <div class="row q-col-gutter-md q-mt-md q-pa-md text-black rounded-borders">
        <!-- Columna 1: ARP -->
        <div class="col-xs-12 col-md-4 text-center">
          <div class="text-h6 text-blue">Archivo Regional de Puno</div>
          <q-separator color="blue" class="q-my-md" />
          <q-img :src="logoSrc" contain class="q-mx-auto q-mb-md" style="max-height: 80px; max-width: 80%;" />
          <div class="text-body2">
            Institución encargada de conservar, custodiar y administrar el patrimonio documental de la región, promoviendo el acceso a la información pública y la memoria histórica del pueblo puneño.
          </div>
          <div class="q-mt-sm text-italic text-subtitle2">Preservando la memoria de Puno</div>
        </div>

        <!-- Columna 2: Transparencia Web -->
        <div class="col-xs-12 col-md-4">
          <div class="text-h6 text-blue text-center">Transparencia Web</div>
          <q-separator color="blue" class="q-my-md" />

          <div><q-icon name="person" color="blue" /> <strong>Lic. PAREDES DIAZ, Adiwir Russo </strong></div>
          <div class="q-mt-xs text-caption">→ Administrador del ARP</div>
          <div class="text-caption">→ R.E.R. Nº 045-2024-GRP/ARP</div>
          <div class="q-mb-md text-caption">
            <q-icon name="email" class="q-mr-xs" color="blue" />
            <a href="mailto:administracion@archivoregionalpuno.gob.pe" class="text-black text-underline">
              administracion@archivoregionalpuno.gob.pe
            </a>
          </div>

          <div class="q-mt-md">
            <q-icon name="engineering" color="blue" />
            <strong> Ing. Percy Condori Yucra</strong>
          </div>
          <div class="text-caption">→ Responsable del Portal de Transparencia</div>
          <div class="text-caption">→ R.E.R. Nº 046-2024-GRP/ARP</div>
          <div class="text-caption">
            <q-icon name="email" class="q-mr-xs" color="black" />
            <a href="mailto:sistemas@archivoregionalpuno.gob.pe" class="text-black text-underline">
              sistemas@archivoregionalpuno.gob.pe
            </a>
          </div>
        </div>

        <!-- Columna 3: Contacto -->
        <div class="col-xs-12 col-md-4 text-center">
          <div class="text-h6 text-blue">Contacto</div>
          <q-separator color="blue" class="q-my-md" />
          <q-img :src="logoAGN" contain style="max-height: 80px; max-width: 40%;" class="q-mb-md" />
          <div class="text-body2">
            <strong>Archivo Regional de Puno</strong>
          </div>
          <div class="text-caption q-mt-sm">→ RUC Nº 20486315411</div>
          <div class="text-caption">→ Jr. Deustua Nº 356, Cercado - Puno</div>
          <div class="text-caption">
            <q-icon name="email" class="q-mr-xs" color="black" />
            <a href="mailto:contacto@archivoregionalpuno.gob.pe" class="text-black text-underline">
              contacto@archivoregionalpuno.gob.pe
            </a>
          </div>
          <div class="text-caption">
            <q-icon name="call" class="q-mr-xs" color="black" />
            (+051) 368501
          </div>
        </div>
      </div>

      <!-- Footer -->
      <q-separator color="blue-4" class="q-my-lg" />
      <div class="text-center text-caption text-grey-4">
        <div class="text-blue"><strong>
          Abg. Everardo, ARACAYO QUISPE – Director del Archivo Regional de Puno</strong>
        </div>
        <div class="text-black">© 2023-2026 Archivo Regional de Puno. Todos los derechos reservados.</div>
      </div>
    </q-page-container>

    <!-- Modal Login -->
    <q-dialog v-model="showLogin" transition-show="slide-down" transition-hide="slide-up">
      <q-card class="login-card">
        <q-card-section>
          <img :src="logoSrc" alt="Logo" class="logo-centred q-mb-md" />
          <LoginForm ref="loginForm" @logued="onLogued" />
        </q-card-section>

        <q-card-actions vertical>
          <div class="text-caption text-center q-mb-sm">
            ¿Olvidaste tu contraseña?
            <a href="#" @click.prevent="onForgotPassword" class="text-primary">Recupérala aquí</a>
          </div>
          <div class="text-caption text-center q-mb-sm">
            Al iniciar sesión, aceptas nuestros
            <a href="#" class="text-primary">Términos</a> y
            <a href="#" class="text-primary">Privacidad</a>.
          </div>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-layout>
</template>

<script setup>
import { ref, computed  } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import LoginForm from 'pages/Auth/LoginForm.vue'
import carousel1 from 'assets/img/carousel1.jpg'
import carousel2 from 'assets/img/carousel2.jpg'
import logo from 'assets/img/logo_ARP.png'
import fondo from 'assets/img/fondo.jpg'
import logoAGN from 'assets/img/logo-agn.png'

const $q = useQuasar()
const router = useRouter()

// Estado
const showLogin = ref(false)
const step = ref(1)
const loading = ref(false)

// Imágenes
const fondoSrc = fondo

const logoSrc = logo
const images = [{ src: carousel1 }, { src: carousel2 }]

const logoAgn = logoAGN

// Eventos
function onLogued() {
  showLogin.value = false
  router.push({ name: 'Dash' })
  $q.notify({ type: 'positive', message: '¡Bienvenido!' })
}


// Estado de la sección actual
const currentSection = ref('inicio')

// Función para cambiar la sección desde el menú
function goToSection(section) {
  currentSection.value = section
}


// Lista de comunicados simulada (puedes reemplazarla por tu data real)
const comunicados = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  detalle: `Este es el detalle del comunicado ${i + 1}.`
}))

// Paginación
const page = ref(1)
const perPage = 3

const pagesNumber = computed(() => Math.ceil(comunicados.length / perPage))

const paginatedComunicados = computed(() => {
  const start = (page.value - 1) * perPage
  return comunicados.slice(start, start + perPage)
})

function onForgotPassword() {
  $q.dialog({
    title: 'Recuperar contraseña',
    message: 'Se enviará un enlace a tu email.',
    cancel: true,
    persistent: true
  }).onOk(() => {
    $q.notify({ type: 'info', message: 'Enlace de recuperación enviado.' })
  })
}

function enviar() {
  loading.value = true
  setTimeout(() => {
    $q.notify({ type: 'positive', message: 'Formulario enviado con éxito 🎉' })
    loading.value = false
  }, 1500)
}
</script>

<style scoped>
.header-custom {
  height: 70px;
  padding: 0 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.logo-large {
  max-height: 65px;
  margin-top: 5px;
  transition: transform 0.2s ease-in-out;
}
.logo-centred {
  display: block;
  margin: 24px auto;
  max-width: 300px;
  padding: 0 16px;
}
.login-card {
  width: 360px;
  max-width: 90vw;
  border-radius: 12px;
  overflow: hidden;
}
.responsive-img {
  max-width: 100%;
  height: auto;
  padding-top: 10px;
  border-radius: 0;
  box-shadow: 0px rgba(0, 0, 0, 0.3);
}
.rounded-borders {
  border-radius: 16px;
}
.text-underline {
  text-decoration: underline;
}
</style>
