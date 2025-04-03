const routes = [
  {
    path: "/login",

    component: () => import("layouts/AuthLayout.vue"),
    children: [
      {
        path: "",
        name: "Login",
        component: () => import("pages/Auth/LoginPage.vue"),
      },
    ],
  },

  {
    path: "/",

    component: () => import("layouts/AdminLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        path: "admin",
        name: "Dash",
        component: () => import("pages/Solicitudes/SolicitudesList.vue"),
      },
      {
        path: "permisos",
        name: "Permisos",
        component: () => import("pages/Admin/Permisos/PermisosList.vue"),
      },
      {
        path: "roles",
        name: "Roles",
        component: () => import("pages/Admin/Roles/RolesList.vue"),
      },
      {
        path: "usuarios",
        name: "Usuarios",
        component: () => import("pages/Admin/Usuarios/UsuariosList.vue"),
      },
      {
        path: "anteriores",
        name: "Anteriores",
        component: () => import("pages/Arp_v1/Anteriores/AnterioresIndex.vue"),
      },
      {
        path: "anteriores2",
        name: "Anteriores2",
        component: () => import("pages/Arp_v1/Anteriores2/Anteriores2Index.vue"),
      },
      {
        path: "arbolito",
        name: "Arbolito",
        component: () => import("pages/Arp_v1/Arbolito/ArbolitoIndex.vue"),
      },
      {
        path: "nuevo",
        name: "Nuevo",
        component: () => import("pages/Arp_v1/Nuevo/NuevoIndex.vue"),
      },
      {
        path: "nuevo2",
        name: "Nuevo2",
        component: () => import("pages/Arp_v1/Nuevo2/Nuevo2Index.vue"),
      },
      {
        path: "sia",
        name: "Sia",
        component: () => import("pages/Arp_v1/Sia/SiaIndex.vue"),
      },
      {
        path: "sis2018",
        name: "Sis2018",
        component: () => import("pages/Arp_v1/Sis2018/Sis2018Index.vue"),
      },
      {
        path: "sis2018_2",
        name: "Sis2018_2",
        component: () => import("pages/Arp_v1/Sis2018_2/Sis2018_2Index.vue"),
      },
      {
        path: "notarios",
        name: "Notarios",
        component: () => import("pages/Admin/Notarios/NotariosList.vue"),
      },
      {
        path: "subseries",
        name: "SubSeries",
        component: () => import("pages/Admin/SubSeries/SubSeriesList.vue"),
      },
      {
        path: "areas",
        name: "Areas",
        component: () => import("pages/Admin/Areas/AreasList.vue"),
      },
      {
        path: "escrituras/:id",
        name: "Escrituras",
        component: () => import("pages/Libros/ShowEcriturastoList.vue"),
      },

      {
        path: "test",
        name: "Test",
        component: () => import("pages/Test/TestDni.vue"),
      },
      {
        path: "busqueda",
        name: "Busqueda",
        component: () => import("pages/Solicitudes/Registros/BusquedasList.vue"),
      },
      {
        path: "verificacion",
        name: "Verificacion",
        component: () => import("pages/Solicitudes/Registros/VerificacionesList.vue"),
      },
      {
        path: "/busqueda/:id",
        name: "Busquedashow",
        component: () => import("components/ShowBusqueda.vue"),
      },
      {
        path: "/verificacion/:id",
        name: "Verificacioneshow",
        component: () => import("components/ShowVerificacion.vue"),
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
