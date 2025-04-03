import { defineStore } from "pinia";
import { api } from "src/boot/axios";
import { Cookies } from "quasar";
import { secureStorage } from "src/utils/SecureStorage";

// Definimos la tienda (store) para gestionar el estado del usuario
export const useUserStore = defineStore("user", {
  // Estado del store: aquí guardamos la información del usuario
  state: () => ({
    id: null,           // ID del usuario
    name: null,         // Nombre del usuario
    email: null,        // Correo electrónico del usuario
    area_id: null,      // ID del área a la que pertenece el usuario
    roles: null,        // Roles asignados al usuario
    permisos: null,     // Permisos asignados al usuario
    area: null,         // Nombre del área a la que pertenece el usuario
  }),

  // Getters para acceder al estado del store
  getters: {
    getId: (state) => state.id,                 // Obtener el ID del usuario
    getName: (state) => state.name,             // Obtener el nombre del usuario
    getEmail: (state) => state.email,           // Obtener el email del usuario
    getRoles: (state) => state.roles,           // Obtener los roles del usuario
    getPermisos: (state) => state.permisos,     // Obtener los permisos del usuario
    getArea: (state) => state.area,             // Obtener el área del usuario
    getAreaId: (state) => state.area_id,        // Obtener el ID del área del usuario
  },

  // Acciones que permiten realizar tareas asíncronas o modificar el estado
  actions: {
    // Función para iniciar sesión (login)
    async login(email, password) {
      Cookies.remove("token");                  // Eliminar el token actual
      secureStorage.removeItem("user");         // Eliminar datos del usuario de almacenamiento seguro

      try {
        // Solicitud POST a la API para obtener el token de acceso
        const res = await api.post("oauth/token", {
          grant_type: "password",                // Tipo de autorización (password)
          client_id: import.meta.env.VITE_APP_ID, // ID de cliente (configurado en .env)
          client_secret: import.meta.env.VITE_APP_SECRET, // Secreto de cliente (configurado en .env)
          username: email,                        // Email del usuario
          password: password,                     // Contraseña del usuario
          scope: "",                              // Ámbito de acceso
        });

        // Almacenar el token en las cookies
        let tokenString = "Bearer " + res.data.access_token;
        Cookies.set("token", tokenString, { path: "/" }); // Establecer el token con un alcance global
      } catch (e) {
        if (e) throw e; // Si ocurre un error, lanzarlo
      }
    },

    // Función para obtener los datos del usuario
    async getUser() {
      try {
        // Solicitar los datos del usuario a la API
        const user = await api.get("api/user");

        // Guardar la información del usuario de forma segura
        secureStorage.setItem("user", user.data.user);

        // Establecer los datos del usuario en el store
        this.setUser(user.data);
      } catch (e) {
        if (e) throw e; // Si ocurre un error, lanzarlo
      }
    },

    // Función para cerrar sesión (logout)
    logout() {
      Cookies.remove("token");  // Eliminar el token
      this.clearUser();         // Limpiar los datos del usuario
    },

    // Función para asignar los datos del usuario al estado del store
    setUser(payload) {
      // Asignar la información básica del usuario
      if (payload.user.id) this.id = payload.user.id;
      if (payload.user.name) this.name = payload.user.name;
      if (payload.user.email) this.email = payload.user.email;
      if (payload.user.area_id) this.area_id = payload.user.area_id;
      if (payload.user.area?.nombre) this.area = payload.user.area.nombre;

      // Asignar roles y permisos
      if (payload.roles) this.roles = payload.roles;       // Roles del usuario
      if (payload.permisos) {
        // Si el usuario es admin, asignar todos los permisos
        if (this.roles.includes('admin')) {
          // Asignar todos los permisos de administrador
          this.permisos = [
            'admin-solicitudes',
            'admin-registro_busquedas',
            'admin-registro_verificaciones',
            'admin-gestion_usuarios',
            'admin-ver_reportes',  // Añadir más permisos si es necesario
            ...payload.permisos     // Combinar con los permisos adicionales del usuario
          ];
        } else {
          // Si no es admin, asignar solo los permisos del usuario
          this.permisos = payload.permisos;
        }
      }
    },

    // Función para limpiar los datos del usuario
    clearUser() {
      this.id = null;
      this.name = null;
      this.email = null;
      this.area_id = null;
      this.permisos = null;
      this.roles = null;
      this.area = null;
    },

    // Función para verificar si el usuario tiene un permiso específico
    hasPermission(permission) {
      return this.permisos?.includes(permission);  // Verificar si el permiso está en la lista de permisos
    },
  },
});
// Exportar la tienda para su uso en otros componentes
